<?php
header('Content-Type: application/json; charset=utf-8');
require __DIR__ . '/../../inc/db.php';
session_start();

$user = $_POST['user'] ?? '';
$pass = $_POST['pass'] ?? '';

try {
    $stmt = $pdo->prepare("SELECT id, username, pass_hash, role FROM users WHERE username = :u LIMIT 1");
    $stmt->execute([':u' => $user]);
    $u = $stmt->fetch();
    if ($u && password_verify($pass, $u['pass_hash'])) {
        $token = bin2hex(random_bytes(24));
        $_SESSION['api_token'] = $token;
        $_SESSION['api_role']  = $u['role']; // 'admin' or 'editor'
        $_SESSION['api_user']  = $u['username'];
        echo json_encode(['token' => $token, 'role' => $u['role']]);
        exit;
    }
} catch (Exception $e) { /* table may not exist, fallback below */ }

require __DIR__ . '/../../inc/auth.php'; // fallback constants
if ($user === ADMIN_USER && password_verify($pass, ADMIN_PASS_HASH)) {
    $token = bin2hex(random_bytes(24));
    $_SESSION['api_token'] = $token;
    $_SESSION['api_role']  = 'admin';
    $_SESSION['api_user']  = ADMIN_USER;
    echo json_encode(['token' => $token, 'role' => 'admin']);
    exit;
}

http_response_code(401);
echo json_encode(['error'=>'invalid_credentials']);
