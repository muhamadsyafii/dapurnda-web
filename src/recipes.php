<?php

declare(strict_types=1);

function get_categories(bool $withCounts = false): array
{
    if (!$withCounts) {
        return db_all('SELECT * FROM categories ORDER BY name');
    }
    return db_all("SELECT c.*, COUNT(r.id) AS recipe_count
        FROM categories c
        LEFT JOIN recipes r ON r.category_id = c.id AND r.status = 'approved'
        GROUP BY c.id ORDER BY c.name");
}

function search_recipes(string $search = '', string $category = '', int $limit = 24, int $offset = 0): array
{
    $where = ["r.status = 'approved'"];
    $params = [];
    if ($search !== '') {
        $where[] = '(r.title LIKE ? OR r.description LIKE ? OR c.name LIKE ?)';
        $term = '%' . $search . '%';
        array_push($params, $term, $term, $term);
    }
    if ($category !== '') {
        $where[] = 'c.slug = ?';
        $params[] = $category;
    }
    $safeLimit = max(1, min($limit, 60));
    $safeOffset = max(0, $offset);

    return db_all("SELECT r.*, c.name AS category_name, c.slug AS category_slug, s.name AS sponsor_name
        FROM recipes r
        JOIN categories c ON c.id = r.category_id
        LEFT JOIN sponsors s ON s.id = r.sponsor_id
        WHERE " . implode(' AND ', $where) . "
        ORDER BY (r.sponsor_id IS NOT NULL) DESC, COALESCE(r.published_at, r.created_at) DESC, r.id DESC
        LIMIT {$safeLimit} OFFSET {$safeOffset}", $params);
}

function count_recipes(string $search = '', string $category = ''): int
{
    $where = ["r.status = 'approved'"];
    $params = [];
    if ($search !== '') {
        $where[] = '(r.title LIKE ? OR r.description LIKE ? OR c.name LIKE ?)';
        $term = '%' . $search . '%';
        array_push($params, $term, $term, $term);
    }
    if ($category !== '') {
        $where[] = 'c.slug = ?';
        $params[] = $category;
    }
    $statement = db()->prepare('SELECT COUNT(*) FROM recipes r JOIN categories c ON c.id = r.category_id WHERE ' . implode(' AND ', $where));
    $statement->execute($params);
    return (int) $statement->fetchColumn();
}

function popular_recipes(int $limit = 5): array
{
    $safeLimit = max(1, min($limit, 20));
    return db_all("SELECT r.*, c.name AS category_name, c.slug AS category_slug, s.name AS sponsor_name
        FROM recipes r
        JOIN categories c ON c.id = r.category_id
        LEFT JOIN sponsors s ON s.id = r.sponsor_id
        WHERE r.status = 'approved'
        ORDER BY r.views DESC, COALESCE(r.published_at, r.created_at) DESC, r.id DESC
        LIMIT {$safeLimit}");
}

function record_recipe_view(int $recipeId): bool
{
    if ($recipeId < 1 || ($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET' || is_admin()) {
        return false;
    }

    $userAgent = strtolower((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''));
    if ($userAgent !== '' && preg_match('/bot|crawler|spider|slurp|preview|facebookexternalhit|whatsapp|telegrambot/', $userAgent)) {
        return false;
    }

    $viewedRecipes = $_SESSION['viewed_recipes'] ?? [];
    if (isset($viewedRecipes[$recipeId])) {
        return false;
    }

    db()->prepare('UPDATE recipes SET views = views + 1 WHERE id = ?')->execute([$recipeId]);
    $_SESSION['viewed_recipes'][$recipeId] = time();
    return true;
}

function format_view_count(int $views): string
{
    return number_format(max(0, $views), 0, ',', '.');
}

function recipe_by_slug(string $slug, bool $includeUnapproved = false): ?array
{
    $sql = "SELECT r.*, c.name AS category_name, c.slug AS category_slug,
            s.name AS sponsor_name, s.id AS linked_sponsor_id
        FROM recipes r
        JOIN categories c ON c.id = r.category_id
        LEFT JOIN sponsors s ON s.id = r.sponsor_id
        WHERE r.slug = ?";
    if (!$includeUnapproved) {
        $sql .= " AND r.status = 'approved'";
    }
    $recipe = db_one($sql . ' LIMIT 1', [$slug]);
    if (!$recipe) {
        return null;
    }
    $recipe['ingredients'] = db_all('SELECT * FROM recipe_ingredients WHERE recipe_id = ? ORDER BY sort_order', [$recipe['id']]);
    $recipe['steps'] = db_all('SELECT * FROM recipe_steps WHERE recipe_id = ? ORDER BY sort_order', [$recipe['id']]);
    return $recipe;
}

function recipe_by_id(int $id): ?array
{
    $recipe = db_one('SELECT r.*, c.name AS category_name FROM recipes r JOIN categories c ON c.id = r.category_id WHERE r.id = ?', [$id]);
    if (!$recipe) {
        return null;
    }
    $recipe['ingredients'] = db_all('SELECT * FROM recipe_ingredients WHERE recipe_id = ? ORDER BY sort_order', [$id]);
    $recipe['steps'] = db_all('SELECT * FROM recipe_steps WHERE recipe_id = ? ORDER BY sort_order', [$id]);
    return $recipe;
}

function unique_recipe_slug(string $title, ?int $ignoreId = null): string
{
    $base = slugify($title);
    $slug = $base;
    $counter = 2;
    while (true) {
        $sql = 'SELECT id FROM recipes WHERE slug = ?';
        $params = [$slug];
        if ($ignoreId !== null) {
            $sql .= ' AND id <> ?';
            $params[] = $ignoreId;
        }
        if (!db_one($sql . ' LIMIT 1', $params)) {
            return $slug;
        }
        $slug = $base . '-' . $counter++;
    }
}

function validate_recipe_input(array $input, bool $requirePublishingConsent = false): array
{
    $title = trim((string) ($input['title'] ?? ''));
    $description = trim((string) ($input['description'] ?? ''));
    $author = trim((string) ($input['author_name'] ?? ''));
    $contact = trim((string) ($input['contributor_contact'] ?? ''));
    $publishingConsent = !empty($input['publishing_consent']);
    $categoryId = (int) ($input['category_id'] ?? 0);
    $cookTime = (int) ($input['cook_time'] ?? 0);
    $servings = (int) ($input['servings'] ?? 0);
    $difficulty = (string) ($input['difficulty'] ?? 'Mudah');
    $ingredientNames = $input['ingredient_name'] ?? [];
    $ingredientAmounts = $input['ingredient_amount'] ?? [];
    $stepsInput = $input['steps'] ?? [];

    if (mb_strlen($title) < 5 || mb_strlen($title) > 160) {
        throw new InvalidArgumentException('Judul resep harus 5–160 karakter.');
    }
    if (mb_strlen($description) < 30 || mb_strlen($description) > 3000) {
        throw new InvalidArgumentException('Cerita singkat resep harus 30–3.000 karakter.');
    }
    if (!db_one('SELECT id FROM categories WHERE id = ?', [$categoryId])) {
        throw new InvalidArgumentException('Pilih kategori yang tersedia.');
    }
    if ($cookTime < 1 || $cookTime > 1440 || $servings < 1 || $servings > 100) {
        throw new InvalidArgumentException('Waktu masak atau jumlah porsi tidak valid.');
    }
    if (!in_array($difficulty, ['Mudah', 'Sedang', 'Menantang'], true)) {
        throw new InvalidArgumentException('Tingkat kesulitan tidak valid.');
    }
    if (mb_strlen($author) > 100) {
        throw new InvalidArgumentException('Nama pengirim maksimal 100 karakter.');
    }
    if (mb_strlen($contact) > 160) {
        throw new InvalidArgumentException('Email atau nomor WhatsApp maksimal 160 karakter.');
    }
    if ($requirePublishingConsent && !$publishingConsent) {
        throw new InvalidArgumentException('Persetujuan penerbitan resep dan foto perlu dicentang.');
    }

    $ingredients = [];
    foreach ((array) $ingredientNames as $index => $name) {
        $name = trim((string) $name);
        $amount = trim((string) ($ingredientAmounts[$index] ?? ''));
        if ($name === '' && $amount === '') {
            continue;
        }
        if ($name === '' || mb_strlen($name) > 180 || mb_strlen($amount) > 100) {
            throw new InvalidArgumentException('Periksa kembali nama dan takaran bahan.');
        }
        $ingredients[] = ['name' => $name, 'amount' => $amount];
    }
    $steps = [];
    foreach ((array) $stepsInput as $step) {
        $step = trim((string) $step);
        if ($step === '') {
            continue;
        }
        if (mb_strlen($step) > 2000) {
            throw new InvalidArgumentException('Setiap langkah maksimal 2.000 karakter.');
        }
        $steps[] = $step;
    }
    if (count($ingredients) < 2 || count($ingredients) > 40 || count($steps) < 2 || count($steps) > 40) {
        throw new InvalidArgumentException('Resep memerlukan 2–40 bahan dan 2–40 langkah.');
    }

    return compact('title', 'description', 'author', 'contact', 'publishingConsent', 'categoryId', 'cookTime', 'servings', 'difficulty', 'ingredients', 'steps');
}

function create_recipe(array $data, ?string $photoFilename): int
{
    $connection = db();
    $connection->beginTransaction();
    try {
        $statement = $connection->prepare("INSERT INTO recipes
            (category_id, title, slug, description, cook_time, servings, difficulty, photo_filename, status, author_name, contributor_contact, publishing_consent_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?, ?, ?)");
        $statement->execute([
            $data['categoryId'], $data['title'], unique_recipe_slug($data['title']), $data['description'],
            $data['cookTime'], $data['servings'], $data['difficulty'], $photoFilename, $data['author'] ?: null,
            $data['contact'] ?: null, $data['publishingConsent'] ? gmdate('Y-m-d H:i:s') : null,
        ]);
        $id = (int) $connection->lastInsertId();
        save_recipe_parts($id, $data['ingredients'], $data['steps']);
        $connection->commit();
        return $id;
    } catch (Throwable $exception) {
        $connection->rollBack();
        throw $exception;
    }
}

function update_recipe(int $id, array $data, ?string $photoFilename = null, ?int $sponsorId = null): void
{
    $existing = recipe_by_id($id);
    if (!$existing) {
        throw new InvalidArgumentException('Resep tidak ditemukan.');
    }
    $photo = $photoFilename ?: $existing['photo_filename'];
    $connection = db();
    $connection->beginTransaction();
    try {
        $statement = $connection->prepare('UPDATE recipes SET category_id = ?, sponsor_id = ?, title = ?, slug = ?, description = ?, cook_time = ?, servings = ?, difficulty = ?, photo_filename = ? WHERE id = ?');
        $statement->execute([$data['categoryId'], $sponsorId ?: null, $data['title'], unique_recipe_slug($data['title'], $id), $data['description'], $data['cookTime'], $data['servings'], $data['difficulty'], $photo, $id]);
        $connection->prepare('DELETE FROM recipe_ingredients WHERE recipe_id = ?')->execute([$id]);
        $connection->prepare('DELETE FROM recipe_steps WHERE recipe_id = ?')->execute([$id]);
        save_recipe_parts($id, $data['ingredients'], $data['steps']);
        $connection->commit();
    } catch (Throwable $exception) {
        $connection->rollBack();
        throw $exception;
    }
}

function save_recipe_parts(int $recipeId, array $ingredients, array $steps): void
{
    $ingredientStatement = db()->prepare('INSERT INTO recipe_ingredients (recipe_id, name, amount, sort_order) VALUES (?, ?, ?, ?)');
    foreach ($ingredients as $index => $ingredient) {
        $ingredientStatement->execute([$recipeId, $ingredient['name'], $ingredient['amount'], $index + 1]);
    }
    $stepStatement = db()->prepare('INSERT INTO recipe_steps (recipe_id, instruction, sort_order) VALUES (?, ?, ?)');
    foreach ($steps as $index => $step) {
        $stepStatement->execute([$recipeId, $step, $index + 1]);
    }
}

function enforce_submission_rate_limit(): void
{
    db()->prepare('DELETE FROM submission_attempts WHERE created_at < UTC_TIMESTAMP() - INTERVAL 2 HOUR')->execute();
    $minutes = (int) config('security.submission_window_minutes', 60);
    $statement = db()->prepare("SELECT COUNT(*) FROM submission_attempts WHERE ip_address = ? AND created_at >= UTC_TIMESTAMP() - INTERVAL {$minutes} MINUTE");
    $statement->execute([request_ip()]);
    if ((int) $statement->fetchColumn() >= (int) config('security.submission_limit', 5)) {
        throw new RuntimeException('Batas pengiriman dari jaringan ini telah tercapai. Coba lagi dalam satu jam.');
    }
}

function record_submission_attempt(): void
{
    db()->prepare('INSERT INTO submission_attempts (ip_address) VALUES (?)')->execute([request_ip()]);
}
