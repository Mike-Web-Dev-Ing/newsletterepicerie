<?php
header('Content-Type: application/json; charset=utf-8');
$REQUIRED_ROLE = 'admin';
require __DIR__ . '/_auth_guard_role.php';
require __DIR__ . '/../../inc/db.php';
$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { http_response_code(422); echo json_encode(['success'=>false,'error'=>'invalid_id']); exit; }
$stmt = $pdo->prepare("DELETE FROM promotions WHERE id=:id");
$stmt->execute([':id'=>$id]);
echo json_encode(['success'=>true]);
