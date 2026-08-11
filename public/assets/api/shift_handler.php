<?php
require_once __DIR__ . '/bootstrap.php';
// admin/api/shift_handler.php
header('Content-Type: application/json');

// Session & Role Check
if (!isLoggedIn() || !in_array($_SESSION['user_role'] ?? '', ['Admin', 'HR'], true)) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
    exit;
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';
if (isHrPortalUser() && $action !== '') {
    hrGuardApiRequest($pdo, $action, basename(__FILE__));
}

switch ($action) {
    case 'fetch':
        try {
            $stmt = $pdo->query("SELECT * FROM shifts WHERE deleted_at IS NULL ORDER BY id DESC");
            $shifts = $stmt->fetchAll();
            echo json_encode(['status' => 'success', 'data' => $shifts]);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'A server error occurred. Please try again.']);
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
                \App\Helpers\WebSocketHelper::broadcast('shift_updated');
                echo json_encode(['status' => 'success', 'message' => 'Shift created successfully.']);
            } catch (PDOException $e) {
                echo json_encode(['status' => 'error', 'message' => 'A server error occurred. Please try again.']);
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
                \App\Helpers\WebSocketHelper::broadcast('shift_updated');
                echo json_encode(['status' => 'success', 'message' => 'Shift updated successfully.']);
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
                $stmt = $pdo->prepare("UPDATE shifts SET deleted_at = NOW() WHERE id = ?");
                $stmt->execute([$id]);
                \App\Helpers\WebSocketHelper::broadcast('shift_updated');
                echo json_encode(['status' => 'success', 'message' => 'Shift deleted successfully.']);
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
