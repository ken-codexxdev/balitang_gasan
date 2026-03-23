<?php
require_once __DIR__ . '/../config/db.php';
require_login();

$id     = (int)($_GET['id'] ?? 0);
$status = $_GET['status'] ?? '';

if (!$id || !in_array($status, ['published','draft'])) {
    header('Location: ' . SITE_URL . '/admin/articles.php');
    exit;
}

$pub_date = ($status === 'published') ? date('Y-m-d H:i:s') : null;

$stmt = $pdo->prepare("UPDATE articles SET status = ?, published_at = COALESCE(published_at, ?) WHERE id = ?");
$stmt->execute([$status, $pub_date, $id]);

$_SESSION['success'] = 'Article status updated to ' . ucfirst($status) . '.';
header('Location: ' . SITE_URL . '/admin/articles.php');
exit;
