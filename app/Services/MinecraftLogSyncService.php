<?php

namespace App\Services;

use App\Models\GameChatMessage;
use Illuminate\Support\Facades\Log;

/**
 * Minecraft 服务器日志同步服务
 *
 * 读取 MC 服务器 logs/latest.log 文件，解析出玩家聊天消息并入库。
 * 支持原版 / Paper / Spigot 服务端的日志格式。
 *
 * 典型日志行格式：
 *   [12:34:56] [Server thread/INFO]: <Notch> hello world
 *   [12:34:56] [Server thread/INFO]: [Notch] hello world   (某些插件)
 *   [12:34:56] [Server thread/INFO]: [Async Chat Thread - #0/INFO]: <Notch> hello world
 */
class MinecraftLogSyncService
{
    /** 日志文件相对于 MC 服务器根目录的路径 */
    private const LOG_RELATIVE_PATH = 'logs' . DIRECTORY_SEPARATOR . 'latest.log';

    /** 用来记录上次同步读到的字节位置，避免重复入库 */
    private const POSITION_CACHE_KEY = 'mc_chat_log_pos';

    /** 单次最多解析多少条（防止日志巨大时卡死） */
    private int $maxBatch = 500;

    /** 最近一次解析时跳过的行（调试用） */
    private array $lastSkippedLines = [];

    /**
     * 执行一次同步，返回新增消息数等信息
     */
    public function sync(): array
    {
        $this->lastSkippedLines = [];
        $logPath = $this->getLogPath();

        if (! $logPath || ! file_exists($logPath)) {
            return [
                'ok' => false,
                'reason' => 'log_not_found',
                'message' => '找不到 MC 服务器日志文件：' . ($logPath ?: '(未配置路径)'),
                'inserted' => 0,
                'parsed' => 0,
            ];
        }

        if (! is_readable($logPath)) {
            return [
                'ok' => false,
                'reason' => 'log_not_readable',
                'message' => '日志文件不可读：' . $logPath . '（请检查 Web 用户对该文件的读取权限）',
                'inserted' => 0,
                'parsed' => 0,
            ];
        }

        $currentSize = filesize($logPath);
        $lastPos = $this->getLastPosition();

        // 如果文件变小了（日志轮转 / 服务器重启重写了 latest.log），从头开始读
        if ($currentSize === false || $lastPos > $currentSize) {
            $lastPos = 0;
        }

        // 第一次运行或者上次位置为 0，为了避免把整个历史日志全灌进来，
        // 默认只读最后 64KB
        if ($lastPos === 0 && $currentSize > 65536) {
            $lastPos = $currentSize - 65536;
        }

        $handle = @fopen($logPath, 'rb');
        if ($handle === false) {
            return [
                'ok' => false,
                'reason' => 'open_failed',
                'message' => '无法打开日志文件：' . $logPath,
                'inserted' => 0,
                'parsed' => 0,
            ];
        }

        fseek($handle, $lastPos);

        $parsed = 0;
        $inserted = 0;
        $newPos = $lastPos;
        $batchCount = 0;
        $skipped = [];

        try {
            while (($line = fgets($handle, 4096)) !== false) {
                $newPos = ftell($handle);
                $parsed++;

                $msg = $this->parseLine($line);
                if ($msg === null) {
                    // 保留最近 20 条被跳过的行，方便调试
                    $skipped[] = rtrim($line, "\r\n");
                    if (count($skipped) > 20) {
                        array_shift($skipped);
                    }
                    continue;
                }

                // 用 (玩家名+消息+时间戳前缀) 作为去重依据，避免重复入库
                if ($this->alreadyExists($msg)) {
                    continue;
                }

                GameChatMessage::addMessage(
                    $msg['player'],
                    $msg['message'],
                    null, // 离线模式无 UUID
                    'global'
                );
                $inserted++;

                if (++$batchCount >= $this->maxBatch) {
                    break;
                }
            }
        } finally {
            fclose($handle);
        }

        // 保存本次读取到的位置
        $this->savePosition($newPos);
        $this->lastSkippedLines = $skipped;

        return [
            'ok' => true,
            'log_path' => $logPath,
            'file_size' => $currentSize,
            'last_pos' => $lastPos,
            'new_pos' => $newPos,
            'parsed' => $parsed,
            'inserted' => $inserted,
            'skipped_sample' => $skipped,
            'message' => "解析 {$parsed} 行，新增 {$inserted} 条聊天消息",
        ];
    }

    /**
     * 获取最近一次同步时被跳过的日志行（调试用）
     */
    public function getLastSkippedLines(): array
    {
        return $this->lastSkippedLines;
    }

    /**
     * 解析一行日志，返回 [player, message] 或 null
     */
    public function parseLine(string $line): ?array
    {
        $line = rtrim($line, "\r\n");

        // 匹配日志头：[12:34:56] [.../INFO]: 后面是正文
        // 第二段方括号可能是 "Server thread" / "Async Chat Thread - #0" / "VoiceChatPacketProcessingThread" 等
        if (! preg_match('/^\[(\d{2}:\d{2}:\d{2})\]\s+\[[^\]]*\/INFO\]:\s*(.+)$/u', $line, $m)) {
            return null;
        }

        $time = $m[1];
        $body = $m[2];

        // 关键过滤：只接受来自 "Server thread" 或 "Async Chat Thread" 的消息
        // 其他线程（如 VoiceChatPacketProcessingThread / Netty Server / ...）的消息一律跳过
        // 通过日志头判断（重新匹配一次，单独提取线程名）
        if (! preg_match('/^\[\d{2}:\d{2}:\d{2}\]\s+\[([^\]]+)\/INFO\]:/', $line, $tm)) {
            return null;
        }
        $thread = $tm[1];
        $isChatThread = preg_match('/^(Server thread|Async Chat Thread\b)/', $thread);
        if (! $isChatThread) {
            return null;
        }

        // 跳过常见的非聊天 INFO 消息（玩家进出服、命令反馈等）
        // 这些都是 Server thread 输出的，但不是聊天
        if (preg_match('/\b(joined the game|left the game|lost connection|has earned the achievement|made the advancement|completed challenge|logged in with|moved too quickly|was slain by|drowned|fell from|blew up|withered away|died|hurt by|killed by|fell out of the world|walked into|hit the ground|burned to|tried to swim in)\b/i', $body)) {
            return null;
        }

        // 可选前缀：[Not Secure] 或 [Secure] —— 1.19.3+ 聊天签名验证标记
        // 格式举例：[Not Secure] <玩家> 消息
        // 注意：可能有多个前缀叠加，循环去除直到没有为止
        while (preg_match('/^\[(Not Secure|Secure|Filtered|Preview|System|Chat Type|Sender)\]\s*/u', $body)) {
            $body = preg_replace('/^\[(Not Secure|Secure|Filtered|Preview|System|Chat Type|Sender)\]\s*/u', '', $body);
        }

        // 匹配 <玩家名> 消息  —— 原版聊天格式（支持中英文数字下划线）
        if (preg_match('/^<([^>]{1,64})>\s*(.+)$/u', $body, $bm)) {
            return [
                'time' => $time,
                'player' => $bm[1],
                'message' => $bm[2],
            ];
        }

        // 匹配 [玩家名] 消息  —— 某些插件 / /me 命令
        // 玩家名允许中文、英文、数字、下划线，长度 1-16
        // 但要排除 [插件名] 开头的消息（如 [voicechat] / [Essentials] / [ LuckPerms] 等）
        if (preg_match('/^\[([\x{4e00}-\x{9fa5}A-Za-z0-9_]{1,16})\]\s*(.+)$/u', $body, $bm)) {
            $lower = strtolower($bm[1]);
            // 已知的插件前缀黑名单（持续可扩展）
            $pluginBlacklist = [
                'async', 'server', 'system', 'chat',
                'voicechat', 'essentials', 'luckperms', 'vault',
                'worldedit', 'worldguard', 'placeholderapi', 'papi',
                'multiverse', 'multiverse-core', 'multiverse-netherportals',
                'protocollib', 'viaversion', ' ProtocolLib',
                'dynmap', 'bluemap', 'pl3xmap',
                'discordsrv', 'simple-voice-chat', 'plasmo-voice',
                'skript', 'mythicmobs', 'citizens', 'mythiclib',
                'mmocore', 'mmoitems', 'towny', 'factions',
                'coreprotect', 'logblock', 'hawk-eye',
                'permissionsex', 'groupmanager', 'bPermissions',
                'chestshop', 'quickshop', 'shop',
                'mcmmo', 'aurelium', 'auraskills',
                'event', 'config', 'console',
            ];
            if (! in_array($lower, $pluginBlacklist, true)) {
                return [
                    'time' => $time,
                    'player' => $bm[1],
                    'message' => $bm[2],
                ];
            }
        }

        return null;
    }

    /**
     * 简单的去重：查最近 5 分钟内是否有完全相同的消息
     */
    private function alreadyExists(array $msg): bool
    {
        return GameChatMessage::where('player_name', $msg['player'])
            ->where('message', $msg['message'])
            ->where('timestamp', '>=', now()->subMinutes(5))
            ->exists();
    }

    private function getLogPath(): ?string
    {
        $base = rtrim((string) config('services.minecraft.log_path', env('MC_SERVER_PATH')), '\\/');
        if ($base === '') {
            return null;
        }
        return $base . DIRECTORY_SEPARATOR . self::LOG_RELATIVE_PATH;
    }

    private function getLastPosition(): int
    {
        return (int) cache(self::POSITION_CACHE_KEY, 0);
    }

    private function savePosition(int $pos): void
    {
        cache([self::POSITION_CACHE_KEY => $pos], now()->addDays(7));
    }

    public function setMaxBatch(int $n): self
    {
        $this->maxBatch = max(1, $n);
        return $this;
    }

    /**
     * 重置读取位置（下次会从头读最后 64KB）
     */
    public function resetPosition(): void
    {
        cache()->forget(self::POSITION_CACHE_KEY);
    }
}
