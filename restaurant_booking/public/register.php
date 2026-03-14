<?php
require_once __DIR__ . '/../src/config/db.php';
require_once __DIR__ . '/../src/helpers/auth.php';
require_once __DIR__ . '/../src/helpers/flash.php';
require_once __DIR__ . '/../src/helpers/security.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: /restaurant_booking/public/index.php');
    exit;
}

$errors = [];
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrfValidate();

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Basic Validation
    if (empty($email)) {
        $errors[] = "L'adresse email est requise.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format d'email invalide.";
    }

    if (empty($password)) {
        $errors[] = "Le mot de passe est requis.";
    } elseif (strlen($password) < 6) {
        $errors[] = "Le mot de passe doit faire au moins 6 caractères.";
    }

    if ($password !== $confirm_password) {
        $errors[] = "Les mots de passe ne correspondent pas.";
    }

    if (empty($errors)) {
        $db = getDB();

        // Check if email already exists
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $errors[] = "Cet email est déjà utilisé.";
        }
        $stmt->close();

        if (empty($errors)) {
            // Create user
            $hashed_password = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
            $roles = '["ROLE_USER"]';

            $stmt = $db->prepare("INSERT INTO users (email, password_hash, roles_json) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $email, $hashed_password, $roles);

            if ($stmt->execute()) {
                $userId = $stmt->insert_id;
                $stmt->close();

                // Log the user in automatically (match login.php)
                $_SESSION['user_id'] = $userId;
                $_SESSION['email']   = $email;
                $_SESSION['roles']   = $roles;
                session_regenerate_id(true);

                flashSet('success', 'Bienvenue ! Votre compte a été créé avec succès.');
                header('Location: /restaurant_booking/public/index.php');
                exit;
            } else {
                $errors[] = "Une erreur est survenue lors de la création du compte.";
                $stmt->close();
            }
        }
    }
}

$pageTitle = 'Inscription — La Table d\'Or';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($pageTitle) ?></title>
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
            <p class="login-subtitle">Rejoignez-nous pour vos prochaines saveurs</p>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error" id="statusFeedback" style="margin-bottom: 1.5rem;">
                <ul style="margin: 0; padding-left: 1.2rem; text-align: left;">
                    <?php foreach ($errors as $error): ?>
                        <li><?= h($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" novalidate id="mainForm">
            <input type="hidden" name="csrf" value="<?= h(csrfToken()) ?>">

            <label for="email">Adresse Email</label>
            <input type="email" id="email" name="email" value="<?= h($email) ?>" required placeholder="votre@email.com">

            <label for="password">Mot de passe</label>
            <input type="password" id="password" name="password" required placeholder="••••••••">

            <label for="confirm_password">Confirmer le mot de passe</label>
            <input type="password" id="confirm_password" name="confirm_password" required placeholder="••••••••">

            <button type="submit" style="margin-top: 1rem;">S'inscrire</button>
        </form>

        <div style="margin-top: 1.5rem; text-align: center; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 1rem;">
            <p style="color: var(--text-muted); font-size: 0.9rem;">
                Déjà un compte ? <a href="/restaurant_booking/public/login.php" style="color: var(--accent); text-decoration: none; font-weight: 600;">Se connecter</a>
            </p>
        </div>
    </div>

    <!-- GSAP for Animations -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/1.11.1/TweenMax.min.js"></script>
    <script src="/restaurant_booking/public/js/animations.js"></script>
</body>
</html>
