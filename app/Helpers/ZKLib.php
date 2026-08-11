<?php

namespace App\Helpers;

/**
 * ZKLib - ZKTeco K60/ID Biometric Device Helper
 * Protocol driver for ZKTeco K60/ID biometric attendance machines.
 */
class ZKLib
{
    private string $ip;
    private int $port;
    private int $comm_key;
    private $socket = null;
    private int $session_id = 0;
    private int $reply_id = 0;
    private bool $use_udp = true;

    public function __construct(string $ip = '', int $port = 4370, bool $use_udp = true, int $comm_key = 0)
    {
        $this->ip = $ip;
        $this->port = $port;
        $this->use_udp = $use_udp;
        $this->comm_key = $comm_key;
    }

    public function connect(): bool
    {
        if ($this->use_udp) {
            return $this->connectUDP();
        }

        return $this->connectTCP();
    }

    private function connectTCP(): bool
    {
        if (!function_exists('socket_create')) {
            return false;
        }

        $this->socket = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if (!$this->socket) {
            return false;
        }

        @socket_set_option($this->socket, SOL_SOCKET, SO_RCVTIMEO, ['sec' => 3, 'usec' => 0]);
        @socket_set_option($this->socket, SOL_SOCKET, SO_SNDTIMEO, ['sec' => 3, 'usec' => 0]);

        if (!@socket_connect($this->socket, $this->ip, $this->port)) {
            @socket_close($this->socket);
            $this->socket = null;
            return false;
        }

        return $this->performConnection();
    }

    private function connectUDP(): bool
    {
        if (!function_exists('socket_create')) {
            return false;
        }

        $this->socket = @socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        if (!$this->socket) {
            return false;
        }

        @socket_set_option($this->socket, SOL_SOCKET, SO_RCVTIMEO, ['sec' => 3, 'usec' => 0]);
        @socket_set_option($this->socket, SOL_SOCKET, SO_SNDTIMEO, ['sec' => 3, 'usec' => 0]);

        return $this->performConnection();
    }

    private function performConnection(): bool
    {
        $command = $this->createCommand(1000); // CMD_CONNECT
        $reply = $this->sendAndReceive($command);

        if ($reply && strlen($reply) >= 8) {
            $header = unpack('vcmd/vchk/vsession/vreply', substr($reply, 0, 8));
            if (isset($header['cmd'])) {
                $this->session_id = $header['session'];

                // If CMD_ACK_OK (2000) or CMD_CONNECT (1000), connection is ready
                if ($header['cmd'] == 2000 || $header['cmd'] == 1000) {
                    if ($this->comm_key !== 0) {
                        return $this->authenticate();
                    }
                    return true;
                }

                // If CMD_ACK_UNAUTH (2005), session requires CMD_AUTH (1100) handshake packet!
                if ($header['cmd'] == 2005) {
                    if (defined('ZK_DEBUG') && ZK_DEBUG) {
                        echo "   [DEBUG] Machine returned 2005 (UNAUTH). Sending CMD_AUTH (1100) handshake...\n";
                    }
                    return $this->authenticate();
                }
            }
        }

        return false;
    }

    private function authenticate(): bool
    {
        $authKey = $this->makeCommKey($this->comm_key, $this->session_id);
        $command = $this->createCommand(1100, $authKey);
        $reply = $this->sendAndReceive($command);

        if ($reply && strlen($reply) >= 8) {
            $header = unpack('vcmd/vchk/vsession/vreply', substr($reply, 0, 8));
            if (defined('ZK_DEBUG') && ZK_DEBUG) {
                echo "   [DEBUG] CMD_AUTH (1100) Reply Code: " . ($header['cmd'] ?? 'N/A') . "\n";
            }
            return isset($header['cmd']) && $header['cmd'] == 2000;
        }

        if (defined('ZK_DEBUG') && ZK_DEBUG) {
            echo "   [DEBUG] CMD_AUTH (1100) received no response or timeout.\n";
        }

        return false;
    }

    private function makeCommKey(int $key, int $session_id, int $ticks = 50): string
    {
        $k = 0;
        for ($i = 0; $i < 32; $i++) {
            if (($key & (1 << $i)) != 0) {
                $k = ($k << 1) | 1;
            } else {
                $k = $k << 1;
            }
        }

        $k += $session_id;

        $pack = pack('V', $k);
        $arr = array_values(unpack('C4', $pack));

        $arr[0] ^= ord('Z');
        $arr[1] ^= ord('K');
        $arr[2] ^= ord('S');
        $arr[3] ^= ord('O');

        $pack2 = pack('C4', $arr[0], $arr[1], $arr[2], $arr[3]);
        $val = array_values(unpack('v2', $pack2));

        $val[0] ^= $ticks;
        $val[1] ^= $ticks;

        return pack('v2', $val[0], $val[1]);
    }

    public function disableDevice(): bool
    {
        $command = $this->createCommand(1007); // CMD_DISABLEDEVICE
        $reply = $this->sendAndReceive($command);
        if ($reply && strlen($reply) >= 8) {
            $header = unpack('vcmd/vchk/vsession/vreply', substr($reply, 0, 8));
            return isset($header['cmd']) && ($header['cmd'] == 2000 || $header['cmd'] == 1000);
        }
        return false;
    }

    public function enableDevice(): bool
    {
        $command = $this->createCommand(1008); // CMD_ENABLEDEVICE
        $reply = $this->sendAndReceive($command);
        if ($reply && strlen($reply) >= 8) {
            $header = unpack('vcmd/vchk/vsession/vreply', substr($reply, 0, 8));
            return isset($header['cmd']) && ($header['cmd'] == 2000 || $header['cmd'] == 1000);
        }
        return false;
    }

    public function getDeviceStats(): array
    {
        $command = $this->createCommand(1014); // CMD_GET_FREE_SIZES
        $reply = $this->sendAndReceive($command);
        $stats = [];
        if ($reply && strlen($reply) >= 8) {
            $header = unpack('vcmd/vchk/vsession/vreply', substr($reply, 0, 8));
            if (isset($header['cmd']) && $header['cmd'] == 2000 && strlen($reply) >= 28) {
                $payload = substr($reply, 8);
                // Unpack integers from payload
                $data = unpack('V*', $payload);
                if ($data) {
                    $stats = [
                        'admin_count' => $data[1] ?? 0,
                        'user_count' => $data[2] ?? 0,
                        'fps_count' => $data[3] ?? 0,
                        'password_count' => $data[4] ?? 0,
                        'op_log_count' => $data[5] ?? 0,
                        'att_log_count' => $data[6] ?? 0,
                    ];
                }
            }
        }
        return $stats;
    }

    public function disconnect(): void
    {
        if ($this->socket) {
            $this->enableDevice();
            $command = $this->createCommand(1001); // CMD_EXIT
            $this->sendAndReceive($command);
            @socket_close($this->socket);
            $this->socket = null;
        }
    }

    public function getAttendance(): array
    {
        // Flush RAM buffer to log stream by temporarily disabling input
        $this->disableDevice();

        $command = $this->createCommand(13); // CMD_ATT_LOG_RRQ
        $reply = $this->sendAndReceive($command);

        $attendance = [];

        if ($reply && strlen($reply) >= 8) {
            $header = unpack('vcmd/vchk/vsession/vreply', substr($reply, 0, 8));
            if (defined('ZK_DEBUG') && ZK_DEBUG) {
                echo "   [DEBUG] Reply Header CMD: " . ($header['cmd'] ?? 'N/A') . " | Reply Len: " . strlen($reply) . " bytes\n";
            }
            $allData = '';
            if (strlen($reply) > 8) {
                $allData .= substr($reply, 8);
            }

            // Stream additional packets if response is chunked
            $maxRead = 200;
            $chunksRead = 0;
            while ($maxRead > 0) {
                $maxRead--;
                $chunk = $this->receive();
                if (!$chunk || strlen($chunk) == 0) {
                    break;
                }
                $chunksRead++;
                if (strlen($chunk) > 8) {
                    $allData .= substr($chunk, 8);
                }
            }

            $totalLen = strlen($allData);
            if (defined('ZK_DEBUG') && ZK_DEBUG) {
                echo "   [DEBUG] Chunks Read: $chunksRead | Total Data Payload: $totalLen bytes\n";
            }

            // Check TFT 40-byte record format with 12-byte header
            $offset = 12;
            $recordSize = 40;
            if ($totalLen >= $recordSize) {
                // Try 40-byte TFT format
                for ($i = $offset; $i <= $totalLen - $recordSize; $i += $recordSize) {
                    $record = substr($allData, $i, $recordSize);
                    $attendance[] = $this->parseAttendanceRecord($record);
                }

                // Fallback: If 0 records parsed with 12-byte offset, try 0-byte offset (or 16-byte BW format)
                if (count($attendance) === 0 && $totalLen >= 16) {
                    for ($i = 0; $i <= $totalLen - $recordSize; $i += $recordSize) {
                        $record = substr($allData, $i, $recordSize);
                        $attendance[] = $this->parseAttendanceRecord($record);
                    }
                }
            }
        }

        $this->enableDevice();

        return $attendance;
    }

    private function decodeZkTime(int $t): int
    {
        if ($t <= 0) return time();
        $second = $t % 60;
        $t = (int)($t / 60);
        $minute = $t % 60;
        $t = (int)($t / 60);
        $hour = $t % 24;
        $t = (int)($t / 24);
        $day = ($t % 31) + 1;
        $t = (int)($t / 31);
        $month = ($t % 12) + 1;
        $t = (int)($t / 12);
        $year = $t + 2000;
        
        return strtotime(sprintf('%04d-%02d-%02d %02d:%02d:%02d', $year, $month, $day, $hour, $minute, $second)) ?: time();
    }

    private function parseAttendanceRecord(string $record): array
    {
        // 40-byte TFT record format:
        // Bytes 0-1 (2 bytes): index (v)
        // Bytes 2-24 (23 bytes): user_id string (A23)
        // Byte 25 (1 byte): status (C)
        // Byte 26 (1 byte): verify (C)
        // Bytes 27-30 (4 bytes): timestamp (V)
        // Bytes 31-39 (9 bytes): reserved (A9)
        $data = unpack('vindex/A23user_id/Cstatus/Cverify/Vtimestamp/A9reserved', $record);

        $userId = trim($data['user_id'] ?? '');
        $userIdClean = preg_replace('/[\x00-\x1F\x7F]/', '', $userId);
        
        $rawTime = (int) ($data['timestamp'] ?? 0);
        $unixTime = $this->decodeZkTime($rawTime);

        return [
            'user_id' => $userIdClean,
            'timestamp' => $unixTime,
            'status' => (int) ($data['status'] ?? 0),
            'verify' => (int) ($data['verify'] ?? 0),
            'workcode' => 0,
            'date' => date('Y-m-d H:i:s', $unixTime),
            'device' => 'K60/ID',
        ];
    }

    private function createCommand(int $command, string $data = ''): string
    {
        $pkt = pack('vvv', $command, 0, $this->session_id) . pack('v', $this->reply_id) . $data;
        $checksum = $this->calculateChecksum($pkt);
        $pkt = pack('vvv', $command, $checksum, $this->session_id) . pack('v', $this->reply_id) . $data;
        $this->reply_id++;
        return $pkt;
    }

    private function calculateChecksum(string $packet): int
    {
        $length = strlen($packet);
        $checksum = 0;
        for ($i = 0; $i < $length; $i += 2) {
            if ($i == 2) continue; // Skip checksum bytes position
            if ($i + 1 < $length) {
                $val = unpack('v', substr($packet, $i, 2))[1];
            } else {
                $val = ord($packet[$i]);
            }
            $checksum += $val;
            if ($checksum > 0xffff) {
                $checksum = ($checksum & 0xffff) + ($checksum >> 16);
            }
        }
        return ~$checksum & 0xffff;
    }

    private function sendAndReceive(string $packet): ?string
    {
        if (!$this->socket) return null;

        if ($this->use_udp) {
            @socket_sendto($this->socket, $packet, strlen($packet), 0, $this->ip, $this->port);
        } else {
            $tcpHdr = "\x50\x50\x82\x7d" . pack('v', strlen($packet));
            @socket_write($this->socket, $tcpHdr . $packet, strlen($tcpHdr . $packet));
        }

        return $this->receive();
    }

    private function receive(): ?string
    {
        if (!$this->socket) return null;

        $buf = '';
        if ($this->use_udp) {
            $from = '';
            $fromPort = 0;
            @socket_recvfrom($this->socket, $buf, 65535, 0, $from, $fromPort);
        } else {
            $buf = @socket_read($this->socket, 65535);
        }

        return (is_string($buf) && strlen($buf) > 0) ? $buf : null;
    }
}
