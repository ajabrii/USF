<?php
require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../helpers/flash.php';
require_once __DIR__ . '/../helpers/security.php';

$_pageCss  = $pageCss  ?? '';
$_pageTitle = $pageTitle ?? 'Restaurant Booking';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Restaurant Booking — Réservation de tables en ligne">
  <title><?= h($_pageTitle) ?> — La Table d'Or</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/restaurant_booking/public/css/style.css">
  <?php if ($_pageCss): ?>
  <style><?= $_pageCss ?></style>
  <?php endif; ?>
</head>
<body>

  <!-- ─── NAVIGATION ──────────────────────────────────────── -->
  <nav class="navbar" id="pageHeader">
    <div class="nav-inner">
      <a href="/restaurant_booking/public/" class="nav-brand">
        <span class="brand-icon">🍽</span> La Table d'Or
      </a>
      <?php if (isLoggedIn()): ?>
      <div class="nav-links">
        <a href="/restaurant_booking/public/">Accueil</a>
        <a href="/restaurant_booking/reservations/create.php">Réserver</a>
        <a href="/restaurant_booking/reservations/index.php">Mes Réservations</a>
        <?php if (hasRole('ROLE_ADMIN')): ?>
        <a href="/restaurant_booking/admin/reservations/index.php">Admin Réservations</a>
        <a href="/restaurant_booking/admin/slots/index.php">Créneaux</a>
        <?php endif; ?>
        <a href="/restaurant_booking/public/logout.php" class="nav-logout">Déconnexion</a>
      </div>
      <button class="nav-toggle" id="navToggle" aria-label="Menu">
        <span></span><span></span><span></span>
      </button>
      <?php endif; ?>
    </div>
  </nav>

  <!-- ─── FLASH MESSAGES ──────────────────────────────────── -->
  <?php $flashes = flashGet(); if (!empty($flashes)): ?>
  <div class="flash-container">
    <?php foreach ($flashes as $type => $messages): ?>
      <?php foreach ($messages as $msg): ?>
        <div class="flash-message flash-<?= h($type) ?>">
          <?= h($msg) ?>
          <button class="flash-close" onclick="this.parentElement.remove()" aria-label="Close">&times;</button>
        </div>
      <?php endforeach; ?>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <!-- ─── MAIN CONTENT ────────────────────────────────────── -->
  <main class="main-content">
