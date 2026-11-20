<?php

declare(strict_types=1);

require dirname(__DIR__) . '/app.php';

$search = mb_substr(trim((string) ($_GET['q'] ?? '')), 0, 100);
$category = mb_substr(trim((string) ($_GET['category'] ?? '')), 0, 90);
$limit = max(1, min((int) ($_GET['limit'] ?? 10), 24));
$offset = max(0, (int) ($_GET['offset'] ?? 0));
$recipes = search_recipes($search, $category, $limit, $offset);
$total = count_recipes($search, $category);
$html = '';
foreach ($recipes as $recipe) {
    $html .= recipe_card($recipe);
}
json_response([
    'html' => $html,
    'count' => count($recipes),
    'total' => $total,
    'offset' => $offset,
    'has_more' => ($offset + count($recipes)) < $total,
]);
