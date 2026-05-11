<?php 
$page_title = "Workspace Notifications";
$page_subtitle = "Keep track of all your alerts and updates.";
include 'includes/header.php'; 
?>
<?php include 'includes/sidebar.php'; ?>

<!-- Page Action Area -->
<div class="page-action-area gap-12">
    <button class="btn-primary" id="markAllReadBtn"><i data-lucide="check-check" size="16"></i> Mark All as Read</button>
    <button class="btn-primary danger" id="clearAllBtn" type="button"><i data-lucide="trash-2" size="16"></i> Clear All</button>
</div>

<!-- Notifications List -->
<div class="card p-0 overflow-hidden" id="notiCard">
    <div class="noti-list" id="notiList">
        <!-- 1 - Leave (unread / new) -->
        <div class="noti-item noti-item--unread" data-read="0">
            <div class="icon-box warning">
                <i data-lucide="calendar-clock" size="20"></i>
            </div>
            <div class="w-full">
                <div class="mb-5">
                    <h4 class="font-600">Leave Request Submitted</h4>
                </div>
                <p class="font-14 text-light">Oliver Mitchell has submitted a new leave request (Casual Leave, 2 days). Pending your approval.</p>
                <div class="flex-center gap-10 mt-10">
                    <span class="badge badge-warning">Leave</span>
                    <a href="leave-management.php" class="font-12 font-600 text-primary cursor-pointer noti-link">View Request</a>
                </div>
            </div>
            <div class="noti-item-right">
                <div class="noti-right-meta">
                    <span class="badge-new">New</span>
                    <span class="noti-time font-11 text-light">9:42 AM</span>
                </div>
                <div class="noti-item-actions">
                    <button type="button" class="noti-read-btn" title="Mark as read" aria-label="Mark as read"><i data-lucide="check" size="16"></i></button>
                    <button type="button" class="action-btn align-start no-border no-bg noti-remove-btn" title="Remove"><i data-lucide="x" size="14"></i></button>
                </div>
            </div>
        </div>

        <!-- 2 - New Joining (unread) -->
        <div class="noti-item noti-item--unread" data-read="0">
            <div class="icon-box primary">
                <i data-lucide="user-plus" size="20"></i>
            </div>
            <div class="w-full">
                <div class="mb-5">
                    <h4 class="font-600">New Joining</h4>
                </div>
                <p class="font-14 text-light">New employee account created for Michael Scott in the Engineering department. Welcome onboard!</p>
                <div class="flex-center gap-10 mt-10">
                    <span class="badge badge-primary">System</span>
                    <span class="font-12 font-600 text-primary cursor-pointer">View Profile</span>
                </div>
            </div>
            <div class="noti-item-right">
                <div class="noti-right-meta">
                    <span class="badge-new">New</span>
                    <span class="noti-time font-11 text-light">10:05 AM</span>
                </div>
                <div class="noti-item-actions">
                    <button type="button" class="noti-read-btn" title="Mark as read" aria-label="Mark as read"><i data-lucide="check" size="16"></i></button>
                    <button type="button" class="action-btn align-start no-border no-bg noti-remove-btn" title="Remove"><i data-lucide="x" size="14"></i></button>
                </div>
            </div>
        </div>

        <!-- 3 - Attendance (unread) -->
        <div class="noti-item noti-item--unread" data-read="0">
            <div class="icon-box info">
                <i data-lucide="calendar-check" size="20"></i>
            </div>
            <div class="w-full">
                <div class="mb-5">
                    <h4 class="font-600">Attendance Correction Request</h4>
                </div>
                <p class="font-14 text-light">Emma Williams requested a correction for 12 Sep 2026 — Check-in time update. Action required.</p>
                <div class="flex-center gap-10 mt-10">
                    <span class="badge badge-info">Attendance</span>
                    <a href="attendance-log.php" class="font-12 font-600 text-primary cursor-pointer noti-link">Review</a>
                </div>
            </div>
            <div class="noti-item-right">
                <div class="noti-right-meta">
                    <span class="badge-new">New</span>
                    <span class="noti-time font-11 text-light">Yesterday, 4:30 PM</span>
                </div>
                <div class="noti-item-actions">
                    <button type="button" class="noti-read-btn" title="Mark as read" aria-label="Mark as read"><i data-lucide="check" size="16"></i></button>
                    <button type="button" class="action-btn align-start no-border no-bg noti-remove-btn" title="Remove"><i data-lucide="x" size="14"></i></button>
                </div>
            </div>
        </div>

        <!-- 4 - Leave (read) -->
        <div class="noti-item noti-item--read" data-read="1">
            <div class="icon-box warning">
                <i data-lucide="calendar-clock" size="20"></i>
            </div>
            <div class="w-full">
                <div class="mb-5">
                    <h4 class="font-600">Leave Request Submitted</h4>
                </div>
                <p class="font-14 text-light">James Bond has submitted a leave request (Casual Leave, 2 days — 20–21 Nov 2026). Awaiting approval.</p>
                <div class="flex-center gap-10 mt-10">
                    <span class="badge badge-warning">Leave</span>
                    <a href="leave-management.php" class="font-12 font-600 text-primary cursor-pointer noti-link">View Request</a>
                </div>
            </div>
            <div class="noti-item-right">
                <div class="noti-right-meta">
                    <span class="noti-time font-11 text-light">Yesterday, 2:15 PM</span>
                </div>
                <div class="noti-item-actions">
                    <span class="noti-read-done" title="Read"><i data-lucide="check-check" size="16"></i></span>
                    <button type="button" class="action-btn align-start no-border no-bg noti-remove-btn" title="Remove"><i data-lucide="x" size="14"></i></button>
                </div>
            </div>
        </div>

        <!-- 5 - New Joining (read) -->
        <div class="noti-item noti-item--read" data-read="1">
            <div class="icon-box primary">
                <i data-lucide="user-plus" size="20"></i>
            </div>
            <div class="w-full">
                <div class="mb-5">
                    <h4 class="font-600">New Joining</h4>
                </div>
                <p class="font-14 text-light">New employee Sarah Connor has been added to the HRM system. Department: Operations.</p>
                <div class="flex-center gap-10 mt-10">
                    <span class="badge badge-primary">System</span>
                    <span class="font-12 font-600 text-primary cursor-pointer">View Profile</span>
                </div>
            </div>
            <div class="noti-item-right">
                <div class="noti-right-meta">
                    <span class="noti-time font-11 text-light">Yesterday, 11:00 AM</span>
                </div>
                <div class="noti-item-actions">
                    <span class="noti-read-done" title="Read"><i data-lucide="check-check" size="16"></i></span>
                    <button type="button" class="action-btn align-start no-border no-bg noti-remove-btn" title="Remove"><i data-lucide="x" size="14"></i></button>
                </div>
            </div>
        </div>

        <!-- 6 - Attendance (read) -->
        <div class="noti-item noti-item--read" data-read="1">
            <div class="icon-box info">
                <i data-lucide="calendar-check" size="20"></i>
            </div>
            <div class="w-full">
                <div class="mb-5">
                    <h4 class="font-600">Attendance Correction Request</h4>
                </div>
                <p class="font-14 text-light">Noah Smith requested correction for 10 Oct 2026 — Check-out time was missed. Please review.</p>
                <div class="flex-center gap-10 mt-10">
                    <span class="badge badge-info">Attendance</span>
                    <a href="attendance-log.php" class="font-12 font-600 text-primary cursor-pointer noti-link">Review</a>
                </div>
            </div>
            <div class="noti-item-right">
                <div class="noti-right-meta">
                    <span class="noti-time font-11 text-light">3 days ago</span>
                </div>
                <div class="noti-item-actions">
                    <span class="noti-read-done" title="Read"><i data-lucide="check-check" size="16"></i></span>
                    <button type="button" class="action-btn align-start no-border no-bg noti-remove-btn" title="Remove"><i data-lucide="x" size="14"></i></button>
                </div>
            </div>
        </div>

        <!-- 7 - Leave (read) -->
        <div class="noti-item noti-item--read" data-read="1">
            <div class="icon-box warning">
                <i data-lucide="calendar-clock" size="20"></i>
            </div>
            <div class="w-full">
                <div class="mb-5">
                    <h4 class="font-600">Leave Request Submitted</h4>
                </div>
                <p class="font-14 text-light">Diana Prince has submitted an Annual Leave request (12 days, 20 Dec – 31 Dec 2026). Pending your review.</p>
                <div class="flex-center gap-10 mt-10">
                    <span class="badge badge-warning">Leave</span>
                    <a href="leave-management.php" class="font-12 font-600 text-primary cursor-pointer noti-link">View Request</a>
                </div>
            </div>
            <div class="noti-item-right">
                <div class="noti-right-meta">
                    <span class="noti-time font-11 text-light">2 days ago</span>
                </div>
                <div class="noti-item-actions">
                    <span class="noti-read-done" title="Read"><i data-lucide="check-check" size="16"></i></span>
                    <button type="button" class="action-btn align-start no-border no-bg noti-remove-btn" title="Remove"><i data-lucide="x" size="14"></i></button>
                </div>
            </div>
        </div>
    </div>
    <!-- Empty state when no notifications -->
    <div class="noti-empty" id="notiEmpty" aria-hidden="true">
        <div class="noti-empty-icon">
            <i data-lucide="bell-off" size="48"></i>
        </div>
        <h3 class="noti-empty-title">No notifications</h3>
        <p class="noti-empty-text">You're all caught up. New alerts will show up here.</p>
    </div>
</div>

<script>
(function() {
    function initNotiRead() {
        var list = document.querySelector('.noti-list');
        if (!list) return;
        list.addEventListener('click', function(e) {
            var readBtn = e.target.closest('.noti-read-btn');
            if (readBtn) {
                var item = readBtn.closest('.noti-item');
                if (item && item.dataset.read === '0') setItemRead(item);
                return;
            }
            var removeBtn = e.target.closest('.noti-remove-btn');
            if (removeBtn) {
                var item = removeBtn.closest('.noti-item');
                if (item && !item.classList.contains('noti-item--swipe-out')) removeNotiWithSwipe(item);
            }
        });
        document.getElementById('markAllReadBtn')?.addEventListener('click', function() {
            list.querySelectorAll('.noti-item[data-read="0"]').forEach(setItemRead);
        });
        document.getElementById('clearAllBtn')?.addEventListener('click', function() {
            var items = list.querySelectorAll('.noti-item');
            if (items.length === 0) return;
            items.forEach(function(item) {
                if (!item.classList.contains('noti-item--swipe-out')) item.classList.add('noti-item--swipe-out');
            });
            setTimeout(function() {
                items.forEach(function(item) { item.remove(); });
                showEmptyIfNeeded();
            }, 380);
        });
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }
    function showEmptyIfNeeded() {
        var list = document.getElementById('notiList');
        var empty = document.getElementById('notiEmpty');
        if (!list || !empty) return;
        if (list.querySelectorAll('.noti-item').length === 0) {
            empty.style.display = 'flex';
            empty.setAttribute('aria-hidden', 'false');
            if (typeof lucide !== 'undefined') lucide.createIcons();
        } else {
            empty.style.display = 'none';
            empty.setAttribute('aria-hidden', 'true');
        }
    }
    function setItemRead(item) {
        item.classList.remove('noti-item--unread');
        item.classList.add('noti-item--read');
        item.dataset.read = '1';
        var meta = item.querySelector('.noti-right-meta .badge-new');
        if (meta) meta.remove();
        var actions = item.querySelector('.noti-item-actions');
        if (!actions) return;
        var btn = actions.querySelector('.noti-read-btn');
        if (btn) {
            var span = document.createElement('span');
            span.className = 'noti-read-done';
            span.title = 'Read';
            span.innerHTML = '<i data-lucide="check-check" size="16"></i>';
            btn.replaceWith(span);
            if (typeof lucide !== 'undefined') lucide.createIcons();
        }
    }
    function removeNotiWithSwipe(item) {
        item.classList.add('noti-item--swipe-out');
        item.addEventListener('transitionend', function onEnd() {
            item.removeEventListener('transitionend', onEnd);
            item.remove();
            showEmptyIfNeeded();
        });
    }

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initNotiRead);
    else initNotiRead();
})();
</script>

<?php include 'includes/footer.php'; ?>
