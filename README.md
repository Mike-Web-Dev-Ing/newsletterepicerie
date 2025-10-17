# newsletterepicerie — Guide d'installation

## Prérequis
- PHP 8.x avec extensions `pdo` et `pdo_mysql`
- MySQL/MariaDB
- Serveur HTTP (MAMP, XAMPP, Apache, Nginx…)

## Structure du projet
- `index.php` : page d'accueil affichant les promotions et le formulaire newsletter.
- `comment.php` : collecte et affiche les avis clients (note optionnelle).
- `newsletter.php` / `newsletter_subscribe.php` : version imprimable de la newsletter et endpoint d'inscription.
- `product.php` : fiche produit individuelle.
- `admin/` : interface de gestion (login, promotions, abonnés, Mailchimp…).
- `backend/api/` : endpoints PHP utilisés par l'admin (CRUD promotions, push notifications, etc.).
- `inc/` : utilitaires partagés (`db.php`, `auth.php`, `footer.php`, `mailchimp.php`).
- `data/` : schémas SQL et jeux de données (`product.json` pour la vitrine).
- `assets/`, `style.css`, `script.js` : ressources front.
- `public/` : alias pour servir certaines pages si votre hébergeur impose un `DocumentRoot` sur ce dossier (les fichiers y requièrent ceux de la racine).

## Mise en route rapide
1. Placez le dossier du projet dans la racine web de votre serveur (ex. `/Applications/MAMP/htdocs/newsletterepicerie`).
   - Vous pouvez pointer directement le `DocumentRoot` vers ce dossier. Les fichiers de `public/` restent utilisables si vous préférez une racine dédiée.
2. Vérifiez/ajustez les identifiants de connexion dans `inc/db.php` (`EPICERIE_DSN`, `EPICERIE_DB_USER`, `EPICERIE_DB_PASS` peuvent être définies dans l'environnement).
3. Importez la base de données.

## Base de données
1) Créez une base `epicerie` (ou adaptez le nom dans `inc/db.php`).
2) Importez le schéma contenant les tables `subscribers`, `promotions`, `comments` :

```
# Schéma minimal
mysql -u root -p epicerie < data/schema.sql

# OU dump complet équivalent (voir également data/backups/ pour un dump phpMyAdmin)
mysql -u root -p epicerie < data/epicerie.sql
```

3) Testez la connexion en rechargeant la page d'accueil ou en exécutant un script CLI :

```
EPICERIE_DSN="mysql:host=localhost;dbname=epicerie;charset=utf8mb4" \
EPICERIE_DB_USER="root" EPICERIE_DB_PASS="root" php -r "require 'inc/db.php'; echo 'OK', PHP_EOL;"
```

## Accès administrateur
- URL : `/admin/login`
- Identifiants par défaut : `admin` / `admin123`
- Pour changer le mot de passe : remplacez `ADMIN_PASS_HASH` dans `inc/auth.php` par un hash bcrypt :

```
php -r "echo password_hash('votreMotDePasse', PASSWORD_BCRYPT, ['cost'=>12]), PHP_EOL;"
```

## Promotions
- Gestion : `/admin/promotions/` (créer, éditer, supprimer).
- Uploads : enregistrés dans le dossier `uploads/` (créé automatiquement si nécessaire).

## Abonnés newsletter
- Liste : `/admin/subscribers`
- Formulaire public : section "Restez informé" sur la page d'accueil (`/`).
- Page dédiée : `/newsletter` (bouton "Imprimer / PDF" et paramètre `?print=1`).

## Collecte d'avis
- Page publique : `/comment.php`
- Les avis sont stockés dans la table `comments` (prénom, note 1-5 facultative, message).
- La page d'accueil renvoie vers cette page via le bouton "Votre avis".

## Pages légales
- Mentions légales : `/mentions-legales.php`
- Politique de confidentialité : `/politique-confidentialite.php`

## Mailchimp (envoi de la newsletter)
- Configuration : `inc/mailchimp.php`
  - `MAILCHIMP_API_KEY`
  - `MAILCHIMP_SERVER_PREFIX`
  - `MAILCHIMP_LIST_ID`
  - `MAILCHIMP_FROM_NAME`, `MAILCHIMP_REPLY_TO`
  - `MAILCHIMP_SUBSCRIBE_MODE` (`pending` par défaut, `subscribed` pour inscription directe)
- Envoi depuis l'admin : `/admin/mailchimp`
  - Mode Test (emails de test) et Production (toute l'audience, confirmation "CONFIRMER" requise)
  - Aperçu HTML généré à partir des promotions actives

## Débogage rapide
- `debug_error.php` : état PHP / extensions PDO.
- `test.php` : sortie `phpinfo()`.

## Sécurité admin (.htaccess)
- Double protection (facultative) activée :
  - Auth HTTP Basic : `admin/.htaccess` + `admin/.htpasswd` (login `admin`)
  - Auth PHP (login de l'admin)
- Pour changer le mot de passe Basic : `htpasswd -c -B admin/.htpasswd admin`
- Assurez-vous que votre serveur autorise `.htaccess` (`AllowOverride All`)
