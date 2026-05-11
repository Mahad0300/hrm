<?php
$page_title = "Role Management";
$page_subtitle = "Control application access and granular permissions for each role.";
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
                    <p class="font-12 text-light m-0">Select a role to modify its access rights</p>
                </div>
            </div>
            <div class="flex-center gap-12">
                <select class="form-control bg-white-input w-200" id="roleSelector">
                    <option value="hr">Human Resources (HR)</option>
                    <option value="manager">Manager</option>
                    <option value="employee">Employee</option>
                    <option value="intern">Intern</option>
                </select>
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
                    <!-- Main Menu (sidebar) -->
                    <tr class="perm-matrix-section">
                        <td colspan="6" class="perm-matrix-section__cell">Main Menu</td>
                    </tr>
                    <tr data-page="index">
                        <td>
                            <div class="flex-center gap-12 permission-row-title">
                                <i data-lucide="layout-dashboard" size="16"></i>
                                <span>Dashboard</span>
                            </div>
                        </td>
                        <td class="text-center">
                            <label class="switch">
                                <input type="checkbox" class="view-toggle" checked>
                                <span class="slider round"></span>
                            </label>
                        </td>
                        <td class="text-center"><input type="checkbox" class="action-check" disabled></td>
                        <td class="text-center"><input type="checkbox" class="action-check" disabled></td>
                        <td class="text-center"><input type="checkbox" class="action-check" disabled></td>
                        <td class="text-center"><input type="checkbox" class="action-check" checked></td>
                    </tr>

                    <!-- Organization -->
                    <tr class="perm-matrix-section">
                        <td colspan="6" class="perm-matrix-section__cell">Organization</td>
                    </tr>
                    <tr data-page="employees">
                        <td>
                            <div class="flex-center gap-12 permission-row-title">
                                <i data-lucide="users" size="16"></i>
                                <span>Employees</span>
                            </div>
                        </td>
                        <td class="text-center">
                            <label class="switch">
                                <input type="checkbox" class="view-toggle" checked>
                                <span class="slider round"></span>
                            </label>
                        </td>
                        <td class="text-center"><input type="checkbox" class="action-check" checked></td>
                        <td class="text-center"><input type="checkbox" class="action-check" checked></td>
                        <td class="text-center"><input type="checkbox" class="action-check"></td>
                        <td class="text-center"><input type="checkbox" class="action-check" checked></td>
                    </tr>
                    <tr data-page="attendance">
                        <td>
                            <div class="flex-center gap-12 permission-row-title">
                                <i data-lucide="calendar-check" size="16"></i>
                                <span>Attendance</span>
                            </div>
                        </td>
                        <td class="text-center">
                            <label class="switch">
                                <input type="checkbox" class="view-toggle" checked>
                                <span class="slider round"></span>
                            </label>
                        </td>
                        <td class="text-center"><input type="checkbox" class="action-check" checked></td>
                        <td class="text-center"><input type="checkbox" class="action-check" checked></td>
                        <td class="text-center"><input type="checkbox" class="action-check"></td>
                        <td class="text-center"><input type="checkbox" class="action-check" checked></td>
                    </tr>
                    <tr data-page="leave-management">
                        <td>
                            <div class="flex-center gap-12 permission-row-title">
                                <i data-lucide="clock" size="16"></i>
                                <span>Leave Management</span>
                            </div>
                        </td>
                        <td class="text-center">
                            <label class="switch">
                                <input type="checkbox" class="view-toggle" checked>
                                <span class="slider round"></span>
                            </label>
                        </td>
                        <td class="text-center"><input type="checkbox" class="action-check" checked></td>
                        <td class="text-center"><input type="checkbox" class="action-check" checked></td>
                        <td class="text-center"><input type="checkbox" class="action-check"></td>
                        <td class="text-center"><input type="checkbox" class="action-check" checked></td>
                    </tr>
                    <tr data-page="candidates">
                        <td>
                            <div class="flex-center gap-12 permission-row-title">
                                <i data-lucide="user-plus" size="16"></i>
                                <span>New Joining</span>
                            </div>
                        </td>
                        <td class="text-center">
                            <label class="switch">
                                <input type="checkbox" class="view-toggle" checked>
                                <span class="slider round"></span>
                            </label>
                        </td>
                        <td class="text-center"><input type="checkbox" class="action-check" checked></td>
                        <td class="text-center"><input type="checkbox" class="action-check" checked></td>
                        <td class="text-center"><input type="checkbox" class="action-check"></td>
                        <td class="text-center"><input type="checkbox" class="action-check" checked></td>
                    </tr>
                    <tr data-page="hierarchy">
                        <td>
                            <div class="flex-center gap-12 permission-row-title">
                                <i data-lucide="network" size="16"></i>
                                <span>Hierarchy</span>
                            </div>
                        </td>
                        <td class="text-center">
                            <label class="switch">
                                <input type="checkbox" class="view-toggle" checked>
                                <span class="slider round"></span>
                            </label>
                        </td>
                        <td class="text-center"><input type="checkbox" class="action-check" disabled></td>
                        <td class="text-center"><input type="checkbox" class="action-check" checked></td>
                        <td class="text-center"><input type="checkbox" class="action-check" disabled></td>
                        <td class="text-center"><input type="checkbox" class="action-check" checked></td>
                    </tr>
                    <tr data-page="kpi-management">
                        <td>
                            <div class="flex-center gap-12 permission-row-title">
                                <i data-lucide="line-chart" size="16"></i>
                                <span>KPI Management</span>
                            </div>
                        </td>
                        <td class="text-center">
                            <label class="switch">
                                <input type="checkbox" class="view-toggle" checked>
                                <span class="slider round"></span>
                            </label>
                        </td>
                        <td class="text-center"><input type="checkbox" class="action-check" checked></td>
                        <td class="text-center"><input type="checkbox" class="action-check" checked></td>
                        <td class="text-center"><input type="checkbox" class="action-check"></td>
                        <td class="text-center"><input type="checkbox" class="action-check" checked></td>
                    </tr>
                    <tr data-page="event-calendar">
                        <td>
                            <div class="flex-center gap-12 permission-row-title">
                                <i data-lucide="calendar" size="16"></i>
                                <span>Event Calendar</span>
                            </div>
                        </td>
                        <td class="text-center">
                            <label class="switch">
                                <input type="checkbox" class="view-toggle" checked>
                                <span class="slider round"></span>
                            </label>
                        </td>
                        <td class="text-center"><input type="checkbox" class="action-check" checked></td>
                        <td class="text-center"><input type="checkbox" class="action-check" checked></td>
                        <td class="text-center"><input type="checkbox" class="action-check"></td>
                        <td class="text-center"><input type="checkbox" class="action-check"></td>
                    </tr>

                    <!-- Job Management (submenu) -->
                    <tr class="bg-light-soft">
                        <td colspan="6" class="font-12 font-700 text-light ls-05 p-12 px-24 uppercase text-left">Job
                            Management</td>
                    </tr>
                    <tr data-page="job-list">
                        <td>
                            <div class="flex-center gap-12 permission-row-title">
                                <i data-lucide="list" size="16"></i>
                                <span>Job Postings</span>
                            </div>
                        </td>
                        <td class="text-center">
                            <label class="switch">
                                <input type="checkbox" class="view-toggle" checked>
                                <span class="slider round"></span>
                            </label>
                        </td>
                        <td class="text-center"><input type="checkbox" class="action-check" checked></td>
                        <td class="text-center"><input type="checkbox" class="action-check" checked></td>
                        <td class="text-center"><input type="checkbox" class="action-check"></td>
                        <td class="text-center"><input type="checkbox" class="action-check" checked></td>
                    </tr>
                    <tr data-page="create-job">
                        <td>
                            <div class="flex-center gap-12 permission-row-title">
                                <i data-lucide="plus-circle" size="16"></i>
                                <span>Create New Job</span>
                            </div>
                        </td>
                        <td class="text-center">
                            <label class="switch">
                                <input type="checkbox" class="view-toggle" checked>
                                <span class="slider round"></span>
                            </label>
                        </td>
                        <td class="text-center"><input type="checkbox" class="action-check" checked></td>
                        <td class="text-center"><input type="checkbox" class="action-check" checked></td>
                        <td class="text-center"><input type="checkbox" class="action-check"></td>
                        <td class="text-center"><input type="checkbox" class="action-check" disabled></td>
                    </tr>
                    <tr data-page="job-candidates">
                        <td>
                            <div class="flex-center gap-12 permission-row-title">
                                <i data-lucide="users" size="16"></i>
                                <span>Candidate Pool</span>
                            </div>
                        </td>
                        <td class="text-center">
                            <label class="switch">
                                <input type="checkbox" class="view-toggle" checked>
                                <span class="slider round"></span>
                            </label>
                        </td>
                        <td class="text-center"><input type="checkbox" class="action-check" checked></td>
                        <td class="text-center"><input type="checkbox" class="action-check" checked></td>
                        <td class="text-center"><input type="checkbox" class="action-check"></td>
                        <td class="text-center"><input type="checkbox" class="action-check" checked></td>
                    </tr>
                    <tr data-page="interviews">
                        <td>
                            <div class="flex-center gap-12 permission-row-title">
                                <i data-lucide="calendar" size="16"></i>
                                <span>Interviews</span>
                            </div>
                        </td>
                        <td class="text-center">
                            <label class="switch">
                                <input type="checkbox" class="view-toggle" checked>
                                <span class="slider round"></span>
                            </label>
                        </td>
                        <td class="text-center"><input type="checkbox" class="action-check" checked></td>
                        <td class="text-center"><input type="checkbox" class="action-check" checked></td>
                        <td class="text-center"><input type="checkbox" class="action-check" disabled></td>
                        <td class="text-center"><input type="checkbox" class="action-check" checked></td>
                    </tr>

                    <!-- Administration -->
                    <tr class="perm-matrix-section">
                        <td colspan="6" class="perm-matrix-section__cell">Administration</td>
                    </tr>
                    <tr data-page="payroll">
                        <td>
                            <div class="flex-center gap-12 permission-row-title">
                                <i data-lucide="banknote" size="16"></i>
                                <span>Payroll</span>
                            </div>
                        </td>
                        <td class="text-center">
                            <label class="switch">
                                <input type="checkbox" class="view-toggle" checked>
                                <span class="slider round"></span>
                            </label>
                        </td>
                        <td class="text-center"><input type="checkbox" class="action-check" checked></td>
                        <td class="text-center"><input type="checkbox" class="action-check" checked></td>
                        <td class="text-center"><input type="checkbox" class="action-check"></td>
                        <td class="text-center"><input type="checkbox" class="action-check" checked></td>
                    </tr>
                    <tr data-page="activity-logs">
                        <td>
                            <div class="flex-center gap-12 permission-row-title">
                                <i data-lucide="history" size="16"></i>
                                <span>Activity Logs</span>
                            </div>
                        </td>
                        <td class="text-center">
                            <label class="switch">
                                <input type="checkbox" class="view-toggle" checked>
                                <span class="slider round"></span>
                            </label>
                        </td>
                        <td class="text-center"><input type="checkbox" class="action-check" disabled></td>
                        <td class="text-center"><input type="checkbox" class="action-check" disabled></td>
                        <td class="text-center"><input type="checkbox" class="action-check" disabled></td>
                        <td class="text-center"><input type="checkbox" class="action-check" checked></td>
                    </tr>
                    <tr data-page="announcements">
                        <td>
                            <div class="flex-center gap-12 permission-row-title">
                                <i data-lucide="megaphone" size="16"></i>
                                <span>Announcements</span>
                            </div>
                        </td>
                        <td class="text-center">
                            <label class="switch">
                                <input type="checkbox" class="view-toggle" checked>
                                <span class="slider round"></span>
                            </label>
                        </td>
                        <td class="text-center"><input type="checkbox" class="action-check" checked></td>
                        <td class="text-center"><input type="checkbox" class="action-check" checked></td>
                        <td class="text-center"><input type="checkbox" class="action-check"></td>
                        <td class="text-center"><input type="checkbox" class="action-check"></td>
                    </tr>
                    <tr data-page="notifications">
                        <td>
                            <div class="flex-center gap-12 permission-row-title">
                                <i data-lucide="bell" size="16"></i>
                                <span>Notifications</span>
                            </div>
                        </td>
                        <td class="text-center">
                            <label class="switch">
                                <input type="checkbox" class="view-toggle" checked>
                                <span class="slider round"></span>
                            </label>
                        </td>
                        <td class="text-center"><input type="checkbox" class="action-check" disabled></td>
                        <td class="text-center"><input type="checkbox" class="action-check" checked></td>
                        <td class="text-center"><input type="checkbox" class="action-check"></td>
                        <td class="text-center"><input type="checkbox" class="action-check" disabled></td>
                    </tr>

                    <!-- System (Settings submenu) -->
                    <tr class="perm-matrix-section">
                        <td colspan="6" class="perm-matrix-section__cell">System</td>
                    </tr>
                    <tr data-page="shifts">
                        <td>
                            <div class="flex-center gap-12 permission-row-title">
                                <i data-lucide="plus-circle" size="16"></i>
                                <span>Add Shift</span>
                            </div>
                        </td>
                        <td class="text-center">
                            <label class="switch">
                                <input type="checkbox" class="view-toggle" checked>
                                <span class="slider round"></span>
                            </label>
                        </td>
                        <td class="text-center"><input type="checkbox" class="action-check" checked></td>
                        <td class="text-center"><input type="checkbox" class="action-check" checked></td>
                        <td class="text-center"><input type="checkbox" class="action-check"></td>
                        <td class="text-center"><input type="checkbox" class="action-check" disabled></td>
                    </tr>
                    <tr data-page="department-management">
                        <td>
                            <div class="flex-center gap-12 permission-row-title">
                                <i data-lucide="building-2" size="16"></i>
                                <span>Dept Management</span>
                            </div>
                        </td>
                        <td class="text-center">
                            <label class="switch">
                                <input type="checkbox" class="view-toggle" checked>
                                <span class="slider round"></span>
                            </label>
                        </td>
                        <td class="text-center"><input type="checkbox" class="action-check" checked></td>
                        <td class="text-center"><input type="checkbox" class="action-check" checked></td>
                        <td class="text-center"><input type="checkbox" class="action-check"></td>
                        <td class="text-center"><input type="checkbox" class="action-check" checked></td>
                    </tr>
                    <tr data-page="role-management">
                        <td>
                            <div class="flex-center gap-12 permission-row-title">
                                <i data-lucide="shield-check" size="16"></i>
                                <span>Role Management</span>
                            </div>
                        </td>
                        <td class="text-center">
                            <label class="switch">
                                <input type="checkbox" class="view-toggle" checked>
                                <span class="slider round"></span>
                            </label>
                        </td>
                        <td class="text-center"><input type="checkbox" class="action-check" checked></td>
                        <td class="text-center"><input type="checkbox" class="action-check" checked></td>
                        <td class="text-center"><input type="checkbox" class="action-check"></td>
                        <td class="text-center"><input type="checkbox" class="action-check" disabled></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>



<script src="assets/js/role-management.js"></script>
<?php include 'includes/footer.php'; ?>