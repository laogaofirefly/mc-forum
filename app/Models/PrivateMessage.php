<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrivateMessage extends Model
{
    protected $fillable = ['sender_id', 'receiver_id', 'message', 'read_at'];

    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
        ];
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    /**
     * 获取两个用户之间的消息
     */
    public static function getConversation(int $userIdA, int $userIdB, int $limit = 100, ?int $afterId = null)
    {
        $query = self::where(function ($q) use ($userIdA, $userIdB) {
            $q->where('sender_id', $userIdA)->where('receiver_id', $userIdB);
        })->orWhere(function ($q) use ($userIdA, $userIdB) {
            $q->where('sender_id', $userIdB)->where('receiver_id', $userIdA);
        })->orderBy('id', 'desc')->limit($limit);

        if ($afterId) {
            $query->where('id', '>', $afterId);
        }

        return $query->get()->reverse()->values();
    }

    /**
     * 获取用户的最近聊天对象列表
     */
    public static function getRecentContacts(int $userId, int $limit = 20)
    {
        // 获取最近有私信往来的用户
        $sentIds = self::where('sender_id', $userId)
            ->distinct()
            ->pluck('receiver_id');

        $receivedIds = self::where('receiver_id', $userId)
            ->distinct()
            ->pluck('sender_id');

        $contactIds = $sentIds->merge($receivedIds)->unique()->values();

        if ($contactIds->isEmpty()) {
            return collect();
        }

        return User::whereIn('id', $contactIds)
            ->where('is_blocked', false)
            ->orderBy('name')
            ->limit($limit)
            ->get();
    }
}