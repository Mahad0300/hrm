<?php
/**
 * Assets Router
 * Dynamically resolves relative JS and CSS requests to shared/ or role-specific subdirectories based on active user session.
 */

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$type = $_GET['type'] ?? '';
$file = $_GET['file'] ?? '';

// Prevent path traversal
$file = basename($file);

if ($type !== 'js' && $type !== 'css') {
    http_response_code(404);
    die('Not Found');
}

// Set correct Content-Type header
if ($type === 'js') {
    header('Content-Type: application/javascript; charset=utf-8');
    $subfolders = ['shared', 'admin', 'hr', 'user'];
} else {
    header('Content-Type: text/css; charset=utf-8');
    $subfolders = ['admin', 'hr', 'user'];
}

// Determine active role
$role = $_SESSION['user_role'] ?? '';
$roleFolder = match ($role) {
    'Admin' => 'admin',
    'HR' => 'hr',
    'Employee' => 'user',
    default => ''
};

$basePath = __DIR__ . '/' . $type . '/';

// 1. Try role folder first (if role is active)
if ($roleFolder !== '') {
    $target = $basePath . $roleFolder . '/' . $file;
    if (is_file($target)) {
        readfile($target);
        exit;
    }
}

// 2. Try shared folder next
$target = $basePath . 'shared/' . $file;
if (is_file($target)) {
    readfile($target);
    exit;
}

// 3. Fallback to check all folders in order of precedence
foreach ($subfolders as $folder) {
    if ($folder === $roleFolder || $folder === 'shared') {
        continue;
    }
    $target = $basePath . $folder . '/' . $file;
    if (is_file($target)) {
        readfile($target);
        exit;
    }
}

// 4. If still not found, return 404
http_response_code(404);
echo "/* File not found: {$file} in {$type} */";
