<?php

declare(strict_types=1);

require __DIR__ . '/app.php';

$search = trim((string) ($_GET['q'] ?? ''));
$category = trim((string) ($_GET['category'] ?? ''));
$pageSize = 10;
$recipes = search_recipes($search, $category, $pageSize);
$totalMatchingRecipes = count_recipes($search, $category);
$categories = array_values(array_filter(get_categories(true), static fn (array $item): bool => (int) $item['recipe_count'] > 0));
$totalRecipes = (int) db()->query("SELECT COUNT(*) FROM recipes WHERE status = 'approved'")->fetchColumn();
$currentCategoryLabel = 'Semua kategori';
foreach ($categories as $item) {
    if ($item['slug'] === $category) {
        $currentCategoryLabel = $item['name'];
        break;
    }
}

render_header([
    'title' => 'Cari Resep Rumahan',
    'description' => 'Cari resep DapurNda berdasarkan nama masakan, bahan, atau kategori lalu buka tutorial memasaknya langkah demi langkah.',
    'url' => url('search.php', array_filter(['q' => $search, 'category' => $category])),
]);
?>
<main id="main-content" class="search-page">
    <section class="search-page-hero">
        <div class="shell">
            <a class="search-back" href="<?= e(url()) ?>"><?= icon('arrow') ?> Kembali ke beranda</a>
            <div class="search-page-heading reveal is-visible">
                <span class="eyebrow"><?= icon('spark') ?> Temukan menu yang pas</span>
                <h1>Mau masak apa<br><em>hari ini?</em></h1>
                <p>Cari nama hidangan atau bahan yang tersedia. Setelah menemukan yang cocok, sentuh kartunya untuk membuka bahan dan tutorial lengkap.</p>
            </div>

            <div class="discovery-panel search-page-panel reveal is-visible">
                <form class="discovery-search" action="<?= e(url('search.php')) ?>" method="get" data-live-search data-search-endpoint="<?= e(url('api/search.php')) ?>">
                    <div class="discovery-label"><span><?= icon('search') ?></span><div><strong>Cari resep atau bahan</strong><small>Misalnya “ayam”, “telur”, atau “sarapan”</small></div></div>
                    <label class="search-input-wrap" for="recipe-search"><span class="sr-only">Cari resep atau bahan</span><input id="recipe-search" name="q" value="<?= e($search) ?>" placeholder="Ketik nama resep atau bahan…" autocomplete="off"><button type="submit"><span>Cari</span><?= icon('arrow') ?></button></label>
                    <div class="mobile-category-filter">
                        <span>Filter kategori</span>
                        <div class="category-picker" data-category-picker>
                            <button class="category-picker-trigger" type="button" data-category-trigger aria-expanded="false"><span><small>Kategori pilihan</small><strong data-category-current><?= e($currentCategoryLabel) ?></strong></span><?= icon('arrow') ?></button>
                            <div class="category-picker-menu" data-category-menu hidden>
                                <button type="button" class="<?= $category === '' ? 'is-active' : '' ?>" data-category-option data-value="" data-label="Semua kategori"><span>Semua kategori</span><b><?= $totalRecipes ?></b></button>
                                <?php foreach ($categories as $item): ?><button type="button" class="<?= $category === $item['slug'] ? 'is-active' : '' ?>" data-category-option data-value="<?= e($item['slug']) ?>" data-label="<?= e($item['name']) ?>"><span><?= e($item['name']) ?></span><b><?= (int) $item['recipe_count'] ?></b></button><?php endforeach; ?>
                            </div>
                        </div>
                        <select class="category-native-select" name="category" data-category-select tabindex="-1" aria-hidden="true"><option value="">Semua kategori</option><?php foreach ($categories as $item): ?><option value="<?= e($item['slug']) ?>"<?= selected($category, $item['slug']) ?>><?= e($item['name']) ?></option><?php endforeach; ?></select>
                        <div class="view-switch mobile-category-view-switch" data-view-switch role="group" aria-label="Ubah tampilan resep"><button class="is-active" type="button" data-view="grid" aria-pressed="true"><?= icon('grid') ?><span>Grid</span></button><button type="button" data-view="list" aria-pressed="false"><?= icon('list') ?><span>List</span></button></div>
                    </div>
                </form>
                <div class="category-scroll" role="navigation" aria-label="Filter kategori" data-category-filters>
                    <a href="<?= e(url('search.php', $search ? ['q' => $search] : [])) ?>" class="category-chip<?= $category === '' ? ' is-active' : '' ?>" data-category="">Semua <span><?= $totalRecipes ?></span></a>
                    <?php foreach ($categories as $item): ?>
                        <a href="<?= e(url('search.php', array_filter(['q' => $search, 'category' => $item['slug']]))) ?>" class="category-chip<?= $category === $item['slug'] ? ' is-active' : '' ?>" data-category="<?= e($item['slug']) ?>"><?= e($item['name']) ?> <span><?= (int) $item['recipe_count'] ?></span></a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>

    <section class="search-results shell" aria-label="Hasil pencarian resep">
        <div class="search-results-heading">
            <div class="search-results-copy"><div class="search-summary" data-search-summary aria-live="polite"><?= $search ? 'Hasil untuk “' . e($search) . '” · ' : '' ?><span><?= $totalMatchingRecipes ?></span> resep ditemukan</div><p>Sentuh kartu untuk melihat bahan dan cara memasaknya.</p></div>
            <div class="view-switch results-view-switch" data-view-switch role="group" aria-label="Ubah tampilan resep"><button class="is-active" type="button" data-view="grid" aria-pressed="true"><?= icon('grid') ?><span>Grid</span></button><button type="button" data-view="list" aria-pressed="false"><?= icon('list') ?><span>List</span></button></div>
        </div>
        <div class="recipe-grid is-view-grid" data-recipe-grid>
            <?php foreach ($recipes as $recipe): ?><?= recipe_card($recipe) ?><?php endforeach; ?>
        </div>
        <div class="load-more-wrap<?= count($recipes) >= $totalMatchingRecipes ? ' is-hidden' : '' ?>" data-load-more-wrap aria-live="polite">
            <button class="button button-outline button-large" type="button" data-load-more data-page-size="<?= $pageSize ?>"><span data-load-more-label>Muat 10 resep berikutnya</span> <?= icon('arrow') ?></button>
            <div class="infinite-load-status" data-infinite-status><span class="infinite-load-spinner" aria-hidden="true"></span><span data-infinite-label>Gulir sedikit lagi untuk melihat resep berikutnya</span></div>
            <small><b data-loaded-count><?= count($recipes) ?></b> dari <b data-total-count><?= $totalMatchingRecipes ?></b> resep sudah ditampilkan</small>
        </div>
        <div class="empty-state<?= $recipes ? ' is-hidden' : '' ?>" data-empty-state>
            <span><?= icon('search') ?></span><h2>Belum menemukan yang cocok</h2><p>Coba nama bahan yang lebih sederhana, misalnya “ayam”, “nasi”, atau “telur”.</p><a class="button button-outline" href="<?= e(url('search.php')) ?>">Lihat semua resep</a>
        </div>
    </section>
</main>
<?php render_footer(); ?>
