<?php
/**
 * Email template assets — update HRM_BASE_URL on production.
 */
if (!defined('HRM_BASE_URL')) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    define('HRM_BASE_URL', $protocol . '://' . $host . '/hrmnew');
}

if (!defined('HRM_EMAIL_LOGO_URL')) {
    define('HRM_EMAIL_LOGO_URL', HRM_BASE_URL . '/images/loginimage/logo.png');
}

// SMTP Configuration
if (!defined('SMTP_HOST')) {
    define('SMTP_HOST', 'smtp.gmail.com');
}
if (!defined('SMTP_PORT')) {
    define('SMTP_PORT', 587);
}
if (!defined('SMTP_USER')) {
    define('SMTP_USER', 'zainrtg3@gmail.com');
}
if (!defined('SMTP_PASSWORD')) {
    define('SMTP_PASSWORD', 'wvyv rqkl jkxs kcnw');
}
if (!defined('SMTP_SECURE')) {
    define('SMTP_SECURE', 'tls'); // 'tls' or 'ssl'
}
if (!defined('SMTP_FROM_EMAIL')) {
    define('SMTP_FROM_EMAIL', 'zainrtg3@gmail.com');
}
if (!defined('SMTP_FROM_NAME')) {
    define('SMTP_FROM_NAME', 'Richmond Tech Group');
}

