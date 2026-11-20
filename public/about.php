<?php

declare(strict_types=1);

require __DIR__ . '/app.php';

render_header([
    'title' => 'Tentang DapurNda',
    'description' => 'Kenali DapurNda, panduan resep rumahan Indonesia yang jelas, hangat, dan ramah bagi siapa pun yang sedang belajar memasak.',
    'url' => url('about.php'),
], 'trust-page');
?>
<main id="main-content" class="trust-main">
    <header class="trust-hero shell"><span class="eyebrow"><?= icon('spark') ?> Tentang DapurNda</span><h1>Masak rumahan<br><em>tanpa rasa takut salah.</em></h1><p>DapurNda dibuat untuk membantu keluarga, pemula, dan siapa pun yang ingin menyajikan makanan hangat dengan petunjuk yang mudah diikuti.</p></header>
    <section class="trust-grid shell">
        <article><span>01</span><h2>Resep yang dapat dipraktikkan</h2><p>Kami mengutamakan bahan yang mudah ditemukan, takaran yang masuk akal, dan langkah yang tidak melompat-lompat.</p></article>
        <article><span>02</span><h2>Ditinjau sebelum tayang</h2><p>Resep kiriman pembaca diperiksa lebih dahulu agar judul, bahan, foto, dan urutan memasaknya cukup jelas.</p></article>
        <article><span>03</span><h2>Cerita dari dapur keluarga</h2><p>Kami percaya resep bukan hanya daftar bahan, tetapi juga pengetahuan keluarga yang layak diteruskan.</p></article>
    </section>
    <section class="trust-contact shell"><div><span class="eyebrow">Ada yang ingin ditanyakan?</span><h2>Kami senang mendengar dari Anda.</h2><p>Hubungi pengelola untuk koreksi resep, pertanyaan privasi, atau hal lain mengenai DapurNda.</p></div><a class="button button-primary" href="mailto:halo@dapurnda.id">halo@dapurnda.id <?= icon('arrow') ?></a></section>
</main>
<?php render_footer(['hide_invite' => true]); ?>
