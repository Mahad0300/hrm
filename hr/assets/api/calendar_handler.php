<?php
require_once '../../../includes/db_connect.php';
require_once '../../../includes/auth_helper.php';
require_once '../../../includes/api/notification_handler.php';
require_once '../../../includes/api/activity_helper.php';

header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if (!isLoggedIn()) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
    exit;
}

function getEventNotificationRecipients($target_dept, $sender_id) {
    global $pdo;

    $target_dept = trim((string)$target_dept);
    $is_everyone = (strtolower($target_dept) === 'everyone' || strtolower($target_dept) === 'all');

    if ($is_everyone) {
        $stmt = $pdo->query("SELECT id FROM employees WHERE status = 'Active' AND deleted_at IS NULL");
        $recipients = $stmt->fetchAll(PDO::FETCH_COLUMN);
    } else {
        $depts = array_filter(array_map('trim', explode(',', $target_dept)));
        if (empty($depts)) {
            return [];
        }

        $placeholders = str_repeat('?,', count($depts) - 1) . '?';
        $stmt = $pdo->prepare("
            SELECT e.id 
            FROM employees e
            JOIN departments d ON e.department_id = d.id
            WHERE d.name IN ($placeholders) AND e.status = 'Active' AND e.deleted_at IS NULL
        ");
        $stmt->execute($depts);
        $recipients = $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    return array_values(array_filter($recipients, function($rid) use ($sender_id) {
        return (int)$rid !== (int)$sender_id;
    }));
}

try {
    switch ($action) {
        case 'fetch':
            $stmt = $pdo->query("SELECT e.*, CONCAT(emp.first_name, ' ', emp.last_name) as author_name 
                                FROM events e 
                                LEFT JOIN employees emp ON e.created_by = emp.id 
                                ORDER BY e.event_date DESC");
            echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll()]);
            break;

        case 'save':
            $id = $_POST['id'] ?? null;
            $title = $_POST['title'];
            $description = $_POST['description'];
            $event_date = $_POST['event_date'];
            $event_time = $_POST['event_time'];
            $category = $_POST['category'];
            $target_dept = $_POST['target_dept'];
            $show_in_announcement = $_POST['show_in_announcement'] ?? 0;
            $created_by = $_SESSION['user_id'];

            if ($id) {
                $sql = "UPDATE events SET title=?, description=?, event_date=?, event_time=?, category=?, target_dept=?, show_in_announcement=? WHERE id=?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$title, $description, $event_date, $event_time, $category, $target_dept, $show_in_announcement, $id]);
                $event_id = $id;
            } else {
                $sql = "INSERT INTO events (title, description, event_date, event_time, category, target_dept, show_in_announcement, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$title, $description, $event_date, $event_time, $category, $target_dept, $show_in_announcement, $created_by]);
                $event_id = $pdo->lastInsertId();
            }

            // [TRIGGER] Notify relevant employees on create and on later edits.
            $recipients = getEventNotificationRecipients($target_dept, $created_by);
            if (!empty($recipients)) {
                $formattedDate = date('d M, Y', strtotime($event_date));
                $formattedTime = date('h:i A', strtotime($event_time));

                if ($id) {
                    $msg = "Event Updated: $title is scheduled for $formattedDate at $formattedTime.";
                    addNotification($recipients, "Event Updated", $msg, "event-calendar.php", "System", $created_by);
                } else {
                    $check = $pdo->prepare("SELECT is_notified FROM events WHERE id = ?");
                    $check->execute([$event_id]);
                    $is_notified = (int)$check->fetchColumn();

                    if (!$is_notified) {
                        $msg = "New Event: $title on $formattedDate at $formattedTime.";
                        if (addNotification($recipients, "Upcoming Event", $msg, "event-calendar.php", "System", $created_by)) {
                            $pdo->prepare("UPDATE events SET is_notified = 1 WHERE id = ?")->execute([$event_id]);
                        }
                    }
                }
            }

            // [LOG]
            $formatted_event_date = date('M d, Y', strtotime($event_date));
            $logAction = $id ? "Updated Event" : "Created Event";
            $logMsg = $id ? "Modified the details of event: '$title' (Scheduled for $formatted_event_date)" : "Scheduled a new company event: '$title' on $formatted_event_date";
            $admin_id = $_SESSION['user_id'] ?? 0;
            logActivity($admin_id, $logAction, "Event Calendar", $logMsg);

            echo json_encode(['status' => 'success', 'message' => 'Event saved successfully.']);
            break;

        case 'delete':
            $id = $_POST['id'] ?? null;
            if ($id) {
                // Fetch title for logging
                $t_stmt = $pdo->prepare("SELECT title FROM events WHERE id = ?");
                $t_stmt->execute([$id]);
                $event_title = $t_stmt->fetchColumn() ?: "Unknown";

                $stmt = $pdo->prepare("DELETE FROM events WHERE id = ?");
                $stmt->execute([$id]);

                // [LOG]
                $admin_id = $_SESSION['user_id'] ?? 0;
                logActivity($admin_id, "Deleted Event", "Event Calendar", "Permanently removed the event: '$event_title'");

                echo json_encode(['status' => 'success', 'message' => 'Event deleted successfully.']);
            }
            break;

        case 'fetch_depts':
            $stmt = $pdo->query("SELECT id, name FROM departments WHERE deleted_at IS NULL");
            echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll()]);
            break;

        case 'fetch_interviews':
            $stmt = $pdo->query("
                SELECT i.id as id, DATE(i.interview_date) as date, TIME_FORMAT(i.interview_date, '%H:%i') as time, 
                       c.name as name, 
                       j.title as job, c.id as candidate_id, i.feedback
                FROM interviews i
                JOIN candidates c ON i.candidate_id = c.id
                JOIN jobs j ON c.job_id = j.id
                ORDER BY i.interview_date ASC
            ");
            echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll()]);
            break;

        default:
            echo json_encode(['status' => 'error', 'message' => 'Invalid action.']);
            break;
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'A server error occurred. Please try again.']);
}
?>
