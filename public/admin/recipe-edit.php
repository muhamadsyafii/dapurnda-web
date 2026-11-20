<?php

declare(strict_types=1);

require dirname(__DIR__) . '/app.php';
require_admin();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: (int) ($_POST['id'] ?? 0);
$recipe = recipe_by_id((int) $id);
if (!$recipe) {
    flash('error', 'Resep tidak ditemukan.');
    redirect('admin/recipes.php');
}

if (is_post()) {
    verify_csrf();
    $newPhoto = null;
    try {
        $data = validate_recipe_input($_POST);
        $newPhoto = store_image($_FILES['photo'] ?? [], 'recipes', false);
        $oldPhoto = $recipe['photo_filename'];
        update_recipe((int) $id, $data, $newPhoto, (int) ($_POST['sponsor_id'] ?? 0));
        if ($newPhoto && $oldPhoto) {
            delete_image($oldPhoto, 'recipes');
        }
        flash('success', 'Perubahan resep berhasil disimpan.');
        redirect('admin/recipe-edit.php?id=' . (int) $id);
    } catch (Throwable $exception) {
        if ($newPhoto) {
            delete_image($newPhoto, 'recipes');
        }
        flash('error', $exception->getMessage());
        redirect('admin/recipe-edit.php?id=' . (int) $id);
    }
}

$categories = get_categories();
$sponsors = db_all('SELECT id, name FROM sponsors ORDER BY name');
render_admin_page_start('Edit ' . $recipe['title'], 'recipes');
?>
<main id="main-content" class="admin-shell container-xxl flex-grow-1 container-p-y">
    <div class="admin-top"><div><span class="admin-kicker">Editor resep</span><h1><?= e($recipe['title']) ?></h1><p>Periksa informasi, bahan, dan langkah sebelum menyimpan perubahan.</p></div><div class="admin-top-actions"><a class="button button-outline" href="<?= e(url('admin/recipes.php')) ?>"><?= icon('arrow-left') ?> Daftar resep</a></div></div>
    <form class="admin-edit-form" method="post" enctype="multipart/form-data" data-recipe-form><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $recipe['id'] ?>">
        <section class="admin-panel form-card"><h2>Informasi utama</h2><div class="field-grid"><label class="field field-wide"><span>Judul</span><input name="title" required minlength="5" maxlength="160" value="<?= e($recipe['title']) ?>"></label><label class="field field-wide"><span>Deskripsi</span><textarea name="description" required minlength="30" maxlength="3000" rows="5"><?= e($recipe['description']) ?></textarea></label><label class="field"><span>Kategori</span><select name="category_id"><?php foreach ($categories as $category): ?><option value="<?= (int) $category['id'] ?>"<?= selected($recipe['category_id'], $category['id']) ?>><?= e($category['name']) ?></option><?php endforeach; ?></select></label><label class="field"><span>Pengirim</span><input name="author_name" maxlength="100" value="<?= e($recipe['author_name']) ?>"></label><label class="field"><span>Kontak kontributor</span><input value="<?= e($recipe['contributor_contact'] ?? '') ?>" readonly><small>Privat · hanya untuk kabar moderasi.</small></label><label class="field"><span>Persetujuan penerbitan</span><input value="<?= !empty($recipe['publishing_consent_at']) ? e(format_date($recipe['publishing_consent_at'], true)) : 'Belum tercatat' ?>" readonly></label><label class="field"><span>Waktu (menit)</span><input type="number" name="cook_time" min="1" max="1440" value="<?= (int) $recipe['cook_time'] ?>"></label><label class="field"><span>Porsi</span><input type="number" name="servings" min="1" max="100" value="<?= (int) $recipe['servings'] ?>"></label><label class="field"><span>Kesulitan</span><select name="difficulty"><?php foreach (['Mudah', 'Sedang', 'Menantang'] as $level): ?><option<?= selected($recipe['difficulty'], $level) ?>><?= $level ?></option><?php endforeach; ?></select></label><label class="field"><span>Sponsor terkait</span><select name="sponsor_id"><option value="">Tanpa sponsor</option><?php foreach ($sponsors as $sponsor): ?><option value="<?= (int) $sponsor['id'] ?>"<?= selected($recipe['sponsor_id'], $sponsor['id']) ?>><?= e($sponsor['name']) ?></option><?php endforeach; ?></select></label><label class="field field-wide"><span>Ganti foto (opsional)</span><input type="file" name="photo" accept="image/jpeg,image/png,image/webp" data-image-input><small>Foto dioptimalkan otomatis hingga maksimal 500 KB.</small></label></div></section>
        <section class="admin-panel form-card"><h2>Bahan-bahan</h2><div class="dynamic-list" data-ingredient-list><?php foreach ($recipe['ingredients'] as $ingredient): ?><div class="ingredient-row"><span class="drag-handle" aria-hidden="true"><?= icon('grip') ?></span><input name="ingredient_name[]" value="<?= e($ingredient['name']) ?>" required><input name="ingredient_amount[]" value="<?= e($ingredient['amount']) ?>"><button type="button" data-remove-row aria-label="Hapus bahan"><?= icon('trash') ?></button></div><?php endforeach; ?></div><button class="add-row-button" type="button" data-add-ingredient><?= icon('plus') ?> Tambah bahan</button></section>
        <section class="admin-panel form-card"><h2>Langkah memasak</h2><div class="dynamic-list steps-editor" data-step-list><?php foreach ($recipe['steps'] as $index => $step): ?><div class="step-row"><span class="step-index"><?= str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) ?></span><textarea name="steps[]" rows="3" required><?= e($step['instruction']) ?></textarea><button type="button" data-remove-row aria-label="Hapus langkah"><?= icon('trash') ?></button></div><?php endforeach; ?></div><button class="add-row-button" type="button" data-add-step><?= icon('plus') ?> Tambah langkah</button></section>
        <div class="form-submit"><div><span class="status status-<?= e($recipe['status']) ?>"><?= e($recipe['status']) ?></span></div><button class="button button-primary button-large" type="submit">Simpan perubahan <?= icon('arrow') ?></button></div>
    </form>
</main>
<?php render_admin_page_end(); ?>
