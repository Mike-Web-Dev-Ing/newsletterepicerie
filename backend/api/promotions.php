<?php
header('Content-Type: application/json; charset=utf-8');
require __DIR__ . '/../../inc/db.php';
require_once __DIR__ . '/../../app/Support/Asset.php';

$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$min = isset($_GET['min']) ? floatval($_GET['min']) : null;
$max = isset($_GET['max']) ? floatval($_GET['max']) : null;
$page = max(1, intval($_GET['page'] ?? 1));
$per = max(1, min(100, intval($_GET['per_page'] ?? 10)));
$offset = ($page - 1) * $per;

$where = ['active = 1'];
$params = [];
if ($q !== '') { $where[] = '(title LIKE :q OR description LIKE :q)'; $params[':q'] = '%'.$q.'%'; }
if ($min !== null) { $where[] = 'price >= :min'; $params[':min'] = $min; }
if ($max !== null) { $where[] = 'price <= :max'; $params[':max'] = $max; }
$whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

$total = $pdo->prepare("SELECT COUNT(*) FROM promotions $whereSql");
$total->execute($params);
$totalCount = (int)$total->fetchColumn();

$sql = "SELECT * FROM promotions $whereSql ORDER BY created_at DESC LIMIT :lim OFFSET :off";
$stmt = $pdo->prepare($sql);
foreach($params as $k=>$v){ $stmt->bindValue($k, $v); }
$stmt->bindValue(':lim', $per, PDO::PARAM_INT);
$stmt->bindValue(':off', $offset, PDO::PARAM_INT);
$stmt->execute();
$rows = $stmt->fetchAll();

foreach ($rows as &$row) {
    $row['product_image'] = \App\Support\Asset::url($row['product_image'] ?? null);
}
unset($row);

echo json_encode(['data'=>$rows, 'total'=>$totalCount, 'page'=>$page, 'per_page'=>$per], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
