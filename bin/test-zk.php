<?php
require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once dirname(__DIR__) . '/config/config.php';

use App\Core\Database;
use App\Helpers\ZKLib;

define('ZK_DEBUG', true);

$pdo = Database::connection();
$stmt = $pdo->prepare("SELECT meta_key, meta_value FROM settings WHERE meta_key LIKE 'biometric_%'");
$stmt->execute();
$bioSettings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

$dbIp = trim($bioSettings['biometric_ip'] ?? '');
$ip   = !empty($argv[1]) ? trim($argv[1]) : $dbIp;
$port = (int)($bioSettings['biometric_port'] ?? 4370);

if (empty($ip)) {
    echo "❌ No machine IP configured. Run with IP argument (e.g. php bin/test-zk.php 192.168.1.200) or configure in Admin Settings.\n";
    exit;
}

echo "=== ZKTeco K60 Connection Test (Comm Key: 0) ===\n";
echo "Target IP: $ip:$port\n\n";

$testKeys = [0, 786];

foreach ($testKeys as $commKey) {
    echo "--- Testing Comm Key: {$commKey} ---\n";
    $zk = new ZKLib($ip, $port, true, $commKey);

    if (!$zk->connect()) {
        echo "❌ Connection failed with Comm Key: {$commKey}\n\n";
        continue;
    }

    echo "🎉 SUCCESS! Connected to biometric machine with Comm Key: {$commKey}!\n";

    $stats = $zk->getDeviceStats();
    if (!empty($stats)) {
        echo "📊 Machine Hardware Stats:\n";
        echo "   - Registered Users: " . ($stats['user_count'] ?? 'N/A') . "\n";
        echo "   - Fingerprints: " . ($stats['fps_count'] ?? 'N/A') . "\n";
        echo "   - Attendance Logs in Memory: " . ($stats['att_log_count'] ?? 'N/A') . "\n";
    }

    echo "\nFetching attendance logs from machine...\n";
    $logs = $zk->getAttendance();
    $zk->disconnect();

    echo "Fetched " . count($logs) . " attendance records from machine!\n";
    if (count($logs) > 0) {
        echo "🎉 SUCCESS! First 3 sample logs:\n";
        for ($i = 0; $i < min(3, count($logs)); $i++) {
            echo "   Log #{$i}: User ID: {$logs[$i]['user_id']} | Date: {$logs[$i]['date']} | Status: {$logs[$i]['status']}\n";
        }
    }
    break;
}






