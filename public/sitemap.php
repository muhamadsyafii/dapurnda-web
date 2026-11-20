<?php

declare(strict_types=1);

require __DIR__ . '/app.php';

header('Content-Type: application/xml; charset=utf-8');
$recipes = db_all("SELECT slug, updated_at FROM recipes WHERE status = 'approved' ORDER BY updated_at DESC");
echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url><loc><?= e(url()) ?></loc><changefreq>daily</changefreq><priority>1.0</priority></url>
    <url><loc><?= e(url('search.php')) ?></loc><changefreq>daily</changefreq><priority>0.9</priority></url>
    <url><loc><?= e(url('add.php')) ?></loc><changefreq>monthly</changefreq><priority>0.6</priority></url>
    <url><loc><?= e(url('about.php')) ?></loc><changefreq>monthly</changefreq><priority>0.5</priority></url>
    <url><loc><?= e(url('privacy.php')) ?></loc><changefreq>yearly</changefreq><priority>0.3</priority></url>
    <url><loc><?= e(url('terms.php')) ?></loc><changefreq>yearly</changefreq><priority>0.3</priority></url>
    <?php if (config('features.public_sponsors', false)): ?><url><loc><?= e(url('sponsor.php')) ?></loc><changefreq>monthly</changefreq><priority>0.5</priority></url><?php endif; ?>
    <?php foreach ($recipes as $recipe): ?>
    <url><loc><?= e(url('recipe.php', ['slug' => $recipe['slug']])) ?></loc><lastmod><?= e(substr($recipe['updated_at'], 0, 10)) ?></lastmod><changefreq>monthly</changefreq><priority>0.8</priority></url>
    <?php endforeach; ?>
</urlset>
