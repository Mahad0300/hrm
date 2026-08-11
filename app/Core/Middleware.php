<?php
/**
 * Middleware Handler
 * From: includes/middleware.php
 */

namespace App\Core;

use App\Core\View;

class Middleware
{
    /**
     * Protect module access by role
     */
    public static function protectModule(array $allowedRoles): void
    {
        if (!Auth::isLoggedIn()) {
            $_SESSION['error'] = 'Please login first to access this page.';
            header('Location: ' . View::url('login'));
            exit;
        }

        $currentRole = Auth::role();
        
        if (!in_array($currentRole, $allowedRoles, true)) {
            $_SESSION['error'] = 'Access denied: Please use your assigned portal.';
            Auth::redirectByRole();
        }

        // Set cache control headers to prevent browser back-button access
        self::setCacheHeaders();
    }

    /**
     * Require authentication
     */
    public static function requireAuth(): void
    {
        if (!Auth::isLoggedIn()) {
            $_SESSION['error'] = 'Please login first.';
            header('Location: ' . View::url('login'));
            exit;
        }

        self::setCacheHeaders();
    }

    /**
     * Require specific role
     */
    public static function requireRole(string $role): void
    {
        self::requireAuth();
        
        if (Auth::role() !== $role) {
            http_response_code(403);
            die("Unauthorized: $role access required.");
        }
    }

    /**
     * Require Admin role
     */
    public static function requireAdmin(): void
    {
        self::requireRole('Admin');
    }

    /**
     * Require HR role
     */
    public static function requireHR(): void
    {
        self::requireRole('HR');
    }

    /**
     * Require Employee role
     */
    public static function requireEmployee(): void
    {
        self::requireRole('Employee');
    }

    /**
     * Require Admin or HR
     */
    public static function requireAdminOrHR(): void
    {
        self::requireAuth();
        
        if (!Auth::isAdmin() && !Auth::isHR()) {
            http_response_code(403);
            die('Unauthorized: Admin or HR access required.');
        }
    }

    /**
     * Set cache control headers
     */
    private static function setCacheHeaders(): void
    {
        header("Cache-Control: no-cache, no-store, must-revalidate");
        header("Pragma: no-cache");
        header("Expires: 0");
    }
}
?>
