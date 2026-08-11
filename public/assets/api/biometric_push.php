<?php

/**
 * ZKTeco ADMS / HTTP Push Receiver Endpoint
 * Accepts live attendance push requests from ZKTeco K60/ID biometric machine.
 */

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';
require_once dirname(__DIR__, 2) . '/config/config.php';

use App\Core\Database;
use App\Helpers\WebSocketHelper;

// Handle ADMS handshake / heartbeat
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Machine heartbeat or registration ping
    header("Content-Type: text/plain");
    echo "OK";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo = Database::connection();
        $rawInput = file_get_contents('php://input');

        // Parse log lines (Format: PIN\tTime\tStatus\tWorkCode)
        $lines = explode("\n", trim($rawInput));
        $processed = 0;

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            $parts = preg_split('/\s+/', $line);
            if (count($parts) >= 2) {
                $user_id = trim($parts[0]);
                $dateTimeStr = trim($parts[1] . ' ' . ($parts[2] ?? ''));
                $timestamp = strtotime($dateTimeStr);

                if (empty($user_id) || !$timestamp) {
                    // Try key-value format (e.g., pin=96&time=2026-07-22 18:00:00)
                    parse_str($line, $parsed);
                    $user_id = $parsed['pin'] ?? $parsed['Userid'] ?? $parsed['user_id'] ?? null;
                    $dateTimeStr = $parsed['time'] ?? $parsed['date'] ?? null;
                    $timestamp = $dateTimeStr ? strtotime($dateTimeStr) : null;
                }

                if (!$user_id || !$timestamp) continue;

                $fullDateTime = date('Y-m-d H:i:s', $timestamp);
                $punchDate = date('Y-m-d', $timestamp);
                $timeStr = date('H:i:s', $timestamp);
                $hour = (int)date('H', $timestamp);

                // Match employee strictly by biometric_id
                $empStmt = $pdo->prepare("SELECT id, shift_id FROM employees WHERE biometric_id = ? AND deleted_at IS NULL LIMIT 1");
                $empStmt->execute([(string)$user_id]);
                $emp = $empStmt->fetch(PDO::FETCH_ASSOC);

                if (!$emp) continue;

                // Shift info if assigned
                $shift = null;
                if (!empty($emp['shift_id'])) {
                    $shiftStmt = $pdo->prepare("SELECT start_time, end_time, grace_time, halfday_hours FROM shifts WHERE id = ? LIMIT 1");
                    $shiftStmt->execute([$emp['shift_id']]);
                    $shift = $shiftStmt->fetch(PDO::FETCH_ASSOC);
                }

                // 1. Check if there is an unclosed attendance record for this employee (open clock_in)
                $openStmt = $pdo->prepare("
                    SELECT id, date, clock_in, is_manual 
                    FROM attendance 
                    WHERE employee_id = ? AND clock_in IS NOT NULL AND clock_out IS NULL 
                    ORDER BY clock_in DESC LIMIT 1
                ");
                $openStmt->execute([$emp['id']]);
                $openAtt = $openStmt->fetch(PDO::FETCH_ASSOC);

                if ($openAtt && (int)($openAtt['is_manual'] ?? 0) === 0) {
                    $inTs = strtotime($openAtt['clock_in']);
                    $diffSecs = $timestamp - $inTs;

                    // If punch is between 2 hours and 20 hours after clock_in, it's a CHECK OUT for that open record!
                    if ($diffSecs >= 7200 && $diffSecs <= 72000) {
                        $hours = floor($diffSecs / 3600);
                        $minutes = floor(($diffSecs % 3600) / 60);
                        $workingHoursStr = "{$hours}h " . str_pad($minutes, 2, '0', STR_PAD_LEFT) . "m";

                        $status = '-';
                        if ($shift && !empty($shift['halfday_hours'])) {
                            $halfdayMinutes = (float)$shift['halfday_hours'] * 60;
                            if (($diffSecs / 60) < $halfdayMinutes) {
                                $status = 'HALF DAY';
                            }
                        }

                        $upd = $pdo->prepare("UPDATE attendance SET clock_out = ?, working_hours = ?, status = CASE WHEN status = '-' THEN ? ELSE status END WHERE id = ?");
                        $upd->execute([$fullDateTime, $workingHoursStr, $status, $openAtt['id']]);
                        $processed++;
                        continue;
                    }
                }

                // 2. Otherwise, calculate logical date for a new clock_in
                $logicalDate = $punchDate;

                if ($shift && !empty($shift['start_time']) && !empty($shift['end_time'])) {
                    if (strtotime($shift['start_time']) > strtotime($shift['end_time'])) {
                        if ($hour < 12) {
                            $logicalDate = date('Y-m-d', strtotime($punchDate . ' -1 day'));
                        }
                    }
                } elseif ($hour < 12) {
                    // For unassigned shifts, check if yesterday has a record needing clock_out
                    $yest = date('Y-m-d', strtotime($punchDate . ' -1 day'));
                    $yestStmt = $pdo->prepare("SELECT id, clock_in, clock_out, is_manual FROM attendance WHERE employee_id = ? AND date = ? LIMIT 1");
                    $yestStmt->execute([$emp['id'], $yest]);
                    $yestAtt = $yestStmt->fetch(PDO::FETCH_ASSOC);

                    if ($yestAtt && !empty($yestAtt['clock_in']) && empty($yestAtt['clock_out']) && (int)($yestAtt['is_manual'] ?? 0) === 0) {
                        $inTs = strtotime($yestAtt['clock_in']);
                        $diffSecs = $timestamp - $inTs;
                        if ($diffSecs >= 7200 && $diffSecs <= 72000) {
                            $hours = floor($diffSecs / 3600);
                            $minutes = floor(($diffSecs % 3600) / 60);
                            $workingHoursStr = "{$hours}h " . str_pad($minutes, 2, '0', STR_PAD_LEFT) . "m";

                            $upd = $pdo->prepare("UPDATE attendance SET clock_out = ?, working_hours = ? WHERE id = ?");
                            $upd->execute([$fullDateTime, $workingHoursStr, $yestAtt['id']]);
                            $processed++;
                            continue;
                        }
                    }
                }

                $status = '-';
                if ($shift && !empty($shift['start_time'])) {
                    $status = 'ON TIME';
                    $shiftStart = strtotime($logicalDate . ' ' . $shift['start_time']);
                    $grace = (int)($shift['grace_time'] ?? 15);
                    if ($timestamp > ($shiftStart + $grace * 60 + 59)) {
                        $status = 'LATE IN';
                    }
                }

                // Insert / Update attendance
                $attStmt = $pdo->prepare("SELECT id, clock_in, clock_out, is_manual FROM attendance WHERE employee_id = ? AND date = ? LIMIT 1");
                $attStmt->execute([$emp['id'], $logicalDate]);
                $att = $attStmt->fetch(PDO::FETCH_ASSOC);

                if ($att && (int)($att['is_manual'] ?? 0) === 1) {
                    continue;
                }

                if (!$att) {
                    $ins = $pdo->prepare("INSERT INTO attendance (employee_id, shift_id, date, clock_in, status, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
                    $ins->execute([$emp['id'], $emp['shift_id'] ?? null, $logicalDate, $fullDateTime, $status]);
                } else if (empty($att['clock_in'])) {
                    $upd = $pdo->prepare("UPDATE attendance SET clock_in = ?, status = ? WHERE id = ?");
                    $upd->execute([$fullDateTime, $status, $att['id']]);
                } else if (empty($att['clock_out'])) {
                    $inTs = strtotime($att['clock_in']);
                    $diffSecs = $timestamp - $inTs;
                    if ($diffSecs >= 7200) {
                        $hours = floor($diffSecs / 3600);
                        $minutes = floor(($diffSecs % 3600) / 60);
                        $workingHoursStr = "{$hours}h " . str_pad($minutes, 2, '0', STR_PAD_LEFT) . "m";

                        $upd = $pdo->prepare("UPDATE attendance SET clock_out = ?, working_hours = ? WHERE id = ?");
                        $upd->execute([$fullDateTime, $workingHoursStr, $att['id']]);
                    }
                }
                $processed++;
            }
        }

        if ($processed > 0) {
            WebSocketHelper::broadcast('attendance_updated');
        }

        header("Content-Type: text/plain");
        echo "OK";
        exit;
    } catch (\Throwable $e) {
        header("Content-Type: text/plain", true, 500);
        echo "ERROR: " . $e->getMessage();
        exit;
    }
}
