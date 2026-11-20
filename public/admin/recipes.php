<?php

declare(strict_types=1);

require dirname(__DIR__) . '/app.php';
require_admin();

if (is_post()) {
    verify_csrf();
    $id = (int) ($_POST['id'] ?? 0);
    $action = (string) ($_POST['action'] ?? '');
    $recipe = recipe_by_id($id);
    if (!$recipe) {
        flash('error', 'Resep tidak ditemukan.');
        redirect('admin/recipes.php');
    }
    if ($action === 'approve') {
        db()->prepare("UPDATE recipes SET status = 'approved', published_at = COALESCE(published_at, UTC_TIMESTAMP()) WHERE id = ?")->execute([$id]);
        flash('success', 'Resep telah disetujui dan sekarang tampil untuk publik.');
    } elseif ($action === 'reject') {
        db()->prepare("UPDATE recipes SET status = 'rejected' WHERE id = ?")->execute([$id]);
        flash('success', 'Resep dipindahkan ke status ditolak.');
    } elseif ($action === 'pending') {
        db()->prepare("UPDATE recipes SET status = 'pending' WHERE id = ?")->execute([$id]);
        flash('success', 'Resep dikembalikan ke antrean tinjauan.');
    } elseif ($action === 'delete') {
        db()->prepare('DELETE FROM recipes WHERE id = ?')->execute([$id]);
        delete_image($recipe['photo_filename'], 'recipes');
        flash('success', 'Resep dan data terkait telah dihapus.');
    }
    redirect('admin/recipes.php', 303);
}

$status = (string) ($_GET['status'] ?? 'all');
if (!in_array($status, ['all', 'pending', 'approved', 'rejected'], true)) {
    $status = 'all';
}
$sql = 'SELECT r.*, c.name AS category_name FROM recipes r JOIN categories c ON c.id = r.category_id';
$params = [];
if ($status !== 'all') {
    $sql .= ' WHERE r.status = ?';
    $params[] = $status;
}
$sql .= " ORDER BY FIELD(r.status, 'pending', 'approved', 'rejected'), r.created_at DESC LIMIT 100";
$recipes = db_all($sql, $params);
$counts = db_all('SELECT status, COUNT(*) AS total FROM recipes GROUP BY status');
$countMap = array_column($counts, 'total', 'status');
$totalRecipeCount = array_sum(array_map('intval', $countMap));

render_admin_page_start('Moderasi Resep', 'recipes', ['recipes' => (int) ($countMap['pending'] ?? 0)]);
?>
<main id="main-content" class="admin-shell container-xxl flex-grow-1 container-p-y">
    <div class="admin-top"><div><span class="admin-kicker">Manajemen konten</span><h1>Moderasi resep</h1><p>Tinjau kualitas isi, kontak kontributor, dan status publikasi setiap resep.</p></div><div class="admin-top-actions"><a class="button button-outline" href="<?= e(url('add.php')) ?>" target="_blank" rel="noopener"><?= icon('plus') ?> Tulis resep</a></div></div>
    <nav class="filter-tabs admin-recipes-filters" aria-label="Filter status resep"><a class="<?= $status === 'all' ? 'is-active' : '' ?>" href="<?= e(url('admin/recipes.php')) ?>"<?= $status === 'all' ? ' aria-current="page"' : '' ?>>Semua <span><?= $totalRecipeCount ?></span></a><?php foreach (['pending' => 'Menunggu', 'approved' => 'Tayang', 'rejected' => 'Ditolak'] as $key => $label): ?><a class="<?= $status === $key ? 'is-active' : '' ?>" href="<?= e(url('admin/recipes.php', ['status' => $key])) ?>"<?= $status === $key ? ' aria-current="page"' : '' ?>><?= $label ?> <span><?= (int) ($countMap[$key] ?? 0) ?></span></a><?php endforeach; ?></nav>
    <section class="admin-panel"><div class="panel-heading"><div><span class="panel-kicker">Daftar resep</span><h2><?= $status === 'all' ? 'Semua kiriman' : (['pending' => 'Menunggu tinjauan', 'approved' => 'Resep tayang', 'rejected' => 'Resep ditolak'][$status] ?? 'Semua kiriman') ?></h2><p><?= count($recipes) ?> resep ditampilkan pada halaman ini.</p></div></div><div class="admin-table-wrap"><table class="admin-table admin-recipes-table"><thead><tr><th>Resep</th><th>Detail</th><th>Pengirim</th><th>Status &amp; tayangan</th><th>Tindakan</th></tr></thead><tbody>
    <?php foreach ($recipes as $recipe): ?><?php $photo = recipe_photo_url($recipe['photo_filename'] ?? null); ?><tr class="recipe-moderation-row" data-recipe-status="<?= e($recipe['status']) ?>"><td class="recipe-main-cell" data-label="Resep"><span class="admin-recipe-cell"><?php if ($photo): ?><img src="<?= e($photo) ?>" alt="" width="56" height="56"><?php else: ?><span class="admin-recipe-placeholder"><?= icon('image') ?></span><?php endif; ?><span><strong><?= e($recipe['title']) ?></strong><small><?= e(format_date($recipe['created_at'], true)) ?></small></span></span></td><td class="recipe-detail-cell" data-label="Detail"><?= e($recipe['category_name']) ?><small><?= (int) $recipe['cook_time'] ?> menit · <?= (int) $recipe['servings'] ?> porsi</small></td><td class="recipe-author-cell" data-label="Pengirim"><?= e($recipe['author_name'] ?: 'Anonim') ?><small><?= e($recipe['contributor_contact'] ?: 'Kontak tidak dicantumkan') ?></small></td><td class="recipe-status-cell" data-label="Status & tayangan"><span class="status status-<?= e($recipe['status']) ?>"><?= e(['pending' => 'Menunggu', 'approved' => 'Tayang', 'rejected' => 'Ditolak'][$recipe['status']] ?? $recipe['status']) ?></span><span class="admin-view-count"><?= icon('eye') ?> <?= e(format_view_count((int) $recipe['views'])) ?></span></td><td class="recipe-actions-cell" data-label="Tindakan"><div class="table-actions"><a class="action-edit" href="<?= e(url('admin/recipe-edit.php', ['id' => $recipe['id']])) ?>"><?= icon('edit') ?> Edit resep</a><form method="post"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $recipe['id'] ?>"><?php if ($recipe['status'] !== 'approved'): ?><button class="action-approve" name="action" value="approve"><?= icon('check') ?> Setujui</button><?php endif; ?><?php if ($recipe['status'] !== 'rejected'): ?><button class="action-reject" name="action" value="reject"><?= icon('close') ?> Tolak</button><?php endif; ?><button class="danger" name="action" value="delete" data-confirm="Hapus permanen resep ini?" aria-label="Hapus resep <?= e($recipe['title']) ?>"><?= icon('trash') ?></button></form></div></td></tr><?php endforeach; ?>
    <?php if (!$recipes): ?><tr><td colspan="5" class="empty-table">Tidak ada resep pada status ini.</td></tr><?php endif; ?>
    </tbody></table></div></section>
</main>
<?php render_admin_page_end(); ?>
