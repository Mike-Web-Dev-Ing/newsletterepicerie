<?php
if (!function_exists('esc')) {
    function esc($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
}
?>
<footer class="site-footer">
  <div class="container footer-inner">
    <div>
      <strong>Franprix</strong><br>
      2 chemin du rivage, 13620 Carry le Rouet<br>
      <a href="tel:+33123456789">+33 4 42 45 01 44</a><br>
      <a href="mailto:magasin1904@franprix.fr">magasin1904@franprix.fr</a>
    </div>
    <div>
      <nav aria-label="Liens utiles">
        <a href="/mentions-legales.php">Mentions légales</a><br>
        <a href="/politique-confidentialite.php">Politique de confidentialité</a><br>
        <a href="/#contact">Infos pratiques</a>
      </nav>
    </div>
  </div>
</footer>

<div class="cookie-banner" id="cookie-banner" role="dialog" aria-live="polite" aria-modal="false">
  <div class="cookie-banner__content">
    <p class="cookie-banner__text">
      Nous utilisons des cookies essentiels pour faire fonctionner le site et mesurer l'audience. Vous pouvez à tout moment ajuster vos préférences.
    </p>
    <div class="cookie-banner__actions">
      <button type="button" class="btn btn-primary cookie-banner__accept" data-cc-accept>Accepter les cookies</button>
      <button type="button" class="btn btn-outline cookie-banner__prefs" data-cc-open-preferences>Paramètres cookies</button>
    </div>
  </div>
</div>

<div class="cookie-preferences" id="cookie-preferences" role="dialog" aria-modal="true" aria-labelledby="cookie-preferences-title" aria-hidden="true">
  <div class="cookie-preferences__backdrop" data-cc-close></div>
  <div class="cookie-preferences__dialog" role="document" tabindex="-1">
    <header class="cookie-preferences__header">
      <h2 id="cookie-preferences-title">Paramètres des cookies</h2>
      <button type="button" class="cookie-preferences__close" aria-label="Fermer la fenêtre" data-cc-close>&times;</button>
    </header>
    <div class="cookie-preferences__body">
      <p>Choisissez quelles catégories de cookies vous souhaitez autoriser. Les cookies nécessaires sont toujours activés car ils garantissent le bon fonctionnement du site.</p>
      <div class="cookie-option">
        <div>
          <strong>Essentiels</strong>
          <p>Indispensables pour assurer la sécurité et les fonctionnalités de base. Toujours actifs.</p>
        </div>
        <label class="switch" aria-disabled="true">
          <input type="checkbox" checked disabled>
          <span class="slider"></span>
        </label>
      </div>
      <div class="cookie-option">
        <div>
          <strong>Statistiques</strong>
          <p>Nous aident à comprendre comment le site est utilisé pour l'améliorer.</p>
        </div>
        <label class="switch">
          <input type="checkbox" data-cc-category="analytics">
          <span class="slider"></span>
        </label>
      </div>
      <div class="cookie-option">
        <div>
          <strong>Marketing</strong>
          <p>Permettent de personnaliser votre expérience et nos communications.</p>
        </div>
        <label class="switch">
          <input type="checkbox" data-cc-category="marketing">
          <span class="slider"></span>
        </label>
      </div>
    </div>
    <footer class="cookie-preferences__footer">
      <button type="button" class="btn btn-outline" data-cc-reject>Refuser</button>
      <button type="button" class="btn btn-primary" data-cc-save>Enregistrer</button>
    </footer>
  </div>
</div>

<script src="/assets/js/cookie-consent.js" defer></script>
