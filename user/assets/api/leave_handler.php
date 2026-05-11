<?php
// user/assets/api/leave_handler.php
require_once '../../../includes/db_connect.php';
require_once '../../../includes/auth_helper.php';
require_once '../../../includes/api/notification_handler.php';
require_once '../../../includes/api/activity_helper.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$employee_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $leave_type_name = $_POST['leave_type'] ?? '';
    $date_from = $_POST['date_from'] ?? '';
    $date_to = $_POST['date_to'] ?? '';
    $reason = $_POST['reason'] ?? '';
    
    if (empty($leave_type_name) || empty($date_from) || empty($date_to) || empty($reason)) {
        echo json_encode(['status' => 'error', 'message' => 'All required fields must be filled.']);
        exit;
    }

    try {
        // Resolve Leave Type ID
        $stmt_lt = $pdo->prepare("SELECT id FROM leave_types WHERE name = ?");
        $stmt_lt->execute([$leave_type_name]);
        $lt = $stmt_lt->fetch();
        
        if (!$lt) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid leave type.']);
            exit;
        }
        $leave_type_id = $lt['id'];

        $document_path = null;

        // Handle File Upload
        if (isset($_FILES['document']) && $_FILES['document']['error'] === UPLOAD_ERR_OK) {
            $allowed_exts = ['pdf', 'png', 'jpg', 'jpeg'];
            $file_name = $_FILES['document']['name'];
            $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            
            if (!in_array($ext, $allowed_exts)) {
                echo json_encode(['status' => 'error', 'message' => 'Invalid file format. Only PDF, JPG, and PNG are allowed.']);
                exit;
            }

            $upload_dir = '../../../uploads/leaves/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            $filename = 'leave_' . $employee_id . '_' . time() . '.' . $ext;
            $destination = $upload_dir . $filename;

            if (move_uploaded_file($_FILES['document']['tmp_name'], $destination)) {
                $document_path = 'uploads/leaves/' . $filename;
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to upload document.']);
                exit;
            }
        }

        $action = $_POST['action'] ?? '';
        $leave_id = $_POST['leave_id'] ?? '';

        if ($action === 'edit' && !empty($leave_id)) {
            // Verify ownership and status
            $check_stmt = $pdo->prepare("SELECT document_path FROM leave_requests WHERE id = ? AND employee_id = ? AND status = 'Pending'");
            $check_stmt->execute([$leave_id, $employee_id]);
            $existing_req = $check_stmt->fetch();
            
            if (!$existing_req) {
                // If we uploaded a new file but record not found, delete it immediately
                if ($document_path) {
                    @unlink('../../../' . $document_path);
                }
                echo json_encode(['status' => 'error', 'message' => 'Invalid leave request or it cannot be edited anymore.']);
                exit;
            }

            // Cleanup logic: If nwe document is uploaded, delete the old one
            if ($document_path) {
                if (!empty($existing_req['document_path'])) {
                    $old_file_path = '../../../' . $existing_req['document_path'];
                    if (file_exists($old_file_path)) {
                        @unlink($old_file_path);
                    }
                }
            } else {
                // Keep old document if no new one provided
                $document_path = $existing_req['document_path'];
            }

            $up_stmt = $pdo->prepare("
                UPDATE leave_requests 
                SET leave_type_id = ?, start_date = ?, end_date = ?, reason = ?, document_path = ?
                WHERE id = ?
            ");
            $up_stmt->execute([
                $leave_type_id,
                $date_from,
                $date_to,
                $reason,
                $document_path,
                $leave_id
            ]);

            // [LOG]
            $f_from = date('M d, Y', strtotime($date_from));
            $f_to = date('M d, Y', strtotime($date_to));
            logActivity($employee_id, "Updated Leave Request", "Leave Management", "Updated $leave_type_name request for the period: $f_from to $f_to");

            echo json_encode(['status' => 'success', 'message' => 'Leave request updated successfully.']);
            exit;

        } else {
            // Insert Request
            $stmt = $pdo->prepare("
                INSERT INTO leave_requests (employee_id, leave_type_id, start_date, end_date, reason, document_path, status) 
                VALUES (?, ?, ?, ?, ?, ?, 'Pending')
            ");
            
            $stmt->execute([
                $employee_id,
                $leave_type_id,
                $date_from,
                $date_to,
                $reason,
                $document_path
            ]);

            // [LOG]
            $f_from = date('M d, Y', strtotime($date_from));
            $f_to = date('M d, Y', strtotime($date_to));
            logActivity($employee_id, "Submitted Leave Request", "Leave Management", "Applied for $leave_type_name from $f_from to $f_to");

            // [TRIGGER] Notify Admin/HR
            $user_name = $_SESSION['user_name'] ?? 'An employee';
            $admin_stmt = $pdo->query("SELECT id FROM employees WHERE role IN ('Admin', 'HR') AND deleted_at IS NULL");
            $admin_ids = $admin_stmt->fetchAll(PDO::FETCH_COLUMN);
            
            if (!empty($admin_ids)) {
                $f_from = date('d M, Y', strtotime($date_from));
                $f_to = date('d M, Y', strtotime($date_to));
                $msg = "$user_name has submitted a new leave request ($leave_type_name, From $f_from to $f_to). Awaiting your approval.";
                addNotification($admin_ids, "New Leave Request Submitted", $msg, "leave-management.php", "Leave", $employee_id);
            }

            echo json_encode(['status' => 'success', 'message' => 'Leave request submitted successfully.']);
            exit;
        }

    } catch (PDOException $e) {
        // If DB fails, remove the just-uploaded file
        if ($document_path) {
            @unlink('../../../' . $document_path);
        }
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
?>
