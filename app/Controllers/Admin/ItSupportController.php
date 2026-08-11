<?php
namespace App\Controllers\Admin;

use App\Core\BaseController;

class ItSupportController extends BaseController
{
    public function index(): void
    {
        $this->render('it-support');
    }
}

