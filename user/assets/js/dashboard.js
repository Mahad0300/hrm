// user/assets/js/dashboard.js

let liveTimerInterval = null;

document.addEventListener('DOMContentLoaded', function() {
    fetchPersonalStats();
    fetchLatestAnnouncements();
    fetchRecentNotifications();
    
    // Refresh every 5 minutes
    setInterval(() => {
        fetchPersonalStats();
        fetchLatestAnnouncements();
        fetchRecentNotifications();
    }, 300000);
});

function fetchRecentNotifications() {
    const container = document.getElementById('notificationsFeed');
    if (!container) return;

    fetch('../includes/api/stats_handler.php?action=get_recent_notifications')
        .then(res => res.json())
        .then(res => {
            if (res.status === 'success') {
                const data = res.data;
                if (!data || data.length === 0) {
                    container.innerHTML = '<div class="py-20 text-center font-13 text-light">No recent notifications.</div>';
                    return;
                }

                container.innerHTML = data.map(not => `
                    <a href="notifications.php" class="udash-feed__item">
                        <span class="udash-feed__body">
                            <span class="udash-feed__title"><span class="udash-feed__emoji" aria-hidden="true">🔔</span>${not.title}</span>
                            <span class="udash-feed__meta">
                                ${not.message.substring(0, 45)}${not.message.length > 45 ? '...' : ''} · ${not.time_ago}
                            </span>
                        </span>
                    </a>
                `).join('');
            }
        });
}

function fetchLatestAnnouncements() {
    const container = document.getElementById('announcementsFeed');
    if (!container) return;

    fetch('../includes/api/stats_handler.php?action=get_latest_announcements')
        .then(res => res.json())
        .then(res => {
            if (res.status === 'success') {
                const data = res.data;
                if (!data || data.length === 0) {
                    container.innerHTML = '<div class="py-20 text-center font-13 text-light">No active announcements.</div>';
                    return;
                }

                const getEmoji = (type) => {
                    const t = (type || '').toUpperCase();
                    if (t === 'IMPORTANT') return '🚨';
                    if (t === 'CELEBRATION') return '🎉';
                    if (t === 'HOLIDAY') return '📅';
                    if (t === 'MEETING') return '🤝';
                    return '📢';
                };

                const getBadgeClass = (type) => {
                    const t = (type || '').toUpperCase();
                    if (t === 'IMPORTANT') return 'important';
                    if (t === 'UPDATE') return 'update';
                    if (t === 'HOLIDAY') return 'holiday';
                    return 'notice';
                };

                container.innerHTML = data.map(ann => `
                    <a href="announcements.php" class="udash-feed__item">
                        <span class="udash-feed__body">
                            <span class="udash-feed__title"><span class="udash-feed__emoji" aria-hidden="true">${getEmoji(ann.type)}</span>${ann.title}</span>
                            <span class="udash-feed__meta">
                                <span class="udash-feed__badge udash-feed__badge--${getBadgeClass(ann.type)}">${ann.type}</span> 
                                ${ann.author_name} · ${ann.time_ago}
                            </span>
                        </span>
                    </a>
                `).join('');
            }
        });
}

function fetchPersonalStats() {
    fetch('../includes/api/stats_handler.php?action=get_personal_overview')
        .then(res => res.json())
        .then(res => {
            if (res.status === 'success') {
                const d = res.data;
                const serverTime = new Date(d.server_time);
                
                // Update Stats
                updateStat('stat-leave-balance', d.leave_balance + ' Days');
                updateStat('stat-leave-trend', '-' + d.approved_leaves + ' days');
                updateStat('stat-dept-count-badge', d.dept_employees);
                updateStat('stat-dept-employees-count', d.dept_employees + ' employees in your department');
                updateStat('stat-unread-notifications', d.unread_notifications);
                updateStat('stat-unread-count-badge', '+' + d.unread_notifications);
                updateStat('user-job-title', d.dept_name); // Set Big Title to Dept Name
                updateStat('stat-work-target', 'Target: ' + d.target_hours);

                // Attendance Stats
                const att = d.today_attendance;
                const statusEl = document.getElementById('stat-present-status');
                const timeEl = document.getElementById('stat-attendance-time');
                const workHoursEl = document.getElementById('stat-work-hours');
                const badge = document.querySelector('.stat-card:nth-child(1) .trend-up span');
                const badgeIcon = document.querySelector('.stat-card:nth-child(1) .trend-up i');

                if (att) {
                    statusEl.textContent = att.status;
                    
                    if (att.clock_in) {
                        const inTime = new Date(att.clock_in).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                        timeEl.textContent = 'Checked in at ' + inTime;
                        
                        // If still checked in (no clock out), start live timer
                        if (!att.clock_out) {
                            // Calculate Shift End Timestamp
                            const shiftDate = att.date; // YYYY-MM-DD
                            let shiftEnd = new Date(shiftDate + ' ' + att.end_time);
                            const shiftStart = new Date(shiftDate + ' ' + att.start_time);
                            
                            // Handle overnight shift
                            if (shiftStart > shiftEnd) {
                                shiftEnd.setDate(shiftEnd.getDate() + 1);
                            }
                            
                            startLiveTimer(att.clock_in, d.server_time, d.target_hours, shiftEnd.getTime());
                        } else {
                            if (liveTimerInterval) clearInterval(liveTimerInterval);
                            workHoursEl.textContent = att.working_hours || '0h 00m';
                            updateWorkTrend(att.working_hours, d.target_hours);
                        }
                    }

                    // Update badge style based on status
                    if (att.status === 'LATE IN') {
                        badge.parentElement.className = 'trend-down';
                        badge.textContent = 'Late';
                        if (badgeIcon) badgeIcon.setAttribute('data-lucide', 'trending-down');
                    } else if (att.status === 'ON TIME') {
                        badge.parentElement.className = 'trend-up';
                        badge.textContent = 'On time';
                        if (badgeIcon) badgeIcon.setAttribute('data-lucide', 'trending-up');
                    }
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                } else {
                    statusEl.textContent = 'Absent';
                    workHoursEl.textContent = '0h 00m';
                    timeEl.textContent = 'Not checked in today';
                    badge.parentElement.style.display = 'none';
                    if (liveTimerInterval) clearInterval(liveTimerInterval);
                }
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
}

function startLiveTimer(checkInTimeStr, serverTimeStr, targetHoursStr, shiftEndTimestamp) {
    if (liveTimerInterval) clearInterval(liveTimerInterval);
    
    const checkIn = new Date(checkInTimeStr).getTime();
    let lastServerTime = new Date(serverTimeStr);
    const clientTime = new Date().getTime();
    const offset = lastServerTime.getTime() - clientTime; 

    liveTimerInterval = setInterval(() => {
        const now = new Date().getTime() + offset;
        const diff = now - checkIn;

        if (diff > 0) {
            const totalSeconds = Math.floor(diff / 1000);
            const h = Math.floor(totalSeconds / 3600);
            const m = Math.floor((totalSeconds % 3600) / 60);
            
            const hoursStr = h + 'h ' + m.toString().padStart(2, '0') + 'm';
            const el = document.getElementById('stat-work-hours');
            if (el) el.textContent = hoursStr;

            // Update Trend Badge (Remaining Shift Time)
            const trendContainer = document.getElementById('stat-work-trend-container');
            const trendText = document.getElementById('stat-work-trend-text');
            const trendIcon = document.getElementById('stat-work-trend-icon');
            
            if (trendContainer && trendText && shiftEndTimestamp) {
                const remainingSec = Math.floor((shiftEndTimestamp - now) / 1000);
                const absDiff = Math.abs(remainingSec);
                const rh = Math.floor(absDiff / 3600);
                const rm = Math.floor((absDiff % 3600) / 60);
                const formattedDiff = (rh > 0 ? rh + 'h ' : '') + rm + 'm';

                trendContainer.style.display = 'flex';
                if (remainingSec >= 0) {
                    trendText.textContent = `-${formattedDiff}`;
                    trendContainer.className = 'trend-down'; // Time left
                    if (trendIcon) trendIcon.setAttribute('data-lucide', 'trending-down');
                } else {
                    trendText.textContent = `+${formattedDiff}`;
                    trendContainer.className = 'trend-up'; // Overtime past shift end
                    if (trendIcon) trendIcon.setAttribute('data-lucide', 'trending-up');
                }
                if (typeof lucide !== 'undefined') lucide.createIcons();
            }
        }
        
        lastServerTime.setSeconds(lastServerTime.getSeconds() + 1);
    }, 1000);
}

function updateWorkTrend(workedStr, targetStr) {
    const trendContainer = document.getElementById('stat-work-trend-container');
    const trendText = document.getElementById('stat-work-trend-text');
    const trendIcon = document.getElementById('stat-work-trend-icon');
    if (!trendContainer || !trendText) return;

    const parseToSeconds = (str) => {
        const match = str.match(/(\d+)h\s+(\d+)m/);
        return match ? (parseInt(match[1]) * 3600) + (parseInt(match[2]) * 60) : 0;
    };

    const workedSec = parseToSeconds(workedStr);
    const targetSec = parseToSeconds(targetStr);
    const diffSec = workedSec - targetSec;
    
    const absDiff = Math.abs(diffSec);
    const h = Math.floor(absDiff / 3600);
    const m = Math.floor((absDiff % 3600) / 60);
    const formattedDiff = (h > 0 ? h + 'h ' : '') + m + 'm';

    trendContainer.style.display = 'flex';
    if (diffSec >= 0) {
        trendText.textContent = `+${formattedDiff}`;
        trendContainer.className = 'trend-up'; 
        if (trendIcon) trendIcon.setAttribute('data-lucide', 'trending-up');
    } else {
        trendText.textContent = `-${formattedDiff}`;
        trendContainer.className = 'trend-down';
        if (trendIcon) trendIcon.setAttribute('data-lucide', 'trending-down');
    }
    if (typeof lucide !== 'undefined') lucide.createIcons();
}

function updateStat(id, value) {
    const el = document.getElementById(id);
    if (!el) return;
    el.textContent = value;
}

function formatFullDate(dateString) {
    if (!dateString) return '';
    const d = new Date(dateString);
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    return `${d.getDate()} ${months[d.getMonth()]}, ${d.getFullYear()}`;
}
