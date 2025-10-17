<?php
// newsletter_subscribe.php
header('Content-Type: application/json; charset=utf-8');
require __DIR__ . '/inc/db.php';

$rawName = filter_input(INPUT_POST, 'name', FILTER_UNSAFE_RAW, FILTER_FLAG_STRIP_LOW) ?? '';
$name = trim(strip_tags($rawName));
$email = trim(filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL) ?? '');

if (!$email) {
    echo json_encode(['success' => false, 'error' => 'Email invalide.']);
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO subscribers (name, email) VALUES (:name, :email)
                           ON DUPLICATE KEY UPDATE name = VALUES(name)");
    $stmt->execute([':name' => $name, ':email' => $email]);
    echo json_encode(['success' => true]);
    exit;
} catch (PDOException $e) {
    // Log $e->getMessage() côté serveur en prod
    echo json_encode(['success' => false, 'error' => 'Erreur serveur.']);
    exit;
}
