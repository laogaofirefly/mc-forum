<?php

namespace App\Http\Controllers;

use App\Models\GameChatMessage;
use App\Services\MinecraftRconService;
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

    public function fetch(Request $request): JsonResponse
    {
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
                $user = \App\Models\User::where('name', $m->player_name)->first();
                $avatarUrl = $user ? $user->getAvatarUrl() : \App\Services\PlayerAvatarService::initialAvatar($m->player_name);
                return [
                    'id' => $m->id,
                    'player_name' => $m->player_name,
                    'player_uuid' => $m->player_uuid,
                    'avatar_url' => $avatarUrl,
                    'message' => $m->message,
                    'channel' => $m->channel,
                    'timestamp' => $m->timestamp?->format('Y-m-d H:i:s') ?? now()->format('Y-m-d H:i:s'),
                ];
            }),
        ]);
    }

    /**
     * 从网站向游戏发消息（通过 RCON 调用 say 命令）
     *
     * 使用 say 命令而非 tellraw，因为：
     * 1. say 命令的消息会写入服务器日志，日志同步服务可以正常拉取
     * 2. say 命令在游戏内所有玩家都能看到
     * 3. tellraw 不写日志，会导致网页端看不到自己发的消息
     *
     * say 命令格式：say [网站] 玩家名：消息内容
     * 游戏内显示：[Server] [网站] 玩家名：消息内容
     * 日志同步服务会跳过含 [网站] 的消息，避免重复入库
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
        // 格式：<玩家名> 消息内容
        // 使用 tellraw 而非 say：
        // 1. say 会显示 [Server] 前缀，暴露消息来自网站
        // 2. say 会写日志导致日志同步服务可能重复入库
        // 3. tellraw 纯客户端显示，不写日志，视觉上完全等同玩家游戏内聊天
        try {
            $rcon = new MinecraftRconService($rconHost, $rconPort, $rconPassword, 3);
            $rcon->connect();

            // 构造 tellraw JSON，模拟 MC 默认聊天格式
            // 游戏内显示为：<玩家名> 消息 —— 和普通玩家说话一模一样
            $raw = json_encode([
                ['text' => '<', 'color' => 'gray'],
                ['text' => $playerName, 'color' => 'gold'],
                ['text' => '> ', 'color' => 'gray'],
                ['text' => $message, 'color' => 'white'],
            ], JSON_UNESCAPED_UNICODE);

            $command = 'tellraw @a ' . escapeshellarg($raw);
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
                    'message' => $saved->message,
                    'timestamp' => $saved->timestamp->format('Y-m-d H:i:s'),
                    'avatar_url' => $user->getAvatarUrl(),
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
                'message' => $saved->message,
                'timestamp' => $saved->timestamp->format('Y-m-d H:i:s'),
                'avatar_url' => $user->getAvatarUrl(),
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
                'message' => $created->message,
                'timestamp' => $created->timestamp->format('Y-m-d H:i:s'),
            ],
        ]);
    }
}
