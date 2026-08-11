<?php
/**
 * Connected Apps API
 * Handles saving/retrieving third-party app integrations and biometric machine configuration.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$rootDir = dirname(__DIR__, 3);
require_once $rootDir . '/vendor/autoload.php';
require_once $rootDir . '/config/config.php';
require_once $rootDir . '/app/Core/Database.php';

use App\Core\Database;
use App\Helpers\ZKLib;
use App\Helpers\WebSocketHelper;

$pdo = Database::connection();

$userRole = $_SESSION['user_role'] ?? '';
if ($userRole !== 'Admin') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Admin access required.']);
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';
header('Content-Type: application/json');

switch ($action) {
    case 'save_integration':
        $key   = trim($_POST['key'] ?? '');
        $value = trim($_POST['value'] ?? '');

        if ($key === '') {
            echo json_encode(['status' => 'error', 'message' => 'Invalid key.']);
            exit;
        }

        $allowed = ['chatrox_url', 'chatrox_type', 'chatrox_domain', 'chatrox_ip', 'chatrox_port', 'biometric_name', 'biometric_model', 'biometric_ip', 'biometric_port', 'biometric_comm_key', 'biometric_mode', 'biometric_auto_sync', 'biometric_sync_interval'];
        if (!in_array($key, $allowed, true)) {
            echo json_encode(['status' => 'error', 'message' => 'Unknown integration key.']);
            exit;
        }

        $stmt = $pdo->prepare(
            "INSERT INTO settings (meta_key, meta_value) VALUES (?, ?)
             ON DUPLICATE KEY UPDATE meta_value = VALUES(meta_value)"
        );
        $stmt->execute([$key, $value]);

        echo json_encode(['status' => 'success', 'message' => 'Integration saved.']);
        break;

    case 'save_biometric_settings':
        $name     = trim($_POST['biometric_name'] ?? 'ZKTeco Attendance Machine');
        $model    = trim($_POST['biometric_model'] ?? 'ZKTeco K60/ID');
        $ip       = trim($_POST['biometric_ip'] ?? '');
        $port     = (int)($_POST['biometric_port'] ?? 4370);
        $commKey  = (int)($_POST['biometric_comm_key'] ?? 0);
        $mode     = trim($_POST['biometric_mode'] ?? 'UDP');
        $autoSync = trim($_POST['biometric_auto_sync'] ?? '1');
        $interval = (int)($_POST['biometric_sync_interval'] ?? 10);

        if (empty($ip)) {
            echo json_encode(['status' => 'error', 'message' => 'Device IP address is required.']);
            exit;
        }

        $settingsMap = [
            'biometric_name'          => $name,
            'biometric_model'         => $model,
            'biometric_ip'            => $ip,
            'biometric_port'          => $port,
            'biometric_comm_key'      => $commKey,
            'biometric_mode'          => $mode,
            'biometric_auto_sync'     => $autoSync,
            'biometric_sync_interval' => $interval
        ];

        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare(
                "INSERT INTO settings (meta_key, meta_value) VALUES (?, ?)
                 ON DUPLICATE KEY UPDATE meta_value = VALUES(meta_value)"
            );
            foreach ($settingsMap as $k => $v) {
                $stmt->execute([$k, (string)$v]);
            }
            $pdo->commit();

            echo json_encode(['status' => 'success', 'message' => 'Biometric machine configuration saved successfully.']);
        } catch (\Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            echo json_encode(['status' => 'error', 'message' => 'Failed to save settings: ' . $e->getMessage()]);
        }
        break;

    case 'disconnect_biometric':
        try {
            $stmt = $pdo->prepare("UPDATE settings SET meta_value = '' WHERE meta_key = 'biometric_ip'");
            $stmt->execute();
            $stmt2 = $pdo->prepare("UPDATE settings SET meta_value = '0' WHERE meta_key = 'biometric_auto_sync'");
            $stmt2->execute();
            echo json_encode(['status' => 'success', 'message' => 'Biometric machine disconnected.']);
        } catch (\Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'Failed to disconnect machine: ' . $e->getMessage()]);
        }
        break;

    case 'test_biometric_connection':
        $ip      = trim($_POST['biometric_ip'] ?? '');
        $port    = (int)($_POST['biometric_port'] ?? 4370);
        $commKey = (int)($_POST['biometric_comm_key'] ?? 0);
        $mode    = strtoupper(trim($_POST['biometric_mode'] ?? 'UDP'));

        if (empty($ip)) {
            $stmt = $pdo->prepare("SELECT meta_value FROM settings WHERE meta_key = 'biometric_ip' LIMIT 1");
            $stmt->execute();
            $ip = trim((string)$stmt->fetchColumn());
        }

        if (empty($ip)) {
            echo json_encode(['status' => 'error', 'message' => 'Please enter a valid Device IP Address to test connection.']);
            exit;
        }

        try {
            $useUdp = ($mode !== 'TCP');
            $zk = new ZKLib($ip, $port, $useUdp, $commKey);

            $connected = $zk->connect();
            if (!$connected && $useUdp) {
                // Fallback to TCP attempt
                $zk = new ZKLib($ip, $port, false, $commKey);
                $connected = $zk->connect();
                if ($connected) $mode = 'TCP (Fallback)';
            }

            if (!$connected) {
                echo json_encode([
                    'status' => 'error',
                    'message' => "Could not establish connection with biometric machine at {$ip}:{$port}. Please check IP, port, or network cable."
                ]);
                exit;
            }

            $logs = $zk->getAttendance();
            $zk->disconnect();
            $logCount = is_array($logs) ? count($logs) : 0;

            echo json_encode([
                'status' => 'success',
                'message' => "Successfully connected to biometric machine at {$ip}:{$port} ({$mode})!",
                'data' => [
                    'ip' => $ip,
                    'port' => $port,
                    'mode' => $mode,
                    'log_count' => $logCount
                ]
            ]);
        } catch (\Throwable $e) {
            echo json_encode(['status' => 'error', 'message' => 'Connection test failed: ' . $e->getMessage()]);
        }
        break;

    case 'trigger_biometric_sync':
        $scriptPath = $rootDir . '/bin/biometric-sync.php';
        if (!file_exists($scriptPath)) {
            echo json_encode(['status' => 'error', 'message' => 'Sync script not found.']);
            exit;
        }

        exec("php " . escapeshellarg($scriptPath) . " 2>&1", $outputArr, $returnCode);
        $outputStr = implode("\n", $outputArr);

        if ($returnCode === 0 || strpos($outputStr, 'Sync complete') !== false) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Attendance machine sync executed successfully!',
                'output' => $outputStr
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Machine sync failed.',
                'output' => $outputStr
            ]);
        }
        break;

    case 'get_integrations':
        $stmt = $pdo->query(
            "SELECT meta_key, meta_value FROM settings
             WHERE meta_key IN ('chatrox_url', 'biometric_name', 'biometric_model', 'biometric_ip', 'biometric_port', 'biometric_comm_key', 'biometric_mode', 'biometric_auto_sync', 'biometric_sync_interval')"
        );
        $rows = $stmt ? $stmt->fetchAll(\PDO::FETCH_KEY_PAIR) : [];
        echo json_encode(['status' => 'success', 'data' => $rows]);
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Unknown action.']);
}
