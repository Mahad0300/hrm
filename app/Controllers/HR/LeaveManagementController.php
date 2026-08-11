<?php
namespace App\Controllers\HR;

use App\Core\BaseController;

class LeaveManagementController extends BaseController
{
    public function index(): void
    {
        $this->render('leave-management');
    }
}

