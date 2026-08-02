<?php

namespace App\Services;

use App\Models\User;

/**
 * 玩家头像服务
 *
 * 统一头像获取逻辑，优先级：
 *   1. 绑定了网站账号且该账号上传了自定义头像 → 使用网站账号头像
 *   2. 兜底 → 用名字首字母生成 SVG 头像
 *
 * 注意：不再使用 crafatar 的 MC 皮肤头像，未上传自定义头像一律用首字母。
 * 已绑定的网站账号映射会在单次请求内缓存，避免重复查询。
 */
class PlayerAvatarService
{
    /** @var array<string, User>|null  小写 mc_username => User */
    private static ?array $boundMap = null;

    /** 首字母头像配色（与 Tailwind 调性一致） */
    private const COLORS = [
        '#ef4444', '#f97316', '#f59e0b', '#eab308',
        '#84cc16', '#22c55e', '#10b981', '#14b8a6',
        '#06b6d4', '#0ea5e9', '#3b82f6', '#6366f1',
        '#8b5cf6', '#a855f7', '#d946ef', '#ec4899',
    ];

    /**
     * 获取玩家头像 URL
     */
    public static function url(?string $name, ?string $uuid = null): string
    {
        $name = trim((string) $name);

        // 1. 绑定了网站账号且上传了自定义头像 → 使用网站账号头像
        $bound = self::boundUser($name);
        if ($bound && $bound->avatar) {
            return $bound->getAvatarUrl();
        }

        // 2. 兜底：名字首字母
        return self::initialAvatar($name);
    }

    /**
     * 根据玩家名查找已绑定的网站账号（大小写不敏感）
     */
    public static function boundUser(?string $name): ?User
    {
        $name = trim((string) $name);
        if ($name === '') {
            return null;
        }
        return self::boundMap()[strtolower($name)] ?? null;
    }

    /**
     * 生成名字首字母头像（SVG data URI）
     */
    public static function initialAvatar(string $name): string
    {
        $name = trim($name);
        $letter = mb_strtoupper(mb_substr($name, 0, 1)) ?: '?';
        $color = self::colorFor($name);

        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" viewBox="0 0 80 80">'
            . '<rect width="80" height="80" fill="' . $color . '"/>'
            . '<text x="40" y="40" dy=".35em" text-anchor="middle" dominant-baseline="middle"'
            . ' font-family="Inter,system-ui,sans-serif" font-size="38" font-weight="700" fill="#ffffff">'
            . htmlspecialchars($letter, ENT_XML1 | ENT_QUOTES, 'UTF-8')
            . '</text></svg>';

        return 'data:image/svg+xml,' . rawurlencode($svg);
    }

    /**
     * 根据名字生成稳定的颜色（相同名字始终得到相同颜色）
     */
    public static function colorFor(string $name): string
    {
        $name = trim($name);
        if ($name === '') {
            return self::COLORS[0];
        }
        $sum = 0;
        $len = mb_strlen($name);
        for ($i = 0; $i < $len; $i++) {
            $sum += mb_ord(mb_substr($name, $i, 1), 'UTF-8');
        }
        return self::COLORS[$sum % count(self::COLORS)];
    }

    /**
     * 加载已绑定 MC 用户名的网站账号映射（单次请求内缓存）
     *
     * @return array<string, User>
     */
    private static function boundMap(): array
    {
        if (self::$boundMap === null) {
            self::$boundMap = [];
            $users = User::whereNotNull('mc_username')
                ->where('mc_username', '!=', '')
                ->get(['id', 'name', 'mc_username', 'avatar', 'mc_uuid', 'updated_at']);
            foreach ($users as $u) {
                self::$boundMap[strtolower((string) $u->mc_username)] = $u;
            }
        }
        return self::$boundMap;
    }
}
