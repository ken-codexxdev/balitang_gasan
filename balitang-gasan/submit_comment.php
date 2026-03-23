<?php
require_once __DIR__ . '/config/db.php';
header('Content-Type: application/json');

$article_id = (int)($_POST['article_id'] ?? 0);
$name       = trim($_POST['name'] ?? '');
$email      = trim($_POST['email'] ?? '');
$body       = trim($_POST['body'] ?? '');

if (!$article_id || !$name || !$email || !$body) {
    echo json_encode(['success' => false, 'message' => 'All fields are required.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Please enter a valid email address.']);
    exit;
}

if (strlen($body) < 10) {
    echo json_encode(['success' => false, 'message' => 'Comment is too short.']);
    exit;
}

// Verify article exists
$chk = $pdo->prepare("SELECT id FROM articles WHERE id = ? AND status='published'");
$chk->execute([$article_id]);
if (!$chk->fetch()) {
    echo json_encode(['success' => false, 'message' => 'Article not found.']);
    exit;
}

$stmt = $pdo->prepare("
    INSERT INTO comments (article_id, commenter_name, commenter_email, body, status)
    VALUES (?, ?, ?, ?, 'pending')
");
$stmt->execute([$article_id, $name, $email, $body]);

echo json_encode([
    'success' => true,
    'message' => 'Your comment has been submitted and is awaiting approval. Thank you!'
]);

?>