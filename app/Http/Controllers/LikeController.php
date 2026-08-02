<?php
namespace App\Http\Controllers;
use App\Models\Like;
use App\Models\Reply;
use App\Models\Thread;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LikeController extends Controller
{
    public function toggle(Request $request): JsonResponse
    {
        $user = Auth::user();
        $validated = $request->validate([
            'likeable_type' => ['required', 'string', 'in:thread,reply'],
            'likeable_id' => ['required', 'integer'],
        ]);

        // 映射
        $modelClass = $validated['likeable_type'] === 'thread' ? Thread::class : Reply::class;
        $likeable = $modelClass::findOrFail($validated['likeable_id']);

        $existing = Like::where('user_id', $user->id)
            ->where('likeable_type', $modelClass)
            ->where('likeable_id', $likeable->id)
            ->first();

        if ($existing) {
            $existing->delete();
            $liked = false;
        } else {
            Like::create([
                'user_id' => $user->id,
                'likeable_type' => $modelClass,
                'likeable_id' => $likeable->id,
            ]);
            $liked = true;
        }

        // 发送点赞通知（仅点赞时，且不是自己给自己点）
        if ($liked && $modelClass === Reply::class) {
            $reply = $likeable;
            if ($reply->user_id !== $user->id) {
                NotificationController::createNotification(
                    $reply->user_id,
                    'like',
                    $reply->id,
                    Reply::class,
                    $user->id,
                    ['thread_id' => $reply->thread_id, 'thread_slug' => $reply->thread->slug]
                );
            }
        } elseif ($liked && $modelClass === Thread::class) {
            $thread = $likeable;
            if ($thread->user_id !== $user->id) {
                NotificationController::createNotification(
                    $thread->user_id,
                    'like',
                    $thread->id,
                    Thread::class,
                    $user->id,
                    ['thread_slug' => $thread->slug]
                );
            }
        }

        $count = $likeable->likesCount();

        return response()->json(['ok' => true, 'liked' => $liked, 'count' => $count]);
    }
}