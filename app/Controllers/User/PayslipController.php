<?php
namespace App\Controllers\User;

use App\Core\BaseController;

class PayslipController extends BaseController
{
    public function index(): void
    {
        $this->render('payroll');
    }

    public function print(): void
    {
        $this->render('payslip-print');
    }
}

