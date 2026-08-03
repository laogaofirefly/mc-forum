<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

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
        $query = self::with('sender')->where(function ($q) use ($userIdA, $userIdB) {
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
     * 获取用户的最近聊天对象列表（含最后消息、时间、未读数，按最近消息时间倒序）
     */
    public static function getRecentContacts(int $userId, int $limit = 20)
    {
        $sentIds = self::where('sender_id', $userId)->distinct()->pluck('receiver_id');
        $receivedIds = self::where('receiver_id', $userId)->distinct()->pluck('sender_id');
        $contactIds = $sentIds->merge($receivedIds)->unique()->values();

        if ($contactIds->isEmpty()) {
            return collect();
        }

        // 批量获取每个联系人的最后一条消息 ID
        $lastMsgRows = DB::table('private_messages')
            ->where(function ($q) use ($userId) {
                $q->where('sender_id', $userId)->orWhere('receiver_id', $userId);
            })
            ->select(DB::raw('MAX(id) as id'))
            ->groupBy(DB::raw('CASE WHEN sender_id = ' . intval($userId) . ' THEN receiver_id ELSE sender_id END'))
            ->pluck('id');

        // 批量获取最后消息实体，按 contact_id 索引
        $lastMessages = self::whereIn('id', $lastMsgRows)->get()->keyBy(function ($msg) use ($userId) {
            return $msg->sender_id === $userId ? $msg->receiver_id : $msg->sender_id;
        });

        // 批量获取未读计数
        $unreadCounts = self::where('receiver_id', $userId)
            ->whereNull('read_at')
            ->whereIn('sender_id', $contactIds->toArray())
            ->selectRaw('sender_id, COUNT(*) as count')
            ->groupBy('sender_id')
            ->pluck('count', 'sender_id');

        // 获取用户实体
        $contacts = User::whereIn('id', $contactIds)
            ->where('is_blocked', false)
            ->get();

        // 附加最后消息、时间、未读数
        foreach ($contacts as $contact) {
            $lastMsg = $lastMessages->get($contact->id);
            $contact->last_message = $lastMsg ? mb_substr($lastMsg->message, 0, 30) : '';
            $contact->last_message_time = $lastMsg ? $lastMsg->created_at->format('m-d H:i') : '';
            $contact->last_message_ts = $lastMsg ? $lastMsg->created_at->timestamp : 0;
            $contact->unread_count = (int) ($unreadCounts->get($contact->id, 0));
        }

        return $contacts->sortByDesc('last_message_ts')->values();
    }
}