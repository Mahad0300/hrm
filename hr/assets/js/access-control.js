document.addEventListener('DOMContentLoaded', () => {
    const saveBtn = document.getElementById('savePermissions');
    const viewToggles = document.querySelectorAll('.view-toggle');

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

    saveBtn?.addEventListener('click', () => {
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<i data-lucide="loader-2" class="spin" size="16"></i> <span>Saving...</span>';
        lucide.createIcons();

        setTimeout(() => {
            showToast('Access control settings updated successfully!', 'success');
            saveBtn.disabled = false;
            saveBtn.innerHTML = '<i data-lucide="save" size="16"></i> <span>Save Changes</span>';
            lucide.createIcons();
        }, 1200);
    });

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

        setTimeout(() => toast.classList.add('active'), 10);

        setTimeout(() => {
            toast.classList.remove('active');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }
});
