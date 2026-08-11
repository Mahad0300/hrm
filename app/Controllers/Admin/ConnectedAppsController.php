<?php
namespace App\Controllers\Admin;

use App\Core\BaseController;

class ConnectedAppsController extends BaseController
{
    public function index(): void
    {
        $this->render('connected-apps');
    }
}
