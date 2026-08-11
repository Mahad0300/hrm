<?php
/**
 * Hierarchy Helper
 * From: includes/hierarchy_helper.php
 */

namespace App\Helpers;

use PDO;
use InvalidArgumentException;

class HierarchyHelper
{
    /**
     * Get setting from database
     */
    public static function getSetting(PDO $pdo, string $key, string $default = ''): string
    {
        try {
            $stmt = $pdo->prepare('SELECT meta_value FROM settings WHERE meta_key = ? LIMIT 1');
            $stmt->execute([$key]);
            $val = $stmt->fetchColumn();
            return ($val === false || $val === null) ? $default : (string)$val;
        } catch (\Exception $e) {
            return $default;
        }
    }

    /**
     * Set setting in database
     */
    public static function setSetting(PDO $pdo, string $key, string $value): void
    {
        $stmt = $pdo->prepare(
            'INSERT INTO settings (meta_key, meta_value) VALUES (?, ?)
             ON DUPLICATE KEY UPDATE meta_value = VALUES(meta_value)'
        );
        $stmt->execute([$key, $value]);
    }

    /**
     * Get all hierarchy settings
     */
    public static function getSettings(PDO $pdo): array
    {
        return [
            'ceo_mode' => self::getSetting($pdo, 'org_ceo_mode', 'manual'),
            'ceo_employee_id' => self::getSetting($pdo, 'org_ceo_employee_id', ''),
            'ceo_manual_name' => self::getSetting($pdo, 'org_ceo_manual_name', ''),
            'ceo_manual_title' => self::getSetting($pdo, 'org_ceo_manual_title', 'CEO'),
            'cto_employee_id' => self::getSetting($pdo, 'org_cto_employee_id', ''),
            'management_dept_id' => self::getSetting($pdo, 'org_management_dept_id', ''),
        ];
    }

    /**
     * Save hierarchy settings
     */
    public static function saveSettings(PDO $pdo, array $data): void
    {
        self::setSetting($pdo, 'org_ceo_mode', ($data['ceo_mode'] ?? 'manual') === 'employee' ? 'employee' : 'manual');
        self::setSetting($pdo, 'org_ceo_employee_id', trim((string)($data['ceo_employee_id'] ?? '')));
        self::setSetting($pdo, 'org_ceo_manual_name', trim((string)($data['ceo_manual_name'] ?? '')));
        self::setSetting($pdo, 'org_ceo_manual_title', trim((string)($data['ceo_manual_title'] ?? 'CEO')) ?: 'CEO');
        self::setSetting($pdo, 'org_cto_employee_id', trim((string)($data['cto_employee_id'] ?? '')));
        self::setSetting($pdo, 'org_management_dept_id', trim((string)($data['management_dept_id'] ?? '')));
    }

    /**
     * Validate executive assignments
     */
    public static function validateExecutiveAssignments(array $payload): void
    {
        $ceoMode = ($payload['ceo_mode'] ?? 'manual') === 'employee' ? 'employee' : 'manual';
        if ($ceoMode !== 'employee') {
            return;
        }

        $ceoId = (int)($payload['ceo_employee_id'] ?? 0);
        $ctoId = (int)($payload['cto_employee_id'] ?? 0);

        if ($ceoId > 0 && $ctoId > 0 && $ceoId === $ctoId) {
            throw new InvalidArgumentException('CEO and CIO cannot be the same person.');
        }
    }

    /**
     * Get employee name
     */
    public static function employeeName(array $row): string
    {
        return trim(implode(' ', array_filter([
            trim($row['first_name'] ?? ''),
            trim($row['middle_name'] ?? ''),
            trim($row['last_name'] ?? ''),
        ])));
    }


    private static function isChartEligibleRole(?string $role): bool
    {
        return in_array($role ?? 'Employee', ['Employee', 'Admin', 'HR'], true);
    }
    private static function fetchEmployee(PDO $pdo, int $id): ?array
    {
        $stmt = $pdo->prepare(
            "SELECT e.id, e.first_name, e.middle_name, e.last_name, e.job_title, e.role, e.profile_pic, d.name AS department_name
             FROM employees e
             LEFT JOIN departments d ON e.department_id = d.id AND d.deleted_at IS NULL
             WHERE e.id = ? AND e.deleted_at IS NULL AND e.status = 'Active'
             LIMIT 1"
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }
    public static function fetchActiveEmployees(PDO $pdo): array
    {
        return $pdo->query(
            "SELECT e.id, e.department_id, e.first_name, e.middle_name, e.last_name, e.job_title, e.role, d.name AS department_name
             FROM employees e
             LEFT JOIN departments d ON e.department_id = d.id AND d.deleted_at IS NULL
             WHERE e.deleted_at IS NULL AND e.status = 'Active' AND e.role IN ('Employee', 'Admin', 'HR')
             ORDER BY e.first_name ASC, e.last_name ASC"
        )->fetchAll(PDO::FETCH_ASSOC);
    }
    public static function fetchDepartments(PDO $pdo): array
{
    return $pdo->query(
        "SELECT d.id, d.name, d.manager, d.head,
                CONCAT_WS(' ', em.first_name, NULLIF(em.middle_name, ''), em.last_name) AS manager_name,
                CONCAT_WS(' ', eh.first_name, NULLIF(eh.middle_name, ''), eh.last_name) AS head_name
         FROM departments d
         LEFT JOIN employees em ON d.manager = em.id
         LEFT JOIN employees eh ON d.head = eh.id
         WHERE d.deleted_at IS NULL
         ORDER BY d.name ASC"
    )->fetchAll(PDO::FETCH_ASSOC);
}
    public static function saveManagerAssignments(PDO $pdo, array $managers): void
{
    $pdo->exec('UPDATE departments SET manager = NULL WHERE deleted_at IS NULL');

    $stmt = $pdo->prepare('UPDATE departments SET manager = ? WHERE id = ? AND deleted_at IS NULL');

    foreach ($managers as $row) {
        $empId = (int) ($row['employee_id'] ?? 0);
        if ($empId <= 0) {
            continue;
        }
        $boss = self::fetchEmployee($pdo, $empId);
        if (!$boss) {
            continue;
        }
        foreach ($row['department_ids'] ?? [] as $deptId) {
            $deptId = (int) $deptId;
            if ($deptId > 0) {
                $stmt->execute([$empId, $deptId]);
            }
        }
    }
}
    public static function getManagerAssignments(PDO $pdo): array
{
    $depts = self::fetchDepartments($pdo);
    $grouped = [];
    foreach ($depts as $dept) {
        if (empty($dept['manager'])) {
            continue;
        }
        $mid = (int) $dept['manager'];
        if (!isset($grouped[$mid])) {
            $grouped[$mid] = [
                'employee_id' => $mid,
                'employee_name' => $dept['manager_name'],
                'department_ids' => [],
            ];
        }
        $grouped[$mid]['department_ids'][] = (int) $dept['id'];
    }
    return array_values($grouped);
}
    private static function buildChartContext(array $settings, array $groupedManagers): array
    {
        $ceoId = 0;
        if (($settings['ceo_mode'] ?? '') === 'employee' && !empty($settings['ceo_employee_id'])) {
            $ceoId = (int) $settings['ceo_employee_id'];
        }
        $ctoId = !empty($settings['cto_employee_id']) ? (int) $settings['cto_employee_id'] : 0;
        $managerIds = array_map('intval', array_keys($groupedManagers));

        $executiveIds = array_values(array_filter(array_unique(array_merge([$ceoId, $ctoId], $managerIds))));
        $staffExclude = $executiveIds;

        return [
            'ceo_id' => $ceoId,
            'cto_id' => $ctoId,
            'executive_ids' => $executiveIds,
            'manager_ids' => $managerIds,
            'staff_exclude' => $staffExclude,
        ];
    }

    private static function isExecutive(int $employeeId, array $context): bool
    {
        return $employeeId > 0 && in_array($employeeId, $context['executive_ids'], true);
    }

    private static function mergeExclude(array $localIds, array $context): array
    {
        return array_values(array_unique(array_merge(
            array_filter(array_map('intval', $localIds)),
            $context['staff_exclude'] ?? []
        )));
    }

    private static function renderNode(string $cssClass, string $name, string $subtitle, bool $hasChildren, ?string $profilePic = null): string
    {
        $name = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        $subtitle = htmlspecialchars($subtitle, ENT_QUOTES, 'UTF-8');
        $defaultAvatar = '../assets/images/profile-image/default-avatar.svg';

        $avatarUrl = $defaultAvatar;
        if (!empty($profilePic)) {
            $diskPath = \App\Helpers\StorageHelper::diskPath($profilePic);
            if (file_exists($diskPath)) {
                $avatarUrl = '../' . ltrim($profilePic, '/');
            }
        }

        $avatarHtml = '<img src="' . htmlspecialchars($avatarUrl, ENT_QUOTES, 'UTF-8') . '" class="hier-node-avatar" alt="Avatar" onerror="this.onerror=null; this.src=\'' . $defaultAvatar . '\';">';

        $onclick = $hasChildren ? ' onclick="toggleNode(this)"' : '';
        $toggle = $hasChildren ? '<div class="hier-toggle"><i data-lucide="chevron-down"></i></div>' : '';

        return '<div class="' . $cssClass . '"' . $onclick . '>'
            . $avatarHtml
            . '<div class="hier-info"><h4>' . $name . '</h4><p>' . $subtitle . '</p></div>'
            . $toggle . '</div>';
    }

    private static function fetchDeptStaff(PDO $pdo, int $deptId, array $excludeIds): array
    {
        $excludeIds = array_values(array_filter(array_map('intval', $excludeIds)));
        $sql = "SELECT id, CONCAT_WS(' ', first_name, NULLIF(middle_name, ''), last_name) AS fullname, job_title, profile_pic
                FROM employees
                WHERE department_id = ? AND deleted_at IS NULL AND status = 'Active' AND role IN ('Employee', 'Admin', 'HR')";
        $params = [$deptId];
        if (!empty($excludeIds)) {
            $placeholders = implode(',', array_fill(0, count($excludeIds), '?'));
            $sql .= " AND id NOT IN ($placeholders)";
            $params = array_merge($params, $excludeIds);
        }
        $sql .= ' ORDER BY first_name ASC, last_name ASC';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private static function renderStaffStack(PDO $pdo, array $dept, array $excludeIds, array $context, bool $includeDeptBadge = true): string
    {
        $staff = self::fetchDeptStaff($pdo, (int) $dept['id'], self::mergeExclude($excludeIds, $context));
        if (empty($staff)) {
            return '';
        }

        $html = '';
        if ($includeDeptBadge) {
            $html .= '<div class="hier-dept-header"><span class="hier-dept-badge">' . htmlspecialchars($dept['name'], ENT_QUOTES, 'UTF-8') . '</span></div>';
        }
        $html .= '<ul class="vertical-stack">';
        foreach ($staff as $s) {
            $jobTitle = trim($s['job_title'] ?? '') ?: 'Employee';
            $html .= '<li>' . self::renderNode(
                'hier-node staff',
                $s['fullname'],
                $jobTitle,
                false,
                $s['profile_pic'] ?? null
            ) . '</li>';
        }
        $html .= '</ul>';
        return $html;
    }

    private static function renderDepartmentBranch(PDO $pdo, array $dept, array $context): string
    {
        $managerId = (int) ($dept['manager_id'] ?? $dept['manager'] ?? 0);
        $headId = (int) ($dept['head_id'] ?? $dept['head'] ?? 0);
        $headRole = $dept['head_role'] ?? 'Employee';
        $headProfilePic = $dept['head_profile_pic'] ?? null;
        $deptBadge = '<div class="hier-dept-header"><span class="hier-dept-badge">' . htmlspecialchars($dept['name'], ENT_QUOTES, 'UTF-8') . '</span></div>';

        if ($headId && (!self::isChartEligibleRole($headRole) || in_array($headId, $context['executive_ids'], true))) {
            $staffHtml = self::renderStaffStack($pdo, $dept, [$managerId, $headId], $context, true);
            if ($staffHtml === '') {
                return '';
            }
            return '<li class="hier-staff-only-branch">' . $staffHtml . '</li>';
        }

        if ($headId) {
            $headName = $dept['head_fullname'] ?? $dept['head_name'] ?? 'Department Head';
            $staffHtml = self::renderStaffStack($pdo, $dept, [$managerId, $headId], $context, false);
            $hasChildren = $staffHtml !== '';

            if (!$hasChildren && in_array($headId, $context['manager_ids'], true)) {
                return '';
            }

            $html = '<li class="hier-dept-branch">';
            $html .= $deptBadge;
            $html .= self::renderNode('hier-node hod', $headName, 'Head | ' . $dept['name'], $hasChildren, $headProfilePic);
            $html .= $staffHtml;
            $html .= '</li>';
            return $html;
        }

        $staffHtml = self::renderStaffStack($pdo, $dept, [$managerId], $context, true);
        if ($staffHtml === '') {
            return '';
        }

        return '<li class="hier-staff-only-branch">' . $staffHtml . '</li>';
    }

    private static function renderManagerBranch(PDO $pdo, int $managerId, array $managerData, array $context): string
    {
        if (($context['ceo_id'] ?? 0) === $managerId) {
            return '';
        }

        $managerRole = $managerData['role'] ?? 'Employee';
        if (!self::isChartEligibleRole($managerRole)) {
            return '';
        }

        $isCto = ($context['cto_id'] ?? 0) === $managerId;
        $nodeClass = $isCto ? 'hier-node cto' : 'hier-node cio';
        $roleTitle = $isCto ? (trim($managerData['title'] ?? '') ?: 'CIO') : (trim($managerData['title'] ?? '') ?: 'Manager');
        $ownDept = !empty($managerData['dept_name']) ? $managerData['dept_name'] : 'Management';

        $childHtml = '';
        foreach ($managerData['departments'] as $dept) {
            $branch = self::renderDepartmentBranch($pdo, $dept, $context);
            if ($branch !== '') {
                $childHtml .= $branch;
            }
        }
        $hasChildren = $childHtml !== '';

        $subtitle = $roleTitle . ' | ' . $ownDept;

        $html = '<li>';
        $html .= self::renderNode(
            $nodeClass,
            $managerData['fullname'],
            $subtitle,
            $hasChildren,
            $managerData['profile_pic'] ?? null
        );

        if ($hasChildren) {
            $html .= '<ul>' . $childHtml . '</ul>';
        }

        $html .= '</li>';
        return $html;
    }

    private static function loadDepartmentGroups(PDO $pdo): array
    {
        $deptStmt = $pdo->query(
            "SELECT d.id, d.name, d.manager AS manager_id,
                    CONCAT_WS(' ', em.first_name, NULLIF(em.middle_name, ''), em.last_name) AS manager_fullname,
                    em.job_title AS m_title, em.role AS manager_role, em.profile_pic AS manager_profile_pic,
                    md.name AS manager_dept_name,
                    d.head AS head_id,
                    CONCAT_WS(' ', eh.first_name, NULLIF(eh.middle_name, ''), eh.last_name) AS head_fullname,
                    eh.job_title AS h_title, eh.role AS head_role, eh.profile_pic AS head_profile_pic
             FROM departments d
             LEFT JOIN employees em ON d.manager = em.id
             LEFT JOIN departments md ON em.department_id = md.id AND md.deleted_at IS NULL
             LEFT JOIN employees eh ON d.head = eh.id
             WHERE d.deleted_at IS NULL
             ORDER BY manager_fullname ASC, d.name ASC"
        );
        $allDepts = $deptStmt->fetchAll(PDO::FETCH_ASSOC);

        $groupedManagers = [];
        $orphans = [];

        foreach ($allDepts as $dept) {
            $mId = $dept['manager_id'] ?: null;
            if ($mId && self::isChartEligibleRole($dept['manager_role'] ?? 'Employee')) {
                if (!isset($groupedManagers[$mId])) {
                    $groupedManagers[$mId] = [
                        'fullname' => $dept['manager_fullname'],
                        'title' => $dept['m_title'],
                        'role' => $dept['manager_role'],
                        'profile_pic' => $dept['manager_profile_pic'],
                        'dept_name' => $dept['manager_dept_name'] ?: 'Management',
                        'departments' => [],
                    ];
                }
                $groupedManagers[$mId]['departments'][] = $dept;
            } else {
                $orphans[] = $dept;
            }
        }

        return ['managers' => $groupedManagers, 'orphans' => $orphans];
    }

    public static function renderOrgChart(PDO $pdo): string
    {
        $settings = self::getSettings($pdo);
        $groups = self::loadDepartmentGroups($pdo);
        $context = self::buildChartContext($settings, $groups['managers']);

        $ceoName = '';
        $ceoSubtitle = 'CEO';
        $ceoHasChildren = false;
        $ceoProfilePic = null;

        if ($settings['ceo_mode'] === 'employee' && $settings['ceo_employee_id'] !== '') {
            $ceo = self::fetchEmployee($pdo, (int) $settings['ceo_employee_id']);
            if ($ceo && self::isChartEligibleRole($ceo['role'] ?? 'Employee')) {
                $ceoName = self::employeeName($ceo);
                $ceoSubtitle = trim($ceo['job_title'] ?? '') ?: 'CEO';
                $ceoProfilePic = $ceo['profile_pic'] ?? null;
            }
        } else {
            $ceoName = trim($settings['ceo_manual_name']);
            $ceoSubtitle = trim($settings['ceo_manual_title']) ?: 'CEO';
        }

        if ($ceoName === '') {
            return '<li><div class="hierarchy-empty-state">'
                . '<p class="font-14 text-dark font-600 mb-8">Organization hierarchy is not configured.</p>'
                . '<p class="font-13 text-light mb-0">Open <strong>Hierarchy Settings</strong> to set CEO, CIO, and managers.</p>'
                . '</div></li>';
        }

        $underCeoBranches = '';

        // 1. MANAGEMENT Department Branch under CEO
        $mgmtDeptId = null;
        foreach ($groups['orphans'] as $k => $dept) {
            if (strcasecmp($dept['name'], 'Management') === 0 || (int)$dept['id'] === (int)($settings['management_dept_id'] ?? 0)) {
                $mgmtDeptId = (int)$dept['id'];
                unset($groups['orphans'][$k]);
                break;
            }
        }

        if (!$mgmtDeptId) {
            $mStmt = $pdo->query("SELECT id FROM departments WHERE name = 'Management' AND deleted_at IS NULL LIMIT 1");
            $mgmtDeptId = $mStmt->fetchColumn() ?: null;
        }

        if ($mgmtDeptId) {
            $mgmtStaffStmt = $pdo->prepare(
                "SELECT e.id, CONCAT_WS(' ', e.first_name, NULLIF(e.middle_name, ''), e.last_name) AS fullname,
                        e.job_title, e.role, e.profile_pic
                 FROM employees e
                 WHERE e.department_id = ? AND e.deleted_at IS NULL AND e.status = 'Active' AND e.role IN ('Employee', 'Admin', 'HR')
                 ORDER BY e.first_name ASC, e.last_name ASC"
            );
            $mgmtStaffStmt->execute([$mgmtDeptId]);
            $mgmtEmps = $mgmtStaffStmt->fetchAll(PDO::FETCH_ASSOC);

            $mgmtMembersHtml = '';
            foreach ($mgmtEmps as $mEmp) {
                $mEmpId = (int)$mEmp['id'];
                if ($mEmpId === $context['ceo_id']) {
                    continue;
                }

                if (isset($groups['managers'][$mEmpId])) {
                    $mgmtMembersHtml .= self::renderManagerBranch($pdo, $mEmpId, $groups['managers'][$mEmpId], $context);
                    unset($groups['managers'][$mEmpId]);
                } else {
                    $jobTitle = trim($mEmp['job_title'] ?? '') ?: 'Management';
                    $mgmtMembersHtml .= '<li>' . self::renderNode(
                        'hier-node staff',
                        $mEmp['fullname'],
                        $jobTitle,
                        false,
                        $mEmp['profile_pic'] ?? null
                    ) . '</li>';
                }
            }

            if ($mgmtMembersHtml !== '') {
                $underCeoBranches .= '<li class="hier-dept-branch">'
                    . '<div class="hier-dept-header"><span class="hier-dept-badge">MANAGEMENT</span></div>'
                    . '<ul>' . $mgmtMembersHtml . '</ul>'
                    . '</li>';
            }
        }

        // 2. Render remaining Manager branches
        foreach ($groups['managers'] as $managerId => $managerData) {
            $underCeoBranches .= self::renderManagerBranch($pdo, (int) $managerId, $managerData, $context);
        }

        // 3. Render remaining Orphan departments
        foreach ($groups['orphans'] as $dept) {
            $branch = self::renderDepartmentBranch($pdo, $dept, $context);
            if ($branch !== '') {
                $underCeoBranches .= $branch;
            }
        }

        $ceoHasChildren = $underCeoBranches !== '';
        $html = '<li>';
        $html .= self::renderNode('hier-node root ceo', $ceoName, $ceoSubtitle, $ceoHasChildren, $ceoProfilePic);
        if ($ceoHasChildren) {
            $html .= '<ul>' . $underCeoBranches . '</ul>';
        }
        $html .= '</li>';

        return $html;
    }
}
?>
