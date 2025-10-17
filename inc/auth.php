<?php
// inc/auth.php

use App\Support\Url;

// Démarrer la session si nécessaire
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Configurez un compte admin (en prod : stockez dans DB + hash)
const ADMIN_USER = 'admin';
// Hash pour le mot de passe par défaut "admin123" (bcrypt cost 12)
const ADMIN_PASS_HASH = '$2y$12$8SYfkqeCsE5PnC.1niRVGO8X7y8nCm.T2HEer79iMXwkIaCUfPMEi';

// helpers
function admin_is_logged(){
    return !empty($_SESSION['admin_logged']) && $_SESSION['admin_logged'] === true;
}
function admin_url(string $path = ''): string{
    return Url::path($path);
}
function require_admin(){
    if (!admin_is_logged()) {
        header('Location: ' . admin_url('admin/login'));
        exit;
    }
}
