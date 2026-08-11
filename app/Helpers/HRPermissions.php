<?php
/**
 * HR Portal Permission Helper
 * Replaces legacy access_control_helper.php for view-level permission logic.
 */

namespace App\Helpers;

use App\Core\Auth;
use App\Core\View;
use PDO;
use PDOException;

class HRPermissions
{
    private const HR_ADMIN_ONLY_PAGES = [];

    public static function accessPageRegistry(): array
    {
        return [
            'index' => ['file' => 'index.php', 'sidebar' => true, 'section' => 'main'],
            'employees' => ['file' => 'employees.php', 'sidebar' => true, 'section' => 'organization'],
            'attendance' => ['file' => 'attendance.php', 'sidebar' => true, 'section' => 'organization'],
            'daily-attendance' => ['file' => 'daily-attendance.php', 'sidebar' => true, 'section' => 'organization'],
            'leave-management' => ['file' => 'leave-management.php', 'sidebar' => true, 'section' => 'organization'],
            'new-joining' => ['file' => 'new-joining.php', 'sidebar' => true, 'section' => 'organization'],
            'hierarchy' => ['file' => 'hierarchy.php', 'sidebar' => true, 'section' => 'organization'],
            'kpi-management' => ['file' => 'kpi-management.php', 'sidebar' => true, 'section' => 'organization'],
            'event-calendar' => ['file' => 'event-calendar.php', 'sidebar' => true, 'section' => 'organization'],
            'job-list' => ['file' => 'job-list.php', 'sidebar' => true, 'section' => 'jobs'],
            'create-job' => ['file' => 'create-job.php', 'sidebar' => true, 'section' => 'jobs'],
            'job-candidates' => ['file' => 'job-candidates.php', 'sidebar' => true, 'section' => 'jobs'],
            'walk-in-candidates' => ['file' => 'walk-in-candidates.php', 'sidebar' => true, 'section' => 'jobs'],
            'interviews' => ['file' => 'interviews.php', 'sidebar' => true, 'section' => 'jobs'],
            'payroll' => ['file' => 'payroll.php', 'sidebar' => true, 'section' => 'administration'],
            'activity-logs' => ['file' => 'activity-logs.php', 'sidebar' => true, 'section' => 'administration'],
            'announcements' => ['file' => 'announcements.php', 'sidebar' => true, 'section' => 'administration'],
            'notifications' => ['file' => 'notifications.php', 'sidebar' => true, 'section' => 'administration'],
            'it-support' => ['file' => 'it-support.php', 'sidebar' => true, 'section' => 'administration'],
            'shifts' => ['file' => 'shifts.php', 'sidebar' => true, 'section' => 'system'],
            'department-management' => ['file' => 'department-management.php', 'sidebar' => true, 'section' => 'system'],
            'policy-management' => ['file' => 'policy-management.php', 'sidebar' => true, 'section' => 'system'],
            'payroll-settings' => ['file' => 'payroll-settings.php', 'sidebar' => true, 'section' => 'system'],
            'hierarchy-settings' => ['file' => 'hierarchy-settings.php', 'sidebar' => true, 'section' => 'system'],
            'employee-profile' => ['file' => 'employee-profile.php', 'sidebar' => false, 'section' => 'detail'],
            'attendance-log' => ['file' => 'attendance-log.php', 'sidebar' => false, 'section' => 'detail'],
            'edit-job' => ['file' => 'edit-job.php', 'sidebar' => false, 'section' => 'detail'],
            'candidate-detail' => ['file' => 'candidate-detail.php', 'sidebar' => false, 'section' => 'detail'],
            'kpi-report' => ['file' => 'kpi-report.php', 'sidebar' => false, 'section' => 'detail'],
            'payslip-print' => ['file' => 'payslip-print.php', 'sidebar' => false, 'section' => 'detail'],
        ];
    }

    public static function pageCapabilities(): array
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
            'walk-in-candidates' => ['view', 'schedule_interview', 'update_pipeline', 'reject_ban', 'delete'],
            'interviews' => [],
            'payroll' => ['view', 'create', 'edit', 'export'],
            'activity-logs' => ['view'],
            'announcements' => ['view', 'create', 'edit', 'delete'],
            'notifications' => ['view', 'mark_read', 'delete'],
            'it-support' => ['view', 'edit'],
            'shifts' => ['view', 'create', 'edit', 'delete'],
            'department-management' => ['view', 'create', 'edit', 'delete'],
            'policy-management' => ['view', 'create', 'edit', 'delete'],
            'payroll-settings' => ['view', 'edit'],
            'hierarchy-settings' => ['view', 'edit'],
            'employee-profile' => [],
            'attendance-log' => [],
            'edit-job' => ['view', 'edit'],
            'candidate-detail' => [],
            'kpi-report' => [],
            'payslip-print' => ['view', 'export'],
        ];
    }

    public static function pageMatrixSections(): array
    {
        return [
            'Main Menu' => ['index'],
            'Organization' => ['employees', 'attendance', 'leave-management', 'new-joining', 'hierarchy', 'kpi-management', 'event-calendar'],
            'Job Management' => ['job-list', 'job-candidates', 'walk-in-candidates'],
            'Administration' => ['payroll', 'activity-logs', 'announcements', 'notifications', 'it-support'],
            'System' => ['shifts', 'department-management', 'policy-management', 'payroll-settings', 'hierarchy-settings'],
            'Detail & Linked Pages' => ['employee-profile', 'attendance-log', 'create-job', 'edit-job', 'candidate-detail', 'interviews', 'kpi-report', 'payslip-print'],
        ];
    }

    public static function pageMatrixLabels(): array
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
            'walk-in-candidates' => ['label' => 'Walk-In Candidates', 'icon' => 'user-check'],
            'interviews' => ['label' => 'Interviews', 'icon' => 'calendar'],
            'payroll' => ['label' => 'Payroll', 'icon' => 'banknote'],
            'activity-logs' => ['label' => 'Activity Logs', 'icon' => 'history'],
            'announcements' => ['label' => 'Announcements', 'icon' => 'megaphone'],
            'notifications' => ['label' => 'Notifications', 'icon' => 'bell'],
            'it-support' => ['label' => 'IT Helpdesk', 'icon' => 'headset'],
            'shifts' => ['label' => 'Add Shift', 'icon' => 'plus-circle'],
            'department-management' => ['label' => 'Dept Management', 'icon' => 'building-2'],
            'policy-management' => ['label' => 'Policy Management', 'icon' => 'file-text'],
            'payroll-settings' => ['label' => 'Payroll Cycle', 'icon' => 'calculator'],
            'hierarchy-settings' => ['label' => 'Hierarchy Settings', 'icon' => 'network'],
            'employee-profile' => ['label' => 'Employee Profile', 'icon' => 'user'],
            'attendance-log' => ['label' => 'Attendance History', 'icon' => 'clipboard-list'],
            'edit-job' => ['label' => 'Edit Job', 'icon' => 'pencil'],
            'candidate-detail' => ['label' => 'Candidate Detail', 'icon' => 'user-search'],
            'kpi-report' => ['label' => 'KPI Scorecard', 'icon' => 'bar-chart-2'],
            'payslip-print' => ['label' => 'Payslip Print', 'icon' => 'file-output'],
        ];
    }

    public static function pagePermissionParent(): array
    {
        return [
            'attendance-log' => 'attendance',
            'employee-profile' => 'employees',
            'create-job' => 'job-list',
            'edit-job' => 'job-list',
            'candidate-detail' => 'job-candidates',
            'walk-in-candidates' => 'job-candidates',
            'interviews' => 'job-candidates',
            'kpi-report' => 'kpi-management',
            'payslip-print' => 'payroll',
        ];
    }

    public static function pageInheritsView(string $pageKey): bool
    {
        return isset(self::pagePermissionParent()[$pageKey]);
    }

    public static function resolvePermissionPageKey(string $pageKey, string $type = 'view'): string
    {
        $parent = self::pagePermissionParent()[$pageKey] ?? null;
        if (!$parent) {
            return $pageKey;
        }
        if ($type === 'view') {
            return $parent;
        }
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
        if (!self::pageSupportsCapability($pageKey, $type)) {
            return $parent;
        }
        return $pageKey;
    }

    public static function pageSupportsCapability(string $pageKey, string $type): bool
    {
        if (in_array($pageKey, self::HR_ADMIN_ONLY_PAGES, true)) {
            return false;
        }
        if ($type === 'view') {
            return true;
        }
        $caps = self::pageCapabilities();
        return in_array($type, $caps[$pageKey] ?? [], true);
    }

    public static function capabilityDbColumn(string $type): ?string
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

    public static function matrixColumnCapability(string $pageKey, string $column): ?string
    {
        if ($column === 'create' && self::pageSupportsCapability($pageKey, 'schedule_interview') && !self::pageSupportsCapability($pageKey, 'create')) {
            return 'schedule_interview';
        }
        if ($column === 'edit' && self::pageSupportsCapability($pageKey, 'mark_read') && !self::pageSupportsCapability($pageKey, 'edit')) {
            return 'mark_read';
        }
        if ($column === 'edit' && self::pageSupportsCapability($pageKey, 'update_pipeline') && !self::pageSupportsCapability($pageKey, 'edit')) {
            return 'update_pipeline';
        }
        if ($column === 'delete' && self::pageSupportsCapability($pageKey, 'toggle_status') && !self::pageSupportsCapability($pageKey, 'delete')) {
            return 'toggle_status';
        }
        if ($column === 'delete' && self::pageSupportsCapability($pageKey, 'reject_ban') && !self::pageSupportsCapability($pageKey, 'delete')) {
            return 'reject_ban';
        }
        if (!in_array($column, ['create', 'edit', 'delete', 'export'], true)) {
            return null;
        }
        return self::pageSupportsCapability($pageKey, $column) ? $column : null;
    }

    public static function matrixCapabilityLabel(string $cap): string
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

    public static function normalizePermissionRow(string $pageKey, array $perm): array
    {
        if (in_array($pageKey, self::HR_ADMIN_ONLY_PAGES, true)) {
            return [
                'can_view' => 0,
                'can_create' => 0,
                'can_edit' => 0,
                'can_delete' => 0,
                'can_export' => 0,
            ];
        }

        if (self::pageInheritsView($pageKey)) {
            $perm['can_view'] = 0;
        }

        $supportsCreateCol = self::pageSupportsCapability($pageKey, 'create') || self::pageSupportsCapability($pageKey, 'schedule_interview');
        $supportsEditCol = self::pageSupportsCapability($pageKey, 'edit')
            || self::pageSupportsCapability($pageKey, 'mark_read')
            || self::pageSupportsCapability($pageKey, 'update_pipeline');
        $supportsDeleteCol = self::pageSupportsCapability($pageKey, 'delete')
            || self::pageSupportsCapability($pageKey, 'toggle_status')
            || self::pageSupportsCapability($pageKey, 'reject_ban');

        return [
            'can_view' => !empty($perm['can_view']) ? 1 : 0,
            'can_create' => $supportsCreateCol && !empty($perm['can_create']) ? 1 : 0,
            'can_edit' => $supportsEditCol && !empty($perm['can_edit']) ? 1 : 0,
            'can_delete' => $supportsDeleteCol && !empty($perm['can_delete']) ? 1 : 0,
            'can_export' => self::pageSupportsCapability($pageKey, 'export') && !empty($perm['can_export']) ? 1 : 0,
        ];
    }

    public static function accessDefaultPermissions(): array
    {
        $defaults = [];
        foreach (self::accessPageRegistry() as $pageKey => $_meta) {
            if (in_array($pageKey, self::HR_ADMIN_ONLY_PAGES, true)) {
                $defaults[$pageKey] = [
                    'can_view' => 0, 'can_create' => 0, 'can_edit' => 0, 'can_delete' => 0, 'can_export' => 0,
                ];
                continue;
            }
            $supportsCreateCol = self::pageSupportsCapability($pageKey, 'create') || self::pageSupportsCapability($pageKey, 'schedule_interview');
            $supportsEditCol = self::pageSupportsCapability($pageKey, 'edit')
                || self::pageSupportsCapability($pageKey, 'mark_read')
                || self::pageSupportsCapability($pageKey, 'update_pipeline');
            $supportsDeleteCol = self::pageSupportsCapability($pageKey, 'delete')
                || self::pageSupportsCapability($pageKey, 'toggle_status')
                || self::pageSupportsCapability($pageKey, 'reject_ban');

            $defaults[$pageKey] = [
                'can_view' => 1,
                'can_create' => $supportsCreateCol ? 1 : 0,
                'can_edit' => $supportsEditCol ? 1 : 0,
                'can_delete' => $supportsDeleteCol ? 1 : 0,
                'can_export' => self::pageSupportsCapability($pageKey, 'export') ? 1 : 0,
            ];

            if ($pageKey === 'it-support') {
                $defaults[$pageKey]['can_edit'] = 0;
            }
        }
        return $defaults;
    }

    public static function isHrPortalUser(): bool
    {
        return Auth::isLoggedIn() && Auth::isHR();
    }

    public static function resolvePageKey(?string $uri = null): ?string
    {
        $path = self::normalizeRequestPath($uri);
        $path = rtrim($path, '/') ?: '/';
        
        $map = [
            '/' => 'login',
            '/logout' => 'logout',
            '/job-apply' => 'job-apply',
            '/walk-in' => 'walk-in',
            '/joining-form' => 'joining-form',
            '/dashboard' => 'index',
            '/employees' => 'employees',
            '/employees/profile' => 'employee-profile',
            '/attendance' => 'attendance',
            '/attendance/log' => 'attendance-log',
            '/leave' => 'leave-management',
            '/new-joining' => 'new-joining',
            '/hierarchy' => 'hierarchy',
            '/hierarchy/settings' => 'hierarchy-settings',
            '/kpi' => 'kpi-management',
            '/kpi/report' => 'kpi-report',
            '/events' => 'event-calendar',
            '/jobs' => 'job-list',
            '/jobs/create' => 'create-job',
            '/jobs/edit' => 'edit-job',
            '/recruitment' => 'job-candidates',
            '/recruitment/walk-in' => 'walk-in-candidates',
            '/recruitment/detail' => 'candidate-detail',
            '/recruitment/interviews' => 'interviews',
            '/payroll' => 'payroll',
            '/payroll/settings' => 'payroll-settings',
            '/payslip/print' => 'payslip-print',
            '/activity-logs' => 'activity-logs',
            '/announcements' => 'announcements',
            '/notifications' => 'notifications',
            '/it-support' => 'it-support',
            '/shifts' => 'shifts',
            '/departments' => 'department-management',
            '/roles' => 'role-management',
            '/policy-management' => 'policy-management',
            '/policies' => 'policies',
            '/policies/detail' => 'policy-detail',
            '/profile' => 'profile',
            '/daily-attendance' => 'daily-attendance',
            '/sheets' => 'sheets',
            '/sheets/editor' => 'sheets',
            '/connected-apps' => 'connected-apps',
        ];

        if (isset($map[$path])) {
            return $map[$path];
        }

        $uri = $uri ?? ($_SERVER['REQUEST_URI'] ?? '');
        $legacyPath = parse_url($uri, PHP_URL_PATH) ?: '';
        $legacyPath = rtrim(str_replace('\\', '/', $legacyPath), '/');
        $segments = array_values(array_filter(explode('/', $legacyPath)));

        $roleIdx = null;
        foreach ($segments as $i => $seg) {
            if (in_array($seg, ['admin', 'hr', 'user'], true)) {
                $roleIdx = $i;
                break;
            }
        }

        if ($roleIdx === null) {
            return empty($segments) ? 'index' : null;
        }

        $routeParts = array_slice($segments, $roleIdx + 1);
        if ($routeParts === []) {
            return 'index';
        }

        $compoundMap = [
            'payroll/settings' => 'payroll-settings',
            'hierarchy/settings' => 'hierarchy-settings',
            'job/create' => 'create-job',
            'job/edit' => 'edit-job',
            'kpi/report' => 'kpi-report',
            'employee/profile' => 'employee-profile',
            'recruitment/candidates' => 'job-candidates',
            'recruitment/walk-in' => 'walk-in-candidates',
            'recruitment/detail' => 'candidate-detail',
            'recruitment/interviews' => 'interviews',
            'payroll/payslip' => 'payslip-print',
            'payslip/print' => 'payslip-print',
            'policy/detail' => 'policy-detail',
            'attendance/log' => 'attendance-log',
        ];

        if (count($routeParts) >= 2) {
            $compound = $routeParts[0] . '/' . $routeParts[1];
            if (isset($compoundMap[$compound])) {
                return $compoundMap[$compound];
            }
        }

        $segment = preg_replace('/\.php$/i', '', end($routeParts));
        $routeMap = [
            'index' => 'index',
            'dashboard' => 'index',
            'employee' => 'employees',
            'employees' => 'employees',
            'employee-profile' => 'employee-profile',
            'attendance' => 'attendance',
            'attendance-log' => 'attendance-log',
            'leave' => 'leave-management',
            'leave-management' => 'leave-management',
            'new-joining' => 'new-joining',
            'hierarchy' => 'hierarchy',
            'hierarchy-settings' => 'hierarchy-settings',
            'kpi' => 'kpi-management',
            'kpi-management' => 'kpi-management',
            'kpi-report' => 'kpi-report',
            'event' => 'event-calendar',
            'events' => 'event-calendar',
            'event-calendar' => 'event-calendar',
            'job' => 'job-list',
            'jobs' => 'job-list',
            'job-list' => 'job-list',
            'create-job' => 'create-job',
            'edit-job' => 'edit-job',
            'recruitment' => 'job-candidates',
            'job-candidates' => 'job-candidates',
            'walk-in-candidates' => 'walk-in-candidates',
            'candidate-detail' => 'candidate-detail',
            'interviews' => 'interviews',
            'payroll' => 'payroll',
            'payroll-settings' => 'payroll-settings',
            'payslip-print' => 'payslip-print',
            'activity-log' => 'activity-logs',
            'activity-logs' => 'activity-logs',
            'announcement' => 'announcements',
            'announcements' => 'announcements',
            'notification' => 'notifications',
            'notifications' => 'notifications',
            'it-support' => 'it-support',
            'shift' => 'shifts',
            'shifts' => 'shifts',
            'department' => 'department-management',
            'departments' => 'department-management',
            'department-management' => 'department-management',
            'role' => 'role-management',
            'roles' => 'role-management',
            'policy' => 'policy-management',
            'policy-management' => 'policy-management',
            'profile' => 'profile',
            'daily-attendance' => 'daily-attendance',
            'policies' => 'policies',
            'policy-detail' => 'policy-detail',
        ];

        return $routeMap[$segment] ?? null;
    }

    private static function normalizeRequestPath(?string $uri = null): string
    {
        $uri = $uri ?? ($_SERVER['REQUEST_URI'] ?? '');
        $path = parse_url($uri, PHP_URL_PATH) ?: '';
        $path = str_replace('\\', '/', $path);

        $base = Auth::getBasePath();
        if ($base !== '' && $base !== '/' && str_starts_with($path, $base)) {
            $path = substr($path, strlen($base));
        }

        return rtrim($path, '/') ?: '/';
    }

    public static function resolveLegacySidebarPage(?string $uri = null): string
    {
        $key = self::resolvePageKey($uri) ?? 'index';

        foreach (self::accessPageRegistry() as $pageKey => $meta) {
            if ($pageKey === $key) {
                return $meta['file'];
            }
        }

        $extra = [
            'profile' => 'profile.php',
            'daily-attendance' => 'daily-attendance.php',
            'policies' => 'policies.php',
            'policy-detail' => 'policy-detail.php',
            'role-management' => 'role-management.php',
        ];

        return $extra[$key] ?? 'index.php';
    }

    public static function permissionsRevision(PDO $pdo): int
    {
        $stmt = $pdo->prepare("SELECT meta_value FROM settings WHERE meta_key = 'hr_permissions_revision' LIMIT 1");
        $stmt->execute();
        return (int) ($stmt->fetchColumn() ?: 1);
    }

    public static function fetchAllPermissions(PDO $pdo): array
    {
        $defaults = self::accessDefaultPermissions();
        $registry = self::accessPageRegistry();
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
            if (in_array($pageKey, self::HR_ADMIN_ONLY_PAGES, true)) {
                $perm = ['can_view' => 0, 'can_create' => 0, 'can_edit' => 0, 'can_delete' => 0, 'can_export' => 0];
            } else {
                $perm = self::normalizePermissionRow($pageKey, $perm);
            }
            $merged[$pageKey] = array_merge($perm, ['page_key' => $pageKey, 'file' => $meta['file']]);
        }

        return $merged;
    }

    public static function getPagePermission(PDO $pdo, string $pageKey): array
    {
        $all = self::fetchAllPermissions($pdo);
        return $all[$pageKey] ?? [
            'can_view' => 0, 'can_create' => 0, 'can_edit' => 0, 'can_delete' => 0, 'can_export' => 0,
        ];
    }

    public static function canAccessInterviewsModule(PDO $pdo): bool
    {
        return self::can($pdo, 'job-candidates', 'view') && self::can($pdo, 'job-candidates', 'schedule_interview');
    }

    public static function canViewPortalPage(PDO $pdo, string $pageKey): bool
    {
        if (!self::isHrPortalUser()) {
            return true;
        }
        if (in_array($pageKey, self::HR_ADMIN_ONLY_PAGES, true)) {
            return false;
        }
        if ($pageKey === 'interviews') {
            return self::canAccessInterviewsModule($pdo);
        }
        if (!self::can($pdo, $pageKey, 'view')) {
            return false;
        }
        if ($pageKey === 'create-job' && !self::can($pdo, 'job-list', 'create')) {
            return false;
        }
        return true;
    }

    public static function findFirstAllowedPage(PDO $pdo, ?string $excludePageKey = null): ?string
    {
        $registry = self::accessPageRegistry();
        foreach (self::portalLandingPageOrder() as $pageKey) {
            if ($pageKey === $excludePageKey || !isset($registry[$pageKey])) {
                continue;
            }
            if (self::canViewPortalPage($pdo, $pageKey)) {
                return $pageKey;
            }
        }
        return null;
    }

    public static function hasAnyPortalAccess(PDO $pdo): bool
    {
        return self::findFirstAllowedPage($pdo) !== null;
    }

    public static function portalLandingPageOrder(): array
    {
        return [
            'index',
            'employees', 'attendance', 'leave-management', 'new-joining', 'hierarchy',
            'kpi-management', 'event-calendar',
            'job-list', 'create-job', 'job-candidates', 'walk-in-candidates', 'interviews',
            'payroll', 'activity-logs', 'announcements', 'notifications', 'it-support',
            'shifts', 'department-management', 'policy-management', 'payroll-settings', 'hierarchy-settings',
        ];
    }

    public static function resolveDeniedRedirectFile(PDO $pdo, ?string $currentPageKey): ?string
    {
        $slug = self::resolveDeniedRedirectSlug($pdo, $currentPageKey);
        return $slug !== null ? View::url($slug) : null;
    }

    public static function slugForPageKey(string $pageKey): string
    {
        $map = [
            'index' => 'dashboard',
            'employees' => 'employees',
            'attendance' => 'attendance',
            'leave-management' => 'leave',
            'new-joining' => 'new-joining',
            'hierarchy' => 'hierarchy',
            'kpi-management' => 'kpi',
            'event-calendar' => 'events',
            'job-list' => 'jobs',
            'job-candidates' => 'recruitment',
            'walk-in-candidates' => 'recruitment/walk-in',
            'payroll' => 'payroll',
            'activity-logs' => 'activity-logs',
            'announcements' => 'announcements',
            'notifications' => 'notifications',
            'it-support' => 'it-support',
            'shifts' => 'shifts',
            'department-management' => 'departments',
            'policy-management' => 'policy-management',
            'payroll-settings' => 'payroll/settings',
            'hierarchy-settings' => 'hierarchy/settings',
            'employee-profile' => 'employees/profile',
            'attendance-log' => 'attendance/log',
            'create-job' => 'jobs/create',
            'edit-job' => 'jobs/edit',
            'candidate-detail' => 'recruitment/detail',
            'interviews' => 'recruitment/interviews',
            'kpi-report' => 'kpi/report',
            'payslip-print' => 'payslip/print',
        ];
        return $map[$pageKey] ?? 'dashboard';
    }

    public static function resolveDeniedRedirectSlug(PDO $pdo, ?string $currentPageKey): ?string
    {
        if ($currentPageKey !== 'index' && self::canViewPortalPage($pdo, 'index')) {
            return 'dashboard';
        }

        $fallbackKey = self::findFirstAllowedPage($pdo, $currentPageKey);
        if ($fallbackKey === null) {
            return null;
        }

        return self::slugForPageKey($fallbackKey);
    }

    public static function handleDeniedPageAccess(PDO $pdo, ?string $currentPageKey): void
    {
        $target = self::resolveDeniedRedirectFile($pdo, $currentPageKey);
        if ($target !== null) {
            header('Location: ' . $target);
            exit;
        }

        if ($currentPageKey !== 'index') {
            header('Location: ' . View::url('dashboard'));
            exit;
        }

        $GLOBALS['hr_access_denied'] = true;
    }

    public static function can(PDO $pdo, string $pageKey, string $type = 'view'): bool
    {
        if (!self::isHrPortalUser()) {
            return true;
        }
        if (in_array($pageKey, self::HR_ADMIN_ONLY_PAGES, true)) {
            return false;
        }

        if ($pageKey === 'interviews' && in_array($type, ['create', 'edit'], true)) {
            $type = 'schedule_interview';
        }

        $effectiveKey = self::resolvePermissionPageKey($pageKey, $type);

        if ($effectiveKey === $pageKey && !self::pageSupportsCapability($pageKey, $type)) {
            return false;
        }
        if (!self::pageSupportsCapability($effectiveKey, $type)) {
            return false;
        }

        $perm = self::getPagePermission($pdo, $effectiveKey);
        $col = self::capabilityDbColumn($type) ?? 'can_view';
        return !empty($perm[$col]);
    }

    public static function enforcePageAccess(PDO $pdo, ?string $pageKey): void
    {
        if (!self::isHrPortalUser() || $pageKey === null) {
            return;
        }
        if (in_array($pageKey, self::HR_ADMIN_ONLY_PAGES, true)) {
            $_SESSION['error'] = 'Access denied: You do not have permission to view this page.';
            self::handleDeniedPageAccess($pdo, $pageKey);
            return;
        }

        $canView = self::can($pdo, $pageKey, 'view');
        if ($pageKey === 'interviews' && !self::canAccessInterviewsModule($pdo)) {
            $canView = false;
        }

        if (!$canView) {
            $_SESSION['error'] = 'Access denied: You do not have permission to view this page.';
            self::handleDeniedPageAccess($pdo, $pageKey);
            return;
        }

        if ($pageKey === 'create-job' && !self::can($pdo, 'job-list', 'create')) {
            $_SESSION['error'] = 'Access denied: You do not have permission to create jobs.';
            self::handleDeniedPageAccess($pdo, $pageKey);
            return;
        }
    }

    public static function canViewSidebarPage(PDO $pdo, string $pageKey): bool
    {
        return self::canViewPortalPage($pdo, $pageKey);
    }

    public static function guardApiRequest(PDO $pdo, string $action, ?string $handlerFile = null): void
    {
        if (!self::isHrPortalUser()) {
            return;
        }

        if ($action === 'fetch_requirements') {
            if (self::can($pdo, 'employees', 'view') || self::can($pdo, 'new-joining', 'view') || self::can($pdo, 'kpi-management', 'view')) {
                return;
            }
            echo json_encode([
                'status' => 'error',
                'success' => false,
                'message' => 'You do not have permission to perform this action.',
            ]);
            exit;
        }

        $pageKey = self::resolveApiPageKey($action, $handlerFile);

        if ($action === 'update' && $pageKey === 'new-joining') {
            if (!self::can($pdo, 'new-joining', 'create')) {
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
            if (!self::can($pdo, 'kpi-management', $permType)) {
                echo json_encode([
                    'status' => 'error',
                    'success' => false,
                    'message' => 'You do not have permission to perform this action.',
                ]);
                exit;
            }
            return;
        }

        $checks = self::resolveActionPermissionChecks($action);
        $allowed = false;

        if ($checks['mode'] === 'any') {
            $allowed = self::canAny($pdo, $pageKey, $checks['types']);
        } else {
            $allowed = true;
            foreach ($checks['types'] as $type) {
                if (!self::can($pdo, $pageKey, $type)) {
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

    public static function enforceExportPageAccess(PDO $pdo, string $pageKey): void
    {
        if (!self::isHrPortalUser()) {
            return;
        }
        if (!self::can($pdo, $pageKey, 'view') || !self::can($pdo, $pageKey, 'export')) {
            $_SESSION['error'] = 'You do not have permission to export or print this page.';
            header('Location: ' . View::url('dashboard') . '?access_denied=1');
            exit;
        }
    }

    public static function upsertPermissionRow(PDO $pdo, string $pageKey, array $perm): void
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

    public static function syncMissingPermissionPages(PDO $pdo): void
    {
        try {
            $existing = $pdo->query("SELECT page_key FROM hr_page_permissions")->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            return;
        }

        $existingMap = array_flip($existing);
        $defaults = self::accessDefaultPermissions();

        foreach ($defaults as $pageKey => $perm) {
            if (isset($existingMap[$pageKey])) {
                continue;
            }
            self::upsertPermissionRow($pdo, $pageKey, $perm);
        }
    }

    public static function applyDefaultPermissions(PDO $pdo, bool $bumpRevision = true): void
    {
        $defaults = self::accessDefaultPermissions();
        foreach ($defaults as $pageKey => $perm) {
            self::upsertPermissionRow($pdo, $pageKey, $perm);
        }
        if ($bumpRevision) {
            self::bumpPermissionsRevision($pdo);
        }
    }

    public static function capabilityMigrationVersion(PDO $pdo): int
    {
        try {
            $stmt = $pdo->prepare("SELECT meta_value FROM settings WHERE meta_key = 'hr_capability_migration' LIMIT 1");
            $stmt->execute();
            return (int) ($stmt->fetchColumn() ?: 0);
        } catch (PDOException $e) {
            return 0;
        }
    }

    public static function setCapabilityMigrationVersion(PDO $pdo, int $version): void
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
            // ignore
        }
    }

    public static function runCapabilityMigrations(PDO $pdo): void
    {
        $version = self::capabilityMigrationVersion($pdo);
        $changed = false;

        if ($version < 1) {
            $noti = self::getPagePermission($pdo, 'notifications');
            if ((int) ($noti['can_view'] ?? 0) === 1 && (int) ($noti['can_edit'] ?? 0) === 0) {
                $noti['can_edit'] = 1;
                self::upsertPermissionRow($pdo, 'notifications', self::normalizePermissionRow('notifications', $noti));
                $changed = true;
            }
            $version = 1;
        }

        if ($changed) {
            self::setCapabilityMigrationVersion($pdo, $version);
        }
    }

    public static function bumpPermissionsRevision(PDO $pdo): void
    {
        $stmt = $pdo->prepare("SELECT id FROM settings WHERE meta_key = 'hr_permissions_revision' LIMIT 1");
        $stmt->execute();
        if ($stmt->fetchColumn()) {
            $pdo->exec("UPDATE settings SET meta_value = meta_value + 1 WHERE meta_key = 'hr_permissions_revision'");
        } else {
            $pdo->prepare("INSERT INTO settings (meta_key, meta_value) VALUES ('hr_permissions_revision', '2')")->execute();
        }
    }

    public static function seedPermissionsIfEmpty(PDO $pdo): void
    {
        try {
            $count = (int) $pdo->query("SELECT COUNT(*) FROM hr_page_permissions")->fetchColumn();
        } catch (PDOException $e) {
            return;
        }

        if ($count === 0) {
            self::applyDefaultPermissions($pdo, true);
            self::setCapabilityMigrationVersion($pdo, 5);
            return;
        }

        self::syncMissingPermissionPages($pdo);
        self::runCapabilityMigrations($pdo);
    }

    public static function resolveActionPermissionChecks(string $action): array
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

    public static function canAny(PDO $pdo, string $pageKey, array $types): bool
    {
        foreach ($types as $type) {
            if (self::can($pdo, $pageKey, $type)) {
                return true;
            }
        }
        return false;
    }

    public static function resolveApiPageKey(string $action, ?string $handlerFile = null): string
    {
        $handlerFile = $handlerFile ?? basename($_SERVER['SCRIPT_NAME'] ?? '');
        $handlerPageMap = [
            'shift_handler.php' => 'shifts',
            'department_handler.php' => 'department-management',
            'policy_handler.php' => 'policy-management',
            'settings_handler.php' => 'payroll-settings',
            'hierarchy_settings_handler.php' => 'hierarchy-settings',
            'leave_type_handler.php' => 'leave-management',
            'leave_status_handler.php' => 'leave-management',
            'leave_handler.php' => 'leave-management',
            'employee_handler.php' => 'employees',
            'announcement_handler.php' => 'announcements',
            'attendance_handler.php' => 'attendance',
            'calendar_handler.php' => 'event-calendar',
            'it-support-handler.php' => 'it-support',
            'notification_handler.php' => 'notifications',
            'payroll_handler.php' => 'payroll',
            'activity_handler.php' => 'activity-logs',
            'job_handler.php' => 'job-list',
            'kpi_handler.php' => 'kpi-management',
            'profile_handler.php' => 'profile',
        ];
        $defaultKey = $handlerPageMap[$handlerFile] ?? (self::accessPageRegistry()[$handlerFile] ?? 'index');

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
            'fetch_walk_in_candidates' => 'walk-in-candidates',
            'submit_walk_in' => 'walk-in-candidates',
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
            'fetch_summary' => 'kpi-management',
            'fetch_list' => 'kpi-management',
            'fetch_employees' => 'kpi-management',
            'fetch_latest_goals' => 'kpi-management',
            'add_review' => 'kpi-management',
            'delete_review' => 'kpi-management',
            'fetch_report_data' => 'kpi-report',
            'process_bulk_attendance' => 'attendance',
            'update_attendance' => 'attendance',
            'restore' => 'employees',
        ];

        return $actionPageMap[$action] ?? $defaultKey;
    }

    public static function permissionsJsConfig(): array
    {
        $registry = self::accessPageRegistry();
        $hrefMap = [];
        $fileMap = [];
        $sidebarKeys = [];

        foreach ($registry as $key => $meta) {
            $slug = self::slugForPageKey($key);
            $cleanUrl = View::url($slug);
            $fileMap[$key] = $cleanUrl;
            $hrefMap[$meta['file']] = $key;
            $hrefMap[$cleanUrl] = $key;
            if (!empty($meta['sidebar'])) {
                $sidebarKeys[] = $key;
            }
        }

        return [
            'page_href_map' => $hrefMap,
            'page_files' => $fileMap,
            'page_parents' => self::pagePermissionParent(),
            'landing_order' => self::portalLandingPageOrder(),
            'sidebar_page_keys' => $sidebarKeys,
        ];
    }
}
