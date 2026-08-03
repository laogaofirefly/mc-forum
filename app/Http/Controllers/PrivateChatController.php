<?php

namespace App\Http\Controllers;

use App\Models\PrivateMessage;
use App\Models\User;
use App\Services\PlayerAvatarService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class PrivateChatController extends Controller
{
    public function index(Request $request): View
    {
        $userId = Auth::id();
        $messages = collect();
        $chatUser = null;
        $withId = $request->integer('with');
        $tableExists = Schema::hasTable('private_messages');

        if ($withId && $withId !== $userId && $tableExists) {
            $chatUser = User::where('id', $withId)->where('is_blocked', false)->first();
            if ($chatUser) {
                $messages = PrivateMessage::getConversation($userId, $chatUser->id, 100);
                PrivateMessage::where('sender_id', $chatUser->id)
                    ->where('receiver_id', $userId)
                    ->whereNull('read_at')
                    ->update(['read_at' => now()]);
            }
        }

        $contacts = $tableExists ? PrivateMessage::getRecentContacts($userId) : collect();
        $users = User::where('id', '!=', $userId)
            ->where('is_blocked', false)
            ->orderBy('name')
            ->limit(20)
            ->get()
            ->map(function ($user) {
                $user->avatar_url = $user->getAvatarUrl();
                return $user;
            });

        return view('private-chat.index', compact('messages', 'chatUser', 'contacts', 'users'));
    }

    public function fetch(Request $request): JsonResponse
    {
        if (!Schema::hasTable('private_messages')) {
            return response()->json(['ok' => false, 'message' => '私聊功能未初始化，请运行 php artisan migrate --force'], 500);
        }

        $userId = Auth::id();
        $withId = $request->integer('with_id');
        $afterId = $request->integer('after_id', 0);

        if (!$withId || $withId === $userId) {
            return response()->json(['ok' => false, 'message' => '参数错误'], 400);
        }

        $chatUser = User::where('id', $withId)->where('is_blocked', false)->first();
        if (!$chatUser) {
            return response()->json(['ok' => false, 'message' => '用户不存在'], 404);
        }

        $messages = PrivateMessage::getConversation($userId, $withId, 100, $afterId ?: null);

        // 标记已读
        PrivateMessage::where('sender_id', $withId)
            ->where('receiver_id', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json([
            'ok' => true,
            'current_user_id' => $userId,
            'current_user_name' => Auth::user()->name,
            'chat_user_name' => $chatUser->name,
            'count' => $messages->count(),
            'last_id' => $messages->last()?->id ?? $afterId,
            'messages' => $messages->map(function ($m) use ($userId) {
                $sender = $m->sender_id === $userId ? Auth::user() : $m->sender;
                return [
                    'id' => $m->id,
                    'sender_id' => $m->sender_id,
                    'player_name' => $sender->name,
                    'avatar_url' => PlayerAvatarService::url($sender->name, $sender->mc_uuid),
                    'message' => $m->message,
                    'timestamp' => $m->created_at->format('Y-m-d H:i:s'),
                ];
            }),
        ]);
    }

    public function send(Request $request): JsonResponse
    {
        if (!Auth::check()) {
            return response()->json(['ok' => false, 'message' => '请先登录'], 401);
        }

        if (!Schema::hasTable('private_messages')) {
            return response()->json(['ok' => false, 'message' => '私聊功能未初始化，请运行 php artisan migrate --force'], 500);
        }

        $request->validate([
            'message' => 'required|string|min:1|max:500',
            'receiver_id' => 'required|integer|exists:users,id',
        ], [
            'message.required' => '消息内容不能为空',
            'message.max' => '消息最多 500 字',
            'receiver_id.required' => '请选择聊天对象',
            'receiver_id.exists' => '聊天对象不存在',
        ]);

        $userId = Auth::id();
        $receiverId = (int) $request->input('receiver_id');

        if ($receiverId === $userId) {
            return response()->json(['ok' => false, 'message' => '不能给自己发消息'], 400);
        }

        $receiver = User::where('id', $receiverId)->where('is_blocked', false)->first();
        if (!$receiver) {
            return response()->json(['ok' => false, 'message' => '用户不存在或已被封禁'], 404);
        }

        $message = trim($request->input('message'));

        $saved = PrivateMessage::create([
            'sender_id' => $userId,
            'receiver_id' => $receiverId,
            'message' => $message,
        ]);

        $user = Auth::user();

        return response()->json([
            'ok' => true,
            'record' => [
                'id' => $saved->id,
                'sender_id' => $saved->sender_id,
                'player_name' => $user->name,
                'avatar_url' => PlayerAvatarService::url($user->name, $user->mc_uuid),
                'message' => $saved->message,
                'timestamp' => $saved->created_at->format('Y-m-d H:i:s'),
            ],
        ]);
    }

    public function contacts(Request $request): JsonResponse
    {
        if (!Schema::hasTable('private_messages')) {
            return response()->json(['ok' => true, 'contacts' => []]);
        }

        $userId = Auth::id();
        $contacts = PrivateMessage::getRecentContacts($userId);

        return response()->json([
            'ok' => true,
            'contacts' => $contacts,
        ]);
    }

    public function searchUsers(Request $request): JsonResponse
    {
        $userId = Auth::id();
        $q = trim($request->input('q', ''));

        if (mb_strlen($q) < 1) {
            return response()->json(['ok' => true, 'users' => []]);
        }

        $users = User::where('id', '!=', $userId)
            ->where('is_blocked', false)
            ->where(function ($query) use ($q) {
                $query->where('name', 'like', '%' . $q . '%')
                    ->orWhere('mc_username', 'like', '%' . $q . '%');
            })
            ->orderBy('name')
            ->limit(20)
            ->get()
            ->map(function ($user) {
                $user->avatar_url = $user->getAvatarUrl();
                return $user;
            });

        return response()->json([
            'ok' => true,
            'users' => $users,
        ]);
    }
}