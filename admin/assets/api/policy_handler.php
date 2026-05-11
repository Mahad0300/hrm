<?php
require_once '../../../includes/db_connect.php';
require_once '../../../includes/auth_helper.php';

header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if (!isLoggedIn()) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
    exit;
}

$user_id = $_SESSION['user_id'] ?? 1; // Fallback to 1 if not set

try {
    switch ($action) {
        case 'fetch_policies':
            $stmt = $pdo->query("SELECT * FROM policies ORDER BY created_at DESC");
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
                // Update
                $stmt = $pdo->prepare("UPDATE policies SET title = ?, status = ?, effective_date = ?, content = ? WHERE id = ?");
                $stmt->execute([$title, $status, $effective_date, $content, $id]);
                echo json_encode(['status' => 'success', 'message' => 'Policy updated successfully.']);
            } else {
                // Add New
                $stmt = $pdo->prepare("INSERT INTO policies (title, status, effective_date, content, created_by) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$title, $status, $effective_date, $content, $user_id]);
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
            echo json_encode(['status' => 'success', 'message' => 'Policy deleted successfully.']);
            break;

        default:
            echo json_encode(['status' => 'error', 'message' => 'Invalid action.']);
            break;
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
