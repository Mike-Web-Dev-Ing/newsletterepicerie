<?php
require_once __DIR__ . '/../inc/auth.php';
require_admin();
require_once __DIR__ . '/../inc/db.php';

// pagination simple
$page = max(1, (int)($_GET['page'] ?? 1));
$per = 40;
$offset = ($page - 1) * $per;

$total = $pdo->query("SELECT COUNT(*) FROM subscribers")->fetchColumn();
$stmt = $pdo->prepare("SELECT * FROM subscribers ORDER BY created_at DESC LIMIT :lim OFFSET :off");
$stmt->bindValue(':lim', $per, PDO::PARAM_INT);
$stmt->bindValue(':off', $offset, PDO::PARAM_INT);
$stmt->execute();
$subs = $stmt->fetchAll();
?>
<!doctype html>
<html lang="fr">
<head><meta charset="utf-8"><title>Admin — Abonnés</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
      <a class="navbar-brand" href="dashboard.php">Admin</a>
      <div class="d-flex ms-auto">
        <a class="btn btn-outline-light btn-sm" href="dashboard.php">Dashboard</a>
      </div>
    </div>
  </nav>
  <main class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h1 class="h3 mb-0">Abonnés <span class="badge bg-secondary"><?php echo (int)$total; ?></span></h1>
      <a class="btn btn-secondary" href="dashboard.php">← Retour</a>
    </div>

    <div class="table-responsive">
      <table class="table table-striped table-hover align-middle">
        <thead class="table-light"><tr><th>#</th><th>Nom</th><th>Email</th><th>Inscrit le</th></tr></thead>
        <tbody>
        <?php foreach($subs as $s): ?>
          <tr>
            <td><?php echo (int)$s['id']; ?></td>
            <td><?php echo htmlspecialchars($s['name']); ?></td>
            <td><a href="mailto:<?php echo htmlspecialchars($s['email']); ?>"><?php echo htmlspecialchars($s['email']); ?></a></td>
            <td><?php echo htmlspecialchars($s['created_at']); ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <?php $pages = (int)ceil($total / $per); if($pages>1): ?>
      <nav aria-label="Pagination">
        <ul class="pagination">
          <?php for($i=1; $i<=$pages; $i++): ?>
            <li class="page-item <?php echo $i===$page?'active':''; ?>">
              <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
            </li>
          <?php endfor; ?>
        </ul>
      </nav>
    <?php endif; ?>
  </main>
</body>
</html>
