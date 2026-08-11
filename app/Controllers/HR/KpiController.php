<?php
namespace App\Controllers\HR;

use App\Core\BaseController;

class KpiController extends BaseController
{
    public function index(): void
    {
        $this->render('kpi-management');
    }

    public function report(): void
    {
        $this->render('kpi-report');
    }

    public function templates(): void
    {
        $this->render('kpi-templates');
    }

    public function evaluate(): void
    {
        $this->render('kpi-evaluate');
    }
}
