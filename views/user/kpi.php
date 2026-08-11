<?php
use App\Core\View;
$page_title = "My KPI Result";
$page_subtitle = "View your personal performance evaluation scores and goal results.";
$load_kpi_user = true;
include __DIR__ . '/../partials/user/header.php';
?>
<?php include __DIR__ . '/../partials/user/sidebar.php'; ?>

<div class="user-kpi-container mb-30">
    <div class="card mb-20 p-20" style="border-radius: 14px; border: 1px solid #e2e8f0; background: #fff;">
        <div class="flex-between flex-wrap gap-15">
            <div>
                <h3 class="font-18 font-700 m-0">My Performance Evaluation</h3>
                <p class="font-12 text-light m-0 mt-2">Personal KPI score, attendance ratio, and goal achievement results.</p>
            </div>
            <div class="flex-center gap-10" style="display: none;">
                <span class="font-13 font-600 text-light">Period:</span>
                <select id="myKpiMonthSelect" class="per-page-select font-13 font-600" style="height: 38px; padding: 0 15px; border-radius: 8px; border: 1px solid #cbd5e1; cursor: pointer; background: #f8fafc;">
                    <option value="">Latest Period</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Main Dynamic Content Root -->
    <div id="myKpiRoot">
        <div class="card p-30 text-center" style="border-radius: 16px; background: #fff;">
            <div class="loader-ripple" style="margin: 20px auto;"><div></div><div></div></div>
            <p class="font-13 text-light m-0">Loading your performance results…</p>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../partials/user/footer.php'; ?>
