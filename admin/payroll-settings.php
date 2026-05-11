<?php
$page_title = "Payroll Settings";
$page_subtitle = "Configure global payroll cycle and dates.";
include 'includes/header.php';
require_once '../includes/payroll_config.php';
?>
<?php include 'includes/sidebar.php'; ?>

<div class="row justify-center mt-30">
    <div class="col-md-6">
        <div class="card p-0 overflow-hidden">
            <div class="card-header-v2 p-24 border-bottom">
                <div class="flex-center gap-12">
                    <div class="type-icon-box primary">
                        <i data-lucide="settings-2" size="20"></i>
                    </div>
                    <div>
                        <h3 class="font-18 font-700 m-0">Payroll Cycle Configuration</h3>
                        <p class="font-12 text-light m-0">Define the start and end days for your monthly payroll.</p>
                    </div>
                </div>
            </div>
            
            <form id="payrollSettingsForm" class="p-24">
                <div class="highlight-box bg-primary-light border-primary-light mb-24">
                    <div class="flex-center gap-12">
                        <i data-lucide="info" class="text-primary-color mt-4" size="20"></i>
                        <p class="font-13 text-dark leading-relaxed">
                            Currently, your payroll cycle is set from the <strong><?= PAYROLL_START_DAY ?>th</strong> of the previous month to the <strong><?= PAYROLL_END_DAY ?>th</strong> of the current month.
                        </p>
                    </div>
                </div>

                <div class="grid-2 gap-24">
                    <div class="form-group">
                        <label class="admin-form-label">Payroll Start Day</label>
                        <div class="input-with-icon">
                            <i data-lucide="calendar-days" class="input-icon"></i>
                            <input type="number" name="start_day" class="form-control bg-white-input pl-45" 
                                   value="<?= PAYROLL_START_DAY ?>" min="1" max="31" required>
                        </div>
                        <small class="text-light font-11 mt-4 block">e.g., 21 for 21st of previous month</small>
                    </div>

                    <div class="form-group">
                        <label class="admin-form-label">Payroll End Day</label>
                        <div class="input-with-icon">
                            <i data-lucide="calendar-check" class="input-icon"></i>
                            <input type="number" name="end_day" class="form-control bg-white-input pl-45" 
                                   value="<?= PAYROLL_END_DAY ?>" min="1" max="31" required>
                        </div>
                        <small class="text-light font-11 mt-4 block">e.g., 20 for 20th of current month</small>
                    </div>
                </div>

                <div class="mt-30 border-top pt-24 text-right">
                    <button type="submit" class="btn btn-primary px-40 btn-premium-lg">
                        <i data-lucide="save" size="18"></i>
                        <span>Save Settings</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('payrollSettingsForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = {
        start_day: formData.get('start_day'),
        end_day: formData.get('end_day')
    };

    const submitBtn = this.querySelector('button[type="submit"]');
    const originalContent = submitBtn.innerHTML;
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i data-lucide="loader-2" class="spin" size="18"></i><span>Saving...</span>';
    if(window.lucide) lucide.createIcons();

    fetch('assets/api/settings_handler.php?action=save_payroll_cycle', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
    .then(res => res.json())
    .then(res => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalContent;
        if(window.lucide) lucide.createIcons();

        if(res.status === 'success') {
            Toastify({
                text: res.message,
                duration: 3000,
                gravity: "top",
                position: "right",
                style: { background: "#10b981" }
            }).showToast();
            
            // Reload after short delay to apply changes globally
            setTimeout(() => location.reload(), 1500);
        } else {
            Toastify({
                text: res.message,
                duration: 3000,
                gravity: "top",
                position: "right",
                style: { background: "#ef4444" }
            }).showToast();
        }
    })
    .catch(err => {
        console.error(err);
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalContent;
    });
});
</script>

<?php include 'includes/footer.php'; ?>
