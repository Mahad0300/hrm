<?php
require_once __DIR__ . '/../db_connect.php';
require_once __DIR__ . '/../auth_helper.php';
require_once __DIR__ . '/../access_control_helper.php';
require_once __DIR__ . '/activity_helper.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$userRole = $_SESSION['user_role'] ?? '';
$userId = (int) ($_SESSION['user_id'] ?? 0);

try {
    hrSeedPermissionsIfEmpty($pdo);

    switch ($action) {
        case 'fetch_permissions':
            if ($userRole !== 'Admin') {
                echo json_encode(['status' => 'error', 'message' => 'Only Admin can manage access control.']);
                exit;
            }
            echo json_encode([
                'status' => 'success',
                'data' => array_values(hrFetchAllPermissions($pdo)),
                'revision' => hrPermissionsRevision($pdo),
            ]);
            break;

        case 'save_permissions':
            if ($userRole !== 'Admin') {
                echo json_encode(['status' => 'error', 'message' => 'Only Admin can manage access control.']);
                exit;
            }

            $raw = $_POST['permissions'] ?? '';
            $permissions = json_decode($raw, true);
            if (!is_array($permissions)) {
                echo json_encode(['status' => 'error', 'message' => 'Invalid permissions payload.']);
                exit;
            }

            $registry = hrAccessPageRegistry();
            $stmt = $pdo->prepare(
                "INSERT INTO hr_page_permissions (page_key, can_view, can_create, can_edit, can_delete, can_export)
                 VALUES (?, ?, ?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE
                    can_view = VALUES(can_view),
                    can_create = VALUES(can_create),
                    can_edit = VALUES(can_edit),
                    can_delete = VALUES(can_delete),
                    can_export = VALUES(can_export),
                    updated_at = CURRENT_TIMESTAMP"
            );

            foreach ($permissions as $row) {
                $pageKey = $row['page_key'] ?? '';
                if (!isset($registry[$pageKey])) {
                    continue;
                }
                $normalized = hrNormalizePermissionRow($pageKey, [
                    'can_view' => !empty($row['can_view']) ? 1 : 0,
                    'can_create' => !empty($row['can_create']) ? 1 : 0,
                    'can_edit' => !empty($row['can_edit']) ? 1 : 0,
                    'can_delete' => !empty($row['can_delete']) ? 1 : 0,
                    'can_export' => !empty($row['can_export']) ? 1 : 0,
                ]);
                $stmt->execute([
                    $pageKey,
                    $normalized['can_view'],
                    $normalized['can_create'],
                    $normalized['can_edit'],
                    $normalized['can_delete'],
                    $normalized['can_export'],
                ]);
            }

            hrBumpPermissionsRevision($pdo);
            logActivity($userId, 'Access Control Updated', 'Security', 'HR portal permissions matrix was updated by Admin.');

            echo json_encode([
                'status' => 'success',
                'message' => 'Access control settings saved successfully.',
                'revision' => hrPermissionsRevision($pdo),
            ]);
            break;

        case 'my_permissions':
            if ($userRole !== 'HR') {
                echo json_encode(['status' => 'success', 'data' => [], 'revision' => hrPermissionsRevision($pdo)]);
                exit;
            }
            $all = hrFetchAllPermissions($pdo);
            $mine = [];
            foreach ($all as $key => $perm) {
                $mine[$key] = [
                    'page_key' => $key,
                    'can_view' => (int) ($perm['can_view'] ?? 0),
                    'can_create' => (int) ($perm['can_create'] ?? 0),
                    'can_edit' => (int) ($perm['can_edit'] ?? 0),
                    'can_delete' => (int) ($perm['can_delete'] ?? 0),
                    'can_export' => (int) ($perm['can_export'] ?? 0),
                ];
            }
            echo json_encode([
                'status' => 'success',
                'data' => $mine,
                'revision' => hrPermissionsRevision($pdo),
            ]);
            break;

        case 'check_access':
            $pageKey = trim($_GET['page'] ?? $_POST['page'] ?? '');
            if ($pageKey === '') {
                echo json_encode(['status' => 'error', 'message' => 'Page key required.']);
                exit;
            }
            if ($userRole !== 'HR') {
                echo json_encode(['status' => 'success', 'can_view' => true, 'revision' => hrPermissionsRevision($pdo)]);
                exit;
            }
            $canView = hrCanViewSidebarPage($pdo, $pageKey);
            echo json_encode([
                'status' => 'success',
                'can_view' => $canView,
                'page_key' => $pageKey,
                'revision' => hrPermissionsRevision($pdo),
            ]);
            break;

        default:
            echo json_encode(['status' => 'error', 'message' => 'Invalid action.']);
            break;
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error. Please ensure hr_page_permissions table exists.']);
}
