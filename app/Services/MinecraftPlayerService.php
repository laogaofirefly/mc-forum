<?php

namespace App\Services;

/**
 * Minecraft 服务器成员服务
 *
 * 读取 MC 服务器根目录下的 usercache.json（记录所有曾登录过的玩家）
 * 以及 whitelist.json（白名单玩家），合并后返回所有成员名单。
 *
 * usercache.json 格式：
 *   [{"name":"Notch","uuid":"xxx","expiresOn":"2024-01-01 00:00:00 +0000"}, ...]
 *
 * whitelist.json 格式：
 *   [{"uuid":"xxx","name":"Notch"}, ...]
 */
class MinecraftPlayerService
{
    /** usercache.json 相对于 MC 服务器根目录的路径 */
    private const USERCACHE_FILE = 'usercache.json';

    /** whitelist.json 相对于 MC 服务器根目录的路径 */
    private const WHITELIST_FILE = 'whitelist.json';

    /**
     * 获取所有曾登录过 MC 服务器的玩家
     *
     * @return array{ok: bool, message: string, players: array, total: int}
     */
    public function getAllPlayers(): array
    {
        $basePath = rtrim((string) config('services.minecraft.log_path', env('MC_SERVER_PATH')), '\\/');

        if ($basePath === '') {
            return [
                'ok' => false,
                'message' => '未配置 MC_SERVER_PATH，无法读取服务器玩家数据',
                'players' => [],
                'total' => 0,
            ];
        }

        $players = $this->loadFromUsercache($basePath);

        // 如果同时存在白名单，合并白名单（防止 usercache 过期被清理）
        $whitelist = $this->loadFromWhitelist($basePath);
        if (! empty($whitelist)) {
            $players = $this->mergePlayers($players, $whitelist);
        }

        if (empty($players)) {
            return [
                'ok' => false,
                'message' => '未找到任何玩家数据（usercache.json 和 whitelist.json 均为空或不存在）',
                'players' => [],
                'total' => 0,
            ];
        }

        // 按玩家名排序
        usort($players, function ($a, $b) {
            return strcasecmp($a['name'], $b['name']);
        });

        return [
            'ok' => true,
            'message' => '成功读取 ' . count($players) . ' 名成员',
            'players' => $players,
            'total' => count($players),
        ];
    }

    /**
     * 从 usercache.json 读取玩家
     */
    private function loadFromUsercache(string $basePath): array
    {
        $path = $basePath . DIRECTORY_SEPARATOR . self::USERCACHE_FILE;

        if (! is_file($path) || ! is_readable($path)) {
            return [];
        }

        $content = @file_get_contents($path);
        if ($content === false) {
            return [];
        }

        $data = json_decode($content, true);
        if (! is_array($data)) {
            return [];
        }

        $players = [];
        foreach ($data as $entry) {
            if (! is_array($entry)) {
                continue;
            }
            $name = $entry['name'] ?? null;
            $uuid = $entry['uuid'] ?? null;
            if (empty($name) || empty($uuid)) {
                continue;
            }
            $players[$uuid] = [
                'name' => (string) $name,
                'uuid' => (string) $uuid,
                'expires_on' => $entry['expiresOn'] ?? null,
            ];
        }

        return $players;
    }

    /**
     * 从 whitelist.json 读取玩家
     */
    private function loadFromWhitelist(string $basePath): array
    {
        $path = $basePath . DIRECTORY_SEPARATOR . self::WHITELIST_FILE;

        if (! is_file($path) || ! is_readable($path)) {
            return [];
        }

        $content = @file_get_contents($path);
        if ($content === false) {
            return [];
        }

        $data = json_decode($content, true);
        if (! is_array($data)) {
            return [];
        }

        $players = [];
        foreach ($data as $entry) {
            if (! is_array($entry)) {
                continue;
            }
            $name = $entry['name'] ?? null;
            $uuid = $entry['uuid'] ?? null;
            if (empty($name) || empty($uuid)) {
                continue;
            }
            $players[$uuid] = [
                'name' => (string) $name,
                'uuid' => (string) $uuid,
                'expires_on' => null,
            ];
        }

        return $players;
    }

    /**
     * 合并两个玩家列表（以 uuid 为键去重）
     */
    private function mergePlayers(array $a, array $b): array
    {
        foreach ($b as $uuid => $player) {
            if (! isset($a[$uuid])) {
                $a[$uuid] = $player;
            }
        }
        return $a;
    }

    /**
     * 获取玩家头像 URL（使用 crafitar 服务，根据 UUID）
     */
    public function getAvatarUrl(string $uuid): string
    {
        // 去掉 UUID 中的横线，crafatar 两种格式都支持，这里统一用带横线格式
        return 'https://crafatar.com/avatars/' . $uuid . '?size=80&default=MHF_Steve';
    }
}
