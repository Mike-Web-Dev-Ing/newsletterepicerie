<?php
header('Content-Type: application/json; charset=utf-8');
$REQUIRED_ROLE = 'editor';
require __DIR__ . '/_auth_guard_role.php';
require __DIR__ . '/../../inc/db.php';
$id = (int)($_GET['id'] ?? 0);
$input = json_decode(file_get_contents('php://input'), true) ?: [];
if ($id <= 0) { http_response_code(422); echo json_encode(['success'=>false,'error'=>'invalid_id']); exit; }
$title = isset($input['title']) ? trim($input['title']) : null;
$price = isset($input['price']) ? floatval($input['price']) : null;
$fields = []; $params = [':id'=>$id];
if ($title !== null){ $fields[] = 'title=:title'; $params[':title'] = $title; }
if ($price !== null){ $fields[] = 'price=:price'; $params[':price'] = $price; }
if (!$fields){ echo json_encode(['success'=>true]); exit; }
$sql = 'UPDATE promotions SET '.implode(',', $fields).' WHERE id=:id';
$stmt = $pdo->prepare($sql); $stmt->execute($params);
echo json_encode(['success'=>true]);
