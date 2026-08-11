<?php
namespace App\Controllers\HR;

use App\Core\BaseController;

class ItSupportController extends BaseController
{
    public function index(): void
    {
        $this->render('it-support');
    }
}

