<?php
namespace App\Controllers\Admin;

use App\Core\BaseController;

class PayrollController extends BaseController
{
    public function index(): void
    {
        $this->render('payroll');
    }

    public function settings(): void
    {
        $this->render('payroll-settings');
    }

    public function payslip(): void
    {
        $this->render('payslip-print');
    }
}

