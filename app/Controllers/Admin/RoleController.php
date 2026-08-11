<?php
namespace App\Controllers\Admin;

use App\Core\BaseController;

class RoleController extends BaseController
{
    public function index(): void
    {
        $this->render('role-management');
    }
}

