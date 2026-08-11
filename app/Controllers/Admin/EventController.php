<?php
namespace App\Controllers\Admin;

use App\Core\BaseController;

class EventController extends BaseController
{
    public function index(): void
    {
        $this->render('event-calendar');
    }
}

