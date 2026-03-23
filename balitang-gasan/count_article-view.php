<?php
// ping_view.php — called via JS fetch to increment view count without reloading
require_once __DIR__ . '/config/db.php';

$id = (int)($_GET['id'] ?? 0);
if ($id) {
    $pdo->prepare("UPDATE articles SET views = views + 1 WHERE id = ?")->execute([$id]);
}

http_response_code(204); // No Content

?>