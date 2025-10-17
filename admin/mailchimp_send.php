<?php
require_once __DIR__ . '/../inc/auth.php';
require_admin();
require_once __DIR__ . '/../inc/db.php';
require_once __DIR__ . '/../inc/mailchimp.php';

$errors = [];
$done = null;

// Charger les promotions actives
$promos = $pdo->query("SELECT * FROM promotions WHERE active=1 ORDER BY created_at DESC")->fetchAll();

// Construire une version email-friendly (table layout, styles inline simples)
function build_email_html(array $promos): string {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $base = rtrim($scheme . '://' . $host, '/') . '/';

    $rows = '';
    foreach ($promos as $p) {
        $title = htmlspecialchars($p['title'] ?? '', ENT_QUOTES, 'UTF-8');
        $desc  = htmlspecialchars($p['description'] ?? '', ENT_QUOTES, 'UTF-8');
        $price = number_format((float)$p['price'], 2, ',', ' ');
        $old   = $p['old_price'] !== null ? number_format((float)$p['old_price'], 2, ',', ' ') : '';
        $dates = trim(($p['start_date'] ?? '') . ' → ' . ($p['end_date'] ?? ''));
        $img   = !empty($p['product_image']) ? $base . ltrim($p['product_image'], '/') : $base . 'assets/images/unnamed.jpg';
        $rows .= "
          <tr>
            <td style=\"padding:12px;border:1px solid #e5e7eb;vertical-align:top;\">
              <img src=\"{$img}\" alt=\"" . $title . "\" width=\"560\" style=\"max-width:100%;height:auto;border-radius:8px;display:block;margin:0 0 8px 0;\">
              <div style=\"font-size:16px;line-height:1.4;font-weight:700;margin:4px 0;\">{$title}</div>
              " . ($desc ? "<div style=\"font-size:14px;line-height:1.5;color:#374151;margin:4px 0;\">{$desc}</div>" : '') . "
              <div style=\"font-size:16px;line-height:1.4;margin:6px 0;\"><strong>{$price}€</strong>" . ($old ? " <span style=\"color:#6b7280;text-decoration:line-through;\">{$old}€</span>" : '') . "</div>
              " . ($dates !== ' → ' ? "<div style=\"font-size:12px;line-height:1.4;color:#6b7280;\">Valable: {$dates}</div>" : '') . "
            </td>
          </tr>
        ";
    }

    $today = date('d/m/Y');
    $html = "<!doctype html><html><head><meta charset=\"utf-8\"></head><body style=\"margin:0;padding:0;background:#f5f7fa;\">
      <table role=\"presentation\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\" style=\"background:#f5f7fa;\"><tr><td align=\"center\">
        <table role=\"presentation\" width=\"600\" cellspacing=\"0\" cellpadding=\"0\" style=\"max-width:600px;width:100%;margin:0 auto;background:#ffffff;border:1px solid #e5e7eb;border-radius:10px;overflow:hidden;\">
          <tr><td style=\"padding:16px 20px;border-bottom:1px solid #e5e7eb;\">
            <table width=\"100%\" cellspacing=\"0\" cellpadding=\"0\"><tr>
              <td style=\"font-weight:700;font-size:16px;\">Promotions en cours</td>
              <td align=\"right\" style=\"color:#6b7280;font-size:12px;\">{$today}</td>
            </tr></table>
          </td></tr>
          <tr><td style=\"padding:12px;\">
            <table role=\"presentation\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\">{$rows}</table>
          </td></tr>
          <tr><td style=\"padding:14px 20px;border-top:1px solid #e5e7eb;color:#6b7280;font-size:12px;\">Franprix — 2 chemin du rivage, 13620 Carry-le-Rouet • <a href=\"{$base}\" style=\"color:#6b7280\">Site</a></td></tr>
        </table>
      </td></tr></table>
    </body></html>";
    return $html;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject = trim($_POST['subject'] ?? ('Promotions — ' . date('d/m/Y')));
    $sendMode = $_POST['send_mode'] ?? 'test'; // 'test' ou 'live'
    $testEmailsRaw = trim($_POST['test_emails'] ?? '');
    $testEmails = array_values(array_filter(array_map('trim', preg_split('/[,;\s]+/', $testEmailsRaw))));
    $confirmLive = trim($_POST['confirm_live'] ?? '');

    if (MAILCHIMP_API_KEY === 'CHANGE_ME-xxxx-us21' || MAILCHIMP_LIST_ID === 'CHANGE_ME_LIST_ID') {
        $errors[] = "Veuillez configurer inc/mailchimp.php (API key et LIST ID).";
    }
    if (empty($promos)) {
        $errors[] = "Aucune promotion active à envoyer.";
    }
    if ($sendMode === 'test' && count($testEmails) === 0) {
        $errors[] = "Renseignez au moins une adresse de test";
    }
    if ($sendMode === 'live' && strtoupper($confirmLive) !== 'CONFIRMER') {
        $errors[] = "Pour l'envoi en production, tapez CONFIRMER dans le champ de confirmation.";
    }

    if (!$errors) {
        // 1) Créer la campagne
        $create = mailchimp_request('POST', '/campaigns', [
            'type' => 'regular',
            'recipients' => [ 'list_id' => MAILCHIMP_LIST_ID ],
            'settings' => [
                'subject_line' => $subject,
                'from_name'    => MAILCHIMP_FROM_NAME,
                'reply_to'     => MAILCHIMP_REPLY_TO,
            ],
        ]);

        if ($create['status'] >= 200 && $create['status'] < 300 && !empty($create['body']['id'])) {
            $cid = $create['body']['id'];

            // 2) Définir le contenu HTML
            $html = build_email_html($promos);
            $content = mailchimp_request('PUT', '/campaigns/' . urlencode($cid) . '/content', [ 'html' => $html ]);

            if (!($content['status'] >= 200 && $content['status'] < 300)) {
                $errors[] = 'Erreur contenu: ' . ($content['raw'] ?? '');
            } else {
                // 3) Envoi: test ou production
                if ($sendMode === 'test') {
                    $payload = [ 'test_emails' => $testEmails, 'send_type' => 'html' ];
                    $send = mailchimp_request('POST', '/campaigns/' . urlencode($cid) . '/actions/test', $payload);
                    if ($send['status'] === 204) {
                        $done = 'Email de test envoyé à: ' . htmlspecialchars(implode(', ', $testEmails)) . ' (Campagne: ' . htmlspecialchars($cid) . ').';
                    } else {
                        $errors[] = 'Erreur envoi test: ' . ($send['raw'] ?? '');
                    }
                } else {
                    $send = mailchimp_request('POST', '/campaigns/' . urlencode($cid) . '/actions/send');
                    if ($send['status'] === 204) {
                        $done = 'Campagne envoyée avec succès (ID: ' . htmlspecialchars($cid) . ').';
                    } else {
                        $errors[] = 'Erreur envoi: ' . ($send['raw'] ?? '');
                    }
                }
            }
        } else {
            $errors[] = 'Erreur création campagne: ' . ($create['raw'] ?? '');
        }
    }
}
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin — Envoyer Newsletter (Mailchimp)</title>
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
    <h1 class="h3 mb-3">Envoyer la newsletter (Mailchimp)</h1>

    <?php foreach ($errors as $e): ?>
      <div class="alert alert-danger"><?php echo htmlspecialchars($e); ?></div>
    <?php endforeach; ?>
    <?php if ($done): ?>
      <div class="alert alert-success"><?php echo $done; ?></div>
    <?php endif; ?>

    <div class="card shadow-sm mb-3">
      <div class="card-body">
        <p class="mb-2">Promotions actives à inclure: <strong><?php echo count($promos); ?></strong></p>
        <form method="post" class="row g-3" id="mc-form">
          <div class="col-md-8">
            <label class="form-label">Objet de l'email
              <input class="form-control" name="subject" value="<?php echo htmlspecialchars('Promotions — ' . date('d/m/Y')); ?>">
            </label>
          </div>
          <div class="col-md-4">
            <label class="form-label">Mode d'envoi</label>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="send_mode" id="mode_test" value="test" checked>
              <label class="form-check-label" for="mode_test">Test (recommandé d'abord)</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="send_mode" id="mode_live" value="live">
              <label class="form-check-label" for="mode_live">Production (à toute l'audience)</label>
            </div>
          </div>
          <div class="col-12">
            <label class="form-label">Emails de test (séparés par virgules)
              <input class="form-control" name="test_emails" placeholder="ex: vous@domaine.com, collegue@domaine.com">
            </label>
          </div>
          <div class="col-12" id="confirm-live-wrap" style="display:none;">
            <label class="form-label">Confirmation envoi production
              <input class="form-control" name="confirm_live" id="confirm_live" placeholder="Tapez CONFIRMER pour valider">
            </label>
            <div class="form-text">Sécurité: requis pour l'envoi à toute l'audience.</div>
          </div>
          <div class="col-12">
            <button class="btn btn-primary" type="submit" id="submit_btn">Créer et envoyer</button>
          </div>
        </form>
        <p class="text-muted small mt-3">Configurez votre clé API et List ID dans <code>inc/mailchimp.php</code>. En mode test, la campagne n'est pas envoyée à l'audience, seulement aux adresses ci-dessus.</p>
      </div>
    </div>

    <div class="card">
      <div class="card-body">
        <h5 class="card-title">Aperçu HTML (généré)</h5>
        <iframe title="preview" style="width:100%;height:400px;border:1px solid #e5e7eb;border-radius:8px;" srcdoc="<?php echo htmlspecialchars(build_email_html($promos)); ?>"></iframe>
      </div>
    </div>
  </main>
  <script>
    (function(){
      const modeTest = document.getElementById('mode_test');
      const modeLive = document.getElementById('mode_live');
      const wrap = document.getElementById('confirm-live-wrap');
      const confirmInput = document.getElementById('confirm_live');
      const form = document.getElementById('mc-form');
      function update(){
        const live = modeLive.checked;
        wrap.style.display = live ? '' : 'none';
        confirmInput.required = live;
      }
      modeTest.addEventListener('change', update);
      modeLive.addEventListener('change', update);
      update();
      form.addEventListener('submit', function(e){
        if (modeLive.checked) {
          if ((confirmInput.value || '').trim().toUpperCase() !== 'CONFIRMER') {
            e.preventDefault();
            alert("Tapez CONFIRMER dans le champ de confirmation pour lancer l'envoi en production.");
            confirmInput.focus();
            return false;
          }
          if (!confirm('Confirmer l\\'envoi à toute l\\'audience Mailchimp ?')) {
            e.preventDefault();
            return false;
          }
        }
      });
    })();
  </script>
</body>
</html>
