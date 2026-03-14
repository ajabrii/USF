---
name: php-gsap-school-project
description: >
  Use this skill IMMEDIATELY and ALWAYS whenever the user asks to build, generate, scaffold, start,
  or implement any of the 10 PHP/GSAP school web projects from the "Cahiers des charges - Projets Web"
  specification. Triggers include: "build the project", "create the CRM project", "start the HelpDesk",
  "generate the Job Board", "make the library app", "restaurant booking", "events platform",
  "e-commerce", "grades manager", "association cotisations", "quiz platform", or any mention of
  building a PHP school project with GSAP. Also triggers when the user says "implement the full project",
  "write the code for project X", or "help me build my school project".
  This skill produces a COMPLETE, production-ready, fully-functional PHP 8.2+ project with MySQL,
  native PHP pages, MySQLi prepared statements, role-based access control, owner-check security,
  and GSAP 1.11.1 animations — all from scratch, ready to drop into XAMPP/MAMP.
---

# PHP + GSAP School Project Builder

## Overview

This skill builds **any of the 10 projects** from the "Cahiers des charges - Projets Web" specification,
fully respecting every constraint: PHP 8.2+, MySQL (XAMPP/MAMP), MySQLi prepared statements,
ROLE_USER / ROLE_ADMIN access control, owner-check (403), GSAP 1.11.1, and all minimum page counts.

---

## STEP 0 — Identify the Project

**Before writing a single line of code**, confirm which project the user wants:

| # | Project | Core entities |
|---|---------|---------------|
| 1 | CRM – Contacts | contacts, tags (M2M) |
| 2 | HelpDesk – Tickets | tickets, messages |
| 3 | Job Board | job_offers, applications |
| 4 | Bibliothèque | books, loans |
| 5 | Restaurant Booking | time_slots, reservations |
| 6 | Événements | events, registrations |
| 7 | Mini e-Commerce | products, orders, order_items |
| 8 | Gestion de Notes | modules, grades |
| 9 | Association | member_profiles, contributions |
| 10 | Quiz Platform | quizzes, questions, choices, attempts |

If the user has not specified a project number, ask them. Then load the matching section in
**## PROJECT SPECS** below before generating files.

---

## STEP 1 — Universal Architecture Blueprint

Every project MUST follow this folder structure exactly:

```
project_name/
├── public/
│   ├── index.php              ← landing / redirect to login
│   ├── login.php
│   ├── logout.php
│   ├── 403.php
│   ├── 404.php
│   ├── css/
│   │   └── style.css
│   ├── js/
│   │   └── animations.js      ← ALL GSAP code, commented
│   └── uploads/               ← secured upload folder
├── src/
│   ├── config/
│   │   └── db.php             ← MySQLi connection singleton
│   ├── helpers/
│   │   ├── auth.php           ← session helpers, role checks
│   │   ├── flash.php          ← flash message system
│   │   └── security.php       ← sanitize, validate, upload
│   └── templates/
│       ├── header.php
│       └── footer.php
├── [feature_folders]/         ← e.g. contacts/, tickets/, admin/
├── database.sql               ← full schema + demo seed data
└── README.md
```

---

## STEP 2 — Mandatory Foundation Files (generate for EVERY project)

### 2.1 — `src/config/db.php`

```php
<?php
// Database configuration — XAMPP/MAMP defaults
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');          // change if needed
define('DB_NAME', 'project_db');// replaced per project

function getDB(): mysqli {
    static $db = null;
    if ($db === null) {
        $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($db->connect_error) {
            die('DB connection failed: ' . $db->connect_error);
        }
        $db->set_charset('utf8mb4');
    }
    return $db;
}
```

### 2.2 — `src/helpers/auth.php`

```php
<?php
if (session_status() === PHP_SESSION_NONE) session_start();

function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

function requireLogin(): void {
    if (!isLoggedIn()) {
        header('Location: /login.php');
        exit;
    }
}

function requireAdmin(): void {
    requireLogin();
    if (!hasRole('ROLE_ADMIN')) {
        http_response_code(403);
        include __DIR__ . '/../../public/403.php';
        exit;
    }
}

function hasRole(string $role): bool {
    $roles = json_decode($_SESSION['roles'] ?? '[]', true);
    return in_array($role, $roles, true);
}

function currentUserId(): int {
    return (int)($_SESSION['user_id'] ?? 0);
}

/**
 * Owner-check: aborts with 403 if $ownerId !== current user AND not admin.
 */
function requireOwnerOrAdmin(int $ownerId): void {
    requireLogin();
    if ($ownerId !== currentUserId() && !hasRole('ROLE_ADMIN')) {
        http_response_code(403);
        include __DIR__ . '/../../public/403.php';
        exit;
    }
}
```

### 2.3 — `src/helpers/flash.php`

```php
<?php
if (session_status() === PHP_SESSION_NONE) session_start();

function flashSet(string $type, string $msg): void {
    $_SESSION['_flash'][$type][] = $msg;
}

function flashGet(): array {
    $flash = $_SESSION['_flash'] ?? [];
    unset($_SESSION['_flash']);
    return $flash;
}
```

### 2.4 — `src/helpers/security.php`

```php
<?php
/**
 * Validate and move an uploaded file safely.
 * @param array  $file          $_FILES['field']
 * @param array  $allowedMimes  e.g. ['image/jpeg','image/png']
 * @param array  $allowedExts   e.g. ['jpg','jpeg','png']
 * @param int    $maxBytes      e.g. 2 * 1024 * 1024
 * @param string $destination   absolute path to uploads dir
 * @return string  new filename on success
 * @throws RuntimeException on failure
 */
function secureUpload(
    array $file,
    array $allowedMimes,
    array $allowedExts,
    int $maxBytes,
    string $destination
): string {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Upload error code: ' . $file['error']);
    }
    if ($file['size'] > $maxBytes) {
        throw new RuntimeException('File too large (max ' . ($maxBytes / 1024 / 1024) . ' MB).');
    }
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime  = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    if (!in_array($mime, $allowedMimes, true)) {
        throw new RuntimeException('File type not allowed.');
    }
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExts, true)) {
        throw new RuntimeException('File extension not allowed.');
    }
    $newName = bin2hex(random_bytes(16)) . '.' . $ext;
    $dest    = rtrim($destination, '/') . '/' . $newName;
    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        throw new RuntimeException('Could not move uploaded file.');
    }
    return $newName;
}

function h(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
```

### 2.5 — `public/login.php` (universal template)

```php
<?php
require_once __DIR__ . '/../src/config/db.php';
require_once __DIR__ . '/../src/helpers/auth.php';
require_once __DIR__ . '/../src/helpers/flash.php';
require_once __DIR__ . '/../src/helpers/security.php';

if (isLoggedIn()) { header('Location: /'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($email === '' || $password === '') {
        $error = 'All fields are required.';
    } else {
        $db   = getDB();
        $stmt = $db->prepare('SELECT id, password_hash, roles_json, is_active FROM users WHERE email = ?');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if ($user && (int)$user['is_active'] === 1 && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email']   = $email;
            $_SESSION['roles']   = $user['roles_json'];
            header('Location: /');
            exit;
        }
        $error = 'Invalid credentials.';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Login</title>
  <link rel="stylesheet" href="/css/style.css">
</head>
<body class="login-page">
  <div class="login-card" id="loginCard">
    <h1>Sign In</h1>
    <?php if ($error): ?><p class="alert alert-error"><?= h($error) ?></p><?php endif; ?>
    <form method="POST" novalidate>
      <label>Email<input type="email" name="email" required autocomplete="email"></label>
      <label>Password<input type="password" name="password" required autocomplete="current-password"></label>
      <button type="submit">Login</button>
    </form>
  </div>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/1.11.1/TweenMax.min.js"></script>
  <script src="/js/animations.js"></script>
</body>
</html>
```

### 2.6 — `public/403.php`

```php
<?php http_response_code(403); ?>
<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8"><title>403 Forbidden</title>
<link rel="stylesheet" href="/css/style.css"></head>
<body class="error-page">
  <div class="error-card" id="errorCard">
    <h1>403</h1>
    <p>Access denied — you do not have permission to view this resource.</p>
    <a href="/">← Back to home</a>
  </div>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/1.11.1/TweenMax.min.js"></script>
  <script src="/js/animations.js"></script>
</body></html>
```

---

## STEP 3 — Security Rules (enforce on every page)

Apply these rules on **every** PHP page that processes data or serves protected content:

1. **Session start + auth check** at top of every protected page.
2. **MySQLi prepared statements only** — zero string interpolation in SQL.
   ```php
   // CORRECT
   $stmt = $db->prepare('SELECT * FROM contacts WHERE id = ? AND owner_id = ?');
   $stmt->bind_param('ii', $id, currentUserId());
   // WRONG — never do this
   $db->query("SELECT * FROM contacts WHERE id = $id");
   ```
3. **Output escaping**: always `h()` before echoing user data.
4. **CSRF token** on every state-changing form:
   ```php
   // Generate once per session
   if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
   // In form
   <input type="hidden" name="csrf" value="<?= h($_SESSION['csrf']) ?>">
   // On POST
   if (!hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? '')) { http_response_code(403); exit; }
   ```
5. **Owner-check** on every show/edit/delete route:
   ```php
   requireOwnerOrAdmin($resource['owner_id']);
   ```
6. **Upload security**: always call `secureUpload()` helper.
7. **Redirect after POST** (PRG pattern) to prevent double-submit.

---

## STEP 4 — GSAP Integration Rules

> GSAP version: **1.11.1** (via CDN) — use the exact CDN URL below.

```html
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/1.11.1/TweenMax.min.js"></script>
<script src="/js/animations.js"></script>
```

`TweenMax.min.js` from 1.11.1 includes: TweenLite, TweenMax, TimelineLite, TimelineMax, CSSPlugin,
EasePack, and most standard plugins in one file.

### `public/js/animations.js` — Template

```javascript
/**
 * animations.js — All GSAP animations for [PROJECT NAME]
 * GSAP v1.11.1 — TweenMax / TimelineMax
 *
 * Philosophy:
 *  - Progressive enhancement: all content visible without JS.
 *  - Non-blocking: never hide critical form fields or error messages permanently.
 *  - Reversible: transitions can be rewound where applicable.
 */

(function () {
  "use strict";

  // ─── Utility: check element exists before animating ───────────────────────
  function $(sel) { return document.querySelector(sel); }
  function $$(sel) { return Array.from(document.querySelectorAll(sel)); }

  // ─── 1. LOGIN PAGE — card fade-in ─────────────────────────────────────────
  var loginCard = $('#loginCard');
  if (loginCard) {
    TweenMax.from(loginCard, 0.6, { opacity: 0, y: -30, ease: Power2.easeOut });
  }

  // ─── 2. FLASH MESSAGES — stagger in, auto-dismiss ─────────────────────────
  var flashes = $$('.flash-message');
  if (flashes.length) {
    TweenMax.staggerFrom(flashes, 0.4, { opacity: 0, x: 40, ease: Back.easeOut }, 0.15);
    // Auto-dismiss after 4 s
    flashes.forEach(function (el) {
      TweenMax.to(el, 0.4, { delay: 4, opacity: 0, x: 40, onComplete: function () { el.remove(); } });
    });
  }

  // ─── 3. LIST ITEMS — stagger reveal ───────────────────────────────────────
  var listItems = $$('.list-item, .card');
  if (listItems.length) {
    TweenMax.staggerFrom(listItems, 0.5, { opacity: 0, y: 20, ease: Power1.easeOut }, 0.08);
  }

  // ─── 4. PAGE HEADER — slide down ──────────────────────────────────────────
  var pageHeader = $('#pageHeader');
  if (pageHeader) {
    TweenMax.from(pageHeader, 0.5, { opacity: 0, y: -20, ease: Power2.easeOut });
  }

  // ─── 5. FORM APPEAR — fade + scale ────────────────────────────────────────
  var mainForm = $('#mainForm');
  if (mainForm) {
    TweenMax.from(mainForm, 0.5, { opacity: 0, scale: 0.97, ease: Power2.easeOut });
  }

  // ─── 6. 403 / ERROR PAGE ──────────────────────────────────────────────────
  var errorCard = $('#errorCard');
  if (errorCard) {
    var tl = new TimelineMax();
    tl.from(errorCard, 0.4, { opacity: 0, scale: 0.8, ease: Back.easeOut })
      .from(errorCard.querySelector('h1'), 0.3, { opacity: 0, y: -10 }, '-=0.1');
  }

  // ─── PROJECT-SPECIFIC ANIMATIONS (inserted below per project) ─────────────

})();
```

### GSAP Minimum Requirements (per project — see PROJECT SPECS)

Each project has a **minimum of 3 distinct GSAP animation zones**:
1. A **timeline** on a key screen (dashboard, form, or detail page).
2. A **stagger** on a list (items, cards, rows).
3. A **user-action feedback** animation (confirmation, status change, toast).

---

## STEP 5 — Database SQL Template

Every `database.sql` must include:

```sql
-- ============================================================
-- Project: [NAME]  |  DB: project_db
-- Run this file once in phpMyAdmin or `mysql -u root < database.sql`
-- ============================================================

DROP DATABASE IF EXISTS project_db;
CREATE DATABASE project_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE project_db;

-- USERS TABLE (shared across all projects)
CREATE TABLE users (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  email         VARCHAR(255) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  roles_json    VARCHAR(255) NOT NULL DEFAULT '["ROLE_USER"]',
  is_active     TINYINT(1)   NOT NULL DEFAULT 1,
  created_at    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- [PROJECT-SPECIFIC TABLES — see PROJECT SPECS below]

-- DEMO ACCOUNTS
-- admin@test.com / admin123
-- user1@test.com / user123
-- user2@test.com / user123
INSERT INTO users (email, password_hash, roles_json) VALUES
  ('admin@test.com', '$2y$12$...', '["ROLE_ADMIN","ROLE_USER"]'),
  ('user1@test.com', '$2y$12$...', '["ROLE_USER"]'),
  ('user2@test.com', '$2y$12$...', '["ROLE_USER"]');
-- NOTE: Generate hashes with: php -r "echo password_hash('admin123', PASSWORD_BCRYPT, ['cost'=>12]);"
-- Then replace the $2y$12$... placeholders above.

-- DEMO DATA (at least 5 rows per main entity)
```

---

## STEP 6 — README Template

```markdown
# [Project Name]

## Setup
1. Copy folder to `htdocs/` (XAMPP) or `Sites/` (MAMP).
2. Import `database.sql` via phpMyAdmin or CLI.
3. Generate password hashes:
   ```
   php -r "echo password_hash('admin123', PASSWORD_BCRYPT, ['cost'=>12]);"
   php -r "echo password_hash('user123',  PASSWORD_BCRYPT, ['cost'=>12]);"
   ```
   Update the INSERT in database.sql and re-import.
4. Visit `http://localhost/[project]/public/`

## Test Accounts
| Email | Password | Role |
|-------|----------|------|
| admin@test.com | admin123 | ADMIN |
| user1@test.com | user123 | USER |
| user2@test.com | user123 | USER |

## Architecture
- **Language**: PHP 8.2+ native pages (.php)
- **Database**: MySQL via MySQLi (prepared statements only)
- **Auth**: Session-based, ROLE_USER / ROLE_ADMIN
- **Security**: Owner-check (403), CSRF, upload validation, XSS escaping
- **Animations**: GSAP 1.11.1 (TweenMax, TimelineMax) in /public/js/animations.js

## Security Choices
- All SQL queries use MySQLi prepared statements (zero interpolation).
- Uploaded files: MIME-type check + extension whitelist + random rename.
- Sessions regenerated on login. CSRF token on all POST forms.
- Owner-check: any resource fetch validates `owner_id = currentUserId()` or ROLE_ADMIN.

## GSAP Animation Choices
[Describe 3+ animation zones, plugins used, and UX rationale here]

## Known Limitations & Possible Improvements
- MailHog required for email features (configure SMTP in src/config/mail.php).
- No rate-limiting on login (improvement: add attempt counter in DB).
- [Add project-specific notes]
```

---

## PROJECT SPECS

Read the section matching the requested project number. Use ONLY that section's tables, routes, and
GSAP requirements alongside the universal foundation above.

---

### PROJECT 1 — CRM: Gestion de Contacts

**Tables:**
```sql
CREATE TABLE contacts (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  owner_id   INT UNSIGNED NOT NULL,
  name       VARCHAR(120) NOT NULL,
  email      VARCHAR(255),
  phone      VARCHAR(30),
  city       VARCHAR(100),
  company    VARCHAR(150),
  notes      TEXT,
  photo_path VARCHAR(255),
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE tags (
  id    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  label VARCHAR(60) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE contact_tag (
  contact_id INT UNSIGNED NOT NULL,
  tag_id     INT UNSIGNED NOT NULL,
  PRIMARY KEY (contact_id, tag_id),
  FOREIGN KEY (contact_id) REFERENCES contacts(id) ON DELETE CASCADE,
  FOREIGN KEY (tag_id)     REFERENCES tags(id)     ON DELETE CASCADE
) ENGINE=InnoDB;
```

**Required Pages (≥9):**
`login.php` | `index.php` (list+search+filter+sort) | `show.php` | `create.php` | `edit.php` |
`delete.php` (confirm form) | `email.php` | `admin/index.php` (all contacts + user mgmt) | `403.php`

**Upload:** `photo_path` — jpg/png/webp, max 2MB, stored in `/public/uploads/photos/`

**Search query:** `?q=&city=&tag=&sort=name`

**GSAP animations.js additions:**
```javascript
// CRM-SPECIFIC

// Dashboard timeline on /admin/index.php
var dashHeader = $('#dashHeader');
if (dashHeader) {
  var dashTl = new TimelineMax();
  dashTl.from(dashHeader, 0.5, { opacity: 0, y: -30, ease: Power3.easeOut })
        .staggerFrom($$('.stat-card'), 0.4, { opacity: 0, scale: 0.9 }, 0.1, '-=0.2');
}

// Filter bar animation when search is submitted
var filterBar = $('#filterBar');
if (filterBar) {
  TweenMax.from(filterBar, 0.4, { opacity: 0, x: -20, ease: Power2.easeOut });
}

// Contact photo reveal on show.php
var profilePhoto = $('#profilePhoto');
if (profilePhoto) {
  TweenMax.from(profilePhoto, 0.6, { opacity: 0, scale: 0.8, rotation: -5, ease: Back.easeOut });
}

// Email sent confirmation toast
var emailToast = $('#emailToast');
if (emailToast) {
  var toastTl = new TimelineMax();
  toastTl.from(emailToast, 0.3, { opacity: 0, y: 20, ease: Power2.easeOut })
         .to(emailToast, 0.3, { delay: 3, opacity: 0, y: -20 });
}
```

---

### PROJECT 2 — HelpDesk: Tickets de Support

**Tables:**
```sql
CREATE TABLE tickets (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  owner_id   INT UNSIGNED NOT NULL,
  title      VARCHAR(200) NOT NULL,
  category   ENUM('hardware','software','network','other') NOT NULL DEFAULT 'other',
  priority   ENUM('low','medium','high','critical')        NOT NULL DEFAULT 'medium',
  status     ENUM('OPEN','IN_PROGRESS','CLOSED')           NOT NULL DEFAULT 'OPEN',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE messages (
  id        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  ticket_id INT UNSIGNED NOT NULL,
  author_id INT UNSIGNED NOT NULL,
  content   TEXT NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,
  FOREIGN KEY (author_id) REFERENCES users(id)   ON DELETE CASCADE
) ENGINE=InnoDB;
```

**Required Pages (≥8):** `login.php` | `tickets/create.php` | `tickets/index.php` (my tickets + filters) |
`tickets/show.php` (detail + messages) | `tickets/message_add.php` | `admin/tickets/index.php` |
`admin/tickets/status.php` | `403.php`

**Business rules to enforce in PHP:**
- Ticket CLOSED → block `message_add.php` for USER (show warning, no form).
- Filter combos: `?status=&priority=&category=`

**GSAP animations.js additions:**
```javascript
// HelpDesk-SPECIFIC

// Ticket list stagger
TweenMax.staggerFrom($$('.ticket-row'), 0.4, { opacity: 0, x: -30, ease: Power2.easeOut }, 0.07);

// Ticket detail timeline (show.php)
var ticketDetail = $('#ticketDetail');
if (ticketDetail) {
  var detailTl = new TimelineMax();
  detailTl.from(ticketDetail, 0.5, { opacity: 0, y: 20, ease: Power3.easeOut })
          .staggerFrom($$('.message-bubble'), 0.3, { opacity: 0, x: 20 }, 0.1, '-=0.2');
}

// Status badge color pulse on change
var statusBadge = $('#statusBadge');
if (statusBadge) {
  TweenMax.from(statusBadge, 0.5, { scale: 0, ease: Back.easeOut(1.7) });
}

// Admin status change feedback
var statusFeedback = $('#statusFeedback');
if (statusFeedback) {
  TweenMax.from(statusFeedback, 0.4, { opacity: 0, y: -15, ease: Power2.easeOut });
}
```

---

### PROJECT 3 — Job Board: Offres et Candidatures

**Tables:**
```sql
CREATE TABLE job_offers (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title       VARCHAR(200) NOT NULL,
  company     VARCHAR(150) NOT NULL,
  city        VARCHAR(100),
  type        ENUM('CDI','CDD','stage','alternance','freelance') NOT NULL DEFAULT 'CDI',
  description TEXT NOT NULL,
  is_active   TINYINT(1) NOT NULL DEFAULT 1,
  created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE applications (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  offer_id   INT UNSIGNED NOT NULL,
  user_id    INT UNSIGNED NOT NULL,
  cv_path    VARCHAR(255) NOT NULL,
  cover_note TEXT,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY unique_apply (offer_id, user_id),
  FOREIGN KEY (offer_id) REFERENCES job_offers(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id)  REFERENCES users(id)      ON DELETE CASCADE
) ENGINE=InnoDB;
```

**Required Pages (≥9):** `login.php` | `offers/index.php` (list+search) | `offers/show.php` |
`offers/apply.php` (upload CV) | `offers/thanks.php` | `admin/offers/index.php` |
`admin/offers/create.php` + `edit.php` | `admin/applications.php` | `403.php`

**Upload:** CV — PDF only, max 5MB, `/public/uploads/cvs/`. Download restricted to ROLE_ADMIN.

**GSAP animations.js additions:**
```javascript
// JobBoard-SPECIFIC

// Offer cards stagger reveal
TweenMax.staggerFrom($$('.offer-card'), 0.5, { opacity: 0, y: 30, ease: Power2.easeOut }, 0.1);

// Apply form slide-in
var applyForm = $('#applyForm');
if (applyForm) {
  TweenMax.from(applyForm, 0.6, { opacity: 0, x: 40, ease: Power3.easeOut });
}

// Thank-you / confirmation sequence
var thanksPage = $('#thanksPage');
if (thanksPage) {
  var thanksTl = new TimelineMax();
  thanksTl.from('#checkIcon',   0.5, { scale: 0, rotation: -180, ease: Back.easeOut(2) })
          .from('#thanksTitle', 0.4, { opacity: 0, y: 20 }, '-=0.2')
          .from('#thanksText',  0.3, { opacity: 0 }, '-=0.1');
}
```

---

### PROJECT 4 — Bibliothèque: Livres et Emprunts

**Tables:**
```sql
CREATE TABLE books (
  id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title            VARCHAR(255) NOT NULL,
  author           VARCHAR(150) NOT NULL,
  isbn             VARCHAR(20),
  genre            VARCHAR(80),
  cover_path       VARCHAR(255),
  available_copies INT UNSIGNED NOT NULL DEFAULT 1,
  created_at       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE loans (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  book_id     INT UNSIGNED NOT NULL,
  user_id     INT UNSIGNED NOT NULL,
  loan_date   DATE NOT NULL,
  due_date    DATE NOT NULL,   -- loan_date + 14 days
  return_date DATE,            -- NULL = still borrowed
  FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;
```

**Required Pages (≥8):** `login.php` | `books/index.php` (catalog + search) | `books/show.php` |
`books/borrow.php` | `loans/index.php` (my loans) | `loans/return.php` | `admin/books/index.php` |
`admin/books/create.php` + `edit.php` | `403.php`

**Business rules:** max 3 active loans per user | available_copies must not go negative |
return_date = NULL means active | due_date overdue → highlight in red.

**GSAP animations.js additions:**
```javascript
// Library-SPECIFIC

// Book cards progressive reveal
TweenMax.staggerFrom($$('.book-card'), 0.45, { opacity: 0, y: 25, ease: Power1.easeOut }, 0.06);

// Availability badge animate
$$('.availability-badge').forEach(function (badge) {
  TweenMax.from(badge, 0.4, { scale: 0, ease: Back.easeOut(1.5) });
});

// Borrow confirmation feedback
var borrowFeedback = $('#borrowFeedback');
if (borrowFeedback) {
  var borrowTl = new TimelineMax();
  borrowTl.from(borrowFeedback, 0.4, { opacity: 0, scale: 0.85, ease: Power2.easeOut })
          .from('#dueDate',     0.3, { opacity: 0, y: 10 }, '-=0.1');
}

// Overdue loans highlight pulse
$$('.loan-overdue').forEach(function (el) {
  TweenMax.to(el, 0.8, { backgroundColor: '#ffe0e0', repeat: -1, yoyo: true, ease: Power1.easeInOut });
});
```

---

### PROJECT 5 — Restaurant Booking: Réservations

**Tables:**
```sql
CREATE TABLE time_slots (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  label        VARCHAR(80) NOT NULL,
  start_time   TIME NOT NULL,
  end_time     TIME NOT NULL,
  capacity_max INT UNSIGNED NOT NULL DEFAULT 30,
  is_active    TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE reservations (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id    INT UNSIGNED NOT NULL,
  slot_id    INT UNSIGNED NOT NULL,
  date       DATE NOT NULL,
  guests     TINYINT UNSIGNED NOT NULL,
  phone      VARCHAR(30) NOT NULL,
  notes      TEXT,
  status     ENUM('CONFIRMED','CANCELLED') NOT NULL DEFAULT 'CONFIRMED',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id)      ON DELETE CASCADE,
  FOREIGN KEY (slot_id) REFERENCES time_slots(id) ON DELETE RESTRICT
) ENGINE=InnoDB;
```

**Required Pages (≥8):** `login.php` | `reservations/create.php` | `reservations/thanks.php` |
`reservations/index.php` | `reservations/edit.php` | `reservations/cancel.php` |
`admin/reservations/index.php` | `admin/slots/index.php` | `403.php`

**Capacity check query (use prepared stmt):**
```sql
SELECT COALESCE(SUM(guests),0) AS total_guests
FROM reservations
WHERE slot_id = ? AND date = ? AND status = 'CONFIRMED'
```
If `total_guests + new_guests > capacity_max` → reject with flash error.

**GSAP animations.js additions:**
```javascript
// Restaurant-SPECIFIC

// Reservation form timeline
var resForm = $('#reservationForm');
if (resForm) {
  var resTl = new TimelineMax();
  resTl.from('#dateField',  0.4, { opacity: 0, x: -20, ease: Power2.easeOut })
       .from('#slotField',  0.4, { opacity: 0, x: -20 }, '-=0.2')
       .from('#guestField', 0.4, { opacity: 0, x: -20 }, '-=0.2')
       .from('#submitBtn',  0.3, { opacity: 0, scale: 0.9, ease: Back.easeOut }, '-=0.1');
}

// Capacity progress bar (animated counter)
var capacityBar = $('#capacityBar');
if (capacityBar) {
  var pct = parseFloat(capacityBar.dataset.pct || 0);
  TweenMax.to(capacityBar, 1.2, { width: pct + '%', ease: Power2.easeOut });
}

// Confirmation page
var confirmPage = $('#confirmPage');
if (confirmPage) {
  var confTl = new TimelineMax();
  confTl.from('#confIcon',  0.5, { scale: 0, ease: Back.easeOut(2) })
        .staggerFrom($$('.conf-detail'), 0.3, { opacity: 0, y: 10 }, 0.1, '-=0.2');
}
```

---

### PROJECT 6 — Événements: Events et Inscriptions

**Tables:**
```sql
CREATE TABLE events (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title       VARCHAR(200) NOT NULL,
  description TEXT,
  location    VARCHAR(200),
  event_date  DATETIME NOT NULL,
  capacity    INT UNSIGNED NOT NULL DEFAULT 50,
  is_active   TINYINT(1) NOT NULL DEFAULT 1,
  created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE registrations (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  event_id   INT UNSIGNED NOT NULL,
  user_id    INT UNSIGNED NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY unique_reg (event_id, user_id),
  FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id)  REFERENCES users(id)  ON DELETE CASCADE
) ENGINE=InnoDB;
```

**Required Pages (≥7):** `login.php` | `events/index.php` | `events/show.php` | `events/register.php` |
`events/my.php` | `admin/events/index.php` | `admin/events/create.php` + `edit.php` | `403.php`

**GSAP animations.js additions:**
```javascript
// Events-SPECIFIC

// Event cards reveal
TweenMax.staggerFrom($$('.event-card'), 0.5, { opacity: 0, y: 30, scale: 0.95, ease: Power2.easeOut }, 0.1);

// Capacity gauge animated fill
$$('.capacity-gauge').forEach(function (gauge) {
  var filled  = parseInt(gauge.dataset.filled  || 0);
  var total   = parseInt(gauge.dataset.total   || 1);
  var bar     = gauge.querySelector('.gauge-fill');
  TweenMax.to(bar, 1.0, { width: (filled / total * 100) + '%', ease: Power2.easeOut });
  // Counter animation
  var counter = gauge.querySelector('.gauge-count');
  if (counter) {
    var obj = { val: 0 };
    TweenMax.to(obj, 1.0, { val: filled, ease: Power2.easeOut,
      onUpdate: function () { counter.textContent = Math.round(obj.val); }
    });
  }
});

// Registration confirmation ticket
var ticketCard = $('#ticketCard');
if (ticketCard) {
  var tickTl = new TimelineMax();
  tickTl.from(ticketCard,          0.5, { opacity: 0, y: 40, ease: Power3.easeOut })
        .from('#ticketBadge',      0.4, { scale: 0, rotation: -15, ease: Back.easeOut(2) }, '-=0.2')
        .staggerFrom($$('.ticket-line'), 0.2, { opacity: 0, x: 10 }, 0.08, '-=0.1');
}
```

---

### PROJECT 7 — Mini e-Commerce: Catalogue et Panier

**Tables:**
```sql
CREATE TABLE products (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name        VARCHAR(200) NOT NULL,
  description TEXT,
  price       DECIMAL(10,2) UNSIGNED NOT NULL,
  stock       INT UNSIGNED NOT NULL DEFAULT 0,
  image_path  VARCHAR(255),
  is_active   TINYINT(1) NOT NULL DEFAULT 1,
  created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE orders (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id    INT UNSIGNED NOT NULL,
  total      DECIMAL(10,2) UNSIGNED NOT NULL DEFAULT 0.00,
  status     ENUM('PENDING','CONFIRMED','SHIPPED','CANCELLED') NOT NULL DEFAULT 'PENDING',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE order_items (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  order_id   INT UNSIGNED NOT NULL,
  product_id INT UNSIGNED NOT NULL,
  quantity   INT UNSIGNED NOT NULL DEFAULT 1,
  unit_price DECIMAL(10,2) UNSIGNED NOT NULL,
  FOREIGN KEY (order_id)   REFERENCES orders(id)   ON DELETE CASCADE,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT
) ENGINE=InnoDB;
```

**Required Pages (≥8):** `login.php` | `products/index.php` | `products/show.php` | `cart/index.php` |
`checkout.php` | `orders/thanks.php` | `admin/products/index.php` | `admin/products/create.php` +
`edit.php` | `403.php`

**Cart:** stored in `$_SESSION['cart']` as `[product_id => qty]`. Total always recalculated server-side.

**GSAP animations.js additions:**
```javascript
// eCommerce-SPECIFIC

// Product grid stagger
TweenMax.staggerFrom($$('.product-card'), 0.45, { opacity: 0, y: 25, ease: Power2.easeOut }, 0.07);

// Add to cart: product flies to cart icon (simplified feedback)
var cartFeedback = $('#cartFeedback');
if (cartFeedback) {
  TweenMax.from(cartFeedback, 0.4, { scale: 0, ease: Back.easeOut(2) });
  TweenMax.to(cartFeedback, 0.3, { delay: 2, opacity: 0 });
}

// Cart item list
TweenMax.staggerFrom($$('.cart-item'), 0.4, { opacity: 0, x: -20, ease: Power1.easeOut }, 0.08);

// Checkout confirmation timeline
var orderThanks = $('#orderThanks');
if (orderThanks) {
  var orderTl = new TimelineMax();
  orderTl.from('#orderIcon',   0.5, { scale: 0, ease: Back.easeOut(2) })
         .from('#orderTitle',  0.4, { opacity: 0, y: 15 }, '-=0.2')
         .staggerFrom($$('.order-summary-row'), 0.25, { opacity: 0, x: 10 }, 0.07, '-=0.1');
}
```

---

### PROJECT 8 — Gestion de Notes: Étudiants, Modules et Moyennes

**Tables:**
```sql
CREATE TABLE modules (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name       VARCHAR(150) NOT NULL,
  coef       DECIMAL(4,2) UNSIGNED NOT NULL DEFAULT 1.00,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE grades (
  id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  student_user_id INT UNSIGNED NOT NULL,
  module_id       INT UNSIGNED NOT NULL,
  value           DECIMAL(5,2) NOT NULL,  -- 0.00 to 20.00
  created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY unique_grade (student_user_id, module_id),
  FOREIGN KEY (student_user_id) REFERENCES users(id)    ON DELETE CASCADE,
  FOREIGN KEY (module_id)       REFERENCES modules(id)  ON DELETE CASCADE,
  CONSTRAINT chk_grade CHECK (value >= 0 AND value <= 20)
) ENGINE=InnoDB;
```

**Required Pages (≥7):** `login.php` | `admin/modules/index.php` | `admin/modules/create.php` +
`edit.php` | `admin/grades/input.php` | `admin/grades/list.php` | `me/grades.php` | `403.php`

**Weighted average query:**
```sql
SELECT SUM(g.value * m.coef) / SUM(m.coef) AS weighted_avg
FROM grades g
JOIN modules m ON m.id = g.module_id
WHERE g.student_user_id = ?
```

**Mention logic (PHP):**
```php
function getMention(float $avg): string {
    return match(true) {
        $avg >= 16 => 'Très Bien',
        $avg >= 14 => 'Bien',
        $avg >= 12 => 'Assez Bien',
        $avg >= 10 => 'Passable',
        default    => 'Insuffisant',
    };
}
```

**GSAP animations.js additions:**
```javascript
// Grades-SPECIFIC

// Modules/grades stagger
TweenMax.staggerFrom($$('.module-row, .grade-row'), 0.4, { opacity: 0, x: -20, ease: Power2.easeOut }, 0.07);

// Weighted average counter
var avgDisplay = $('#averageDisplay');
if (avgDisplay) {
  var finalAvg = parseFloat(avgDisplay.dataset.avg || 0);
  var obj = { val: 0 };
  TweenMax.to(obj, 1.5, {
    val: finalAvg, ease: Power2.easeOut,
    onUpdate: function () { avgDisplay.textContent = obj.val.toFixed(2); }
  });
}

// Mention reveal with scale
var mentionBadge = $('#mentionBadge');
if (mentionBadge) {
  TweenMax.from(mentionBadge, 0.6, { scale: 0, rotation: -10, ease: Back.easeOut(2), delay: 1.6 });
}
```

---

### PROJECT 9 — Association: Cotisations et Newsletter

**Tables:**
```sql
CREATE TABLE member_profiles (
  user_id    INT UNSIGNED NOT NULL PRIMARY KEY,
  first_name VARCHAR(80),
  last_name  VARCHAR(80),
  phone      VARCHAR(30),
  joined_at  DATE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE contributions (
  id      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  year    YEAR NOT NULL,
  paid    TINYINT(1) NOT NULL DEFAULT 0,
  paid_at TIMESTAMP NULL,
  UNIQUE KEY unique_contrib (user_id, year),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE newsletter_logs (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  sent_by    INT UNSIGNED NOT NULL,
  target     ENUM('all','paid','unpaid') NOT NULL,
  year       YEAR NOT NULL,
  sent_count INT UNSIGNED NOT NULL DEFAULT 0,
  sent_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (sent_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;
```

**Required Pages (≥5):** `login.php` | `admin/members.php` (list+filter paid/unpaid+year) |
`admin/contributions.php` (edit payment status) | `me/contribution.php` | `403.php`

**GSAP animations.js additions:**
```javascript
// Association-SPECIFIC

// Member list rows stagger
TweenMax.staggerFrom($$('.member-row'), 0.35, { opacity: 0, x: -15, ease: Power1.easeOut }, 0.05);

// Paid/unpaid status badge flip
$$('.status-badge').forEach(function (badge) {
  TweenMax.from(badge, 0.4, { scale: 0, ease: Back.easeOut(1.5) });
});

// Filter transition
var filterResult = $('#filterResult');
if (filterResult) {
  TweenMax.from(filterResult, 0.5, { opacity: 0, y: 15, ease: Power2.easeOut });
}

// Newsletter send result
var newsletterResult = $('#newsletterResult');
if (newsletterResult) {
  var nlTl = new TimelineMax();
  nlTl.from(newsletterResult, 0.4, { opacity: 0, scale: 0.9, ease: Power2.easeOut })
      .from('#sentCount',     0.5, { opacity: 0 }, '-=0.2');
  // Count up animation
  var countEl = document.getElementById('sentCount');
  if (countEl) {
    var target = parseInt(countEl.dataset.count || 0);
    var obj    = { val: 0 };
    TweenMax.to(obj, 1.0, { val: target, ease: Power2.easeOut,
      onUpdate: function () { countEl.textContent = Math.round(obj.val); }
    });
  }
}
```

---

### PROJECT 10 — Quiz Platform: QCM et Scores

**Tables:**
```sql
CREATE TABLE quizzes (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title       VARCHAR(200) NOT NULL,
  description TEXT,
  is_active   TINYINT(1) NOT NULL DEFAULT 1,
  created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE questions (
  id       INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  quiz_id  INT UNSIGNED NOT NULL,
  text     TEXT NOT NULL,
  position TINYINT UNSIGNED NOT NULL DEFAULT 1,
  FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE choices (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  question_id INT UNSIGNED NOT NULL,
  text        VARCHAR(255) NOT NULL,
  is_correct  TINYINT(1) NOT NULL DEFAULT 0,
  FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE attempts (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  quiz_id    INT UNSIGNED NOT NULL,
  user_id    INT UNSIGNED NOT NULL,
  score      INT UNSIGNED NOT NULL DEFAULT 0,
  max_score  INT UNSIGNED NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id)   ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE attempt_answers (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  attempt_id  INT UNSIGNED NOT NULL,
  question_id INT UNSIGNED NOT NULL,
  choice_id   INT UNSIGNED NOT NULL,
  is_correct  TINYINT(1) NOT NULL DEFAULT 0,
  FOREIGN KEY (attempt_id)  REFERENCES attempts(id)  ON DELETE CASCADE,
  FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE,
  FOREIGN KEY (choice_id)   REFERENCES choices(id)   ON DELETE CASCADE
) ENGINE=InnoDB;
```

**Required Pages (≥8):** `login.php` | `quizzes/index.php` | `quizzes/start.php` |
`quizzes/submit.php` (result) | `quizzes/correction.php` | `quizzes/history.php` |
`admin/quizzes/index.php` + `create.php` + `edit.php` | `403.php`

**GSAP animations.js additions:**
```javascript
// Quiz-SPECIFIC

// Question transition (on JS-driven quiz progression)
function showQuestion(el) {
  TweenMax.from(el, 0.4, { opacity: 0, x: 40, ease: Power2.easeOut });
}

// Progress bar
var progressBar = $('#quizProgress');
if (progressBar) {
  var pct = parseFloat(progressBar.dataset.pct || 0);
  TweenMax.to(progressBar, 0.6, { width: pct + '%', ease: Power2.easeOut });
}

// Score reveal timeline
var scoreCard = $('#scoreCard');
if (scoreCard) {
  var scoreTl = new TimelineMax();
  scoreTl.from(scoreCard, 0.5, { opacity: 0, scale: 0.85, ease: Power3.easeOut })
         .from('#scoreValue', 0.1, { opacity: 0 }, '-=0.2');
  // Animated score counter
  var scoreEl = document.getElementById('scoreValue');
  if (scoreEl) {
    var finalScore = parseInt(scoreEl.dataset.score || 0);
    var obj = { val: 0 };
    TweenMax.to(obj, 1.2, {
      val: finalScore, ease: Power2.easeOut,
      onUpdate: function () { scoreEl.textContent = Math.round(obj.val); }
    });
  }
}

// Correction items stagger
TweenMax.staggerFrom($$('.correction-item'), 0.35, { opacity: 0, y: 15, ease: Power1.easeOut }, 0.08);

// History rows
TweenMax.staggerFrom($$('.attempt-row'), 0.3, { opacity: 0, x: -10, ease: Power1.easeOut }, 0.06);
```

---

## STEP 7 — Email (MailHog) Setup

Every project that requires email uses this shared mailer. Create `src/config/mail.php`:

```php
<?php
/**
 * Send email via MailHog (SMTP localhost:1025, no auth)
 * Uses PHP's built-in mail() configured via php.ini SMTP setting,
 * OR directly via fsockopen for reliability.
 */
function sendMail(string $to, string $subject, string $body): bool {
    $from    = 'noreply@myapp.local';
    $headers = "From: $from\r\nContent-Type: text/html; charset=UTF-8\r\n";
    // Configure php.ini: SMTP=localhost, smtp_port=1025
    return mail($to, $subject, $body, $headers);
}
```

In `php.ini` (XAMPP): `SMTP=localhost` and `smtp_port=1025`. MailHog listens on port 1025.

---

## STEP 8 — Code Generation Order

When building the project, generate files in this order to avoid dependency issues:

1. `database.sql` (schema + seed data + password hash placeholders)
2. `src/config/db.php`
3. `src/helpers/auth.php`, `flash.php`, `security.php`
4. `src/config/mail.php`
5. `src/templates/header.php`, `footer.php`
6. `public/css/style.css` (clean, minimal, responsive)
7. `public/login.php`, `public/logout.php`, `public/403.php`, `public/404.php`
8. `public/index.php` (dashboard / home redirect)
9. All USER-facing feature pages (list, show, create, edit, delete)
10. All ADMIN pages (`admin/` subfolder)
11. `public/js/animations.js` (universal + project-specific sections)
12. `README.md`

---

## STEP 9 — Final Checklist Before Delivering

Run through this before outputting any code:

- [ ] ≥ 8 PHP screen pages (including login + 403)
- [ ] ROLE_USER and ROLE_ADMIN properly separated
- [ ] Owner-check (`requireOwnerOrAdmin`) on every resource page
- [ ] ALL SQL queries use `$stmt->prepare()` + `bind_param()` — zero string interpolation
- [ ] CSRF token on every POST form
- [ ] `h()` escaping on every `echo` of user data
- [ ] Upload validation: MIME check + extension whitelist + rename + size limit
- [ ] PRG (redirect after POST) on all state-changing forms
- [ ] GSAP v1.11.1 CDN included on every page with `<script>`
- [ ] `animations.js` contains universal + project-specific sections, all commented
- [ ] At least 3 distinct GSAP animation zones (timeline, stagger, action feedback)
- [ ] `database.sql` creates DB, all tables, demo users, and demo data
- [ ] `README.md` with setup, test accounts, architecture, GSAP paragraph
- [ ] Graceful degradation: all content readable with JavaScript disabled

---

## NOTES FOR THE AI BUILDING THIS PROJECT

- **Never interpolate variables into SQL strings.** If you catch yourself writing `"... WHERE id = $id"`, stop and rewrite with prepared statements.
- **Always call `requireLogin()` or `requireAdmin()` as the first line** of every protected page, before any HTML output.
- **The `403.php` page must set `http_response_code(403)`** — the spec requires a capture of a real 403 response.
- **GSAP 1.11.1 API syntax**: use `TweenMax.from()`, `TweenMax.to()`, `new TimelineMax()`, `.staggerFrom()`. Do NOT use GSAP 3.x syntax (`gsap.to()`, `gsap.timeline()`) — the version is locked at **1.11.1**.
- **Generate realistic demo data**: at least 5 rows per main entity, spread across the 3 test users.
- **The `password_hash` placeholders** in `database.sql` should include a comment showing the PHP command to generate them, since hashes can't be pre-computed without knowing the salt.
- **Keep PHP pages self-contained**: each `.php` file handles its own includes, auth checks, DB logic, and HTML output — no MVC framework, pure native PHP as required.