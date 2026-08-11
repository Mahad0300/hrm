<?php
use App\Core\View;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Not Found | HRM</title>
    <base href="<?= defined('BASE_URL') ? View::e(BASE_URL . '/') : '/' ?>">
    <link rel="stylesheet" href="<?= View::asset('css/admin/style.css') ?>">
</head>
<body style="display:flex;align-items:center;justify-content:center;min-height:100vh;font-family:Inter,sans-serif;">
    <div style="text-align:center;padding:2rem;">
        <h1 style="font-size:4rem;margin:0;color:#64748b;">404</h1>
        <p style="color:#475569;margin:1rem 0 1.5rem;">The page you requested could not be found.</p>
        <a href="<?= View::url('dashboard') ?>" style="color:#2563eb;text-decoration:none;font-weight:600;">Go to Dashboard</a>
        &nbsp;·&nbsp;
        <a href="<?= View::url('login') ?>" style="color:#2563eb;text-decoration:none;font-weight:600;">Login</a>
    </div>
</body>
</html>
