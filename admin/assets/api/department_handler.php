<?php
// admin/ajax/department_handler.php
require_once '../../../includes/db_connect.php';
require_once '../../../includes/auth_helper.php';

header('Content-Type: application/json');

// Session & Role Check
if (!isLoggedIn() || $_SESSION['user_role'] !== 'Admin') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
    exit;
}

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'fetch':
        try {
            // Fetch departments with names for manager and head
            $stmt = $pdo->query("
                SELECT d.*, 
                CONCAT_WS(' ', em.first_name, NULLIF(em.middle_name, ''), em.last_name) as manager_name,
                CONCAT_WS(' ', eh.first_name, NULLIF(eh.middle_name, ''), eh.last_name) as head_name,
                (SELECT COUNT(*) FROM employees e WHERE e.department_id = d.id AND e.deleted_at IS NULL) as total 
                FROM departments d 
                LEFT JOIN employees em ON d.manager = em.id
                LEFT JOIN employees eh ON d.head = eh.id
                WHERE d.deleted_at IS NULL 
                ORDER BY d.id DESC
            ");
            $departments = $stmt->fetchAll();
            echo json_encode(['status' => 'success', 'data' => $departments]);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'A server error occurred. Please try again.']);
        }
        break;

    case 'add':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name'] ?? '');
            $manager = trim($_POST['manager'] ?? '');
            $head = trim($_POST['head'] ?? '');

            if (empty($name)) {
                echo json_encode(['status' => 'error', 'message' => 'Department name is required.']);
                exit;
            }

            try {
                // Handle optional fields as NULL if empty
                $managerValue = !empty($manager) ? $manager : null;
                $headValue = !empty($head) ? $head : null;

                $stmt = $pdo->prepare("INSERT INTO departments (name, manager, head) VALUES (?, ?, ?)");
                $stmt->execute([$name, $managerValue, $headValue]);
                echo json_encode(['status' => 'success', 'message' => 'Department created successfully.']);
            } catch (PDOException $e) {
                echo json_encode(['status' => 'error', 'message' => 'A server error occurred. Please try again.']);
            }
        }
        break;

    case 'edit':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? null;
            $name = trim($_POST['name'] ?? '');
            $manager = trim($_POST['manager'] ?? '');
            $head = trim($_POST['head'] ?? '');

            if (!$id || empty($name)) {
                echo json_encode(['status' => 'error', 'message' => 'ID and name are required.']);
                exit;
            }

            try {
                // Handle optional fields as NULL if empty
                $managerValue = !empty($manager) ? $manager : null;
                $headValue = !empty($head) ? $head : null;

                $stmt = $pdo->prepare("UPDATE departments SET name = ?, manager = ?, head = ? WHERE id = ?");
                $stmt->execute([$name, $managerValue, $headValue, $id]);
                echo json_encode(['status' => 'success', 'message' => 'Department updated successfully.']);
            } catch (PDOException $e) {
                echo json_encode(['status' => 'error', 'message' => 'A server error occurred. Please try again.']);
            }
        }
        break;

    case 'delete':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? null;
            if (!$id) {
                echo json_encode(['status' => 'error', 'message' => 'ID is required.']);
                exit;
            }

            try {
                // Soft delete
                $stmt = $pdo->prepare("UPDATE departments SET deleted_at = NOW() WHERE id = ?");
                $stmt->execute([$id]);
                echo json_encode(['status' => 'success', 'message' => 'Department deleted successfully.']);
            } catch (PDOException $e) {
                echo json_encode(['status' => 'error', 'message' => 'A server error occurred. Please try again.']);
            }
        }
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid action.']);
        break;
}
?>