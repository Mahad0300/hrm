// admin/assets/js/dashboard.js

document.addEventListener('DOMContentLoaded', function() {
    fetchOverviewStats();
    
    // Refresh every 5 minutes
    setInterval(fetchOverviewStats, 300000);
});

function fetchOverviewStats() {
    fetch('../includes/api/stats_handler.php?action=get_overview')
        .then(res => res.json())
        .then(res => {
            if (res.status === 'success') {
                updateStat('stat-total-employees', res.data.total_employees);
                updateStat('stat-present-today', res.data.present_today);
                updateStat('stat-pending-leaves', res.data.pending_leaves);
                updateStat('stat-active-jobs', res.data.active_jobs);
                
                // Secondary snaps
                updateSnap('snap-active-jobs', res.data.active_jobs);
            }
        })
        .catch(err => console.error('Dashboard Stats Error:', err));

    // Fetch Upcoming Events
    fetch('assets/api/calendar_handler.php?action=fetch')
        .then(res => res.json())
        .then(res => {
            if (res.status === 'success') {
                const today = new Date().toISOString().split('T')[0];
                const upcoming = res.data.filter(e => e.event_date >= today).length;
                updateStat('stat-upcoming-events', upcoming);
            }
        });
}

function updateStat(id, value) {
    const el = document.getElementById(id);
    if (!el) return;
    
    // Animate counter
    const current = parseInt(el.textContent.replace(/\D/g, '')) || 0;
    animateValue(el, current, value, 1000);
}

function updateSnap(id, value) {
    const el = document.getElementById(id);
    if (el) el.textContent = value;
}

function animateValue(obj, start, end, duration) {
    let startTimestamp = null;
    const step = (timestamp) => {
        if (!startTimestamp) startTimestamp = timestamp;
        const progress = Math.min((timestamp - startTimestamp) / duration, 1);
        obj.innerHTML = Math.floor(progress * (end - start) + start);
        if (progress < 1) {
            window.requestAnimationFrame(step);
        }
    };
    window.requestAnimationFrame(step);
}
