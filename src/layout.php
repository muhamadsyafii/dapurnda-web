<?php

declare(strict_types=1);

function icon(string $name, string $class = ''): string
{
    $paths = [
        'search' => '<circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>',
        'clock' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 1.5"/>',
        'eye' => '<path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z"/><circle cx="12" cy="12" r="2.5"/>',
        'users' => '<path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><path d="M17 11a4 4 0 0 0 0-8M23 21v-2a4 4 0 0 0-3-3.87"/>',
        'arrow' => '<path d="M5 12h14"/><path d="m13 6 6 6-6 6"/>',
        'chevron-right' => '<path d="m9 18 6-6-6-6"/>',
        'arrow-left' => '<path d="M19 12H5"/><path d="m11 18-6-6 6-6"/>',
        'heart' => '<path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3.32.85-4.5 2.1A6 6 0 0 0 7.5 3 5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/>',
        'share' => '<circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><path d="m8.59 10.51 6.83-3.98M8.59 13.49l6.83 3.98"/>',
        'menu' => '<path d="M4 7h16M4 12h16M4 17h16"/>',
        'close' => '<path d="m18 6-12 12M6 6l12 12"/>',
        'check' => '<path d="m20 6-11 11-5-5"/>',
        'chef' => '<path d="M6 13.87A4 4 0 0 1 7.5 6a5 5 0 0 1 9 0 4 4 0 0 1 1.5 7.87V21H6Z"/><path d="M6 17h12"/>',
        'spark' => '<path d="m12 3-1.35 3.65L7 8l3.65 1.35L12 13l1.35-3.65L17 8l-3.65-1.35Z"/><path d="m19 14-.8 2.2L16 17l2.2.8L19 20l.8-2.2L22 17l-2.2-.8Z"/><path d="m5 14-.8 2.2L2 17l2.2.8L5 20l.8-2.2L8 17l-2.2-.8Z"/>',
        'link' => '<path d="M10 13a5 5 0 0 0 7.07.07l2-2A5 5 0 0 0 12 4l-1.15 1.15"/><path d="M14 11a5 5 0 0 0-7.07-.07l-2 2A5 5 0 0 0 12 20l1.15-1.15"/>',
        'lock' => '<rect x="4" y="10" width="16" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3M12 14v3"/>',
        'grid' => '<rect x="3" y="3" width="7" height="7" rx="2"/><rect x="14" y="3" width="7" height="7" rx="2"/><rect x="3" y="14" width="7" height="7" rx="2"/><rect x="14" y="14" width="7" height="7" rx="2"/>',
        'list' => '<path d="M8 6h13M8 12h13M8 18h13"/><path d="M3 6h.01M3 12h.01M3 18h.01"/>',
        'edit' => '<path d="M12 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.38 2.62a2.1 2.1 0 0 1 3 3L12 15l-4 1 1-4Z"/>',
        'home' => '<path d="m3 11 9-8 9 8"/><path d="M5 10v10h14V10M9 20v-6h6v6"/>',
        'utensils' => '<path d="M3 2v7a3 3 0 0 0 6 0V2M6 2v20M14 2v8a3 3 0 0 0 3 3h2V2M19 13v9"/>',
        'gauge' => '<path d="M12 14 15.5 10.5"/><path d="M3.34 19a10 10 0 1 1 17.32 0"/>',
        'plus' => '<path d="M5 12h14M12 5v14"/>',
        'trash' => '<path d="M3 6h18M8 6V4h8v2M19 6l-1 15H6L5 6M10 11v5M14 11v5"/>',
        'grip' => '<circle cx="9" cy="5" r="1" fill="currentColor" stroke="none"/><circle cx="15" cy="5" r="1" fill="currentColor" stroke="none"/><circle cx="9" cy="12" r="1" fill="currentColor" stroke="none"/><circle cx="15" cy="12" r="1" fill="currentColor" stroke="none"/><circle cx="9" cy="19" r="1" fill="currentColor" stroke="none"/><circle cx="15" cy="19" r="1" fill="currentColor" stroke="none"/>',
        'image' => '<rect x="3" y="3" width="18" height="18" rx="3"/><circle cx="9" cy="9" r="2"/><path d="m21 15-5-5L5 21"/>',
        'book-open' => '<path d="M2 4h6a4 4 0 0 1 4 4v13a3 3 0 0 0-3-3H2Z"/><path d="M22 4h-6a4 4 0 0 0-4 4v13a3 3 0 0 1 3-3h7Z"/>',
        'briefcase' => '<rect x="3" y="7" width="18" height="13" rx="2"/><path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M3 12h18M10 12v2h4v-2"/>',
        'mail' => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/>',
        'external-link' => '<path d="M15 3h6v6M10 14 21 3"/><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>',
        'logout' => '<path d="M10 17l5-5-5-5M15 12H3"/><path d="M14 3h5a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-5"/>',
        'bell' => '<path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4"/>',
        'calendar' => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 10h18"/>',
    ];
    return '<svg class="icon ' . e($class) . '" viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' . ($paths[$name] ?? '') . '</svg>';
}

function render_admin_sidebar(string $active, array $badges = []): void
{
    $items = [
        'dashboard' => ['Ringkasan', 'gauge', 'admin/index.php'],
        'recipes' => ['Resep', 'book-open', 'admin/recipes.php'],
        'sponsors' => ['Sponsor', 'briefcase', 'admin/sponsors.php'],
        'inquiries' => ['Pesan', 'mail', 'admin/inquiries.php'],
    ];
    $username = (string) ($_SESSION['admin_username'] ?? 'Admin');
    ?>
<aside class="admin-sidebar" aria-label="Navigasi pengelola">
    <div class="admin-sidebar-inner">
        <a class="admin-brand" href="<?= e(url('admin/index.php')) ?>" aria-label="Dashboard DapurNda">
            <span class="brand-mark"><?= icon('chef') ?></span>
            <span><strong>DapurNda</strong><small>Area pengelola</small></span>
        </a>
        <a class="admin-view-site" href="<?= e(url()) ?>" target="_blank" rel="noopener"><?= icon('external-link') ?><span>Lihat website</span></a>
        <nav class="admin-side-nav">
            <?php foreach ($items as $key => [$label, $iconName, $path]): ?>
                <a class="<?= $active === $key ? 'is-active' : '' ?>" href="<?= e(url($path)) ?>"<?= $active === $key ? ' aria-current="page"' : '' ?>>
                    <?= icon($iconName) ?><span><?= e($label) ?></span>
                    <?php if (!empty($badges[$key])): ?><b><?= (int) $badges[$key] ?></b><?php endif; ?>
                </a>
            <?php endforeach; ?>
        </nav>
        <div class="admin-account">
            <span class="admin-avatar"><?= e(mb_strtoupper(mb_substr($username, 0, 1))) ?></span>
            <span><strong><?= e($username) ?></strong><small>Administrator</small></span>
            <a href="<?= e(url('admin/logout.php')) ?>" aria-label="Keluar dari dashboard" title="Keluar"><?= icon('logout') ?></a>
        </div>
    </div>
</aside>
    <?php
}

function social_icon(string $name): string
{
    $icons = [
        'whatsapp' => ['0 0 16 16', '<path d="M13.601 2.326A7.854 7.854 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.93 7.93 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.9 7.9 0 0 0 13.6 2.326ZM7.994 14.521a6.573 6.573 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.25a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.56 6.56 0 0 1 1.928 4.66c-.004 3.639-2.961 6.592-6.592 6.592Zm3.615-4.934c-.198-.099-1.17-.578-1.353-.646-.182-.066-.315-.099-.445.1-.133.197-.513.646-.627.775-.116.133-.232.15-.43.05-.198-.1-.836-.308-1.592-.984-.59-.525-.986-1.17-1.102-1.368-.116-.198-.013-.305.087-.404.09-.088.198-.232.297-.348.1-.116.133-.198.198-.33.066-.134.033-.249-.017-.348-.05-.099-.445-1.074-.61-1.47-.16-.389-.323-.336-.445-.342-.116-.006-.248-.006-.38-.006a.73.73 0 0 0-.529.248c-.182.198-.693.678-.693 1.654s.71 1.916.81 2.049c.098.132 1.394 2.132 3.383 2.992.47.205.84.326 1.129.418.475.15.907.129 1.248.078.38-.058 1.171-.48 1.338-.943.164-.462.164-.86.116-.943-.05-.082-.182-.132-.38-.232Z"/>'],
        'telegram' => ['0 0 24 24', '<path d="M21.7 3.4 18.5 20c-.2 1.2-.9 1.5-1.8.9l-4.8-3.5-2.3 2.2c-.2.3-.5.5-1 .5l.3-4.8 8.9-8c.4-.3-.1-.5-.6-.2l-11 6.9-4.7-1.5c-1-.3-1-1 .2-1.5L20.2 3.9c.9-.3 1.7.2 1.5-.5Z"/>'],
        'facebook' => ['0 0 24 24', '<path d="M13.6 22v-9h3l.5-3h-3.5V8.1c0-.9.3-1.6 1.7-1.6h2V3.8c-.4-.1-1.6-.2-3-.2-3 0-5.1 1.8-5.1 5.2V10H6v3h3.2v9h4.4Z"/>'],
    ];
    if (!isset($icons[$name])) {
        return '';
    }
    [$viewBox, $path] = $icons[$name];
    return '<svg class="social-icon social-icon-' . e($name) . '" viewBox="' . $viewBox . '" aria-hidden="true" fill="currentColor">' . $path . '</svg>';
}

function render_header(array $meta = [], string $bodyClass = ''): void
{
    $defaults = [
        'title' => 'DapurNda — Resep yang Selalu Ingin Dimasak Lagi',
        'description' => 'Temukan resep rumahan pilihan, bagikan kreasi dapurmu, dan masak makanan penuh cerita bersama DapurNda.',
        'image' => url('assets/images/og-cover.jpg'),
        'url' => current_url(),
        'type' => 'website',
        'image_width' => 1200,
        'image_height' => 630,
        'robots' => 'index, follow, max-image-preview:large',
        'json_ld' => null,
    ];
    $meta = array_merge($defaults, $meta);
    $pageTitle = str_contains($meta['title'], 'DapurNda') ? $meta['title'] : $meta['title'] . ' · DapurNda';
    $isAdminPage = str_contains($bodyClass, 'admin-page');
    $bodyClass = trim(($isAdminPage ? '' : 'public-page ') . $bodyClass);
    $GLOBALS['layout_is_admin_page'] = $isAdminPage;
    ?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title><?= e($pageTitle) ?></title>
    <meta name="description" content="<?= e($meta['description']) ?>">
    <meta name="robots" content="<?= e($isAdminPage ? 'noindex, nofollow' : $meta['robots']) ?>">
    <link rel="canonical" href="<?= e($meta['url']) ?>">
    <meta property="og:locale" content="id_ID">
    <meta property="og:site_name" content="DapurNda">
    <meta property="og:type" content="<?= e($meta['type']) ?>">
    <meta property="og:title" content="<?= e($pageTitle) ?>">
    <meta property="og:description" content="<?= e($meta['description']) ?>">
    <meta property="og:url" content="<?= e($meta['url']) ?>">
    <meta property="og:image" content="<?= e($meta['image']) ?>">
    <meta property="og:image:width" content="<?= (int) $meta['image_width'] ?>">
    <meta property="og:image:height" content="<?= (int) $meta['image_height'] ?>">
    <meta property="og:image:alt" content="<?= e($meta['title']) ?>">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= e($pageTitle) ?>">
    <meta name="twitter:description" content="<?= e($meta['description']) ?>">
    <meta name="twitter:image" content="<?= e($meta['image']) ?>">
    <meta name="theme-color" content="#f7efe3">
    <link rel="icon" href="<?= e(asset('images/favicon.svg')) ?>" type="image/svg+xml">
    <link rel="preload" href="<?= e(asset('fonts/plus-jakarta-sans-variable.woff2')) ?>" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="<?= e(asset('fonts/fraunces-variable.woff2')) ?>" as="font" type="font/woff2" crossorigin>
    <link rel="stylesheet" href="<?= e(asset('css/style.css')) ?>">
    <?php if ($meta['json_ld']): ?>
        <script type="application/ld+json"><?= json_encode($meta['json_ld'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) ?></script>
    <?php endif; ?>
</head>
<body class="<?= e($bodyClass) ?>">
<a class="skip-link" href="#main-content">Lewati ke isi</a>
<div class="scroll-progress" aria-hidden="true"><i data-scroll-progress></i></div>
<header class="site-header" data-header>
    <div class="shell header-inner">
        <a class="brand" href="<?= e(url()) ?>" aria-label="DapurNda, kembali ke beranda">
            <span class="brand-mark"><?= icon('chef') ?></span>
            <span>Dapur<em>Nda</em></span>
        </a>
        <button class="nav-toggle" type="button" data-nav-toggle aria-label="Buka menu" aria-expanded="false"><?= icon('menu') ?></button>
        <nav class="main-nav" data-nav aria-label="Navigasi utama">
            <a href="<?= e(url() . '#resep') ?>">Jelajah resep</a>
            <a href="<?= e(url('add.php')) ?>">Tulis resep</a>
            <?php if ($isAdminPage && is_admin()): ?><a href="<?= e(url('admin/index.php')) ?>">Dashboard</a><?php endif; ?>
            <a class="button button-small button-dark" href="<?= e(url('search.php')) ?>">Cari resep <?= icon('search') ?></a>
        </nav>
    </div>
</header>
<div class="toast-stack" data-toast-stack aria-live="polite">
    <?php foreach (pull_flashes() as $flash): ?>
        <div class="toast toast-<?= e($flash['type']) ?>" data-toast><?= e($flash['message']) ?></div>
    <?php endforeach; ?>
</div>
<?php
}

function render_footer(array $options = []): void
{
    $sponsors = config('features.public_sponsors', false) && empty($GLOBALS['layout_is_admin_page']) ? active_sponsors('footer') : [];
    $currentPage = basename((string) ($_SERVER['SCRIPT_NAME'] ?? 'index.php'));
    record_sponsor_impressions($sponsors);
    ?>
<?php if ($sponsors): ?>
<section class="supporters" aria-label="Sponsor DapurNda">
    <div class="shell supporters-inner">
        <p>Dipercaya dan didukung oleh</p>
        <div class="supporter-logos">
            <?php foreach ($sponsors as $sponsor): ?>
                <a href="<?= e(url('go.php', ['id' => $sponsor['id']])) ?>" target="_blank" rel="sponsored noopener" aria-label="Kunjungi <?= e($sponsor['name']) ?>">
                    <?php if ($sponsor['logo_filename']): ?><img src="<?= e(url('uploads/sponsors/' . $sponsor['logo_filename'])) ?>" alt="<?= e($sponsor['name']) ?>" loading="lazy"><?php else: ?><span><?= e($sponsor['name']) ?></span><?php endif; ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>
<footer class="site-footer">
    <?php if (empty($options['hide_invite'])): ?>
    <div class="shell footer-invite">
        <div><span class="eyebrow eyebrow-light">Resep keluarga juga berarti</span><h2>Ada cerita yang layak<br>diteruskan.</h2></div>
        <div><p>Punya resep yang selalu diminta keluarga? Tuliskan di DapurNda. Kami akan meninjaunya sebelum diterbitkan.</p><a class="button button-cream" href="<?= e(url('add.php')) ?>">Tulis resep di DapurNda <?= icon('arrow') ?></a></div>
    </div>
    <?php endif; ?>
    <div class="shell footer-grid">
        <div>
            <a class="brand brand-light" href="<?= e(url()) ?>"><span class="brand-mark"><?= icon('chef') ?></span><span>Dapur<em>Nda</em></span></a>
            <p>Panduan masak rumahan yang jelas, hangat, dan mudah diikuti bahkan saat baru pertama masuk dapur.</p>
        </div>
        <div class="footer-links"><h2>Informasi</h2><a href="<?= e(url('about.php')) ?>">Tentang kami</a><a href="<?= e(url('privacy.php')) ?>">Privasi</a><a href="<?= e(url('terms.php')) ?>">Ketentuan</a><a href="mailto:halo@dapurnda.id">Kontak</a><a href="<?= e(url('admin/index.php')) ?>">Area pengelola</a></div>
    </div>
    <nav class="shell footer-legal" aria-label="Informasi dan kebijakan"><a href="<?= e(url('about.php')) ?>">Tentang</a><a href="<?= e(url('privacy.php')) ?>">Privasi</a><a href="<?= e(url('terms.php')) ?>">Ketentuan</a><a href="mailto:halo@dapurnda.id">Kontak</a></nav>
    <div class="shell footer-bottom"><span>© <?= date('Y') ?> DapurNda. Dibuat untuk meja makan Indonesia.</span><span>Masak · Cerita · Berbagi</span></div>
</footer>
<?php if (empty($GLOBALS['layout_is_admin_page'])): ?>
<nav class="mobile-quick-nav" aria-label="Navigasi cepat">
    <a<?= $currentPage === 'index.php' ? ' class="is-active" aria-current="page"' : '' ?> href="<?= e(url()) ?>" aria-label="Beranda"><?= icon('home') ?><span>Beranda</span></a>
    <a<?= $currentPage === 'search.php' ? ' class="is-active" aria-current="page"' : '' ?> href="<?= e(url('search.php')) ?>" aria-label="Cari resep"><?= icon('search') ?><span>Cari</span></a>
    <a<?= $currentPage === 'add.php' ? ' class="is-active" aria-current="page"' : '' ?> href="<?= e(url('add.php')) ?>" aria-label="Tulis resep"><?= icon('edit') ?><span>Tulis</span></a>
</nav>
<?php endif; ?>
<script src="<?= e(asset('js/app.js')) ?>" defer></script>
</body>
</html>
<?php
}

function recipe_card(array $recipe): string
{
    ob_start();
    $image = recipe_photo_url($recipe['photo_filename'] ?? null);
    ?>
<article class="recipe-card reveal" data-recipe-card data-motion-card>
    <a class="recipe-card-link" href="<?= e(url('recipe.php', ['slug' => $recipe['slug']])) ?>" aria-label="Buka tutorial resep <?= e($recipe['title']) ?>">
        <span class="recipe-card-media tone-<?= e(((int) $recipe['id'] % 6) + 1) ?>">
            <?php if ($image): ?><img src="<?= e($image) ?>" alt="<?= e($recipe['title']) ?>" loading="lazy" width="640" height="440"><?php else: ?><span class="food-illustration" aria-hidden="true"><i></i><b></b></span><?php endif; ?>
            <span class="card-category"><?= e($recipe['category_name']) ?></span>
            <span class="difficulty-badge"><?= e($recipe['difficulty']) ?></span>
            <?php if (!empty($recipe['sponsor_name'])): ?><span class="sponsored-badge">Disponsori · <?= e($recipe['sponsor_name']) ?></span><?php endif; ?>
        </span>
        <div class="recipe-card-body">
            <h3><?= e($recipe['title']) ?></h3>
            <p><?= e(mb_strimwidth($recipe['description'], 0, 112, '…')) ?></p>
            <div class="card-meta"><span><?= icon('clock') ?> <?= (int) $recipe['cook_time'] ?> menit</span><span><?= icon('users') ?> <?= (int) $recipe['servings'] ?> porsi</span><span class="recipe-view-count" title="<?= e(format_view_count((int) ($recipe['views'] ?? 0))) ?> kali dilihat"><?= icon('eye') ?> <?= e(format_view_count((int) ($recipe['views'] ?? 0))) ?></span><span class="card-arrow" aria-hidden="true"><?= icon('chevron-right') ?></span></div>
        </div>
    </a>
</article>
    <?php
    return (string) ob_get_clean();
}
