<?php
namespace App\Controllers\User;

use App\Core\BaseController;

class AttendanceController extends BaseController
{
    public function index(): void
    {
        $this->render('daily-attendance');
    }
}

