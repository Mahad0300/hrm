<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once dirname(__DIR__) . '/config/config.php';

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use App\Core\WebSocketHandler;
use React\EventLoop\Factory;
use React\Socket\Server as ReactSocket;

echo "Starting WebSocket server...\n";

$loop = Factory::create();
$webSocketHandler = new WebSocketHandler();

// 1. WebSockets Server listening on 0.0.0.0:WS_PORT (e.g. 6001)
$wsSocket = new ReactSocket('0.0.0.0:' . WS_PORT, $loop);
$wsServer = new IoServer(
    new HttpServer(
        new WsServer(
            $webSocketHandler
        )
    ),
    $wsSocket,
    $loop
);

// 2. Secondary Local Loopback Socket Server for Apache to push events
// We will listen on 127.0.0.1:(WS_PORT + 1) -> e.g. 6002
$localPort = WS_PORT + 1;
$localSocket = new ReactSocket('127.0.0.1:' . $localPort, $loop);

$localSocket->on('connection', function (\React\Socket\ConnectionInterface $connection) use ($webSocketHandler) {
    $buffer = '';
    
    $connection->on('data', function ($data) use (&$buffer, $connection, $webSocketHandler) {
        $buffer .= $data;
        
        if (str_contains($buffer, "\n")) {
            $lines = explode("\n", $buffer);
            $buffer = array_pop($lines);
            
            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line)) {
                    continue;
                }
                
                $payload = json_decode($line, true);
                if ($payload) {
                    $targetUser = isset($payload['target_user']) ? (int)$payload['target_user'] : null;
                    $eventData = isset($payload['event_data']) ? $payload['event_data'] : [];
                    
                    if ($targetUser !== null && $targetUser > 0) {
                        echo "Routing local event to User ID {$targetUser}: " . json_encode($eventData) . "\n";
                        $webSocketHandler->sendToUser($targetUser, $eventData);
                    } else {
                        echo "Broadcasting local event to all clients: " . json_encode($eventData) . "\n";
                        $webSocketHandler->broadcast($eventData);
                    }
                }
            }
            
            $connection->end();
        }
    });
});

echo "WebSocket Server running on ws://0.0.0.0:" . WS_PORT . "\n";
echo "Internal Broadcast Listener running on tcp://127.0.0.1:" . $localPort . "\n";

$loop->run();
