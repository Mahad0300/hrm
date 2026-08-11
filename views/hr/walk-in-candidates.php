<?php
use App\Core\View;
$page_title = "Walk-In Candidates Pool";
$page_subtitle = "Review and manage all applicants who registered via walk-in interviews.";
include __DIR__ . '/../partials/hr/header.php';
?>
<?php include __DIR__ . '/../partials/hr/sidebar.php'; ?>

<div class="job-list-toolbar mb-24" style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
    <div class="total-walkins-pill" style="display: inline-flex; align-items: center; gap: 10px; padding: 0 14px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; height: 40px; font-weight: 600; font-size: 13.5px; color: #334155; box-shadow: 0 1px 2px rgba(0,0,0,0.03);">
        <div style="background: #4f46e5; color: #ffffff; width: 24px; height: 24px; border-radius: 6px; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <i data-lucide="users" style="width: 13px !important; height: 13px !important; stroke: #ffffff;"></i>
        </div>
        <span>Total Walk-Ins</span>
        <span id="totalWalkInsCount" style="background: #eef2ff; color: #4f46e5; border: 1px solid #c7d2fe; padding: 1px 8px; border-radius: 12px; font-size: 12px; font-weight: 700;">0</span>
    </div>
    <div class="flex-center gap-12">
        <button type="button" class="btn-light job-list-toolbar__create" onclick="copyWalkInLink()">
            <i data-lucide="copy"></i>
            <span>Copy Public Link</span>
        </button>
        <a href="<?= View::url('recruitment.interviews')?>" class="btn-primary job-list-toolbar__create job-candidates-toolbar__interviews">
            <i data-lucide="calendar"></i>
            <span>View Interviews</span>
        </a>
    </div>
</div>

<div class="card p-24 mb-24">
    <div class="filter-grid">
        <div class="filter-item">
            <label class="admin-form-label font-12">Search Walk-In Candidates</label>
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
                <option value="newest">Sort by: Newest</option>
                <option value="oldest">Sort by: Oldest</option>
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
                    <th style="width: 25%;">CANDIDATE NAME</th>
                    <th style="width: 25%;">JOB APPLIED FOR</th>
                    <th style="width: 20%;">DEPARTMENT</th>
                    <th style="width: 15%;">STATUS</th>
                    <th class="text-center" style="width: 15%;">ACTIONS</th>
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

<script>
function copyWalkInLink() {
    const link = '<?= \App\Core\View::url('walk-in') ?>';
    function showSuccess() {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'success',
                title: 'Link Copied!',
                text: 'Public Walk-In form link has been copied to clipboard.',
                timer: 1800,
                showConfirmButton: false
            });
        } else {
            alert('Public Walk-In link copied to clipboard!');
        }
    }
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(link).then(showSuccess).catch(err => fallbackCopy());
    } else {
        fallbackCopy();
    }
    function fallbackCopy() {
        const textarea = document.createElement('textarea');
        textarea.value = link;
        document.body.appendChild(textarea);
        textarea.select();
        try { document.execCommand('copy'); } catch(e) {}
        document.body.removeChild(textarea);
        showSuccess();
    }
}
</script>
<script src="<?= \App\Core\View::asset('js/hr/walk-in-management.js') ?>"></script>
<?php include __DIR__ . '/../partials/hr/footer.php'; ?>
