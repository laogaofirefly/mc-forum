<?php

namespace App\Http\Controllers;

use App\Models\ServerStatus;
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
            return response()->json([
                'ok' => true,
                'online' => $status?->is_online ?? false,
                'players_online' => $status?->players_online ?? 0,
                'players_max' => $status?->players_max ?? 0,
                'motd' => $status?->motd,
                'version' => $status?->version,
                'players_json' => $status?->players_json ?? [],
                'updated_at' => $status?->updated_at?->diffForHumans(),
                'updated_at_ts' => $status?->updated_at?->timestamp,
            ]);
        }

        // 兼容旧路由：返回服务器状态视图（首页通过 AppServiceProvider 直接注入即可）
        return response()->json(['ok' => true, 'data' => $status]);
    }
}
