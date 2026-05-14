<?php
// admin/assets/api/leave_status_handler.php
require_once __DIR__ . '/../../../includes/db_connect.php';
require_once __DIR__ . '/../../../includes/auth_helper.php';
require_once __DIR__ . '/../../../includes/api/notification_handler.php';
require_once __DIR__ . '/../../../includes/api/activity_helper.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isLoggedIn() || !isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['Admin', 'HR'])) {
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
        exit;
    }

    $leave_id = $_POST['leave_id'] ?? '';
    $action = $_POST['action'] ?? ''; // 'Approve' or 'Reject'
    $admin_note = $_POST['admin_note'] ?? '';

    if (empty($leave_id) || !in_array($action, ['Approve', 'Reject', 'Update'])) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid parameters.']);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("SELECT id, status, employee_id FROM leave_requests WHERE id = ?");
        $stmt->execute([$leave_id]);
        $req = $stmt->fetch();

        if (!$req) {
            echo json_encode(['status' => 'error', 'message' => 'Leave request not found.']);
            exit;
        }

        if ($action === 'Update') {
            $up_stmt = $pdo->prepare("UPDATE leave_requests SET admin_note = ? WHERE id = ?");
            $up_stmt->execute([$admin_note, $leave_id]);
            echo json_encode(['status' => 'success', 'message' => 'Admin remarks updated successfully.']);
            exit;
        } else {
            $new_status = ($action === 'Approve') ? 'Approved' : 'Rejected';
            $up_stmt = $pdo->prepare("UPDATE leave_requests SET status = ?, admin_note = ? WHERE id = ?");
            $up_stmt->execute([$new_status, $admin_note, $leave_id]);
            
            // [TRIGGER] Notify Employee
            $emp_id = $req['employee_id'];
            $msg = "Your leave request has been $new_status.";
            if (!empty($admin_note)) {
                $msg .= " Remarks: " . substr($admin_note, 0, 50) . "...";
            }
            addNotification([$emp_id], "Leave Request $new_status", $msg, "leave-management.php", "Leave", $_SESSION['user_id']);

            // [LOG]
            $admin_id = $_SESSION['user_id'] ?? 0;
            $e_stmt = $pdo->prepare("SELECT e.first_name, e.last_name, lt.name as leave_type FROM leave_requests lr JOIN employees e ON lr.employee_id = e.id JOIN leave_types lt ON lr.leave_type_id = lt.id WHERE lr.id = ?");
            $e_stmt->execute([$leave_id]);
            $e_info = $e_stmt->fetch();
            $e_name = ($e_info['first_name'] ?? 'Unknown') . ' ' . ($e_info['last_name'] ?? '');
            $l_type = $e_info['leave_type'] ?? 'Leave';

            $logAction = ($new_status === 'Approved') ? "Approved Leave" : "Rejected Leave";
            $logMsg = ($new_status === 'Approved') ? "Formally approved the $l_type request for team member: $e_name" : "Declined the $l_type request for team member: $e_name";
            logActivity($admin_id, $logAction, "Leave Management", $logMsg);

            $statusMsg = ($new_status === 'Approved') ? 'approved' : 'rejected';
            echo json_encode(['status' => 'success', 'message' => 'Leave request ' . $statusMsg . ' successfully.']);
            exit;
        }

    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'A server error occurred. Please try again.']);
        exit;
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit;
}
?>
