<?php
/**
 * Single Entry Point / Front Controller
 * ChatRox-style dynamic regex routing bootstrap
 */

define('ROOT_DIR', dirname(__DIR__));

require_once ROOT_DIR . '/vendor/autoload.php';
require_once ROOT_DIR . '/config/config.php';

use App\Core\ErrorHandler;
use App\Core\Router;
use App\Core\Auth;
use App\Middleware\AuthMiddleware;
use App\Middleware\GuestMiddleware;
use App\Middleware\AdminMiddleware;
use App\Middleware\HRMiddleware;
use App\Middleware\EmployeeMiddleware;
use App\Middleware\AdminOrHRMiddleware;

ErrorHandler::register();

$router = new Router();

// Public Routes
$router->get('/', 'PublicController@login', [GuestMiddleware::class]);
$router->post('/', 'PublicController@login', [GuestMiddleware::class]);
$router->get('/logout', 'PublicController@logout');
$router->get('/job-apply', 'PublicController@jobApply');
$router->get('/walk-in', 'PublicController@walkIn');
$router->get('/joining-form', 'PublicController@joiningForm');
$router->get('/new-joining-form', 'PublicController@joiningForm');

// Authenticated Shared Routes (resolved dynamically inside Router based on role)
$router->get('/dashboard', 'DashboardController@index', [AuthMiddleware::class]);
$router->get('/leave', 'LeaveManagementController@index', [AuthMiddleware::class]);
$router->get('/hierarchy', 'HierarchyController@index', [AuthMiddleware::class]);
$router->get('/events', 'EventController@index', [AuthMiddleware::class]);
$router->get('/payroll', 'PayrollController@index', [AuthMiddleware::class]);
$router->get('/announcements', 'AnnouncementController@index', [AuthMiddleware::class]);
$router->get('/notifications', 'NotificationController@index', [AuthMiddleware::class]);
$router->get('/it-support', 'ItSupportController@index', [AuthMiddleware::class]);

// Admin & HR Shared Routes (resolved dynamically inside Router based on role)
$router->get('/employees', 'EmployeeController@index', [AdminOrHRMiddleware::class]);
$router->get('/employees/profile', 'EmployeeController@profile', [AdminOrHRMiddleware::class]);
$router->get('/attendance', 'AttendanceController@index', [AdminOrHRMiddleware::class]);
$router->get('/attendance/log', 'AttendanceController@log', [AdminOrHRMiddleware::class]);
$router->get('/new-joining', 'NewJoiningController@index', [AdminOrHRMiddleware::class]);
$router->get('/hierarchy/settings', 'HierarchyController@settings', [AdminOrHRMiddleware::class]);
$router->get('/kpi', 'KpiController@index', [AuthMiddleware::class]);
$router->get('/kpi/report', 'KpiController@report', [AdminOrHRMiddleware::class]);
$router->get('/kpi/templates', 'KpiController@templates', [AdminOrHRMiddleware::class]);
$router->get('/kpi/evaluate', 'KpiController@evaluate', [AdminOrHRMiddleware::class]);
$router->get('/jobs', 'JobController@index', [AdminOrHRMiddleware::class]);
$router->get('/jobs/create', 'JobController@create', [AdminOrHRMiddleware::class]);
$router->get('/jobs/edit', 'JobController@edit', [AdminOrHRMiddleware::class]);
$router->get('/recruitment', 'RecruitmentController@candidates', [AdminOrHRMiddleware::class]);
$router->get('/recruitment/walk-in', 'RecruitmentController@walkInCandidates', [AdminOrHRMiddleware::class]);
$router->get('/recruitment/detail', 'RecruitmentController@detail', [AdminOrHRMiddleware::class]);
$router->get('/recruitment/interviews', 'RecruitmentController@interviews', [AdminOrHRMiddleware::class]);
$router->get('/payroll/settings', 'PayrollController@settings', [AdminOrHRMiddleware::class]);
$router->get('/activity-logs', 'ActivityLogController@index', [AdminOrHRMiddleware::class]);
$router->get('/shifts', 'ShiftController@index', [AdminOrHRMiddleware::class]);
$router->get('/departments', 'DepartmentController@index', [AdminOrHRMiddleware::class]);
$router->get('/policy-management', 'PolicyController@index', [AdminOrHRMiddleware::class]);

// Admin Specific Routes
$router->get('/roles', 'RoleController@index', [AdminMiddleware::class]);
$router->get('/connected-apps', 'App\\Controllers\\Admin\\ConnectedAppsController@index', [AdminMiddleware::class]);

// Employee Specific Routes
$router->get('/policies', 'PolicyController@index', [EmployeeMiddleware::class]);
$router->get('/policies/detail', 'PolicyController@detail', [EmployeeMiddleware::class]);
$router->get('/policies/detail/{slug}', 'PolicyController@detail', [EmployeeMiddleware::class]);
$router->get('/profile', 'ProfileController@index', [EmployeeMiddleware::class]);
$router->get('/daily-attendance', 'App\\Controllers\\User\\AttendanceController@index', [AuthMiddleware::class]);
$router->get('/sheets', 'App\\Controllers\\SheetsController@index', [AuthMiddleware::class]);
$router->get('/sheets/editor', 'App\\Controllers\\SheetsController@editor', [AuthMiddleware::class]);

// Special Payslip Print Route (custom callback for dynamic class routing)
$router->get('/payslip/print', function() {
    $role = Auth::role();
    if ($role === 'Admin' || $role === 'HR') {
        (new AdminOrHRMiddleware())->handle();
        $controller = new App\Controllers\Admin\PayrollController();
        $controller->payslip();
    } else {
        (new EmployeeMiddleware())->handle();
        $controller = new App\Controllers\User\PayslipController();
        $controller->print();
    }
});

// Dispatch request using query string 'url'
$url = isset($_GET['url']) ? rtrim((string) $_GET['url'], '/') : '';

try {
    $router->dispatch($url);
} catch (\Throwable $e) {
    ErrorHandler::handleException($e);
}
