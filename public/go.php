<?php

declare(strict_types=1);

require __DIR__ . '/app.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$sponsor = $id ? db_one('SELECT * FROM sponsors WHERE id = ? AND is_active = 1 AND start_date <= ? AND end_date >= ?', [$id, local_today(), local_today()]) : null;
if (!$sponsor || !filter_var($sponsor['destination_url'], FILTER_VALIDATE_URL) || !in_array(parse_url($sponsor['destination_url'], PHP_URL_SCHEME), ['http', 'https'], true)) {
    http_response_code(404);
    redirect('', 302);
}
db()->prepare('UPDATE sponsors SET clicks = clicks + 1 WHERE id = ?')->execute([$sponsor['id']]);
header('X-Robots-Tag: noindex, nofollow');
redirect($sponsor['destination_url']);
