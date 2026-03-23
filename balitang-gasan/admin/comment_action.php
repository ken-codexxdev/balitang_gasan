<?php
require_once __DIR__ . '/../config/db.php';
require_login();

$id     = (int)($_GET['id'] ?? 0);
$action = $_GET['action'] ?? '';
$ref    = $_GET['ref'] ?? 'comments'; // where to redirect

if (!$id) { header('Location: ' . SITE_URL . '/admin/comments.php'); exit; }

if ($action === 'delete') {
    $pdo->prepare("DELETE FROM comments WHERE id = ?")->execute([$id]);
    $_SESSION['success'] = 'Comment deleted.';
} elseif (in_array($action, ['approved','rejected','pending'])) {
    $pdo->prepare("UPDATE comments SET status = ? WHERE id = ?")->execute([$action, $id]);
    $_SESSION['success'] = 'Comment marked as ' . $action . '.';
} else {
    $_SESSION['error'] = 'Invalid action.';
}

header('Location: ' . SITE_URL . '/admin/' . basename($ref) . '.php');
exit;
