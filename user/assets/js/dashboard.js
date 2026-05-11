// user/assets/js/dashboard.js

document.addEventListener('DOMContentLoaded', function() {
    fetchPersonalStats();
    
    // Refresh every 5 minutes
    setInterval(fetchPersonalStats, 300000);
});

function fetchPersonalStats() {
    fetch('../includes/api/stats_handler.php?action=get_personal_overview')
        .then(res => res.json())
        .then(res => {
            if (res.status === 'success') {
                const d = res.data;
                
                // Update Stats
                updateStat('stat-leave-balance', d.leave_balance + ' Days');
                updateStat('stat-dept-employees-count', d.dept_employees + ' employees in your department');
            }
        })
        .catch(err => console.error('Personal Stats Error:', err));

    // Fetch Announcements & Notifications
    fetch('../includes/api/stats_handler.php?action=get_announcements_notifications')
        .then(res => res.json())
        .then(res => {
            if (res.status === 'success') {
                const d = res.data;
                
                // Update Unread Count
                updateStat('stat-unread-notifications', d.unread_count);

                // 1. Render Announcements
                const annContainer = document.querySelector('.udash-grid--secondary .udash-panel:nth-child(1) .udash-feed');
                if (annContainer) {
                    if (d.announcements.length === 0) {
                        annContainer.innerHTML = '<div class="p-20 text-light italic font-13 text-center">No recent announcements.</div>';
                    } else {
                        annContainer.innerHTML = d.announcements.map(ann => `
                            <a href="announcements.php" class="udash-feed__item">
                                <span class="udash-feed__body">
                                    <span class="udash-feed__title"><span class="udash-feed__emoji" aria-hidden="true">📢</span>${ann.title}</span>
                                    <span class="udash-feed__meta">
                                        <span class="udash-feed__badge ${ann.category === 'Holiday' ? 'udash-feed__badge--holiday' : 'udash-feed__badge--update'}">
                                            ${ann.category.toUpperCase()}
                                        </span> 
                                        · ${formatFullDate(ann.event_date)}
                                    </span>
                                </span>
                            </a>
                        `).join('');
                    }
                }

                // 2. Render Notifications
                const notContainer = document.querySelector('.udash-grid--secondary .udash-panel:nth-child(2) .udash-feed');
                if (notContainer) {
                    if (d.notifications.length === 0) {
                        notContainer.innerHTML = '<div class="p-20 text-light italic font-13 text-center">No new notifications.</div>';
                    } else {
                        notContainer.innerHTML = d.notifications.map(n => `
                            <a href="notifications.php" class="udash-feed__item">
                                <span class="udash-feed__body">
                                    <span class="udash-feed__title"><span class="udash-feed__emoji" aria-hidden="true">🔔</span>${n.title}</span>
                                    <span class="udash-feed__meta">${n.message.substring(0, 50)}${n.message.length > 50 ? '...' : ''} · ${formatFullDate(n.created_at)}</span>
                                </span>
                            </a>
                        `).join('');
                    }
                }
            }
        });

    fetch('../includes/api/stats_handler.php?action=get_overview') 
        // Reusing overview for presence count, but better to have personal
        .then(res => res.json())
        .then(res => {
             // Logic to show if user is present today could go here
             // For now, keep it simple
        });
}

function updateStat(id, value) {
    const el = document.getElementById(id);
    if (!el) return;
    el.textContent = value;
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

function formatTimeAgo(dateString) {
    const now = new Date();
    const past = new Date(dateString);
    const diff = Math.floor((now - past) / 1000);

    if (diff < 60) return 'Just now';
    if (diff < 3600) return Math.floor(diff / 60) + 'm ago';
    if (diff < 86400) return Math.floor(diff / 3600) + 'h ago';
    if (diff < 604800) return Math.floor(diff / 86400) + 'd ago';
    return past.toLocaleDateString();
}

function formatFullDate(dateString) {
    if (!dateString) return '';
    const d = new Date(dateString);
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    return `${d.getDate()} ${months[d.getMonth()]}, ${d.getFullYear()}`;
}
