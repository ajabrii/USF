# La Table d'Or — Restaurant Booking

Application de réservation de tables pour un restaurant, développée en PHP 8.2+ natif avec MySQL et animations GSAP 1.11.1.

## Setup

1. Copiez le dossier `restaurant_booking/` dans `htdocs/` (XAMPP) ou `Sites/` (MAMP).
2. Importez `database.sql` via phpMyAdmin ou en CLI :
   ```bash
   mysql -u root < database.sql
   ```
3. Les mots de passe des comptes de test sont déjà hashés dans le fichier SQL. Si besoin de régénérer :
   ```bash
   php -r "echo password_hash('admin123', PASSWORD_BCRYPT, ['cost'=>12]);"
   php -r "echo password_hash('user123',  PASSWORD_BCRYPT, ['cost'=>12]);"
   ```
4. Visitez `http://localhost/restaurant_booking/public/`

## Comptes de test

| Email           | Mot de passe | Rôle   |
|-----------------|-------------|--------|
| admin@test.com  | admin123    | ADMIN  |
| user1@test.com  | user123     | USER   |
| user2@test.com  | user123     | USER   |

## Architecture

- **Langage** : PHP 8.2+ — pages natives (.php), aucun framework
- **Base de données** : MySQL via MySQLi (requêtes préparées uniquement)
- **Authentification** : Sessions PHP, ROLE_USER / ROLE_ADMIN
- **Sécurité** : Owner-check (403), CSRF, validation d'upload, échappement XSS
- **Animations** : GSAP 1.11.1 (TweenMax, TimelineMax) dans `/public/js/animations.js`

## Structure des fichiers

```
restaurant_booking/
├── public/
│   ├── index.php              ← Dashboard / accueil
│   ├── login.php              ← Connexion
│   ├── logout.php             ← Déconnexion
│   ├── 403.php / 404.php      ← Pages d'erreur
│   ├── css/style.css          ← Design system complet
│   └── js/animations.js       ← Toutes les animations GSAP
├── src/
│   ├── config/db.php          ← Connexion MySQLi
│   ├── helpers/               ← auth, flash, security
│   └── templates/             ← header.php, footer.php
├── reservations/              ← CRUD utilisateur
│   ├── create.php, index.php, edit.php, cancel.php, thanks.php
├── admin/
│   ├── reservations/index.php ← Gestion admin des réservations
│   └── slots/index.php        ← CRUD créneaux horaires
├── database.sql               ← Schéma + données de démo
└── README.md
```

## Choix de sécurité

- **Toutes les requêtes SQL** utilisent des requêtes préparées MySQLi (`prepare` + `bind_param`) — zéro interpolation de variables.
- **Jeton CSRF** sur chaque formulaire POST (généré via `csrfToken()`, validé via `csrfValidate()`).
- **Owner-check** : chaque page de ressource vérifie `owner_id = currentUserId()` ou `ROLE_ADMIN`.
- **Sessions** régénérées à la connexion (`session_regenerate_id(true)`).
- **Échappement** : `h()` sur chaque donnée utilisateur affichée.
- **PRG** (Post/Redirect/Get) sur tous les formulaires de modification.

## Animations GSAP (v1.11.1)

Le fichier `animations.js` contient **6 zones universelles** et **7 zones spécifiques au restaurant** :

1. **Timeline Dashboard** — séquence d'entrée du header de bienvenue + cartes de statistiques admin avec stagger
2. **Stagger des listes** — les lignes de tableau et cartes de créneaux apparaissent progressivement
3. **Timeline formulaire réservation** — chaque champ glisse séquentiellement (date → créneau → couverts → bouton)
4. **Barre de capacité animée** — remplissage progressif avec easing
5. **Page de confirmation** — icône avec scale-bounce + détails en stagger
6. **Flash messages** — entrée avec Back easeOut + auto-disparition après 4s
7. **Filtres** — slide-in de la barre de filtres

Toutes les animations utilisent la syntaxe GSAP 1.x (`TweenMax.from()`, `TimelineMax()`, `.staggerFrom()`) et dégradent gracieusement si JavaScript est désactivé.

## Limitations connues & améliorations possibles

- Pas de rate-limiting sur la page de connexion (amélioration : compteur de tentatives en DB).
- Pas de notification email lors de la confirmation de réservation (nécessite MailHog ou un serveur SMTP).
- L'interface est en français ; l'internationalisation pourrait être ajoutée.
- Pas de pagination sur les listes — à ajouter pour de gros volumes de données.
