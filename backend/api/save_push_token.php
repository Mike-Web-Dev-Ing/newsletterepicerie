<?php
header('Content-Type: application/json; charset=utf-8');
require __DIR__ . '/_auth_guard.php';
require __DIR__ . '/../../inc/db.php';
$token = $_POST['push_token'] ?? '';
if (!$token) { http_response_code(422); echo json_encode(['error'=>'missing']); exit; }
$stmt = $pdo->prepare("INSERT IGNORE INTO push_tokens (token, created_at) VALUES (:t, NOW())");
$stmt->execute([':t' => $token]);
echo json_encode(['success'=>true]);
