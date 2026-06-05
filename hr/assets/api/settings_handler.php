<?php
// admin/assets/api/settings_handler.php
header('Content-Type: application/json');

require_once dirname(__DIR__, 3) . '/includes/db_connect.php';
require_once dirname(__DIR__, 3) . '/includes/auth_helper.php';

if (!isLoggedIn() || !in_array($_SESSION['user_role'], ['Admin', 'HR'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
    exit;
}

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'save_payroll_cycle':
        handleSavePayrollCycle($pdo);
        break;
    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid action.']);
        break;
}

function handleSavePayrollCycle($pdo) {
    $data = json_decode(file_get_contents('php://input'), true);
    $start_day = (int)($data['start_day'] ?? 21);
    $end_day = (int)($data['end_day'] ?? 20);

    if ($start_day < 1 || $start_day > 31 || $end_day < 1 || $end_day > 31) {
        echo json_encode(['status' => 'error', 'message' => 'Days must be between 1 and 31.']);
        return;
    }

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("INSERT INTO settings (meta_key, meta_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE meta_value = VALUES(meta_value)");
        
        $stmt->execute(['payroll_start_day', $start_day]);
        $stmt->execute(['payroll_end_day', $end_day]);

        $pdo->commit();
        echo json_encode(['status' => 'success', 'message' => 'Payroll cycle settings updated successfully.']);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['status' => 'error', 'message' => 'A server error occurred. Please try again.']);
    }
}
