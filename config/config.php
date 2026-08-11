<?php

/**
 * Application configuration — loaded once from public/index.php
 * Pattern matches ChatRox config/config.php
 */

date_default_timezone_set('Asia/Karachi');

if (!defined('ROOT_DIR')) {
    define('ROOT_DIR', dirname(__DIR__));
}

define('APP_DIR', ROOT_DIR . '/app');
define('VIEW_DIR', ROOT_DIR . '/views');
define('CONFIG_DIR', ROOT_DIR . '/config');
define('STORAGE_DIR', ROOT_DIR . '/storage');

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptName = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/hrmnew/public'));
$basePath = str_replace('/public', '', $scriptName);
$dynamicBaseUrl = rtrim($protocol . '://' . $host . $basePath, '/');

$envBaseUrl = $_ENV['BASE_URL'] ?? (function_exists('env') ? env('BASE_URL') : '') ?: '';
$envBasePath = $_ENV['HRM_BASE_PATH'] ?? (function_exists('env') ? env('HRM_BASE_PATH') : '') ?: '';

if ($envBaseUrl !== '') {
    define('BASE_URL', rtrim($envBaseUrl, '/'));
} elseif ($envBasePath !== '') {
    if (str_starts_with($envBasePath, 'http://') || str_starts_with($envBasePath, 'https://')) {
        define('BASE_URL', rtrim($envBasePath, '/'));
    } else {
        define('BASE_URL', rtrim($protocol . '://' . $host . rtrim($envBasePath, '/'), '/'));
    }
} else {
    define('BASE_URL', $dynamicBaseUrl);
}

define('BASE_PATH', rtrim(parse_url(BASE_URL, PHP_URL_PATH) ?: '', '/'));

define('APP_NAME', $_ENV['APP_NAME'] ?? 'Richmond Tech Group HRM');
define('APP_DEBUG', filter_var($_ENV['APP_DEBUG'] ?? 'false', FILTER_VALIDATE_BOOLEAN));

// Load .env variables cleanly
if (class_exists('App\Helpers\EnvHelper')) {
    \App\Helpers\EnvHelper::load(ROOT_DIR . '/.env');
}

define('DB_HOST', \App\Helpers\EnvHelper::get('DB_HOST', 'localhost'));
define('DB_NAME', \App\Helpers\EnvHelper::get('DB_NAME', 'hrm'));
define('DB_USER', \App\Helpers\EnvHelper::get('DB_USER', 'root'));
define('DB_PASS', \App\Helpers\EnvHelper::get('DB_PASS', ''));

// WebSocket configuration
define('WS_PORT', 6001);
