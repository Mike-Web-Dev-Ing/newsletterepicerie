<?php
header('Content-Type: application/json; charset=utf-8');
$REQUIRED_ROLE = 'editor';
require __DIR__ . '/_auth_guard_role.php';
require __DIR__ . '/../../inc/db.php';
$input = json_decode(file_get_contents('php://input'), true) ?: [];
$title = trim($input['title'] ?? '');
$price = floatval($input['price'] ?? 0);
if ($title === '' || $price <= 0) { http_response_code(422); echo json_encode(['success'=>false,'error'=>'invalid']); exit; }
$stmt = $pdo->prepare("INSERT INTO promotions (title, price, active, created_at) VALUES (:t,:p,1,NOW())");
$stmt->execute([':t'=>$title, ':p'=>$price]);
echo json_encode(['success'=>true, 'id'=>$pdo->lastInsertId()]);
