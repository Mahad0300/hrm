<?php
use App\Core\View;
use App\Core\Database;

$emp_id = $_GET['id'] ?? '';
$name_param = $_GET['name'] ?? $_GET['employee'] ?? '';

$pdo = $pdo ?? Database::connection();

// 1. If id is provided but name is not, redirect to canonical slug URL
if ($emp_id !== '' && $name_param === '') {
    try {
        $stmt = $pdo->prepare("SELECT first_name, middle_name, last_name FROM employees WHERE id = ? LIMIT 1");
        $stmt->execute([$emp_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $fullName = trim($row['first_name'] . ' ' . (!empty($row['middle_name']) ? $row['middle_name'] . ' ' : '') . $row['last_name']);
            $canonicalSlug = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $fullName), '-'));
            if ($canonicalSlug !== '') {
                header('Location: ' . View::url('kpi.report') . '?name=' . rawurlencode($canonicalSlug));
                exit;
            }
        }
    } catch (\Throwable $e) {}
}

// 2. If name is provided, resolve it to numeric emp_id
if ($name_param !== '') {
    $targetSlug = strtolower(trim($name_param));
    try {
        $stmt = $pdo->query("SELECT id, first_name, middle_name, last_name FROM employees");
        $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($employees as $row) {
            $fullName = trim($row['first_name'] . ' ' . (!empty($row['middle_name']) ? $row['middle_name'] . ' ' : '') . $row['last_name']);
            $rowSlug = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $fullName), '-'));
            if ($rowSlug === $targetSlug) {
                $emp_id = $row['id'];
                break;
            }
        }
    } catch (\Throwable $e) {}
}

$page_title = "Employee KPI Scorecard";
include __DIR__ . '/../partials/hr/header.php';
?>
<?php include __DIR__ . '/../partials/hr/sidebar.php'; ?>

<style>
/* Category Section Cards (Matching KPI System) */
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

<input type="hidden" id="currentEmpId" value="<?= htmlspecialchars($emp_id) ?>">

<div id="kpiReportPage" class="mb-32">
    <!-- Top Header -->
    <div class="flex-between align-center mb-24 flex-wrap gap-12" style="display:flex; justify-content:space-between; align-items:center;">
        <div class="flex-center gap-12" style="display:flex; align-items:center; gap:12px;">
            <a href="<?= View::url('kpi') ?>" class="action-btn border bg-white" title="Back to KPI Dashboard" style="display:inline-flex; align-items:center; justify-content:center; width:38px; height:38px; border-radius:10px;">
                <i data-lucide="arrow-left" size="18"></i>
            </a>
            <div>
                <h3 class="font-18 font-800 m-0 text-dark" style="font-size:18px; font-weight:800; color:#0f172a; margin:0;">Employee Performance Scorecard</h3>
                <p class="font-12 text-light m-0 mt-2" style="font-size:12px; color:#64748b; margin-top:2px;">Detailed performance indicators and historical evaluation history.</p>
            </div>
        </div>
        <div class="flex-center gap-12">
            <a href="#" id="addNewEvalBtn" class="btn-primary py-10 px-20 font-13 font-600 flex-center gap-8" style="border-radius:10px; display:inline-flex; align-items:center; gap:8px;">
                <i data-lucide="plus-circle" size="16"></i>
                <span>Add New Evaluation</span>
            </a>
        </div>
    </div>

    <div style="display:grid; grid-template-columns: 2.2fr 1fr; gap:24px; align-items:start;">
        
        <!-- Left: Detailed Scorecard -->
        <div style="display:flex; flex-direction:column; gap:20px;">
            
            <!-- Employee Profile Header Card -->
            <div class="card p-24" style="background:#fff; border-radius:16px; border:1px solid #e2e8f0; padding:24px;">
                <div class="flex-between align-center flex-wrap gap-16" style="display:flex; justify-content:space-between; align-items:center;">
                    <div style="display:flex; align-items:center; gap:16px;">
                        <img id="detailAvatar" src="<?= View::image('profile-image/default-avatar.svg') ?>"
                            style="width:72px; height:72px; border-radius:50%; object-fit:cover; border:3px solid #f1f5f9;" alt="Avatar">
                        <div>
                            <h3 class="font-20 font-800 text-dark m-0" id="detailName" style="font-size:20px; font-weight:800; color:#0f172a; margin:0;">Loading...</h3>
                            <p class="font-13 text-light m-0 mt-4" id="detailDept" style="font-size:13px; color:#64748b; margin-top:4px;">---</p>
                            <div style="display:flex; align-items:center; gap:10px; margin-top:8px;">
                                <span class="badge" id="detailStatus" style="font-size:11px; font-weight:700; padding:4px 12px; border-radius:20px;">---</span>
                                <span style="font-size:13px; font-weight:700; color:#64748b;">•</span>
                                <span class="font-16 font-800 text-primary-color" id="detailScore" style="font-size:16px; font-weight:800; color:#6c4cf1;">0%</span>
                            </div>
                        </div>
                    </div>
                    <div id="latestReviewMeta" style="text-align:right;">
                        <span class="font-11 text-light uppercase font-700" style="font-size:11px; font-weight:700; color:#64748b; display:block;">LAST EVALUATED</span>
                        <span class="font-14 font-700 text-dark" id="detailLastPeriod" style="font-size:14px; font-weight:700; color:#0f172a;">--</span>
                    </div>
                </div>

                <!-- Job Description Preview -->
                <div id="jobDescriptionBox" class="mt-16 p-14 rounded-12 bg-light border" style="display:none; background:#f8fafc; border-radius:12px; padding:14px; border:1px solid #e2e8f0;">
                    <span class="font-11 text-light uppercase ls-1 font-700" style="font-size:11px; font-weight:700; color:#64748b; display:block; mb-4;">JOB DESCRIPTION</span>
                    <p class="font-13 text-dark m-0" id="jobDescriptionText" style="white-space:pre-line; font-size:13px; color:#0f172a; margin:0;"></p>
                </div>
            </div>

            <!-- Scorecard Categories Container -->
            <div id="detailGoalsContainer">
                <div class="card p-35 text-center text-light" style="background:#fff; border-radius:16px; border:1px solid #e2e8f0; text-align:center; padding:35px; color:#64748b;">
                    Loading performance scorecard data...
                </div>
            </div>

        </div>

        <!-- Right: Evaluation History Timeline -->
        <div class="card p-24" style="background:#fff; border-radius:16px; border:1px solid #e2e8f0; padding:24px; position:sticky; top:24px;">
            <div class="flex-between align-center mb-16 pb-12 border-bottom" style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid #e2e8f0; padding-bottom:12px;">
                <h4 class="font-14 font-800 text-dark uppercase m-0 ls-05" style="font-size:14px; font-weight:800; color:#0f172a;">Evaluation History</h4>
            </div>
            <div id="feedbackTimeline" style="display:flex; flex-direction:column; gap:16px;">
                <p class="text-light italic font-13 text-center" style="font-size:13px; color:#94a3b8; font-style:italic;">Loading history...</p>
            </div>
        </div>

    </div>
</div>

<script src="<?= View::asset('js/admin/kpi-report.js?v=6') ?>"></script>
<?php include __DIR__ . '/../partials/hr/footer.php'; ?>
