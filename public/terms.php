<?php

declare(strict_types=1);

require __DIR__ . '/app.php';

render_header([
    'title' => 'Ketentuan DapurNda',
    'description' => 'Ketentuan penggunaan DapurNda dan pengiriman resep atau foto oleh kontributor.',
    'url' => url('terms.php'),
], 'trust-page');
?>
<main id="main-content" class="trust-main">
    <header class="trust-hero trust-hero-compact shell"><span class="eyebrow">Ketentuan</span><h1>Berbagi resep<br><em>dengan saling menghargai.</em></h1><p>Terakhir diperbarui 13 November 2026.</p></header>
    <article class="legal-copy shell">
        <section><h2>Penggunaan resep</h2><p>Resep disediakan sebagai panduan umum. Sesuaikan bahan dengan kondisi kesehatan, alergi, kebutuhan diet, serta keamanan pangan keluarga Anda.</p></section>
        <section><h2>Resep dan foto kiriman</h2><p>Dengan mengajukan resep, Anda menyatakan bahwa tulisan dan foto tersebut merupakan milik Anda atau boleh Anda bagikan. Anda memberi DapurNda izin non-eksklusif untuk meninjau, menyunting seperlunya, menerbitkan, dan menampilkan materi tersebut di layanan DapurNda.</p></section>
        <section><h2>Proses moderasi</h2><p>Pengajuan tidak langsung tayang. Pengelola dapat meminta perbaikan, menolak, membatalkan penerbitan, atau menghapus materi yang tidak jelas, berbahaya, melanggar hak pihak lain, atau tidak sesuai dengan tujuan DapurNda.</p></section>
        <section><h2>Nama dan kontak kontributor</h2><p>Nama yang dicantumkan dapat tampil sebagai kredit pada resep. Email atau nomor WhatsApp hanya digunakan untuk komunikasi moderasi dan tidak ditampilkan kepada pembaca.</p></section>
        <section><h2>Permintaan dan pertanyaan</h2><p>Untuk koreksi, keberatan hak cipta, atau penghapusan kiriman, hubungi <a href="mailto:halo@dapurnda.id">halo@dapurnda.id</a>.</p></section>
    </article>
</main>
<?php render_footer(['hide_invite' => true]); ?>
