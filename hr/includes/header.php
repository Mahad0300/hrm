<?php
require_once dirname(__DIR__, 2) . '/includes/middleware.php';
protectModule(['HR']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HRM Dashboard | Rtg Corp</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    <?php if (!empty($load_apexcharts)): ?>
        <!-- ApexCharts (admin dashboard — set $load_apexcharts before including this file) -->
        <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <?php endif; ?>
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
</head>

<body>
    <div class="admin-container">