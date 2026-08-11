<?php
use App\Core\View;
$page_title = "Candidate Pool";
$page_subtitle = "Review and manage all applicants for active job postings.";
include __DIR__ . '/../partials/hr/header.php';
?>
<?php include __DIR__ . '/../partials/hr/sidebar.php'; ?>

<div class="job-list-toolbar mb-24">
    <div class="job-list-toolbar__actions">
        <div class="job-list-toolbar__search-wrap"></div>
        <a href="<?= View::to('recruitment.interviews')?>" class="btn-primary job-list-toolbar__create job-candidates-toolbar__interviews">
            <i data-lucide="calendar"></i>
            <span>View Interviews</span>
        </a>
    </div>
</div>

<div class="card p-24 mb-24">
    <div class="filter-grid">
        <div class="filter-item">
            <label class="admin-form-label font-12">Search Candidates</label>
            <div class="search-box w-full">
                <i data-lucide="search" size="16"></i>
                <input type="text" id="candidateSearch" class="form-control"
                    placeholder="Search by name or job title...">
            </div>
        </div>
        <div class="filter-item">
            <label class="admin-form-label font-12">Department</label>
            <select id="filterDept" class="form-control">
                <option value="">All Departments</option>
            </select>
        </div>
        <div class="filter-item">
            <label class="admin-form-label font-12">Status</label>
            <select id="filterStatus" class="form-control">
                <option value="">All Status</option>
                <option>New</option>
                <option>Shortlisted</option>
                <option>Interview</option>
                <option>Offer</option>
                <option>Hired</option>
                <option>Rejected</option>
                <option>Duplicated</option>
                <option>Banned</option>
            </select>
        </div>
        <div class="filter-item">
            <label class="admin-form-label font-12">Sort By</label>
            <select id="sortBy" class="form-control">
                <option>Sort by: Newest</option>
                <option>Sort by: Oldest</option>
                <option>Sort by: Match Score</option>
            </select>
        </div>
    </div>
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
        <span class="font-13 text-light" id="tableSummary">Showing 0 entries</span>
    </div>
</div>

<div class="card p-0 overflow-hidden">
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 22%;">CANDIDATE NAME</th>
                    <th style="width: 22%;">JOB APPLIED FOR</th>
                    <th style="width: 16%;">DEPARTMENT</th>
                    <th style="width: 14%;">APPLIED DATE</th>
                    <th style="width: 16%;">STATUS</th>
                    <th class="text-right px-30" style="width: 10%;">ACTIONS</th>
                </tr>
            </thead>
            <tbody id="candidateTableBody">
                <!-- Candidate rows will be injected by JS -->
            </tbody>
        </table>
    </div>
    <!-- Pagination Footer INSIDE Card -->
    <div class="p-24 flex-between border-top">
        <span class="font-13 text-light" id="paginationInfo">Showing 0 entries</span>
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

<!-- Interview Scheduling Modal -->
<div class="modal-overlay" id="interviewModal">
    <div class="modal-content premium wide-sm">
        <div class="modal-header">
            <div>
                <h3>Schedule Interview</h3>
                <p class="font-12 text-light mt-1">Set up interview details for the candidate</p>
            </div>
            <button class="icon-btn" onclick="closeModal('interviewModal')"><i data-lucide="x"></i></button>
        </div>
        <div class="modal-body p-30">
            <form id="interviewForm">
                <input type="hidden" id="interviewCandId">
                <div class="form-group mb-20">
                    <label class="admin-form-label">Interview Type *</label>
                    <select id="interviewType" class="form-control bg-white-input">
                        <option value="Onsite">Onsite Interview</option>
                        <option value="Online">Online Interview</option>
                    </select>
                </div>
                <div class="form-group mb-20">
                    <label class="admin-form-label">Interview Date *</label>
                    <input type="date" id="interviewDate" class="form-control bg-white-input" required>
                </div>
                <div class="form-group mb-20">
                    <label class="admin-form-label">Interview Time *</label>
                    <input type="time" id="interviewTime" class="form-control bg-white-input" required>
                </div>
                <div class="form-group mb-0 flex-center gap-8 justify-content-start">
                    <input type="checkbox" id="interviewSendEmail" class="form-checkbox" checked style="width: 16px; height: 16px; accent-color: var(--primary);">
                    <label for="interviewSendEmail" class="font-13 cursor-pointer m-0">Send email notification to candidate</label>
                </div>
            </form>
        </div>
        <div class="modal-footer flex-end gap-12 p-30 border-top-0">
            <button type="submit" form="interviewForm" class="btn-primary px-30">Save Schedule</button>
        </div>
    </div>
</div>

<script src="<?= \App\Core\View::asset('js/hr/job-management.js') ?>"></script>
<?php include __DIR__ . '/../partials/hr/footer.php'; ?>
