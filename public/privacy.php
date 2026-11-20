<?php

declare(strict_types=1);

require __DIR__ . '/app.php';

render_header([
    'title' => 'Kebijakan Privasi',
    'description' => 'Penjelasan singkat mengenai data yang diproses DapurNda ketika Anda membaca, menyimpan, atau mengirimkan resep.',
    'url' => url('privacy.php'),
], 'trust-page');
?>
<main id="main-content" class="trust-main">
    <header class="trust-hero trust-hero-compact shell"><span class="eyebrow">Privasi</span><h1>Data Anda,<br><em>dipakai seperlunya.</em></h1><p>Terakhir diperbarui 13 November 2026.</p></header>
    <article class="legal-copy shell">
        <section><h2>Data yang kami terima</h2><p>Saat Anda mengajukan resep, DapurNda menerima isi resep, foto, nama pengirim bila dicantumkan, serta email atau nomor WhatsApp bila Anda memilih untuk memberikannya. Sistem juga mencatat alamat IP secara terbatas untuk mencegah spam.</p></section>
        <section><h2>Tujuan penggunaan</h2><p>Data digunakan untuk meninjau dan menerbitkan resep, menghubungi kontributor mengenai status atau koreksi resep, menjaga keamanan layanan, dan memahami resep yang paling sering dibaca.</p></section>
        <section><h2>Penyimpanan lokal pada perangkat</h2><p>Fitur Simpan Resep dan progres memasak menggunakan penyimpanan browser Anda. Informasi tersebut tidak dikirim ke server dan dapat dihapus melalui pengaturan browser.</p></section>
        <section><h2>Kontak tidak ditampilkan</h2><p>Email atau nomor WhatsApp kontributor hanya dapat dilihat pengelola dan tidak diterbitkan pada halaman resep. Jangan menuliskan informasi sensitif di dalam deskripsi resep.</p></section>
        <section><h2>Permintaan perubahan atau penghapusan</h2><p>Anda dapat meminta koreksi atau penghapusan data dengan menghubungi <a href="mailto:halo@dapurnda.id">halo@dapurnda.id</a> dan menyebutkan judul resep terkait.</p></section>
    </article>
</main>
<?php render_footer(['hide_invite' => true]); ?>
