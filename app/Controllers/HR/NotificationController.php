<?php
namespace App\Controllers\HR;

use App\Core\BaseController;

class NotificationController extends BaseController
{
    public function index(): void
    {
        $this->render('notifications');
    }
}

