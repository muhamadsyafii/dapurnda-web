<?php

declare(strict_types=1);

define('APP_ROOT', dirname(__DIR__));
define('PUBLIC_ROOT', defined('PUBLIC_ROOT_OVERRIDE') ? PUBLIC_ROOT_OVERRIDE : APP_ROOT . '/public');

$GLOBALS['app_config'] = require APP_ROOT . '/config/config.php';

require_once __DIR__ . '/helpers.php';

date_default_timezone_set((string) config('app.timezone', 'Asia/Jakarta'));
ini_set('display_errors', config('app.debug', false) ? '1' : '0');
ini_set('log_errors', '1');
ini_set('error_log', APP_ROOT . '/storage/logs/php-error.log');

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_name('dapurnda_session');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => request_base_path() ?: '/',
        'secure' => is_https(),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

send_security_headers();

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/upload.php';
require_once __DIR__ . '/recipes.php';
require_once __DIR__ . '/sponsors.php';
require_once __DIR__ . '/layout.php';
require_once __DIR__ . '/admin_layout.php';

set_exception_handler(static function (Throwable $exception): void {
    error_log($exception->__toString());
    http_response_code(500);

    if (config('app.debug', false)) {
        $message = e($exception->getMessage());
        $detail = '<pre style="white-space:pre-wrap;overflow:auto">' . e($exception->getTraceAsString()) . '</pre>';
    } else {
        $message = 'Terjadi kendala saat memproses permintaan.';
        $detail = '<p>Silakan coba kembali beberapa saat lagi.</p>';
    }

    echo '<!doctype html><html lang="id"><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">';
    echo '<title>Terjadi kendala · DapurNda</title><style>body{font-family:system-ui;background:#fff8ef;color:#2d241f;padding:8vw}main{max-width:760px;margin:auto;background:#fff;padding:32px;border-radius:24px;box-shadow:0 20px 60px #4c2e1718}a{color:#c34f2c}</style>';
    echo '<main><p>DapurNda</p><h1>' . $message . '</h1>' . $detail . '<p><a href="' . e(url()) . '">Kembali ke beranda</a></p></main></html>';
});
