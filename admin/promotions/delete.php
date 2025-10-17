<?php
require_once __DIR__ . '/../../inc/auth.php';
require_admin();
require_once __DIR__ . '/../../inc/db.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare("SELECT product_image FROM promotions WHERE id = :id");
$stmt->execute([':id' => $id]);
$promotion = $stmt->fetch();

if ($promotion) {
    $pdo->prepare("DELETE FROM promotions WHERE id = :id")->execute([':id' => $id]);

    if (!empty($promotion['product_image'])) {
        $filePath = __DIR__ . '/../../' . $promotion['product_image'];
        if (is_file($filePath)) {
            @unlink($filePath);
        }
    }
}

header('Location: index.php');
exit;
