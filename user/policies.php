<?php
$page_title = "Company Policies";
$page_subtitle = "Read official company policies. Click a card to open the full document.";
$load_policies_user = true;
include 'includes/header.php';
?>
<?php include 'includes/sidebar.php'; ?>

<p class="font-12 text-light mb-15 policy-user-empty-hint" id="policyUserEmptyHint" style="display:none;">No policies are available right now.</p>

<div class="policy-tiles-grid" id="policyTilesRoot" aria-live="polite"></div>

<?php include 'includes/footer.php'; ?>
