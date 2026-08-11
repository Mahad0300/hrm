<?php
$page_title = "Workspace Notifications";
$page_subtitle = "Keep track of all your alerts and updates.";
include __DIR__ . '/../partials/admin/header.php';
?>
<?php include __DIR__ . '/../partials/admin/sidebar.php'; ?>

<!-- Page Action Area -->
<div class="page-action-area gap-12">
    <button class="btn-primary" id="markAllReadBtn"><i data-lucide="check-check" size="18"></i> Mark All as Read</button>
    <button class="btn-primary danger" id="clearAllBtn" type="button"><i data-lucide="trash-2" size="18"></i> Clear All</button>
</div>

<!-- Table Tools: Per Page & Summary -->
<div class="flex-between mb-24 px-4">
    <div class="flex-center gap-10">
        <span class="font-13 text-light">Show</span>
        <select class="form-control font-13 font-600 per-page-select" id="perPageSelect">
            <option value="5">5</option>
            <option value="10" selected>10</option>
            <option value="25">25</option>
            <option value="50">50</option>
            <option value="all">All</option>
        </select>
        <span class="font-13 text-light">entries</span>
    </div>
    <div class="text-right">
        <span class="font-13 text-light" id="tableSummary">Showing 0 to 0 of 0 entries</span>
    </div>
</div>

<!-- Notifications List Card -->
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
    <!-- Pagination Footer -->
    <div class="p-24 flex-between border-top" id="paginationWrapper">
        <span class="font-13 text-light" id="paginationInfo">Showing 0 to 0 of 0 entries</span>
        <div class="flex-center gap-8" id="paginationControls">
            <button class="action-btn" id="prevPage"><i data-lucide="chevron-left" size="16"></i></button>
            <div id="pageNumbers" class="flex-center gap-8"></div>
            <button class="action-btn" id="nextPage"><i data-lucide="chevron-right" size="16"></i></button>
        </div>
    </div>
</div>

<script>
    (function () {
        const list = document.getElementById('notiList');
        const emptyState = document.getElementById('notiEmpty');
        const perPageSelect = document.getElementById('perPageSelect');

        let allNotis = [];
        let currentPage = 1;
        let rowsPerPage = 10;

        function escapeHtml(text) {
            if (text == null) return '';
            return String(text)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        function safeNotiUrl(url) {
            if (!url || typeof url !== 'string') return '';
            const trimmed = url.trim();
            if (/^(javascript|data):/i.test(trimmed)) return '';
            if (/^https?:\/\//i.test(trimmed)) {
                return escapeHtml(trimmed);
            }
            if (window.HRM && typeof window.HRM.url === 'function') {
                return escapeHtml(window.HRM.url(trimmed));
            }
            return escapeHtml(trimmed);
        }

        function fetchNotifications() {
            fetch('/assets/api/notification_handler.php?action=fetch')
                .then(res => res.json())
                .then(res => {
                    if (res.status === 'success') {
                        allNotis = res.data || [];
                        renderNotifications();
                    }
                });
        }

        function renderNotifications() {
            list.innerHTML = '';
            const total = allNotis.length;

            if (total === 0) {
                emptyState.style.display = 'flex';
                document.getElementById('paginationWrapper').style.display = 'none';
                updatePaginationInfo(0, 0, 0);
                return;
            }

            emptyState.style.display = 'none';
            document.getElementById('paginationWrapper').style.display = 'flex';

            const totalPages = rowsPerPage === -1 ? 1 : Math.ceil(total / rowsPerPage);
            if (currentPage > totalPages && totalPages > 0) {
                currentPage = totalPages;
            }

            const start = (currentPage - 1) * rowsPerPage;
            const end = rowsPerPage === -1 ? total : Math.min(start + rowsPerPage, total);
            const paginated = rowsPerPage === -1 ? allNotis : allNotis.slice(start, end);

            paginated.forEach(noti => {
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

                const safeUrl = safeNotiUrl(noti.target_url);
                item.innerHTML = `
                    <div class="icon-box ${iconClass}">
                        <i data-lucide="${icon}" size="20"></i>
                    </div>
                    <div class="w-full">
                        <div class="mb-5">
                            <h4 class="font-600">${escapeHtml(noti.title)}</h4>
                        </div>
                        <p class="font-14 text-light">${escapeHtml(noti.message)}</p>
                        <div class="flex-center gap-10 mt-10">
                            <span class="badge badge-${iconClass}">${escapeHtml(noti.type)}</span>
                            ${safeUrl ? `<a href="${safeUrl}" class="font-12 font-600 text-primary noti-link">View Details</a>` : ''}
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

            updatePaginationInfo(start + 1, end, total);
            if (typeof lucide !== 'undefined') lucide.createIcons();
        }

        function updatePaginationInfo(start, end, total) {
            const info = `Showing ${total === 0 ? 0 : start} to ${end} of ${total} entries`;
            const pInfo = document.getElementById('paginationInfo');
            const tSum = document.getElementById('tableSummary');
            if (pInfo) pInfo.textContent = info;
            if (tSum) tSum.textContent = info;

            const totalPages = rowsPerPage === -1 ? 1 : Math.ceil(total / rowsPerPage);
            const pageNumbers = document.getElementById('pageNumbers');
            if (pageNumbers) {
                pageNumbers.innerHTML = '';
                for (let i = 1; i <= totalPages; i++) {
                    const btn = document.createElement('button');
                    btn.className = `action-btn ${i === currentPage ? 'btn-active' : ''}`;
                    btn.textContent = i;
                    btn.onclick = () => { currentPage = i; renderNotifications(); };
                    pageNumbers.appendChild(btn);
                }
            }

            const prevBtn = document.getElementById('prevPage');
            const nextBtn = document.getElementById('nextPage');
            if (prevBtn) prevBtn.disabled = currentPage === 1;
            if (nextBtn) nextBtn.disabled = currentPage === totalPages || totalPages === 0;
        }

        window.markRead = function(id) {
            const fd = new FormData();
            fd.append('action', 'mark_read');
            fd.append('id', id);
            fetch('/assets/api/notification_handler.php', { method: 'POST', body: fd })
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
                fetch('/assets/api/notification_handler.php', { method: 'POST', body: fd })
                    .then(() => fetchNotifications());
            }, 300);
        };

        document.getElementById('markAllReadBtn').onclick = function() {
            const fd = new FormData();
            fd.append('action', 'mark_all_read');
            fetch('/assets/api/notification_handler.php', { method: 'POST', body: fd })
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
                    fetch('/assets/api/notification_handler.php', { method: 'POST', body: fd })
                        .then(() => fetchNotifications());
                }
            });
        };

        perPageSelect.onchange = function() {
            rowsPerPage = this.value === 'all' ? -1 : parseInt(this.value);
            currentPage = 1;
            renderNotifications();
        };

        document.getElementById('prevPage').onclick = function() {
            if (currentPage > 1) {
                currentPage--;
                renderNotifications();
            }
        };

        document.getElementById('nextPage').onclick = function() {
            const totalPages = Math.ceil(allNotis.length / rowsPerPage);
            if (currentPage < totalPages) {
                currentPage++;
                renderNotifications();
            }
        };

        function formatDate(dateStr) {
            const date = new Date(dateStr);
            const now = new Date();
            const diff = now - date;
            if (diff < 86400000 && now.getDate() === date.getDate()) return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            return date.toLocaleDateString([], { month: 'short', day: 'numeric' });
        }

        fetchNotifications();
        window.fetchNotifications = fetchNotifications;
    })();
</script>

<?php include __DIR__ . '/../partials/admin/footer.php'; ?>
