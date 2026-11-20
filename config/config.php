<?php

declare(strict_types=1);

return [
    'app' => [
        'name' => getenv('APP_NAME') ?: 'DapurNda',
        'url' => rtrim(getenv('APP_URL') ?: '', '/'),
        'environment' => getenv('APP_ENV') ?: 'local',
        'debug' => filter_var(getenv('APP_DEBUG') ?: '1', FILTER_VALIDATE_BOOL),
        'timezone' => getenv('APP_TIMEZONE') ?: 'Asia/Jakarta',
    ],
    'database' => [
        'host' => getenv('DB_HOST') ?: '127.0.0.1',
        'port' => (int) (getenv('DB_PORT') ?: 3306),
        'name' => getenv('DB_NAME') ?: 'resep',
        'user' => getenv('DB_USER') ?: 'root',
        'password' => getenv('DB_PASS') ?: '',
        'charset' => 'utf8mb4',
    ],
    'security' => [
        // Ganti melalui APP_SETUP_KEY sebelum website dapat diakses publik.
        'setup_key' => getenv('APP_SETUP_KEY') ?: 'ganti-kunci-setup-ini',
        // Pengguna boleh memilih foto asli; yang disimpan ditargetkan <= 500 KB.
        'upload_max_bytes' => 500 * 1024,
        'upload_source_max_bytes' => 25 * 1024 * 1024,
        'upload_max_pixels' => 20_000_000,
        'submission_limit' => 5,
        'submission_window_minutes' => 60,
    ],
    'features' => [
        // Disiapkan untuk fase berikutnya, tetapi belum ditampilkan ke publik.
        'public_sponsors' => false,
    ],
];
