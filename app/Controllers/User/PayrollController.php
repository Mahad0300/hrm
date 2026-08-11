<?php
namespace App\Controllers\User;

use App\Core\BaseController;

class PayrollController extends BaseController
{
    public function index(): void
    {
        $this->render('payroll');
    }
}

