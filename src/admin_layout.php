<?php

declare(strict_types=1);

function admin_page_title(string $title): string
{
    return str_contains($title, 'DapurNda') ? $title : $title . ' · DapurNda';
}

function render_admin_document_head(string $title, bool $auth = false): void
{
    $htmlClass = $auth ? 'layout-wide customizer-hide' : 'layout-menu-fixed layout-compact';
    ?>
<!doctype html>
<html lang="id" class="<?= e($htmlClass) ?>" data-assets-path="<?= e(url('assets/admin/')) ?>" data-template="vertical-menu-template-free">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
    <title><?= e(admin_page_title($title)) ?></title>
    <meta name="description" content="Area pengelola DapurNda">
    <meta name="robots" content="noindex,nofollow">
    <meta name="theme-color" content="#c85535">
    <link rel="icon" href="<?= e(asset('images/favicon.svg')) ?>" type="image/svg+xml">
    <link rel="preload" href="<?= e(asset('admin/fonts/public-sans-variable.woff2')) ?>" as="font" type="font/woff2" crossorigin>
    <link rel="stylesheet" href="<?= e(asset('admin/vendor/fonts/iconify-icons.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('admin/vendor/css/core.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('admin/css/demo.css')) ?>">
    <?php if ($auth): ?><link rel="stylesheet" href="<?= e(asset('admin/vendor/css/pages/page-auth.css')) ?>"><?php endif; ?>
    <link rel="stylesheet" href="<?= e(asset('admin/css/dapurnda-admin.css')) ?>">
</head>
    <?php
}

function render_admin_page_start(string $title, string $active, array $badges = []): void
{
    $items = [
        'dashboard' => ['Ringkasan', 'bx-home-smile', 'admin/index.php'],
        'recipes' => ['Resep', 'bx-book-open', 'admin/recipes.php'],
        'sponsors' => ['Sponsor', 'bx-briefcase-alt-2', 'admin/sponsors.php'],
        'inquiries' => ['Pesan', 'bx-envelope', 'admin/inquiries.php'],
    ];
    if (is_super_admin()) {
        $items['users'] = ['Pengguna', 'bx-user-plus', 'admin/users.php'];
    }
    $activeLabel = $items[$active][0] ?? 'Dashboard';
    $username = (string) ($_SESSION['admin_username'] ?? 'Admin');
    $roleLabel = admin_role_label();
    render_admin_document_head($title);
    ?>
<body class="dapurnda-admin">
<div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
        <aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
            <div class="app-brand dapurnda-app-brand">
                <a href="<?= e(url('admin/index.php')) ?>" class="app-brand-link">
                    <span class="dapurnda-brand-mark"><?= icon('chef') ?></span>
                    <span class="app-brand-text menu-text fw-bold ms-2">Dapur<span>Nda</span></span>
                </a>
                <button type="button" class="layout-menu-toggle dapurnda-menu-close ms-auto d-xl-none" aria-label="Tutup menu">
                    <i class="icon-base bx bx-x" aria-hidden="true"></i>
                </button>
            </div>
            <div class="menu-divider mt-0"></div>
            <div class="menu-inner-shadow"></div>
            <ul class="menu-inner py-1">
                <li class="menu-header small text-uppercase"><span class="menu-header-text">Kelola DapurNda</span></li>
                <?php foreach ($items as $key => [$label, $iconName, $path]): ?>
                    <li class="menu-item<?= $active === $key ? ' active' : '' ?>">
                        <a href="<?= e(url($path)) ?>" class="menu-link"<?= $active === $key ? ' aria-current="page"' : '' ?>>
                            <span class="dapurnda-menu-icon"><i class="menu-icon icon-base bx <?= e($iconName) ?>"></i></span>
                            <div class="text-truncate"><?= e($label) ?></div>
                            <?php if (!empty($badges[$key])): ?><span class="badge rounded-pill bg-danger ms-auto"><?= (int) $badges[$key] ?></span><?php endif; ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </aside>

        <div class="layout-page">
            <nav class="layout-navbar container-xxl navbar-detached navbar navbar-expand-xl align-items-center bg-navbar-theme" id="layout-navbar">
                <div class="navbar-nav-right d-flex align-items-center justify-content-between" id="navbar-collapse">
                    <div class="navbar-nav align-items-center dapurnda-navbar-start">
                        <button class="dapurnda-sidebar-toggle" type="button" aria-label="Ciutkan menu samping" aria-controls="layout-menu" aria-expanded="true" title="Tampilkan atau ciutkan menu">
                            <i class="icon-base bx bx-menu-alt-left"></i>
                        </button>
                        <div class="dapurnda-navbar-title">
                            <small>DapurNda Admin</small>
                            <strong><?= e($activeLabel) ?></strong>
                        </div>
                    </div>
                    <ul class="navbar-nav flex-row align-items-center ms-auto">
                        <li class="nav-item me-2">
                            <a class="nav-link dapurnda-navbar-action" href="<?= e(url()) ?>" target="_blank" rel="noopener" title="Lihat website publik"><i class="icon-base bx bx-globe"></i><span>Website publik</span></a>
                        </li>
                        <li class="nav-item navbar-dropdown dropdown-user dropdown">
                            <a class="nav-link dropdown-toggle hide-arrow dapurnda-account-trigger" href="javascript:void(0);" data-bs-toggle="dropdown" aria-label="Menu akun">
                                <span class="avatar avatar-online"><span class="avatar-initial rounded-circle bg-label-primary"><?= e(mb_strtoupper(mb_substr($username, 0, 1))) ?></span></span>
                                <span class="dapurnda-account-copy"><strong><?= e($username) ?></strong><small><?= e($roleLabel) ?></small></span>
                                <i class="icon-base bx bx-chevron-down dapurnda-account-chevron"></i>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><span class="dropdown-item-text"><span class="fw-medium d-block"><?= e($username) ?></span><small class="text-muted"><?= e($roleLabel) ?></small></span></li>
                                <li><div class="dropdown-divider"></div></li>
                                <li><a class="dropdown-item" href="<?= e(url('admin/logout.php')) ?>"><i class="icon-base bx bx-power-off me-2"></i>Keluar</a></li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </nav>

            <div class="content-wrapper">
                <?php $flashes = pull_flashes(); if ($flashes): ?>
                    <div class="container-xxl dapurnda-flashes pt-4">
                        <?php foreach ($flashes as $flash): ?>
                            <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : 'success' ?> alert-dismissible" role="alert">
                                <?= e($flash['message']) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
    <?php
}

function render_admin_page_end(): void
{
    ?>
                <footer class="content-footer footer bg-footer-theme dapurnda-admin-footer">
                    <div class="container-xxl dapurnda-footer-inner">
                        <div class="dapurnda-footer-brand"><span class="dapurnda-footer-mark"><?= icon('chef') ?></span><span><strong>DapurNda</strong><small>Panel pengelola</small></span></div>
                        <div class="dapurnda-footer-meta"><span class="dapurnda-system-ready"><i></i>Sistem siap</span><span>© <?= date('Y') ?></span></div>
                    </div>
                </footer>
                <div class="content-backdrop fade"></div>
            </div>
        </div>
    </div>
    <div class="layout-overlay layout-menu-toggle"></div>
</div>
<?php render_admin_scripts(); ?>
</body>
</html>
    <?php
}

function render_admin_auth_start(string $title): void
{
    render_admin_document_head($title, true);
    ?>
<body class="dapurnda-admin dapurnda-auth">
    <?php foreach (pull_flashes() as $flash): ?>
        <div class="dapurnda-auth-alert alert alert-<?= $flash['type'] === 'error' ? 'danger' : 'success' ?> alert-dismissible" role="alert">
            <?= e($flash['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    <?php endforeach; ?>
    <?php
}

function render_admin_auth_end(): void
{
    ?>
    <div class="dapurnda-auth-credit">Admin UI berbasis <a href="https://themeselection.com/" target="_blank" rel="noopener">Sneat · ThemeSelection</a></div>
<?php render_admin_scripts(false); ?>
</body>
</html>
    <?php
}

function render_admin_scripts(bool $withMenu = true): void
{
    ?>
<script src="<?= e(asset('admin/vendor/js/bootstrap.bundle.min.js')) ?>"></script>
<script src="<?= e(asset('admin/js/dapurnda-sneat.js')) ?>" data-menu="<?= $withMenu ? 'true' : 'false' ?>"></script>
<script src="<?= e(asset('js/app.js')) ?>"></script>
    <?php
}
