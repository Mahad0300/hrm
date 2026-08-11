<?php
namespace App\Controllers\User;

use App\Core\BaseController;

class ProfileController extends BaseController
{
    public function index(): void
    {
        $this->render('profile');
    }
}

