<?php

namespace App\Services;

class MinecraftQueryService
{
    private int $timeout = 3;

    public function query(string $host, int $port = 25565): array
    {
        $socket = @fsockopen($host, $port, $errno, $errstr, $this->timeout);

        if (!$socket) {
            throw new \RuntimeException("Unable to connect to {$host}:{$port} - {$errstr}");
        }

        stream_set_timeout($socket, $this->timeout);

        $handshake = $this->buildHandshakePacket($host, $port);
        fwrite($socket, $handshake);

        $request = "\x01\x00";
        fwrite($socket, $request);

        $length = $this->readVarInt($socket);
        if ($length <= 0) {
            fclose($socket);
            throw new \RuntimeException('Invalid response length');
        }

        $packetId = $this->readVarInt($socket);
        if ($packetId !== 0x00) {
            fclose($socket);
            throw new \RuntimeException('Unexpected packet ID');
        }

        $jsonLength = $this->readVarInt($socket);
        $jsonData = $this->readString($socket, $jsonLength);

        fclose($socket);

        $data = json_decode($jsonData, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('Invalid JSON response');
        }

        return $data;
    }

    private function buildHandshakePacket(string $host, int $port): string
    {
        $packetData = "\x00";
        $packetData .= $this->encodeVarInt(-1);
        $packetData .= $this->encodeString($host);
        $packetData .= pack('n', $port);
        $packetData .= $this->encodeVarInt(1);

        return $this->encodeVarInt(strlen($packetData)) . $packetData;
    }

    private function encodeVarInt(int $value): string
    {
        $result = '';
        while (true) {
            if (($value & ~0x7F) === 0) {
                $result .= chr($value);
                return $result;
            }
            $result .= chr(($value & 0x7F) | 0x80);
            $value = $this->unsignedRightShift($value, 7);
        }
    }

    private function readVarInt($socket): int
    {
        $value = 0;
        $position = 0;
        $currentByte = 0;

        while (true) {
            $currentByte = ord(fgetc($socket));
            $value |= ($currentByte & 0x7F) << $position;

            if (($currentByte & 0x80) !== 0x80) {
                break;
            }

            $position += 7;

            if ($position >= 32) {
                throw new \RuntimeException('VarInt too big');
            }
        }

        return $value;
    }

    private function encodeString(string $value): string
    {
        return $this->encodeVarInt(strlen($value)) . $value;
    }

    private function readString($socket, int $length): string
    {
        $data = '';
        $remaining = $length;

        while ($remaining > 0) {
            $chunk = fread($socket, $remaining);
            if ($chunk === false) {
                throw new \RuntimeException('Failed to read string from socket');
            }
            $data .= $chunk;
            $remaining -= strlen($chunk);
        }

        return $data;
    }

    private function unsignedRightShift(int $a, int $b): int
    {
        if ($b >= 32 || $b < -32) {
            $m = (int)($b / 32);
            $b = $b - ($m * 32);
        }

        if ($b < 0) {
            $b += 32;
        }

        if ($b == 0) {
            return (($a >> 1) & 0x7fffffff) * 2 + (($a >> $b) & 1);
        }

        $z = ($a >> 1);
        if ($a < 0) {
            $z &= 0x7fffffff;
        }

        return ($z >> ($b - 1));
    }

    public function setTimeout(int $seconds): void
    {
        $this->timeout = $seconds;
    }
}
