<?php

namespace App\Http\Controllers;

use App\Models\ServerStatus;
use App\Services\MinecraftPlayerService;
use App\Services\PlayerAvatarService;
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

        // 标记每个玩家是否在线、是否绑定网站账号，并补带头像 URL
        // 头像优先级由 PlayerAvatarService 统一处理：绑定账号 > MC 皮肤 > 名字首字母
        $players = [];
        foreach ($result['players'] as $player) {
            $boundUser = PlayerAvatarService::boundUser($player['name']);
            $isBound = $boundUser !== null;

            $players[] = [
                'name' => $player['name'],
                'uuid' => $player['uuid'],
                'avatar' => PlayerAvatarService::url($player['name'], $player['uuid']),
                'online' => isset($onlineNames[$player['name']]),
                'bound' => $isBound,
                'bound_user_id' => $isBound ? $boundUser->id : null,
                'bound_user_name' => $isBound ? $boundUser->name : null,
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
