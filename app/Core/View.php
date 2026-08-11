<?php

namespace App\Core;


/**
 * URL and view helpers — ChatRox-style (View::url, View::asset).
 */
class View
{
    public static function e(?string $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }

    public static function basePath(): string
    {
        return defined('BASE_PATH') ? BASE_PATH : Auth::getBasePath();
    }

    public static function url(string $slug = 'dashboard'): string
    {
        if ($slug === 'login' || $slug === '') {
            $path = '/';
        } else {
            $path = '/' . str_replace('.', '/', $slug);
        }

        $base = defined('BASE_URL') ? rtrim(BASE_URL, '/') : '';
        return $base . $path;
    }

    public static function to(string $slug = 'dashboard'): string
    {
        return self::url($slug);
    }

    public static function asset(string $path): string
    {
        $path = ltrim(str_replace('\\', '/', $path), '/');
        $base = defined('BASE_URL') ? rtrim(BASE_URL, '/') : rtrim(Auth::getBasePath(), '/');

        if ($base === '') {
            return '/assets/' . $path;
        }

        return $base . '/assets/' . $path;
    }

    public static function image(string $path): string
    {
        $path = ltrim(str_replace('\\', '/', $path), '/');
        $base = defined('BASE_URL') ? rtrim(BASE_URL, '/') : rtrim(Auth::getBasePath(), '/');

        if ($base === '') {
            return '/images/' . $path;
        }

        return $base . '/images/' . $path;
    }

    public static function api(string $suffix = ''): string
    {
        $base = defined('BASE_URL') ? rtrim(BASE_URL, '/') : rtrim(Auth::getBasePath(), '/');
        $api = ($base === '') ? '/assets/api' : $base . '/assets/api';

        if ($suffix === '') {
            return $api;
        }

        return $api . '/' . ltrim($suffix, '/');
    }

    public static function upload(string $relativePath): string
    {
        $relativePath = ltrim(str_replace('\\', '/', $relativePath), '/');
        $base = defined('BASE_URL') ? rtrim(BASE_URL, '/') : rtrim(Auth::getBasePath(), '/');

        if ($base === '') {
            return '/' . $relativePath;
        }

        return $base . '/' . $relativePath;
    }

    public static function jsConfig(): array
    {
        return [
            'baseUrl' => defined('BASE_URL') ? BASE_URL : '',
            'basePath' => self::basePath(),
            'appName' => defined('APP_NAME') ? APP_NAME : 'HRM',
            'csrfToken' => \App\Helpers\CSRFToken::generate(),
        ];
    }
}
