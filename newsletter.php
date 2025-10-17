<?php
require __DIR__ . '/inc/db.php';
function esc($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

$stmt = $pdo->query("SELECT * FROM promotions WHERE active=1 ORDER BY created_at DESC");
$promos = $stmt->fetchAll();
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Newsletter — Promotions</title>
  <link rel="stylesheet" href="style.css" />
  <link rel="icon" href="assets/favicon.ico" />
  <style>
    .nw-header { background:#f5f7fa; border-bottom:1px solid #e5e7eb; }
    .nw-container { max-width: 1100px; margin: 0 auto; padding: 1rem; }
    .promo-grid { display:grid; grid-template-columns: repeat(auto-fill, minmax(260px,1fr)); gap: 1rem; }
    .promo-card { border:1px solid #e5e7eb; border-radius:10px; overflow:hidden; background:#fff; display:flex; flex-direction:column; }
    .promo-card img { width:100%; height:180px; object-fit:cover; }
    .promo-body { padding:1rem; display:flex; flex-direction:column; gap:.5rem; }
    .promo-price { font-weight:700; font-size:1.1rem; }
    .promo-old { color:#6b7280; text-decoration: line-through; margin-left:.5rem; font-weight:400; }
    .promo-dates { color:#6b7280; font-size:.9rem; }
    .muted { color:#6b7280; }
    .print-btn { background:#111827; color:#fff; border:none; padding:.5rem .75rem; border-radius:8px; cursor:pointer; }
    .print-btn:hover { background:#374151; }
    @media print {
      .print-actions { display:none !important; }
      body { background:#fff; }
      .promo-grid { display:block; }
      .promo-card { page-break-inside: avoid; margin-bottom: 12px; border-color:#000; }
    }
  </style>
</head>
<body>
  <header class="nw-header">
    <div class="nw-container" style="display:flex; align-items:center; justify-content:space-between; gap:1rem;">
      <div style="display:flex; align-items:center; gap:.75rem;">
        <a href="/" class="logo"><img src="assets/images/franprix-logo.png" alt="logo" style="height:40px"></a>
        <strong>Newsletter — Promotions en cours</strong>
      </div>
      <div class="print-actions" style="display:flex; gap:.5rem; align-items:center;">
        <a href="/" class="muted">← Retour à l'accueil</a>
        <button type="button" class="print-btn" onclick="window.print()">Imprimer / PDF</button>
      </div>
    </div>
  </header>

  <main class="nw-container" style="padding-top:1.25rem; padding-bottom:2rem;">
    <?php if (empty($promos)): ?>
      <p class="muted">Aucune promotion active pour le moment.</p>
    <?php else: ?>
      <div class="promo-grid">
        <?php foreach ($promos as $p):
          $price = (float)$p['price'];
          $old = $p['old_price'] !== null ? (float)$p['old_price'] : 0.0;
        ?>
          <article class="promo-card">
            <?php if (!empty($p['product_image'])): ?>
              <img src="<?php echo esc($p['product_image']); ?>" alt="<?php echo esc($p['title']); ?>">
            <?php else: ?>
              <img src="assets/images/franprix-logo.png" alt="logo">
            <?php endif; ?>
            <div class="promo-body">
              <h3 style="margin:0; font-size:1.05rem; line-height:1.3; "><?php echo esc($p['title']); ?></h3>
              <?php if (!empty($p['description'])): ?>
                <p class="muted" style="margin:0; white-space:pre-wrap;">&nbsp;<?php echo esc($p['description']); ?></p>
              <?php endif; ?>
              <div class="promo-price">
                <?php echo number_format($price, 2, ',', ' '); ?>€
                <?php if ($old > 0): ?><span class="promo-old"><?php echo number_format($old, 2, ',', ' '); ?>€</span><?php endif; ?>
              </div>
              <?php if (!empty($p['start_date']) || !empty($p['end_date'])): ?>
                <div class="promo-dates">Valable: <?php echo esc($p['start_date'] ?: '-'); ?> → <?php echo esc($p['end_date'] ?: '-'); ?></div>
              <?php endif; ?>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </main>
  <?php if (!empty($_GET['print'])): ?>
  <script>
    window.addEventListener('load', function(){ window.print(); });
  </script>
  <?php endif; ?>
</body>
</html>
