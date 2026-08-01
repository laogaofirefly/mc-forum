<?php

namespace App\Http\Controllers;

use App\Models\GameChatMessage;
use App\Services\MinecraftLogSyncService;
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
