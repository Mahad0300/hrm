<?php
require_once '../../../includes/db_connect.php';
require_once '../../../includes/auth_helper.php';

header('Content-Type: application/json');

if (!isLoggedIn() || !in_array($_SESSION['user_role'], ['Admin', 'HR'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
    exit;
}

$action = $_GET['action'] ?? 'fetch';

try {
    if ($action === 'fetch') {
        $search = $_GET['search'] ?? '';
        $module = $_GET['module'] ?? '';
        $action_filter = $_GET['action_filter'] ?? '';
        $date = $_GET['date'] ?? '';
        $page = (int)($_GET['page'] ?? 1);
        $perPage = (int)($_GET['perPage'] ?? 10);
        $offset = ($page - 1) * $perPage;

        // Base where clause
        $where = " WHERE 1=1 ";
        $params = [];

        if ($search) {
            $where .= " AND (e.first_name LIKE ? OR e.last_name LIKE ? OR e.employee_id LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        
        // Since 'module' column doesn't exist, we search inside description [Module]
        if ($module) {
            $where .= " AND al.description LIKE ?";
            $params[] = "%[$module]%";
        }
        
        if ($action_filter) {
            $where .= " AND al.action LIKE ?";
            $params[] = "%$action_filter%";
        }
        if ($date) {
            $where .= " AND DATE(al.created_at) = ?";
            $params[] = $date;
        }

        // 1. Get Total Count
        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM activity_logs al JOIN employees e ON al.employee_id = e.id $where");
        $countStmt->execute($params);
        $total = $countStmt->fetchColumn();

        // 2. Get Data
        $query = "
            SELECT al.*, al.description as details, e.first_name, e.last_name, e.id as emp_code, e.id_card_path, e.profile_pic
            FROM activity_logs al
            JOIN employees e ON al.employee_id = e.id
            $where
            ORDER BY al.created_at DESC 
            LIMIT $perPage OFFSET $offset
        ";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $logs = $stmt->fetchAll();

        // Clean up details to extract module for UI if possible
        foreach ($logs as &$log) {
            if (preg_match('/^\[(.*?)\] (.*)$/', $log['details'], $matches)) {
                $log['module'] = $matches[1];
                $log['details'] = $matches[2];
            } else {
                $log['module'] = 'System';
            }
        }

        echo json_encode([
            'status' => 'success',
            'data' => $logs,
            'total' => (int)$total,
            'page' => $page,
            'perPage' => $perPage
        ]);
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Query Error: ' . $e->getMessage()]);
}
?>
