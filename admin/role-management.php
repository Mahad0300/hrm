<?php
require_once dirname(__DIR__) . '/includes/auth_helper.php';
require_once dirname(__DIR__) . '/includes/access_control_helper.php';
if (!isLoggedIn() || ($_SESSION['user_role'] ?? '') !== 'Admin') {
    $_SESSION['error'] = 'Only Admin can manage HR access control.';
    header('Location: ' . (($_SESSION['user_role'] ?? '') === 'HR' ? '../hr/index.php' : 'index.php'));
    exit;
}
$page_title = "Access Control";
$page_subtitle = "Control which HR portal pages and actions are allowed.";
include 'includes/header.php';
?>
<?php include 'includes/sidebar.php'; ?>

<div class="role-management-container">
    <div class="card mb-24">
        <div class="flex-between flex-wrap gap-15">
            <div class="flex-center gap-15">
                <div class="stat-icon primary">
                    <i data-lucide="shield-check"></i>
                </div>
                <div>
                    <h4 class="font-18 font-700 m-0">Permissions Matrix</h4>
                    <p class="font-12 text-light m-0">Only actions available on each page are shown. <span class="perm-na-label">—</span> = not available. Custom labels: Notifications = <strong>Mark as Read</strong>, Job Postings = <strong>Active / Close</strong>, Candidate Pool = <strong>Schedule Interview</strong>, <strong>Update Pipeline</strong>, <strong>Reject / Ban</strong>. Interviews page uses <strong>Candidate Pool</strong> permissions (View + Schedule Interview).</p>
                </div>
            </div>
            <div class="flex-center gap-12">
                <button class="btn-primary px-30" id="savePermissions">
                    <i data-lucide="save" size="16"></i>
                    <span>Save Changes</span>
                </button>
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
<?php include dirname(__DIR__) . '/includes/partials/hr_permissions_matrix.php'; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>



<script src="assets/js/access-control.js"></script>
<?php include 'includes/footer.php'; ?>
