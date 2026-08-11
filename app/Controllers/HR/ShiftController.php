<?php
namespace App\Controllers\HR;

use App\Core\BaseController;

class ShiftController extends BaseController
{
    public function index(): void
    {
        $this->render('shifts');
    }
}

