<?php

namespace App\Http\Controllers;

use App\Models\GameChatMessage;
use App\Services\MinecraftLogSyncService;
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

    public function fetch(Request $request, MinecraftLogSyncService $syncService): JsonResponse
    {
        // 每次轮询时自动尝试同步 MC 日志，让玩家聊天能实时显示
        // 同步失败不影响聊天页正常工作
        try {
            $syncService->setMaxBatch(50)->sync();
        } catch (\Throwable $e) {
            // 静默失败，不影响聊天列表加载
        }

        $afterId = $request->integer('after_id', 0);
        $limit = min(200, max(10, $request->integer('limit', 100)));

        $messages = GameChatMessage::getLatest($limit, $afterId ?: null);

        return response()->json([
            'ok' => true,
            'count' => $messages->count(),
            'last_id' => $messages->last()?->id ?? $afterId,
            'time' => now()->toDateTimeString(),
            'messages' => $messages->map(function ($m) {
                return [
                    'id' => $m->id,
                    'player_name' => $m->player_name,
                    'player_uuid' => $m->player_uuid,
                    'message' => $m->message,
                    'channel' => $m->channel,
                    'timestamp' => $m->timestamp?->format('H:i:s') ?? now()->format('H:i:s'),
                ];
            }),
        ]);
    }

    /**
     * 从网站向游戏发消息（通过 RCON 调用 say 命令）
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

        // 通过 RCON 发送 say 命令
        // 格式：say [网站] 玩家名：消息
        // MC 服务器会广播为：[Server] [网站] 玩家名：消息
        try {
            $rcon = new MinecraftRconService($rconHost, $rconPort, $rconPassword, 3);
            $rcon->connect();

            // 转义可能影响命令解析的字符
            $safeMessage = str_replace(['\\', '"', "\n", "\r"], ['\\\\', '\\"', ' ', ' '], $message);
            $safeName = str_replace(['\\', '"', "\n", "\r"], ['\\\\', '\\"', ' ', ' '], $playerName);

            $command = sprintf('say [网站] %s：%s', $safeName, $safeMessage);
            $rcon->sendCommand($command);
            $rcon->disconnect();
        } catch (\Throwable $e) {
            return response()->json([
                'ok' => false,
                'message' => '发送到游戏失败：' . $e->getMessage(),
            ], 500);
        }

        // 同时把这条消息存进数据库（让网页聊天页也能看到自己发的）
        // 用 player_name = 玩家名 + [网站] 标记
        $saved = GameChatMessage::addMessage(
            $playerName . ' [网站]',
            $message,
            null,
            'web'
        );

        return response()->json([
            'ok' => true,
            'message' => '已发送到游戏',
            'record' => [
                'id' => $saved->id,
                'player_name' => $saved->player_name,
                'message' => $saved->message,
                'timestamp' => $saved->timestamp->format('H:i:s'),
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
                'timestamp' => $created->timestamp->format('H:i:s'),
            ],
        ]);
    }
}
