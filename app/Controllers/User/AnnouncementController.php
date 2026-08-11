<?php
namespace App\Controllers\User;

use App\Core\BaseController;

class AnnouncementController extends BaseController
{
    public function index(): void
    {
        $this->render('announcements');
    }
}

