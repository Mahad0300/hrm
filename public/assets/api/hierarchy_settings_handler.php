<?php
require_once __DIR__ . '/bootstrap.php';

use App\Core\Auth;
use App\Helpers\ActivityHelper;
use App\Helpers\HierarchyHelper;

header('Content-Type: application/json');

if (!Auth::isLoggedIn() || !in_array($_SESSION['user_role'] ?? '', ['Admin', 'HR'], true)) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
    exit;
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'fetch':
            echo json_encode([
                'status' => 'success',
                'settings' => HierarchyHelper::getSettings($pdo),
                'employees' => HierarchyHelper::fetchActiveEmployees($pdo),
                'departments' => HierarchyHelper::fetchDepartments($pdo),
                'managers' => HierarchyHelper::getManagerAssignments($pdo),
            ]);
            break;

        case 'save':
            $payload = json_decode(file_get_contents('php://input') ?: '{}', true);
            if (!is_array($payload)) {
                throw new InvalidArgumentException('Invalid request payload.');
            }

            HierarchyHelper::validateExecutiveAssignments($payload);
            HierarchyHelper::saveSettings($pdo, $payload);
            HierarchyHelper::saveManagerAssignments($pdo, $payload['managers'] ?? []);

            // Broadcast WebSocket updates
            \App\Helpers\WebSocketHelper::broadcast('hierarchy_updated');

            ActivityHelper::log(
                (int) ($_SESSION['user_id'] ?? 0),
                'Hierarchy Settings Updated',
                'Organization',
                'Updated CEO/CTO and manager department assignments.'
            );

            echo json_encode([
                'status' => 'success',
                'message' => 'Hierarchy settings saved successfully.',
            ]);
            break;

        default:
            echo json_encode(['status' => 'error', 'message' => 'Invalid action.']);
            break;
    }
} catch (InvalidArgumentException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
} catch (Throwable $e) {
    error_log('hierarchy_settings_handler: ' . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'A server error occurred. Please try again.']);
}
