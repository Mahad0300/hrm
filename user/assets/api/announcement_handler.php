<?php
require_once '../../../includes/db_connect.php';
require_once '../../../includes/auth_helper.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
    exit;
}

$user_id = $_SESSION['user_id'];
$today = date('Y-m-d');

try {
    // 1. Get the current user's department name
    $dept_stmt = $pdo->prepare("
        SELECT d.name 
        FROM employees e 
        LEFT JOIN departments d ON e.department_id = d.id 
        WHERE e.id = ?
    ");
    $dept_stmt->execute([$user_id]);
    $user_dept = $dept_stmt->fetchColumn() ?: 'unknown';

    // 2. Fetch announcements & relevant events
    // Filter by: Not deleted AND (everyone OR user's department) AND (active date range)
    $sql = "SELECT 
                t.*, 
                CONCAT(e.first_name, ' ', e.last_name) as author_name, 
                e.profile_pic
            FROM (
                SELECT 
                    id, title, content, type, target_depts, start_date, end_date, 
                    created_by, created_at, 'announcement' as source
                FROM announcements
                WHERE deleted_at IS NULL 
                  AND (target_depts = 'everyone' OR FIND_IN_SET(?, target_depts) > 0)
                  AND start_date <= ? AND end_date >= ?
                
                UNION ALL
                
                SELECT 
                    id, title, description as content, UPPER(category) as type, 
                    target_dept as target_depts, event_date as start_date, event_date as end_date, 
                    NULL as created_by, created_at, 'event' as source
                FROM events
                WHERE show_in_announcement = 1
                  AND (LOWER(target_dept) IN ('everyone', 'all') OR FIND_IN_SET(?, REPLACE(target_dept, ', ', ',')) > 0)
                  AND event_date >= ?
            ) t
            LEFT JOIN employees e ON t.created_by = e.id
            ORDER BY t.created_at DESC";

    $stmt = $pdo->prepare($sql);
    // Bind parameters for both parts of UNION
    // announcements (target_depts, start, end) + events (target_dept, date)
    $stmt->execute([$user_dept, $today, $today, $user_dept, $today]);
    $results = $stmt->fetchAll();

    // Process results (formatting dates, etc.)
    foreach ($results as &$row) {
        $start_ts = strtotime($row['start_date']);
        $row['formatted_date'] = date("M d, Y", $start_ts);
        $row['date_iso'] = date("Y-m-d", $start_ts);

        // Author Fallback
        if ($row['source'] === 'event' && empty($row['author_name'])) {
            $row['author_name'] = 'System Admin';
        }
    }

    echo json_encode(['status' => 'success', 'data' => $results]);

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'A server error occurred. Please try again.']);
}
?>