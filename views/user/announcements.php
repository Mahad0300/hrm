<?php 
$page_title = "Company Announcements";
$page_subtitle = "Stay updated with the latest news and broadcasts.";
include __DIR__ . '/../partials/user/header.php'; 
?>
<?php include __DIR__ . '/../partials/user/sidebar.php'; ?>

<div class="grid-cards grid-cards--single" id="announcementsContainer">
    <div class="flex-center py-60 w-full">
        <div class="loader-ripple"><div></div><div></div></div>
    </div>
</div>

<script src="<?= \App\Core\View::asset('js/user/announcements.js') ?>"></script>
<?php include __DIR__ . '/../partials/user/footer.php'; ?>

