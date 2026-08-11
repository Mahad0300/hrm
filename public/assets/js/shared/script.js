// HRM Dashboard Utility Scripts

document.addEventListener('DOMContentLoaded', () => {
    // Sidebar Toggle for Mobile
    const menuToggle = document.getElementById('menuToggle');
    const sidebar = document.getElementById('sidebar');
    
    if (menuToggle && sidebar) {
        menuToggle.addEventListener('click', () => {
            sidebar.classList.toggle('active');
        });
    }

    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', (e) => {
        if (window.innerWidth <= 1024) {
            if (!sidebar.contains(e.target) && !menuToggle.contains(e.target) && sidebar.classList.contains('active')) {
                sidebar.classList.remove('active');
            }
        }
    });

    // Dynamic Tooltips (using title attribute)
    // Custom dropdown logic if needed
});

/**
 * Global Toast Notification Helper
 */
function showToast(message, type = 'success') {
    if (typeof Toastify !== 'undefined') {
        const bg = type === 'success' 
            ? 'linear-gradient(to right, #00b09b, #96c93d)' 
            : 'linear-gradient(to right, #ff5f6d, #ffc371)';
            
        Toastify({
            text: message,
            duration: 3000,
            close: true,
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
    } else if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: type === 'success' ? 'success' : 'error',
            title: type === 'success' ? 'Success' : 'Error',
            text: message,
            confirmButtonColor: '#6c4cf1',
            timer: 2000
        });
    } else {
        alert(message);
    }
}
