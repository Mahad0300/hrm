<?php 
$page_title = "Workspace Notifications";
$page_subtitle = "Keep track of all your alerts and updates.";
include 'includes/header.php'; 
?>
<?php include 'includes/sidebar.php'; ?>

<!-- Page Action Area -->
<div class="page-action-area gap-12">
    <button class="btn-primary" id="markAllReadBtn"><i data-lucide="check-check" size="18"></i> Mark All as Read</button>
    <button class="btn-primary danger" id="clearAllBtn" type="button"><i data-lucide="trash-2" size="18"></i> Clear All</button>
</div>

<!-- Notifications List -->
<div class="card p-0 overflow-hidden" id="notiCard">
    <div class="noti-list" id="notiList">
        <!-- Dynamic content will be injected here -->
    </div>
    <!-- Empty state when no notifications -->
    <div class="noti-empty" id="notiEmpty" style="display: none;">
        <div class="noti-empty-icon">
            <i data-lucide="bell-off" size="48"></i>
        </div>
        <h3 class="noti-empty-title">No notifications</h3>
        <p class="noti-empty-text">You're all caught up. New alerts will show up here.</p>
    </div>
</div>

<script>
    (function () {
        const list = document.getElementById('notiList');
        const emptyState = document.getElementById('notiEmpty');

        function fetchNotifications() {
            fetch('../includes/api/notification_handler.php?action=fetch')
                .then(res => res.json())
                .then(res => {
                    if (res.status === 'success') {
                        renderNotifications(res.data);
                    }
                });
        }

        function renderNotifications(data) {
            list.innerHTML = '';
            if (data.length === 0) {
                emptyState.style.display = 'flex';
                return;
            }
            emptyState.style.display = 'none';

            data.forEach(noti => {
                const item = document.createElement('div');
                const isUnread = noti.is_read == 0;
                item.className = `noti-item ${isUnread ? 'noti-item--unread' : 'noti-item--read'}`;
                item.dataset.id = noti.recipient_record_id;
                
                // Icon Logic
                let icon = 'bell';
                let iconClass = 'primary';
                if (noti.type === 'Leave') { icon = 'calendar-clock'; iconClass = 'warning'; }
                if (noti.type === 'Recruitment') { icon = 'user-plus'; iconClass = 'info'; }
                if (noti.type === 'System') { icon = 'shield-check'; iconClass = 'primary'; }

                item.innerHTML = `
                    <div class="icon-box ${iconClass}">
                        <i data-lucide="${icon}" size="20"></i>
                    </div>
                    <div class="w-full">
                        <div class="mb-5">
                            <h4 class="font-600">${noti.title}</h4>
                        </div>
                        <p class="font-14 text-light">${noti.message}</p>
                        <div class="flex-center gap-10 mt-10">
                            <span class="badge badge-${iconClass}">${noti.type}</span>
                            ${noti.target_url ? `<a href="${noti.target_url}" class="font-12 font-600 text-primary noti-link">View Details</a>` : ''}
                        </div>
                    </div>
                    <div class="noti-item-right">
                        <div class="noti-right-meta">
                            ${isUnread ? '<span class="badge-new">New</span>' : ''}
                            <span class="noti-time font-11 text-light">${formatDate(noti.created_at)}</span>
                        </div>
                        <div class="noti-item-actions">
                            ${isUnread ? `
                                <button type="button" class="noti-read-btn" title="Mark as read" onclick="markRead(${noti.recipient_record_id})">
                                    <i data-lucide="check" size="18"></i>
                                </button>
                            ` : `
                                <span class="noti-read-done" title="Read"><i data-lucide="check-check" size="18"></i></span>
                            `}
                            <button type="button" class="action-btn no-border no-bg noti-remove-btn" title="Remove" onclick="removeNotification(${noti.recipient_record_id}, this)">
                                <i data-lucide="x" size="18"></i>
                            </button>
                        </div>
                    </div>
                `;
                list.appendChild(item);
            });
            if (typeof lucide !== 'undefined') lucide.createIcons();
        }

        window.markRead = function(id) {
            const fd = new FormData();
            fd.append('action', 'mark_read');
            fd.append('id', id);
            fetch('../includes/api/notification_handler.php', { method: 'POST', body: fd })
                .then(res => res.json())
                .then(res => {
                    if (res.status === 'success') fetchNotifications();
                });
        };

        window.removeNotification = function(id, btn) {
            const item = btn.closest('.noti-item');
            item.classList.add('noti-item--swipe-out');
            setTimeout(() => {
                const fd = new FormData();
                fd.append('action', 'delete');
                fd.append('id', id);
                fetch('../includes/api/notification_handler.php', { method: 'POST', body: fd })
                    .then(() => fetchNotifications());
            }, 300);
        };

        document.getElementById('markAllReadBtn').onclick = function() {
            const fd = new FormData();
            fd.append('action', 'mark_all_read');
            fetch('../includes/api/notification_handler.php', { method: 'POST', body: fd })
                .then(() => fetchNotifications());
        };

        document.getElementById('clearAllBtn').onclick = function() {
            Swal.fire({
                title: 'Clear all notifications?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, clear all'
            }).then(result => {
                if (result.isConfirmed) {
                    const fd = new FormData();
                    fd.append('action', 'clear');
                    fetch('../includes/api/notification_handler.php', { method: 'POST', body: fd })
                        .then(() => fetchNotifications());
                }
            });
        };

        function formatDate(dateStr) {
            const date = new Date(dateStr);
            const now = new Date();
            const diff = now - date;
            if (diff < 86400000 && now.getDate() === date.getDate()) return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            return date.toLocaleDateString([], { month: 'short', day: 'numeric' });
        }

        fetchNotifications();
        setInterval(fetchNotifications, 5000);
    })();
</script>

<?php include 'includes/footer.php'; ?>
