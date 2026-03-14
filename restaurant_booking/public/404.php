<?php http_response_code(404); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>404 — Page introuvable</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/restaurant_booking/public/css/style.css">
</head>
<body class="error-page">
  <div class="error-card" id="errorCard">
    <h1>404</h1>
    <p>La page que vous cherchez n'existe pas ou a été déplacée.</p>
    <a href="/restaurant_booking/public/">← Retour à l'accueil</a>
  </div>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/1.11.1/TweenMax.min.js"></script>
  <script src="/restaurant_booking/public/js/animations.js"></script>
</body>
</html>
