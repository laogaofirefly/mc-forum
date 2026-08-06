<?php

namespace App\Http\Controllers;

use App\Models\GameChatMessage;
use App\Services\MinecraftRconService;
use App\Services\MinecraftLogSyncService;
use App\Services\PlayerAvatarService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class GameChatController extends Controller
{
    public function index(Request $request): View
    {
        $messages = GameChatMessage::getLatest(150);

        return view('game-chat.index', compact('messages'));
    }

    public function fetch(Request $request, MinecraftLogSyncService $syncService): JsonResponse
    {
        // 页面轮询时增量同步日志，避免依赖常驻的 chat:sync --watch 进程。
        $lockKey = 'mc_chat_log_sync_lock';
        // 两秒节流：避免每个浏览器轮询都重复打开和扫描同一份日志。
        if (Cache::add($lockKey, 1, now()->addSeconds(2))) {
            try {
                $syncService->sync();
            } catch (\Throwable $e) {
                Log::warning('游戏聊天日志同步失败', ['error' => $e->getMessage()]);
            }
        }

        $afterId = $request->integer('after_id', 0);
        $limit = min(200, max(10, $request->integer('limit', 100)));

        $messages = GameChatMessage::getLatest($limit, $afterId ?: null);

        return response()->json([
            'ok' => true,
            'current_user_name' => auth()->user()?->name ?? '',
            'count' => $messages->count(),
            'last_id' => $messages->last()?->id ?? $afterId,
            'time' => now()->toDateTimeString(),
            'messages' => $messages->map(function ($m) {
                return [
                    'id' => $m->id,
                    'player_name' => $m->player_name,
                    'player_uuid' => $m->player_uuid,
                    'avatar_url' => PlayerAvatarService::url($m->player_name, $m->player_uuid),
                    'avatar_fallback' => PlayerAvatarService::initialAvatar($m->player_name),
                    'message' => $m->message,
                    'channel' => $m->channel,
                    'timestamp' => $m->timestamp?->format('Y-m-d H:i:s') ?? now()->format('Y-m-d H:i:s'),
                ];
            }),
        ])->header('Cache-Control', 'no-store, no-cache, must-revalidate');
    }

    /**
     * 从网站向游戏发消息（通过 RCON 调用 tellraw 命令，伪装成普通玩家聊天）
     */
    public function send(Request $request): JsonResponse
    {
        if (! Auth::check()) {
            return response()->json(['ok' => false, 'message' => '请先登录'], 401);
        }
        $request->validate([
            'message' => 'required|string|min:1|max:200',
        ], [
            'message.required' => '消息内容不能为空',
            'message.max' => '消息最多 200 字',
        ]);
        $message = trim($request->input('message'));
        $user = Auth::user();
        $playerName = $user->name;

        // 检查 RCON 配置
        $rconPassword = config('services.minecraft.rcon.password', '');
        $rconHost = config('services.minecraft.rcon.host', '127.0.0.1');
        $rconPort = (int) config('services.minecraft.rcon.port', 25575);
        if (empty($rconPassword)) {
            return response()->json([
                'ok' => false,
                'message' => 'RCON 未配置：请在 .env 里设置 MC_RCON_PASSWORD（参考 MC 服务器 server.properties 里的 rcon.password）',
            ], 500);
        }

        // 先把消息存进数据库（即使 RCON 失败，网页端也能看到）
        $saved = GameChatMessage::create([
            'player_name' => $playerName,
            'message' => $message,
            'channel' => 'web',
            'timestamp' => now(),
        ]);

        // 通过 RCON 发送 tellraw 命令，伪装成普通玩家聊天
        try {
            $rcon = new MinecraftRconService($rconHost, $rconPort, $rconPassword, 3);
            $rcon->connect();

            $raw = json_encode([
                ['text' => '<', 'color' => 'gray'],
                ['text' => $playerName, 'color' => 'gold'],
                ['text' => '> ', 'color' => 'gray'],
                ['text' => $message, 'color' => 'white'],
            ], JSON_UNESCAPED_UNICODE);

            $command = 'tellraw @a ' . $raw;
            $rcon->sendCommand($command);
            $rcon->disconnect();
        } catch (\Throwable $e) {
            // RCON 失败不影响网页端显示
            return response()->json([
                'ok' => true,
                'message' => '消息已保存，但发送到游戏失败：' . $e->getMessage(),
                'record' => [
                    'id' => $saved->id,
                    'player_name' => $saved->player_name,
                    'avatar_url' => $user->getAvatarUrl(),
                    'avatar_fallback' => PlayerAvatarService::initialAvatar($saved->player_name),
                    'message' => $saved->message,
                    'timestamp' => $saved->timestamp->format('Y-m-d H:i:s'),
                ],
                'rcon_error' => $e->getMessage(),
            ]);
        }

        return response()->json([
            'ok' => true,
            'message' => '已发送到游戏',
            'record' => [
                'id' => $saved->id,
                'player_name' => $saved->player_name,
                'avatar_url' => $user->getAvatarUrl(),
                'avatar_fallback' => PlayerAvatarService::initialAvatar($saved->player_name),
                'message' => $saved->message,
                'timestamp' => $saved->timestamp->format('Y-m-d H:i:s'),
            ],
        ]);
    }

    public function demo(Request $request): JsonResponse
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403);
        }

        $names = ['Steve', 'Alex', 'Notch', 'Dinnerbone', 'C418', '牢高', 'Xiaoming', 'Player_' . rand(100, 999)];
        $samples = [
            '有人一起下矿吗？钻石镐快坏了',
            '有多余的钻石吗？交易！',
            '基地附近有没有苦力怕？刚才听到嘶嘶声',
            '末影龙有人组队打吗？需要药水',
            '有谁会做刷铁机？求教学',
            '出生点南边新开了交易市场，欢迎来参观',
            '我的猫失踪了，有人在村庄附近看到吗？',
            '20组泥土换一组红石，有人换吗？',
            '服务器卡吗？我这边突然延迟高了',
            '今天建造了新的城堡，坐标 (-120, 780) 欢迎来看看',
        ];

        $player = $names[array_rand($names)];
        $msg = $samples[array_rand($samples)];

        $created = GameChatMessage::addMessage($player, $msg);

        return response()->json([
            'ok' => true,
            'message' => [
                'id' => $created->id,
                'player_name' => $created->player_name,
                'avatar_url' => PlayerAvatarService::url($created->player_name),
                'avatar_fallback' => PlayerAvatarService::initialAvatar($created->player_name),
                'message' => $created->message,
                'timestamp' => $created->timestamp->format('Y-m-d H:i:s'),
            ],
        ]);
    }
}