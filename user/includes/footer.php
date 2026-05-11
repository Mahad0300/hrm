    </div> <!-- End content-body -->
</main>
</div> <!-- End admin-container -->

<!-- JS Scripts -->
<script src="assets/js/script.js"></script>
<script src="assets/js/modals.js"></script>
<?php if (!empty($load_charts_js)): ?>
<script src="assets/js/charts.js"></script>
<?php endif; ?>
<?php if (!empty($load_policies_user)): ?>
<script src="assets/js/policies-user.js"></script>
<?php endif; ?>
<script>
    // Initialize Lucide icons
    lucide.createIcons();
</script>
</body>
</html>
