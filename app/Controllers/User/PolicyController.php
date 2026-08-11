<?php
namespace App\Controllers\User;

use App\Core\BaseController;

class PolicyController extends BaseController
{
    public function index(): void
    {
        $this->render('policies');
    }

    public function detail(): void
    {
        $this->render('policy-detail');
    }
}

