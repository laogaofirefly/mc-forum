<?php

namespace App\Console\Commands;

use App\Models\GameChatMessage;
use App\Services\MinecraftLogSyncService;
use Illuminate\Console\Command;

class SyncGameChat extends Command
{
    protected $signature = 'chat:sync
                            {--reset : 重置读取位置，下次从头读最后 64KB}
                            {--clean : 清理已知的错误入库数据（voicechat 等插件消息）}
                            {--watch : 持续监听模式，每 10 秒同步一次（按 Ctrl+C 退出）}';

    protected $description = '从 MC 服务器 logs/latest.log 同步玩家聊天记录到数据库';

    public function handle(MinecraftLogSyncService $service): int
    {
        if ($this->option('clean')) {
            $this->cleanJunkMessages();
            // 清理后顺便重置位置，重新解析一遍日志
            $service->resetPosition();
            $this->info('已重置读取位置');
        }

        if ($this->option('reset')) {
            $service->resetPosition();
            $this->info('已重置读取位置，下次会从日志末尾 64KB 开始读取');
        }

        if ($this->option('watch')) {
            $this->info('开始持续监听 MC 日志（每 10 秒同步一次），按 Ctrl+C 退出...');
            $this->info('-----------------------------------');
            while (true) {
                $this->runOnce($service);
                sleep(10);
            }
        }

        return $this->runOnce($service);
    }

    /**
     * 清理已知错误入库的消息（voicechat 等插件消息）
     * 注意：SQLite 不支持 REGEXP，所以用 LIKE 和 PHP 端过滤组合
     */
    private function cleanJunkMessages(): void
    {
        $this->info('开始清理错误入库的消息...');

        $totalDeleted = 0;

        // 1. 删除 player_name 是已知插件名的记录
        $pluginNames = [
            'voicechat', 'essentials', 'luckperms', 'vault',
            'worldedit', 'worldguard', 'placeholderapi', 'papi',
            'multiverse', 'protocollib', 'viaversion',
            'dynmap', 'bluemap', 'pl3xmap',
            'discordsrv', 'simple-voice-chat', 'plasmo-voice',
            'skript', 'mythicmobs', 'citizens',
            'coreprotect', 'logblock',
        ];
        foreach ($pluginNames as $name) {
            $c = GameChatMessage::where('player_name', $name)->delete();
            $totalDeleted += $c;
        }

        // 2. 删除 message 内容包含插件特征关键词的记录（用 LIKE，SQLite 兼容）
        $junkPatterns = [
            'Successfully authenticated player%',
            'Successfully validated connection of player%',
            '%authenticated player%',
            '%validated connection of player%',
        ];
        foreach ($junkPatterns as $p) {
            $c = GameChatMessage::where('message', 'like', $p)->delete();
            $totalDeleted += $c;
        }

        // 3. 用 PHP 端遍历删除 UUID 风格的 player_name 和 message 含长 hash 的记录
        // SQLite 的 LIKE 不好匹配 UUID 模式，所以拉出来 PHP 判断
        $all = GameChatMessage::select(['id', 'player_name', 'message'])->get();
        $uuidPattern = '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i';
        $hashPattern = '/[0-9a-f]{32,}/i';
        $toDelete = [];
        foreach ($all as $m) {
            // player_name 像 UUID
            if (preg_match($uuidPattern, (string) $m->player_name)) {
                $toDelete[] = $m->id;
                continue;
            }
            // message 里包含 32 位以上连续十六进制（UUID 或 hash）
            if (preg_match($hashPattern, (string) $m->message)) {
                $toDelete[] = $m->id;
            }
        }
        if (! empty($toDelete)) {
            // 分批删除，避免 IN 子句太长
            foreach (array_chunk($toDelete, 500) as $chunk) {
                $totalDeleted += GameChatMessage::whereIn('id', $chunk)->delete();
            }
        }

        if ($totalDeleted === 0) {
            $this->info('  没有发现需要清理的脏数据');
        } else {
            $this->info("清理完成，共删除 {$totalDeleted} 条错误数据");
        }
    }

    private function runOnce(MinecraftLogSyncService $service): int
    {
        $result = $service->sync();

        if (! $result['ok']) {
            $this->error('同步失败：' . ($result['message'] ?? '未知错误'));
            $this->line('');
            $this->line('排查建议：');
            $this->line('  1. 检查 .env 里的 MC_SERVER_PATH 是否指向 MC 服务器根目录');
            $this->line('  2. 确认该目录下存在 logs/latest.log 文件');
            $this->line('  3. 确认 Web 用户对该日志文件有读取权限');
            return self::FAILURE;
        }

        $this->info('同步成功 ✓');
        $this->line('  日志文件：' . ($result['log_path'] ?? '-'));
        $this->line('  文件大小：' . number_format($result['file_size'] ?? 0) . ' bytes');
        $this->line('  本次读取：' . number_format(($result['new_pos'] ?? 0) - ($result['last_pos'] ?? 0)) . ' bytes');
        $this->line('  解析行数：' . ($result['parsed'] ?? 0));
        $this->line('  新增消息：' . ($result['inserted'] ?? 0));

        // 显示被跳过的日志样本（调试用）
        $skipped = $result['skipped_sample'] ?? [];
        if (! empty($skipped) && $this->getOutput()->isVerbose()) {
            $this->line('');
            $this->line('  跳过的日志样本（最后 20 条）：');
            foreach ($skipped as $s) {
                $this->line('    ' . $s);
            }
        }

        return self::SUCCESS;
    }
}
