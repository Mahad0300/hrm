<?php
/**
 * Rate Limiter Helper
 * From: includes/api/rate_limiter.php
 */

namespace App\Helpers;

class RateLimiter
{
    /**
     * Get rate limit storage path for an action
     */
    public static function storagePath(string $action): string
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $key = md5($ip . '_' . $action);
        $rateDir = dirname(__DIR__, 2) . '/storage/rate_limits/';
        
        if (!is_dir($rateDir)) {
            @mkdir($rateDir, 0755, true);
        }
        
        return $rateDir . $key . '.json';
    }

    /**
     * Check if an action is rate-limited
     */
    public static function isLimited(string $action, int $maxHits = 10, int $windowSec = 900): bool
    {
        $file = self::storagePath($action);
        
        if (!file_exists($file)) {
            return false;
        }

        $raw = @file_get_contents($file);
        $data = $raw ? json_decode($raw, true) : null;
        
        if (!is_array($data)) {
            return false;
        }

        $now = time();
        if (($data['blocked_until'] ?? 0) > $now) {
            return true;
        }

        $hits = array_filter($data['hits'] ?? [], function ($timestamp) use ($now, $windowSec) {
            return $timestamp > ($now - $windowSec);
        });

        return count($hits) >= $maxHits;
    }

    /**
     * Record a hit for rate limiting
     */
    public static function recordHit(string $action, int $maxHits = 10, int $windowSec = 900): bool
    {
        $file = self::storagePath($action);
        $now = time();
        $blockDuration = 3600; // 1 hour

        $data = [];
        if (file_exists($file)) {
            $raw = @file_get_contents($file);
            $data = $raw ? json_decode($raw, true) : [];
        }

        if (!is_array($data)) {
            $data = [];
        }

        // Check if already blocked
        if (($data['blocked_until'] ?? 0) > $now) {
            return false;
        }

        // Clean old hits
        $data['hits'] = array_filter($data['hits'] ?? [], function ($timestamp) use ($now, $windowSec) {
            return $timestamp > ($now - $windowSec);
        });

        // Add new hit
        $data['hits'][] = $now;

        // Check if limit exceeded
        if (count($data['hits']) > $maxHits) {
            $data['blocked_until'] = $now + $blockDuration;
            file_put_contents($file, json_encode($data), LOCK_EX);
            return false;
        }

        file_put_contents($file, json_encode($data), LOCK_EX);
        return true;
    }
}
?>
