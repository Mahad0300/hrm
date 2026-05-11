<?php
// includes/auth_helper.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Redirect user based on their role
 */
function redirectByRole($role) {
    switch ($role) {
        case 'Admin':
            header('Location: admin/index.php');
            break;
        case 'HR':
            header('Location: hr/index.php');
            break;
        case 'Employee':
            header('Location: user/index.php');
            break;
        default:
            header('Location: index.php');
            break;
    }
    exit;
}

/**
 * Set session data after successful login
 */
function setLoginSession($user) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_email'] = $user['email'];
    $middle_name = !empty($user['middle_name']) ? $user['middle_name'] . ' ' : '';
    $_SESSION['user_name'] = $user['first_name'] . ' ' . $middle_name . $user['last_name'];
    $_SESSION['user_role'] = $user['role'];
    $_SESSION['user_profile_pic'] = $user['profile_pic'] ?? null;
    $_SESSION['logged_in'] = true;
    $_SESSION['login_time'] = time();
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

/**
 * Clear session and logout
 */
function logoutUser() {
    $_SESSION = array();
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
    
    // Clear remember me cookie if exists
    if (isset($_COOKIE['remember_me'])) {
        setcookie('remember_me', '', time() - 3600, '/');
    }
}
?>
