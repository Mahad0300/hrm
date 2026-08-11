<?php
/**
 * Environment Helper
 * From: includes/env_helper.php
 */

namespace App\Helpers;

class EnvHelper
{
    private static bool $loaded = false;

    /**
     * Load .env file
     */
    public static function load(?string $path = null): void
    {
        if (self::$loaded) {
            return;
        }

        if ($path === null) {
            $path = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . '.env';
        }

        if (!is_file($path) || !is_readable($path)) {
            self::$loaded = true;
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            self::$loaded = true;
            return;
        }

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }
            if (!str_contains($line, '=')) {
                continue;
            }

            [$name, $value] = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);
            $value = trim($value, "\"'");

            if ($name === '' || array_key_exists($name, $_ENV)) {
                continue;
            }

            putenv("$name=$value");
            $_ENV[$name] = $value;
        }

        self::$loaded = true;
    }

    /**
     * Get environment variable
     */
    public static function get(string $key, $default = '')
    {
        self::load();
        if (array_key_exists($key, $_ENV)) {
            return $_ENV[$key];
        }
        $val = getenv($key);
        return $val !== false ? $val : $default;
    }
}

// Load env on autoload
EnvHelper::load();
?>
