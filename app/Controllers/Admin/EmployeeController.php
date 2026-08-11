<?php
namespace App\Controllers\Admin;

use App\Core\BaseController;

class EmployeeController extends BaseController
{
    public function index(): void
    {
        $this->render('employees');
    }

    public function profile(): void
    {
        $this->render('employee-profile');
    }
}

