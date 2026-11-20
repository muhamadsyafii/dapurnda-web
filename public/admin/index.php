<?php

declare(strict_types=1);

require dirname(__DIR__) . '/app.php';

$needsSetup = admin_count() === 0;
if (is_post()) {
    verify_csrf();
    try {
        if ($needsSetup) {
            create_first_admin(trim((string) ($_POST['username'] ?? '')), (string) ($_POST['password'] ?? ''), (string) ($_POST['setup_key'] ?? ''));
            flash('success', 'Akun admin pertama berhasil dibuat. Silakan masuk.');
            redirect('admin/index.php');
        }
        if (attempt_admin_login(trim((string) ($_POST['username'] ?? '')), (string) ($_POST['password'] ?? ''))) {
            flash('success', 'Selamat datang kembali.');
            redirect('admin/index.php');
        }
        throw new RuntimeException('Username atau password tidak sesuai.');
    } catch (Throwable $exception) {
        flash('error', $exception->getMessage());
        redirect('admin/index.php');
    }
}

if (!is_admin()) {
    render_admin_auth_start($needsSetup ? 'Setup Admin' : 'Login Admin');
    ?>
    <main class="container-xxl">
        <div class="authentication-wrapper authentication-basic container-p-y">
            <div class="authentication-inner">
                <section class="card px-sm-6 px-0 dapurnda-login-card">
                    <div class="card-body">
                        <div class="app-brand justify-content-center mb-6">
                            <a href="<?= e(url()) ?>" class="app-brand-link gap-2">
                                <span class="dapurnda-brand-mark"><?= icon('chef') ?></span>
                                <span class="app-brand-text text-heading fw-bold">Dapur<span>Nda</span></span>
                            </a>
                        </div>
                        <span class="dapurnda-auth-kicker"><?= $needsSetup ? 'Setup satu kali' : 'Area pengelola' ?></span>
                        <h4 class="mb-1 mt-2"><?= $needsSetup ? 'Buat admin pertama' : 'Selamat datang kembali' ?></h4>
                        <p class="mb-6"><?= $needsSetup ? 'Gunakan kunci setup dari konfigurasi untuk membuat akun pengelola.' : 'Masuk untuk mengelola resep dan aktivitas DapurNda.' ?></p>
                        <form method="post" class="mb-4"><?= csrf_field() ?>
                            <div class="mb-5"><label class="form-label" for="admin-username">Username</label><input class="form-control" id="admin-username" name="username" required minlength="3" maxlength="40" autocomplete="username" autofocus></div>
                            <div class="mb-5 form-password-toggle"><label class="form-label" for="admin-password">Password</label><div class="input-group input-group-merge"><input class="form-control" id="admin-password" type="password" name="password" required minlength="6" autocomplete="<?= $needsSetup ? 'new-password' : 'current-password' ?>"><span class="input-group-text cursor-pointer"><i class="icon-base bx bx-hide"></i></span></div></div>
                            <?php if ($needsSetup): ?><div class="mb-5 form-password-toggle"><label class="form-label" for="setup-key">Setup key</label><div class="input-group input-group-merge"><input class="form-control" id="setup-key" type="password" name="setup_key" required autocomplete="off"><span class="input-group-text cursor-pointer"><i class="icon-base bx bx-hide"></i></span></div></div><?php endif; ?>
                            <button class="btn btn-primary d-grid w-100" type="submit"><?= $needsSetup ? 'Buat akun' : 'Masuk ke dashboard' ?></button>
                        </form>
                        <a class="d-flex justify-content-center align-items-center gap-2 text-body" href="<?= e(url()) ?>"><i class="icon-base bx bx-left-arrow-alt"></i>Kembali ke website</a>
                    </div>
                </section>
            </div>
        </div>
    </main>
    <?php render_admin_auth_end(); exit;
}

$activeSponsorStatement = db()->prepare('SELECT COUNT(*) FROM sponsors WHERE is_active = 1 AND start_date <= ? AND end_date >= ?');
$activeSponsorStatement->execute([local_today(), local_today()]);
$stats = [
    'pending' => (int) db()->query("SELECT COUNT(*) FROM recipes WHERE status = 'pending'")->fetchColumn(),
    'approved' => (int) db()->query("SELECT COUNT(*) FROM recipes WHERE status = 'approved'")->fetchColumn(),
    'sponsors' => (int) $activeSponsorStatement->fetchColumn(),
    'inquiries' => (int) db()->query("SELECT COUNT(*) FROM sponsor_inquiries WHERE status = 'new'")->fetchColumn(),
    'views' => (int) db()->query("SELECT COALESCE(SUM(views), 0) FROM recipes WHERE status = 'approved'")->fetchColumn(),
];
$popularRecipes = popular_recipes(5);
$highestViews = max(1, (int) ($popularRecipes[0]['views'] ?? 0));
$latest = db_all("SELECT r.id, r.title, r.status, r.created_at, r.photo_filename, r.views, c.name AS category_name FROM recipes r JOIN categories c ON c.id = r.category_id ORDER BY r.created_at DESC LIMIT 8");
$configuredPublicUrl = (string) config('app.url', '');
$sharePreviewReady = str_starts_with($configuredPublicUrl, 'https://') && !preg_match('/localhost|127\.0\.0\.1/i', $configuredPublicUrl);
render_admin_page_start('Dashboard Admin', 'dashboard', ['recipes' => $stats['pending'], 'inquiries' => $stats['inquiries']]);
?>
<main id="main-content" class="admin-shell container-xxl flex-grow-1 container-p-y">
    <div class="admin-top"><div><span class="admin-kicker">Ringkasan hari ini</span><h1>Selamat datang, <?= e($_SESSION['admin_username']) ?></h1><p>Pantau konten, moderasi kiriman, dan aktivitas DapurNda dari satu tempat.</p></div><div class="admin-top-actions"><a class="button button-primary" href="<?= e(url('admin/recipes.php', ['status' => 'pending'])) ?>"><?= icon('bell') ?> Tinjau antrean</a></div></div>
    <?php if (!$sharePreviewReady): ?><aside class="admin-readiness"><strong>Preview WhatsApp belum siap di localhost</strong><p>Sebelum publikasi, isi <code>APP_URL</code> dengan domain HTTPS agar tautan, gambar Open Graph, WhatsApp, dan Telegram memakai alamat publik.</p></aside><?php endif; ?>
    <div class="stat-grid">
        <article class="stat-card stat-card-waiting"><span class="stat-icon"><?= icon('clock') ?></span><div><small>Menunggu tinjauan</small><strong><?= $stats['pending'] ?></strong><a href="<?= e(url('admin/recipes.php', ['status' => 'pending'])) ?>">Tinjau resep <?= icon('arrow') ?></a></div></article>
        <article class="stat-card stat-card-live"><span class="stat-icon"><?= icon('book-open') ?></span><div><small>Resep tayang</small><strong><?= $stats['approved'] ?></strong><a href="<?= e(url()) ?>" target="_blank" rel="noopener">Lihat website <?= icon('external-link') ?></a></div></article>
        <article class="stat-card stat-card-partner"><span class="stat-icon"><?= icon('briefcase') ?></span><div><small>Sponsor aktif</small><strong><?= $stats['sponsors'] ?></strong><a href="<?= e(url('admin/sponsors.php')) ?>">Kelola sponsor <?= icon('arrow') ?></a></div></article>
        <article class="stat-card stat-card-message"><span class="stat-icon"><?= icon('mail') ?></span><div><small>Pesan baru</small><strong><?= $stats['inquiries'] ?></strong><a href="<?= e(url('admin/inquiries.php')) ?>">Buka pesan <?= icon('arrow') ?></a></div></article>
        <article class="stat-card stat-card-views"><span class="stat-icon"><?= icon('eye') ?></span><div><small>Total tayangan resep</small><strong><?= e(format_view_count($stats['views'])) ?></strong><a href="#resep-populer">Lihat peringkat <?= icon('arrow') ?></a></div></article>
    </div>
    <section class="admin-analytics" id="resep-populer">
        <article class="admin-panel admin-ranking-panel">
            <div class="panel-heading"><div><span class="panel-kicker">Analitik tayangan</span><h2>Resep paling sering dilihat</h2><p>Peringkat berdasarkan kunjungan nyata ke halaman tutorial.</p></div><span class="ranking-total"><?= icon('eye') ?> <?= e(format_view_count($stats['views'])) ?> total</span></div>
            <div class="recipe-ranking-list">
                <?php foreach ($popularRecipes as $index => $item): ?>
                    <div class="recipe-ranking-item">
                        <span class="ranking-number"><?= $index + 1 ?></span>
                        <span class="ranking-recipe"><strong><?= e($item['title']) ?></strong><small><?= e($item['category_name']) ?></small></span>
                        <span class="ranking-chart"><progress max="<?= $highestViews ?>" value="<?= (int) $item['views'] ?>"><?= (int) $item['views'] ?></progress></span>
                        <strong class="ranking-value"><?= icon('eye') ?> <?= e(format_view_count((int) $item['views'])) ?></strong>
                    </div>
                <?php endforeach; ?>
                <?php if (!$popularRecipes): ?><p class="empty-table">Belum ada resep tayang untuk dianalisis.</p><?php endif; ?>
            </div>
        </article>
        <aside class="popular-highlight">
            <span class="popular-highlight-icon"><?= icon('spark') ?></span>
            <span class="panel-kicker">Peringkat teratas</span>
            <?php if ($popularRecipes): ?><h2><?= e($popularRecipes[0]['title']) ?></h2><p>Resep ini paling banyak dibuka pengunjung saat ini.</p><strong><?= e(format_view_count((int) $popularRecipes[0]['views'])) ?><small> kali dilihat</small></strong><a href="<?= e(url('recipe.php', ['slug' => $popularRecipes[0]['slug']])) ?>" target="_blank" rel="noopener">Lihat resep <?= icon('external-link') ?></a><?php else: ?><h2>Belum ada data</h2><p>Tayangan pertama akan muncul setelah resep dibuka pengunjung.</p><?php endif; ?>
        </aside>
    </section>
    <section class="admin-panel"><div class="panel-heading"><div><span class="panel-kicker">Aktivitas konten</span><h2>Kiriman terbaru</h2><p>Resep yang terakhir masuk atau diperbarui.</p></div><a class="panel-link" href="<?= e(url('admin/recipes.php')) ?>">Lihat semua <?= icon('arrow') ?></a></div><div class="admin-table-wrap"><table class="admin-table"><thead><tr><th>Resep</th><th>Kategori</th><th>Status</th><th>Tayangan</th><th>Dikirim</th><th>Aksi</th></tr></thead><tbody><?php foreach ($latest as $item): ?><?php $photo = recipe_photo_url($item['photo_filename'] ?? null); ?><tr><td data-label="Resep"><span class="admin-recipe-cell"><?php if ($photo): ?><img src="<?= e($photo) ?>" alt="" width="52" height="52"><?php else: ?><span class="admin-recipe-placeholder"><?= icon('image') ?></span><?php endif; ?><strong><?= e($item['title']) ?></strong></span></td><td data-label="Kategori"><?= e($item['category_name']) ?></td><td data-label="Status"><span class="status status-<?= e($item['status']) ?>"><?= e(['pending' => 'Menunggu', 'approved' => 'Tayang', 'rejected' => 'Ditolak'][$item['status']] ?? $item['status']) ?></span></td><td data-label="Tayangan"><span class="admin-view-count"><?= icon('eye') ?> <?= e(format_view_count((int) $item['views'])) ?></span></td><td data-label="Dikirim"><?= e(format_date($item['created_at'], true)) ?></td><td data-label="Aksi"><a class="admin-row-link" href="<?= e(url('admin/recipe-edit.php', ['id' => $item['id']])) ?>">Buka <?= icon('chevron-right') ?></a></td></tr><?php endforeach; ?></tbody></table></div></section>
</main>
<?php render_admin_page_end(); ?>
