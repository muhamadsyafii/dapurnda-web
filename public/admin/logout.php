<?php

declare(strict_types=1);

require dirname(__DIR__) . '/app.php';
logout_admin();
flash('success', 'Anda telah keluar dari dashboard.');
redirect('admin/index.php');
