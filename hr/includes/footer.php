    </div> <!-- End content-body -->
</main>
</div> <!-- End admin-container -->

<!-- JS Scripts -->
<script src="assets/js/script.js"></script>
<script src="assets/js/modals.js"></script>
<script src="assets/js/hr-permissions-ui.js"></script>
<script src="assets/js/hr-permission-guard.js"></script>
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

    // SweetAlert when redirected from a blocked page (not on no-access index landing)
    (function () {
        if (typeof Swal === 'undefined') return;
        const cfg = window.HRM_CONFIG || {};
        if (cfg.hr_no_portal_access) return;

        const params = new URLSearchParams(window.location.search);
        if (params.get('access_denied') === '1') {
            Swal.fire({
                icon: 'warning',
                title: 'Access Not Allowed',
                text: 'You do not have permission to view this page. Please contact your Admin.',
                confirmButtonText: 'OK',
                confirmButtonColor: '#6c4cf1',
                allowOutsideClick: true,
                allowEscapeKey: true,
            });
            params.delete('access_denied');
            params.delete('access_revoked');
            const qs = params.toString();
            window.history.replaceState({}, '', window.location.pathname + (qs ? '?' + qs : ''));
        }
    })();
</script>
</body>
</html>
