<?php

namespace App\Middleware;

use App\Core\Auth;
use App\Core\View;

class AdminOrHRMiddleware implements MiddlewareInterface
{
    public function handle(): void
    {
        if (!Auth::isLoggedIn()) {
            $uri = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
            if ($uri && (str_contains($uri, 'new-joining') || str_contains($uri, 'joining-form'))) {
                header('Location: ' . View::url('joining-form'));
                exit;
            }

            $_SESSION['error'] = 'Please login first to access this page.';
            header('Location: ' . View::url('login'));
            exit;
        }

        if (!Auth::isAdmin() && !Auth::isHR()) {
            $_SESSION['error'] = 'Access denied: Please use your assigned portal.';
            Auth::redirectByRole();
        }
    }
}
