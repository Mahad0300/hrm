<?php

namespace App\Core;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use SplObjectStorage;

class WebSocketHandler implements MessageComponentInterface
{
    /** @var SplObjectStorage */
    protected SplObjectStorage $clients;

    /** @var array Mapping of employee_id => [resourceId => Connection] */
    protected array $userConnections = [];

    public function __construct()
    {
        $this->clients = new SplObjectStorage();
    }

    public function onOpen(ConnectionInterface $conn): void
    {
        $this->clients->attach($conn);
        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg): void
    {
        echo "Received message from {$from->resourceId}: {$msg}\n";
        
        try {
            $data = json_decode($msg, true);
            if (!$data) {
                return;
            }

            $action = $data['action'] ?? $data['type'] ?? '';
            if ($action === 'register' && isset($data['employee_id'])) {
                $employeeId = (int)$data['employee_id'];
                
                if (!isset($this->userConnections[$employeeId])) {
                    $this->userConnections[$employeeId] = [];
                }
                
                $this->userConnections[$employeeId][$from->resourceId] = $from;
                $from->employee_id = $employeeId;
                
                echo "Connection {$from->resourceId} registered to Employee ID {$employeeId}\n";
                $from->send(json_encode(['type' => 'registered', 'status' => 'success']));
            } elseif ($action === 'sheet_join' && isset($data['sheetId'])) {
                $from->sheet_id = (string)$data['sheetId'];
                echo "Connection {$from->resourceId} joined Sheet Channel: {$data['sheetId']}\n";
            } elseif ($action === 'sheet_broadcast' && isset($data['sheetId'])) {
                $sheetId = (string)$data['sheetId'];
                $payload = json_encode([
                    'type' => 'sheet_event',
                    'sheetId' => $sheetId,
                    'senderResourceId' => $from->resourceId,
                    'event' => $data['event'] ?? '',
                    'data' => $data['data'] ?? []
                ]);
                foreach ($this->clients as $client) {
                    if ($client !== $from && isset($client->sheet_id) && $client->sheet_id === $sheetId) {
                        try {
                            $client->send($payload);
                        } catch (\Throwable $e) {
                            echo "Error broadcasting sheet message: " . $e->getMessage() . "\n";
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            echo "Error processing message: " . $e->getMessage() . "\n";
        }
    }

    public function onClose(ConnectionInterface $conn): void
    {
        $this->clients->detach($conn);
        
        if (isset($conn->employee_id) && isset($this->userConnections[$conn->employee_id])) {
            unset($this->userConnections[$conn->employee_id][$conn->resourceId]);
            if (empty($this->userConnections[$conn->employee_id])) {
                unset($this->userConnections[$conn->employee_id]);
            }
            echo "Connection {$conn->resourceId} detached from Employee ID {$conn->employee_id}\n";
        } else {
            echo "Connection {$conn->resourceId} closed.\n";
        }
    }

    public function onError(ConnectionInterface $conn, \Exception $e): void
    {
        echo "An error has occurred: {$e->getMessage()}\n";
        $conn->close();
    }

    /**
     * Send event payload to a specific user (all their active connections)
     */
    public function sendToUser(int $employeeId, array $payload): void
    {
        if (!isset($this->userConnections[$employeeId])) {
            return;
        }

        $message = json_encode($payload);
        foreach ($this->userConnections[$employeeId] as $conn) {
            try {
                $conn->send($message);
            } catch (\Throwable $e) {
                echo "Error sending message to connection {$conn->resourceId}: " . $e->getMessage() . "\n";
            }
        }
    }

    /**
     * Broadcast to all connected clients
     */
    public function broadcast(array $payload): void
    {
        $message = json_encode($payload);
        foreach ($this->clients as $client) {
            try {
                $client->send($message);
            } catch (\Throwable $e) {
                echo "Error sending broadcast: " . $e->getMessage() . "\n";
            }
        }
    }
}
