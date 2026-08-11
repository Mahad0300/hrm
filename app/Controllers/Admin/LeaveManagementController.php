<?php
namespace App\Controllers\Admin;

use App\Core\BaseController;

class LeaveManagementController extends BaseController
{
    public function index(): void
    {
        $this->render('leave-management');
    }
}

