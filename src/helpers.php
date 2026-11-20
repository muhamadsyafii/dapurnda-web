<?php

declare(strict_types=1);

function config(string $key, mixed $default = null): mixed
{
    $value = $GLOBALS['app_config'] ?? [];
    foreach (explode('.', $key) as $segment) {
        if (!is_array($value) || !array_key_exists($segment, $value)) {
            return $default;
        }
        $value = $value[$segment];
    }
    return $value;
}

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function is_https(): bool
{
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
}

function request_base_path(): string
{
    $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/index.php');
    $directory = rtrim(dirname($script), '/.');
    if (in_array(basename($directory), ['admin', 'api'], true)) {
        $directory = rtrim(dirname($directory), '/.');
    }
    return $directory === '/' ? '' : $directory;
}

function request_host(): string
{
    $host = (string) ($_SERVER['HTTP_HOST'] ?? '127.0.0.1:8000');
    return preg_match('/^[a-z0-9.-]+(?::\d{1,5})?$/i', $host) ? $host : 'localhost';
}

function base_url(): string
{
    $configured = (string) config('app.url', '');
    if ($configured !== '') {
        return $configured;
    }

    return (is_https() ? 'https' : 'http') . '://' . request_host() . request_base_path();
}

function url(string $path = '', array $query = []): string
{
    $target = rtrim(base_url(), '/');
    if ($path !== '') {
        $target .= '/' . ltrim($path, '/');
    }
    if ($query !== []) {
        $target .= '?' . http_build_query($query);
    }
    return $target;
}

function asset(string $path): string
{
    $file = PUBLIC_ROOT . '/assets/' . ltrim($path, '/');
    $version = is_file($file) ? (string) filemtime($file) : '1';
    return url('assets/' . ltrim($path, '/')) . '?v=' . rawurlencode($version);
}

function recipe_photo_url(?string $filename): ?string
{
    if (!$filename) {
        return null;
    }
    if (str_starts_with($filename, 'asset:')) {
        return asset('images/recipes/' . substr($filename, 6));
    }
    return url('uploads/recipes/' . rawurlencode($filename));
}

function recipe_photo_dimensions(?string $filename): array
{
    if (!$filename) {
        return [1200, 630];
    }
    $path = str_starts_with($filename, 'asset:')
        ? PUBLIC_ROOT . '/assets/images/recipes/' . substr($filename, 6)
        : PUBLIC_ROOT . '/uploads/recipes/' . basename($filename);
    $size = is_file($path) ? @getimagesize($path) : false;
    return $size ? [(int) $size[0], (int) $size[1]] : [1200, 630];
}

function current_url(): string
{
    $uri = $_SERVER['REQUEST_URI'] ?? '/';
    return (is_https() ? 'https' : 'http') . '://' . request_host() . $uri;
}

function redirect(string $path, int $status = 302): never
{
    $target = preg_match('~^https?://~i', $path) ? $path : url($path);
    header('Location: ' . $target, true, $status);
    exit;
}

function is_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

function csrf_token(): string
{
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void
{
    $provided = $_POST['_csrf'] ?? '';
    if (!is_string($provided) || !hash_equals(csrf_token(), $provided)) {
        http_response_code(419);
        throw new RuntimeException('Sesi formulir telah berakhir. Muat ulang halaman lalu coba lagi.');
    }
}

function flash(string $type, string $message): void
{
    $_SESSION['_flash'][] = ['type' => $type, 'message' => $message];
}

function pull_flashes(): array
{
    $flashes = $_SESSION['_flash'] ?? [];
    unset($_SESSION['_flash']);
    return is_array($flashes) ? $flashes : [];
}

function remember_input(array $input): void
{
    unset($input['_csrf'], $input['password'], $input['password_confirm']);
    $_SESSION['_old'] = $input;
}

function old(string $key, mixed $default = ''): mixed
{
    return $_SESSION['_old'][$key] ?? $default;
}

function clear_old_input(): void
{
    unset($_SESSION['_old']);
}

function slugify(string $value): string
{
    $value = trim(mb_strtolower($value));
    if (function_exists('transliterator_transliterate')) {
        $value = transliterator_transliterate('Any-Latin; Latin-ASCII', $value) ?: $value;
    }
    $value = preg_replace('/[^a-z0-9]+/i', '-', $value) ?? '';
    return trim($value, '-') ?: 'resep';
}

function request_ip(): string
{
    return substr((string) ($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'), 0, 45);
}

function format_date(?string $date, bool $withTime = false): string
{
    if (!$date) {
        return '—';
    }
    $format = $withTime ? 'd M Y, H.i' : 'd M Y';
    return (new DateTimeImmutable($date, new DateTimeZone('UTC')))
        ->setTimezone(new DateTimeZone((string) config('app.timezone', 'Asia/Jakarta')))
        ->format($format);
}

function local_today(): string
{
    return (new DateTimeImmutable('now', new DateTimeZone((string) config('app.timezone', 'Asia/Jakarta'))))->format('Y-m-d');
}

function send_security_headers(): void
{
    if (headers_sent()) {
        return;
    }
    header_remove('X-Powered-By');
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
    header("Content-Security-Policy: default-src 'self'; img-src 'self' data: blob:; style-src 'self' 'unsafe-inline'; script-src 'self'; font-src 'self'; connect-src 'self'; form-action 'self'; frame-ancestors 'self'; base-uri 'self'; object-src 'none'");
}

function json_response(array $data, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    exit;
}

function checked(bool $condition): string
{
    return $condition ? ' checked' : '';
}

function selected(string|int|null $value, string|int|null $expected): string
{
    return (string) $value === (string) $expected ? ' selected' : '';
}
