<?php

declare(strict_types=1);

require dirname(__DIR__) . '/app.php';
require_admin();

if (is_post()) {
    verify_csrf();
    $action = (string) ($_POST['action'] ?? 'save');
    $id = (int) ($_POST['id'] ?? 0);
    if ($action === 'delete') {
        $existing = db_one('SELECT * FROM sponsors WHERE id = ?', [$id]);
        if ($existing) {
            db()->prepare('DELETE FROM sponsors WHERE id = ?')->execute([$id]);
            delete_image($existing['logo_filename'], 'sponsors');
            delete_image($existing['banner_filename'], 'sponsors');
            flash('success', 'Sponsor dan aset gambarnya telah dihapus.');
        }
        redirect('admin/sponsors.php');
    }
    if ($action === 'toggle') {
        db()->prepare('UPDATE sponsors SET is_active = NOT is_active WHERE id = ?')->execute([$id]);
        flash('success', 'Status sponsor diperbarui.');
        redirect('admin/sponsors.php');
    }

    $logo = $banner = null;
    try {
        $data = validate_sponsor_input($_POST);
        $existing = $id ? db_one('SELECT * FROM sponsors WHERE id = ?', [$id]) : null;
        $logo = store_image($_FILES['logo'] ?? [], 'sponsors', false);
        $banner = store_image($_FILES['banner'] ?? [], 'sponsors', false);
        if ($id && $existing) {
            db()->prepare('UPDATE sponsors SET name=?, slug=?, logo_filename=?, banner_filename=?, description=?, destination_url=?, tier=?, positions=?, start_date=?, end_date=?, is_active=? WHERE id=?')->execute([$data['name'], unique_sponsor_slug($data['name'], $id), $logo ?: $existing['logo_filename'], $banner ?: $existing['banner_filename'], $data['description'], $data['destinationUrl'], $data['tier'], implode(',', $data['positions']), $data['startDate'], $data['endDate'], $data['isActive'], $id]);
            if ($logo && $existing['logo_filename']) delete_image($existing['logo_filename'], 'sponsors');
            if ($banner && $existing['banner_filename']) delete_image($existing['banner_filename'], 'sponsors');
            flash('success', 'Data sponsor berhasil diperbarui.');
        } else {
            db()->prepare('INSERT INTO sponsors (name, slug, logo_filename, banner_filename, description, destination_url, tier, positions, start_date, end_date, is_active) VALUES (?,?,?,?,?,?,?,?,?,?,?)')->execute([$data['name'], unique_sponsor_slug($data['name']), $logo, $banner, $data['description'], $data['destinationUrl'], $data['tier'], implode(',', $data['positions']), $data['startDate'], $data['endDate'], $data['isActive']]);
            flash('success', 'Sponsor baru berhasil ditambahkan.');
        }
        redirect('admin/sponsors.php');
    } catch (Throwable $exception) {
        if ($logo) delete_image($logo, 'sponsors');
        if ($banner) delete_image($banner, 'sponsors');
        flash('error', $exception->getMessage());
        redirect('admin/sponsors.php' . ($id ? '?edit=' . $id : '?create=1'));
    }
}

$editId = filter_input(INPUT_GET, 'edit', FILTER_VALIDATE_INT);
$editing = $editId ? db_one('SELECT * FROM sponsors WHERE id = ?', [$editId]) : null;
$sponsors = db_all('SELECT * FROM sponsors ORDER BY is_active DESC, created_at DESC');
$activeCount = count(array_filter($sponsors, static fn (array $sponsor): bool => (bool) $sponsor['is_active']));
$openCreateModal = filter_input(INPUT_GET, 'create', FILTER_VALIDATE_BOOL) === true;

function render_sponsor_editor_modal(?array $sponsor, string $modalId, bool $autoOpen = false): void
{
    $isEditing = $sponsor !== null;
    $positions = explode(',', $sponsor['positions'] ?? 'footer');
    ?>
    <div class="modal fade" id="<?= e($modalId) ?>" tabindex="-1" aria-labelledby="<?= e($modalId) ?>-title" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false" data-sponsor-editor-modal<?= $autoOpen ? ' data-auto-open="true"' : '' ?>>
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable admin-sponsor-modal-dialog">
            <div class="modal-content admin-user-modal admin-sponsor-modal">
                <div class="modal-header">
                    <div><span class="panel-kicker"><?= $isEditing ? 'Perbarui kemitraan' : 'Mitra baru' ?></span><h2 class="modal-title" id="<?= e($modalId) ?>-title"><?= $isEditing ? 'Edit sponsor' : 'Tambah sponsor' ?></h2><p>Materi gambar akan dioptimalkan otomatis hingga maksimal 500 KB.</p></div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <form method="post" enctype="multipart/form-data" class="admin-sponsor-form"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) ($sponsor['id'] ?? 0) ?>">
                    <div class="modal-body">
                        <div class="field-grid sponsor-primary-fields">
                            <label class="field"><span>Nama sponsor</span><input name="name" required maxlength="120" value="<?= e($sponsor['name'] ?? '') ?>" placeholder="Contoh: Bumbu Nusantara"></label>
                            <label class="field"><span>URL tujuan</span><input type="url" name="destination_url" required value="<?= e($sponsor['destination_url'] ?? '') ?>" placeholder="https://"></label>
                            <label class="field field-wide"><span>Deskripsi singkat</span><textarea name="description" maxlength="500" rows="3" placeholder="Jelaskan brand atau kampanye secara singkat."><?= e($sponsor['description'] ?? '') ?></textarea></label>
                        </div>
                        <div class="field-grid sponsor-campaign-fields">
                            <label class="field"><span>Tier</span><select name="tier"><?php foreach (['utama', 'emas', 'perak'] as $tier): ?><option value="<?= $tier ?>"<?= selected($sponsor['tier'] ?? 'perak', $tier) ?>><?= ucfirst($tier) ?></option><?php endforeach; ?></select></label>
                            <label class="user-active-toggle sponsor-active-toggle"><input type="checkbox" name="is_active" value="1"<?= checked((bool) ($sponsor['is_active'] ?? true)) ?>><span><i><?= icon('check') ?></i><b>Sponsor aktif</b><small>Dapat ditampilkan pada periode kampanye.</small></span></label>
                            <label class="field"><span>Mulai</span><input type="date" name="start_date" required value="<?= e($sponsor['start_date'] ?? date('Y-m-d')) ?>"></label>
                            <label class="field"><span>Berakhir</span><input type="date" name="end_date" required value="<?= e($sponsor['end_date'] ?? date('Y-m-d', strtotime('+30 days'))) ?>"></label>
                        </div>
                        <fieldset class="position-options sponsor-position-options"><legend>Posisi tayang</legend><?php foreach (['hero' => 'Hero', 'strip' => 'Logo strip', 'detail' => 'Detail resep', 'footer' => 'Footer'] as $key => $label): ?><label><input type="checkbox" name="positions[]" value="<?= $key ?>"<?= checked(in_array($key, $positions, true)) ?>><span><?= $label ?></span></label><?php endforeach; ?></fieldset>
                        <div class="field-grid sponsor-media-fields">
                            <label class="field"><span>Logo</span><input type="file" name="logo" accept="image/jpeg,image/png,image/webp" data-image-input><small><?= $isEditing && !empty($sponsor['logo_filename']) ? 'Kosongkan untuk mempertahankan logo saat ini.' : 'JPG, PNG, atau WebP.' ?></small></label>
                            <label class="field"><span>Banner</span><input type="file" name="banner" accept="image/jpeg,image/png,image/webp" data-image-input><small><?= $isEditing && !empty($sponsor['banner_filename']) ? 'Kosongkan untuk mempertahankan banner saat ini.' : 'JPG, PNG, atau WebP.' ?></small></label>
                        </div>
                    </div>
                    <div class="modal-footer"><button class="button button-outline" type="button" data-bs-dismiss="modal">Batal</button><button class="button button-primary" type="submit"><?= $isEditing ? 'Simpan perubahan' : 'Tambah sponsor' ?> <?= icon('arrow') ?></button></div>
                </form>
            </div>
        </div>
    </div>
    <?php
}

render_admin_page_start('Kelola Sponsor', 'sponsors');
?>
<main id="main-content" class="admin-shell container-xxl flex-grow-1 container-p-y">
    <div class="admin-top">
        <div><span class="admin-kicker">Kemitraan</span><h1>Kelola sponsor</h1><p>Atur materi, periode kampanye, posisi tayang, dan performa mitra.</p></div>
        <div class="admin-top-actions"><span class="admin-summary-chip"><?= icon('briefcase') ?> <?= $activeCount ?> sponsor aktif</span><button class="button button-primary" type="button" data-bs-toggle="modal" data-bs-target="#createSponsorModal"><?= icon('plus') ?> Tambah sponsor</button></div>
    </div>

    <section class="admin-panel sponsor-list-panel">
        <div class="panel-heading"><div><span class="panel-kicker">Kemitraan terdaftar</span><h2>Daftar sponsor</h2><p><?= count($sponsors) ?> sponsor tersimpan di DapurNda.</p></div></div>
        <div class="sponsor-admin-list">
            <?php foreach ($sponsors as $sponsor): ?>
                <article class="sponsor-admin-row<?= !(bool) $sponsor['is_active'] ? ' is-inactive' : '' ?>">
                    <div class="sponsor-admin-logo"><?php if ($sponsor['logo_filename']): ?><img src="<?= e(url('uploads/sponsors/' . $sponsor['logo_filename'])) ?>" alt="Logo <?= e($sponsor['name']) ?>"><?php else: ?><?= e(mb_strtoupper(mb_substr($sponsor['name'], 0, 1))) ?><?php endif; ?></div>
                    <div class="sponsor-admin-identity"><h3><?= e($sponsor['name']) ?></h3><p><?= e($sponsor['description'] ?: 'Belum ada deskripsi sponsor.') ?></p><a href="<?= e($sponsor['destination_url']) ?>" target="_blank" rel="noopener"><?= icon('external-link') ?> Kunjungi tujuan</a></div>
                    <div class="sponsor-admin-period"><span class="sponsor-tier tier-<?= e($sponsor['tier']) ?>"><?= e(ucfirst($sponsor['tier'])) ?></span><small><?= icon('calendar') ?> <?= e(format_date($sponsor['start_date'])) ?> – <?= e(format_date($sponsor['end_date'])) ?></small></div>
                    <div class="sponsor-admin-performance"><strong><?= number_format((int) $sponsor['impressions'], 0, ',', '.') ?></strong><small>tayangan</small><strong><?= number_format((int) $sponsor['clicks'], 0, ',', '.') ?></strong><small>klik</small></div>
                    <span class="status <?= $sponsor['is_active'] ? 'status-approved' : 'status-rejected' ?>"><?= $sponsor['is_active'] ? 'Aktif' : 'Nonaktif' ?></span>
                    <div class="sponsor-admin-actions"><a href="<?= e(url('admin/sponsors.php', ['edit' => $sponsor['id']])) ?>"><?= icon('edit') ?> Edit</a><form method="post"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $sponsor['id'] ?>"><button class="sponsor-toggle" name="action" value="toggle"><?= $sponsor['is_active'] ? 'Nonaktifkan' : 'Aktifkan' ?></button><button class="sponsor-delete" name="action" value="delete" data-confirm="Hapus sponsor <?= e($sponsor['name']) ?> beserta logo dan bannernya?" aria-label="Hapus sponsor <?= e($sponsor['name']) ?>"><?= icon('trash') ?><span>Hapus</span></button></form></div>
                </article>
            <?php endforeach; ?>
            <?php if (!$sponsors): ?><div class="sponsor-empty-state"><?= icon('briefcase') ?><h3>Belum ada sponsor</h3><p>Tambahkan mitra pertama melalui tombol di kanan atas.</p><button class="button button-primary" type="button" data-bs-toggle="modal" data-bs-target="#createSponsorModal"><?= icon('plus') ?> Tambah sponsor</button></div><?php endif; ?>
        </div>
    </section>
</main>
<?php render_sponsor_editor_modal(null, 'createSponsorModal', $openCreateModal); ?>
<?php if ($editing): ?><?php render_sponsor_editor_modal($editing, 'editSponsorModal', true); ?><?php endif; ?>
<?php render_admin_page_end(); ?>
