<?php
namespace App\Controllers\Admin;

use App\Core\BaseController;

class NewJoiningController extends BaseController
{
    public function index(): void
    {
        $this->render('new-joining');
    }
}

