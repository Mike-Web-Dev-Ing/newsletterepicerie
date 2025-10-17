<?php
require_once __DIR__ . '/../../inc/auth.php';
require_admin();
require_once __DIR__ . '/../../inc/db.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM promotions WHERE id = :id");
$stmt->execute([':id' => $id]);
$promotion = $stmt->fetch();

if (!$promotion) {
    header('Location: index.php');
    exit;
}

$errors = [];

$title = $promotion['title'];
$description = $promotion['description'];
$price = $promotion['price'];
$old_price = $promotion['old_price'];
$start = $promotion['start_date'];
$end = $promotion['end_date'];
$active = (int)$promotion['active'];
$imagePath = $promotion['product_image'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $old_price = ($_POST['old_price'] === '' ? null : (float)$_POST['old_price']);
    $start = $_POST['start_date'] ?: null;
    $end = $_POST['end_date'] ?: null;
    $active = isset($_POST['active']) ? 1 : 0;

    $newImagePath = $imagePath;
    if (!empty($_FILES['product_image']['name'])) {
        $u = $_FILES['product_image'];
        if ($u['error'] === UPLOAD_ERR_OK && preg_match('/image\//', $u['type'])) {
            $ext = strtolower(pathinfo($u['name'], PATHINFO_EXTENSION));
            $fileName = 'uploads/prom_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
            @mkdir(__DIR__ . '/../../uploads', 0755, true);
            if (move_uploaded_file($u['tmp_name'], __DIR__ . '/../../' . $fileName)) {
                $newImagePath = $fileName;
            } else {
                $errors[] = "Impossible d'uploader l'image.";
            }
        } else {
            $errors[] = "Fichier image invalide.";
        }
    }

    if (!$title) {
        $errors[] = "Le titre est requis.";
    }
    if ($price <= 0) {
        $errors[] = "Prix invalide.";
    }

    if (!$errors) {
        $stmt = $pdo->prepare("UPDATE promotions SET title=:title, description=:description, product_image=:image, price=:price, old_price=:old_price, start_date=:start_date, end_date=:end_date, active=:active WHERE id=:id");
        $stmt->execute([
            ':title' => $title,
            ':description' => $description,
            ':image' => $newImagePath,
            ':price' => $price,
            ':old_price' => $old_price,
            ':start_date' => $start,
            ':end_date' => $end,
            ':active' => $active,
            ':id' => $id,
        ]);

        if ($newImagePath !== $imagePath && $imagePath) {
            $oldFile = __DIR__ . '/../../' . $imagePath;
            if (is_file($oldFile)) {
                @unlink($oldFile);
            }
        }

        header('Location: index.php');
        exit;
    }
}
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Éditer la promotion</title>
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
    <h1 class="h3">Modifier la promotion</h1>
    <?php if($errors): foreach($errors as $e){ echo "<div class='alert alert-danger'>".htmlspecialchars($e)."</div>"; } endif; ?>
    <form class="row g-3" method="post" enctype="multipart/form-data">
      <div class="col-md-8">
        <label class="form-label">Titre
          <input class="form-control" name="title" value="<?php echo htmlspecialchars($title); ?>" required>
        </label>
      </div>
      <div class="col-md-4">
        <label class="form-label">Image produit (optionnel)
          <input class="form-control" type="file" name="product_image" accept="image/*">
        </label>
        <?php if ($imagePath): ?>
          <div class="mt-2">
            <img src="/<?php echo htmlspecialchars($imagePath); ?>" alt="Image actuelle" class="img-thumbnail" style="max-width: 180px;">
          </div>
        <?php endif; ?>
      </div>
      <div class="col-12">
        <label class="form-label">Description
          <textarea class="form-control" name="description" rows="4"><?php echo htmlspecialchars($description); ?></textarea>
        </label>
      </div>
      <div class="col-md-4">
        <label class="form-label">Prix (€)
          <input class="form-control" name="price" value="<?php echo htmlspecialchars($price); ?>" required>
        </label>
      </div>
      <div class="col-md-4">
        <label class="form-label">Ancien prix (€)
          <input class="form-control" name="old_price" value="<?php echo $old_price !== null ? htmlspecialchars($old_price) : ''; ?>">
        </label>
      </div>
      <div class="col-md-2">
        <label class="form-label">Début
          <input class="form-control" type="date" name="start_date" value="<?php echo htmlspecialchars($start ?? ''); ?>">
        </label>
      </div>
      <div class="col-md-2">
        <label class="form-label">Fin
          <input class="form-control" type="date" name="end_date" value="<?php echo htmlspecialchars($end ?? ''); ?>">
        </label>
      </div>
      <div class="col-12 form-check">
        <input class="form-check-input" type="checkbox" name="active" id="active" <?php echo $active ? 'checked' : ''; ?>>
        <label class="form-check-label" for="active">Actif</label>
      </div>
      <div class="col-12">
        <button class="btn btn-primary">Enregistrer</button>
      </div>
    </form>
  </main>
</body>
</html>
