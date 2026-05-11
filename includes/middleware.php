<?php
// includes/middleware.php
require_once dirname(__FILE__) . '/auth_helper.php';

/**
 * Guard for specific modules (admin, hr, user)
 */
function protectModule($allowed_roles) {
    if (!isLoggedIn()) {
        $_SESSION['error'] = 'Please login first to access this page.';
        header('Location: ../index.php');
        exit;
    }

    $current_role = $_SESSION['user_role'] ?? '';
    
    // Check if the current user's role is in the allowed_roles array
    if (!in_array($current_role, $allowed_roles)) {
        $_SESSION['error'] = 'Access Denied: You do not have permission to access the ' . $current_role . ' portal.';
        
        // Redirect based on current role (so they go back to their own dashboard)
        switch ($current_role) {
            case 'Admin':
                header('Location: ../admin/index.php');
                break;
            case 'HR':
                header('Location: ../hr/index.php');
                break;
            case 'Employee':
                header('Location: ../user/index.php');
                break;
            default:
                header('Location: ../index.php');
                break;
        }
        exit;
    }

    // Set cache control headers to prevent browser back button access
    header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1.
    header("Pragma: no-cache"); // HTTP 1.0.
    header("Expires: 0"); // Proxies.
}
?>
