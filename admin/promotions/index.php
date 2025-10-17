<?php
require_once __DIR__ . '/../../inc/auth.php';
require_admin();
require_once __DIR__ . '/../../inc/db.php';

$stmt = $pdo->query("SELECT * FROM promotions ORDER BY active DESC, created_at DESC");
$items = $stmt->fetchAll();
?>
<!doctype html>
<html lang="fr">
<head><meta charset="utf-8"><title>Promotions — Admin</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
      <a class="navbar-brand" href="../dashboard.php">Admin</a>
      <div class="d-flex ms-auto">
        <a class="btn btn-outline-light btn-sm" href="../dashboard.php">Dashboard</a>
      </div>
    </div>
  </nav>
  <main class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h1 class="h3 mb-0">Promotions</h1>
      <a class="btn btn-primary" href="create.php">+ Nouvelle promotion</a>
    </div>
    <div class="table-responsive">
      <table class="table table-striped table-hover align-middle">
        <thead class="table-light"><tr><th>#</th><th>Titre</th><th>Prix</th><th>Du → Au</th><th>Actif</th><th>Actions</th></tr></thead>
        <tbody>
          <?php foreach($items as $it): ?>
          <tr>
            <td><?php echo (int)$it['id']; ?></td>
            <td><?php echo htmlspecialchars($it['title']); ?></td>
            <td>
              <?php echo number_format($it['price'],2,',',' '); ?>€
              <?php if($it['old_price']) echo " <small class='text-muted text-decoration-line-through'>".number_format($it['old_price'],2,',',' ')."€</small>"; ?>
            </td>
            <td><?php echo htmlspecialchars($it['start_date']) . ' → ' . htmlspecialchars($it['end_date']); ?></td>
            <td><?php echo $it['active'] ? '<span class="badge bg-success">Oui</span>' : '<span class="badge bg-secondary">Non</span>'; ?></td>
            <td class="text-nowrap">
              <a class="btn btn-sm btn-outline-primary" href="edit.php?id=<?php echo $it['id']; ?>">Éditer</a>
              <a class="btn btn-sm btn-outline-danger" href="delete.php?id=<?php echo $it['id']; ?>" onclick="return confirm('Supprimer ?')">Supprimer</a>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </main>
</body>
</html>
