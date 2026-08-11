<?php

/**
 * Biometric Attendance Machine Sync Script (ZKTeco K60/ID)
 * Fetches punch logs from configured biometric machine and syncs into MySQL `attendance` table.
 */

require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once dirname(__DIR__) . '/config/config.php';

use App\Core\Database;
use App\Helpers\ZKLib;
use App\Helpers\WebSocketHelper;

echo "=== Biometric Attendance Machine Sync Engine ===\n";

// Parse CLI flags
$isDaemon = in_array('--daemon', $argv) || in_array('-d', $argv);
$force = in_array('--force', $argv) || in_array('-f', $argv);

$intervalIndex = array_search('--interval', $argv);
if ($intervalIndex === false) {
    $intervalIndex = array_search('-i', $argv);
}
$interval = 10; // Default 10 seconds
if ($intervalIndex !== false && isset($argv[$intervalIndex + 1])) {
    $interval = max(1, (int)$argv[$intervalIndex + 1]);
}

$pdo = Database::connection();

// Fetch settings dynamically
$stmt = $pdo->prepare("SELECT meta_key, meta_value FROM settings WHERE meta_key LIKE 'biometric_%'");
$stmt->execute();
$bioSettings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

$ip       = trim($bioSettings['biometric_ip'] ?? '');
$port     = (int)($bioSettings['biometric_port'] ?? 4370);
$commKey  = (int)($bioSettings['biometric_comm_key'] ?? 0);
$mode     = strtoupper(trim($bioSettings['biometric_mode'] ?? 'UDP'));
$autoSync = (int)($bioSettings['biometric_auto_sync'] ?? 1);
$intervalSetting = (int)($bioSettings['biometric_sync_interval'] ?? 10);

if (empty($ip)) {
    echo "❌ Biometric machine IP is not configured in settings. Please configure machine in Admin Settings.\n";
    exit;
}

if ($intervalSetting > 0 && $intervalIndex === false) {
    $interval = $intervalSetting;
}

function doSync($pdo, $ip, $port, $commKey, $mode, $force) {
    try {
        echo "[" . date('Y-m-d H:i:s') . "] Connecting to Biometric Device {$ip}:{$port} ({$mode})...\n";

        $useUdpPrimary = ($mode !== 'TCP');
        $zk = new ZKLib($ip, $port, $useUdpPrimary, $commKey);
        if (!$zk->connect()) {
            // Try opposite protocol mode as fallback
            $zk = new ZKLib($ip, $port, !$useUdpPrimary, $commKey);
            if (!$zk->connect()) {
                echo "❌ Failed to connect to biometric machine {$ip}:{$port}.\n";
                return false;
            }
        }

        $logs = $zk->getAttendance();
        $zk->disconnect();

        $totalLogs = count($logs);
        if ($totalLogs === 0) {
            echo "No logs found on machine.\n";
            return true;
        }

        // Cache active employees strictly by biometric ID (or DB ID only if biometric_id is empty)
        $empRows = $pdo->query("SELECT id, biometric_id, shift_id FROM employees WHERE deleted_at IS NULL")->fetchAll(PDO::FETCH_ASSOC);
        $empCache = [];
        foreach ($empRows as $er) {
            if (!empty($er['biometric_id'])) {
                $bioStr = trim((string)$er['biometric_id']);
                $empCache[$bioStr] = $er;
                $empCache[(string)(int)$bioStr] = $er;
            }
            // NO DB ID fallback - NEVER map by employee table id
        }

        // Cache shifts
        $shiftRows = $pdo->query("SELECT id, start_time, end_time, grace_time, halfday_hours FROM shifts WHERE deleted_at IS NULL")->fetchAll(PDO::FETCH_ASSOC);
        $shiftCache = [];
        foreach ($shiftRows as $sr) {
            $shiftCache[(int)$sr['id']] = $sr;
        }

        // Group punches by employee ID and logical date
        $groupedLogs = [];
        $maxTimestamp = 0;

        foreach ($logs as $log) {
            $raw_id = trim((string)$log['user_id']);
            $timestamp = (int)$log['timestamp'];
            if (empty($raw_id) || $timestamp <= 0) continue;

            if ($timestamp > $maxTimestamp) {
                $maxTimestamp = $timestamp;
            }

            $int_id = (string)(int)$raw_id;
            $nozero_id = ltrim($raw_id, '0');
            $emp = $empCache[$raw_id] ?? $empCache[$int_id] ?? $empCache[$nozero_id] ?? null;
            if (!$emp) continue;

            $shift = $shiftCache[(int)($emp['shift_id'] ?? 0)] ?? null;

            $punchDate = date('Y-m-d', $timestamp);
            $logicalDate = $punchDate;
            $hour = (int)date('H', $timestamp);

            if ($shift && !empty($shift['start_time']) && !empty($shift['end_time'])) {
                if (strtotime($shift['start_time']) > strtotime($shift['end_time'])) {
                    // Overnight shift logic: morning punches (< 12:00 PM) belong to previous day's shift
                    if ($hour < 12) {
                        $logicalDate = date('Y-m-d', strtotime($punchDate . ' -1 day'));
                    }
                }
            } else {
                // If NO shift is assigned (or unassigned/flexible shift):
                // Early morning punches (< 12:00 PM) belong to the previous day's night shift!
                if ($hour < 12) {
                    $logicalDate = date('Y-m-d', strtotime($punchDate . ' -1 day'));
                }
            }

            $groupedLogs[$emp['id']][$logicalDate][] = [
                'timestamp' => $timestamp,
                'status'    => (int)($log['status'] ?? 0),
                'datetime'  => date('Y-m-d H:i:s', $timestamp),
                'shift'     => $shift
            ];
        }

        if (empty($groupedLogs)) {
            echo "No valid employee punches to process.\n";
            return true;
        }

        $insertedCount = 0;
        $updatedCount = 0;

        // Process grouped logs per employee per logical date
        foreach ($groupedLogs as $empId => $dates) {
            foreach ($dates as $logicalDate => $punches) {
                // Sort punches chronologically
                usort($punches, function ($a, $b) {
                    return $a['timestamp'] <=> $b['timestamp'];
                });

                $firstPunch = $punches[0];
                $clockIn = $firstPunch['datetime'];
                $shift = $firstPunch['shift'];
                $clockOut = null;
                $workingHoursStr = null;
                $currentlyCheckedIn = true;

                // Find valid check-out punches: MUST occur at least 4 hours (14400 seconds) after check-in
                $outPunches = [];
                foreach ($punches as $p) {
                    if (($p['timestamp'] - $firstPunch['timestamp']) >= 14400) {
                        $outPunches[] = $p;
                    }
                }

                if (!empty($outPunches)) {
                    $lastPunch = end($outPunches);
                    $clockOut = $lastPunch['datetime'];
                    $currentlyCheckedIn = false;

                    $totalSeconds = $lastPunch['timestamp'] - $firstPunch['timestamp'];
                    $hours = floor($totalSeconds / 3600);
                    $minutes = floor(($totalSeconds % 3600) / 60);
                    $workingHoursStr = "{$hours}h " . str_pad($minutes, 2, '0', STR_PAD_LEFT) . "m";
                }

                // Shift status logic based on first check-in
                $status = 'ON TIME';
                if ($shift && !empty($shift['start_time'])) {
                    $shiftStart = strtotime($logicalDate . ' ' . $shift['start_time']);
                    $graceMinutes = (int)($shift['grace_time'] ?? 15);
                    if ($firstPunch['timestamp'] > ($shiftStart + ($graceMinutes * 60) + 59)) {
                        $status = 'LATE IN';
                    }
                }

                // Half day status logic ONLY IF employee has checked out and working hours exist
                if (!$currentlyCheckedIn && $workingHoursStr && $shift && !empty($shift['halfday_hours'])) {
                    if (preg_match('/(\d+)h\s*(\d+)m/', $workingHoursStr, $whm)) {
                        $workedMinutes = ((int)$whm[1] * 60) + (int)$whm[2];
                        $halfdayMinutes = (float)$shift['halfday_hours'] * 60;
                        if ($workedMinutes < $halfdayMinutes) {
                            $status = 'HALF DAY';
                        }
                    }
                }

                // Check if HR manually updated this record - protect manual edits from machine overwrite
                $chkStmt = $pdo->prepare("SELECT is_manual FROM attendance WHERE employee_id = ? AND date = ? LIMIT 1");
                $chkStmt->execute([$empId, $logicalDate]);
                $existingAtt = $chkStmt->fetch(PDO::FETCH_ASSOC);

                if ($existingAtt && (int)$existingAtt['is_manual'] === 1) {
                    // HR manually modified this attendance - DO NOT OVERWRITE!
                    continue;
                }

                // Atomic upsert: INSERT or UPDATE in one statement
                $upsertStmt = $pdo->prepare("
                    INSERT INTO attendance (employee_id, shift_id, date, clock_in, clock_out, working_hours, status, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
                    ON DUPLICATE KEY UPDATE
                        shift_id = VALUES(shift_id),
                        clock_in = VALUES(clock_in),
                        clock_out = VALUES(clock_out),
                        working_hours = VALUES(working_hours),
                        status = VALUES(status)
                ");
                $upsertStmt->execute([
                    $empId,
                    $shift['id'] ?? null,
                    $logicalDate,
                    $clockIn,
                    $clockOut,
                    $workingHoursStr,
                    $status
                ]);
                if ($upsertStmt->rowCount() === 1) {
                    $insertedCount++;
                } else {
                    $updatedCount++;
                }
            }
        }

        echo "✅ Sync complete! New: {$insertedCount}, Updated: {$updatedCount}.\n";

        // Save last sync time setting
        if ($maxTimestamp > 0) {
            $setStmt = $pdo->prepare("INSERT INTO settings (meta_key, meta_value) VALUES ('last_biometric_sync_time', ?) ON DUPLICATE KEY UPDATE meta_value = VALUES(meta_value)");
            $setStmt->execute([(string)$maxTimestamp]);
        }

        // Broadcast WebSocket update to frontends
        if ($insertedCount > 0) {
            WebSocketHelper::broadcast('attendance_updated', [
                'message' => "Biometric machine sync completed: {$insertedCount} check-ins processed."
            ]);
            echo "📡 Broadcasted WebSocket refresh to dashboards.\n";
        }

        return true;
    } catch (\Throwable $e) {
        echo "❌ Sync Error: " . $e->getMessage() . "\n";
        return false;
    }
}

if ($isDaemon) {
    echo "Starting in DAEMON mode (polling every {$interval} seconds). Press Ctrl+C to stop.\n";
    while (true) {
        if ($autoSync === 1) {
            doSync($pdo, $ip, $port, $commKey, $mode, $force);
        } else {
            echo "[" . date('Y-m-d H:i:s') . "] Auto-sync is currently disabled in settings.\n";
        }
        sleep($interval);
    }
} else {
    doSync($pdo, $ip, $port, $commKey, $mode, $force);
}
