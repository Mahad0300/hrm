	</div> <!-- End content-body -->
</main>
</div> <!-- End admin-container -->

<!-- JS Scripts -->
<?php use App\Core\View; ?>
<script src="<?= View::asset('js/user/script.js') ?>"></script>
<script src="<?= View::asset('js/user/modals.js') ?>"></script>
<?php if (!empty($load_charts_js)): ?>
<script src="<?= View::asset('js/user/charts.js') ?>"></script>
<?php endif; ?>
<?php if (!empty($load_policies_user)): ?>
<script src="<?= View::asset('js/user/policies-user.js') ?>"></script>
<?php endif; ?>
<?php if (!empty($load_kpi_user)): ?>
<script src="<?= View::asset('js/user/kpi-user.js') ?>"></script>
<?php endif; ?>
<script>
	// Initialize Lucide icons
	lucide.createIcons();
</script>
</body>
</html>

