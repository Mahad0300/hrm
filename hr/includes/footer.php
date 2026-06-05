    </div> <!-- End content-body -->
</main>
</div> <!-- End admin-container -->

<!-- JS Scripts -->
<script src="assets/js/script.js"></script>
<script src="assets/js/modals.js"></script>
<?php if (!empty($load_charts_js)): ?>
<script src="assets/js/charts.js"></script>
<?php endif; ?>
<?php if (!empty($load_policy_management)): ?>
<script src="assets/js/policy-management.js"></script>
<?php endif; ?>
<!-- Toastify-js JS -->
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>

<script>
    // Global Toast Notification Helper
    function showToast(message, type = 'success') {
        const bg = type === 'success' 
            ? 'linear-gradient(to right, #00b09b, #96c93d)' 
            : 'linear-gradient(to right, #ff5f6d, #ffc371)';
            
        Toastify({
            text: message,
            duration: 3000,
            gravity: "top", 
            position: "right",
            stopOnFocus: true,
            style: {
                background: bg,
                borderRadius: "10px",
                boxShadow: "0 10px 15px -3px rgba(0, 0, 0, 0.1)",
                fontFamily: "'Inter', sans-serif",
                fontSize: "14px"
            }
        }).showToast();
    }

    // Initialize Lucide icons
    lucide.createIcons();
</script>
</body>
</html>
