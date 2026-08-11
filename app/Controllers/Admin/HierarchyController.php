<?php
namespace App\Controllers\Admin;

use App\Core\BaseController;

class HierarchyController extends BaseController
{
    public function index(): void
    {
        $this->render('hierarchy');
    }

    public function settings(): void
    {
        $this->render('hierarchy-settings');
    }
}

