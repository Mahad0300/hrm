<?php
/**
 * Base Controller Class
 * Provides common methods for all controllers
 */

namespace App\Core;

use App\Core\Database;

class BaseController
{
    protected array $data = [];

    /**
     * Render a view with data
     */
    protected function render(string $view, array $data = []): void
    {
        $this->data = array_merge($this->data, $data);
        $pdo = Database::connection();
        extract($this->data);
        
        // Determine view file path based on role
        $role = $_SESSION['user_role'] ?? 'public';
        $rolePath = strtolower($role);
        
        // Map roles to view directories (views/ at project root)
        $viewsRoot = defined('VIEW_DIR') ? VIEW_DIR : ((defined('ROOT_DIR') ? ROOT_DIR : dirname(__DIR__, 2)) . '/views');
        if ($role === 'Admin') {
            $viewPath = $viewsRoot . "/admin/$view.php";
        } elseif ($role === 'HR') {
            $viewPath = $viewsRoot . "/hr/$view.php";
        } elseif ($role === 'Employee') {
            $viewPath = $viewsRoot . "/user/$view.php";
        } else {
            $viewPath = $viewsRoot . "/public/$view.php";
        }

        if (!file_exists($viewPath)) {
            $userViewPath = $viewsRoot . "/user/$view.php";
            if (file_exists($userViewPath)) {
                $viewPath = $userViewPath;
            } else {
                $publicViewPath = $viewsRoot . "/public/$view.php";
                if (file_exists($publicViewPath)) {
                    $viewPath = $publicViewPath;
                } else {
                    die("View not found: $view ($viewPath)");
                }
            }
        }

        require_once $viewPath;
    }

    /**
     * Render partial view
     */
    protected function renderPartial(string $partial, array $data = []): void
    {
        extract($data);
        $viewsRoot = defined('VIEW_DIR') ? VIEW_DIR : ((defined('ROOT_DIR') ? ROOT_DIR : dirname(__DIR__, 2)) . '/views');
        $partialPath = $viewsRoot . "/partials/$partial.php";
        
        if (!file_exists($partialPath)) {
            die("Partial not found: $partial ($partialPath)");
        }
        
        require_once $partialPath;
    }

    /**
     * Redirect to a URL
     */
    protected function redirect(string $url): void
    {
        header("Location: $url");
        exit;
    }

    /**
     * Return JSON response
     */
    protected function json(array $data, int $statusCode = 200): void
    {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($data);
        exit;
    }

    /**
     * Set flash message
     */
    protected function flash(string $type, string $message): void
    {
        $_SESSION['flash'] = [
            'type' => $type,
            'message' => $message
        ];
    }

    /**
     * Get flash message
     */
    protected function getFlash(): ?array
    {
        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);
        return $flash;
    }
}
?>
