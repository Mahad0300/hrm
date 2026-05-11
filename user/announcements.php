<?php 
$page_title = "Company Announcements";
$page_subtitle = "Stay updated with the latest news and broadcasts.";
include 'includes/header.php'; 
?>
<?php include 'includes/sidebar.php'; ?>

<div class="grid-cards grid-cards--single" id="announcementsContainer">
    <div class="flex-center py-60 w-full">
        <div class="loader-ripple"><div></div><div></div></div>
    </div>
</div>

<script src="assets/js/announcements.js"></script>
<?php include 'includes/footer.php'; ?>
