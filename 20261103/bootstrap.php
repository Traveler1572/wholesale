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
function e($value): string { return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function url(string $path): string { global $config; return rtrim($config['base_path'], '/') . '/' . ltrim($path, '/'); }
function redirect(string $path): void { header('Location: ' . url($path)); exit; }
function csrf(): string { return $_SESSION['csrf'] ??= bin2hex(random_bytes(32)); }
function csrfInput(): string { return '<input type="hidden" name="csrf" value="' . e(csrf()) . '">'; }
function checkCsrf(): void { if (!hash_equals(csrf(), (string)($_POST['csrf'] ?? ''))) { http_response_code(403); exit('操作の有効期限が切れました。画面を再読み込みしてください。'); } }
function requireLogin(): void { if (empty($_SESSION['user'])) redirect('/app/login.php'); }
function flash(string $message, string $type = 'success'): void { $_SESSION['flash'] = [$message, $type]; }
function money($value): string { return '¥' . number_format((float)$value); }
function positiveInt($value, string $label, int $max = 1000000): int {
    $n = filter_var($value, FILTER_VALIDATE_INT);
    if ($n === false || $n < 1 || $n > $max) throw new DomainException($label . 'は1～' . $max . '以下の整数で入力してください。');
    return $n;
}
function validDate(string $date): string {
    
}
?>