<?php

namespace App\Console\Commands;

use App\Services\MinecraftLogSyncService;
use Illuminate\Console\Command;

class SyncGameChat extends Command
{
    protected $signature = 'chat:sync
                            {--reset : 重置读取位置，下次从头读最后 64KB}
                            {--watch : 持续监听模式，每 10 秒同步一次（按 Ctrl+C 退出）}';

    protected $description = '从 MC 服务器 logs/latest.log 同步玩家聊天记录到数据库';

    public function handle(MinecraftLogSyncService $service): int
    {
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

        return self::SUCCESS;
    }
}
