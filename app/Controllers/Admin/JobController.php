<?php
namespace App\Controllers\Admin;

use App\Core\BaseController;

class JobController extends BaseController
{
    public function index(): void
    {
        $this->render('job-list');
    }

    public function create(): void
    {
        $this->render('create-job');
    }

    public function edit(): void
    {
        $this->render('edit-job');
    }
}

