<?php
declare(strict_types=1);
date_default_timezone_set('Asia/Tokyo');
$configFile = getenv('WHOLESALE_CONFIG') ?: dirname(__DIR__) . '/config.php';
$config = require is_file($configFile) ? $configFile : dirname(__DIR__) . '/config.example.php';
if (PHP_SAPI !== 'cli') {
    session_name($config['session_name']);
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax', 'secure' => !empty($_SERVER['HTTPS']) !== 'off', 'path' => '/']);
    session_start();
} else { $_SESSION = []; }
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: same-origin');
header("Content-Security-Policy: default-src 'self'; style-src 'self'; script-src 'self'; img-src 'self' data:; frame-ancestors 'none'; form-action 'self'");
header('Cache-Control: no-store');
set_exception_handler(function(Throwable $e): void {
    error_log((string)$e);
});
?>