<?php
require __DIR__ . '/inc/db.php'; // Pour la fonction esc() et la cohérence

// Fonction pour échapper les sorties HTML
function esc($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

$productId = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
$product = null;

if ($productId) {
    $productsFile = __DIR__ . '/data/product.json';
    if (file_exists($productsFile)) {
        $products = json_decode(file_get_contents($productsFile), true) ?: [];
        // Chercher le produit par son ID
        foreach ($products as $p) {
            if ($p['id'] == $productId) {
                $product = $p;
                break;
            }
        }
    }
}

// Si aucun produit n'est trouvé, on pourrait rediriger ou afficher une erreur.
// Pour l'instant, on affiche un message simple.

$pageTitle = $product ? esc($product['name']) : 'Produit non trouvé';
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title><?php echo $pageTitle; ?> — Franprix</title>
  <link rel="stylesheet" href="style.css" />
  <link rel="icon" href="assets/favicon.ico" />
  <meta name="robots" content="noindex"> <!-- Optionnel: à retirer si vous voulez indexer ces pages -->
</head>
<body>
  <header class="site-header">
    <div class="container header-inner">
      <div class="brand">
        <a href="/" class="logo" aria-label="Franprix - Retour à l'accueil"><img src="assets/images/franprix-logo.png" alt="logo franprix"></a>
      </div>
      <nav class="main-nav"><a href="/">← Retour à l'accueil</a></nav>
    </div>
  </header>

  <main>
    <?php if ($product): ?>
      <nav aria-label="Fil d'Ariane" class="breadcrumb-nav">
        <div class="container">
          <ol class="breadcrumb">
            <li><a href="/">Accueil</a></li>
            <li><span aria-current="page"><?php echo esc($product['name']); ?></span></li>
          </ol>
        </div>
      </nav>
      <article class="container product-detail" style="display: flex; gap: 2rem; background: var(--card); padding: 2rem; border-radius: var(--radius);">
        <div style="flex: 1 1 40%;">
          <img src="<?php echo esc($product['image']); ?>" alt="<?php echo esc($product['name']); ?>" style="width: 100%; height: auto; border-radius: var(--radius);">
        </div>
        <div style="flex: 1 1 60%;">
          <h1><?php echo esc($product['name']); ?></h1>
          <p style="font-size: 1.2rem; color: var(--muted);"><?php echo esc($product['description']); ?></p>
      </article>
    <?php else: ?>
      <div class="container card" style="padding: 2rem; text-align: center;">
        <h1>Produit non trouvé</h1>
        <p>Le produit que vous cherchez n'existe pas ou n'est plus disponible.</p>
        <p><a href="/" class="btn btn-primary">Retour à l'accueil</a></p>
      </div>
    <?php endif; ?>
  </main>

  <?php include __DIR__ . '/inc/footer.php'; // J'ai créé un footer réutilisable pour la cohérence ?>

</body>
</html>
