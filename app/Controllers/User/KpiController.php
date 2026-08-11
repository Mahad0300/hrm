<?php
namespace App\Controllers\User;

use App\Core\BaseController;

class KpiController extends BaseController
{
    public function index(): void
    {
        $this->render('kpi');
    }
}
