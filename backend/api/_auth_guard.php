<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
$hdr = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
if (!preg_match('/Bearer\s+(.*)/i', $hdr, $m)) { http_response_code(401); echo json_encode(['error'=>'missing_token']); exit; }
$token = $m[1] ?? '';
if (empty($_SESSION['api_token']) || !hash_equals($_SESSION['api_token'], $token)) {
    http_response_code(401); echo json_encode(['error'=>'invalid_token']); exit;
}
