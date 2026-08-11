<?php
namespace App\Controllers\Admin;

use App\Core\BaseController;

class DashboardController extends BaseController
{
    public function index(): void
    {
        $this->render('dashboard');
    }
}

