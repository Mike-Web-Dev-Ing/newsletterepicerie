<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
$hdr = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
if (!preg_match('/Bearer\s+(.*)/i', $hdr, $m)) { http_response_code(401); echo json_encode(['error'=>'missing_token']); exit; }
$token = $m[1] ?? '';
if (empty($_SESSION['api_token']) || !hash_equals($_SESSION['api_token'], $token)) {
    http_response_code(401); echo json_encode(['error'=>'invalid_token']); exit;
}
$currentRole = $_SESSION['api_role'] ?? 'guest';
function role_rank($r){ return $r === 'admin' ? 2 : ($r === 'editor' ? 1 : 0); }
$required = isset($REQUIRED_ROLE) ? $REQUIRED_ROLE : 'editor';
if (role_rank($currentRole) < role_rank($required)) {
    http_response_code(403); echo json_encode(['error'=>'insufficient_role']); exit;
}
