<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\ConnectionException;

/**
 * MCSM（秒服务器管理/MCSManager）API 客户端
 *
 * 通过 MCSM 面板的 REST API 执行：状态查询、日志获取、命令执行、启停操作。
 *
 * 配置项（.env）：
 *   MCSM_API_URL     - MCSM 面板地址，如 http://localhost:23333
 *   MCSM_API_KEY     - MCSM 面板 API 密钥
 *   MCSM_DAEMON_ID   - 守护进程 ID
 *   MCSM_INSTANCE_ID - 实例 ID
 *
 * 使用示例：
 *   $mcsm = new McsmService();
 *   $status = $mcsm->getStatus();
 *   $log = $mcsm->getLog();
 *   $mcsm->executeCommand('list');
 *   $mcsm->start();
 */
class McsmService
{
    private string $apiUrl;
    private string $apiKey;
    private string $daemonId;
    private string $instanceId;
    private ?string $uuid = null;

    public function __construct()
    {
        $this->apiUrl = rtrim((string) config('services.minecraft.mcsm.api_url', ''), '/');
        $this->apiKey = config('services.minecraft.mcsm.api_key', '');
        $this->daemonId = config('services.minecraft.mcsm.daemon_id', '');
        $this->instanceId = config('services.minecraft.mcsm.instance_id', '');
        $this->uuid = $this->resolveUuid();
    }

    /**
     * 是否已配置 MCSM
     */
    public function isConfigured(): bool
    {
        return $this->apiUrl !== '' && $this->apiKey !== '' && $this->instanceId !== '';
    }

    /**
     * 获取配置信息（脱敏后返回给前端）
     */
    public function configInfo(): array
    {
        return [
            'api_url' => $this->apiUrl ?: '未配置',
            'api_key' => $this->apiKey ? substr($this->apiKey, 0, 6) . '****' : '未配置',
            'daemon_id' => $this->daemonId ?: '未配置',
            'instance_id' => $this->instanceId ?: '未配置',
            'uuid' => $this->uuid ?? '需刷新页面获取',
            'configured' => $this->isConfigured(),
        ];
    }

    /**
     * 获取实例状态
     * 返回: ['online' => bool, 'cpu' => int, 'memory' => int, 'max_memory' => int, ...]
     */
    public function getStatus(): array
    {
        $data = $this->request('GET', '/api/instance/' . $this->uuid);
        $inst = $data['data'] ?? [];

        return [
            'ok' => true,
            'online' => ($inst['status'] ?? 0) === 1,
            'status' => $inst['status'] ?? 0,       // 0=停止, 1=运行中, 2=启动中, 3=停止中
            'cpu' => $inst['cpu'] ?? 0,
            'memory' => $inst['memory'] ?? 0,
            'max_memory' => $inst['max_memory'] ?? 0,
            'name' => $inst['nickname'] ?? $this->instanceId,
            'raw' => $inst,
        ];
    }

    /**
     * 获取实例日志（最后 N 行）
     */
    public function getLog(int $afterLine = 0, int $limit = 200): array
    {
        // MCSM 日志没有 offset 参数，需要自行截取
        $data = $this->request('GET', '/api/instance/' . $this->uuid . '/log', [
            'size' => max($limit, 500),
        ]);

        $allLogs = $data['data']['logs'] ?? [];
        if (is_string($allLogs)) {
            $allLogs = explode("\n", $allLogs);
        }
        if (! is_array($allLogs)) {
            $allLogs = [];
        }

        // MCSM 日志格式: [时间] 内容
        $parsedLines = [];
        $lineNum = 0;

        foreach ($allLogs as $raw) {
            $lineNum++;
            if ($lineNum <= $afterLine) continue;
            $raw = rtrim($raw, "\r\n");

            // 尝试解析前缀时间戳
            $chat = null;
            $service = app(\App\Services\MinecraftLogSyncService::class);
            $parsed = $service->parseLine($raw);
            if ($parsed) {
                $chat = ['player' => $parsed['player'], 'message' => $parsed['message']];
            }

            $parsedLines[] = ['n' => $lineNum, 'raw' => $raw, 'chat' => $chat];
        }

        return [
            'ok' => true,
            'lines' => $parsedLines,
            'size' => count($allLogs),
            'pos' => $lineNum,
        ];
    }

    /**
     * 向服务器发送命令（通过 MCSM /cgi-bin/command）
     */
    public function sendCommand(string $command): array
    {
        // MCSM 发送命令的 API
        $resp = $this->request('POST', '/api/protected_instance/command', [
            'daemonId' => $this->daemonId,
            'uuid' => $this->uuid,
            'command' => $command,
        ]);

        return [
            'ok' => true,
            'command' => '/' . $command,
            'response' => $resp['data'] ?? ($resp['message'] ?? '命令已发送'),
        ];
    }

    /**
     * 启动实例
     */
    public function start(): array
    {
        $resp = $this->request('POST', '/api/protected_instance/' . $this->uuid . '/open');
        return ['ok' => true, 'message' => $resp['data'] ?? $resp['message'] ?? '启动命令已发送'];
    }

    /**
     * 停止实例
     */
    public function stop(): array
    {
        $resp = $this->request('POST', '/api/protected_instance/' . $this->uuid . '/stop');
        return ['ok' => true, 'message' => $resp['data'] ?? $resp['message'] ?? '停止命令已发送'];
    }

    /**
     * 重启实例
     */
    public function restart(): array
    {
        $resp = $this->request('POST', '/api/protected_instance/' . $this->uuid . '/restart');
        return ['ok' => true, 'message' => $resp['data'] ?? $resp['message'] ?? '重启命令已发送'];
    }

    // ── 内部方法 ──

    private function resolveUuid(): string
    {
        if (empty($this->daemonId) || empty($this->instanceId)) {
            return '';
        }
        try {
            $resp = $this->request('GET', '/api/instance', [
                'daemonId' => $this->daemonId,
                'page' => 1,
                'page_size' => 200,
            ]);
            $instances = $resp['data']['data'] ?? $resp['data'] ?? [];
            foreach ($instances as $inst) {
                if ((string) ($inst['daemonId'] ?? '') === $this->daemonId
                    && (string) ($inst['nickname'] ?? '') === $this->instanceId) {
                    return $inst['uuid'] ?? '';
                }
            }
        } catch (\Throwable $e) {
            // 静默失败
        }
        // 如果 instance_id 本身就是 UUID，直接返回
        if (preg_match('/^[a-f0-9\-]{32,36}$/i', $this->instanceId)) {
            return $this->instanceId;
        }
        return '';
    }

    /**
     * 发送 HTTP 请求到 MCSM API
     */
    private function request(string $method, string $path, mixed $data = null): array
    {
        $url = $this->apiUrl . $path;
        $options = [
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $this->apiKey,
            ],
            'timeout' => 15,
            'connect_timeout' => 5,
        ];

        try {
            $response = null;
            if ($method === 'GET') {
                if (is_array($data)) {
                    $options['query'] = $data;
                }
                $response = Http::withOptions($options)->get($url);
            } elseif ($method === 'POST') {
                $payload = is_array($data) ? $data : [];
                $response = Http::withOptions($options)->asJson()->post($url, $payload);
            }

            if (! $response) {
                throw new \RuntimeException('无响应');
            }

            $body = $response->json();
            if (! $body) {
                $raw = $response->body();
                throw new \RuntimeException('API 返回无法解析：' . substr($raw, 0, 200));
            }

            if (! $response->successful()) {
                $msg = $body['message'] ?? $body['error'] ?? 'HTTP ' . $response->status();
                throw new \RuntimeException($msg);
            }

            return $body;
        } catch (ConnectionException $e) {
            throw new \RuntimeException('无法连接 MCSM 面板（' . $this->apiUrl . '），请确认面板正在运行且网络可达');
        }
    }
}
