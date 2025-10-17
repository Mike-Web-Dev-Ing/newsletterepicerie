<?php
header('Content-Type: application/json; charset=utf-8');
require __DIR__ . '/../../inc/db.php';

$title = $_POST['title'] ?? $_GET['title'] ?? 'Nouvelle promo';
$body  = $_POST['body']  ?? $_GET['body']  ?? '';
$data  = $_POST['data']  ?? $_GET['data']  ?? '{}';

$stmt = $pdo->query("SELECT token FROM push_tokens");
$tokens = $stmt->fetchAll(PDO::FETCH_COLUMN);
if (!$tokens) { echo json_encode(['success'=>true, 'sent'=>0, 'message'=>'no tokens']); exit; }

$messages = [];
foreach ($tokens as $t) {
  $messages[] = ['to'=>$t, 'title'=>$title, 'body'=>$body, 'data'=>json_decode($data, true)];
}

$ch = curl_init('https://exp.host/--/api/v2/push/send');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($messages));
$resp = curl_exec($ch);
$err  = curl_error($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($err) { http_response_code(500); echo json_encode(['success'=>false, 'error'=>$err]); exit; }
echo json_encode(['success'=>($code>=200 && $code<300), 'code'=>$code, 'response'=>json_decode($resp, true)]);
