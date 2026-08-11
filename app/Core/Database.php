<?php
/**
 * Database Connection Handler
 * Centralized PDO connection with error handling
 * From: includes/db_connect.php
 */

namespace App\Core;

use PDO;

class Database
{
    private static ?PDO $connection = null;

    /**
     * Get singleton PDO connection
     */
    public static function connection(): PDO
    {
        if (self::$connection === null) {
            self::connect();
        }
        return self::$connection;
    }

    /**
     * Initialize database connection
     */
    private static function connect(): void
    {
        $host = defined('DB_HOST') ? DB_HOST : \App\Helpers\EnvHelper::get('DB_HOST', 'localhost');
        $db = defined('DB_NAME') ? DB_NAME : \App\Helpers\EnvHelper::get('DB_NAME', 'hrm');
        $user = defined('DB_USER') ? DB_USER : \App\Helpers\EnvHelper::get('DB_USER', 'root');
        $pass = defined('DB_PASS') ? DB_PASS : \App\Helpers\EnvHelper::get('DB_PASS', '');
        $charset = 'utf8mb4';

        $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        try {
            self::$connection = new PDO($dsn, $user, $pass, $options);
            
            // Set timezone
            date_default_timezone_set('Asia/Karachi');
            $now = new \DateTime();
            $mins = $now->getOffset() / 60;
            $sgn = ($mins < 0 ? -1 : 1);
            $mins = abs($mins);
            $hrs = floor($mins / 60);
            $mins %= 60;
            $offset = sprintf('%+03d:%02d', $hrs * $sgn, $mins);
            self::$connection->exec("SET time_zone='$offset';");
        } catch (\PDOException $e) {
            error_log('Database connection failed: ' . $e->getMessage());
            if (defined('APP_DEBUG') && APP_DEBUG) {
                die('Database connection failed: ' . htmlspecialchars($e->getMessage()) . '<br><br>Host: ' . htmlspecialchars($host) . ' | DB: ' . htmlspecialchars($db) . ' | User: ' . htmlspecialchars($user));
            }
            // Show exact error if connection fails during initial setup to help diagnose
            die('Unable to connect to the database. Error: ' . htmlspecialchars($e->getMessage()) . '<br>Please check DB_HOST (try 127.0.0.1 or localhost), DB_NAME, DB_USER, and DB_PASS in .env.');
        }
    }

    /**
     * Reset connection (for testing)
     */
    public static function reset(): void
    {
        self::$connection = null;
    }
}

/**
 * Helper function to get environment variables
 * From: includes/env_helper.php
 */
if (!function_exists('env')) {
    function env(string $key, $default = null)
    {
        return $_ENV[$key] ?? getenv($key) ?: $default;
    }
}
?>
