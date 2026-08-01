<?php

namespace App\Models;

use App\Services\MinecraftQueryService;
use Illuminate\Database\Eloquent\Model;

class ServerStatus extends Model
{
    protected $table = 'server_status';
    const CREATED_AT = null;

    protected $fillable = [
        'host', 'port', 'is_online', 'players_online', 'players_max',
        'motd', 'version', 'favicon', 'players_json',
    ];

    protected function casts(): array
    {
        return [
            'is_online' => 'boolean',
            'players_online' => 'integer',
            'players_max' => 'integer',
            'players_json' => 'array',
            'updated_at' => 'datetime',
        ];
    }

    public static function getStatus(string $host, int $port = 25565): ?self
    {
        $serverStatus = self::where('host', $host)->where('port', $port)->first();

        if (!$serverStatus || $serverStatus->updated_at->diffInMinutes(now()) >= 1) {
            if (!$serverStatus) {
                $serverStatus = new self();
                $serverStatus->host = $host;
                $serverStatus->port = $port;
            }

            try {
                $queryService = app(MinecraftQueryService::class);
                $data = $queryService->query($host, $port);

                $serverStatus->is_online = true;
                $serverStatus->players_online = $data['players']['online'] ?? 0;
                $serverStatus->players_max = $data['players']['max'] ?? 0;
                $serverStatus->motd = $data['description']['text'] ?? ($data['description'] ?? '');
                $serverStatus->version = $data['version']['name'] ?? null;
                $serverStatus->favicon = $data['favicon'] ?? null;
                $serverStatus->players_json = $data['players']['sample'] ?? null;
            } catch (\Exception $e) {
                $serverStatus->is_online = false;
            }

            $serverStatus->updated_at = now();
            $serverStatus->save();
        }

        return $serverStatus;
    }

    public function updateStatus(): void
    {
        $this->updated_at = now()->subMinutes(2);
        $this->save();
        self::getStatus($this->host, $this->port);
    }
}
