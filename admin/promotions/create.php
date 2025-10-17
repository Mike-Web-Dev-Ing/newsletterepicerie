<?php
require_once __DIR__ . '/../../inc/auth.php';
require_admin();
require_once __DIR__ . '/../../inc/db.php';

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $old_price = ($_POST['old_price'] === '') ? null : (float)$_POST['old_price'];
    $start = $_POST['start_date'] ?: null;
    $end = $_POST['end_date'] ?: null;
    $active = isset($_POST['active']) ? 1 : 0;
    $imagePath = null;

    // upload image (optionnel)
    if (!empty($_FILES['product_image']['name'])) {
        $u = $_FILES['product_image'];
        if ($u['error'] === UPLOAD_ERR_OK && preg_match('/image\//', $u['type'])) {
            $ext = pathinfo($u['name'], PATHINFO_EXTENSION);
            $fileName = 'uploads/prom_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
            @mkdir(__DIR__ . '/../../uploads', 0755, true);
            if (move_uploaded_file($u['tmp_name'], __DIR__ . '/../../' . $fileName)) {
                $imagePath = $fileName;
            } else {
                $errors[] = "Impossible d'uploader l'image.";
            }
        } else {
            $errors[] = "Fichier image invalide.";
        }
    }

    if (!$title) $errors[] = "Le titre est requis.";
    if ($price <= 0) $errors[] = "Prix invalide.";

    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO promotions (title, description, product_image, price, old_price, start_date, end_date, active)
                               VALUES (:title, :desc, :img, :price, :old_price, :start, :end, :active)");
        $stmt->execute([
            ':title' => $title,
            ':desc' => $description,
            ':img' => $imagePath,
            ':price' => $price,
            ':old_price' => $old_price,
            ':start' => $start,
            ':end' => $end,
            ':active' => $active
        ]);
        header('Location: index.php');
        exit;
    }
}
?>
<!doctype html>
<html lang="fr">
<head><meta charset="utf-8"><title>Nouvelle promotion</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
      <a class="navbar-brand" href="index.php">Promotions</a>
      <div class="d-flex ms-auto">
        <a class="btn btn-outline-light btn-sm" href="index.php">← Retour</a>
      </div>
    </div>
  </nav>
  <main class="container py-4">
    <h1 class="h3">Nouvelle promotion</h1>
    <?php if($errors): foreach($errors as $e){ echo "<div class='alert alert-danger'>".htmlspecialchars($e)."</div>"; } endif; ?>
    <form class="row g-3" method="post" enctype="multipart/form-data">
      <div class="col-md-8">
        <label class="form-label">Titre
          <input class="form-control" name="title" required>
        </label>
      </div>
      <div class="col-md-4">
        <label class="form-label">Image produit (optionnel)
          <input class="form-control" type="file" name="product_image" accept="image/*">
        </label>
      </div>
      <div class="col-12">
        <label class="form-label">Description
          <textarea class="form-control" name="description" rows="4"></textarea>
        </label>
      </div>
      <div class="col-md-4">
        <label class="form-label">Prix (€)
          <input class="form-control" name="price" required>
        </label>
      </div>
      <div class="col-md-4">
        <label class="form-label">Ancien prix (€)
          <input class="form-control" name="old_price">
        </label>
      </div>
      <div class="col-md-2">
        <label class="form-label">Début
          <input class="form-control" type="date" name="start_date">
        </label>
      </div>
      <div class="col-md-2">
        <label class="form-label">Fin
          <input class="form-control" type="date" name="end_date">
        </label>
      </div>
      <div class="col-12 form-check">
        <input class="form-check-input" type="checkbox" name="active" id="active" checked>
        <label class="form-check-label" for="active">Actif</label>
      </div>
      <div class="col-12">
        <button class="btn btn-primary">Créer</button>
      </div>
    </form>
  </main>
</body>
</html>
