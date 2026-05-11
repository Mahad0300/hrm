<?php
// admin/api/shift_handler.php
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
            $stmt = $pdo->query("SELECT * FROM shifts WHERE deleted_at IS NULL ORDER BY id DESC");
            $shifts = $stmt->fetchAll();
            echo json_encode(['status' => 'success', 'data' => $shifts]);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    case 'add':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name'] ?? '');
            $start = $_POST['start_time'] ?? '';
            $end = $_POST['end_time'] ?? '';
            $grace = (int)($_POST['grace_time'] ?? 0);
            $halfday = (float)($_POST['halfday_hours'] ?? 0);

            if (empty($name) || empty($start) || empty($end)) {
                echo json_encode(['status' => 'error', 'message' => 'Shift Name, Start Time, and End Time are required.']);
                exit;
            }

            try {
                $stmt = $pdo->prepare("INSERT INTO shifts (name, start_time, end_time, grace_time, halfday_hours) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$name, $start, $end, $grace, $halfday]);
                echo json_encode(['status' => 'success', 'message' => 'Shift created successfully.']);
            } catch (PDOException $e) {
                echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
            }
        }
        break;

    case 'edit':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? null;
            $name = trim($_POST['name'] ?? '');
            $start = $_POST['start_time'] ?? '';
            $end = $_POST['end_time'] ?? '';
            $grace = (int)($_POST['grace_time'] ?? 0);
            $halfday = (float)($_POST['halfday_hours'] ?? 0);

            if (!$id || empty($name) || empty($start) || empty($end)) {
                echo json_encode(['status' => 'error', 'message' => 'ID, Shift Name, Start Time, and End Time are required.']);
                exit;
            }

            try {
                $stmt = $pdo->prepare("UPDATE shifts SET name = ?, start_time = ?, end_time = ?, grace_time = ?, halfday_hours = ? WHERE id = ?");
                $stmt->execute([$name, $start, $end, $grace, $halfday, $id]);
                echo json_encode(['status' => 'success', 'message' => 'Shift updated successfully.']);
            } catch (PDOException $e) {
                echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
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
                $stmt = $pdo->prepare("UPDATE shifts SET deleted_at = NOW() WHERE id = ?");
                $stmt->execute([$id]);
                echo json_encode(['status' => 'success', 'message' => 'Shift deleted successfully.']);
            } catch (PDOException $e) {
                echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
            }
        }
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid action.']);
        break;
}
?>
