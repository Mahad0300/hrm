<?php
namespace App\Controllers\Admin;

use App\Core\BaseController;

class ActivityLogController extends BaseController
{
    public function index(): void
    {
        $this->render('activity-logs');
    }
}

