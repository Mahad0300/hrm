	</div> <!-- End content-body -->
</main>
</div> <!-- End admin-container -->

<!-- JS Scripts -->
<?php use App\Core\View; ?>
<script src="<?= View::asset('js/user/script.js') ?>"></script>
<script src="<?= View::asset('js/user/modals.js') ?>"></script>
<script>
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
</script>
<?php
// Ensure optional script flags are defined to avoid undefined variable notices
$load_charts_js = $load_charts_js ?? false;
$load_policy_management = $load_policy_management ?? false;
?>
<?php if (!empty($load_charts_js)): ?>
    <script src="<?= View::asset('js/shared/charts.js') ?>"></script>
<?php endif; ?>
<?php if (!empty($load_policy_management)): ?>
    <script src="<?= View::asset('js/admin/policy-management.js') ?>"></script>
<?php endif; ?>
