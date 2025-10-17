# Dossier Projet – Newsletter Épicerie

**Titre :** Mise en place d'une plateforme de promotion et de newsletter pour Franprix Carry-le-Rouet  
**Auteur :** JEAN-CHARLES Mickaël
**Période :** Septembre – Octobre 2025  
**Entreprise d'accueil :** Franprix Carry-le-Rouet, 2 chemin du rivage, 13620 Carry-le-Rouet

---

## Page de garde

- Titre du projet : *Newsletter Épicerie – Digitalisation des offres locales*
- Candidat : Jean-Charles Mickaël
- Session : 2025
- Organisme de formation : Online Formapro
- Maître d'apprentissage / tuteurs : Jean-Charles Grégory,
 Dubois Anne Sophie

---


- Date de remise : [Date]

---

## Sommaire

1. Compétences DWWM couvertes
2. Contexte du projet
3. Réalisations front-end
4. Réalisations back-end
5. Jeu d'essai représentatif
6. Contraintes et arbitrages
7. Veille sécurité
8. Bilan et perspectives
9. Annexes

---

## 1. Compétences DWWM couvertes

| Compétence | Activités menées | Couverture |
| --- | --- | --- |
| Bloc 1 – Maquetter une application | Conception d'un kit UI et d'un parcours utilisateur desktop/mobile (Annexes A.1 et A.2), alignement charte Franprix | Validé |
| Bloc 1 – Réaliser une interface utilisateur web statique et adaptable | Intégration HTML/CSS responsive et navigation mobile (`index.php:11`, `style.css:295`) | Validé |
| Bloc 1 – Développer une interface utilisateur web dynamique | Gestion modale, filtrage promotions, envoi AJAX (`script.js:2` → `script.js:176`) | Validé |
| Bloc 2 – Créer une base de données | Modélisation MCD/MPD, scripts MySQL (`data/schema.sql`, `backend/sql/schema_roles.sql`) | Validé |
| Bloc 2 – Développer des composants d'accès aux données | CRUD promotions, inscription newsletter, PDO préparé (`newsletter_subscribe.php:17`) | Validé |
| Bloc 2 – Développer la partie back-end sécurisée | Authentification double, rôles API, Mailchimp, notifications push | Validé |
| Bloc 3 – Préparer et exécuter le déploiement | Procédures d'installation MAMP, import SQL, configuration Mailchimp (`README.md`) | Partiel |
| Bloc 3 – Contribuer à l'intégration et au déploiement | Checklist de mise en production, sécurisation `.htaccess`, recommandation HTTPS | Partiel |

**Validation globale :** le projet couvre l'ensemble des compétences clés du référentiel DWWM, à l'exception des modules CMS/e-commerce hors périmètre.

## 2. Contexte du projet

### 2.1 Entreprise et service
- **Enseigne :** Franprix Carry-le-Rouet, magasin de proximité (400 m², 5 collaborateurs).
- **Service porteur :** Marketing local, sous la supervision de la gérante et du responsable digital.
- **Organisation :** cycles courts de deux semaines, démonstration hebdomadaire.

### 2.2 Expression des besoins
- Site vitrine responsive mettant en avant promotions et informations pratiques.
- Collecte d'emails conforme RGPD avec double opt-in Mailchimp.
- Administration simple des promotions et abonnés par le personnel.
- Génération d'une version imprimable/PDF de la newsletter.
- Socle sécurisé (authentification, protection données, sauvegardes).

### 2.3 Contraintes et livrables
- **Délais :** 6 semaines (2 cadrage, 3 développement, 1 recette).
- **Budget :** 0 € licences (outils open-source et comptes existants).
- **Livrables :** site responsive, espace administrateur, scripts SQL, procédures Mailchimp, dossier projet, annexes.

### 2.4 Environnement humain et technique
- **Humain :** 1 développeur fullstack (candidat), 1 UI designer freelance (5 j/h), 1 référent métier.
- **Technique :** PHP 8.2 (MAMP), MySQL 8, HTML5/CSS3, JavaScript vanilla, Bootstrap/Tailwind (admin), Mailchimp API v3, Expo Push API.
- **Outils :** Git local, Figma, Trello, Zed (IDE).

### 2.5 Objectifs qualité
- Accessibilité (AA), navigation clavier, ARIA, focus control.
- Performance (chargement < 2 s desktop, < 3 s mobile) par optimisation images et lazy-loading.
- Sécurité (hash mots de passe, sessions sécurisées, validation input).
- Maintenabilité (séparation front/back, configuration centralisée, commentaires ciblés).

## 3. Réalisations front-end

### 3.1 Approche UI/UX
- Maquettes desktop/mobile (Annexes A.1 et A.2) basées sur la charte Franprix.
- Palette : orange #E9630C, gris clair, typographie Inter.
- Parcours principal : Découverte → Promotions → Fiche → Inscription newsletter.

### 3.2 Intégration responsive
- Structure HTML sémantique (`index.php:11`).
- Header mixant navigation desktop & bouton burger (`index.php:35`).
- Sections dédiées : hero, valeurs, promotions, newsletter, témoignages, infos pratiques.
- Media queries et grilles CSS (`style.css:295` → `style.css:338`).
- Navigation mobile gérée par JavaScript (`script.js:2` → `script.js:33`).

### 3.3 Dynamique front
- Modale produit accessible (focus trap) (`script.js:20` → `script.js:68`).
- Chargement dynamique des promotions pour la page newsletter (`script.js:116` → `script.js:176`).
- Soumission AJAX du formulaire newsletter avec retours contextualisés (`script.js:77` → `script.js:106`).

### 3.4 Accessibilité et performance
- Attributs ARIA (`index.php:35`, `index.php:112`).
- `aria-live="polite"` pour les messages formulaire.
- Images WebP, `loading="lazy"`, compression des assets.
- Fonction d'échappement PHP systématique (`index.php:9`).

## 4. Réalisations back-end

### 4.1 Architecture
- Couche accès données (`inc/db.php`) et authentification (`inc/auth.php`).
- Modules admin : gestion promotions, abonnés, envoi Mailchimp.
- APIs REST pour usages externes (`backend/api/*`).

### 4.2 Base de données
- Tables `promotions`, `subscribers`, `users`, `push_tokens` (`data/schema.sql`, `backend/sql/schema_roles.sql`, `backend/sql/schema_push.sql`).
- Indexation : `active/created_at`, `UNIQUE email`, `UNIQUE token`.
- Scripts d'initialisation fournis.

### 4.3 Composants métier
- Génération campaign Mailchimp (`admin/mailchimp_send.php:13` → `admin/mailchimp_send.php:118`).
- CRUD promotions avec gestion upload (`admin/promotions/create.php`, `edit.php`).
- Page newsletter imprimable (`newsletter.php`).

### 4.4 Accès données & APIs
- Inscription newsletter (`newsletter_subscribe.php:17`) avec Mailchimp non bloquant.
- API `promotions.php` (filtre, pagination) (`backend/api/promotions.php`).
- API `auth_login.php` (tokens session + rôles) (`backend/api/auth_login.php`).
- Garde rôles (`backend/api/_auth_guard_role.php`).

### 4.5 Sécurité
- Double authentification admin : HTTP Basic + session PHP (`admin/.htaccess`, `inc/auth.php`).
- Hash Bcrypt, tokens API, validations serveur.
- Contrôle MIME uploads (`admin/promotions/create.php:20`).
- Mentions légales & RGPD (`mentions-legales.php`).

### 4.6 Notifications push
- Enregistrement token (`backend/api/save_push_token.php`).
- Envoi via Expo (`backend/api/push_send.php`) – amélioration à prévoir pour le contrôle d'accès.

### 4.7 Exploitation
- README (installation, import SQL, configuration Mailchimp).
- Paramétrage centralisé (`inc/db.php`, `inc/mailchimp.php`).
- Logs d'erreur à centraliser côté serveur.

## 5. Jeu d'essai représentatif – Inscription newsletter

| Cas | Données d'entrée | Résultat attendu | Résultat obtenu | Statut |
| --- | --- | --- | --- | --- |
| Succès | `name=Lucie`, `email=lucie@example.com` | JSON succès + Mailchimp pending | Conforme | ✅ |
| Email invalide | `name=Marc`, `email=marc@` | JSON erreur "Email invalide." | Conforme | ✅ |
| Doublon | `email=lucie@example.com` | Mise à jour prénom, pas de duplication | Conforme | ✅ |
| Mailchimp indisponible | API key invalide | JSON succès + warning service indisponible | Conforme | ✅ |

## 6. Contraintes et arbitrages

- Environnement MAMP sans HTTPS : recommandation Let’s Encrypt en production.
- Budget nul → choix PHP natif + JS vanilla plutôt qu'un framework.
- Ressources images limitées → mix photos internes et placeholders optimisés.
- API push à sécuriser (rôle admin requis, cf. Section 7.2).

## 7. Veille sécurité

### 7.1 Démarche
- Veille OWASP Top 10 2021, newsletters SANS & CERT-FR.
- Suivi CVE PHP/MySQL (ex : CVE-2023-3823, patché en 8.2).
- Lecture guides Mailchimp API v3 (gestion clés, HTTPS).
- Références RGPD (CNIL) pour newsletters : double opt-in recommandé.

### 7.2 Vulnérabilités identifiées & corrections
- Absence token CSRF formulaires admin → ajout recommandé (`$_SESSION['csrf']`).
- Accès libre `push_send.php` → protéger via `_auth_guard_role` avec `$REQUIRED_ROLE='admin'`.
- Validation upload à renforcer (limite taille, `finfo_file`).
- Headers sécurité additionnels (`CSP`, `HSTS`, `X-Frame-Options`).
- Journaux : prévoir rotation, anonymisation et stockage hors racine web.

## 8. Bilan et perspectives

- Objectifs atteints : vitrine responsive, back-office autonome, intégration Mailchimp, documentation.
- Plateforme extensible (catégories, click-and-collect, analytics Mailchimp).
- Prochaines étapes : 2FA admin, tokens JWT, pipeline CI/CD, exports statistiques.

## 9. Annexes (30 pages max)

- **Annexe A.1** – Maquette desktop (Figma, export PNG).
- **Annexe A.2** – Maquette mobile (Figma, export PNG).
- **Annexe A.3** – Flowchart parcours utilisateur.
- **Annexe B.1** – Rapport tests Postman (captures).
- **Annexe B.2** – Captures front (desktop/tablette/mobile) + extraits code associés.
- **Annexe C.1** – Scripts SQL (`data/schema.sql`, `backend/sql/schema_roles.sql`, `backend/sql/schema_push.sql`).
- **Annexe C.2** – Extraits back-end (`admin/mailchimp_send.php`, `newsletter_subscribe.php`, `backend/api/promotion_create.php`).
- **Annexe C.3** – Checklists déploiement & sécurité.

---

**Notes pagination :** document principal 38 pages (format A4, interligne 1,15, marges 2,54 cm). Annexes limitées à 18 pages (6 annexes × 3 pages en moyenne).

