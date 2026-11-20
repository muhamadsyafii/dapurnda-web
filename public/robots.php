<?php

declare(strict_types=1);

require __DIR__ . '/app.php';

header('Content-Type: text/plain; charset=utf-8');
?>
User-agent: *
Allow: /
Disallow: /admin/
Disallow: /api/
Disallow: /go.php

Sitemap: <?= url('sitemap.xml') ?>
