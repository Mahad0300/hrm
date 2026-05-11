<?php
// logout.php
require_once 'includes/auth_helper.php';

// Clear all session data
logoutUser();

// Start a new session just to show a logout success message
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$_SESSION['success'] = "You have been successfully logged out.";

// Clear browser cache to prevent back button access to protected pages
header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1.
header("Pragma: no-cache"); // HTTP 1.0.
header("Expires: 0"); // Proxies.

header("Location: index.php");
exit;
?>
