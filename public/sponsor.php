<?php

declare(strict_types=1);

require __DIR__ . '/app.php';

if (!config('features.public_sponsors', false)) {
    redirect('');
}

if (is_post()) {
    verify_csrf();
    if (!empty($_POST['website'])) {
        redirect('sponsor.php?sent=1');
    }
    try {
        enforce_submission_rate_limit();
        record_submission_attempt();
        $name = trim((string) ($_POST['name'] ?? ''));
        $contact = trim((string) ($_POST['contact'] ?? ''));
        $brand = trim((string) ($_POST['brand_name'] ?? ''));
        $message = trim((string) ($_POST['message'] ?? ''));
        if (mb_strlen($name) < 2 || mb_strlen($name) > 100 || mb_strlen($contact) < 5 || mb_strlen($contact) > 160 || mb_strlen($brand) < 2 || mb_strlen($brand) > 140 || mb_strlen($message) < 20 || mb_strlen($message) > 3000) {
            throw new InvalidArgumentException('Lengkapi data kontak dan ceritakan kebutuhan kolaborasi Anda.');
        }
        db()->prepare('INSERT INTO sponsor_inquiries (name, contact, brand_name, message) VALUES (?, ?, ?, ?)')->execute([$name, $contact, $brand, $message]);
        flash('success', 'Terima kasih! Pesan kolaborasi sudah diterima dan akan segera kami pelajari.');
        redirect('sponsor.php?sent=1');
    } catch (Throwable $exception) {
        flash('error', $exception->getMessage());
        redirect('sponsor.php#kontak');
    }
}

render_header([
    'title' => 'Kolaborasi Brand & Sponsor',
    'description' => 'Hadirkan brand Anda secara relevan di tengah komunitas pecinta masak DapurNda melalui konten dan penempatan sponsor yang elegan.',
    'url' => url('sponsor.php'),
]);
?>
<main id="main-content" class="sponsor-page">
    <section class="sponsor-hero">
        <div class="shell sponsor-hero-grid">
            <div class="reveal is-visible"><span class="eyebrow eyebrow-light"><?= icon('spark') ?> Kemitraan yang terasa natural</span><h1>Temui audiens<br><em>di momen yang tepat.</em></h1><p>DapurNda menghubungkan brand dengan orang-orang saat mereka sedang mencari inspirasi, memilih bahan, dan bersiap memasak.</p><a class="button button-cream" href="#kontak">Diskusikan kolaborasi <?= icon('arrow') ?></a></div>
            <div class="sponsor-metrics reveal is-visible"><div><strong>Relevan</strong><span>Hadir dalam konteks memasak yang tepat</span></div><div><strong>Terukur</strong><span>Statistik tayang dan klik per kampanye</span></div><div><strong>Elegan</strong><span>Penempatan menyatu tanpa mengganggu</span></div></div>
        </div>
    </section>

    <section class="shell partnership-intro">
        <div class="section-heading reveal"><div><span class="eyebrow">Pilihan kemitraan</span><h2>Lebih dari sekadar <em>banner.</em></h2></div><p>Kami merancang kehadiran brand agar berguna, jelas sebagai sponsor, dan tetap terasa selaras dengan pengalaman pembaca.</p></div>
        <div class="package-grid">
            <article class="package-card featured reveal"><span class="package-tier">Utama</span><h3>Signature Partner</h3><p>Eksposur dominan untuk kampanye besar dan peluncuran produk.</p><ul><li><?= icon('check') ?> Hero carousel beranda</li><li><?= icon('check') ?> Banner halaman resep</li><li><?= icon('check') ?> Logo di seluruh footer</li><li><?= icon('check') ?> Laporan tayang dan klik</li></ul><a href="#kontak">Minta media kit <?= icon('arrow') ?></a></article>
            <article class="package-card reveal"><span class="package-tier">Emas</span><h3>Growth Partner</h3><p>Jangkauan konsisten di beberapa titik interaksi utama.</p><ul><li><?= icon('check') ?> Logo strip beranda</li><li><?= icon('check') ?> Banner detail resep</li><li><?= icon('check') ?> Logo footer</li><li><?= icon('check') ?> Statistik kampanye</li></ul><a href="#kontak">Diskusikan paket <?= icon('arrow') ?></a></article>
            <article class="package-card reveal"><span class="package-tier">Perak</span><h3>Community Partner</h3><p>Kehadiran ringan dan terpercaya bersama komunitas kami.</p><ul><li><?= icon('check') ?> Logo mitra</li><li><?= icon('check') ?> Tautan sponsor terukur</li><li><?= icon('check') ?> Periode tayang fleksibel</li><li><?= icon('check') ?> Label sponsor transparan</li></ul><a href="#kontak">Mulai berkenalan <?= icon('arrow') ?></a></article>
        </div>
    </section>

    <section class="placement-section"><div class="shell"><div class="section-heading reveal"><div><span class="eyebrow">Penempatan</span><h2>Terlihat, tanpa terasa <em>memaksa.</em></h2></div></div><div class="placement-list"><div class="reveal"><span>01</span><h3>Homepage spotlight</h3><p>Visual kampanye berukuran besar pada area paling bernilai di beranda.</p></div><div class="reveal"><span>02</span><h3>Contextual recipe</h3><p>Brand hadir di sekitar resep dan momen memasak yang relevan.</p></div><div class="reveal"><span>03</span><h3>Trusted footer</h3><p>Logo mitra mendapatkan visibilitas konsisten di seluruh halaman.</p></div></div></div></section>

    <section class="contact-section shell" id="kontak">
        <div class="contact-copy reveal"><span class="eyebrow">Mari berkenalan</span><h2>Ceritakan target<br><em>brand Anda.</em></h2><p>Kami akan menyusun opsi kemitraan sesuai tujuan, durasi, dan konteks produk Anda.</p><div class="contact-note"><span>@</span><div><small>Atau hubungi langsung</small><a href="mailto:partner@dapurnda.id">partner@dapurnda.id</a></div></div></div>
        <form class="contact-form reveal" method="post"><?= csrf_field() ?><input class="honeypot" name="website" tabindex="-1" autocomplete="off">
            <div class="field-grid"><label class="field"><span>Nama Anda *</span><input name="name" required maxlength="100" placeholder="Nama lengkap"></label><label class="field"><span>Brand / perusahaan *</span><input name="brand_name" required maxlength="140" placeholder="Nama brand"></label><label class="field field-wide"><span>Email atau WhatsApp *</span><input name="contact" required maxlength="160" placeholder="email@brand.com / 08…"></label><label class="field field-wide"><span>Rencana kolaborasi *</span><textarea name="message" required minlength="20" maxlength="3000" rows="6" placeholder="Ceritakan produk, target, dan perkiraan periode kampanye…"></textarea></label></div>
            <button class="button button-primary button-large" type="submit">Kirim permintaan <?= icon('arrow') ?></button>
        </form>
    </section>
</main>
<?php render_footer(); ?>
