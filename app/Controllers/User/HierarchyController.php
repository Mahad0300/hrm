<?php
namespace App\Controllers\User;

use App\Core\BaseController;

class HierarchyController extends BaseController
{
    public function index(): void
    {
        $this->render('hierarchy');
    }
}

