<?php
require_once __DIR__ . '/../inc/auth.php';
require_admin();
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Admin — Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
      <a class="navbar-brand" href="dashboard.php">Admin</a>
      <div class="d-flex ms-auto">
        <span class="navbar-text me-3">Bonjour, <?php echo htmlspecialchars($_SESSION['admin_user'] ?? ''); ?></span>
        <a class="btn btn-outline-light btn-sm" href="logout.php">Se déconnecter</a>
      </div>
    </div>
  </nav>

  <main class="container py-4">
      <div class="row g-3">
        <div class="col-md-6">
          <div class="card shadow-sm h-100">
            <div class="card-body">
              <h5 class="card-title">Abonnés newsletter</h5>
              <p class="card-text text-muted">Consulter la liste des abonnés et leur date d'inscription.</p>
              <a class="btn btn-primary" href="subscribers.php">Voir les abonnés</a>
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="card shadow-sm h-100">
            <div class="card-body">
              <h5 class="card-title">Promotions</h5>
              <p class="card-text text-muted">Créer, modifier, supprimer les promotions affichées sur le site.</p>
              <a class="btn btn-primary" href="promotions/index.php">Gérer les promotions</a>
            </div>
          </div>
        </div>
        <div class="col-md-12">
          <div class="card shadow-sm h-100">
            <div class="card-body">
              <h5 class="card-title">Newsletter — Mailchimp</h5>
              <p class="card-text text-muted">Générez et envoyez la newsletter des promotions actives à votre audience Mailchimp.</p>
              <a class="btn btn-outline-primary" href="mailchimp_send.php">Envoyer via Mailchimp</a>
            </div>
          </div>
        </div>
      </div>
  </main>
</body>
</html>
