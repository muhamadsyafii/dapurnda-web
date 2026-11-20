<?php

declare(strict_types=1);

require __DIR__ . '/app.php';

$slug = trim((string) ($_GET['slug'] ?? ''));
$recipe = recipe_by_slug($slug);
if (!$recipe) {
    http_response_code(404);
    render_header(['title' => 'Resep tidak ditemukan', 'robots' => 'noindex, follow']);
    echo '<main id="main-content" class="shell simple-state"><span class="eyebrow">404</span><h1>Resep ini belum tersedia.</h1><p>Mungkin resep sedang ditinjau atau alamatnya berubah.</p><a class="button button-primary" href="' . e(url()) . '">Jelajahi resep lain</a></main>';
    render_footer();
    exit;
}

if (record_recipe_view((int) $recipe['id'])) {
    $recipe['views'] = (int) $recipe['views'] + 1;
}
$related = array_values(array_filter(search_recipes('', $recipe['category_slug'], 4), static fn ($item) => $item['id'] !== $recipe['id']));
$detailSponsors = config('features.public_sponsors', false) ? active_sponsors('detail') : [];
record_sponsor_impressions($detailSponsors);
$recipeUrl = url('recipe.php', ['slug' => $recipe['slug']]);
$imageUrl = recipe_photo_url($recipe['photo_filename']) ?: url('assets/images/og-cover.jpg');
$imageDimensions = recipe_photo_dimensions($recipe['photo_filename']);
$jsonLd = [
    '@context' => 'https://schema.org', '@type' => 'Recipe', 'name' => $recipe['title'],
    'description' => $recipe['description'], 'image' => [$imageUrl],
    'author' => ['@type' => 'Person', 'name' => $recipe['author_name'] ?: 'Komunitas DapurNda'],
    'datePublished' => $recipe['published_at'] ?: $recipe['created_at'],
    'prepTime' => 'PT' . (int) $recipe['cook_time'] . 'M', 'cookTime' => 'PT' . (int) $recipe['cook_time'] . 'M',
    'recipeYield' => (int) $recipe['servings'] . ' porsi', 'recipeCategory' => $recipe['category_name'],
    'recipeIngredient' => array_map(static fn ($item) => trim($item['amount'] . ' ' . $item['name']), $recipe['ingredients']),
    'recipeInstructions' => array_map(static fn ($item, $index) => ['@type' => 'HowToStep', 'position' => $index + 1, 'text' => $item['instruction']], $recipe['steps'], array_keys($recipe['steps'])),
];

render_header([
    'title' => $recipe['title'], 'description' => mb_strimwidth($recipe['description'], 0, 155, '…'),
    'image' => $imageUrl, 'image_width' => $imageDimensions[0], 'image_height' => $imageDimensions[1],
    'url' => $recipeUrl, 'type' => 'article', 'json_ld' => $jsonLd,
], 'recipe-page');
?>
<aside class="recipe-mobile-bar" data-recipe-mobile-bar aria-hidden="true">
    <a class="recipe-mobile-back" href="<?= e(url('search.php', ['category' => $recipe['category_slug']])) ?>" aria-label="Kembali ke daftar resep"><?= icon('arrow') ?></a>
    <span><small>Sedang dibaca</small><strong><?= e($recipe['title']) ?></strong></span>
    <button type="button" data-save-recipe data-id="<?= (int) $recipe['id'] ?>" aria-label="Simpan resep"><?= icon('heart') ?><span class="sr-only">Simpan</span></button>
    <button type="button" data-mobile-share aria-label="Bagikan resep"><?= icon('share') ?></button>
</aside>
<main id="main-content">
    <div class="shell recipe-breadcrumb"><a href="<?= e(url()) ?>">Beranda</a><span>/</span><a href="<?= e(url('', ['category' => $recipe['category_slug']])) ?>"><?= e($recipe['category_name']) ?></a><span>/</span><span><?= e($recipe['title']) ?></span></div>
    <article>
        <header class="recipe-hero shell">
            <div class="recipe-title-block reveal is-visible">
                <div class="recipe-labels"><span><?= e($recipe['category_name']) ?></span><?php if ($recipe['sponsor_name']): ?><span class="sponsored-badge static">Disponsori · <?= e($recipe['sponsor_name']) ?></span><?php endif; ?></div>
                <h1><?= e($recipe['title']) ?></h1>
                <p><?= e($recipe['description']) ?></p>
                <div class="recipe-author"><span class="author-avatar"><?= e(mb_strtoupper(mb_substr($recipe['author_name'] ?: 'D', 0, 1))) ?></span><span>Resep oleh <strong><?= e($recipe['author_name'] ?: 'Komunitas DapurNda') ?></strong><small>Diterbitkan <?= e(format_date($recipe['published_at'] ?: $recipe['created_at'])) ?></small></span></div>
            </div>
            <div class="recipe-image tone-<?= e(((int) $recipe['id'] % 6) + 1) ?> reveal is-visible">
                <?php if ($recipe['photo_filename']): ?><img src="<?= e($imageUrl) ?>" alt="<?= e($recipe['title']) ?>" width="900" height="680"><?php else: ?><span class="food-illustration hero-food"><i></i><b></b></span><?php endif; ?>
            </div>
        </header>

        <div class="recipe-info-bar shell reveal">
            <div><?= icon('clock') ?><span><small>Waktu memasak</small><strong><?= (int) $recipe['cook_time'] ?> menit</strong></span></div>
            <div><?= icon('users') ?><span><small>Hasil</small><strong><?= (int) $recipe['servings'] ?> porsi</strong></span></div>
            <div><?= icon('gauge') ?><span><small>Kesulitan</small><strong><?= e($recipe['difficulty']) ?></strong></span></div>
            <div><?= icon('eye') ?><span><small>Sudah dilihat</small><strong><?= e(format_view_count((int) $recipe['views'])) ?> kali</strong></span></div>
            <button class="save-button" type="button" data-save-recipe data-id="<?= (int) $recipe['id'] ?>"><?= icon('heart') ?><span>Simpan</span></button>
        </div>

        <div class="beginner-tip shell reveal"><span><?= icon('spark') ?></span><div><strong>Baru belajar masak?</strong><p>Baca semua langkah satu kali sebelum mulai, lalu siapkan dan centang bahan satu per satu. Tidak perlu terburu-buru.</p></div></div>

        <div class="shell recipe-content-grid">
            <aside class="ingredients-card reveal">
                <span class="section-number">01</span><h2>Bahan-bahan</h2><p class="ingredient-hint">Centang bahan yang sudah siap</p>
                <div class="ingredient-progress" data-ingredient-progress><div><span>Bahan siap</span><strong><b data-ingredients-done>0</b>/<?= count($recipe['ingredients']) ?></strong></div><span><i data-ingredient-bar></i></span></div>
                <div class="ingredient-list" data-ingredient-list-check>
                    <?php foreach ($recipe['ingredients'] as $index => $ingredient): ?>
                        <label><input type="checkbox"><span class="custom-check"><?= icon('check') ?></span><span><strong><?= e($ingredient['name']) ?></strong><small><?= e($ingredient['amount']) ?></small></span></label>
                    <?php endforeach; ?>
                </div>
            </aside>
            <section class="steps-section reveal">
                <span class="section-number">02</span><h2>Mode memasak</h2><p>Selesaikan satu langkah, lalu tandai sebelum lanjut ke langkah berikutnya.</p>
                <div class="cooking-progress" data-cooking-progress><div><span>Progres memasak</span><strong><b data-steps-done>0</b>/<?= count($recipe['steps']) ?> selesai</strong></div><span class="cooking-progress-track"><i data-cooking-bar></i></span><button type="button" data-reset-cooking>Ulangi dari awal</button></div>
                <ol class="steps-list" data-cooking-steps>
                    <?php foreach ($recipe['steps'] as $index => $step): ?><li class="cooking-step"><span><?= str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) ?></span><div><p><?= e($step['instruction']) ?></p><button type="button" data-step-check aria-pressed="false"><span class="step-check-icon"><?= icon('check') ?></span><span class="step-check-copy"><strong data-step-check-label>Tandai sudah selesai</strong><small data-step-check-hint>Sentuh setelah langkah ini selesai</small></span></button></div></li><?php endforeach; ?>
                </ol>
            </section>
        </div>
    </article>

    <?php foreach ($detailSponsors as $sponsor): ?>
    <aside class="detail-sponsor shell reveal"><div><span class="sponsored-kicker">Dipersembahkan bersama</span><h2><?= e($sponsor['name']) ?></h2><p><?= e($sponsor['description']) ?></p><a href="<?= e(url('go.php', ['id' => $sponsor['id']])) ?>" target="_blank" rel="sponsored noopener">Kunjungi mitra <?= icon('arrow') ?></a></div><?php if ($sponsor['banner_filename']): ?><img src="<?= e(url('uploads/sponsors/' . $sponsor['banner_filename'])) ?>" alt="<?= e($sponsor['name']) ?>"><?php endif; ?></aside>
    <?php endforeach; ?>

    <section class="share-recipe shell reveal" id="bagikan-resep" data-share data-url="<?= e($recipeUrl) ?>" data-title="<?= e($recipe['title']) ?>">
        <div><span class="eyebrow">Sebarkan rasa</span><h2>Bagikan resep ini</h2><p>Kirim ke teman masakmu atau simpan untuk akhir pekan nanti.</p></div>
        <div class="share-buttons">
            <a class="share-wa" href="https://wa.me/?text=<?= rawurlencode($recipe['title'] . ' — ' . $recipeUrl) ?>" target="_blank" rel="noopener" aria-label="Bagikan ke WhatsApp"><?= social_icon('whatsapp') ?><span>WhatsApp</span></a>
            <a class="share-telegram" href="https://t.me/share/url?url=<?= rawurlencode($recipeUrl) ?>&text=<?= rawurlencode($recipe['title']) ?>" target="_blank" rel="noopener" aria-label="Bagikan ke Telegram"><?= social_icon('telegram') ?><span>Telegram</span></a>
            <a class="share-facebook" href="https://www.facebook.com/sharer/sharer.php?u=<?= rawurlencode($recipeUrl) ?>" target="_blank" rel="noopener" aria-label="Bagikan ke Facebook"><?= social_icon('facebook') ?><span>Facebook</span></a>
            <button class="share-native" type="button" data-native-share><?= icon('share') ?><span>Bagikan</span></button>
            <button class="share-copy" type="button" data-copy-link><?= icon('link') ?><span data-copy-label>Salin tautan</span></button>
        </div>
    </section>

    <?php if ($related): ?><section class="related-section shell"><div class="section-heading"><div><span class="eyebrow">Lanjut memasak</span><h2>Resep yang mungkin <em>kamu suka</em></h2></div></div><div class="recipe-grid recipe-grid-three"><?php foreach (array_slice($related, 0, 3) as $item): ?><?= recipe_card($item) ?><?php endforeach; ?></div></section><?php endif; ?>
</main>
<?php render_footer(); ?>
