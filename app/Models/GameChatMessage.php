<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GameChatMessage extends Model
{
    protected $fillable = ['player_uuid', 'player_name', 'message', 'channel', 'timestamp'];

    protected function casts(): array
    {
        return [
            'timestamp' => 'datetime',
        ];
    }

    public static function addMessage(string $playerName, string $message, ?string $playerUuid = null, string $channel = 'global'): self
    {
        return self::create([
            'player_uuid' => $playerUuid,
            'player_name' => $playerName,
            'message' => $message,
            'channel' => $channel,
            'timestamp' => now(),
        ]);
    }

    public static function getLatest(int $limit = 100, ?int $afterId = null)
    {
        $query = self::orderBy('id', 'desc')
            ->limit($limit);
        if ($afterId) {
            $query->where('id', '>', $afterId);
        }
        return $query->get()->reverse()->values();
    }
}
