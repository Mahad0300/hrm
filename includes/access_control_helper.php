<?php
/**
 * HR portal page & action permissions (managed by Admin only).
 */

require_once __DIR__ . '/db_connect.php';

/** Pages that HR can never access regardless of matrix (admin-only management). */
const HR_ADMIN_ONLY_PAGES = ['role-management'];

/**
 * Registry: page_key => php filename in hr/ portal.
 */
function hrAccessPageRegistry(): array
{
    return [
        'index' => ['file' => 'index.php', 'sidebar' => true, 'section' => 'main'],
        'employees' => ['file' => 'employees.php', 'sidebar' => true, 'section' => 'organization'],
        'attendance' => ['file' => 'attendance.php', 'sidebar' => true, 'section' => 'organization'],
        'leave-management' => ['file' => 'leave-management.php', 'sidebar' => true, 'section' => 'organization'],
        'new-joining' => ['file' => 'new-joining.php', 'sidebar' => true, 'section' => 'organization'],
        'hierarchy' => ['file' => 'hierarchy.php', 'sidebar' => true, 'section' => 'organization'],
        'kpi-management' => ['file' => 'kpi-management.php', 'sidebar' => true, 'section' => 'organization'],
        'event-calendar' => ['file' => 'event-calendar.php', 'sidebar' => true, 'section' => 'organization'],
        'job-list' => ['file' => 'job-list.php', 'sidebar' => true, 'section' => 'jobs'],
        'create-job' => ['file' => 'create-job.php', 'sidebar' => true, 'section' => 'jobs'],
        'job-candidates' => ['file' => 'job-candidates.php', 'sidebar' => true, 'section' => 'jobs'],
        'interviews' => ['file' => 'interviews.php', 'sidebar' => true, 'section' => 'jobs'],
        'payroll' => ['file' => 'payroll.php', 'sidebar' => true, 'section' => 'administration'],
        'activity-logs' => ['file' => 'activity-logs.php', 'sidebar' => true, 'section' => 'administration'],
        'announcements' => ['file' => 'announcements.php', 'sidebar' => true, 'section' => 'administration'],
        'notifications' => ['file' => 'notifications.php', 'sidebar' => true, 'section' => 'administration'],
        'it-support' => ['file' => 'it-support.php', 'sidebar' => true, 'section' => 'administration'],
        'shifts' => ['file' => 'shifts.php', 'sidebar' => true, 'section' => 'system'],
        'department-management' => ['file' => 'department-management.php', 'sidebar' => true, 'section' => 'system'],
        'role-management' => ['file' => 'role-management.php', 'sidebar' => true, 'section' => 'system'],
        'policy-management' => ['file' => 'policy-management.php', 'sidebar' => true, 'section' => 'system'],
        'payroll-settings' => ['file' => 'payroll-settings.php', 'sidebar' => true, 'section' => 'system'],
        'employee-profile' => ['file' => 'employee-profile.php', 'sidebar' => false, 'section' => 'detail'],
        'attendance-log' => ['file' => 'attendance-log.php', 'sidebar' => false, 'section' => 'detail'],
        'edit-job' => ['file' => 'edit-job.php', 'sidebar' => false, 'section' => 'detail'],
        'candidate-detail' => ['file' => 'candidate-detail.php', 'sidebar' => false, 'section' => 'detail'],
        'kpi-report' => ['file' => 'kpi-report.php', 'sidebar' => false, 'section' => 'detail'],
        'payslip-print' => ['file' => 'payslip-print.php', 'sidebar' => false, 'section' => 'detail'],
    ];
}

/**
 * Action types each HR page actually supports (matrix + enforcement).
 * Only listed types appear in Admin access control for that page.
 */
function hrPageCapabilities(): array
{
    return [
        'index' => ['view'],
        'employees' => ['view', 'create', 'edit', 'delete'],
        'attendance' => ['view', 'edit'],
        'leave-management' => ['view', 'edit'],
        'new-joining' => ['view', 'create', 'delete'],
        'hierarchy' => ['view'],
        'kpi-management' => ['view', 'create', 'edit', 'delete'],
        'event-calendar' => ['view', 'create', 'edit', 'delete'],
        'job-list' => ['view', 'create', 'edit', 'toggle_status'],
        'create-job' => [],
        'job-candidates' => ['view', 'schedule_interview', 'update_pipeline', 'reject_ban'],
        'interviews' => [],
        'payroll' => ['view', 'create', 'edit', 'export'],
        'activity-logs' => ['view'],
        'announcements' => ['view', 'create', 'edit', 'delete'],
        'notifications' => ['view', 'mark_read', 'delete'],
        'it-support' => ['view', 'edit'],
        'shifts' => ['view', 'create', 'edit', 'delete'],
        'department-management' => ['view', 'create', 'edit', 'delete'],
        'role-management' => [],
        'policy-management' => ['view', 'create', 'edit', 'delete'],
        'payroll-settings' => ['view', 'edit'],
        'employee-profile' => [],
        'attendance-log' => [],
        'edit-job' => ['view', 'edit'],
        'candidate-detail' => [],
        'kpi-report' => [],
        'payslip-print' => ['view', 'export'],
    ];
}

function hrPageMatrixSections(): array
{
    return [
        'Main Menu' => ['index'],
        'Organization' => ['employees', 'attendance', 'leave-management', 'new-joining', 'hierarchy', 'kpi-management', 'event-calendar'],
        'Job Management' => ['job-list', 'job-candidates'],
        'Administration' => ['payroll', 'activity-logs', 'announcements', 'notifications', 'it-support'],
        'System' => ['shifts', 'department-management', 'role-management', 'policy-management', 'payroll-settings'],
        'Detail & Linked Pages' => ['employee-profile', 'attendance-log', 'create-job', 'edit-job', 'candidate-detail', 'interviews', 'kpi-report', 'payslip-print'],
    ];
}

function hrPageMatrixLabels(): array
{
    return [
        'index' => ['label' => 'Dashboard', 'icon' => 'layout-dashboard'],
        'employees' => ['label' => 'Employees', 'icon' => 'users'],
        'attendance' => ['label' => 'Attendance', 'icon' => 'calendar-check'],
        'leave-management' => ['label' => 'Leave Management', 'icon' => 'clock'],
        'new-joining' => ['label' => 'New Joining', 'icon' => 'user-plus'],
        'hierarchy' => ['label' => 'Hierarchy', 'icon' => 'network'],
        'kpi-management' => ['label' => 'KPI Management', 'icon' => 'line-chart'],
        'event-calendar' => ['label' => 'Event Calendar', 'icon' => 'calendar'],
        'job-list' => ['label' => 'Job Postings', 'icon' => 'list'],
        'create-job' => ['label' => 'Create New Job', 'icon' => 'plus-circle'],
        'job-candidates' => ['label' => 'Candidate Pool', 'icon' => 'users'],
        'interviews' => ['label' => 'Interviews', 'icon' => 'calendar'],
        'payroll' => ['label' => 'Payroll', 'icon' => 'banknote'],
        'activity-logs' => ['label' => 'Activity Logs', 'icon' => 'history'],
        'announcements' => ['label' => 'Announcements', 'icon' => 'megaphone'],
        'notifications' => ['label' => 'Notifications', 'icon' => 'bell'],
        'it-support' => ['label' => 'IT Helpdesk', 'icon' => 'headset'],
        'shifts' => ['label' => 'Add Shift', 'icon' => 'plus-circle'],
        'department-management' => ['label' => 'Dept Management', 'icon' => 'building-2'],
        'role-management' => ['label' => 'Access Control', 'icon' => 'shield-check', 'badge' => 'Admin only'],
        'policy-management' => ['label' => 'Policy Management', 'icon' => 'file-text'],
        'payroll-settings' => ['label' => 'Payroll Cycle', 'icon' => 'calculator'],
        'employee-profile' => ['label' => 'Employee Profile', 'icon' => 'user'],
        'attendance-log' => ['label' => 'Attendance History', 'icon' => 'clipboard-list'],
        'edit-job' => ['label' => 'Edit Job', 'icon' => 'pencil'],
        'candidate-detail' => ['label' => 'Candidate Detail', 'icon' => 'user-search'],
        'kpi-report' => ['label' => 'KPI Scorecard', 'icon' => 'bar-chart-2'],
        'payslip-print' => ['label' => 'Payslip Print', 'icon' => 'file-output'],
    ];
}

/** Detail/linked pages inherit permissions from parent module. */
function hrPagePermissionParent(): array
{
    return [
        'attendance-log' => 'attendance',
        'employee-profile' => 'employees',
        'create-job' => 'job-list',
        'edit-job' => 'job-list',
        'candidate-detail' => 'job-candidates',
        'interviews' => 'job-candidates',
        'kpi-report' => 'kpi-management',
        'payslip-print' => 'payroll',
    ];
}

function hrPageInheritsView(string $pageKey): bool
{
    return isset(hrPagePermissionParent()[$pageKey]);
}

function hrResolvePermissionPageKey(string $pageKey, string $type = 'view'): string
{
    $parent = hrPagePermissionParent()[$pageKey] ?? null;
    if (!$parent) {
        return $pageKey;
    }
    if ($type === 'view') {
        return $parent;
    }
    // Create / edit job forms use job-list action permissions (same as HR UI)
    if ($pageKey === 'create-job' && $type === 'create') {
        return 'job-list';
    }
    if ($pageKey === 'edit-job' && $type === 'edit') {
        return 'job-list';
    }
    if ($pageKey === 'candidate-detail' && $type !== 'view') {
        return 'job-candidates';
    }
    if ($pageKey === 'interviews' && in_array($type, ['create', 'edit'], true)) {
        return 'job-candidates';
    }
    if (!hrPageSupportsCapability($pageKey, $type)) {
        return $parent;
    }
    return $pageKey;
}

function hrPageSupportsCapability(string $pageKey, string $type): bool
{
    if (in_array($pageKey, HR_ADMIN_ONLY_PAGES, true)) {
        return false;
    }
    if ($type === 'view') {
        return true;
    }
    $caps = hrPageCapabilities();
    return in_array($type, $caps[$pageKey] ?? [], true);
}

/** Map permission type to DB column (mark_read stores in can_edit for notifications). */
function hrCapabilityDbColumn(string $type): ?string
{
    $map = [
        'view' => 'can_view',
        'create' => 'can_create',
        'edit' => 'can_edit',
        'mark_read' => 'can_edit',
        'toggle_status' => 'can_delete',
        'schedule_interview' => 'can_create',
        'update_pipeline' => 'can_edit',
        'reject_ban' => 'can_delete',
        'delete' => 'can_delete',
        'export' => 'can_export',
    ];
    return $map[$type] ?? null;
}

/** Matrix column (create|edit|delete|export) → capability for a page. */
function hrMatrixColumnCapability(string $pageKey, string $column): ?string
{
    if ($column === 'create' && hrPageSupportsCapability($pageKey, 'schedule_interview') && !hrPageSupportsCapability($pageKey, 'create')) {
        return 'schedule_interview';
    }
    if ($column === 'edit' && hrPageSupportsCapability($pageKey, 'mark_read') && !hrPageSupportsCapability($pageKey, 'edit')) {
        return 'mark_read';
    }
    if ($column === 'edit' && hrPageSupportsCapability($pageKey, 'update_pipeline') && !hrPageSupportsCapability($pageKey, 'edit')) {
        return 'update_pipeline';
    }
    if ($column === 'delete' && hrPageSupportsCapability($pageKey, 'toggle_status') && !hrPageSupportsCapability($pageKey, 'delete')) {
        return 'toggle_status';
    }
    if ($column === 'delete' && hrPageSupportsCapability($pageKey, 'reject_ban') && !hrPageSupportsCapability($pageKey, 'delete')) {
        return 'reject_ban';
    }
    if (!in_array($column, ['create', 'edit', 'delete', 'export'], true)) {
        return null;
    }
    return hrPageSupportsCapability($pageKey, $column) ? $column : null;
}

function hrMatrixCapabilityLabel(string $cap): string
{
    $labels = [
        'create' => 'Create',
        'edit' => 'Edit / Update',
        'mark_read' => 'Mark as Read',
        'toggle_status' => 'Active / Close',
        'schedule_interview' => 'Schedule Interview',
        'update_pipeline' => 'Update Pipeline',
        'reject_ban' => 'Reject / Ban',
        'delete' => 'Delete',
        'export' => 'Export / PDF',
    ];
    return $labels[$cap] ?? $cap;
}

function hrNormalizePermissionRow(string $pageKey, array $perm): array
{
    if (in_array($pageKey, HR_ADMIN_ONLY_PAGES, true)) {
        return [
            'can_view' => 0,
            'can_create' => 0,
            'can_edit' => 0,
            'can_delete' => 0,
            'can_export' => 0,
        ];
    }

    if (hrPageInheritsView($pageKey)) {
        $perm['can_view'] = 0;
    }

    $supportsCreateCol = hrPageSupportsCapability($pageKey, 'create') || hrPageSupportsCapability($pageKey, 'schedule_interview');
    $supportsEditCol = hrPageSupportsCapability($pageKey, 'edit')
        || hrPageSupportsCapability($pageKey, 'mark_read')
        || hrPageSupportsCapability($pageKey, 'update_pipeline');
    $supportsDeleteCol = hrPageSupportsCapability($pageKey, 'delete')
        || hrPageSupportsCapability($pageKey, 'toggle_status')
        || hrPageSupportsCapability($pageKey, 'reject_ban');

    return [
        'can_view' => !empty($perm['can_view']) ? 1 : 0,
        'can_create' => $supportsCreateCol && !empty($perm['can_create']) ? 1 : 0,
        'can_edit' => $supportsEditCol && !empty($perm['can_edit']) ? 1 : 0,
        'can_delete' => $supportsDeleteCol && !empty($perm['can_delete']) ? 1 : 0,
        'can_export' => hrPageSupportsCapability($pageKey, 'export') && !empty($perm['can_export']) ? 1 : 0,
    ];
}

function hrAccessDefaultPermissions(): array
{
    $defaults = [];
    foreach (hrAccessPageRegistry() as $pageKey => $_meta) {
        if (in_array($pageKey, HR_ADMIN_ONLY_PAGES, true)) {
            $defaults[$pageKey] = [
                'can_view' => 0, 'can_create' => 0, 'can_edit' => 0, 'can_delete' => 0, 'can_export' => 0,
            ];
            continue;
        }
        $supportsCreateCol = hrPageSupportsCapability($pageKey, 'create') || hrPageSupportsCapability($pageKey, 'schedule_interview');
        $supportsEditCol = hrPageSupportsCapability($pageKey, 'edit')
            || hrPageSupportsCapability($pageKey, 'mark_read')
            || hrPageSupportsCapability($pageKey, 'update_pipeline');
        $supportsDeleteCol = hrPageSupportsCapability($pageKey, 'delete')
            || hrPageSupportsCapability($pageKey, 'toggle_status')
            || hrPageSupportsCapability($pageKey, 'reject_ban');
        $defaults[$pageKey] = [
            'can_view' => 1,
            'can_create' => $supportsCreateCol ? 1 : 0,
            'can_edit' => $supportsEditCol ? 1 : 0,
            'can_delete' => $supportsDeleteCol ? 1 : 0,
            'can_export' => hrPageSupportsCapability($pageKey, 'export') ? 1 : 0,
        ];
        // HR Helpdesk: view queue & chats only unless Admin enables Edit in matrix
        if ($pageKey === 'it-support') {
            $defaults[$pageKey]['can_edit'] = 0;
        }
    }
    return $defaults;
}

function isHrPortalUser(): bool
{
    return isLoggedIn() && ($_SESSION['user_role'] ?? '') === 'HR';
}

function hrResolvePageKey(?string $phpFile = null): ?string
{
    $phpFile = $phpFile ?? basename($_SERVER['PHP_SELF'] ?? '');
    foreach (hrAccessPageRegistry() as $key => $meta) {
        if ($meta['file'] === $phpFile) {
            return $key;
        }
    }
    return null;
}

function hrPermissionsRevision(PDO $pdo): int
{
    $stmt = $pdo->prepare("SELECT meta_value FROM settings WHERE meta_key = 'hr_permissions_revision' LIMIT 1");
    $stmt->execute();
    return (int) ($stmt->fetchColumn() ?: 1);
}

function hrBumpPermissionsRevision(PDO $pdo): void
{
    $stmt = $pdo->prepare("SELECT id FROM settings WHERE meta_key = 'hr_permissions_revision' LIMIT 1");
    $stmt->execute();
    if ($stmt->fetchColumn()) {
        $pdo->exec("UPDATE settings SET meta_value = meta_value + 1 WHERE meta_key = 'hr_permissions_revision'");
    } else {
        $pdo->prepare("INSERT INTO settings (meta_key, meta_value) VALUES ('hr_permissions_revision', '2')")->execute();
    }
}

function hrFetchAllPermissions(PDO $pdo): array
{
    $defaults = hrAccessDefaultPermissions();
    $registry = hrAccessPageRegistry();
    $rows = [];

    try {
        $stmt = $pdo->query("SELECT page_key, can_view, can_create, can_edit, can_delete, can_export FROM hr_page_permissions");
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $rows[$row['page_key']] = [
                'can_view' => (int) $row['can_view'],
                'can_create' => (int) $row['can_create'],
                'can_edit' => (int) $row['can_edit'],
                'can_delete' => (int) $row['can_delete'],
                'can_export' => (int) $row['can_export'],
            ];
        }
    } catch (PDOException $e) {
        // Table may not exist yet — fall back to defaults.
    }

    $merged = [];
    foreach ($registry as $pageKey => $meta) {
        $perm = $rows[$pageKey] ?? ($defaults[$pageKey] ?? [
            'can_view' => 0, 'can_create' => 0, 'can_edit' => 0, 'can_delete' => 0, 'can_export' => 0,
        ]);
        if (in_array($pageKey, HR_ADMIN_ONLY_PAGES, true)) {
            $perm = ['can_view' => 0, 'can_create' => 0, 'can_edit' => 0, 'can_delete' => 0, 'can_export' => 0];
        } else {
            $perm = hrNormalizePermissionRow($pageKey, $perm);
        }
        $merged[$pageKey] = array_merge($perm, ['page_key' => $pageKey, 'file' => $meta['file']]);
    }

    return $merged;
}

function hrGetPagePermission(PDO $pdo, string $pageKey): array
{
    $all = hrFetchAllPermissions($pdo);
    return $all[$pageKey] ?? [
        'can_view' => 0, 'can_create' => 0, 'can_edit' => 0, 'can_delete' => 0, 'can_export' => 0,
    ];
}

function hrCanAccessInterviewsModule(PDO $pdo): bool
{
    return hrCan($pdo, 'job-candidates', 'view') && hrCan($pdo, 'job-candidates', 'schedule_interview');
}

/** Sidebar / landing pages checked in menu order when redirecting after denied access. */
function hrPortalLandingPageOrder(): array
{
    return [
        'index',
        'employees', 'attendance', 'leave-management', 'new-joining', 'hierarchy',
        'kpi-management', 'event-calendar',
        'job-list', 'create-job', 'job-candidates', 'interviews',
        'payroll', 'activity-logs', 'announcements', 'notifications', 'it-support',
        'shifts', 'department-management', 'policy-management', 'payroll-settings',
    ];
}

function hrCanViewPortalPage(PDO $pdo, string $pageKey): bool
{
    if (!isHrPortalUser()) {
        return true;
    }
    if (in_array($pageKey, HR_ADMIN_ONLY_PAGES, true)) {
        return false;
    }
    if ($pageKey === 'interviews') {
        return hrCanAccessInterviewsModule($pdo);
    }
    if (!hrCan($pdo, $pageKey, 'view')) {
        return false;
    }
    if ($pageKey === 'create-job' && !hrCan($pdo, 'job-list', 'create')) {
        return false;
    }
    return true;
}

function hrFindFirstAllowedPage(PDO $pdo, ?string $excludePageKey = null): ?string
{
    $registry = hrAccessPageRegistry();
    foreach (hrPortalLandingPageOrder() as $pageKey) {
        if ($pageKey === $excludePageKey || !isset($registry[$pageKey])) {
            continue;
        }
        if (hrCanViewPortalPage($pdo, $pageKey)) {
            return $pageKey;
        }
    }
    return null;
}

function hrHasAnyPortalAccess(PDO $pdo): bool
{
    return hrFindFirstAllowedPage($pdo) !== null;
}

function hrResolveDeniedRedirectFile(PDO $pdo, ?string $currentPageKey): ?string
{
    if ($currentPageKey !== 'index' && hrCanViewPortalPage($pdo, 'index')) {
        return 'index.php';
    }

    $fallbackKey = hrFindFirstAllowedPage($pdo, $currentPageKey);
    if ($fallbackKey === null) {
        return null;
    }

    return hrAccessPageRegistry()[$fallbackKey]['file'];
}

function hrHandleDeniedPageAccess(PDO $pdo, ?string $currentPageKey): void
{
    $target = hrResolveDeniedRedirectFile($pdo, $currentPageKey);
    if ($target !== null) {
        header('Location: ' . $target);
        exit;
    }

    if ($currentPageKey !== 'index') {
        header('Location: index.php');
        exit;
    }

    $GLOBALS['hr_access_denied'] = true;
}

function hrHrPortalLoginPath(PDO $pdo): string
{
    $first = hrFindFirstAllowedPage($pdo);
    if ($first === null || $first === 'index') {
        return 'hr/index.php';
    }

    return 'hr/' . hrAccessPageRegistry()[$first]['file'];
}

function hrCan(PDO $pdo, string $pageKey, string $type = 'view'): bool
{
    if (!isHrPortalUser()) {
        return true;
    }
    if (in_array($pageKey, HR_ADMIN_ONLY_PAGES, true)) {
        return false;
    }

    if ($pageKey === 'interviews' && in_array($type, ['create', 'edit'], true)) {
        $type = 'schedule_interview';
    }

    $effectiveKey = hrResolvePermissionPageKey($pageKey, $type);

    if ($effectiveKey === $pageKey && !hrPageSupportsCapability($pageKey, $type)) {
        return false;
    }
    if (!hrPageSupportsCapability($effectiveKey, $type)) {
        return false;
    }

    $perm = hrGetPagePermission($pdo, $effectiveKey);
    $col = hrCapabilityDbColumn($type) ?? 'can_view';
    return !empty($perm[$col]);
}

function hrEnforcePageAccess(PDO $pdo, ?string $pageKey): void
{
    if (!isHrPortalUser() || $pageKey === null) {
        return;
    }
    if (in_array($pageKey, HR_ADMIN_ONLY_PAGES, true)) {
        $_SESSION['error'] = 'Access denied: You do not have permission to view this page.';
        hrHandleDeniedPageAccess($pdo, $pageKey);
        return;
    }

    $canView = hrCan($pdo, $pageKey, 'view');
    if ($pageKey === 'interviews' && !hrCanAccessInterviewsModule($pdo)) {
        $canView = false;
    }

    if (!$canView) {
        $_SESSION['error'] = 'Access denied: You do not have permission to view this page.';
        hrHandleDeniedPageAccess($pdo, $pageKey);
        return;
    }

    if ($pageKey === 'create-job' && !hrCan($pdo, 'job-list', 'create')) {
        $_SESSION['error'] = 'Access denied: You do not have permission to create jobs.';
        hrHandleDeniedPageAccess($pdo, $pageKey);
        return;
    }
}

function hrCanViewSidebarPage(PDO $pdo, string $pageKey): bool
{
    return hrCanViewPortalPage($pdo, $pageKey);
}

/** Map API handler basename to default page key. */
function hrApiHandlerPageMap(): array
{
    return [
        'employee_handler.php' => 'employees',
        'attendance_handler.php' => 'attendance',
        'leave_status_handler.php' => 'leave-management',
        'leave_type_handler.php' => 'leave-management',
        'payroll_handler.php' => 'payroll',
        'settings_handler.php' => 'payroll-settings',
        'job_handler.php' => 'job-list',
        'kpi_handler.php' => 'kpi-management',
        'department_handler.php' => 'department-management',
        'shift_handler.php' => 'shifts',
        'announcement_handler.php' => 'announcements',
        'activity_handler.php' => 'activity-logs',
        'calendar_handler.php' => 'event-calendar',
        'policy_handler.php' => 'policy-management',
        'notification_handler.php' => 'notifications',
        'it-support-handler.php' => 'it-support',
    ];
}

function hrResolveApiPageKey(string $action, ?string $handlerFile = null): string
{
    $handlerFile = $handlerFile ?? basename($_SERVER['SCRIPT_NAME'] ?? '');
    $defaultKey = hrApiHandlerPageMap()[$handlerFile] ?? 'index';

    if ($action === 'get_employee' && ($_GET['context'] ?? '') === 'new-joining') {
        return 'new-joining';
    }

    if ($action === 'update' && ($_POST['context'] ?? $_GET['context'] ?? '') === 'new-joining') {
        return 'new-joining';
    }

    $actionPageMap = [
        'save_job' => 'create-job',
        'fetch_job_detail' => 'job-list',
        'delete_job' => 'job-list',
        'toggle_job_status' => 'job-list',
        'fetch_candidates' => 'job-candidates',
        'fetch_candidate_detail' => 'job-candidates',
        'update_candidate_status' => 'job-candidates',
        'schedule_interview' => 'job-candidates',
        'reschedule_interview' => 'job-candidates',
        'fetch_interviews' => 'interviews',
        'fetch_pending' => 'new-joining',
        'onboard' => 'new-joining',
        'hire_candidate' => 'new-joining',
        'save_leave_types' => 'leave-management',
        'generate_bulk_payroll' => 'payroll',
        'save_payroll' => 'payroll',
        'save_payroll_cycle' => 'payroll-settings',
        'add_review' => 'kpi-management',
        'delete_review' => 'kpi-management',
        'fetch_report_data' => 'kpi-report',
        'process_bulk_attendance' => 'attendance',
        'update_attendance' => 'attendance',
        'restore' => 'employees',
    ];

    return $actionPageMap[$action] ?? $defaultKey;
}

/**
 * @return array{mode:string,types:array<int,string>}
 */
function hrResolveActionPermissionChecks(string $action): array
{
    if (in_array($action, ['Approve', 'Reject', 'Update'], true)) {
        return ['mode' => 'all', 'types' => ['edit']];
    }

    $normalized = strtolower($action);

    if (in_array($normalized, ['fetch', 'check_email', 'get_employee'], true)) {
        return ['mode' => 'all', 'types' => ['view']];
    }

    if ($normalized === 'restore') {
        return ['mode' => 'all', 'types' => ['edit']];
    }

    if (preg_match('/^(fetch_|get_|list_|read_)/', $normalized)) {
        return ['mode' => 'all', 'types' => ['view']];
    }

    if (in_array($normalized, ['add', 'onboard', 'hire_candidate', 'add_review'], true)) {
        return ['mode' => 'all', 'types' => ['create']];
    }

    if (in_array($normalized, ['get_it_staff'], true)) {
        return ['mode' => 'all', 'types' => ['edit']];
    }

    if ($normalized === 'unread_count') {
        return ['mode' => 'all', 'types' => ['view']];
    }

    if (in_array($normalized, ['mark_read', 'mark_all_read'], true)) {
        return ['mode' => 'all', 'types' => ['mark_read']];
    }

    if ($normalized === 'clear') {
        return ['mode' => 'all', 'types' => ['delete']];
    }

    if ($normalized === 'toggle_job_status') {
        return ['mode' => 'all', 'types' => ['toggle_status']];
    }

    if (in_array($normalized, ['schedule_interview', 'reschedule_interview'], true)) {
        return ['mode' => 'all', 'types' => ['schedule_interview']];
    }

    if ($normalized === 'update_candidate_status') {
        return ['mode' => 'all', 'types' => ['update_pipeline']];
    }

    if (in_array($normalized, ['edit', 'update', 'restore'], true)) {
        return ['mode' => 'all', 'types' => ['edit']];
    }

    if ($normalized === 'delete') {
        return ['mode' => 'all', 'types' => ['delete']];
    }

    if ($normalized === 'save') {
        return ['mode' => 'any', 'types' => ['create', 'edit']];
    }

    if (in_array($normalized, ['save_policy', 'save_job'], true)) {
        return ['mode' => 'any', 'types' => ['create', 'edit']];
    }

    if (in_array($normalized, ['save_payroll', 'save_payroll_cycle', 'save_leave_types', 'reschedule_interview'], true)) {
        return ['mode' => 'all', 'types' => ['edit']];
    }

    if (preg_match('/^(save_|create_|add_|insert_|generate_|schedule_)/', $normalized)) {
        return ['mode' => 'all', 'types' => ['create']];
    }

    if (preg_match('/^(update_|edit_|toggle_|approve_|reject_|mark_|assign_|resolve_|close_|reopen_|handover_|claim_|send_|process_)/', $normalized)) {
        return ['mode' => 'all', 'types' => ['edit']];
    }

    if (preg_match('/^(delete_|remove_|ban_)/', $normalized)) {
        return ['mode' => 'all', 'types' => ['delete']];
    }

    if (preg_match('/(export|pdf|print|download)/', $normalized)) {
        return ['mode' => 'all', 'types' => ['export']];
    }

    return ['mode' => 'all', 'types' => ['view']];
}

function hrCanAny(PDO $pdo, string $pageKey, array $types): bool
{
    foreach ($types as $type) {
        if (hrCan($pdo, $pageKey, $type)) {
            return true;
        }
    }
    return false;
}

function hrGuardApiRequest(PDO $pdo, string $action, ?string $handlerFile = null): void
{
    if (!isHrPortalUser()) {
        return;
    }

    if ($action === 'fetch_requirements') {
        if (hrCan($pdo, 'employees', 'view') || hrCan($pdo, 'new-joining', 'view')) {
            return;
        }
        echo json_encode([
            'status' => 'error',
            'success' => false,
            'message' => 'You do not have permission to perform this action.',
        ]);
        exit;
    }

    $pageKey = hrResolveApiPageKey($action, $handlerFile);

    if ($action === 'update' && $pageKey === 'new-joining') {
        if (!hrCan($pdo, 'new-joining', 'create')) {
            echo json_encode([
                'status' => 'error',
                'success' => false,
                'message' => 'You do not have permission to perform this action.',
            ]);
            exit;
        }
        return;
    }

    if ($action === 'add_review') {
        $reviewId = trim((string) ($_POST['review_id'] ?? ''));
        $permType = $reviewId !== '' ? 'edit' : 'create';
        if (!hrCan($pdo, 'kpi-management', $permType)) {
            echo json_encode([
                'status' => 'error',
                'success' => false,
                'message' => 'You do not have permission to perform this action.',
            ]);
            exit;
        }
        return;
    }

    $checks = hrResolveActionPermissionChecks($action);
    $allowed = false;

    if ($checks['mode'] === 'any') {
        $allowed = hrCanAny($pdo, $pageKey, $checks['types']);
    } else {
        $allowed = true;
        foreach ($checks['types'] as $type) {
            if (!hrCan($pdo, $pageKey, $type)) {
                $allowed = false;
                break;
            }
        }
    }

    if (!$allowed) {
        echo json_encode([
            'status' => 'error',
            'success' => false,
            'message' => 'You do not have permission to perform this action.',
        ]);
        exit;
    }
}

function hrEnforceExportPageAccess(PDO $pdo, string $pageKey): void
{
    if (!isHrPortalUser()) {
        return;
    }
    if (!hrCan($pdo, $pageKey, 'view') || !hrCan($pdo, $pageKey, 'export')) {
        $_SESSION['error'] = 'You do not have permission to export or print this page.';
        header('Location: index.php?access_denied=1');
        exit;
    }
}

function hrUpsertPermissionRow(PDO $pdo, string $pageKey, array $perm): void
{
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
    $stmt->execute([
        $pageKey,
        $perm['can_view'],
        $perm['can_create'],
        $perm['can_edit'],
        $perm['can_delete'],
        $perm['can_export'],
    ]);
}

function hrSyncMissingPermissionPages(PDO $pdo): void
{
    try {
        $existing = $pdo->query("SELECT page_key FROM hr_page_permissions")->fetchAll(PDO::FETCH_COLUMN);
    } catch (PDOException $e) {
        return;
    }

    $existingMap = array_flip($existing);
    $defaults = hrAccessDefaultPermissions();

    foreach ($defaults as $pageKey => $perm) {
        if (isset($existingMap[$pageKey])) {
            continue;
        }
        hrUpsertPermissionRow($pdo, $pageKey, $perm);
    }
}

function hrApplyDefaultPermissions(PDO $pdo, bool $bumpRevision = true): void
{
    $defaults = hrAccessDefaultPermissions();
    foreach ($defaults as $pageKey => $perm) {
        hrUpsertPermissionRow($pdo, $pageKey, $perm);
    }
    if ($bumpRevision) {
        hrBumpPermissionsRevision($pdo);
    }
}

function hrCapabilityMigrationVersion(PDO $pdo): int
{
    try {
        $stmt = $pdo->prepare("SELECT meta_value FROM settings WHERE meta_key = 'hr_capability_migration' LIMIT 1");
        $stmt->execute();
        return (int) ($stmt->fetchColumn() ?: 0);
    } catch (PDOException $e) {
        return 0;
    }
}

function hrSetCapabilityMigrationVersion(PDO $pdo, int $version): void
{
    try {
        $exists = $pdo->query("SELECT id FROM settings WHERE meta_key = 'hr_capability_migration' LIMIT 1")->fetchColumn();
        if ($exists) {
            $pdo->prepare("UPDATE settings SET meta_value = ? WHERE meta_key = 'hr_capability_migration'")
                ->execute([(string) $version]);
        } else {
            $pdo->prepare("INSERT INTO settings (meta_key, meta_value) VALUES ('hr_capability_migration', ?)")
                ->execute([(string) $version]);
        }
    } catch (PDOException $e) {
        /* ignore */
    }
}

/** One-time upgrades when new capability types are added to existing permission rows. */
function hrRunCapabilityMigrations(PDO $pdo): void
{
    $version = hrCapabilityMigrationVersion($pdo);
    $changed = false;

    if ($version < 1) {
        $noti = hrGetPagePermission($pdo, 'notifications');
        if ((int) ($noti['can_view'] ?? 0) === 1 && (int) ($noti['can_edit'] ?? 0) === 0) {
            $noti['can_edit'] = 1;
            hrUpsertPermissionRow($pdo, 'notifications', hrNormalizePermissionRow('notifications', $noti));
            $changed = true;
        }
        $version = 1;
    }

    if ($version < 2) {
        $version = 2;
    }

    if ($version < 3) {
        $jobs = hrGetPagePermission($pdo, 'job-list');
        if (
            (int) ($jobs['can_view'] ?? 0) === 1
            && (int) ($jobs['can_create'] ?? 0) === 1
            && (int) ($jobs['can_edit'] ?? 0) === 1
            && (int) ($jobs['can_delete'] ?? 0) === 0
        ) {
            $jobs['can_delete'] = 1;
            hrUpsertPermissionRow($pdo, 'job-list', hrNormalizePermissionRow('job-list', $jobs));
            $changed = true;
        }
        $version = 3;
    }

    if ($version < 4) {
        $jobs = hrGetPagePermission($pdo, 'job-list');
        if (
            (int) ($jobs['can_view'] ?? 0) === 1
            && (int) ($jobs['can_create'] ?? 0) === 1
            && (int) ($jobs['can_edit'] ?? 0) === 1
            && (int) ($jobs['can_delete'] ?? 0) === 0
        ) {
            $jobs['can_delete'] = 1;
            hrUpsertPermissionRow($pdo, 'job-list', hrNormalizePermissionRow('job-list', $jobs));
            $changed = true;
        }
        $version = 4;
    }

    if ($version < 5) {
        $pool = hrGetPagePermission($pdo, 'job-candidates');
        if (
            (int) ($pool['can_view'] ?? 0) === 1
            && (int) ($pool['can_edit'] ?? 0) === 1
            && (int) ($pool['can_create'] ?? 0) === 0
            && (int) ($pool['can_delete'] ?? 0) === 0
        ) {
            $pool['can_create'] = 1;
            $pool['can_delete'] = 1;
            hrUpsertPermissionRow($pdo, 'job-candidates', hrNormalizePermissionRow('job-candidates', $pool));
            $changed = true;
        }
        $version = 5;
    }

    if ($version < 6) {
        $mgmt = hrGetPagePermission($pdo, 'kpi-management');
        $report = hrGetPagePermission($pdo, 'kpi-report');
        if ((int) ($mgmt['can_view'] ?? 0) === 1) {
            foreach (['can_create', 'can_edit', 'can_delete'] as $col) {
                if ((int) ($mgmt[$col] ?? 0) === 0 && (int) ($report[$col] ?? 0) === 1) {
                    $mgmt[$col] = 1;
                    $changed = true;
                }
            }
            if ($changed) {
                hrUpsertPermissionRow($pdo, 'kpi-management', hrNormalizePermissionRow('kpi-management', $mgmt));
            }
        }
        $version = 6;
    }

    if ($version < 7) {
        $it = hrGetPagePermission($pdo, 'it-support');
        if ((int) ($it['can_view'] ?? 0) === 1 && (int) ($it['can_edit'] ?? 0) === 1) {
            $it['can_edit'] = 0;
            hrUpsertPermissionRow($pdo, 'it-support', hrNormalizePermissionRow('it-support', $it));
            $changed = true;
        }
        $version = 7;
    }

    if ($changed) {
        hrBumpPermissionsRevision($pdo);
    }
    hrSetCapabilityMigrationVersion($pdo, $version);
}

function hrGuardCandidateStatusRequest(PDO $pdo, string $status): void
{
    if (!isHrPortalUser()) {
        return;
    }

    $key = strtolower(trim($status));
    $type = in_array($key, ['rejected', 'banned'], true) ? 'reject_ban' : 'update_pipeline';

    if (!hrCan($pdo, 'job-candidates', $type)) {
        echo json_encode([
            'status' => 'error',
            'success' => false,
            'message' => 'You do not have permission to perform this action.',
        ]);
        exit;
    }
}

function hrSeedPermissionsIfEmpty(PDO $pdo): void
{
    try {
        $count = (int) $pdo->query("SELECT COUNT(*) FROM hr_page_permissions")->fetchColumn();
    } catch (PDOException $e) {
        return;
    }

    if ($count === 0) {
        hrApplyDefaultPermissions($pdo, true);
        hrSetCapabilityMigrationVersion($pdo, 5);
        return;
    }

    hrSyncMissingPermissionPages($pdo);
    hrRunCapabilityMigrations($pdo);
}
