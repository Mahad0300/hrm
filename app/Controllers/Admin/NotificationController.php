<?php
namespace App\Controllers\Admin;

use App\Core\BaseController;

class NotificationController extends BaseController
{
    public function index(): void
    {
        $this->render('notifications');
    }
}

