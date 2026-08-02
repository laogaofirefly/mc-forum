<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServerStatus;
use App\Models\Thread;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ServerMonitorController extends Controller
{
    public function index(Request $request): View
    {
        if (! $request->user() || ! $request->user()->isAdmin()) {
            abort(403, '仅管理员可访问');
        }
        // 基础统计（始终可用）
        $stats = [
            'total_users' => User::count(),
            'total_threads' => Thread::count(),
            'today_threads' => Thread::where('created_at', '>=', now()->startOfDay())->count(),
            'total_replies' => DB::table('replies')->count(),
            'today_registrations' => User::where('created_at', '>=', now()->startOfDay())->count(),
        ];

        // 系统信息（Linux/Mac 可用；Windows 只返回 PHP 运行环境信息）
        $system = [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? php_uname('s'),
            'os' => php_uname('s') . ' ' . php_uname('r') . ' ' . php_uname('m'),
            'server_time' => now()->toDateTimeString(),
            'sapi' => PHP_SAPI,
            'php_memory_limit' => ini_get('memory_limit'),
            'php_upload_max' => ini_get('upload_max_filesize'),
            'php_post_max' => ini_get('post_max_size'),
            'php_max_exec' => ini_get('max_execution_time') . 's',
            'db_driver' => config('database.default'),
            'timezone' => config('app.timezone'),
            'mc_host' => config('services.minecraft.host', 'localhost'),
            'mc_port' => (int) config('services.minecraft.port', 25565),
        ];

        // 系统负载（仅非Windows尝试获取）
        $load = null;
        $disk = null;
        $memory = null;
        if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
            if (function_exists('sys_getloadavg')) {
                $load = sys_getloadavg();
            }
            $diskFree = @disk_free_space(base_path());
            $diskTotal = @disk_total_space(base_path());
            if ($diskFree !== false && $diskTotal !== false) {
                $disk = [
                    'free' => $this->formatBytes($diskFree),
                    'total' => $this->formatBytes($diskTotal),
                    'used' => $this->formatBytes($diskTotal - $diskFree),
                    'percent' => $diskTotal > 0 ? round(($diskTotal - $diskFree) * 100 / $diskTotal, 1) : 0,
                ];
            }
        } else {
            // Windows 尝试读取磁盘空间
            try {
                $diskFree = @disk_free_space('C:');
                $diskTotal = @disk_total_space('C:');
                if ($diskFree !== false && $diskTotal !== false) {
                    $disk = [
                        'free' => $this->formatBytes($diskFree),
                        'total' => $this->formatBytes($diskTotal),
                        'used' => $this->formatBytes($diskTotal - $diskFree),
                        'percent' => $diskTotal > 0 ? round(($diskTotal - $diskFree) * 100 / $diskTotal, 1) : 0,
                    ];
                }
            } catch (\Throwable $e) {}
        }

        // MC 服务器状态
        $serverStatus = null;
        try {
            $serverStatus = ServerStatus::getStatus($system['mc_host'], $system['mc_port']);
        } catch (\Throwable $e) {}

        return view('admin.server-monitor', compact('stats', 'system', 'load', 'disk', 'memory', 'serverStatus'));
    }

    public function metrics(Request $request): JsonResponse
    {
        if (! $request->user() || ! $request->user()->isAdmin()) {
            return response()->json(['ok' => false, 'message' => '仅管理员可访问'], 403);
        }
        $data = [
            'ok' => true,
            'time' => now()->toDateTimeString(),
            'php_memory_usage' => round(memory_get_usage(true) / 1024 / 1024, 2),
            'php_memory_peak' => round(memory_get_peak_usage(true) / 1024 / 1024, 2),
            'uptime' => null,
        ];

        if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN' && function_exists('sys_getloadavg')) {
            $data['load'] = sys_getloadavg();
        } else {
            $data['load'] = null;
        }

        try {
            $host = config('services.minecraft.host', 'localhost');
            $port = (int) config('services.minecraft.port', 25565);
            $status = ServerStatus::getStatus($host, $port);
            if ($status) {
                $data['mc'] = [
                    'online' => $status->is_online,
                    'players_online' => $status->players_online,
                    'players_max' => $status->players_max,
                    'version' => $status->version,
                    'players' => $status->players_json,
                ];
            }
        } catch (\Throwable $e) {
            $data['mc'] = null;
        }

        // 应用统计
        $data['app'] = [
            'today_threads' => Thread::where('created_at', '>=', now()->startOfDay())->count(),
            'today_users' => User::where('created_at', '>=', now()->startOfDay())->count(),
            'total_threads' => Thread::count(),
            'total_users' => User::count(),
        ];

        return response()->json($data);
    }

    protected function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
