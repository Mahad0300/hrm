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
