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
        case 'fetch_policies':
            // Employees only see Active policies
            $stmt = $pdo->prepare("SELECT id, title, status, effective_date, content as bodyHtml, updated_at FROM policies WHERE status = 'Active' ORDER BY effective_date DESC");
            $stmt->execute();
            echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll()]);
            break;

        default:
            echo json_encode(['status' => 'error', 'message' => 'Invalid action.']);
            break;
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'A server error occurred. Please try again.']);
}
