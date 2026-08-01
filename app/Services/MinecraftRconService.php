<?php

namespace App\Services;

/**
 * Minecraft RCON 客户端
 *
 * 通过 RCON 协议向 MC 服务器发送命令（如 say 广播消息）。
 * 协议参考：https://wiki.vg/RCON
 *
 * 使用示例：
 *   $rcon = new MinecraftRconService('127.0.0.1', 25575, 'your_password');
 *   $rcon->connect();
 *   $response = $rcon->sendCommand('say hello from web');
 *   $rcon->disconnect();
 */
class MinecraftRconService
{
    private string $host;
    private int $port;
    private string $password;
    private int $timeout;

    /** @var resource|null */
    private $socket = null;

    private int $requestId = 0;

    // RCON 数据包类型
    private const TYPE_AUTH = 3;
    private const TYPE_AUTH_RESPONSE = 2;
    private const TYPE_EXECCOMMAND = 2;
    private const TYPE_RESPONSE_VALUE = 0;

    public function __construct(string $host, int $port, string $password, int $timeout = 3)
    {
        $this->host = $host;
        $this->port = $port;
        $this->password = $password;
        $this->timeout = $timeout;
    }

    /**
     * 连接并鉴权
     */
    public function connect(): void
    {
        $errno = 0;
        $errstr = '';
        $this->socket = @fsockopen($this->host, $this->port, $errno, $errstr, $this->timeout);

        if (! $this->socket) {
            throw new \RuntimeException("无法连接 RCON ({$this->host}:{$this->port})：{$errstr}");
        }

        stream_set_timeout($this->socket, $this->timeout);

        // 发送鉴权包
        $this->sendPacket(self::TYPE_AUTH, $this->password);
        $response = $this->readPacket();

        if ($response === null) {
            $this->disconnect();
            throw new \RuntimeException('RCON 鉴权失败：服务器无响应');
        }

        if ($response['type'] !== self::TYPE_AUTH_RESPONSE) {
            $this->disconnect();
            throw new \RuntimeException('RCON 鉴权失败：响应类型错误');
        }

        if ($response['id'] === -1) {
            $this->disconnect();
            throw new \RuntimeException('RCON 鉴权失败：密码错误');
        }
    }

    /**
     * 发送命令并返回响应文本
     */
    public function sendCommand(string $command): string
    {
        if (! $this->socket) {
            throw new \RuntimeException('RCON 未连接，请先调用 connect()');
        }

        $this->sendPacket(self::TYPE_EXECCOMMAND, $command);
        $response = $this->readPacket();

        if ($response === null) {
            return '';
        }

        return $response['body'];
    }

    /**
     * 关闭连接
     */
    public function disconnect(): void
    {
        if ($this->socket) {
            @fclose($this->socket);
            $this->socket = null;
        }
    }

    /**
     * 一次性执行命令的快捷方法（自动连接+断开）
     */
    public function command(string $command): string
    {
        try {
            $this->connect();
            $result = $this->sendCommand($command);
            return $result;
        } finally {
            $this->disconnect();
        }
    }

    private function sendPacket(int $type, string $body): void
    {
        $id = ++$this->requestId;
        // 包格式：长度(4) + ID(4) + 类型(4) + body + \x00 + \x00
        $packet = pack('V3', strlen($body) + 10, $id, $type) . $body . "\x00\x00";
        fwrite($this->socket, $packet);
    }

    private function readPacket(): ?array
    {
        // 先读 4 字节长度
        $sizeData = fread($this->socket, 4);
        if ($sizeData === false || strlen($sizeData) < 4) {
            return null;
        }
        $size = unpack('V', $sizeData)[1];
        if ($size < 10 || $size > 4096) {
            return null;
        }

        // 读取剩余数据
        $remaining = $size;
        $data = '';
        while ($remaining > 0) {
            $chunk = fread($this->socket, $remaining);
            if ($chunk === false || $chunk === '') {
                break;
            }
            $data .= $chunk;
            $remaining -= strlen($chunk);
        }

        if (strlen($data) < 8) {
            return null;
        }

        $id = unpack('V', substr($data, 0, 4))[1];
        $type = unpack('V', substr($data, 4, 4))[1];
        // body 是后面的内容（去掉两个结尾 \x00）
        $body = substr($data, 8, -2);

        return [
            'id' => $id,
            'type' => $type,
            'body' => $body,
        ];
    }

    public function __destruct()
    {
        $this->disconnect();
    }
}
