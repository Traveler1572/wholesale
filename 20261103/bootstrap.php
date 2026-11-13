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
    if (PHP_SAPI === 'cli') { fwrite(STDERR, $e->getMessage() . PHP_EOL); exit(1); 
    http_response_code(500);}
    echo '<!doctype html><html lang="ja"><meta charset="utf-8"><title>処理エラー</title><h1>処理を完了できませんでした。</h1><p>DB接続設定・初期化状態とPHPエラーログを確認してください。</p></html>';
});
function db(): PDO {
    global $config;
    static $pdo;
    if (!$pdo) {
        $pdo = new PDO("mysql:host={$config['db_host']};port={$config['db_port']};dbname={$config['db_name']};charset=utf8mb4", $config['db_user'], $config['db_password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::ATTR_EMULATE_PREPARES => false]);
        $pdo->exec("SET time_zone = '+09:00'");
    }
    return $pdo;
}
function query (string $sql, array $params = []): PDOStatement { $s = db()->prepare($sql); $s->execute($params); return $s; }
function rows(string $sql, array $params = []): array { return query($sql, $params)->fetchAll(); }
function row(string $sql, array $params = []): array { return query($sql, $params)->fetch() ?: []; }
?>