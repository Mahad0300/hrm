<?php

namespace App\Middleware;

use App\Core\Auth;

class GuestMiddleware implements MiddlewareInterface
{
    public function handle(): void
    {
        if (Auth::isLoggedIn()) {
            Auth::redirectByRole();
        }
    }
}
