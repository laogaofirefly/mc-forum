<?php

namespace App\Services;

/**
 * Minecraft 服务器成员服务
 *
 * 读取 MC 服务器根目录下的 usercache.json（记录所有曾登录过的玩家）
 * 以及 whitelist.json（白名单玩家），合并后返回所有成员名单。
 *
 * usercache.json 格式：
 *   [{"name":"Notch","uuid":"069a79f4-444e-9472-6a5b-efca90e38aaf5","expiresOn":"..."}, ...]
 *
 * whitelist.json 格式：
 *   [{"uuid":"069a79f444e947266a5befca90e38aaf5","name":"Notch"}, ...]
 *
 * 注意：两个文件里的 UUID 格式可能不一致（一个带横线、一个不带），
 *      合并前必须先规范化（去掉横线 + 转小写），否则同一玩家会被当成两个人。
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

        // 读取两个文件，分别得到 规范化uuid => 玩家 的映射
        $usercache = $this->loadFromUsercache($basePath);
        $whitelist = $this->loadFromWhitelist($basePath);

        // 合并：以规范化 UUID 为键去重，usercache 优先（它有过期时间等更完整的信息）
        $players = $usercache;
        foreach ($whitelist as $uuid => $player) {
            if (! isset($players[$uuid])) {
                $players[$uuid] = $player;
            }
        }

        if (empty($players)) {
            return [
                'ok' => false,
                'message' => '未找到任何玩家数据（usercache.json 和 whitelist.json 均为空或不存在）',
                'players' => [],
                'total' => 0,
            ];
        }

        // 再按名字（小写）做一次去重，防止同一玩家因 UUID 不同而重复
        // （比如离线模式同一名字可能对应不同 UUID，或 usercache 里有历史残留）
        $byName = [];
        foreach ($players as $player) {
            $key = strtolower($player['name']);
            if (! isset($byName[$key])) {
                $byName[$key] = $player;
            }
        }
        $players = array_values($byName);

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
     *
     * @return array<string, array>  规范化uuid => 玩家
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
            $normalizedUuid = $this->normalizeUuid((string) $uuid);
            $players[$normalizedUuid] = [
                'name' => (string) $name,
                'uuid' => $normalizedUuid,
                'expires_on' => $entry['expiresOn'] ?? null,
            ];
        }

        return $players;
    }

    /**
     * 从 whitelist.json 读取玩家
     *
     * @return array<string, array>  规范化uuid => 玩家
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
            $normalizedUuid = $this->normalizeUuid((string) $uuid);
            $players[$normalizedUuid] = [
                'name' => (string) $name,
                'uuid' => $normalizedUuid,
                'expires_on' => null,
            ];
        }

        return $players;
    }

    /**
     * 规范化 UUID：去掉横线并转小写
     *
     * 例如：
     *   "069a79f4-444e-9472-6a5b-efca90e38aaf5" → "069a79f444e947266a5befca90e38aaf5"
     *   "069A79F444E947266A5BEFCA90E38AAF5"      → "069a79f444e947266a5befca90e38aaf5"
     */
    private function normalizeUuid(string $uuid): string
    {
        return strtolower(str_replace('-', '', $uuid));
    }
}
