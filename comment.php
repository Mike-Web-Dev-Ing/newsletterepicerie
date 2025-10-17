<?php
require __DIR__ . '/inc/db.php';

if (!function_exists('esc')) {
    function esc($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
}

$errors = [];
$flashSuccess = isset($_GET['sent']) && $_GET['sent'] === '1';
$formData = [
    'name' => '',
    'rating' => '',
    'message' => '',
];

$createTableSql = <<<SQL
CREATE TABLE IF NOT EXISTS `comments` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(190) NOT NULL,
  `rating` TINYINT UNSIGNED DEFAULT NULL,
  `message` TEXT NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;

try {
    $pdo->exec($createTableSql);
} catch (PDOException $e) {
    $errors[] = "Impossible de préparer l'espace pour les avis. Merci de réessayer plus tard.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($errors)) {
    $formData['name'] = trim(filter_input(INPUT_POST, 'name', FILTER_UNSAFE_RAW) ?? '');
    $formData['name'] = strip_tags($formData['name']);

    $formData['message'] = trim(filter_input(INPUT_POST, 'message', FILTER_UNSAFE_RAW) ?? '');
    $formData['message'] = strip_tags($formData['message']);

    $rawRating = filter_input(INPUT_POST, 'rating', FILTER_SANITIZE_NUMBER_INT);
    $formData['rating'] = $rawRating !== null ? trim($rawRating) : '';

    if ($formData['name'] === '' || mb_strlen($formData['name']) < 2) {
        $errors[] = "Merci d'indiquer votre prénom (au moins 2 caractères).";
    } elseif (mb_strlen($formData['name']) > 190) {
        $errors[] = "Le prénom est trop long.";
    }

    if ($formData['message'] === '' || mb_strlen($formData['message']) < 10) {
        $errors[] = "Votre message doit contenir au moins 10 caractères.";
    } elseif (mb_strlen($formData['message']) > 1000) {
        $errors[] = "Votre message est trop long (maximum 1000 caractères).";
    }

    $ratingValue = null;
    if ($formData['rating'] !== '') {
        $ratingValue = filter_var(
            $formData['rating'],
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1, 'max_range' => 5]]
        );
        if ($ratingValue === false) {
            $errors[] = "La note doit être comprise entre 1 et 5.";
        }
    }

    if (empty($errors)) {
        try {
            $insert = $pdo->prepare(
                "INSERT INTO comments (name, rating, message) VALUES (:name, :rating, :message)"
            );
            $insert->bindValue(':name', $formData['name']);
            if ($ratingValue === null) {
                $insert->bindValue(':rating', null, PDO::PARAM_NULL);
            } else {
                $insert->bindValue(':rating', (int)$ratingValue, PDO::PARAM_INT);
            }
            $insert->bindValue(':message', $formData['message']);
            $insert->execute();

            header('Location: comment.php?sent=1');
            exit;
        } catch (PDOException $e) {
            $errors[] = "Erreur lors de l'enregistrement de votre avis. Merci de réessayer.";
        }
    }
}

try {
    $commentsStmt = $pdo->query(
        "SELECT name, rating, message, created_at FROM comments ORDER BY created_at DESC LIMIT 25"
    );
    $comments = $commentsStmt->fetchAll();
} catch (PDOException $e) {
    $comments = [];
    if (!in_array("Impossible de préparer l'espace pour les avis. Merci de réessayer plus tard.", $errors, true)) {
        $errors[] = "Impossible d'afficher les avis pour le moment.";
    }
}
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Votre avis — Franprix Carry le Rouet</title>
  <link rel="stylesheet" href="/style.css">
  <link rel="icon" href="/assets/favicon.ico">
  <style>
    .page-container {
      max-width: 960px;
      margin: 0 auto;
      padding: 2rem 1.5rem 3rem;
    }
    .page-container h1 {
      margin-bottom: .75rem;
    }
    .page-container p.lead {
      color: var(--muted);
      margin-bottom: 2rem;
    }
    .feedback-grid {
      display: grid;
      gap: 2rem;
      grid-template-columns: minmax(0, 1fr);
    }
    .comment-form {
      background: #fff;
      border-radius: var(--radius);
      padding: 1.75rem;
      box-shadow: 0 15px 25px rgba(0,0,0,0.05);
    }
    .field {
      margin-bottom: 1.25rem;
    }
    .field label {
      display: block;
      font-weight: 600;
      margin-bottom: .4rem;
    }
    .field input[type="text"],
    .field select,
    .field textarea {
      width: 100%;
      padding: .75rem;
      border: 1px solid rgba(0,0,0,0.12);
      border-radius: 8px;
      font: inherit;
      background: #fff;
    }
    .field textarea {
      min-height: 140px;
      resize: vertical;
    }
    .form-help {
      font-size: .85rem;
      color: var(--muted);
    }
    .messages {
      margin-bottom: 1.5rem;
      padding: 1rem;
      border-radius: 8px;
      background: rgba(47, 125, 74, 0.1);
      color: var(--brand);
    }
    .messages.error {
      background: rgba(233, 100, 12, 0.12);
      color: var(--accent-color);
    }
    .comments-section {
      display: flex;
      flex-direction: column;
      gap: 1rem;
    }
    .comment-card {
      background: var(--card);
      padding: 1.5rem;
      border-radius: var(--radius);
    }
    .comment-header {
      display: flex;
      flex-wrap: wrap;
      justify-content: space-between;
      align-items: baseline;
      gap: .5rem;
      margin-bottom: .75rem;
    }
    .comment-header strong {
      font-size: 1rem;
      color: var(--text-color);
    }
    .comment-date {
      font-size: .85rem;
      color: var(--muted);
    }
    .comment-body {
      white-space: pre-line;
    }
    @media (min-width: 900px) {
      .feedback-grid {
        grid-template-columns: 1fr 1fr;
        align-items: start;
      }
      .comments-section {
        max-height: 600px;
        overflow-y: auto;
        padding-right: .5rem;
      }
    }
  </style>
</head>
<body>
  <header class="site-header">
    <div class="container header-inner">
      <div class="brand">
        <a class="logo" href="/"><img src="/assets/images/franprix-logo.png" alt="logo franprix"></a>
        <span class="slogan">Frais • Local • À prix doux</span>
      </div>
      <nav class="main-nav" aria-label="Navigation principale">
        <ul class="nav-list" id="nav-list">
          <li><a href="/">Accueil</a></li>
          <li><a href="/newsletter.php" class="cta">Newsletter</a></li>
          <li><a href="/comment.php" class="cta">Votre avis</a></li>
          <li><a class="btn btn-outline-primary" href="/admin/login.php">Accès admin</a></li>
        </ul>
      </nav>
      <div class="contact">
        <a href="tel:+33442450144" aria-label="Appeler le magasin"><span role="img" aria-hidden="true">📞</span> +33 4 42 45 01 44</a>
      </div>
    </div>
  </header>

  <main>
    <div class="page-container">
      <nav class="breadcrumb-nav" aria-label="Fil d'Ariane">
        <ul class="breadcrumb">
          <li><a href="/">Accueil</a></li>
          <li>Votre avis</li>
        </ul>
      </nav>

      <h1>Partagez votre avis</h1>
      <p class="lead">Merci de nous aider à améliorer votre magasin Franprix Carry le Rouet. Laissez un message et, si vous le souhaitez, une note sur 5.</p>

      <div class="feedback-grid">
        <section aria-labelledby="form-title" class="comment-form">
          <h2 id="form-title" style="margin-bottom:1.25rem;">Votre retour</h2>

          <?php if (!empty($errors)): ?>
            <div class="messages error" role="alert">
              <strong>Nous n'avons pas pu enregistrer votre avis :</strong>
              <ul>
                <?php foreach ($errors as $err): ?>
                  <li><?php echo esc($err); ?></li>
                <?php endforeach; ?>
              </ul>
            </div>
          <?php elseif ($flashSuccess): ?>
            <div class="messages" role="status">
              Merci ! Votre avis a bien été enregistré.
            </div>
          <?php endif; ?>

          <form method="post" novalidate>
            <div class="field">
              <label for="name">Prénom *</label>
              <input type="text" id="name" name="name" required value="<?php echo esc($formData['name']); ?>">
            </div>
            <div class="field">
              <label for="rating">Note (facultatif)</label>
              <select id="rating" name="rating">
                <option value="">Choisir une note</option>
                <?php for ($i = 5; $i >= 1; $i--): ?>
                  <option value="<?php echo $i; ?>" <?php echo ($formData['rating'] === (string)$i) ? 'selected' : ''; ?>>
                    <?php echo $i; ?>/5
                  </option>
                <?php endfor; ?>
              </select>
              <p class="form-help">5 = excellent, 1 = à améliorer.</p>
            </div>
            <div class="field">
              <label for="message">Votre message *</label>
              <textarea id="message" name="message" required maxlength="1000"><?php echo esc($formData['message']); ?></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Envoyer mon avis</button>
          </form>
        </section>

        <section aria-labelledby="list-title">
          <h2 id="list-title" style="margin-bottom:1rem;">Vos messages</h2>
          <div class="comments-section" aria-live="polite">
            <?php if (!empty($comments)): ?>
              <?php foreach ($comments as $comment): ?>
                <article class="comment-card">
                  <div class="comment-header">
                    <strong><?php echo esc($comment['name']); ?></strong>
                    <span class="comment-date"><?php echo date('d/m/Y', strtotime($comment['created_at'])); ?></span>
                  </div>
                  <?php if (!empty($comment['rating'])): ?>
                    <p class="form-help" style="margin-bottom:.75rem;">Note : <?php echo (int)$comment['rating']; ?>/5</p>
                  <?php endif; ?>
                  <p class="comment-body"><?php echo nl2br(esc($comment['message'])); ?></p>
                </article>
              <?php endforeach; ?>
            <?php else: ?>
              <p class="form-help">Soyez le premier à nous laisser un message.</p>
            <?php endif; ?>
          </div>
        </section>
      </div>
    </div>
  </main>

  <?php include __DIR__ . '/inc/footer.php'; ?>
</body>
</html>
