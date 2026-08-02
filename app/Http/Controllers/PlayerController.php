<?php

namespace App\Http\Controllers;

use App\Models\ServerStatus;
use App\Services\MinecraftPlayerService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PlayerController extends Controller
{
    public function index(Request $request, MinecraftPlayerService $playerService): View
    {
        $result = $playerService->getAllPlayers();

        // 获取当前在线玩家列表（来自 server_status.players_json）
        $onlineNames = [];
        $host = config('services.minecraft.host', 'localhost');
        $port = (int) config('services.minecraft.port', 25565);
        $status = ServerStatus::where('host', $host)->where('port', $port)->first();
        if ($status && $status->players_json) {
            foreach ($status->players_json as $p) {
                if (! empty($p['name'])) {
                    $onlineNames[$p['name']] = true;
                }
            }
        }

        // 标记每个玩家是否在线，并补带头像 URL
        $players = [];
        foreach ($result['players'] as $player) {
            $players[] = [
                'name' => $player['name'],
                'uuid' => $player['uuid'],
                'avatar' => $playerService->getAvatarUrl($player['uuid']),
                'online' => isset($onlineNames[$player['name']]),
            ];
        }

        // 在线玩家数（来自 server_status 实时查询）
        $onlineCount = $status?->players_online ?? 0;
        $maxPlayers = $status?->players_max ?? 0;

        // 搜索过滤
        $keyword = trim((string) $request->input('q', ''));
        if ($keyword !== '') {
            $players = array_values(array_filter($players, function ($p) use ($keyword) {
                return stripos($p['name'], $keyword) !== false;
            }));
        }

        return view('players.index', [
            'players' => $players,
            'total' => $result['total'],
            'onlineCount' => $onlineCount,
            'maxPlayers' => $maxPlayers,
            'ok' => $result['ok'],
            'message' => $result['message'],
            'keyword' => $keyword,
        ]);
    }
}
