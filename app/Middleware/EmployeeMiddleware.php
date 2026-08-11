<?php

namespace App\Middleware;

use App\Core\Auth;
use App\Core\View;

class EmployeeMiddleware implements MiddlewareInterface
{
    public function handle(): void
    {
        if (!Auth::isLoggedIn()) {
            $_SESSION['error'] = 'Please login first to access this page.';
            header('Location: ' . View::url('login'));
            exit;
        }

        if (!Auth::isEmployee()) {
            $_SESSION['error'] = 'Access denied: Please use your assigned portal.';
            Auth::redirectByRole();
        }
    }
}
