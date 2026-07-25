<?php

namespace App\Http\Controllers;

use App\Models\ServerStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServerStatusController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $host = $request->get('host', config('services.minecraft.host', 'localhost'));
        $port = (int)$request->get('port', config('services.minecraft.port', 25565));

        try {
            $status = ServerStatus::getStatus($host, $port);

            return response()->json([
                'success' => true,
                'data' => [
                    'host' => $status->host,
                    'port' => $status->port,
                    'is_online' => $status->is_online,
                    'players_online' => $status->players_online,
                    'players_max' => $status->players_max,
                    'motd' => $status->motd,
                    'version' => $status->version,
                    'favicon' => $status->favicon,
                    'players' => $status->players_json,
                    'updated_at' => $status->updated_at->toISOString(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'data' => [
                    'host' => $host,
                    'port' => $port,
                    'is_online' => false,
                    'players_online' => 0,
                    'players_max' => 0,
                ],
            ], 200);
        }
    }
}
