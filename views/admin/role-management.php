<?php
if (!\App\Core\Auth::isLoggedIn() || ($_SESSION['user_role'] ?? '') !== 'Admin') {
    $_SESSION['error'] = 'Only Admin can manage HR access control.';
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    $scriptDir = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
    if ($scriptDir !== '' && preg_match('#/public$#', $scriptDir)) {
        $basePath = substr($scriptDir, 0, -7);
    } else {
        $basePath = $scriptDir ?: '';
    }
    $prefix = ($basePath === '/' ? '' : $basePath);
    header('Location: ' . $prefix . '/');
    exit;
}
$page_title = "Access Control";
$page_subtitle = "Control which HR portal pages and actions are allowed.";
include __DIR__ . '/../partials/admin/header.php';
?>
<?php include __DIR__ . '/../partials/admin/sidebar.php'; ?>

<div class="role-management-container">
    <div class="card mb-24">
        <div class="flex-between flex-wrap gap-15">
            <div class="flex-center gap-15">
                <div class="stat-icon primary">
                    <i data-lucide="shield-check"></i>
                </div>
                <div>
                    <h4 class="font-18 font-700 m-0">Permissions Matrix</h4>
                    <p class="font-12 text-light m-0">Configure page-level access for the HR portal. Each module shows
                        only the permissions it supports; unavailable actions are marked with <span
                            class="perm-na-label">—</span>. Edit permissions may use context-specific
                        labels—<strong>Mark as Read</strong> (Notifications), <strong>Active / Close</strong> (Job
                        Postings), and <strong>Schedule Interview</strong>, <strong>Update Pipeline</strong>, or
                        <strong>Reject / Ban</strong> (Candidate Pool). The Interviews page inherits access from
                        Candidate Pool (View + Schedule Interview).</p>
                </div>
            </div>
            <div class="flex-center gap-12">
                <button class="btn-primary px-30" id="savePermissions">
                    <i data-lucide="save" size="16"></i>
                    <span>Save Changes</span>
                </button>
            </div>
        </div>
<?php
$stmtCheckinSet = $pdo->query("SELECT meta_value FROM settings WHERE meta_key = 'enable_self_checkin' LIMIT 1");
$isCheckinEnabled = ($stmtCheckinSet && ($cval = $stmtCheckinSet->fetchColumn()) !== false) ? ($cval !== '0' && $cval !== 0) : true;
?>
    <!-- Self Check-In / Check-Out Access Toggle Card -->
    <div class="card mb-24 p-20">
        <div class="flex-between flex-wrap gap-15">
            <div class="flex-center gap-15">
                <div class="stat-icon warning" style="width: 44px; height: 44px; display: flex; align-items: center; justify-content: center; background: rgba(245, 158, 11, 0.1); color: #f59e0b; border-radius: 10px;">
                    <i data-lucide="clock" size="22"></i>
                </div>
                <div>
                    <h4 class="font-16 font-700 m-0">Self Check-In / Check-Out Button Access</h4>
                    <p class="font-12 text-light m-0 mt-2">Control whether employees & HR can see and use the self Check-In / Check-Out button in the portal topbar.</p>
                </div>
            </div>
            <div class="flex-center gap-12">
                <label style="display: inline-flex; align-items: center; cursor: pointer; gap: 10px; font-weight: 600; font-size: 14px; user-select: none;">
                    <input type="checkbox" id="selfCheckinToggle" class="custom-toggle" style="width: 20px; height: 20px; accent-color: var(--primary-color, #6c4cf1); cursor: pointer;" <?= $isCheckinEnabled ? 'checked' : '' ?>>
                    <span id="selfCheckinStatusText" style="color: <?= $isCheckinEnabled ? '#16a34a' : '#ef4444' ?>;"><?= $isCheckinEnabled ? 'Show Button (Enabled)' : 'Hide Button (Disabled)' ?></span>
                </label>
            </div>
        </div>
    </div>

    <div class="card p-0 overflow-hidden">
        <div class="table-responsive">
            <table class="data-table mb-0" id="permissionsTable">
                <thead>
                    <tr>
                        <th style="width: 250px;">Module / Page</th>
                        <th class="text-center">View Access</th>
                        <th class="text-center">Create</th>
                        <th class="text-center">Edit / Update</th>
                        <th class="text-center">Delete</th>
                        <th class="text-center">Export / PDF</th>
                    </tr>
                </thead>
                <tbody>
                    <?php include __DIR__ . '/../partials/shared/hr_permissions_matrix.php'; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>



<script src="<?= \App\Core\View::asset('js/admin/access-control.js') ?>"></script>
<?php include __DIR__ . '/../partials/admin/footer.php'; ?>