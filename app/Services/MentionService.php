<?php
namespace App\Services;
use App\Http\Controllers\NotificationController;
use App\Models\Thread;
use App\Models\User;

class MentionService
{
    /**
     * 扫描内容中的 @用户名 并给对应用户发通知
     */
    public static function processMentions(string $body, Thread $thread, int $replyId): void
    {
        // 匹配 @用户名（中文/英文/数字/下划线，2-20 字符）
        preg_match_all('/@([\w\x{4e00}-\x{9fff}]{2,20})/u', $body, $matches);

        if (empty($matches[1])) return;

        $mentionedNames = array_unique($matches[1]);

        foreach ($mentionedNames as $name) {
            $user = User::where('name', $name)->first();
            if (! $user) continue;

            // 不给自己发通知
            if ($user->id === auth()->id()) continue;

            NotificationController::createNotification(
                $user->id,
                'mention',
                $replyId,
                \App\Models\Reply::class,
                auth()->id(),
                [
                    'thread_title' => $thread->title,
                    'thread_slug' => $thread->slug,
                    'reply_id' => $replyId,
                    'body' => mb_substr(strip_tags($body), 0, 200),
                ]
            );
        }
    }
}