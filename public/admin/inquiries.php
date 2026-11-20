<?php

declare(strict_types=1);

require dirname(__DIR__) . '/app.php';
require_admin();

if (is_post()) {
    verify_csrf();
    $id = (int) ($_POST['id'] ?? 0);
    $status = (string) ($_POST['status'] ?? 'new');
    if (in_array($status, ['new', 'contacted', 'closed'], true)) {
        db()->prepare('UPDATE sponsor_inquiries SET status = ? WHERE id = ?')->execute([$status, $id]);
        flash('success', 'Status pesan diperbarui.');
    }
    redirect('admin/inquiries.php');
}

$inquiries = db_all("SELECT * FROM sponsor_inquiries ORDER BY FIELD(status, 'new', 'contacted', 'closed'), created_at DESC LIMIT 100");
$newInquiryCount = count(array_filter($inquiries, static fn(array $item): bool => $item['status'] === 'new'));
render_admin_page_start('Pesan Kolaborasi', 'inquiries', ['inquiries' => $newInquiryCount]);
?>
<main id="main-content" class="admin-shell container-xxl flex-grow-1 container-p-y">
    <div class="admin-top"><div><span class="admin-kicker">Kotak masuk</span><h1>Pesan kolaborasi</h1><p>Tindak lanjuti calon mitra dan catat progres komunikasi dalam satu daftar.</p></div><div class="admin-top-actions"><span class="admin-summary-chip"><?= icon('mail') ?> <?= $newInquiryCount ?> pesan baru</span></div></div>
    <div class="inquiry-list"><?php foreach ($inquiries as $item): ?><article class="admin-panel"><div class="inquiry-heading"><div><span class="status status-<?= $item['status'] === 'new' ? 'pending' : ($item['status'] === 'contacted' ? 'approved' : 'rejected') ?>"><?= e($item['status']) ?></span><h2><?= e($item['brand_name']) ?></h2><p><?= e($item['name']) ?> · <a href="mailto:<?= e($item['contact']) ?>"><?= e($item['contact']) ?></a></p></div><small><?= e(format_date($item['created_at'], true)) ?></small></div><blockquote><?= nl2br(e($item['message'])) ?></blockquote><form method="post"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $item['id'] ?>"><label class="field"><span>Status tindak lanjut</span><select name="status" data-auto-submit><?php foreach (['new' => 'Baru', 'contacted' => 'Sudah dihubungi', 'closed' => 'Selesai'] as $key => $label): ?><option value="<?= $key ?>"<?= selected($item['status'], $key) ?>><?= $label ?></option><?php endforeach; ?></select></label></form></article><?php endforeach; ?><?php if (!$inquiries): ?><div class="admin-panel empty-state"><h2>Belum ada pesan</h2><p>Permintaan dari halaman sponsor akan tampil di sini.</p></div><?php endif; ?></div>
</main>
<?php render_admin_page_end(); ?>
