<?php
require_once __DIR__ . '/bootstrap.php';
header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if (!isLoggedIn()) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
    exit;
}

// 1. Guard against non-Admin/HR users performing modify actions
if (in_array($action, ['save_policy', 'delete_policy'], true)) {
    if (!in_array($_SESSION['user_role'] ?? '', ['Admin', 'HR'], true)) {
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
        exit;
    }
}

// 2. Guard via HRPermissions system for HR portal users
hrGuardApiRequest($pdo, $action, basename(__FILE__));

$user_id = $_SESSION['user_id'] ?? 1; // Fallback to 1 if not set

function notifyEmployeesAboutPolicy($title, $sender_id) {
    global $pdo;

    $stmt = $pdo->prepare("SELECT id FROM employees WHERE role = 'Employee' AND status = 'Active' AND deleted_at IS NULL AND id != ?");
    $stmt->execute([$sender_id]);
    $recipients = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (empty($recipients)) {
        return;
    }

    $message = "New company policy available: $title. Please review it in Company Policies.";
    \App\Helpers\NotificationHelper::add(
        array_map('intval', $recipients),
        "New Company Policy",
        $message,
        "policies",
        "System",
        (int) $sender_id
    );
}

try {
    switch ($action) {
        case 'fetch_policies':
            $is_privileged = in_array($_SESSION['user_role'] ?? '', ['Admin', 'HR'], true);
            if ($is_privileged) {
                $stmt = $pdo->query("SELECT * FROM policies ORDER BY created_at DESC");
            } else {
                $stmt = $pdo->query("SELECT * FROM policies WHERE status = 'Active' ORDER BY created_at DESC");
            }
            $policies = $stmt->fetchAll();
            echo json_encode(['status' => 'success', 'data' => $policies]);
            break;

        case 'save_policy':
            $id = $_POST['id'] ?? null;
            $title = $_POST['title'] ?? '';
            $status = $_POST['status'] ?? 'Active';
            $effective_date = $_POST['effective_date'] ?? date('Y-m-d');
            $content = $_POST['content'] ?? '';

            if (empty($title) || empty($content)) {
                echo json_encode(['status' => 'error', 'message' => 'Title and content are required.']);
                exit;
            }

            if ($id) {
                $old_status_stmt = $pdo->prepare("SELECT status FROM policies WHERE id = ?");
                $old_status_stmt->execute([$id]);
                $old_status = $old_status_stmt->fetchColumn();

                // Update
                $stmt = $pdo->prepare("UPDATE policies SET title = ?, status = ?, effective_date = ?, content = ? WHERE id = ?");
                $stmt->execute([$title, $status, $effective_date, $content, $id]);

                if ($old_status !== false && $old_status !== 'Active' && $status === 'Active') {
                    notifyEmployeesAboutPolicy($title, $user_id);
                }

                \App\Helpers\WebSocketHelper::broadcast('policies_updated');
                echo json_encode(['status' => 'success', 'message' => 'Policy updated successfully.']);
            } else {
                // Add New
                $stmt = $pdo->prepare("INSERT INTO policies (title, status, effective_date, content, created_by) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$title, $status, $effective_date, $content, $user_id]);

                if ($status === 'Active') {
                    notifyEmployeesAboutPolicy($title, $user_id);
                }

                \App\Helpers\WebSocketHelper::broadcast('policies_updated');
                echo json_encode(['status' => 'success', 'message' => 'Policy added successfully.']);
            }
            break;

        case 'delete_policy':
            $id = $_POST['id'] ?? null;
            if (!$id) {
                echo json_encode(['status' => 'error', 'message' => 'ID is required to delete.']);
                exit;
            }
            $stmt = $pdo->prepare("DELETE FROM policies WHERE id = ?");
            $stmt->execute([$id]);
            \App\Helpers\WebSocketHelper::broadcast('policies_updated');
            echo json_encode(['status' => 'success', 'message' => 'Policy deleted successfully.']);
            break;

        default:
            echo json_encode(['status' => 'error', 'message' => 'Invalid action.']);
            break;
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'A server error occurred. Please try again.']);
}
