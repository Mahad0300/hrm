<?php
namespace App\Controllers\HR;

use App\Core\BaseController;

class NewJoiningController extends BaseController
{
    public function index(): void
    {
        $this->render('new-joining');
    }
}

