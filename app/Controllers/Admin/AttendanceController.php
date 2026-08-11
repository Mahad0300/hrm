<?php
namespace App\Controllers\Admin;

use App\Core\BaseController;

class AttendanceController extends BaseController
{
    public function index(): void
    {
        $this->render('attendance');
    }

    public function log(): void
    {
        $this->render('attendance-log');
    }
}

