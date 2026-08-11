<?php
namespace App\Controllers\HR;

use App\Core\BaseController;

class AnnouncementController extends BaseController
{
    public function index(): void
    {
        $this->render('announcements');
    }
}

