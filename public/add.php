<?php

declare(strict_types=1);

require __DIR__ . '/app.php';

if (is_post()) {
    verify_csrf();
    if (!empty($_POST['website'])) {
        redirect('add.php?sent=1');
    }
    remember_input($_POST);
    $photo = null;
    try {
        enforce_submission_rate_limit();
        record_submission_attempt();
        $data = validate_recipe_input($_POST, true);
        $photo = store_image($_FILES['photo'] ?? [], 'recipes', false);
        create_recipe($data, $photo);
        clear_old_input();
        flash('success', 'Resep berhasil diajukan! Tim kami akan meninjaunya sebelum tayang.');
        redirect('add.php?sent=1');
    } catch (Throwable $exception) {
        if ($photo) {
            delete_image($photo, 'recipes');
        }
        flash('error', $exception->getMessage());
        redirect('add.php');
    }
}

$categories = get_categories();
render_header([
    'title' => 'Tulis Resep',
    'description' => 'Tulis dan ajukan resep andalan keluargamu ke DapurNda untuk ditinjau dan diterbitkan. Mudah, tanpa perlu membuat akun.',
    'url' => url('add.php'),
], 'submission-page');
?>
<main id="main-content" class="form-page">
    <a class="submission-mobile-exit" href="<?= e(url()) ?>" aria-label="Keluar dari formulir tulis resep"><span><?= icon('close') ?></span> Keluar</a>
    <section class="form-hero shell reveal is-visible"><span class="eyebrow"><?= icon('spark') ?> Dari dapurmu untuk banyak orang</span><h1>Tulis resep<br><em>yang punya cerita.</em></h1><p>Tulis dengan detail agar siapa pun bisa berhasil memasaknya. Resep akan ditinjau sebelum ditampilkan.</p></section>
    <form class="recipe-form shell" method="post" enctype="multipart/form-data" data-recipe-form>
        <?= csrf_field() ?><input class="honeypot" type="text" name="website" tabindex="-1" autocomplete="off" aria-hidden="true">
        <div class="mobile-form-progress" data-form-progress><div><strong data-wizard-title>Informasi resep</strong><span data-wizard-count>Langkah 1 dari 4</span></div><span><i data-wizard-bar></i></span></div>
        <section class="form-card reveal" data-form-step="1">
            <div class="form-card-heading"><span>01</span><div><h2>Ceritakan resepnya</h2><p>Mulai dari nama yang menggugah dan cerita singkat di balik hidangan.</p></div></div>
            <div class="field-grid">
                <label class="field field-wide"><span>Nama resep *</span><input name="title" required minlength="5" maxlength="160" value="<?= e(old('title')) ?>" placeholder="Contoh: Ayam bakar madu rempah"></label>
                <label class="field field-wide"><span>Cerita singkat *</span><textarea name="description" required minlength="30" maxlength="3000" rows="5" placeholder="Apa yang membuat resep ini istimewa?"><?= e(old('description')) ?></textarea><small><span data-char-count>0</span>/3000 karakter</small></label>
                <label class="field"><span>Kategori *</span><select name="category_id" required><option value="">Pilih kategori</option><?php foreach ($categories as $category): ?><option value="<?= (int) $category['id'] ?>"<?= selected(old('category_id'), $category['id']) ?>><?= e($category['name']) ?></option><?php endforeach; ?></select></label>
                <label class="field"><span>Nama pengirim</span><input name="author_name" maxlength="100" value="<?= e(old('author_name')) ?>" placeholder="Boleh dikosongkan"></label>
                <label class="field"><span>Email atau WhatsApp</span><input name="contributor_contact" maxlength="160" value="<?= e(old('contributor_contact')) ?>" placeholder="Opsional, untuk kabar penerbitan" autocomplete="email"><small>Tidak akan ditampilkan kepada pembaca.</small></label>
                <label class="field"><span>Waktu memasak *</span><div class="input-suffix"><input type="number" name="cook_time" min="1" max="1440" required value="<?= e(old('cook_time', 30)) ?>"><span>menit</span></div></label>
                <label class="field"><span>Jumlah porsi *</span><div class="input-suffix"><input type="number" name="servings" min="1" max="100" required value="<?= e(old('servings', 4)) ?>"><span>porsi</span></div></label>
                <label class="field"><span>Tingkat kesulitan *</span><select name="difficulty" required><?php foreach (['Mudah', 'Sedang', 'Menantang'] as $level): ?><option<?= selected(old('difficulty', 'Mudah'), $level) ?>><?= $level ?></option><?php endforeach; ?></select></label>
            </div>
        </section>

        <section class="form-card reveal" data-form-step="2">
            <div class="form-card-heading"><span>02</span><div><h2>Tambahkan fotonya</h2><p>Foto horizontal dengan cahaya alami akan terlihat paling menarik.</p></div></div>
            <label class="upload-zone" data-upload-zone><input type="file" name="photo" accept="image/*" data-image-input><span class="upload-icon"><?= icon('image') ?></span><strong>Pilih foto asli dari HP</strong><small>Tidak perlu dikompres sendiri · sistem otomatis mengoptimalkan hingga maksimal 500 KB</small><img data-image-preview alt="Preview foto resep"><span data-file-size aria-live="polite"></span></label>
        </section>

        <section class="form-card reveal" data-form-step="3">
            <div class="form-card-heading"><span>03</span><div><h2>Bahan-bahan</h2><p>Tulis satu bahan per baris beserta takarannya.</p></div></div>
            <div class="dynamic-list" data-ingredient-list>
                <?php $oldNames = (array) old('ingredient_name', ['', '', '']); $oldAmounts = (array) old('ingredient_amount', ['', '', '']); foreach ($oldNames as $index => $name): ?>
                <div class="ingredient-row"><span class="drag-handle" aria-hidden="true"><?= icon('grip') ?></span><input name="ingredient_name[]" value="<?= e($name) ?>" placeholder="Nama bahan" required><input name="ingredient_amount[]" value="<?= e($oldAmounts[$index] ?? '') ?>" placeholder="Takaran, mis. 200 g"><button type="button" data-remove-row aria-label="Hapus bahan"><?= icon('trash') ?></button></div>
                <?php endforeach; ?>
            </div>
            <button class="add-row-button" type="button" data-add-ingredient><?= icon('plus') ?> Tambah bahan</button>
        </section>

        <section class="form-card reveal" data-form-step="4">
            <div class="form-card-heading"><span>04</span><div><h2>Langkah memasak</h2><p>Gunakan kalimat singkat, jelas, dan berurutan.</p></div></div>
            <div class="dynamic-list steps-editor" data-step-list>
                <?php $oldSteps = (array) old('steps', ['', '', '']); foreach ($oldSteps as $index => $step): ?>
                <div class="step-row"><span class="step-index"><?= str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) ?></span><textarea name="steps[]" rows="3" maxlength="2000" placeholder="Jelaskan langkah ini…" required><?= e($step) ?></textarea><button type="button" data-remove-row aria-label="Hapus langkah"><?= icon('trash') ?></button></div>
                <?php endforeach; ?>
            </div>
            <button class="add-row-button" type="button" data-add-step><?= icon('plus') ?> Tambah langkah</button>
            <label class="publishing-consent"><input type="checkbox" name="publishing_consent" value="1" required<?= checked((bool) old('publishing_consent', false)) ?>><span><?= icon('check') ?></span><p><strong>Saya mengizinkan DapurNda meninjau dan menerbitkan resep serta foto ini.</strong><small>Saya memastikan tulisan dan foto boleh saya bagikan. Baca <a href="<?= e(url('terms.php')) ?>" target="_blank">ketentuan pengiriman resep</a>.</small></p></label>
        </section>

        <div class="mobile-wizard-controls" data-wizard-controls><button type="button" data-wizard-back><?= icon('arrow') ?> Kembali</button><span data-wizard-short-count>1/4</span><button type="button" data-wizard-next>Berikutnya <?= icon('arrow') ?></button></div>
        <div class="form-submit reveal" data-form-submit><div><?= icon('check') ?><p><strong>Siap diajukan untuk ditinjau</strong><span>Dengan mengajukan, kamu menyatakan resep ini boleh ditampilkan di DapurNda.</span></p></div><button class="button button-primary button-large" type="submit" data-submit-button>Ajukan resep <?= icon('arrow') ?></button></div>
    </form>
</main>
<?php render_footer(['hide_invite' => true]); ?>
