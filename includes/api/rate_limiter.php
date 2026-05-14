<?php
// includes/api/rate_limiter.php
// Simple file-based rate limiter for public endpoints

/**
 * Check if a request from the given IP is within rate limits.
 * Returns true if allowed, false if rate limited.
 * 
 * @param string $action   The action being rate-limited (e.g., 'check_email', 'submit_application')
 * @param int    $maxHits  Maximum requests allowed within the window
 * @param int    $windowSec Time window in seconds
 * @return bool
 */
function checkRateLimit($action, $maxHits = 10, $windowSec = 60) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $key = md5($ip . '_' . $action);
    
    $rateDir = dirname(__DIR__, 2) . '/storage/rate_limits/';
    if (!is_dir($rateDir)) {
        @mkdir($rateDir, 0755, true);
    }
    
    $file = $rateDir . $key . '.json';
    
    $now = time();
    $data = ['hits' => [], 'blocked_until' => 0];
    
    if (file_exists($file)) {
        $raw = @file_get_contents($file);
        $data = $raw ? json_decode($raw, true) : $data;
    }
    
    // Check if currently blocked
    if (isset($data['blocked_until']) && $data['blocked_until'] > $now) {
        return false;
    }
    
    // Clean old hits outside the window
    $data['hits'] = array_filter($data['hits'] ?? [], function($timestamp) use ($now, $windowSec) {
        return $timestamp > ($now - $windowSec);
    });
    
    // Check if over limit
    if (count($data['hits']) >= $maxHits) {
        $data['blocked_until'] = $now + $windowSec; // Block for the full window
        @file_put_contents($file, json_encode($data));
        return false;
    }
    
    // Record this hit
    $data['hits'][] = $now;
    @file_put_contents($file, json_encode($data));
    
    return true;
}

/**
 * Send a rate limit exceeded response and exit.
 */
function rateLimitExceeded() {
    http_response_code(429);
    echo json_encode(['status' => 'error', 'message' => 'Too many requests. Please try again later.']);
    exit;
}
