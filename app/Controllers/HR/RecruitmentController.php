<?php
namespace App\Controllers\HR;

use App\Core\BaseController;

class RecruitmentController extends BaseController
{
    public function candidates(): void
    {
        $this->render('job-candidates');
    }

    public function walkInCandidates(): void
    {
        $this->render('walk-in-candidates');
    }

    public function detail(): void
    {
        $this->render('candidate-detail');
    }

    public function interviews(): void
    {
        $this->render('interviews');
    }
}

