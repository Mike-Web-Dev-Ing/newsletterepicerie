<?php
require __DIR__ . '/inc/db.php';

$promos = $pdo->query("SELECT * FROM promotions WHERE active=1 ORDER BY created_at DESC")->fetchAll();
$productsFile = __DIR__ . '/data/product.json';
$products = [];
if (file_exists($productsFile)) {
    $products = json_decode(file_get_contents($productsFile), true) ?: [];
}
function esc($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Franprix — Offres & Promotions</title>
  <link rel="stylesheet" href="style.css" />
  <link rel="preconnect" href="https://fonts.googleapis.com">
  
  <link rel="icon" href="assets/favicon.ico" />
  <meta name="description" content="Votre épicerie de quartier — produits frais et locaux, promos de la semaine."/>
</head>
<body>
   <div class="background-container">
        <h1>Bienvenue chez Franprix Carry le Rouet</h1>
        
    </div>
  <header class="site-header">
    <div class="container header-inner">
      <div class="brand">
        <a href="/" class="logo" aria-label="Franprix - Retour à l'accueil"><img src="assets/images/franprix-logo.png" alt="logo franprix"></a>
        <span class="slogan">Frais • Local • À prix doux</span>
      </div>

      <nav class="main-nav" id="main-nav" aria-label="Navigation principale">
        <button id="nav-toggle" class="nav-toggle" aria-expanded="false" aria-controls="nav-list"><span class="sr-only">Ouvrir le menu</span>☰</button>
        <ul id="nav-list" class="nav-list">
          <li><a href="/newsletter.php" class="cta">Newsletter</a></li>
          <li><a href="#contact" class="cta">Infos pratiques</a></li>
          <li><a class="btn btn-outline-primary" href="/admin/login.php" class='cta'>Accès admin</a></li>
        </ul>
        
      </nav>

      <div class="contact">
        <a href="tel:+33442450144" aria-label="Appeler le magasin"><span role="img" aria-hidden="true">📞</span> +33 4 42 45 01 44</a>
      </div>
    </div>
  </header>

  <main>
    <!-- HERO -->
     
    <section class="hero" style="background-image: url('assets/images/front.webp');" aria-labelledby="hero-title">
      <div class="container hero-inner">
        <h1 id="hero-title" class="hero-title">Votre épicerie de quartier — produits frais et locaux</h1>
        <p><a href="#offers" class="btn btn-primary">Découvrir nos offres</a></p>
      </div>
    </section>
   <section class="values container" aria-labelledby="val-title">
      <h2 id="val-title">Pourquoi nous choisir ?</h2></br>
      <div class="grid values-grid">
        <div class="card">
          <img src="assets/images/unnamed (4).webp" alt="entrée magasin" class="icon" width="300" height="300">
          <h3>Produits frais</h3>
          <p>Produits locaux et de saison, livrés quotidiennement.</p>
        </div>
        <div class="card">
          <img src="assets/images/unnamed (3).webp" alt="magasin nuit" class="icon" width="300" height="200">
          <h3>Proximité</h3>
          <p>Service de quartier, retrait rapide ou livraison locale.</p>
        </div>
        <div class="card">
          <img src="assets/images/unnamed (6).webp" alt="rayon" class="icon" width="250" height="250">
          <h3>Bon rapport qualité/prix</h3>
          <p>Promotions régulières et carte fidélité.</p>
        </div>
      </div>
    </section>

    <!-- Promotions -->
    <section id="offers" class="offers container" aria-labelledby="offers-title">
      <h2 id="offers-title">Nos offres</h2><br>
      
      <div class="grid products-grid">
        <?php foreach ($products as $p): 
          $old = floatval($p['old_price'] ?? 0);
          $now = floatval($p['price'] ?? 0);
          $discount = $old > 0 ? round((($old - $now)/$old)*100) : 0;
        ?>
        <article class="product-card" aria-labelledby="prod-<?php echo $p['id']; ?>">
          <img src="<?php echo esc($p['image']); ?>" alt="<?php echo esc($p['name']); ?>" loading="lazy">
          <div class="product-body">
            <h3 id="prod-<?php echo $p['id']; ?>"><?php echo esc($p['name']); ?></h3>
            <p class="desc"><?php echo esc($p['description']); ?></p>
            <div class="price-row">
              <?php if ($old>0): ?>
                <span class="old-price"><?php echo number_format($old,2,',',' '); ?>€</span>
              <?php endif; ?>
              <span class="price"><?php echo number_format($now,2,',',' '); ?>€</span>
              <?php if ($discount>0): ?>
                <span class="badge">-<?php echo $discount; ?>%</span>
              <?php endif; ?>
            </div>
            <div class="actions">
              <a href="product.php?id=<?php echo $p['id']; ?>" class="btn btn-outline">En savoir plus</a>
            </div>
          </div>
        </article>
        <?php endforeach; ?>
      </div>
      <p class="promo-note">Offres valables jusqu'au <strong><?php echo date('d F Y'); ?></strong> — vérifiez les dates individuelles des produits.</p>
    </section>

<!-- Newsletter -->
    <section id="newsletter" class="newsletter container" aria-labelledby="news-title">
      <h2 id="news-title">Restez informé de nos promotions</h2><br>
      <form id="newsletter-form" action="newsletter_subscribe.php" method="post" novalidate>
        <label for="n-name">Prénom</label>
        <input type="text" name="name" id="n-name" placeholder="Votre prénom" />
        <label for="n-email">Email *</label>
        <input type="email" name="email" id="n-email" required placeholder="votre@email.com"/>
        <button type="submit" class="btn btn-primary">S'inscrire</button>
        <p id="news-msg" class="form-msg" aria-live="polite"></p>
      </form>
      <p class="small">Avantage : recevez nos offres spéciales en exclusivité.</p><br>
    </section>

    <!-- Témoignages -->
    <section id="testi" class="testimonials container" aria-labelledby="testi-title">
      <h2 id="testi-title">Ce que disent nos clients</h2><br>
      <div class="grid testimonials-grid">
        <blockquote class="testi">
          <p>« Produits super frais et équipe hyper sympa ! »</p><br>
          <footer>— Claire, Le Rouet</footer>
        </blockquote>
        <blockquote class="testi">
          <p>« Drive rapide, très pratique. »</p><br>
          <footer>— Karim, Sausset les Pins</footer>
        </blockquote>
        <blockquote class="testi">
          <p>« Bons prix et conseils avisés. »</p><br>
          <footer>— Luc, Carry le Rouet</footer>
        </blockquote>
        <blockquote class="testi">
          <p><p>« Supermarché bien achalandé avec un personnel agréable. »</p><br>
          <footer>— Robert, Ensues la Redonne</footer>
        </blockquote>
        <blockquote class="testi">
          <p>« Toujours de bons produits frais et un service impeccable. »</p><br>
          <footer>— Sophie, Carry le Rouet</footer>
        </blockquote>
        <blockquote class="testi">
          <p>« J'adore les promotions régulières, ça vaut le coup ! »</p><br>
          <footer>— Marc, Le Rouet</footer>
        </blockquote>
        <blockquote class="testi">
          <p>« Une vraie épicerie de proximité, on y trouve tout ce qu'il faut. »</p><br>
          <footer>— Isabelle, Carry le Rouet</footer>
        </blockquote>
        <blockquote class="testi">
          <p>« Le personnel est toujours souriant et serviable. »</p><br>
          <footer>— David, Sausset les Pins</footer>
        </blockquote>
        <blockquote class="testi">
          <p>« Les fruits et légumes sont toujours de qualité. »</p><br>
          <footer>— Laura, Carry le Rouet</footer>
        </blockquote>
        <blockquote class="testi">
          <p>« Pratique pour les petites courses du quotidien. »</p><br>
          <footer>— Thomas, Ensues la Redonne</footer>
        </blockquote>
      </div>
      <div style="text-align: center; margin-top: 2rem;">
       <a href="comment.php" class="btn btn-primary">Votre avis</a>
      </div>
      </div>
    </section>

    <!-- Infos pratiques -->
    <section id="contact" class="practical container" aria-labelledby="info-title">
      <h2 id="info-title">Infos pratiques</h2><br>
      <div class="grid info-grid">
        <div>
          <h3>Horaires</h3>
          <table class="hours">
            <tr><td>Lundi — samedi</td><td>8:00 — 20:45</td></tr>

            <tr><td>Dimanche</td><td>8:00 - 20:45</td></tr>
          </table>
          <h3>Services</h3>
          <ul>
            <li>Parking gratuit</li>
            <li>Locker Amazon</li>
            <li>Paiement par Tickets Restaurant</li>
          </ul>
        </div>
        <div>
          <h3>Localisation</h3>
          <p>2 chemin du rivage,13620 Carry le Rouet </p>
          
          <div class="map-embed">
            <iframe title="carte" src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d5804.692148787698!2d5.146674775710152!3d43.32794677111939!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x12c9dc2c911982bd%3A0xd0d2cd1a236e1c39!2s2%20Chem.%20du%20Rivage%2C%2013620%20Carry-le-Rouet%2C%20France!5e0!3m2!1sen!2sus!4v1758184397224!5m2!1sen!2sus" width="350" height="250" style ="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
          </div>
        </div>
      </div>
    </section>

    

  </main>

  <?php include __DIR__ . '/inc/footer.php'; ?>

  <!-- Modal produit (vide au départ) -->
  <div id="product-modal" class="modal" aria-hidden="true">
    <div class="modal-inner" role="dialog" aria-modal="true" aria-labelledby="modal-title">
      <button class="modal-close" id="modal-close" aria-label="Fermer">×</button>
      <div id="modal-content"></div>
    </div>
  </div>

  <!-- Expose products to JS safely -->
  <script>
    window.__products = <?php echo json_encode($products, JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT); ?>;
    window.__promos = <?php echo json_encode($promos, JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT); ?>;
  </script>
  <script src="script.js" defer></script>
</body>
</html>
