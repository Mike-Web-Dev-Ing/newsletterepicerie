<?php
header('Content-Type: application/json; charset=utf-8');
require __DIR__ . '/../../inc/db.php';
require_once __DIR__ . '/../../app/Support/Asset.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) { http_response_code(400); echo json_encode(['error'=>'invalid_id']); exit; }

$stmt = $pdo->prepare("SELECT * FROM promotions WHERE id = :id AND active = 1");
$stmt->execute([':id' => $id]);
$row = $stmt->fetch();
if (!$row) { http_response_code(404); echo json_encode(['error'=>'not_found']); exit; }

$row['product_image'] = \App\Support\Asset::url($row['product_image'] ?? null);

echo json_encode($row, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
