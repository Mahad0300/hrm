<?php

namespace App\Helpers;

/**
 * CSRF Protection Helper
 * Generates and verifies anti-CSRF tokens for forms and AJAX requests.
 */
class CSRFToken
{
    /**
     * Generate or retrieve current session CSRF token
     */
    public static function generate(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }

    /**
     * Return hidden HTML input field containing CSRF token
     */
    public static function field(): string
    {
        $token = self::generate();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }

    /**
     * Verify if the provided token matches session token
     */
    public static function verify(?string $token): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $sessionToken = $_SESSION['csrf_token'] ?? '';
        if (empty($sessionToken) || empty($token)) {
            return false;
        }

        return hash_equals($sessionToken, $token);
    }

    /**
     * Extract token from POST/GET parameter or HTTP request header (X-CSRF-TOKEN)
     */
    public static function getRequestToken(): ?string
    {
        if (!empty($_POST['csrf_token'])) {
            return (string) $_POST['csrf_token'];
        }

        if (!empty($_GET['csrf_token'])) {
            return (string) $_GET['csrf_token'];
        }

        $headers = function_exists('getallheaders') ? getallheaders() : [];
        foreach ($headers as $key => $value) {
            if (strtolower($key) === 'x-csrf-token') {
                return (string) $value;
            }
        }

        if (!empty($_SERVER['HTTP_X_CSRF_TOKEN'])) {
            return (string) $_SERVER['HTTP_X_CSRF_TOKEN'];
        }

        return null;
    }

    /**
     * Validate incoming state-changing request (POST, PUT, DELETE, PATCH)
     * Throws an exception or exits with 403 if invalid.
     */
    public static function verifyRequest(): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        if (in_array(strtoupper($method), ['POST', 'PUT', 'DELETE', 'PATCH'], true)) {
            $token = self::getRequestToken();
            if (!self::verify($token)) {
                http_response_code(403);
                header('Content-Type: application/json');
                echo json_encode([
                    'status' => 'error',
                    'message' => 'CSRF validation failed. Invalid or missing security token.'
                ]);
                exit;
            }
        }
    }
}
