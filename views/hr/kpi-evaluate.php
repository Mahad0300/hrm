<?php
use App\Core\View;
$page_title = "Evaluate Employee Performance";
$page_subtitle = "Submit dynamic performance ratings based on employee job criteria.";
include __DIR__ . '/../partials/hr/header.php';

$emp_id_param = $_GET['id'] ?? $_GET['employee_id'] ?? '';
$review_id_param = $_GET['review_id'] ?? '';
?>
<?php include __DIR__ . '/../partials/hr/sidebar.php'; ?>

<style>
/* Hide Chrome/Edge/Safari number input spinners for clean score display */
input[type=number]::-webkit-inner-spin-button, 
input[type=number]::-webkit-outer-spin-button { 
    -webkit-appearance: none;
    margin: 0; 
}
input[type=number] {
    -moz-appearance: textfield;
}

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
    padding: 0 18px;
    border-radius: 24px;
    font-size: 13px;
    font-weight: 600;
    color: #64748b;
    background: transparent;
    border: none;
    cursor: pointer;
    transition: all 0.25s ease;
    line-height: 1;
    white-space: nowrap;
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

<input type="hidden" id="paramEmpId" value="<?= htmlspecialchars($emp_id_param) ?>">
<input type="hidden" id="paramReviewId" value="<?= htmlspecialchars($review_id_param) ?>">

<div id="kpiEvaluatePage" class="mb-32">

    <!-- Top Action Bar -->
    <div class="flex-between align-center mb-20 flex-wrap gap-12" style="display:flex; justify-content:space-between; align-items:center;">
        <div class="flex-center gap-12" style="display:flex; align-items:center; gap:12px;">
            <a href="<?= View::url('kpi') ?>" class="action-btn border bg-white" title="Back to KPI Dashboard" style="display:inline-flex; align-items:center; justify-content:center; width:38px; height:38px; border-radius:10px;">
                <i data-lucide="arrow-left" size="18"></i>
            </a>
            <div>
                <h3 class="font-18 font-800 m-0 text-dark" id="evaluatePageTitle" style="font-size:18px; font-weight:800; color:#0f172a; margin:0;">Employee Performance Evaluation</h3>
                <p class="font-12 text-light m-0 mt-2" style="font-size:12px; color:#64748b; margin-top:2px;">Submit points scored for job evaluation questions.</p>
            </div>
        </div>
    </div>

    <form id="addReviewForm">
        <input type="hidden" name="review_id" id="reviewIdInput" value="<?= htmlspecialchars($review_id_param) ?>">

        <!-- Step 1: Target Scope Search & Evaluation Period -->
        <div class="card p-20 mb-20" style="background:#fff; border-radius:16px; border:1px solid #e2e8f0;">
            <div class="flex-between align-center flex-wrap gap-16" style="display:flex; justify-content:space-between; align-items:center;">
                
                <!-- Searchable Input for Employee -->
                <div style="flex:1; min-width:280px; max-width:540px;">
                    <div class="search-suggestions-container">
                        <div style="position:relative; display:flex; align-items:center;">
                            <i data-lucide="search" size="18" style="position:absolute; left:14px; color:#94a3b8; pointer-events:none;"></i>
                            <input type="text" class="form-control font-14 font-600" id="employeeSearchInput" placeholder="Search Employee Name or ID..." autocomplete="off" style="width:100%; height:44px; border-radius:10px; padding-left:42px; padding-right:36px;">
                            <i data-lucide="chevron-down" size="18" style="position:absolute; right:14px; color:#94a3b8; pointer-events:none;"></i>
                        </div>
                        <div class="search-suggestions-dropdown custom-scrollbar" id="employeeSuggestions"></div>
                    </div>
                    <input type="hidden" id="modalEmployeeSelect" name="employee_id" value="">
                </div>

                <!-- Month Picker & Period Toggle -->
                <div style="display:flex; align-items:center; gap:12px; flex-wrap:wrap;">
                    <div style="position:relative; display:flex; align-items:center;">
                        <i data-lucide="calendar" size="16" style="position:absolute; left:12px; color:#64748b; pointer-events:none;"></i>
                        <input type="month" class="form-control font-13 font-600" name="period_month" id="reviewPeriodMonth" value="<?= date('Y-m') ?>" style="height:40px; border-radius:10px; padding-left:36px; padding-right:12px;">
                    </div>

                    <!-- Period Switch Toggle -->
                    <div class="scope-toggle-container">
                        <button type="button" class="scope-toggle-btn active" onclick="selectPeriod(this,'Monthly')">
                            <span>Monthly</span>
                        </button>
                        <button type="button" class="scope-toggle-btn" onclick="selectPeriod(this,'Quarterly')">
                            <span>Quarterly</span>
                        </button>
                        <button type="button" class="scope-toggle-btn" onclick="selectPeriod(this,'Annual')">
                            <span>Annual</span>
                        </button>
                    </div>
                    <input type="hidden" name="period" id="reviewPeriodInput" value="Monthly">
                </div>

            </div>

            <!-- Job Description Preview Box -->
            <div id="jobDescBox" class="p-16 rounded-12 bg-light border mt-16" style="display:none; background:#f8fafc; border-radius:12px; padding:14px 18px;">
                <div class="flex-between mb-4" style="display:flex; justify-content:space-between; align-items:center;">
                    <span class="font-11 text-light uppercase ls-1 font-700" id="jobDescRoleLabel" style="font-size:11px; font-weight:700; color:#64748b;">JOB DESCRIPTION</span>
                    <a href="#" id="editRoleTemplateLink" class="btn-ghost font-11 text-primary py-2 px-8" style="font-size:11px; font-weight:700; color:#6c4cf1;">
                        <i data-lucide="edit-3" size="12" class="mr-4"></i> Edit Role Template Questions
                    </a>
                </div>
                <p class="font-13 text-dark m-0" id="jobDescText" style="white-space:pre-line; font-size:13px; color:#0f172a; margin:0;"></p>
            </div>
        </div>

        <!-- Step 2: Evaluation Category Cards Section -->
        <div class="mb-20">
            <div class="flex-between align-center mb-16" style="display:flex; justify-content:space-between; align-items:center;">
                <h4 class="font-15 font-800 text-dark m-0 uppercase ls-05" style="font-size:15px; font-weight:800; color:#0f172a;">Evaluation Questions & Scoring</h4>
            </div>

            <div id="dynamicCriteriaContainer">
                <div class="card p-35 text-center text-light" style="background:#fff; border-radius:16px; border:1px solid #e2e8f0; text-align:center; padding:35px; color:#64748b;">
                    Please search and select an employee above to load their evaluation criteria questions.
                </div>
            </div>
        </div>

        <!-- Step 3: Overall Score Summary & Grade Preview Card -->
        <div class="card p-20 mb-20 border" style="background:#fff; border-radius:16px; border:1px solid #e2e8f0; padding:20px;">
            <div class="flex-between align-center flex-wrap gap-16" style="display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <span class="font-12 text-light uppercase ls-1 mb-4" style="font-size:12px; color:#64748b; font-weight:700; display:block;">Overall Scorecard Total</span>
                    <span class="font-28 font-800 m-0 text-dark" id="overallScoreDisplay" style="font-size:28px; font-weight:800; color:#0f172a; margin:0;">0 <span class="font-16 font-400 text-light" style="font-size:16px; font-weight:400; color:#64748b;">/ 100</span></span>
                </div>
                <div style="display:flex; align-items:center; gap:20px;">
                    <div style="text-align:right;">
                        <span class="font-12 text-light uppercase ls-1 mb-4" style="font-size:12px; color:#64748b; font-weight:700; display:block;">Performance Grade</span>
                        <span class="badge font-14 font-800 px-20 py-8" id="overallGradeDisplay" style="padding:8px 20px; border-radius:20px; font-size:14px; font-weight:800;">--</span>
                    </div>
                    <button type="submit" class="btn-primary px-32 py-12 font-700 flex-center gap-8" id="submitReviewBtn" style="border-radius:10px; display:inline-flex; align-items:center; gap:8px;">
                        <i data-lucide="check-circle" size="18"></i>
                        <span>Submit Performance Evaluation</span>
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script src="<?= View::asset('js/admin/kpi-evaluate.js?v=10') ?>"></script>
<?php include __DIR__ . '/../partials/hr/footer.php'; ?>
