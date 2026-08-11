<?php
namespace App\Controllers\HR;

use App\Core\BaseController;

class ActivityLogController extends BaseController
{
    public function index(): void
    {
        $this->render('activity-logs');
    }
}

