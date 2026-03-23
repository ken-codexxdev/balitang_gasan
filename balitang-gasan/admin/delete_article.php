<?php
require_once __DIR__ . '/../config/db.php';
require_login();

$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: ' . SITE_URL . '/admin/articles.php'); exit; }

$stmt = $pdo->prepare("SELECT id, title, image FROM articles WHERE id = ?");
$stmt->execute([$id]);
$article = $stmt->fetch();

if (!$article) {
    $_SESSION['error'] = 'Article not found.';
    header('Location: ' . SITE_URL . '/admin/articles.php');
    exit;
}

// Delete image file if exists
if ($article['image'] && file_exists(UPLOAD_DIR . $article['image'])) {
    unlink(UPLOAD_DIR . $article['image']);
}

// Delete article (comments deleted via CASCADE)
$pdo->prepare("DELETE FROM articles WHERE id = ?")->execute([$id]);

$_SESSION['success'] = 'Article "' . $article['title'] . '" has been deleted.';
header('Location: ' . SITE_URL . '/admin/articles.php');
exit;

?>
