<?php

namespace App\Core;

class Router
{
    /** @var array<string, list<array{pattern: string, action: string, middlewares: list<string>}>> */
    protected array $routes = [];

    public function get(string $route, string|callable $controllerAction, array $middlewares = []): void
    {
        $this->addRoute('GET', $route, $controllerAction, $middlewares);
    }

    public function post(string $route, string|callable $controllerAction, array $middlewares = []): void
    {
        $this->addRoute('POST', $route, $controllerAction, $middlewares);
    }

    public function put(string $route, string|callable $controllerAction, array $middlewares = []): void
    {
        $this->addRoute('PUT', $route, $controllerAction, $middlewares);
    }

    public function delete(string $route, string|callable $controllerAction, array $middlewares = []): void
    {
        $this->addRoute('DELETE', $route, $controllerAction, $middlewares);
    }

    public function patch(string $route, string|callable $controllerAction, array $middlewares = []): void
    {
        $this->addRoute('PATCH', $route, $controllerAction, $middlewares);
    }

    protected function addRoute(string $method, string $route, string|callable $controllerAction, array $middlewares = []): void
    {
        $routeRegex = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<\1>[a-zA-Z0-9_\-\.\@\+\%]+)', $route);
        $routeRegex = '#^' . $routeRegex . '$#';

        $this->routes[$method][] = [
            'pattern' => $routeRegex,
            'action'  => $controllerAction,
            'middlewares' => $middlewares,
        ];
    }

    public function dispatch(string $url): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        
        // Match ChatRox style URL parsing
        $url = '/' . ltrim(parse_url($url, PHP_URL_PATH) ?: '', '/');
        $url = rtrim($url, '/') ?: '/';

        // Translate legacy .php paths (with or without role prefixes) to clean router paths
        $legacyMap = [
            'index'                 => '/dashboard',
            'dashboard'             => '/dashboard',
            'employees'             => '/employees',
            'attendance'            => '/attendance',
            'leave-management'      => '/leave',
            'leave'                 => '/leave',
            'new-joining'           => '/new-joining',
            'joining-form'          => '/joining-form',
            'new-joining-form'      => '/joining-form',
            'hierarchy'             => '/hierarchy',
            'kpi-management'        => '/kpi',
            'kpi'                   => '/kpi',
            'event-calendar'        => '/events',
            'events'                => '/events',
            'job-list'              => '/jobs',
            'jobs'                  => '/jobs',
            'create-job'            => '/jobs/create',
            'edit-job'              => '/jobs/edit',
            'job-candidates'        => '/recruitment',
            'recruitment'           => '/recruitment',
            'candidate-detail'      => '/recruitment/detail',
            'interviews'            => '/recruitment/interviews',
            'payroll'               => '/payroll',
            'payroll-settings'      => '/payroll/settings',
            'activity-logs'         => '/activity-logs',
            'announcements'         => '/announcements',
            'notifications'         => '/notifications',
            'it-support'            => '/it-support',
            'shifts'                => '/shifts',
            'department-management' => '/departments',
            'departments'           => '/departments',
            'policy-management'     => '/policy-management',
            'hierarchy-settings'    => '/hierarchy/settings',
            'employee-profile'      => '/employees/profile',
            'attendance-log'        => '/attendance/log',
            'kpi-report'            => '/kpi/report',
            'payslip-print'         => '/payslip/print',
            'profile'               => '/profile',
            'policies'              => '/policies',
            'policy-detail'         => '/policies/detail',
            'daily-attendance'      => '/daily-attendance',
        ];

        if (preg_match('#^/(?:admin|hr|user|employee)/(.*)$#i', $url, $m)) {
            $url = '/' . $m[1];
        }

        if (preg_match('#^(.*)\.php$#i', $url, $m)) {
            $cleanPath = $m[1];
            $page = strtolower(basename($cleanPath));
            if (isset($legacyMap[$page])) {
                $url = $legacyMap[$page];
            } else {
                $url = $cleanPath;
            }
        }

        $methodRoutes = $this->routes[$method] ?? [];

        foreach ($methodRoutes as $route) {
            if (!preg_match($route['pattern'], $url, $matches)) {
                continue;
            }

            // Execute middlewares registered for this route
            foreach ($route['middlewares'] as $middlewareClass) {
                if (class_exists($middlewareClass)) {
                    $middleware = new $middlewareClass();
                    $middleware->handle();
                } else {
                    throw new \RuntimeException("Middleware class {$middlewareClass} not found.");
                }
            }

            $params = array_values(array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY));
            if (is_callable($route['action'])) {
                call_user_func_array($route['action'], $params);
            } else {
                $this->executeAction($route['action'], $params);
            }

            return;
        }

        $this->send404($url);
    }

    protected function executeAction(string $action, array $params = []): void
    {
        [$controllerName, $methodName] = explode('@', $action);

        // Dynamic subfolder namespace resolution based on user session role
        if (str_starts_with($controllerName, 'App\\') || str_starts_with($controllerName, '\\App\\')) {
            $controllerClass = ltrim($controllerName, '\\');
        } elseif (str_contains($controllerName, '\\')) {
            $controllerClass = 'App\\Controllers\\' . ltrim($controllerName, '\\');
        } else {
            $role = Auth::role();
            $namespace = match ($role) {
                'Admin' => 'Admin',
                'HR' => 'HR',
                'Employee' => 'User',
                default => 'Public',
            };

            $controllerClass = 'App\\Controllers\\' . $namespace . '\\' . $controllerName;
            
            // Fallback to Public namespace if role-specific controller class does not exist
            if (!class_exists($controllerClass)) {
                $controllerClass = 'App\\Controllers\\Public\\' . $controllerName;
            }
        }

        if (!class_exists($controllerClass)) {
            throw new \RuntimeException("Controller class {$controllerClass} not found.");
        }

        $controller = new $controllerClass();

        if (!method_exists($controller, $methodName)) {
            throw new \RuntimeException("Method {$methodName} not found in controller {$controllerClass}.");
        }

        call_user_func_array([$controller, $methodName], $params);
    }

    protected function send404(string $url): void
    {
        http_response_code(404);
        $not_found_path = ltrim($url, '/');
        $errorFile = VIEW_DIR . '/errors/404.php';
        if (is_file($errorFile)) {
            require $errorFile;
        } else {
            echo '<h1>404 Not Found</h1>';
        }
        exit;
    }
}
