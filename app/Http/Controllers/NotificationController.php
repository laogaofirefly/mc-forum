<?php
namespace App\Http\Controllers;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class NotificationController extends Controller
{
    /**
     * 静态工具方法：创建一条通知
     */
    public static function createNotification(
        int $userId,
        string $type,
        int $notifiableId,
        string $notifiableType,
        int $fromUserId,
        ?array $data = null
    ): void {
        if ($userId === $fromUserId) return;
        Notification::create([
            'user_id'         => $userId,
            'type'            => $type,
            'notifiable_id'   => $notifiableId,
            'notifiable_type' => $notifiableType,
            'from_user_id'    => $fromUserId,
            'data'            => $data,
            'is_read'         => false,
        ]);
    }

    /**
     * 通知中心页面
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $notifications = Notification::with(['fromUser'])
            ->where('user_id', $user->id)
            ->latest()
            ->limit(50)
            ->get();

        $unreadCount = $notifications->where('is_read', false)->count();

        // 标记所有为已读
        if ($unreadCount > 0) {
            Notification::where('user_id', $user->id)
                ->where('is_read', false)
                ->update(['is_read' => true]);
        }

        return view('notifications.index', compact('notifications', 'unreadCount'));
    }

    /**
     * AJAX 获取未读通知数量
     */
    public function unreadCount(Request $request): JsonResponse
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['count' => 0]);
        }
        $count = Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->count();
        return response()->json(['count' => $count]);
    }
}
