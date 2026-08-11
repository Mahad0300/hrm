<?php
use App\Core\View;
$page_title = "KPI Management";
$page_subtitle = "Track and manage employee performance indicators and custom criteria templates.";
include __DIR__ . '/../partials/admin/header.php';
?>
<?php include __DIR__ . '/../partials/admin/sidebar.php'; ?>

<style>
/* Fix form control text vertical alignment & cutoff */
#kpiMasterView select.form-control {
    height: 40px !important;
    padding: 0 32px 0 12px !important;
    font-size: 13px !important;
    line-height: normal !important;
    box-sizing: border-box !important;
    vertical-align: middle !important;
}
#kpiMasterView input.form-control {
    height: 40px !important;
    padding: 0 12px !important;
    font-size: 13px !important;
    line-height: normal !important;
    box-sizing: border-box !important;
}

/* Force table 100% full-width without horizontal scroll */
#kpiMasterView .table-responsive {
    overflow-x: hidden !important;
    width: 100% !important;
}

#kpiMasterView table.data-table {
    width: 100% !important;
    min-width: 100% !important;
    table-layout: fixed !important;
}
</style>

<!-- Master Dashboard View -->
<div id="kpiMasterView" class="mb-32">

    <!-- Top Action Bar -->
    <div class="flex-between align-center mb-24 flex-wrap gap-16" style="display:flex; justify-content:space-between; align-items:center;">
        <div>
            <h2 class="font-20 font-800 m-0 text-dark" style="font-size:20px; font-weight:800; color:#0f172a; margin:0;">Performance Evaluation Dashboard</h2>
            <p class="font-13 text-light m-0 mt-4" style="font-size:13px; color:#64748b; margin-top:4px;">Overview of employee performance ratings, scorecards, and role criteria.</p>
        </div>
        <div class="flex-center gap-12 flex-wrap" style="display:flex; align-items:center; gap:12px;">
            <a href="<?= View::url('kpi/templates') ?>" class="btn-ghost border py-10 px-20 font-13 font-600 flex-center gap-8 text-dark" style="border-radius:10px; border:1px solid #e2e8f0; background:#fff; display:inline-flex; align-items:center; gap:8px;">
                <i data-lucide="settings-2" size="16"></i>
                <span>Manage KPI Templates</span>
            </a>
            <a href="<?= View::url('kpi/evaluate') ?>" class="btn-primary py-10 px-20 font-13 font-600 flex-center gap-8" style="border-radius:10px; display:inline-flex; align-items:center; gap:8px;">
                <i data-lucide="plus-circle" size="16"></i>
                <span>Evaluate Employee</span>
            </a>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid-3 gap-24 mb-24" style="display:grid; grid-template-columns: repeat(3, 1fr); gap:24px; margin-bottom:24px;">
        <div class="card p-20 flex-center justify-start gap-16" style="background:#fff; border-radius:16px; border:1px solid #e2e8f0; display:flex; align-items:center; gap:16px; padding:20px;">
            <div class="icon-box-md success-light" style="width:48px; height:48px; border-radius:12px; background:#ecfdf5; display:flex; align-items:center; justify-content:center;">
                <i data-lucide="award" class="text-success" size="24" style="color:#10b981;"></i>
            </div>
            <div>
                <h4 class="font-13 text-light font-500 m-0" style="font-size:13px; color:#64748b; margin:0;">Average Score</h4>
                <p class="font-20 font-800 text-dark m-0 mt-4" id="statAvgScore" style="font-size:20px; font-weight:800; color:#0f172a; margin-top:4px;">-- / 100</p>
            </div>
        </div>
        <div class="card p-20 flex-center justify-start gap-16" style="background:#fff; border-radius:16px; border:1px solid #e2e8f0; display:flex; align-items:center; gap:16px; padding:20px;">
            <div class="icon-box-md primary-light" style="width:48px; height:48px; border-radius:12px; background:#f5f3ff; display:flex; align-items:center; justify-content:center;">
                <i data-lucide="users" class="text-primary" size="24" style="color:#6c4cf1;"></i>
            </div>
            <div>
                <h4 class="font-13 text-light font-500 m-0" style="font-size:13px; color:#64748b; margin:0;">Employees Rated</h4>
                <p class="font-20 font-800 text-dark m-0 mt-4" id="statRatedCount" style="font-size:20px; font-weight:800; color:#0f172a; margin-top:4px;">0 / 0</p>
            </div>
        </div>
        <div class="card p-20 flex-center justify-start gap-16" style="background:#fff; border-radius:16px; border:1px solid #e2e8f0; display:flex; align-items:center; gap:16px; padding:20px;">
            <div class="icon-box-md info-light" style="width:48px; height:48px; border-radius:12px; background:#f0f9ff; display:flex; align-items:center; justify-content:center;">
                <i data-lucide="trending-up" class="text-info" size="24" style="color:#0284c7;"></i>
            </div>
            <div>
                <h4 class="font-13 text-light font-500 m-0" style="font-size:13px; color:#64748b; margin:0;">Top Department</h4>
                <p class="font-20 font-800 text-dark m-0 mt-4" id="statTopDept" style="font-size:20px; font-weight:800; color:#0f172a; margin-top:4px;">---</p>
            </div>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="card p-20 mb-24" style="background:#fff; border-radius:16px; border:1px solid #e2e8f0; padding:20px;">
        <div class="filter-grid" id="kpiFilters" style="display:grid; grid-template-columns: 2.2fr 1fr 1fr 1fr; gap:16px; align-items:end;">
            <div class="filter-item">
                <label class="admin-form-label font-12 font-700 text-light mb-6" style="display:block; font-size:12px; font-weight:700; color:#64748b; margin-bottom:6px;">SEARCH EMPLOYEE</label>
                <div class="search-box w-full" style="position:relative; display:flex; align-items:center;">
                    <i data-lucide="search" size="16" style="position:absolute; left:12px; color:#94a3b8; pointer-events:none;"></i>
                    <input type="text" id="searchEmployee" class="form-control font-13 font-600" placeholder="Search employee name, ID or role..." style="width:100%; height:40px; border-radius:10px; padding-left:38px;">
                </div>
            </div>
            <div class="filter-item">
                <label class="admin-form-label font-12 font-700 text-light mb-6" style="display:block; font-size:12px; font-weight:700; color:#64748b; margin-bottom:6px;">DEPARTMENT</label>
                <select class="form-control font-13 font-600" id="filterDept">
                    <option value="">All Departments</option>
                </select>
            </div>
            <div class="filter-item">
                <label class="admin-form-label font-12 font-700 text-light mb-6" style="display:block; font-size:12px; font-weight:700; color:#64748b; margin-bottom:6px;">GRADE</label>
                <select class="form-control font-13 font-600" id="filterStatus">
                    <option value="">All Grades</option>
                    <option value="Grade A">Grade A (95%+)</option>
                    <option value="Grade B">Grade B (85–94%)</option>
                    <option value="Grade C">Grade C (&lt;85%)</option>
                </select>
            </div>
            <div class="filter-item">
                <label class="admin-form-label font-12 font-700 text-light mb-6" style="display:block; font-size:12px; font-weight:700; color:#64748b; margin-bottom:6px;">PERIOD</label>
                <input type="month" class="form-control font-13 font-600" id="filterMonth">
            </div>
        </div>
    </div>

    <!-- Table Top Controls -->
    <div class="flex-between mb-16 px-4" style="display:flex; justify-content:space-between; align-items:center;">
        <div class="flex-center gap-8" style="display:flex; align-items:center; gap:8px;">
            <span class="font-13 text-light" style="font-size:13px; color:#64748b;">Show</span>
            <select class="form-control font-13 font-600 per-page-select" id="perPageSelect" style="height:34px !important; line-height:normal !important; border-radius:8px; padding:0 8px !important;">
                <option value="5">5</option>
                <option value="10" selected>10</option>
                <option value="25">25</option>
                <option value="all">All</option>
            </select>
            <span class="font-13 text-light" style="font-size:13px; color:#64748b;">entries</span>
        </div>
        <div class="text-right">
            <span class="font-13 text-light" id="tableSummary" style="font-size:13px; color:#64748b;">Loading...</span>
        </div>
    </div>

    <!-- KPI Master Table -->
    <div class="card p-0" style="background:#fff; border-radius:16px; border:1px solid #e2e8f0; overflow:hidden;">
        <div class="table-responsive">
            <table class="data-table font-13" style="width:100%; border-collapse:collapse; table-layout:fixed;">
                <thead style="background:#f8fafc; border-bottom:1px solid #e2e8f0;">
                    <tr>
                        <th style="padding:14px 16px; text-align:left; font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase; width:26%;">EMPLOYEE</th>
                        <th style="padding:14px 16px; text-align:left; font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase; width:18%;">JOB TITLE & DEPT</th>
                        <th style="padding:14px 16px; text-align:center; font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase; width:13%;">OVERALL SCORE</th>
                        <th style="padding:14px 16px; text-align:center; font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase; width:13%;">GRADE</th>
                        <th style="padding:14px 16px; text-align:center; font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase; width:12%;">LAST REVIEW</th>
                        <th style="padding:14px 16px; text-align:center; font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase; width:18%;">ACTIONS</th>
                    </tr>
                </thead>
                <tbody id="kpiTableBody">
                    <!-- Dynamic Data Injected via kpi.js -->
                </tbody>
            </table>
        </div>
        <div class="p-16 flex-between border-top" style="display:flex; justify-content:space-between; align-items:center; padding:16px 20px; border-top:1px solid #e2e8f0;">
            <span class="font-13 text-light" id="paginationInfo" style="font-size:13px; color:#64748b;">Loading...</span>
            <div class="flex-center gap-8" id="paginationControls" style="display:flex; align-items:center; gap:8px;">
                <button class="action-btn" id="prevPage" disabled style="width:34px; height:34px; border-radius:8px; border:1px solid #e2e8f0; background:#fff;"><i data-lucide="chevron-left" size="16"></i></button>
                <div id="pageNumbers" class="flex-center gap-8" style="display:flex; align-items:center; gap:8px;">
                    <button class="action-btn btn-active" style="width:34px; height:34px; border-radius:8px; background:#6c4cf1; color:#fff; font-weight:700; border:none;">1</button>
                </div>
                <button class="action-btn" id="nextPage" disabled style="width:34px; height:34px; border-radius:8px; border:1px solid #e2e8f0; background:#fff;"><i data-lucide="chevron-right" size="16"></i></button>
            </div>
        </div>
    </div>
</div>

<script src="<?= View::asset('js/shared/kpi.js?v=9') ?>"></script>
<?php include __DIR__ . '/../partials/admin/footer.php'; ?>
