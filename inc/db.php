<?php
try {
    $dsn = getenv('EPICERIE_DSN') ?: 'mysql:host=localhost;dbname=epicerie;charset=utf8mb4';
    $dbUser = getenv('EPICERIE_DB_USER') ?: 'root';
    $dbPass = getenv('EPICERIE_DB_PASS') ?: 'root';

    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];

    if (str_starts_with($dsn, 'sqlite:')) {
        $options[PDO::ATTR_PERSISTENT] = false;
    }

    $pdo = new PDO($dsn, $dbUser, $dbPass, $options);
} catch (PDOException $e) {
    die('Connexion échouée : ' . $e->getMessage());
}
?>
