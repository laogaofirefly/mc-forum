<?php

namespace App\Http\Controllers;

use App\Models\Reply;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class NotificationController extends Controller
{
    /**
     * 通知页：展示别人回复了我帖子的记录
     * 已读状态保存在 session（last_notification_seen）
     */
    public function index(Request $request): View
    {
        $user = Auth::user();

        // 查询别人回复我帖子的记录（排除自己回复自己的）
        $replies = Reply::with(['thread', 'user'])
            ->whereHas('thread', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->where('user_id', '!=', $user->id)
            ->latest()
            ->limit(50)
            ->get();

        // 统计未读数：上次查看时间之后的新回复
        $lastSeen = $request->session()->get('last_notification_seen');
        $unreadCount = 0;
        if ($lastSeen) {
            $unreadCount = $replies->where('created_at', '>', $lastSeen)->count();
        } else {
            // 第一次访问，全部算未读
            $unreadCount = $replies->count();
        }

        // 标记为已读
        $request->session()->put('last_notification_seen', now());

        return view('notifications.index', compact('replies', 'unreadCount'));
    }

    /**
     * 获取未读通知数（AJAX 接口，给导航栏小红点用）
     */
    public function unreadCount(Request $request)
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['count' => 0]);
        }

        $query = Reply::whereHas('thread', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })
        ->where('user_id', '!=', $user->id);

        $lastSeen = $request->session()->get('last_notification_seen');
        if ($lastSeen) {
            $query->where('created_at', '>', $lastSeen);
        }

        return response()->json(['count' => $query->count()]);
    }
}
