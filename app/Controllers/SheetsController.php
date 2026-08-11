<?php
namespace App\Controllers;

use App\Core\BaseController;
use App\Core\Auth;

class SheetsController extends BaseController
{
    /**
     * Render the sheets dashboard view
     */
    public function index(): void
    {
        $this->render('sheets', [
            'pageTitle' => 'Google Sheets',
            'userEmail' => $_SESSION['user_email'] ?? '',
            'userName' => $_SESSION['user_name'] ?? ''
        ]);
    }

    /**
     * Render the spreadsheet fullscreen editor view
     */
    public function editor(): void
    {
        $this->render('sheets-editor', [
            'pageTitle' => 'Google Sheets Editor',
            'spreadsheetId' => $_GET['id'] ?? ''
        ]);
    }
}
