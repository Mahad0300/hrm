<?php
$page_title = "IT Support Helpdesk";
include 'includes/header.php';
include 'includes/sidebar.php';

// Check User Department
$is_it_staff = false;
$user_dept = 'Other';

try {
    // Assuming $pdo is the connection variable from db_connect.php (change to $conn if needed)
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT d.name 
        FROM employees e 
        LEFT JOIN departments d ON e.department_id = d.id 
        WHERE e.id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $user_dept = $stmt->fetchColumn() ?: 'Other';
    
    // Check if the department name contains 'IT' or 'Support'
    if (stripos($user_dept, 'IT') !== false || stripos($user_dept, 'Support') !== false) {
        $is_it_staff = true;
    }
} catch (Exception $e) {
    // Fallback if PDO is not configured this way
}
?>

<!-- Add IT Support Specific CSS -->
<link rel="stylesheet" href="../assets/css/it-support.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<!-- jQuery (Required for IT Support AJAX and DOM Manipulation) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- SweetAlert2 for nice popups -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // Pass user info to Javascript
    const IT_USER = {
        emp_id: '<?php echo $_SESSION['user_id']; ?>',
        role: '<?php echo isset($_SESSION['role']) ? $_SESSION['role'] : 'Employee'; ?>',
        dept: '<?php echo htmlspecialchars($user_dept); ?>',
        is_it_staff: <?php echo $is_it_staff ? 'true' : 'false'; ?>
    };
</script>

<div class="it-support-container animate-fade">
            <!-- Left Sidebar: Ticket List -->
            <div class="it-sidebar">
                <div class="it-sidebar-header">
                    <h5><?php echo $is_it_staff ? 'Support Queue' : 'My Tickets'; ?></h5>
                    <?php if (!$is_it_staff): ?>
                        <button class="btn btn-sm" id="btn-create-ticket" style="background: var(--it-primary); color: white; border: none; padding: 5px 12px; border-radius: 5px;">
                            <i class="fas fa-plus"></i> Create Ticket
                        </button>
                    <?php endif; ?>
                </div>
                
                <div class="it-search-wrapper">
                    <div class="it-search-inner">
                        <i class="fas fa-search"></i>
                        <input type="text" id="it-search-input" placeholder="<?php echo $is_it_staff ? 'Search tickets or users...' : 'Search my tickets...'; ?>">
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
                    <!-- Tickets will be loaded here via JS -->
                    <div class="p-4 text-center text-muted small">Loading tickets...</div>
                </div>
            </div>

            <!-- Right Side: Ticket Details / Chat (Dynamic Area) -->
            <div class="it-main-content" id="main-content-area">
                <div class="it-content-placeholder animate-fade">
                    <i class="fas fa-headset" style="font-size: 64px; margin-bottom: 15px; opacity: 0.2;"></i>
                    <h3>IT Support Helpdesk</h3>
                    <p>Track your support requests or create a new ticket for any technical issues.</p>
                </div>
            </div>
        </div>

<?php include 'includes/footer.php'; ?>

<!-- Add IT Support Specific JS -->
<script src="assets/js/it-support.js?v=<?php echo time(); ?>"></script>

<style>
    .content-body{
       padding: 0px;
    }
</style>
