<?php
// includes/api/notification_handler.php
// Dual-purpose file:
//   1. When included via require_once → provides addNotification() helper function
//   2. When called directly via HTTP (fetch/JS) → acts as REST API endpoint

require_once dirname(__FILE__, 2) . '/db_connect.php';
require_once dirname(__FILE__, 2) . '/auth_helper.php';
require_once dirname(__FILE__, 2) . '/access_control_helper.php';

/**
 * Creates a notification and links it to specified recipients.
 *
 * @param array  $recipient_ids  Array of employee IDs to receive the notification.
 * @param string $title          Title of the notification.
 * @param string $message        Detailed message.
 * @param string|null $target_url URL to navigate to when clicked.
 * @param string $type           Type (e.g., 'Leave', 'Recruitment', 'System').
 * @param int|null $sender_id    ID of the employee who triggered the notification.
 * @return bool Success or failure.
 */
function addNotification($recipient_ids, $title, $message, $target_url = null, $type = 'System', $sender_id = null) {
    global $pdo;

    try {
        $pdo->beginTransaction();

        // 1. Insert into notifications table
        $stmt = $pdo->prepare("INSERT INTO notifications (title, message, target_url, type, sender_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$title, $message, $target_url, $type, $sender_id]);
        $notification_id = $pdo->lastInsertId();

        // 2. Insert into notification_recipients for each recipient
        $recipient_stmt = $pdo->prepare("INSERT INTO notification_recipients (notification_id, employee_id) VALUES (?, ?)");
        foreach ($recipient_ids as $employee_id) {
            $recipient_stmt->execute([$notification_id, $employee_id]);
        }

        $pdo->commit();
        return true;

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log("AddNotification Error: " . $e->getMessage());
        return false;
    }
}

// ─── API Endpoint (Only runs when called directly via HTTP) ───────────────────
if (basename($_SERVER['SCRIPT_FILENAME']) !== basename(__FILE__)) {
    return; // File was included via require_once — stop here, function already loaded
}

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
    exit;
}

$user_id = $_SESSION['user_id'];
$action  = $_POST['action'] ?? $_GET['action'] ?? '';

// unread_count is a silent sidebar poll — not a page action
$hr_guard_skip = ['unread_count'];
if (isHrPortalUser() && $action !== '' && !in_array($action, $hr_guard_skip, true)) {
    hrGuardApiRequest($pdo, $action, basename(__FILE__));
}

try {
    switch ($action) {
        case 'fetch':
            $sql = "SELECT 
                        n.id, n.title, n.message, n.target_url, n.type, n.created_at,
                        nr.id as recipient_record_id, nr.is_read,
                        e.profile_pic as sender_pic,
                        CONCAT(e.first_name, ' ', e.last_name) as sender_name
                    FROM notification_recipients nr
                    JOIN notifications n ON nr.notification_id = n.id
                    LEFT JOIN employees e ON n.sender_id = e.id
                    WHERE nr.employee_id = ?
                    ORDER BY n.created_at DESC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$user_id]);
            echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll()]);
            break;

        case 'mark_read':
            $id = $_POST['id'] ?? null;
            if ($id) {
                $stmt = $pdo->prepare("UPDATE notification_recipients SET is_read = 1, read_at = NOW() WHERE id = ? AND employee_id = ?");
                $stmt->execute([$id, $user_id]);
                echo json_encode(['status' => 'success', 'message' => 'Notification marked as read.']);
            }
            break;

        case 'mark_all_read':
            $stmt = $pdo->prepare("UPDATE notification_recipients SET is_read = 1, read_at = NOW() WHERE employee_id = ? AND is_read = 0");
            $stmt->execute([$user_id]);
            echo json_encode(['status' => 'success', 'message' => 'All notifications marked as read.']);
            break;

        case 'clear':
            $stmt = $pdo->prepare("DELETE FROM notification_recipients WHERE employee_id = ?");
            $stmt->execute([$user_id]);
            echo json_encode(['status' => 'success', 'message' => 'Notification history cleared.']);
            break;

        case 'delete':
            $id = $_POST['id'] ?? null;
            if ($id) {
                $stmt = $pdo->prepare("DELETE FROM notification_recipients WHERE id = ? AND employee_id = ?");
                $stmt->execute([$id, $user_id]);
                echo json_encode(['status' => 'success', 'message' => 'Notification deleted.']);
            }
            break;

        case 'unread_count':
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM notification_recipients WHERE employee_id = ? AND is_read = 0");
            $stmt->execute([$user_id]);
            echo json_encode(['status' => 'success', 'count' => (int)$stmt->fetchColumn()]);
            break;

        default:
            echo json_encode(['status' => 'error', 'message' => 'Invalid action.']);
            break;
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'A server error occurred. Please try again.']);
}
?>
