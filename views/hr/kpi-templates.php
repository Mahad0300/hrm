<?php
use App\Core\View;
$page_title = "KPI Criteria Template Builder";
$page_subtitle = "Configure job role evaluation categories, criteria questions, and scoring weights.";
include __DIR__ . '/../partials/hr/header.php';
?>
<?php include __DIR__ . '/../partials/hr/sidebar.php'; ?>

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

/* Scope Toggle Buttons */
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
    gap: 6px;
    height: 36px;
    padding: 0 20px;
    border-radius: 24px;
    font-size: 13px;
    font-weight: 600;
    color: #64748b;
    background: transparent;
    border: none;
    cursor: pointer;
    transition: all 0.25s ease;
    line-height: 1;
}
.scope-toggle-btn.active {
    background: #6c4cf1;
    color: #ffffff !important;
    font-weight: 700;
    box-shadow: 0 4px 12px rgba(108, 76, 241, 0.25);
}

/* Category Section Cards (HRM Theme) */
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

    <!-- Top Action Bar -->
    <div class="flex-between align-center mb-20 flex-wrap gap-12" style="display:flex; justify-content:space-between; align-items:center;">
        <div class="flex-center gap-12" style="display:flex; align-items:center; gap:12px;">
            <a href="<?= View::url('kpi') ?>" class="action-btn border bg-white" title="Back to KPI Dashboard" style="display:inline-flex; align-items:center; justify-content:center; width:38px; height:38px; border-radius:10px;">
                <i data-lucide="arrow-left" size="18"></i>
            </a>
            <div>
                <h3 class="font-18 font-800 m-0 text-dark" style="font-size:18px; font-weight:800; color:#0f172a; margin:0;">KPI Criteria Template Builder</h3>
                <p class="font-12 text-light m-0 mt-2" style="font-size:12px; color:#64748b; margin-top:2px;">Define evaluation criteria, weight percentages, and guidelines per job role or employee.</p>
            </div>
        </div>
        <div class="flex-center gap-12">
            <a href="<?= View::url('kpi/evaluate') ?>" class="btn-primary py-10 px-20 font-13 font-600 flex-center gap-8" style="border-radius:10px; display:inline-flex; align-items:center; gap:8px;">
                <i data-lucide="check-circle" size="16"></i>
                <span>Evaluate Employee</span>
            </a>
        </div>
    </div>

    <!-- Step 1: Target Scope Search & Mode Toggle Card -->
    <div class="card p-20 mb-20" style="background:#fff; border-radius:16px; border:1px solid #e2e8f0;">
        <div class="flex-between align-center flex-wrap gap-16" style="display:flex; justify-content:space-between; align-items:center;">
            
            <!-- Searchable Suggestions Autocomplete Input -->
            <div style="flex:1; min-width:280px; max-width:540px;">
                <div class="search-suggestions-container">
                    <div style="position:relative; display:flex; align-items:center;">
                        <i data-lucide="search" size="18" style="position:absolute; left:14px; color:#94a3b8; pointer-events:none;"></i>
                        <input type="text" class="form-control font-14 font-600" id="targetSearchInput" placeholder="Search Job Title..." autocomplete="off" style="width:100%; height:44px; border-radius:10px; padding-left:42px; padding-right:36px;">
                        <i data-lucide="chevron-down" size="18" style="position:absolute; right:14px; color:#94a3b8; pointer-events:none;"></i>
                    </div>
                    <div class="search-suggestions-dropdown custom-scrollbar" id="targetSuggestions"></div>
                </div>
                <input type="hidden" id="targetSelectedValue" value="">
            </div>

            <!-- Scope Switch Toggle Buttons -->
            <div class="scope-toggle-container">
                <button type="button" class="scope-toggle-btn active" id="btnScopeRole" onclick="switchScopeMode('role')">
                    <i data-lucide="briefcase" size="15"></i>
                    <span>By Job Role</span>
                </button>
                <button type="button" class="scope-toggle-btn" id="btnScopeEmp" onclick="switchScopeMode('employee')">
                    <i data-lucide="user" size="15"></i>
                    <span>By Employee</span>
                </button>
            </div>

        </div>

        <!-- Role Description Preview Box -->
        <div id="roleDescriptionBox" class="mt-16 p-14 rounded-12 bg-light border" style="display:none; background:#f8fafc; border-radius:12px; padding:14px; border:1px solid #e2e8f0;">
            <div class="flex-between align-center mb-4" style="display:flex; justify-content:space-between; align-items:center;">
                <span class="font-11 text-light uppercase ls-1 font-700" style="font-size:11px; font-weight:700; color:#64748b;">JOB DESCRIPTION</span>
                <span class="font-11 text-primary font-600" id="roleDeptBadge" style="font-size:11px; font-weight:600; color:#6c4cf1;"></span>
            </div>
            <p class="font-13 text-dark m-0" id="roleDescriptionText" style="white-space:pre-line; font-size:13px; color:#0f172a; margin:0;"></p>
        </div>
    </div>

    <!-- Step 2: Category Section Cards Template Editor -->
    <form id="templateEditorForm">
        <div class="mb-20">
            <div class="flex-between align-center mb-16" style="display:flex; justify-content:space-between; align-items:center;">
                <h4 class="font-15 font-800 text-dark m-0 uppercase ls-05" style="font-size:15px; font-weight:800; color:#0f172a;">Evaluation Category Sections</h4>
                <div class="flex-center gap-12" style="display:flex; align-items:center; gap:12px;">
                    <span class="font-13 text-light font-600" style="font-size:13px; color:#64748b;">Target Weight Sum:</span>
                    <span class="badge font-14 font-800 px-14 py-6" id="totalWeightBadge" style="padding:6px 14px; border-radius:20px; font-size:14px; font-weight:800;">0%</span>
                </div>
            </div>

            <!-- Dynamic Category Cards render here -->
            <div id="categoryCardsContainer">
                <div class="card p-35 text-center text-light" style="background:#fff; border-radius:16px; border:1px solid #e2e8f0; text-align:center; padding:35px; color:#64748b;">
                    Please search and select a job role or employee above to load/configure criteria template.
                </div>
            </div>
        </div>

        <!-- Step 3: Save Changes Action Bar -->
        <div class="card p-16 border flex-between align-center flex-wrap gap-12" style="background:#fff; border-radius:16px; border:1px solid #e2e8f0; display:flex; justify-content:space-between; align-items:center; padding:16px 20px;">
            <div class="flex-center gap-8" style="display:flex; align-items:center; gap:8px;">
                <i data-lucide="info" size="18" style="color:#6c4cf1;"></i>
                <span class="font-13 text-light font-500" style="font-size:13px; color:#64748b;">Ensure target weights across categories total exactly <strong>100%</strong> before saving.</span>
            </div>
            <button type="submit" class="btn-primary px-28 py-10 font-700 flex-center gap-8" id="saveTemplateBtn" style="border-radius:10px; display:inline-flex; align-items:center; gap:8px;">
                <i data-lucide="save" size="16"></i>
                <span>Save Template Criteria</span>
            </button>
        </div>
    </form>
</div>

<script src="<?= View::asset('js/admin/kpi-templates.js?v=9') ?>"></script>
<?php include __DIR__ . '/../partials/hr/footer.php'; ?>
