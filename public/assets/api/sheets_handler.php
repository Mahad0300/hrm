<?php
/**
 * Sheets Backend API Handler
 * Replaces Firebase Cloud operations with local MySQL DB operations.
 */

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

use App\Core\Auth;
use App\Helpers\EmailHelper;
use App\Helpers\NotificationHelper;

header('Content-Type: application/json');

if (!Auth::isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized: Please login first.']);
    exit;
}

$currentUserId = (int)Auth::userId();
$currentUserEmail = strtolower($_SESSION['user_email'] ?? '');
$currentUserName = $_SESSION['user_name'] ?? 'User';

// Get current user's department
$deptStmt = $pdo->prepare("SELECT department_id FROM employees WHERE id = ? LIMIT 1");
$deptStmt->execute([$currentUserId]);
$deptResult = $deptStmt->fetchColumn();
$currentUserDeptId = ($deptResult !== false && $deptResult !== null) ? (int)$deptResult : null;

$action = $_GET['action'] ?? '';

// Helper to generate UUID v4
function generateUuid(): string {
    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
}

try {
    switch ($action) {
        case 'create':
            $uuid = generateUuid();
            $title = isset($_POST['title']) ? trim((string)$_POST['title']) : 'Untitled spreadsheet';
            if ($title === '') {
                $title = 'Untitled spreadsheet';
            }

            // Default blank sheet JSON structure matching the standalone app
            $defaultData = json_encode([
                "Sheet1" => [
                    "id" => "Sheet1",
                    "name" => "Sheet1",
                    "cells" => new \stdClass(),
                    "colWidths" => new \stdClass(),
                    "rowHeights" => new \stdClass(),
                    "color" => null
                ]
            ]);

            $stmt = $pdo->prepare("
                INSERT INTO hrm_spreadsheets (uuid, title, created_by, owner_email, data_json, department_id, visibility, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, 'private', NOW(), NOW())
            ");
            $stmt->execute([
                $uuid,
                $title,
                $currentUserId,
                $currentUserEmail,
                $defaultData,
                $currentUserDeptId
            ]);

            echo json_encode(['status' => 'success', 'id' => $uuid, 'title' => $title]);
            break;

        case 'list':
            // 1. Owned sheets
            // 2. Directly shared sheets
            // 3. Department visible sheets (where visibility is 'department' and matches user's department)
            // 4. Public sheets
            $stmt = $pdo->prepare("
                SELECT DISTINCT s.uuid as id, s.title, s.owner_email, s.created_at, s.updated_at,
                       CONCAT(e.first_name, ' ', COALESCE(e.last_name, '')) as owner_name,
                       (s.created_by = ?) as is_owner
                FROM hrm_spreadsheets s
                LEFT JOIN employees e ON s.created_by = e.id
                LEFT JOIN hrm_spreadsheet_permissions p ON s.id = p.spreadsheet_id
                WHERE s.deleted_at IS NULL
                  AND (
                    s.created_by = ? 
                    OR p.employee_id = ?
                    OR (s.visibility = 'department' AND s.department_id = ?)
                    OR s.visibility = 'public'
                  )
                ORDER BY s.updated_at DESC
            ");
            $stmt->execute([$currentUserId, $currentUserId, $currentUserId, $currentUserDeptId]);
            $sheets = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Format dates
            foreach ($sheets as &$sheet) {
                $sheet['isPublic'] = false; // logic matching dashboard JS
                $sheet['ownerName'] = $sheet['is_owner'] ? 'me' : ($sheet['owner_name'] ?: $sheet['owner_email']);
            }

            echo json_encode(['status' => 'success', 'spreadsheets' => $sheets]);
            break;

        case 'load':
            $uuid = $_GET['id'] ?? '';
            if ($uuid === '') {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Missing sheet identifier.']);
                exit;
            }

            // Load spreadsheet
            $stmt = $pdo->prepare("
                SELECT s.*, CONCAT(e.first_name, ' ', COALESCE(e.last_name, '')) as owner_name
                FROM hrm_spreadsheets s
                LEFT JOIN employees e ON s.created_by = e.id
                WHERE s.uuid = ? AND s.deleted_at IS NULL
                LIMIT 1
            ");
            $stmt->execute([$uuid]);
            $sheet = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$sheet) {
                http_response_code(404);
                echo json_encode(['status' => 'error', 'message' => 'Spreadsheet not found.']);
                exit;
            }

            $sheetId = (int)$sheet['id'];
            $ownerId = (int)$sheet['created_by'];

            // Determine user's authorization role
            $role = 'none';
            $isAuthorized = false;

            if ($currentUserId === $ownerId) {
                $role = 'owner';
                $isAuthorized = true;
            } else {
                // Check direct permissions
                $permStmt = $pdo->prepare("
                    SELECT permission_level FROM hrm_spreadsheet_permissions
                    WHERE spreadsheet_id = ? AND employee_id = ?
                    LIMIT 1
                ");
                $permStmt->execute([$sheetId, $currentUserId]);
                $permLevel = $permStmt->fetchColumn();

                if ($permLevel) {
                    $role = $permLevel === 'edit' ? 'editor' : 'viewer';
                    $isAuthorized = true;
                } elseif ($sheet['visibility'] === 'department' && $currentUserDeptId !== null && (int)$sheet['department_id'] === $currentUserDeptId) {
                    // Department access defaults to viewer (or editor if department is allowed, here viewer as default safe option)
                    $role = 'viewer';
                    $isAuthorized = true;
                } elseif ($sheet['visibility'] === 'public') {
                    $role = $sheet['public_access_level'] === 'edit' ? 'editor' : 'viewer';
                    $isAuthorized = true;
                }
            }

            if (!$isAuthorized) {
                // If not authorized, check if a request exists or let the frontend know so it can trigger "Request Access"
                echo json_encode([
                    'status' => 'denied',
                    'authorized' => false,
                    'role' => 'none',
                    'title' => $sheet['title'],
                    'ownerName' => $sheet['owner_name'] ?: $sheet['owner_email'],
                    'ownerEmail' => $sheet['owner_email']
                ]);
                exit;
            }

            // Load collaborators list for sharing UI
            $collabStmt = $pdo->prepare("
                SELECT p.permission_level as role, e.email, CONCAT(e.first_name, ' ', COALESCE(e.last_name, '')) as name
                FROM hrm_spreadsheet_permissions p
                JOIN employees e ON p.employee_id = e.id
                WHERE p.spreadsheet_id = ?
            ");
            $collabStmt->execute([$sheetId]);
            $collabs = $collabStmt->fetchAll(PDO::FETCH_ASSOC);

            $sharedUsers = [];
            foreach ($collabs as $c) {
                $sharedUsers[strtolower($c['email'])] = $c['role'];
            }

            // Convert MySQL rows to frontend sheet data object structure
            $sheetsData = json_decode($sheet['data_json'], true) ?: [];

            echo json_encode([
                'status' => 'success',
                'authorized' => true,
                'role' => $role,
                'title' => $sheet['title'],
                'ownerEmail' => $sheet['owner_email'],
                'ownerName' => $sheet['owner_name'] ?: $sheet['owner_email'],
                'isPublic' => ($sheet['visibility'] === 'public'),
                'publicRole' => ($sheet['visibility'] === 'public' ? ($sheet['public_access_level'] === 'edit' ? 'editor' : 'viewer') : 'none'),
                'visibility' => $sheet['visibility'],
                'sharedUsers' => $sharedUsers,
                'sheets' => $sheetsData,
                'numRows' => 100 // default grid rows
            ]);
            break;

        case 'save':
            $raw = file_get_contents('php://input');
            $payload = json_decode($raw, true);

            if (!$payload || !isset($payload['id'])) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Invalid save payload.']);
                exit;
            }

            $uuid = $payload['id'];
            $title = $payload['title'] ?? 'Untitled spreadsheet';
            $sheetsData = $payload['sheets'] ?? null;

            // Load spreadsheet row
            $stmt = $pdo->prepare("SELECT id, created_by, visibility, public_access_level FROM hrm_spreadsheets WHERE uuid = ? AND deleted_at IS NULL LIMIT 1");
            $stmt->execute([$uuid]);
            $sheet = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$sheet) {
                http_response_code(404);
                echo json_encode(['status' => 'error', 'message' => 'Spreadsheet not found.']);
                exit;
            }

            $sheetId = (int)$sheet['id'];
            $ownerId = (int)$sheet['created_by'];

            // Edit authorization check
            $canEdit = false;
            if ($currentUserId === $ownerId) {
                $canEdit = true;
            } else {
                $permStmt = $pdo->prepare("
                    SELECT permission_level FROM hrm_spreadsheet_permissions
                    WHERE spreadsheet_id = ? AND employee_id = ? LIMIT 1
                ");
                $permStmt->execute([$sheetId, $currentUserId]);
                $perm = $permStmt->fetchColumn();
                if ($perm === 'edit') {
                    $canEdit = true;
                } elseif ($sheet['visibility'] === 'public' && $sheet['public_access_level'] === 'edit') {
                    $canEdit = true;
                }
            }

            if (!$canEdit) {
                http_response_code(403);
                echo json_encode(['status' => 'error', 'message' => 'Unauthorized: You do not have edit rights on this sheet.']);
                exit;
            }

            // Update database row
            if ($sheetsData !== null) {
                $upd = $pdo->prepare("UPDATE hrm_spreadsheets SET title = ?, data_json = ?, updated_at = NOW() WHERE id = ?");
                $upd->execute([$title, json_encode($sheetsData), $sheetId]);
            } else {
                $upd = $pdo->prepare("UPDATE hrm_spreadsheets SET title = ?, updated_at = NOW() WHERE id = ?");
                $upd->execute([$title, $sheetId]);
            }

            echo json_encode(['status' => 'success', 'message' => 'Spreadsheet saved successfully.']);
            break;

        case 'delete':
            $uuid = $_POST['id'] ?? '';
            if ($uuid === '') {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Missing sheet ID.']);
                exit;
            }

            // Enforce owner/admin delete restriction
            $stmt = $pdo->prepare("SELECT id, created_by FROM hrm_spreadsheets WHERE uuid = ? LIMIT 1");
            $stmt->execute([$uuid]);
            $sheet = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$sheet) {
                http_response_code(404);
                echo json_encode(['status' => 'error', 'message' => 'Spreadsheet not found.']);
                exit;
            }

            if ((int)$sheet['created_by'] !== $currentUserId && !Auth::isAdmin()) {
                http_response_code(403);
                echo json_encode(['status' => 'error', 'message' => 'Only the owner or an administrator can delete this spreadsheet.']);
                exit;
            }

            $del = $pdo->prepare("UPDATE hrm_spreadsheets SET deleted_at = NOW() WHERE uuid = ?");
            $del->execute([$uuid]);

            echo json_encode(['status' => 'success', 'message' => 'Spreadsheet deleted.']);
            break;

        case 'share':
            $raw = file_get_contents('php://input');
            $data = json_decode($raw, true);

            if (!$data || !isset($data['spreadsheet_id'])) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Missing spreadsheet details.']);
                exit;
            }

            $uuid = $data['spreadsheet_id'];
            $stmt = $pdo->prepare("SELECT id, created_by, title FROM hrm_spreadsheets WHERE uuid = ? LIMIT 1");
            $stmt->execute([$uuid]);
            $sheet = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$sheet) {
                http_response_code(404);
                echo json_encode(['status' => 'error', 'message' => 'Spreadsheet not found.']);
                exit;
            }

            $sheetId = (int)$sheet['id'];

            if ((int)$sheet['created_by'] !== $currentUserId) {
                http_response_code(403);
                echo json_encode(['status' => 'error', 'message' => 'Only the owner can modify sharing permissions.']);
                exit;
            }

            // 1. Handle General Visibility (Link access) updates
            if (isset($data['visibility'])) {
                $vis = $data['visibility']; // 'private', 'department', 'public'
                $publicAccess = $data['public_role'] ?? 'none'; // 'none', 'viewer', 'editor'
                
                $dbVis = 'private';
                $dbPubRole = 'view';
                
                if ($vis === 'public') {
                    $dbVis = 'public';
                    $dbPubRole = ($publicAccess === 'editor') ? 'edit' : 'view';
                } elseif ($vis === 'department') {
                    $dbVis = 'department';
                }

                $updVis = $pdo->prepare("UPDATE hrm_spreadsheets SET visibility = ?, public_access_level = ? WHERE id = ?");
                $updVis->execute([$dbVis, $dbPubRole, $sheetId]);
            }

            // 2. Handle Individual User Collaborator additions
            if (isset($data['email']) && trim($data['email']) !== '') {
                $collabEmail = strtolower(trim($data['email']));
                $role = ($data['role'] ?? 'viewer') === 'editor' ? 'edit' : 'view';

                // Find employee by email
                $empStmt = $pdo->prepare("SELECT id, first_name, last_name FROM employees WHERE email = ? AND deleted_at IS NULL LIMIT 1");
                $empStmt->execute([$collabEmail]);
                $emp = $empStmt->fetch(PDO::FETCH_ASSOC);

                if (!$emp) {
                    http_response_code(404);
                    echo json_encode(['status' => 'error', 'message' => "Employee with email '{$collabEmail}' not found in HRM."]);
                    exit;
                }

                $collabEmpId = (int)$emp['id'];

                $insPerm = $pdo->prepare("
                    INSERT INTO hrm_spreadsheet_permissions (spreadsheet_id, employee_id, permission_level)
                    VALUES (?, ?, ?)
                    ON DUPLICATE KEY UPDATE permission_level = VALUES(permission_level)
                ");
                $insPerm->execute([$sheetId, $collabEmpId, $role]);

                // In-App HRM System Notification
                $roleText = ($role === 'edit') ? 'Editor' : 'Viewer';
                NotificationHelper::add(
                    [$collabEmpId],
                    'Spreadsheet Shared With You',
                    "{$currentUserName} shared \"{$sheet['title']}\" with you as a {$roleText}.",
                    "/sheets/editor?id={$uuid}",
                    'System',
                    $currentUserId
                );
            }

            // 3. Handle Collaborator removals
            if (isset($data['remove_email'])) {
                $removeEmail = strtolower(trim($data['remove_email']));
                $delStmt = $pdo->prepare("
                    DELETE p FROM hrm_spreadsheet_permissions p
                    JOIN employees e ON p.employee_id = e.id
                    WHERE p.spreadsheet_id = ? AND e.email = ?
                ");
                $delStmt->execute([$sheetId, $removeEmail]);
            }

            echo json_encode(['status' => 'success', 'message' => 'Sharing permissions updated.']);
            break;

        case 'request_access':
            $uuid = $_POST['spreadsheet_id'] ?? '';
            $role = ($_POST['requested_role'] ?? 'viewer') === 'editor' ? 'edit' : 'view';
            $msg = isset($_POST['message']) ? trim((string)$_POST['message']) : '';

            $stmt = $pdo->prepare("SELECT id, created_by, owner_email, title FROM hrm_spreadsheets WHERE uuid = ? LIMIT 1");
            $stmt->execute([$uuid]);
            $sheet = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$sheet) {
                http_response_code(404);
                echo json_encode(['status' => 'error', 'message' => 'Spreadsheet not found.']);
                exit;
            }

            $sheetId = (int)$sheet['id'];
            $ownerId = (int)$sheet['created_by'];
            $ownerEmail = $sheet['owner_email'];

            // Log access request
            $insReq = $pdo->prepare("
                INSERT INTO hrm_sheet_access_requests (spreadsheet_id, requester_id, owner_id, requested_level, request_message, status, created_at)
                VALUES (?, ?, ?, ?, ?, 'pending', NOW())
            ");
            $insReq->execute([$sheetId, $currentUserId, $ownerId, $role, $msg]);

            // In-App HRM System Notification to owner
            $roleText = ($role === 'edit') ? 'Editor' : 'Viewer';
            NotificationHelper::add(
                [$ownerId],
                'Spreadsheet Access Request',
                "{$currentUserName} requested {$roleText} access to \"{$sheet['title']}\".",
                '/sheets',
                'System',
                $currentUserId
            );

            echo json_encode(['status' => 'success', 'message' => 'Access request submitted to spreadsheet owner.']);
            break;

        case 'list_requests':
            // Lists pending access requests for spreadsheets owned by the logged-in user
            $stmt = $pdo->prepare("
                SELECT r.id, r.requested_level as requested_role, r.request_message as message, r.created_at,
                       s.title as spreadsheet_title, s.uuid as spreadsheet_id,
                       e.email as requestor_email, CONCAT(e.first_name, ' ', COALESCE(e.last_name, '')) as requestor_name,
                       e.profile_pic
                FROM hrm_sheet_access_requests r
                JOIN hrm_spreadsheets s ON r.spreadsheet_id = s.id
                JOIN employees e ON r.requester_id = e.id
                WHERE r.owner_id = ? AND r.status = 'pending'
                ORDER BY r.created_at DESC
            ");
            $stmt->execute([$currentUserId]);
            $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode(['status' => 'success', 'requests' => $requests]);
            break;

        case 'handle_request':
            $reqId = isset($_POST['request_id']) ? (int)$_POST['request_id'] : 0;
            $decision = $_POST['decision'] ?? ''; // 'approve' or 'decline'

            if ($reqId <= 0 || !in_array($decision, ['approve', 'decline'], true)) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Invalid parameters.']);
                exit;
            }

            $stmt = $pdo->prepare("
                SELECT r.*, s.title as spreadsheet_title, s.uuid as spreadsheet_uuid
                FROM hrm_sheet_access_requests r
                JOIN hrm_spreadsheets s ON r.spreadsheet_id = s.id
                WHERE r.id = ? LIMIT 1
            ");
            $stmt->execute([$reqId]);
            $req = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$req) {
                http_response_code(404);
                echo json_encode(['status' => 'error', 'message' => 'Access request not found.']);
                exit;
            }

            if ((int)$req['owner_id'] !== $currentUserId) {
                http_response_code(403);
                echo json_encode(['status' => 'error', 'message' => 'Unauthorized: Only the owner can handle requests.']);
                exit;
            }

            $pdo->beginTransaction();
            try {
                if ($decision === 'approve') {
                    // Update permissions table
                    $insPerm = $pdo->prepare("
                        INSERT INTO hrm_spreadsheet_permissions (spreadsheet_id, employee_id, permission_level)
                        VALUES (?, ?, ?)
                        ON DUPLICATE KEY UPDATE permission_level = VALUES(permission_level)
                    ");
                    $insPerm->execute([$req['spreadsheet_id'], $req['requester_id'], $req['requested_level']]);

                    // Update request status
                    $updReq = $pdo->prepare("UPDATE hrm_sheet_access_requests SET status = 'approved', updated_at = NOW() WHERE id = ?");
                    $updReq->execute([$reqId]);
                } else {
                    $updReq = $pdo->prepare("UPDATE hrm_sheet_access_requests SET status = 'rejected', updated_at = NOW() WHERE id = ?");
                    $updReq->execute([$reqId]);
                }

                $pdo->commit();

                // In-App HRM System Notification to requester
                $roleText = ($req['requested_level'] === 'edit') ? 'Editor' : 'Viewer';
                if ($decision === 'approve') {
                    NotificationHelper::add(
                        [(int)$req['requester_id']],
                        'Spreadsheet Access Granted',
                        "Your request for {$roleText} access to \"{$req['spreadsheet_title']}\" was approved.",
                        "/sheets/editor?id={$req['spreadsheet_uuid']}",
                        'System',
                        $currentUserId
                    );
                } else {
                    NotificationHelper::add(
                        [(int)$req['requester_id']],
                        'Spreadsheet Access Declined',
                        "Your request for access to \"{$req['spreadsheet_title']}\" was declined.",
                        '/sheets',
                        'System',
                        $currentUserId
                    );
                }

                echo json_encode(['status' => 'success', 'message' => 'Request successfully handled.']);
            } catch (\Exception $e) {
                $pdo->rollBack();
                http_response_code(500);
                echo json_encode(['status' => 'error', 'message' => 'Failed to process request: ' . $e->getMessage()]);
            }
            break;

        case 'search_members':
            // Fetch active employees
            $empList = $pdo->query("
                SELECT email, CONCAT(first_name, ' ', COALESCE(last_name, '')) as name
                FROM employees
                WHERE deleted_at IS NULL AND status = 'Active'
                ORDER BY first_name ASC
            ")->fetchAll(PDO::FETCH_ASSOC);

            // Fetch departments
            $deptList = $pdo->query("
                SELECT id, name
                FROM departments
                WHERE deleted_at IS NULL
                ORDER BY name ASC
            ")->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                'status' => 'success',
                'employees' => $empList,
                'departments' => $deptList
            ]);
            break;

        default:
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => "Invalid API action: '{$action}'."]);
            break;
    }
} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'API Execution Failure: ' . $e->getMessage()]);
}
