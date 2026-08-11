<?php
use App\Core\View;
$page_title = "Configure KPI Templates";
$page_subtitle = "Set up custom evaluation questions, target weights (%), and descriptions per Job Role or Employee.";
include __DIR__ . '/../partials/admin/header.php';
?>
<?php include __DIR__ . '/../partials/admin/sidebar.php'; ?>

<style>
/* Searchable Autocomplete Suggestions */
.search-suggestions-container {
    position: relative;
    width: 100%;
}
.search-suggestions-dropdown {
    position: absolute;
    top: calc(100% + 6px);
    left: 0;
    right: 0;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.1);
    max-height: 250px;
    overflow-y: auto;
    z-index: 1000;
    display: none;
}
.search-suggestion-item {
    padding: 10px 16px;
    font-size: 13px;
    font-weight: 600;
    color: #1e293b;
    cursor: pointer;
    border-bottom: 1px solid #f8fafc;
    display: flex;
    align-items: center;
    justify-content: space-between;
    transition: all 0.15s ease;
}
.search-suggestion-item:last-child {
    border-bottom: none;
}
.search-suggestion-item:hover, .search-suggestion-item.active {
    background: #f8fafc;
    color: #6c4cf1;
}

/* Premium Rounded Pill Scope Switch */
.scope-toggle-container {
    display: inline-flex;
    align-items: center;
    background: #f1f5f9;
    border: 1px solid #e2e8f0;
    border-radius: 30px;
    padding: 4px;
    gap: 4px;
}
.scope-toggle-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    height: 36px;
    padding: 0 20px;
    border-radius: 24px;
    font-size: 13px;
    font-weight: 600;
    color: #64748b;
    background: transparent;
    border: none;
    cursor: pointer;
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    line-height: 1;
    white-space: nowrap;
}
.scope-toggle-btn.active {
    background: #6c4cf1;
    color: #ffffff !important;
    font-weight: 700;
    box-shadow: 0 4px 12px rgba(108, 76, 241, 0.25);
}
.scope-toggle-btn.active i {
    color: #ffffff !important;
}

/* Category Section Card Styling (HRM Theme) */
.tpl-category-section {
    background: #ffffff;
    border-radius: 14px;
    border: 1px solid #e2e8f0;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0,0,0,0.02);
}
.tpl-category-header {
    background: #f8fafc;
    border-left: 4px solid #6c4cf1;
    padding: 14px 20px;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
</style>

<div id="kpiTemplatesPage" class="mb-32">

    <!-- Top Action & Back Bar -->
    <div class="flex-between align-center mb-20 flex-wrap gap-12" style="display:flex; justify-content:space-between; align-items:center;">
        <div class="flex-center gap-12" style="display:flex; align-items:center; gap:12px;">
            <a href="<?= View::url('kpi') ?>" class="action-btn border bg-white" title="Back to KPI Dashboard" style="display:inline-flex; align-items:center; justify-content:center; width:38px; height:38px; border-radius:10px;">
                <i data-lucide="arrow-left" size="18"></i>
            </a>
            <div>
                <h3 class="font-18 font-800 m-0 text-dark" style="font-size:18px; font-weight:800; color:#0f172a; margin:0;">Job Role Evaluation Templates</h3>
                <p class="font-12 text-light m-0 mt-2" style="font-size:12px; color:#64748b; margin-top:2px;">Configure evaluation questions and target weights per Job Role.</p>
            </div>
        </div>
        <div>
            <button type="button" class="btn-ghost border bg-white text-primary font-12 font-700 px-16 py-8 flex-center gap-6" onclick="loadDefaultHrPreset()" style="border-radius:10px; display:inline-flex; align-items:center; gap:6px;">
                <i data-lucide="sparkles" size="14"></i>
                <span>Load HR Executive Template (100%)</span>
            </button>
        </div>
    </div>

    <form id="kpiTemplateForm">
        
        <!-- Step 1: Target Scope Search (Minimal Single Line Header) -->
        <div class="card p-20 mb-20" style="background:#fff; border-radius:16px; border:1px solid #e2e8f0;">
            <div class="flex-between align-center flex-wrap gap-16" style="display:flex; justify-content:space-between; align-items:center;">
                <!-- Search Input Bar -->
                <div style="flex:1; min-width:280px; max-width:580px;">
                    <div class="search-suggestions-container">
                        <div style="position:relative; display:flex; align-items:center;">
                            <i data-lucide="search" size="18" style="position:absolute; left:14px; color:#94a3b8; pointer-events:none;"></i>
                            <input type="text" class="form-control font-14 font-600" id="targetSearchInput" placeholder="Search Job Role (e.g. HR Executive, Web Developer)..." autocomplete="off" style="width:100%; height:44px; border-radius:10px; padding-left:42px; padding-right:36px;">
                            <i data-lucide="chevron-down" size="18" style="position:absolute; right:14px; color:#94a3b8; pointer-events:none;"></i>
                        </div>
                        <div class="search-suggestions-dropdown custom-scrollbar" id="targetSuggestions"></div>
                    </div>
                    <input type="hidden" id="templateJobTitleSelect" value="">
                    <input type="hidden" id="templateEmployeeSelect" value="">
                </div>

                <!-- Premium Pill Scope Switch Toggle -->
                <div class="scope-toggle-container">
                    <button type="button" class="scope-toggle-btn active" id="tabJobRole" onclick="switchScopeMode('job_role')">
                        <i data-lucide="briefcase" size="14"></i>
                        <span>By Job Role</span>
                    </button>
                    <button type="button" class="scope-toggle-btn" id="tabEmployee" onclick="switchScopeMode('employee')">
                        <i data-lucide="user" size="14"></i>
                        <span>By Employee</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Target Weight Summary Bar -->
        <div class="card p-16 mb-20 border flex-between align-center flex-wrap gap-16" id="templateWeightBar" style="background:#fff; border-radius:14px; border:1px solid #e2e8f0; display:flex; justify-content:space-between; align-items:center; padding:16px 20px;">
            <div style="display:flex; align-items:center; gap:10px;">
                <div style="width:36px; height:36px; border-radius:8px; background:#f1f5f9; display:flex; align-items:center; justify-content:center;">
                    <i data-lucide="calculator" class="text-primary" size="18"></i>
                </div>
                <span class="font-14 font-700 text-dark" style="font-size:14px; font-weight:700; color:#0f172a;">Target Weight Sum:</span>
                <span class="font-20 font-800 text-primary-color" id="templateTotalWeightDisplay" style="font-size:20px; font-weight:800; color:#6c4cf1;">0.00%</span>
                <span class="font-12 text-light" style="font-size:12px; color:#64748b;">(Must equal 100% total)</span>
            </div>
            <div id="templateWeightBadge">
                <span class="badge badge-warning font-12 px-14 py-6" style="padding:6px 14px; border-radius:20px; font-size:12px; font-weight:700; display:inline-flex; align-items:center; gap:6px;">
                    <i data-lucide="alert-triangle" size="14"></i> Incomplete (Needs 100%)
                </span>
            </div>
        </div>

        <!-- Step 2: Evaluation Categories & Sub-Criteria Questions -->
        <div class="mb-20">
            <div class="flex-between align-center mb-16" style="display:flex; justify-content:space-between; align-items:center;">
                <h4 class="font-15 font-800 text-dark m-0 uppercase ls-05" style="font-size:15px; font-weight:800; color:#0f172a;">Evaluation Categories & Questions</h4>
                <button type="button" class="btn-ghost text-primary font-12 font-700 border bg-white px-16 py-8" onclick="addCustomCategorySection()" style="border-radius:8px; display:inline-flex; align-items:center; gap:6px;">
                    <i data-lucide="folder-plus" size="14"></i> Add Category Section
                </button>
            </div>

            <!-- Category Section Cards Injected via kpi-templates.js -->
            <div id="categoriesContainer" class="flex-column gap-20"></div>
        </div>

        <!-- Bottom Action Bar -->
        <div class="flex-between align-center p-20 card flex-wrap gap-16 bg-white border" style="background:#fff; border-radius:14px; border:1px solid #e2e8f0; display:flex; justify-content:space-between; align-items:center; padding:20px;">
            <a href="<?= View::url('kpi') ?>" class="btn-ghost font-13 font-600" style="color:#64748b;">Cancel</a>
            <button type="submit" class="btn-primary px-32 py-12 font-700 flex-center gap-8" id="saveTemplateBtn" style="border-radius:10px; display:inline-flex; align-items:center; gap:8px;">
                <i data-lucide="check-circle" size="18"></i>
                <span>Save Criteria Template</span>
            </button>
        </div>
    </form>
</div>

<script src="<?= View::asset('js/admin/kpi-templates.js?v=9') ?>"></script>
<?php include __DIR__ . '/../partials/admin/footer.php'; ?>
