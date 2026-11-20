<?php

declare(strict_types=1);

function active_sponsors(string $position = ''): array
{
    $sql = 'SELECT * FROM sponsors WHERE is_active = 1 AND start_date <= ? AND end_date >= ?';
    $params = [local_today(), local_today()];
    if ($position !== '') {
        $sql .= ' AND FIND_IN_SET(?, positions) > 0';
        $params[] = $position;
    }
    $sql .= " ORDER BY FIELD(tier, 'utama', 'emas', 'perak'), name";
    return db_all($sql, $params);
}

function record_sponsor_impressions(array $sponsors): void
{
    $ids = array_values(array_unique(array_filter(array_map(static fn ($sponsor) => (int) ($sponsor['id'] ?? 0), $sponsors))));
    if ($ids === []) {
        return;
    }
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    db()->prepare("UPDATE sponsors SET impressions = impressions + 1 WHERE id IN ({$placeholders})")->execute($ids);
}

function validate_sponsor_input(array $input): array
{
    $name = trim((string) ($input['name'] ?? ''));
    $description = trim((string) ($input['description'] ?? ''));
    $destinationUrl = trim((string) ($input['destination_url'] ?? ''));
    $tier = (string) ($input['tier'] ?? 'perak');
    $startDate = (string) ($input['start_date'] ?? '');
    $endDate = (string) ($input['end_date'] ?? '');
    $positions = array_values(array_intersect((array) ($input['positions'] ?? []), ['hero', 'strip', 'detail', 'footer']));
    $isActive = isset($input['is_active']) ? 1 : 0;

    if (mb_strlen($name) < 2 || mb_strlen($name) > 120 || mb_strlen($description) > 500) {
        throw new InvalidArgumentException('Nama atau deskripsi sponsor tidak valid.');
    }
    if (!filter_var($destinationUrl, FILTER_VALIDATE_URL) || !in_array(parse_url($destinationUrl, PHP_URL_SCHEME), ['http', 'https'], true)) {
        throw new InvalidArgumentException('URL sponsor harus berupa alamat HTTP atau HTTPS yang valid.');
    }
    if (!in_array($tier, ['utama', 'emas', 'perak'], true) || $positions === []) {
        throw new InvalidArgumentException('Pilih tier dan minimal satu posisi tayang.');
    }
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate) || $endDate < $startDate) {
        throw new InvalidArgumentException('Rentang tanggal sponsor tidak valid.');
    }
    return compact('name', 'description', 'destinationUrl', 'tier', 'startDate', 'endDate', 'positions', 'isActive');
}

function unique_sponsor_slug(string $name, ?int $ignoreId = null): string
{
    $base = slugify($name);
    $slug = $base;
    $counter = 2;
    while (true) {
        $sql = 'SELECT id FROM sponsors WHERE slug = ?';
        $params = [$slug];
        if ($ignoreId !== null) {
            $sql .= ' AND id <> ?';
            $params[] = $ignoreId;
        }
        if (!db_one($sql, $params)) {
            return $slug;
        }
        $slug = $base . '-' . $counter++;
    }
}
