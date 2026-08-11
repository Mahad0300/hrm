<?php
namespace App\Controllers\Admin;

use App\Core\BaseController;

class PolicyController extends BaseController
{
    public function index(): void
    {
        $this->render('policy-management');
    }
}

