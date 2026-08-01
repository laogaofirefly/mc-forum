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

    /**
     * 执行一次同步，返回新增消息数等信息
     */
    public function sync(): array
    {
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

        try {
            while (($line = fgets($handle, 4096)) !== false) {
                $newPos = ftell($handle);
                $parsed++;

                $msg = $this->parseLine($line);
                if ($msg === null) {
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

        return [
            'ok' => true,
            'log_path' => $logPath,
            'file_size' => $currentSize,
            'last_pos' => $lastPos,
            'new_pos' => $newPos,
            'parsed' => $parsed,
            'inserted' => $inserted,
            'message' => "解析 {$parsed} 行，新增 {$inserted} 条聊天消息",
        ];
    }

    /**
     * 解析一行日志，返回 [player, message] 或 null
     */
    public function parseLine(string $line): ?array
    {
        $line = rtrim($line, "\r\n");

        // 匹配日志头：[12:34:56] [.../INFO]: 后面是正文
        // 注意第二段方括号里可能嵌套出现 "Async Chat Thread - #0/INFO"
        // 导致日志格式变成：[Server thread/INFO]: [Async Chat Thread - #0/INFO]: <玩家> 消息
        // 所以要匹配到最后一个 /INFO]: 才是真正的聊天正文起点
        if (! preg_match('/^\[(\d{2}:\d{2}:\d{2})\]\s+\[[^\]]*\/INFO\]:\s*(?:\[[^\]]*\/INFO\]:\s*)?(.+)$/u', $line, $m)) {
            return null;
        }

        $time = $m[1];
        $body = $m[2];

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
        if (preg_match('/^\[([\x{4e00}-\x{9fa5}A-Za-z0-9_]{1,16})\]\s*(.+)$/u', $body, $bm)) {
            // 过滤掉明显的系统消息前缀
            $lower = strtolower($bm[1]);
            if (! in_array($lower, ['async', 'server', 'system', 'chat'], true)) {
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
