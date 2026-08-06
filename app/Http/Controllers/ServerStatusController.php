<?php

namespace App\Http\Controllers;

use App\Models\ServerStatus;
use App\Services\PlayerAvatarService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ServerStatusController extends Controller
{
    public function index(Request $request)
    {
        $host = $request->input('host', config('services.minecraft.host', 'localhost'));
        $port = (int) $request->input('port', config('services.minecraft.port', 25565));
        $force = (bool) $request->input('force', false);

        if ($force) {
            // 强制刷新：把缓存时间改到过期，下次查询会重新拉取
            $existing = ServerStatus::where('host', $host)->where('port', $port)->first();
            if ($existing) {
                $existing->updated_at = now()->subMinutes(10);
                $existing->save();
            }
        }

        $status = ServerStatus::getStatus($host, $port);

        if ($request->wantsJson() || $request->input('json') || $request->expectsJson()) {
            // 给每个在线玩家补上头像 URL
            $players = $this->enrichPlayersWithAvatar($status?->players_json ?? []);

            return response()->json([
                'ok' => true,
                'online' => $status?->is_online ?? false,
                'players_online' => $status?->players_online ?? 0,
                'players_max' => $status?->players_max ?? 0,
                'motd' => $status?->motd,
                'version' => $status?->version,
                'players_json' => $players,
                'updated_at' => $status?->updated_at?->diffForHumans(),
                'updated_at_ts' => $status?->updated_at?->timestamp,
            ]);
        }

        // 兼容旧路由：返回服务器状态视图（首页通过 AppServiceProvider 直接注入即可）
        return response()->json(['ok' => true, 'data' => $status]);
    }

    /**
     * 给在线玩家列表补上头像字段
     * 优先级：绑定的网站账号头像 > MC 皮肤(crafatar) > 名字首字母
     */
    private function enrichPlayersWithAvatar(array $players): array
    {
        return array_map(function ($p) {
            if (! is_array($p)) {
                return $p;
            }
            $name = isset($p['name']) ? preg_replace('/§./', '', $p['name']) : '';
            $uuid = $p['id'] ?? ($p['uuid'] ?? '');
            $p['avatar'] = PlayerAvatarService::url($name, $uuid);
            $p['avatar_fallback'] = PlayerAvatarService::initialAvatar($name);
            return $p;
        }, $players);
    }
}
