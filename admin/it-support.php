<?php
require_once dirname(__DIR__) . '/includes/middleware.php';
protectModule(['Admin', 'HR']);

$page_title = 'IT Helpdesk';
$page_subtitle = 'Monitor and manage all employee support tickets';
include 'includes/header.php';
include 'includes/sidebar.php';

$user_role = $_SESSION['user_role'] ?? 'Admin';
?>

<link rel="stylesheet" href="../assets/css/it-support.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    const IT_USER = {
        emp_id: '<?php echo (int) $_SESSION['user_id']; ?>',
        role: '<?php echo htmlspecialchars($user_role, ENT_QUOTES, 'UTF-8'); ?>',
        dept: 'Administration',
        is_it_staff: true
    };
</script>

<div class="it-support-container animate-fade">
    <div class="it-sidebar">
        <div class="it-sidebar-header">
            <h5>Support Queue</h5>
            <span class="font-12 text-light" style="display:block;margin-top:4px;">Admin — all tickets</span>
        </div>

        <div class="it-search-wrapper">
            <div class="it-search-inner">
                <i class="fas fa-search"></i>
                <input type="text" id="it-search-input" placeholder="Search tickets or users...">
            </div>
        </div>

        <div class="it-filter-tabs">
            <div class="it-filter-tab active" data-filter="All">All</div>
            <div class="it-filter-tab" data-filter="Open">Open</div>
            <div class="it-filter-tab" data-filter="In-Progress">In-Progress</div>
            <div class="it-filter-tab" data-filter="Resolved">Resolved</div>
            <div class="it-filter-tab" data-filter="Closed">Closed</div>
        </div>

        <div class="it-ticket-list" id="ticket-list-container">
            <div class="p-4 text-center text-muted small">Loading tickets...</div>
        </div>
    </div>

    <div class="it-main-content" id="main-content-area">
        <div class="it-content-placeholder animate-fade">
            <i class="fas fa-headset" style="font-size: 64px; margin-bottom: 15px; opacity: 0.2;"></i>
            <h3>IT Support Helpdesk</h3>
            <p>View every ticket, reply in any chat, and use handover, resolve, close, or internal notes.</p>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script src="../user/assets/js/it-support.js?v=<?php echo time(); ?>"></script>

<style>
    .content-body {
        padding: 0;
    }
</style>
