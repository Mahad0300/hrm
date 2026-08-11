<?php
require_once __DIR__ . '/bootstrap.php';
header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if (!isLoggedIn()) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
    exit;
}

if (isHrPortalUser() && $action !== '') {
    hrGuardApiRequest($pdo, $action, basename(__FILE__));
}

$writeActions = ['save', 'delete', 'fetch_depts'];
if (in_array($action, $writeActions) && !in_array($_SESSION['user_role'], ['Admin', 'HR'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
    exit;
}

$today = date('Y-m-d');

try {
    switch ($action) {
        case 'fetch':
            // Use a subquery to combine tables, then join with employees for author details
            $sql = "SELECT 
                        t.*, 
                        CONCAT(e.first_name, ' ', e.last_name) as author_name, 
                        e.profile_pic
                    FROM (
                        SELECT 
                            id, title, content, type, target_depts, start_date, end_date, 
                            created_by, created_at, updated_at, 'announcement' as source
                        FROM announcements
                        WHERE deleted_at IS NULL
                        UNION ALL
                        SELECT 
                            id, title, description as content, UPPER(category) as type, 
                            target_dept as target_depts, event_date as start_date, event_date as end_date, 
                            NULL as created_by, created_at, updated_at, 'event' as source
                        FROM events
                        WHERE show_in_announcement = 1
                    ) t
                    LEFT JOIN employees e ON t.created_by = e.id
                    ORDER BY t.created_at DESC";
            
            $stmt = $pdo->query($sql);
            $results = $stmt->fetchAll();
            
            $isEmployee = !in_array($_SESSION['user_role'] ?? '', ['Admin', 'HR']);
            
            $user_dept = 'unknown';
            if ($isEmployee) {
                $user_id = $_SESSION['user_id'] ?? 0;
                $dept_stmt = $pdo->prepare("SELECT d.name FROM employees e LEFT JOIN departments d ON e.department_id = d.id WHERE e.id = ?");
                $dept_stmt->execute([$user_id]);
                $user_dept = $dept_stmt->fetchColumn() ?: 'unknown';
            }

            $finalData = [];
            foreach ($results as $row) {
                // Logic for Status Calculation
                if ($row['source'] === 'event') {
                    $status = ($row['start_date'] >= $today) ? 'ACTIVE' : 'EXPIRED';
                } else {
                    $status = 'ACTIVE';
                    if ($row['start_date'] > $today) {
                        $status = 'SCHEDULED';
                    } elseif ($row['end_date'] < $today) {
                        $status = 'EXPIRED';
                    }
                }
                $row['calculated_status'] = $status;

                // Employees only see ACTIVE announcements
                if ($isEmployee && $status !== 'ACTIVE') {
                    continue;
                }

                // Filter by target department for standard employees
                if ($isEmployee) {
                    $target = trim((string)$row['target_depts']);
                    $is_everyone = (strtolower($target) === 'everyone' || strtolower($target) === 'all' || $target === '');
                    
                    if (!$is_everyone) {
                        $depts = array_map('trim', explode(',', str_replace(', ', ',', $target)));
                        if (!in_array($user_dept, $depts, true)) {
                            continue;
                        }
                    }
                }

                // Author Fallback for Events or missing name
                if (empty($row['author_name'])) {
                    $row['author_name'] = 'System Admin';
                }

                // Add formatted date fields for JS renderer
                $ts = strtotime($row['created_at']);
                $row['formatted_date'] = $ts ? date('d M Y', $ts) : '-';
                $row['date_iso']       = $ts ? date('Y-m-d', $ts) : '';

                $finalData[] = $row;
            }
            
            echo json_encode(['status' => 'success', 'data' => $finalData]);
            break;

        case 'save':
            $id = $_POST['id'] ?? null;
            $title = $_POST['title'];
            $content = $_POST['content'];
            $type = $_POST['type'];
            $start_date = $_POST['start_date'];
            $end_date = $_POST['end_date'];
            $target_depts = $_POST['target_depts'];
            $created_by = $_SESSION['user_id'];

            if ($start_date < $today) {
                echo json_encode(['status' => 'error', 'message' => 'Start date cannot be a past date.']);
                exit;
            }

            if ($end_date < $start_date) {
                echo json_encode(['status' => 'error', 'message' => 'End date cannot be before start date.']);
                exit;
            }

            $old_target_depts = '';
            $old_is_notified = 0;
            if ($id) {
                $getOld = $pdo->prepare("SELECT target_depts, is_notified FROM announcements WHERE id = ?");
                $getOld->execute([$id]);
                $oldRow = $getOld->fetch(PDO::FETCH_ASSOC);
                if ($oldRow) {
                    $old_target_depts = $oldRow['target_depts'] ?? '';
                    $old_is_notified = (int)($oldRow['is_notified'] ?? 0);
                }
            }

            if ($id) {
                $sql = "UPDATE announcements SET 
                        title = ?, content = ?, type = ?, 
                        start_date = ?, end_date = ?, 
                        target_depts = ? 
                        WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$title, $content, $type, $start_date, $end_date, $target_depts, $id]);
                $announcement_id = $id;
            } else {
                $sql = "INSERT INTO announcements (title, content, type, start_date, end_date, target_depts, created_by) 
                        VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$title, $content, $type, $start_date, $end_date, $target_depts, $created_by]);
                $announcement_id = $pdo->lastInsertId();
            }

            // [TRIGGER] Notify relevant employees (now supports edits & scheduled going active)
            if (!$old_is_notified && $start_date <= $today) {
                // Send to all targets since it was never notified before
                $recipients = \App\Helpers\NotificationHelper::getTargetDeptEmployeeIds($pdo, $target_depts);
                
                // Exclude creator
                $recipients = array_filter($recipients, function($rid) use ($created_by) {
                    return $rid != $created_by;
                });

                if (!empty($recipients)) {
                    $msg = "New Announcement: $title. Check the announcements page for details.";
                    if (addNotification($recipients, "New Company Announcement", $msg, "announcements.php", "System", $created_by)) {
                        $pdo->prepare("UPDATE announcements SET is_notified = 1 WHERE id = ?")->execute([$announcement_id]);
                    }
                }
            } elseif ($old_is_notified) {
                // Already notified before, send only to NEWLY added employee recipients
                $recipients = \App\Helpers\NotificationHelper::getAddedTargetDeptRecipients($pdo, $old_target_depts, $target_depts, $created_by);

                if (!empty($recipients)) {
                    $msg = "New Announcement: $title. Check the announcements page for details.";
                    addNotification($recipients, "New Company Announcement", $msg, "announcements.php", "System", $created_by);
                }
            }

            // [LOG]
            $logAction = $id ? "Updated Announcement" : "Created Announcement";
            $logMsg = $id ? "Modified the details of announcement: '$title'" : "Published a new company announcement titled: '$title'";
            logActivity($created_by, $logAction, "Announcements", $logMsg);

            \App\Helpers\WebSocketHelper::broadcast('announcements_updated');
            echo json_encode(['status' => 'success', 'message' => 'Announcement saved successfully.']);
            break;

        case 'delete':
            $id = $_POST['id'] ?? null;
            if ($id) {
                // Fetch title before deleting for better logging
                $t_stmt = $pdo->prepare("SELECT title FROM announcements WHERE id = ?");
                $t_stmt->execute([$id]);
                $ann_title = $t_stmt->fetchColumn() ?: "Unknown";

                $stmt = $pdo->prepare("UPDATE announcements SET deleted_at = NOW() WHERE id = ?");
                $stmt->execute([$id]);

                // [LOG]
                $admin_id = $_SESSION['user_id'] ?? 0;
                logActivity($admin_id, "Deleted Announcement", "Announcements", "Permanently removed the announcement: '$ann_title'");

                \App\Helpers\WebSocketHelper::broadcast('announcements_updated');
                echo json_encode(['status' => 'success', 'message' => 'Announcement deleted successfully.']);
            }
            break;

        case 'fetch_depts':
            $stmt = $pdo->query("SELECT id, name FROM departments WHERE deleted_at IS NULL");
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
