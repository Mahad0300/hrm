<?php
require_once dirname(__DIR__) . '/includes/db_connect.php';

$page_title = "Leave Management";
$page_subtitle = "Review and manage employee time-off requests.";
include 'includes/header.php';

// Fetch Leave Types Quotas
$stmt = $pdo->prepare("SELECT `id`, `name`, `days_per_year` FROM `leave_types`");
$stmt->execute();
$leaveTypeRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Parse them for easily injecting into HTML
$leaveQuotas = [
    'sick' => 0,
    'casual' => 0,
    'annual' => 0
];
foreach ($leaveTypeRows as $lt) {
    // We map id 1: Sick, 2: Casual, 3: Annual based on seed data
    if ($lt['id'] == 1) $leaveQuotas['sick'] = $lt['days_per_year'];
    if ($lt['id'] == 2) $leaveQuotas['casual'] = $lt['days_per_year'];
    if ($lt['id'] == 3) $leaveQuotas['annual'] = $lt['days_per_year'];
}

// Fetch all leave requests joined with employees and leave types
$req_stmt = $pdo->prepare("
    SELECT lr.*, lt.name as leave_type_name, e.first_name, e.middle_name, e.last_name, e.profile_pic 
    FROM leave_requests lr
    JOIN leave_types lt ON lr.leave_type_id = lt.id
    JOIN employees e ON lr.employee_id = e.id
    ORDER BY lr.applied_at DESC
");
$req_stmt->execute();
$all_requests = $req_stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<?php include 'includes/sidebar.php'; ?>

<!-- Page Action Area -->
<div class="page-action-area">
    <button type="button" class="btn-primary" id="openLeaveSettingsBtn">
        <i data-lucide="settings"></i>
        <span>Leave Settings</span>
    </button>
    <div id="allRequestsBtn">
        <button class="btn-primary" onclick="toggleLeaveView('pending')">
            <i data-lucide="clipboard-list"></i>
            <span>Request Leaves</span>
        </button>
    </div>
    <div id="backToAllBtn" class="hidden">
        <button class="btn-primary no-bg border text-light" onclick="toggleLeaveView('all')">
            <i data-lucide="arrow-left"></i>
            <span>Back to All</span>
        </button>
    </div>
</div>

<!-- Filters Card -->
<div class="card p-24 mb-24">
    <div class="filter-grid">
        <div class="filter-item">
            <label class="admin-form-label font-12">Search Employee</label>
            <div class="search-box w-full">
                <i data-lucide="search" size="16"></i>
                <input type="text" id="leaveFilterSearch" class="form-control" placeholder="Search by employee name...">
            </div>
        </div>
        <div class="filter-item">
            <label class="admin-form-label font-12">Leave Type</label>
            <select class="form-control">
                <option value="">Leave Type (All)</option>
                <option value="sick">Sick Leave</option>
                <option value="casual">Casual Leave</option>
                <option value="annual">Annual Leave</option>
            </select>
        </div>
        <div class="filter-item">
            <label class="admin-form-label font-12">Status</label>
            <select class="form-control">
                <option value="">Status (All)</option>
                <option value="pending">Pending</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
            </select>
        </div>
        <div class="filter-item">
            <label class="admin-form-label font-12">Month</label>
            <input type="month" class="form-control" value="<?= date('Y-m') ?>">
        </div>
    </div>
</div>

<div class="card p-20 mb-24 leave-quota-summary-card" id="leaveQuotaSummaryCard">
    <p class="font-13 m-0 text-dark">
        <strong>Organization leave (per year — all employees):</strong>
        <span id="leaveQuotaSummaryText" class="text-light">
            Sick <?= $leaveQuotas['sick'] ?> &middot; Casual <?= $leaveQuotas['casual'] ?> &middot; Annual <?= $leaveQuotas['annual'] ?> (days each)
        </span>
    </p>
</div>

<!-- Main Table View -->
<div id="allRequestsView">

    <!-- Table Tools: Per Page & Summary -->
    <div class="flex-between mb-24 px-4">
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
            <span class="font-13 text-light" id="tableSummary">Showing 1 to 10 of 10 entries</span>
        </div>
    </div>

    <div class="card p-0 overflow-hidden">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>EMPLOYEE</th>
                        <th>LEAVE TYPE</th>
                        <th>DURATION</th>
                        <th class="allow-wrap">REASON</th>
                        <th>DOCUMENT</th>
                        <th>STATUS</th>
                        <th class="text-right px-30">ACTIONS</th>
                    </tr>
                </thead>
                <tbody id="leaveTableBody">
                    <?php if (count($all_requests) > 0): ?>
                        <?php foreach ($all_requests as $lr): 
                            $emp_id_display = 'EMP-0' . $lr['employee_id'];
                            $middle_name_part = !empty($lr['middle_name']) ? $lr['middle_name'] . ' ' : '';
                            $leave_user_name = trim($lr['first_name'] . ' ' . $middle_name_part . $lr['last_name']);
                            $leave_user_avatar = !empty($lr['profile_pic']) ? '../' . $lr['profile_pic'] : '../images/profile-image/default-avatar.svg';
                            
                            $start_ts = strtotime($lr['start_date']);
                            $end_ts = strtotime($lr['end_date']);
                            $days = (int)round(($end_ts - $start_ts) / (60 * 60 * 24)) + 1;
                            $date_range = date("M d", $start_ts) . ' - ' . date("M d", $end_ts) . " ($days days)";
                            
                            $badge_class = 'badge-warning';
                            if ($lr['status'] === 'Approved') $badge_class = 'badge-success';
                            if ($lr['status'] === 'Rejected') $badge_class = 'badge-danger';
                            
                            $safe_reason = htmlspecialchars($lr['reason'] ?? '', ENT_QUOTES, 'UTF-8');
                            $display_reason = mb_strlen($lr['reason'] ?? '') > 35 ? mb_substr($lr['reason'], 0, 35) . '...' : ($lr['reason'] ?? '');
                            $safe_display_reason = htmlspecialchars($display_reason, ENT_QUOTES, 'UTF-8');
                            
                            $safe_admin_note = htmlspecialchars($lr['admin_note'] ?? '', ENT_QUOTES, 'UTF-8');
                            $safe_admin_note_js = htmlspecialchars(str_replace(["\r", "\n", "'", '"'], [" ", " ", "\\'", "\\\""], $lr['admin_note'] ?? ''), ENT_QUOTES, 'UTF-8');
                            $doc_path_js = !empty($lr['document_path']) ? '../' . htmlspecialchars(str_replace("\\", "/", $lr['document_path']), ENT_QUOTES, 'UTF-8') : '';
                            
                            // Setup document pill
                            $doc_ext = !empty($lr['document_path']) ? strtolower(pathinfo($lr['document_path'], PATHINFO_EXTENSION)) : '';
                            $is_img = in_array($doc_ext, ['jpg', 'jpeg', 'png']);
                        ?>
                        <tr data-id="<?= $lr['id'] ?>" data-status="<?= $lr['status'] ?>">
                            <td>
                                <div class="emp-profile">
                                    <img src="<?= htmlspecialchars($leave_user_avatar, ENT_QUOTES, 'UTF-8') ?>" class="emp-avatar" alt="">
                                    <div class="emp-info">
                                        <span class="name"><?= htmlspecialchars($leave_user_name, ENT_QUOTES, 'UTF-8') ?></span>
                                        <span class="email"><?= htmlspecialchars($emp_id_display, ENT_QUOTES, 'UTF-8') ?></span>
                                    </div>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($lr['leave_type_name']) ?></td>
                            <td><?= $date_range ?></td>
                            <td class="allow-wrap" title="<?= $safe_reason ?>"><?= $safe_display_reason ?></td>
                            <td>
                                <?php if (!empty($lr['document_path'])): ?>
                                    <button class="doc-pill <?= $is_img ? 'doc-pill-image' : 'doc-pill-pdf' ?>" title="View Document"
                                        onclick="openLeaveDocument('<?= $doc_path_js ?>','<?= $is_img ? 'image' : 'pdf' ?>')">
                                        <i data-lucide="<?= $is_img ? 'image' : 'file-text' ?>" size="14"></i>
                                        <span><?= $is_img ? 'Image' : 'PDF' ?></span>
                                    </button>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td><span class="badge <?= $badge_class ?>"><?= $lr['status'] ?></span></td>
                            <td class="text-right px-30">
                                <div class="btn-group justify-end">
                                    <button class="action-btn action-btn-view" title="View Details"
                                        onclick="openLeaveDetailModal(<?= $lr['id'] ?>, '<?= htmlspecialchars($leave_user_name, ENT_QUOTES, 'UTF-8') ?>','<?= htmlspecialchars($lr['leave_type_name'], ENT_QUOTES, 'UTF-8') ?>',<?= $days ?>,'<?= date("d M Y", $start_ts) ?>','<?= date("d M Y", $end_ts) ?>','<?= $safe_admin_note_js ?>','<?= $doc_path_js ?>','<?= $is_img ? 'image' : 'pdf' ?>', '<?= $lr['status'] ?>')"><i data-lucide="eye" size="14"></i></button>
                                    

                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="text-center py-20 text-light">No leave requests found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="p-24 flex-between border-top">
            <span class="font-13 text-light" id="paginationInfo">Showing 1 to 10 of 10 entries</span>
            <div class="flex-center gap-8" id="paginationControls">
                <button class="action-btn" id="prevPage"><i data-lucide="chevron-left" size="16"></i></button>
                <div id="pageNumbers" class="flex-center gap-8">
                    <button class="action-btn btn-active">1</button>
                </div>
                <button class="action-btn" id="nextPage"><i data-lucide="chevron-right" size="16"></i></button>
            </div>
        </div>
    </div>
</div>

<!-- Pending Requests View (In-Page) -->
<div id="pendingRequestsView" class="hidden">

    <!-- Table Tools: Per Page & Summary -->
    <div class="flex-between mb-24 px-4">
        <div class="flex-center gap-10">
            <span class="font-13 text-light">Show</span>
            <select class="form-control font-13 font-600 per-page-select" id="pendingPerPageSelect">
                <option value="5">5</option>
                <option value="10" selected>10</option>
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="all">All</option>
            </select>
            <span class="font-13 text-light">entries</span>
        </div>
        <div class="text-right">
            <span class="font-13 text-light" id="pendingTableSummary">Showing 1 to 10 of 10 entries</span>
        </div>
    </div>

    <div class="flex-between mb-20">
        <div class="section-title">
            <h3 class="font-18 font-700">Pending Requests</h3>
            <p class="font-12 text-light">Manage only pending leave applications</p>
        </div>
        <?php 
            $pending_count = 0;
            foreach ($all_requests as $r) { if ($r['status'] === 'Pending') $pending_count++; }
        ?>
        <span class="badge badge-warning"><?= $pending_count ?> Request<?= $pending_count !== 1 ? 's' : '' ?> Found</span>
    </div>

    <div class="card p-0 overflow-hidden">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>EMPLOYEE</th>
                        <th>LEAVE TYPE</th>
                        <th>DURATION</th>
                        <th>REASON</th>
                        <th>DOCUMENT</th>
                        <th>STATUS</th>
                        <th class="text-right">ACTIONS</th>
                    </tr>
                </thead>
                <tbody id="pendingTableBody">
                    <?php 
                        $has_pending = false;
                        if (count($all_requests) > 0) {
                            foreach ($all_requests as $lr) {
                                if ($lr['status'] !== 'Pending') continue;
                                $has_pending = true;
                                $emp_id_display = 'EMP-0' . $lr['employee_id'];
                                $middle_name_part = !empty($lr['middle_name']) ? $lr['middle_name'] . ' ' : '';
                                $leave_user_name = trim($lr['first_name'] . ' ' . $middle_name_part . $lr['last_name']);
                                $leave_user_avatar = !empty($lr['profile_pic']) ? '../' . $lr['profile_pic'] : '../images/profile-image/default-avatar.svg';
                                
                                $start_ts = strtotime($lr['start_date']);
                                $end_ts = strtotime($lr['end_date']);
                                $days = (int)round(($end_ts - $start_ts) / (60 * 60 * 24)) + 1;
                                $date_range = date("M d", $start_ts) . ' - ' . date("M d", $end_ts) . " ($days days)";
                                
                                $safe_reason = htmlspecialchars($lr['reason'] ?? '', ENT_QUOTES, 'UTF-8');
                                $display_reason = mb_strlen($lr['reason'] ?? '') > 35 ? mb_substr($lr['reason'], 0, 35) . '...' : ($lr['reason'] ?? '');
                                $safe_display_reason = htmlspecialchars($display_reason, ENT_QUOTES, 'UTF-8');
                                
                                $safe_admin_note_js = htmlspecialchars(str_replace(["\r", "\n", "'", '"'], [" ", " ", "\\'", "\\\""], $lr['admin_note'] ?? ''), ENT_QUOTES, 'UTF-8');
                                $doc_path_js = !empty($lr['document_path']) ? '../' . htmlspecialchars(str_replace("\\", "/", $lr['document_path']), ENT_QUOTES, 'UTF-8') : '';
                                
                                $doc_ext = !empty($lr['document_path']) ? strtolower(pathinfo($lr['document_path'], PATHINFO_EXTENSION)) : '';
                                $is_img = in_array($doc_ext, ['jpg', 'jpeg', 'png']);
                    ?>
                        <tr data-id="<?= $lr['id'] ?>" data-status="Pending">
                            <td>
                                <div class="emp-profile">
                                    <img src="<?= htmlspecialchars($leave_user_avatar, ENT_QUOTES, 'UTF-8') ?>" class="emp-avatar" alt="">
                                    <div class="emp-info">
                                        <span class="name"><?= htmlspecialchars($leave_user_name, ENT_QUOTES, 'UTF-8') ?></span>
                                        <span class="email"><?= htmlspecialchars($emp_id_display, ENT_QUOTES, 'UTF-8') ?></span>
                                    </div>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($lr['leave_type_name']) ?></td>
                            <td><?= $date_range ?></td>
                            <td class="allow-wrap" title="<?= $safe_reason ?>"><?= $safe_display_reason ?></td>
                            <td>
                                <?php if (!empty($lr['document_path'])): ?>
                                    <button class="doc-pill <?= $is_img ? 'doc-pill-image' : 'doc-pill-pdf' ?>" title="View Document"
                                        onclick="openLeaveDocument('<?= $doc_path_js ?>','<?= $is_img ? 'image' : 'pdf' ?>')">
                                        <i data-lucide="<?= $is_img ? 'image' : 'file-text' ?>" size="14"></i>
                                        <span><?= $is_img ? 'Image' : 'PDF' ?></span>
                                    </button>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td><span class="badge badge-warning">Pending</span></td>
                            <td class="text-right px-30">
                                <div class="btn-group justify-end">
                                    <button class="action-btn action-btn-view" title="View Details"
                                        onclick="openLeaveDetailModal(<?= $lr['id'] ?>, '<?= htmlspecialchars($leave_user_name, ENT_QUOTES, 'UTF-8') ?>','<?= htmlspecialchars($lr['leave_type_name'], ENT_QUOTES, 'UTF-8') ?>',<?= $days ?>,'<?= date("d M Y", $start_ts) ?>','<?= date("d M Y", $end_ts) ?>','<?= $safe_admin_note_js ?>','<?= $doc_path_js ?>','<?= $is_img ? 'image' : 'pdf' ?>', 'Pending')"><i data-lucide="eye" size="14"></i></button>
                                    
                                </div>
                            </td>
                        </tr>
                    <?php 
                            }
                        } 
                        if (!$has_pending): 
                    ?>
                        <tr><td colspan="7" class="text-center py-20 text-light">No pending leave requests found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="p-24 flex-between border-top">
            <span class="font-13 text-light" id="pendingPaginationInfo">Showing 1 to 10 of 10 entries</span>
            <div class="flex-center gap-8" id="pendingPaginationControls">
                <button class="action-btn" id="pendingPrevPage"><i data-lucide="chevron-left" size="16"></i></button>
                <div id="pendingPageNumbers" class="flex-center gap-8">
                    <button class="action-btn btn-active">1</button>
                </div>
                <button class="action-btn" id="pendingNextPage"><i data-lucide="chevron-right" size="16"></i></button>
            </div>
        </div>
    </div>
</div>

<!-- Premium Leave Management Modal -->
<div class="modal-overlay" id="applyLeaveModal">
    <div class="modal-content premium">
        <div class="modal-header">
            <div>
                <h3>Leave Management</h3>
                <p class="font-12 text-light mt-1">Set company-wide days per year (Sick, Casual, Annual). Everyone gets
                    the same entitlement until you connect HR rules per employee.</p>
            </div>
            <button class="icon-btn js-modal-close"><i data-lucide="x"></i></button>
        </div>
        <div class="modal-body">
            <div class="leave-quota-company mb-0">
                <label class="admin-form-label admin-form-label--compact">Company leave days (per year)</label>
                <p class="font-12 text-light mb-20">These counts apply to <strong>all employees</strong> for annual
                    entitlement.</p>
                <div class="leave-quota-grid mb-16">
                    <div class="form-group mb-0">
                        <label class="admin-form-label admin-form-label--inner" for="leaveQuotaSick">Sick Leave</label>
                        <input type="number" id="leaveQuotaSick" class="form-control bg-white-input" min="0" max="365"
                            step="1" value="<?= $leaveQuotas['sick'] ?>">
                        <span class="font-11 text-light">days / year</span>
                    </div>
                    <div class="form-group mb-0">
                        <label class="admin-form-label admin-form-label--inner" for="leaveQuotaCasual">Casual
                            Leave</label>
                        <input type="number" id="leaveQuotaCasual" class="form-control bg-white-input" min="0" max="365"
                            step="1" value="<?= $leaveQuotas['casual'] ?>">
                        <span class="font-11 text-light">days / year</span>
                    </div>
                    <div class="form-group mb-0">
                        <label class="admin-form-label admin-form-label--inner" for="leaveQuotaAnnual">Annual
                            Leave</label>
                        <input type="number" id="leaveQuotaAnnual" class="form-control bg-white-input" min="0" max="365"
                            step="1" value="<?= $leaveQuotas['annual'] ?>">
                        <span class="font-11 text-light">days / year</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-primary" id="leaveQuotaSaveBtn">
                <i data-lucide="save"></i>
                <span>Save leave counts</span>
            </button>
        </div>
    </div>
</div>
<!-- Leave Detail Modal -->
<div class="modal-overlay" id="leaveDetailModal">
    <div class="modal-content premium leave-detail-modal">
        <div class="modal-header">
            <div class="flex-center gap-12">
                <div class="type-icon-box primary">
                    <i data-lucide="clipboard-list" size="20"></i>
                </div>
                <div>
                    <h3 class="font-18 font-700 m-0">Leave Details</h3>
                    <p class="font-12 text-light m-0">Leave request information</p>
                </div>
            </div>
            <button type="button" class="icon-btn js-modal-close" aria-label="Close"><i data-lucide="x"
                    size="20"></i></button>
        </div>
        <div class="modal-body">
            <div class="leave-detail-summary">
                <div class="leave-detail-summary-main">
                    <span class="leave-detail-emp-name" id="leaveDetailEmpName">—</span>
                    <span class="leave-detail-type-badge" id="leaveDetailLeaveType">—</span>
                </div>
                <div class="leave-detail-days" id="leaveDetailDays">—</div>
            </div>

            <div class="leave-detail-section">
                <h4 class="leave-detail-section-title">Duration</h4>
                <div class="leave-detail-grid leave-detail-dates">
                    <div class="detail-row">
                        <span class="detail-label">From Date</span>
                        <span class="detail-value" id="leaveDetailFromDate">—</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">To Date</span>
                        <span class="detail-value" id="leaveDetailToDate">—</span>
                    </div>
                </div>
            </div>

            <div class="leave-detail-section leave-detail-comment-section">
                <h4 class="leave-detail-section-title">Admin Comment</h4>
                <textarea id="leaveDetailAdminCommentInput" class="leave-detail-comment-input form-control" rows="3"
                    placeholder="Add or edit your comment (e.g. Approved, Rejected, or any note)..."></textarea>
            </div>
        </div>
        <div class="modal-footer leave-detail-footer" style="width: 100%; display: flex; justify-content: space-between; gap: 16px;">
            <button type="button" class="btn-primary leave-btn-approve" style="flex: 1; justify-content: center; display:none;" title="Approve">
                <i data-lucide="check" size="16"></i>
                <span>Approve</span>
            </button>
            <button type="button" class="btn-primary leave-btn-reject" style="flex: 1; justify-content: center; display:none;" title="Reject">
                <i data-lucide="x" size="16"></i>
                <span>Reject</span>
            </button>
            <button type="button" class="btn-primary leave-btn-update" style="flex: 1; justify-content: center; display:none;" title="Update Remarks">
                <i data-lucide="edit-2" size="16"></i>
                <span>Update</span>
            </button>
        </div>
    </div>
</div>


<!-- Leave Document Preview Modal -->
<div class="modal-overlay" id="leaveDocumentModal">
    <div class="modal-content wide-lg">
        <div class="modal-header">
            <div>
                <h3 class="font-16 font-700">Leave Document</h3>
                <p class="font-12 text-light m-0">Preview of the attachment uploaded with this leave request.</p>
            </div>
            <button type="button" class="icon-btn js-modal-close" aria-label="Close">
                <i data-lucide="x" size="18"></i>
            </button>
        </div>
        <div class="modal-body">
            <div class="document-preview-container">
                <img id="leaveDocImage" src="" alt="Leave Document" class="leave-doc-media hidden">
                <iframe id="leaveDocPdf" src="" class="leave-doc-media hidden" frameborder="0"></iframe>
            </div>
        </div>
    </div>
</div>

<script src="assets/js/leave-management.js"></script>
<?php include 'includes/footer.php'; ?>