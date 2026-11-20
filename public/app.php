<?php

declare(strict_types=1);

if (!defined('PUBLIC_ROOT_OVERRIDE')) {
    define('PUBLIC_ROOT_OVERRIDE', __DIR__);
}

$privateRoot = rtrim((string) getenv('APP_PRIVATE_ROOT'), '/');
$candidates = array_filter([
    $privateRoot !== '' ? $privateRoot . '/src/bootstrap.php' : null,
    dirname(__DIR__) . '/src/bootstrap.php',
    dirname(__DIR__) . '/resep/src/bootstrap.php',
]);

foreach ($candidates as $bootstrap) {
    if (is_file($bootstrap)) {
        require $bootstrap;
        return;
    }
}

throw new RuntimeException('Kode inti aplikasi tidak ditemukan. Atur APP_PRIVATE_ROOT ke folder privat aplikasi.');
