<?php
namespace App\Controllers\Admin;

use App\Core\BaseController;

class ShiftController extends BaseController
{
    public function index(): void
    {
        $this->render('shifts');
    }
}

