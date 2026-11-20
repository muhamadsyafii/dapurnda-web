<?php

// Salin nilai yang diperlukan ke config.php atau, lebih baik, gunakan
// environment variables pada Apache/cPanel.
return [
    'APP_ENV' => 'production',
    'APP_DEBUG' => '0',
    'APP_URL' => 'https://contoh-domain.com',
    'APP_TIMEZONE' => 'Asia/Jakarta',
    'DB_HOST' => '127.0.0.1',
    'DB_PORT' => '3306',
    'DB_NAME' => 'nama_database',
    'DB_USER' => 'nama_pengguna',
    'DB_PASS' => 'kata_sandi_database',
    'APP_SETUP_KEY' => 'buat-kunci-acak-yang-panjang',
];
