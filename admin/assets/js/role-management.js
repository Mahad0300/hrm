document.addEventListener('DOMContentLoaded', () => {
    const roleSelector = document.getElementById('roleSelector');
    const saveBtn = document.getElementById('savePermissions');
    const viewToggles = document.querySelectorAll('.view-toggle');
    const actionChecks = document.querySelectorAll('.action-check');

    // Handle View Toggles (disabling actions if view is off)
    viewToggles.forEach(toggle => {
        toggle.addEventListener('change', function() {
            const row = this.closest('tr');
            const rowActions = row.querySelectorAll('.action-check');
            
            if (!this.checked) {
                rowActions.forEach(check => {
                    check.checked = false;
                    check.disabled = true;
                });
            } else {
                rowActions.forEach(check => {
                    check.disabled = false;
                });
            }
        });
    });

    // Handle Role Switching
    roleSelector.addEventListener('change', function() {
        const role = this.value;
        console.log(`Switching to role: ${role}`);
        
        // Mocking role-specific data loading
        showToast(`Loading permissions for ${this.options[this.selectedIndex].text}...`, 'info');
        
        // Reset or modify checkboxes based on role (mock logic)
        setTimeout(() => {
            if (role === 'employee') {
                document.querySelectorAll('.view-toggle').forEach((t, i) => {
                    if (i > 1) t.checked = false; // Disable most for employees
                    t.dispatchEvent(new Event('change'));
                });
            } else {
                document.querySelectorAll('.view-toggle').forEach(t => {
                    t.checked = true;
                    t.dispatchEvent(new Event('change'));
                });
            }
        }, 500);
    });

    // Save Logic
    saveBtn.addEventListener('click', () => {
        const roleName = roleSelector.options[roleSelector.selectedIndex].text;
        
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<i data-lucide="loader-2" class="spin" size="16"></i> <span>Saving...</span>';
        lucide.createIcons();

        // Mock Save Delay
        setTimeout(() => {
            showToast(`Permissions for <b>${roleName}</b> updated successfully!`, 'success');
            saveBtn.disabled = false;
            saveBtn.innerHTML = '<i data-lucide="save" size="16"></i> <span>Save Changes</span>';
            lucide.createIcons();
        }, 1200);
    });

    // Helper: Toast Notification
    function showToast(message, type = 'success') {
        const toast = document.createElement('div');
        toast.className = `custom-toast ${type}`;
        toast.innerHTML = `
            <div class="toast-content">
                <i data-lucide="${type === 'success' ? 'check-circle' : 'info'}" size="18"></i>
                <span>${message}</span>
            </div>
        `;
        
        document.body.appendChild(toast);
        lucide.createIcons();

        // Trigger animation
        setTimeout(() => toast.classList.add('active'), 10);

        // Remove after 3 seconds
        setTimeout(() => {
            toast.classList.remove('active');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }
});
