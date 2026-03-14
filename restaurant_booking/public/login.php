<?php
require_once __DIR__ . '/../src/config/db.php';
require_once __DIR__ . '/../src/helpers/auth.php';
require_once __DIR__ . '/../src/helpers/flash.php';
require_once __DIR__ . '/../src/helpers/security.php';

if (isLoggedIn()) { header('Location: /restaurant_booking/public/'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($email === '' || $password === '') {
        $error = 'Tous les champs sont obligatoires.';
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
            header('Location: /restaurant_booking/public/');
            exit;
        }
        $error = 'Identifiants incorrects.';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Connexion — La Table d'Or</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/restaurant_booking/public/css/style.css">
</head>
<body class="login-page">
  <div class="login-card" id="loginCard">
    <div class="brand-header">
      <span class="brand-icon">🍽</span>
      <h1>La Table d'Or</h1>
      <p class="login-subtitle">Connectez-vous pour réserver</p>
    </div>
    <?php if ($error): ?><p class="alert alert-error"><?= h($error) ?></p><?php endif; ?>
    <form method="POST" novalidate>
      <label for="email">Email</label>
      <input type="email" id="email" name="email" required autocomplete="email" placeholder="votre@email.com" value="<?= h($email ?? '') ?>">
      <label for="password">Mot de passe</label>
      <input type="password" id="password" name="password" required autocomplete="current-password" placeholder="••••••••">
      <button type="submit">Se connecter</button>
    </form>
  </div>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/1.11.1/TweenMax.min.js"></script>
  <script src="/restaurant_booking/public/js/animations.js"></script>
</body>
</html>
