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
// Dynamic Tooltips (using title attribute)
    // Custom dropdown logic if needed
});

/**
 * Attendance & Global Utilities
 */

function showToast(msg, type = 'success') {
    if (typeof Toastify !== 'undefined') {
        Toastify({
            text: msg,
            duration: 3000,
            close: true,
            gravity: "top",
            position: "right",
            stopOnFocus: true,
            style: {
                background: type === 'success' ? "#28a745" : "#dc3545",
                borderRadius: "10px",
                boxShadow: "0 10px 15px -3px rgba(0, 0, 0, 0.1)"
            }
        }).showToast();
    } else {
        alert(msg);
    }
}

function handleCheckIn() {
    fetch('assets/api/attendance_handler.php?action=check_in')
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                showToast(data.message);
                setTimeout(() => location.reload(), 1500);
            } else {
                showToast(data.message, 'error');
            }
        })
        .catch(err => {
            console.error(err);
            showToast('Something went wrong.', 'error');
        });
}

function handleCheckOut() {
    performCheckOut();
}

function performCheckOut() {
    fetch('assets/api/attendance_handler.php?action=check_out')
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                showToast(data.message);
                setTimeout(() => location.reload(), 1500);
            } else {
                showToast(data.message, 'error');
            }
        })
        .catch(err => {
            console.error(err);
            showToast('Something went wrong.', 'error');
        });
}

// Global Polling for Attendance Status
setInterval(() => {
    fetch('assets/api/attendance_handler.php?action=get_status')
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                const inBtn = document.getElementById('checkInBtn');
                const outBtn = document.getElementById('checkOutBtn');
                const timer = document.getElementById('activeSessionTimer');
                const timeSpan = document.getElementById('checkInTime');
                
                // Topbar elements
                const topIn = document.getElementById('topbarCheckIn');
                const topOut = document.getElementById('topbarCheckOut');

                // Toggle Check In button
                if (data.can_check_in) {
                    inBtn?.classList.remove('hidden');
                    topIn?.classList.remove('hidden');
                } else {
                    inBtn?.classList.add('hidden');
                    topIn?.classList.add('hidden');
                }

                // Toggle Check Out button
                if (data.can_check_out) {
                    outBtn?.classList.remove('hidden');
                    topOut?.classList.remove('hidden');
                    timer?.classList.remove('hidden');
                    if (timeSpan) timeSpan.textContent = data.check_in_time;
                } else {
                    outBtn?.classList.add('hidden');
                    topOut?.classList.add('hidden');
                    timer?.classList.add('hidden');
                }
            }
        });
}, 30000);

