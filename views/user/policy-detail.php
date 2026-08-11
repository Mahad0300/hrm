<?php
use App\Core\View;
$page_title = "Policy";
$load_policies_user = true;
include __DIR__ . '/../partials/user/header.php';
?>
<?php include __DIR__ . '/../partials/user/sidebar.php'; ?>

<div class="policy-detail-page">
    <a href="<?= View::url('policies') ?>" class="policy-detail-back">
        <i data-lucide="arrow-left" width="18" height="18" aria-hidden="true"></i>
        <span>Back to policies</span>
    </a>

    <p class="policy-detail-loading" id="policyDetailLoading">Loading…</p>
    <p class="policy-detail-missing" id="policyDetailMissing" style="display:none;">This policy could not be found. It may have been removed.</p>

    <article class="policy-detail-card card p-24" id="policyDetailContent" style="display:none;"></article>
</div>

<?php include __DIR__ . '/../partials/user/footer.php'; ?>

