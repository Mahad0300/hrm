<?php
namespace App\Controllers\Admin;

use App\Core\BaseController;

class DepartmentController extends BaseController
{
    public function index(): void
    {
        $this->render('department-management');
    }
}

