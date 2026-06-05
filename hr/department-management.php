<?php
$page_title = "Department Management";
$page_subtitle = "Configure and manage organizational departments and hierarchies.";
include 'includes/header.php';
?>
<?php include 'includes/sidebar.php'; ?>

<!-- Page Action Area -->
<div class="page-action-area">
    <button class="btn-primary" onclick="openModal('addDeptModal')">
        <i data-lucide="plus"></i>
        <span>Add New Department</span>
    </button>
</div>

<!-- Table Tools: Per Page & Summary -->
<div class="flex-between mb-24 px-4 mt-24">
    <div class="flex-center gap-10">
        <span class="font-13 text-light">Show</span>
        <select class="form-control font-13 font-600 per-page-select" id="perPageSelect">
            <option value="5">5</option>
            <option value="10" selected>10</option>
            <option value="25">25</option>
            <option value="50">50</option>
            <option value="all">All</option>
        </select>
        <span class="font-13 text-light">entries</span>
    </div>
    <div class="text-right">
        <span class="font-13 text-light" id="tableSummary">Showing 1 to 10 of 4 entries</span>
    </div>
</div>

<!-- Departments Card -->
<div class="card p-0 overflow-hidden">
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>DEPARTMENT NAME</th>
                    <th>MANAGER</th>
                    <th>HEAD</th>
                    <th>TOTAL EMPLOYEES</th>
                    <th class="text-right px-30">ACTIONS</th>
                </tr>
            </thead>
            <tbody id="deptTableBody">
                <!-- Departments will be loaded here via JS -->
            </tbody>
        </table>
    </div>
    <!-- Pagination Footer INSIDE Card -->
    <div class="p-24 flex-between border-top">
        <span class="font-13 text-light" id="paginationInfo">Showing 1 to 4 of 4 entries</span>
        <div class="flex-center gap-8" id="paginationControls">
            <button class="action-btn" id="prevPage" title="Previous"><i data-lucide="chevron-left"
                    size="16"></i></button>
            <div id="pageNumbers" class="flex-center gap-8">
                <!-- Page numbers injected by JS -->
            </div>
            <button class="action-btn" id="nextPage" title="Next"><i data-lucide="chevron-right" size="16"></i></button>
        </div>
    </div>
</div>

<!-- Add Department Modal -->
<div class="modal-overlay" id="addDeptModal">
    <div class="modal-content premium wide-sm">
        <div class="modal-header">
            <div>
                <h3>Add New Department</h3>
                <p class="font-12 text-light mt-1">Define properties for a new organizational unit</p>
            </div>
            <button class="icon-btn js-modal-close"><i data-lucide="x"></i></button>
        </div>
        <div class="modal-body p-30">
            <form id="addDeptForm">
                <div class="form-group mb-20">
                    <label class="admin-form-label">Department Name *</label>
                    <input type="text" id="deptName" class="form-control bg-white-input"
                        placeholder="e.g. Engineering, Sales" required>
                </div>
                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="admin-form-label">Department Manager</label>
                        <select id="deptManager" class="form-control bg-white-input">
                            <option value="">Loading managers...</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="admin-form-label">Department Head</label>
                        <select id="deptHead" class="form-control bg-white-input">
                            <option value="">Loading heads...</option>
                        </select>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer p-30 border-top-0">
            <button type="button" class="btn-primary no-bg border text-light js-modal-close" id="deptCancelBtn">
                <i data-lucide="x" size="18"></i>
                <span>Cancel</span>
            </button>
            <button type="submit" form="addDeptForm" id="addDeptFormSubmit" class="btn-primary px-30">Create
                Department</button>
        </div>
    </div>
</div>

<!-- Edit Department Modal -->
<div class="modal-overlay" id="editDeptModal">
    <div class="modal-content premium wide-sm">
        <div class="modal-header">
            <div>
                <h3>Edit Department</h3>
                <p class="font-12 text-light mt-1">Update organizational unit properties</p>
            </div>
            <button class="icon-btn js-modal-close"><i data-lucide="x"></i></button>
        </div>
        <div class="modal-body p-30">
            <form id="editDeptForm">
                <input type="hidden" id="editDeptId">
                <div class="form-group mb-20">
                    <label class="admin-form-label">Department Name *</label>
                    <input type="text" id="editDeptName" class="form-control bg-white-input"
                        placeholder="e.g. Engineering, Sales" required>
                </div>
                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="admin-form-label">Department Manager</label>
                        <select id="editDeptManager" class="form-control bg-white-input">
                            <option value="">Loading managers...</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="admin-form-label">Department Head</label>
                        <select id="editDeptHead" class="form-control bg-white-input">
                            <option value="">Loading heads...</option>
                        </select>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer p-30 border-top-0">
            <button type="button" class="btn-primary no-bg border text-light js-modal-close" id="editDeptCancelBtn">
                <i data-lucide="x" size="18"></i>
                <span>Cancel</span>
            </button>
            <button type="submit" form="editDeptForm" id="editDeptFormSubmit" class="btn-primary px-30">Save
                Changes</button>
        </div>
    </div>
</div>

<script src="assets/js/department.js"></script>
<?php include 'includes/footer.php'; ?>