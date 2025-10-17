<?php
session_start();

require_once __DIR__ . '/../inc/auth.php';
$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = $_POST['user'] ?? '';
    $pass = $_POST['pass'] ?? '';
    if ($user === ADMIN_USER && password_verify($pass, ADMIN_PASS_HASH)) {
        $_SESSION['admin_logged'] = true;
        $_SESSION['admin_user'] = $user;
        header('Location: dashboard.php');
        exit;
    } else {
        $err = 'Identifiants invalides.';
    }
}
?>

<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Admin — Connexion</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100">
  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-md-6">
        <div class="card shadow">
          <div class="card-body p-4">
            <h3 class="mb-3">Connexion administrateur</h3>
            <?php if($err): ?><div class="alert alert-danger"><?php echo htmlspecialchars($err); ?></div><?php endif; ?>
            <form method="post" class="space-y-3">
              <label class="form-label">Utilisateur
                <input class="form-control" name="user" required>
              </label>
              <label class="form-label">Mot de passe
                <input type="password" class="form-control" name="pass" required>
              </label>
              <div class="d-flex justify-content-between align-items-center mt-3">
                <button class="btn btn-primary">Se connecter</button>
                <a href="/index.php" class="text-sm text-muted">Retour site</a>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
