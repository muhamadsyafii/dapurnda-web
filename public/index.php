<?php

declare(strict_types=1);

require __DIR__ . '/app.php';

$recipes = popular_recipes(6);
$totalRecipes = (int) db()->query("SELECT COUNT(*) FROM recipes WHERE status = 'approved'")->fetchColumn();

render_header();
?>
<main id="main-content">
    <section class="hero">
        <div class="hero-orb hero-orb-one"></div><div class="hero-orb hero-orb-two"></div>
        <div class="shell hero-grid">
            <div class="hero-copy reveal is-visible">
                <span class="eyebrow"><?= icon('spark') ?> Resep pilihan, cerita rumahan</span>
                <h1>Masak enak,<br><em>hidup lebih hangat.</em></h1>
                <p>Resep rumahan dengan bahan yang mudah ditemukan, langkah yang tidak membingungkan, dan tampilan yang nyaman diikuti sambil memasak.</p>
                <div class="hero-actions">
                    <a class="button button-primary button-large" href="<?= e(url('search.php')) ?>">Temukan menu hari ini <?= icon('arrow') ?></a>
                    <a class="hero-text-link" href="#resep"><span><?= icon('utensils') ?></span> Lihat menu populer</a>
                </div>
                <div class="hero-promises" aria-label="Keunggulan DapurNda">
                    <span><?= icon('check') ?> Bahan familiar</span><span><?= icon('check') ?> Langkah jelas</span><span><?= icon('check') ?> Ramah pemula</span>
                </div>
            </div>
            <div class="hero-visual reveal is-visible" aria-label="Sajian pilihan DapurNda" data-hero-motion>
                <div class="hero-photo-frame"><img src="<?= e(asset('images/hero-dapurnda.webp')) ?>" alt="Hidangan rumahan Indonesia: nasi goreng, ayam bakar, dan gado-gado" width="900" height="940" fetchpriority="high"></div>
                <div class="floating-note note-one"><?= icon('spark') ?><span><strong>Ramah pemula</strong><small>Langkah demi langkah</small></span></div>
                <div class="floating-note note-two"><?= icon('clock') ?><span><strong>25 menit</strong><small>Praktis dibuat</small></span></div>
                <span class="handwritten">dibuat dengan rasa <?= icon('heart') ?></span>
            </div>
        </div>
    </section>

    <section class="recipe-section shell" id="resep">
        <div class="section-heading reveal">
            <div><span class="eyebrow">Paling sering dibuka</span><h2>Menu Favorit<br><em>pembaca DapurNda</em></h2></div>
            <p>Enam resep yang paling sering dilihat. Cocok sebagai titik awal saat belum tahu ingin memasak apa.</p>
        </div>
        <div class="recipe-grid home-popular-grid">
            <?php foreach ($recipes as $recipe): ?><?= recipe_card($recipe) ?><?php endforeach; ?>
        </div>
        <div class="home-see-all reveal">
            <p>Masih ada <?= max(0, $totalRecipes - count($recipes)) ?> resep lain berdasarkan bahan, kategori, dan waktu memasak.</p>
            <a class="button button-dark button-large" href="<?= e(url('search.php')) ?>">Lihat semua <?= $totalRecipes ?> resep <?= icon('arrow') ?></a>
        </div>
    </section>

    <section class="share-cta">
        <div class="shell share-cta-inner reveal">
            <div><span class="eyebrow eyebrow-light">Resep keluarga untuk DapurNda</span><h2>Punya resep andalan keluarga?</h2><p>Tulis bahan dan langkahnya dengan mudah. Setelah diajukan, resep akan ditinjau sebelum diterbitkan agar jelas dan siap diikuti pembaca.</p></div>
            <a class="button button-cream" href="<?= e(url('add.php')) ?>">Mulai menulis resep <?= icon('arrow') ?></a>
        </div>
    </section>
</main>
<?php render_footer(['hide_invite' => true]); ?>
