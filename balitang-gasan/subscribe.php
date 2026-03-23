<?php
require_once __DIR__ . '/config/db.php';
header('Content-Type: application/json');

$email = trim($_POST['email'] ?? '');

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Please enter a valid email address.']);
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO subscribers (email) VALUES (?)");
    $stmt->execute([$email]);
    echo json_encode(['success' => true, 'message' => 'Thank you for subscribing to Balitang Gaseño!']);
} catch (PDOException $e) {
    if ($e->getCode() == 23000) {
        echo json_encode(['success' => false, 'message' => 'This email is already subscribed.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Something went wrong. Please try again.']);
    }
}

?>
