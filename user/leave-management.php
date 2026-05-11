<?php
$page_title = "Leave History";
$page_subtitle = "Your past and current leave requests in one place.";
include 'includes/header.php';

require_once '../includes/db_connect.php';

$emp_id = $_SESSION['user_id'];

// Get Employee info
$stmt = $pdo->prepare("SELECT first_name, middle_name, last_name, profile_pic FROM employees WHERE id = ?");
$stmt->execute([$emp_id]);
$emp_data = $stmt->fetch();
$middle_name_part = !empty($emp_data['middle_name']) ? $emp_data['middle_name'] . ' ' : '';
$leave_user_name = trim($emp_data['first_name'] . ' ' . $middle_name_part . $emp_data['last_name']);
$leave_user_id = 'EMP-0' . $emp_id;
$leave_user_avatar = !empty($emp_data['profile_pic']) ? '../' . $emp_data['profile_pic'] : '../images/profile-image/default-avatar.svg';

// Get Leave Requests for this user
$stmt_l = $pdo->prepare("
    SELECT lr.*, lt.name as leave_type_name
    FROM leave_requests lr
    LEFT JOIN leave_types lt ON lr.leave_type_id = lt.id
    WHERE lr.employee_id = ?
    ORDER BY lr.applied_at DESC
");
$stmt_l->execute([$emp_id]);
$leave_requests = $stmt_l->fetchAll(PDO::FETCH_ASSOC);
?>
<?php include 'includes/sidebar.php'; ?>

<div class="page-action-area">
    <button type="button" class="btn-primary" onclick="openApplyLeaveModal()">
        <i data-lucide="plus-circle" size="18"></i>
        <span>Apply leave</span>
    </button>
</div>

<!-- Filters Card -->
<div class="card p-24 mb-24">
    <div class="filter-grid">
        <div class="search-box">
            <i data-lucide="search" size="18"></i>
            <input type="text" placeholder="Search by leave type or reason...">
        </div>
        <div class="filter-item">
            <select class="form-control">
                <option value="">Leave Type (All)</option>
                <option value="sick">Sick Leave</option>
                <option value="casual">Casual Leave</option>
                <option value="annual">Annual Leave</option>
            </select>
        </div>
        <div class="filter-item">
            <select class="form-control">
                <option value="">Status (All)</option>
                <option value="pending">Pending</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
            </select>
        </div>
        <div class="filter-item">
            <input type="month" class="form-control" value="<?= date('Y-m') ?>">
        </div>
    </div>
</div>



<!-- Leave history (single user) -->
<div class="leave-history-main">
    
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
                    <?php if (!empty($leave_requests)): ?>
                        <?php foreach ($leave_requests as $lr): ?>
                            <?php
                            $start_ts = strtotime($lr['start_date']);
                            $end_ts = strtotime($lr['end_date']);
                            $days = (int)round(($end_ts - $start_ts) / (60 * 60 * 24)) + 1;
                            $date_range = date("M d", $start_ts) . ' - ' . date("M d", $end_ts) . " ($days days)";
                            
                            $badge_class = 'badge-warning';
                            if ($lr['status'] === 'Approved') $badge_class = 'badge-success';
                            if ($lr['status'] === 'Rejected') $badge_class = 'badge-danger';

                            // Handle specific character encodings properly for reasons
                            $safe_reason = htmlspecialchars($lr['reason'], ENT_QUOTES, 'UTF-8');
                            $display_reason = mb_strlen($lr['reason']) > 35 ? mb_substr($lr['reason'], 0, 35) . '...' : $lr['reason'];
                            $safe_display_reason = htmlspecialchars($display_reason, ENT_QUOTES, 'UTF-8');
                            
                            $safe_reason_js = htmlspecialchars(str_replace(["\r", "\n", "'", '"'], [" ", " ", "\\'", "\\\""], $lr['reason']), ENT_QUOTES, 'UTF-8');
                            $safe_admin_note_js = htmlspecialchars(str_replace(["\r", "\n", "'", '"'], [" ", " ", "\\'", "\\\""], $lr['admin_note'] ?? ''), ENT_QUOTES, 'UTF-8');
                            ?>
                            <tr data-id="<?= $lr['id'] ?>" data-leave-type="<?= htmlspecialchars($lr['leave_type_name']) ?>" data-date-from="<?= $lr['start_date'] ?>" data-date-to="<?= $lr['end_date'] ?>" data-reason="<?= $safe_reason ?>" data-document-path="<?= htmlspecialchars($lr['document_path'] ?? '', ENT_QUOTES) ?>">
                                <td>
                                    <div class="emp-profile">
                                        <img src="<?= htmlspecialchars($leave_user_avatar, ENT_QUOTES, 'UTF-8') ?>" class="emp-avatar" alt="">
                                        <div class="emp-info">
                                            <span class="name"><?= htmlspecialchars($leave_user_name, ENT_QUOTES, 'UTF-8') ?></span>
                                            <span class="email"><?= htmlspecialchars($leave_user_id, ENT_QUOTES, 'UTF-8') ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($lr['leave_type_name']) ?></td>
                                <td><?= $date_range ?></td>
                                <td class="allow-wrap" title="<?= $safe_reason ?>"><?= $safe_display_reason ?></td>
                                <td>
                                    <?php if (!empty($lr['document_path'])): ?>
                                        <?php 
                                        $doc_ext = strtolower(pathinfo($lr['document_path'], PATHINFO_EXTENSION));
                                        $is_img = in_array($doc_ext, ['jpg', 'jpeg', 'png']);
                                        ?>
                                        <button type="button" class="doc-pill <?= $is_img ? 'doc-pill-image' : 'doc-pill-pdf' ?>" title="View Document"
                                            onclick="openLeaveDocument('../<?= htmlspecialchars($lr['document_path']) ?>','<?= $is_img ? 'image' : 'pdf' ?>')">
                                            <i data-lucide="<?= $is_img ? 'image' : 'file-text' ?>" size="14"></i>
                                            <span><?= $is_img ? 'Image' : 'PDF' ?></span>
                                        </button>
                                    <?php else: ?>
                                        <span class="text-light">-</span>
                                    <?php endif; ?>
                                </td>
                                <td><span class="badge <?= $badge_class ?>"><?= htmlspecialchars($lr['status']) ?></span></td>
                                <td class="text-right px-30">
                                    <div class="btn-group justify-end">
                                        <?php if ($lr['status'] === 'Pending'): ?>
                                            <button type="button" class="action-btn action-btn-edit" title="Edit request" onclick="openEditLeaveModal(this)"><i data-lucide="edit-2" size="14"></i></button>
                                        <?php endif; ?>
                                        <button type="button" class="action-btn action-btn-view" title="View details" onclick="openLeaveDetailModal(
                                            '<?= htmlspecialchars($leave_user_name, ENT_QUOTES) ?>',
                                            '<?= htmlspecialchars($lr['leave_type_name'], ENT_QUOTES) ?>',
                                            <?= $days ?>,
                                            '<?= date("d M Y", $start_ts) ?>',
                                            '<?= date("d M Y", $end_ts) ?>',
                                            '<?= $safe_reason_js ?>',
                                            '<?= $safe_admin_note_js ?>',
                                            '<?= !empty($lr['document_path']) ? '../' . $lr['document_path'] : '' ?>',
                                            '<?= (!empty($lr['document_path']) && isset($is_img) && $is_img) ? 'image' : 'pdf' ?>'
                                            )">
                                            <i data-lucide="eye" size="14"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="text-center p-20 text-light italic">No leave requests found.</td></tr>
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

<!-- Apply Leave Modal -->
<div class="modal-overlay" id="applyLeaveModal">
    <div class="modal-content premium wide-md">
        <div class="modal-header">
            <div>
                <h3 class="font-18 font-700 m-0">Apply for leave</h3>
                <p class="font-12 text-light m-0">Submit a new leave request</p>
        </div>
            <button type="button" class="icon-btn js-modal-close" aria-label="Close"><i data-lucide="x" size="20"></i></button>
        </div>
        <form id="applyLeaveForm">
            <div class="modal-body p-30">
                <div class="form-grid-2 gap-20">
                    <div class="form-group col-span-2">
                        <label class="admin-form-label" for="applyLeaveType">Leave type</label>
                        <select class="form-control bg-white-input" id="applyLeaveType" name="leave_type" required>
                            <option value="">Select leave type</option>
                            <option value="Sick Leave">Sick Leave</option>
                            <option value="Casual Leave">Casual Leave</option>
                            <option value="Annual Leave">Annual Leave</option>
                        </select>
    </div>
                    <div class="form-group">
                        <label class="admin-form-label" for="applyLeaveFrom">From date</label>
                        <input type="date" class="form-control bg-white-input" id="applyLeaveFrom" name="date_from" required>
        </div>
                    <div class="form-group">
                        <label class="admin-form-label" for="applyLeaveTo">To date</label>
                        <input type="date" class="form-control bg-white-input" id="applyLeaveTo" name="date_to" required>
    </div>
                    <div class="form-group col-span-2">
                        <label class="admin-form-label" for="applyLeaveReason">Reason</label>
                        <textarea class="form-control bg-white-input" id="applyLeaveReason" name="reason" rows="3" required placeholder="Briefly describe why you need leave"></textarea>
                                </div>
                    <div class="form-group col-span-2">
                        <label class="admin-form-label" for="applyLeaveDocument">Document (optional)</label>
                        <div class="custom-file-upload">
                            <label class="file-upload-wrapper" id="applyLeaveFileWrapper" for="applyLeaveDocument">
                                <i data-lucide="upload-cloud" size="18"></i>
                                <span class="file-upload-label">Choose file</span>
                                <span class="file-upload-info">PDF, JPG, PNG</span>
                            </label>
                            <input type="file" class="hidden-file-input" id="applyLeaveDocument" name="document" accept=".pdf,.png,.jpg,.jpeg,application/pdf,image/*">
                            </div>
                        <p class="font-12 text-light m-0 mt-6" id="applyLeaveFileHint">No file chosen</p>
        </div>
                </div>
            </div>
            <div class="modal-footer flex-between flex-wrap gap-12">
                <button type="button" class="btn-ghost js-modal-close">
                    <i data-lucide="x" size="16"></i>
                    Cancel
                </button>
                <button type="submit" class="btn-primary">
                    <i data-lucide="send" size="16"></i>
                    <span>Submit request</span>
                </button>
        </div>
        </form>
    </div>
</div>

<!-- Edit Leave Modal -->
<div class="modal-overlay" id="editLeaveModal">
    <div class="modal-content premium wide-md">
        <div class="modal-header">
            <div>
                <h3 class="font-18 font-700 m-0">Edit leave request</h3>
                <p class="font-12 text-light m-0">Update your pending request before it is reviewed</p>
            </div>
            <button type="button" class="icon-btn js-modal-close" aria-label="Close"><i data-lucide="x" size="20"></i></button>
        </div>
        <form id="editLeaveForm">
            <div class="modal-body p-30">
                <input type="hidden" id="editLeaveId" name="leave_id">
                <input type="hidden" name="action" value="edit">
                <div class="form-grid-2 gap-20">
                    <div class="form-group col-span-2">
                        <label class="admin-form-label" for="editLeaveType">Leave type</label>
                        <select class="form-control bg-white-input" id="editLeaveType" name="leave_type" required>
                            <option value="">Select leave type</option>
                            <option value="Sick Leave">Sick Leave</option>
                            <option value="Casual Leave">Casual Leave</option>
                            <option value="Annual Leave">Annual Leave</option>
                        </select>
            </div>
                    <div class="form-group">
                        <label class="admin-form-label" for="editLeaveFrom">From date</label>
                        <input type="date" class="form-control bg-white-input" id="editLeaveFrom" name="date_from" required>
                    </div>
                    <div class="form-group">
                        <label class="admin-form-label" for="editLeaveTo">To date</label>
                        <input type="date" class="form-control bg-white-input" id="editLeaveTo" name="date_to" required>
                    </div>
                    <div class="form-group col-span-2">
                        <label class="admin-form-label" for="editLeaveReason">Reason</label>
                        <textarea class="form-control bg-white-input" id="editLeaveReason" name="reason" rows="3" required placeholder="Briefly describe why you need leave"></textarea>
                    </div>
                    <div class="form-group col-span-2">
                        <label class="admin-form-label" for="editLeaveDocument">Document (optional)</label>
                        <div class="custom-file-upload">
                            <label class="file-upload-wrapper" id="editLeaveFileWrapper" for="editLeaveDocument">
                                <i data-lucide="upload-cloud" size="18" id="editLeaveFileIcon"></i>
                                <span class="file-upload-label" id="editLeaveFileLabel">Choose file</span>
                                <span class="file-upload-info" id="editLeaveFileInfo">Replace or keep existing</span>
                            </label>
                            <input type="file" class="hidden-file-input" id="editLeaveDocument" name="document" accept=".pdf,.png,.jpg,.jpeg,application/pdf,image/*">
                        </div>
                        <p class="font-12 text-light m-0 mt-6" id="editLeaveFileHint">Replace attachment, or leave empty to keep the current file</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer flex-between flex-wrap gap-12">
                <button type="button" class="btn-ghost js-modal-close">
                    <i data-lucide="x" size="16"></i>
                    Cancel
                </button>
                <button type="submit" class="btn-primary">
                    <i data-lucide="save" size="16"></i>
                    <span>Save changes</span>
                </button>
        </div>
        </form>
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
            <button type="button" class="icon-btn js-modal-close" aria-label="Close"><i data-lucide="x" size="20"></i></button>
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

            <div class="leave-detail-section">
                <h4 class="leave-detail-section-title">Reason</h4>
                <p class="font-14 text-dark m-0 leave-detail-reason" id="leaveDetailReason">—</p>
            </div>

            <div class="leave-detail-section leave-detail-comment-section">
                <h4 class="leave-detail-section-title">Remarks</h4>
                <p class="font-14 text-dark m-0 leave-detail-remarks" id="leaveDetailRemarks">—</p>
            </div>
        </div>
        <div class="modal-footer leave-detail-footer flex-between flex-wrap gap-12">
            <button type="button" class="btn-ghost js-modal-close">
                    <i data-lucide="x" size="16"></i>
                Cancel
                </button>
            <button type="button" class="btn-primary" onclick="leaveDetailViewDocument()">
                <i data-lucide="file-text" size="16"></i>
                <span>View attachment</span>
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
