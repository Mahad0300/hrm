<?php
require_once '../../../includes/db_connect.php';
require_once '../../../includes/auth_helper.php';

header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if (!isLoggedIn()) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
    exit;
}

try {
    switch ($action) {
        case 'fetch':
            $user_id = $_SESSION['user_id'];
            
            // 1. Get user's department
            $userDeptStmt = $pdo->prepare("SELECT d.name FROM employees e LEFT JOIN departments d ON e.department_id = d.id WHERE e.id = ?");
            $userDeptStmt->execute([$user_id]);
            $userDept = $userDeptStmt->fetchColumn() ?: '';

            // 2. Fetch events matching all departments or any selected department
            $stmt = $pdo->prepare("
                SELECT e.*, CONCAT(emp.first_name, ' ', emp.last_name) as author_name 
                FROM events e 
                LEFT JOIN employees emp ON e.created_by = emp.id 
                WHERE LOWER(e.target_dept) IN ('everyone', 'all') OR FIND_IN_SET(?, REPLACE(e.target_dept, ', ', ',')) > 0
                ORDER BY e.event_date DESC
            ");
            $stmt->execute([$userDept]);
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
