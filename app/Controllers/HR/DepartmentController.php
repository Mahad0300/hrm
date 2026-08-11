<?php
namespace App\Controllers\HR;

use App\Core\BaseController;

class DepartmentController extends BaseController
{
    public function index(): void
    {
        $this->render('department-management');
    }
}

