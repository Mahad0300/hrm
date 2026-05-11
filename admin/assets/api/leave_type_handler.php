<?php
require_once '../../../includes/db_connect.php';
session_start();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit;
}

// Only Admin should save global numbers
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'Admin') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
    exit;
}

$sick = isset($_POST['sick']) ? (int)$_POST['sick'] : 0;
$casual = isset($_POST['casual']) ? (int)$_POST['casual'] : 0;
$annual = isset($_POST['annual']) ? (int)$_POST['annual'] : 0;

try {
    $pdo->beginTransaction();

    // Map IDs to variables (ID 1: Sick, 2: Casual, 3: Annual based on SQL dump)
    $updates = [
        1 => $sick,
        2 => $casual,
        3 => $annual
    ];

    $stmt = $pdo->prepare("UPDATE `leave_types` SET `days_per_year` = :days WHERE `id` = :id");

    foreach ($updates as $id => $days) {
        $stmt->execute([':days' => $days, ':id' => $id]);
    }

    $pdo->commit();
    echo json_encode(['status' => 'success', 'message' => 'Leave quotas updated successfully.']);
} catch (PDOException $e) {
    $pdo->rollBack();
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
