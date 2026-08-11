<?php

namespace App\Helpers;

class WebSocketHelper
{
    /**
     * Send real-time event message to a specific employee ID
     */
    public static function sendToUser(int $employeeId, string $type, array $data = []): bool
    {
        $payload = [
            'target_user' => $employeeId,
            'event_data' => array_merge(['type' => $type], $data)
        ];
        return self::pushToDaemon($payload);
    }

    /**
     * Send real-time event messages to multiple employee IDs in a single TCP socket connection
     */
    public static function sendToUsers(array $employeeIds, string $type, array $data = []): bool
    {
        $messages = '';
        foreach ($employeeIds as $employeeId) {
            $payload = [
                'target_user' => (int) $employeeId,
                'event_data' => array_merge(['type' => $type], $data)
            ];
            $messages .= json_encode($payload) . "\n";
        }
        return self::pushMultipleToDaemon($messages);
    }

    /**
     * Broadcast real-time event to all connected users
     */
    public static function broadcast(string $type, array $data = []): bool
    {
        $payload = [
            'target_user' => null,
            'event_data' => array_merge(['type' => $type], $data)
        ];
        return self::pushToDaemon($payload);
    }

    /**
     * Push JSON payload to the WebSocket server local TCP port via loopback socket
     */
    private static function pushToDaemon(array $payload): bool
    {
        $localPort = defined('WS_PORT') ? (WS_PORT + 1) : 6002;
        $host = '127.0.0.1';
        
        try {
            $message = json_encode($payload) . "\n";
            
            $socket = @fsockopen($host, $localPort, $errno, $errstr, 1.0);
            if (!$socket) {
                return false;
            }
            
            fwrite($socket, $message);
            fclose($socket);
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Push bulk string payload (multiple new-line separated JSON events) via single socket connection
     */
    private static function pushMultipleToDaemon(string $messages): bool
    {
        $localPort = defined('WS_PORT') ? (WS_PORT + 1) : 6002;
        $host = '127.0.0.1';
        
        try {
            $socket = @fsockopen($host, $localPort, $errno, $errstr, 1.0);
            if (!$socket) {
                return false;
            }
            
            fwrite($socket, $messages);
            fclose($socket);
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }
}
