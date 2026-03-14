<?php
require_once __DIR__ . '/../src/config/db.php';
require_once __DIR__ . '/../src/helpers/auth.php';
require_once __DIR__ . '/../src/helpers/flash.php';
require_once __DIR__ . '/../src/helpers/security.php';

if (!isLoggedIn()) {
    header('Location: /restaurant_booking/public/login.php');
    exit;
}

$db     = getDB();
$userId = currentUserId();
$isAdmin = hasRole('ROLE_ADMIN');

// Upcoming reservations for current user
$stmt = $db->prepare('
    SELECT r.*, ts.label AS slot_label, ts.start_time, ts.end_time
    FROM reservations r
    JOIN time_slots ts ON ts.id = r.slot_id
    WHERE r.user_id = ? AND r.date >= CURDATE() AND r.status = "CONFIRMED"
    ORDER BY r.date ASC, ts.start_time ASC
    LIMIT 5
');
$stmt->bind_param('i', $userId);
$stmt->execute();
$upcoming = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Admin stats
if ($isAdmin) {
    $todayCount = $db->query("SELECT COUNT(*) AS c FROM reservations WHERE date = CURDATE() AND status = 'CONFIRMED'")->fetch_assoc()['c'];
    $todayGuests = $db->query("SELECT COALESCE(SUM(guests),0) AS g FROM reservations WHERE date = CURDATE() AND status = 'CONFIRMED'")->fetch_assoc()['g'];
    $totalSlots = $db->query("SELECT COUNT(*) AS c FROM time_slots WHERE is_active = 1")->fetch_assoc()['c'];
    $totalUsers = $db->query("SELECT COUNT(*) AS c FROM users")->fetch_assoc()['c'];
}

$pageTitle = 'Accueil';
include __DIR__ . '/../src/templates/header.php';
?>

<div class="welcome-section" id="dashHeader">
  <h2>Bienvenue, <?= h($_SESSION['email']) ?> 👋</h2>
  <p>Gérez vos réservations et découvrez nos créneaux disponibles.</p>
  <div class="quick-actions">
    <a href="/restaurant_booking/reservations/create.php" class="btn btn-primary">🍽 Nouvelle réservation</a>
    <a href="/restaurant_booking/reservations/index.php" class="btn btn-secondary">📋 Mes réservations</a>
  </div>
</div>

<?php if ($isAdmin): ?>
<!-- ─── ADMIN STATS ──────────────────────────────────────── -->
<div class="stats-grid">
  <div class="stat-card card">
    <div class="stat-value"><?= (int)$todayCount ?></div>
    <div class="stat-label">Réservations aujourd'hui</div>
  </div>
  <div class="stat-card card">
    <div class="stat-value"><?= (int)$todayGuests ?></div>
    <div class="stat-label">Couverts aujourd'hui</div>
  </div>
  <div class="stat-card card">
    <div class="stat-value"><?= (int)$totalSlots ?></div>
    <div class="stat-label">Créneaux actifs</div>
  </div>
  <div class="stat-card card">
    <div class="stat-value"><?= (int)$totalUsers ?></div>
    <div class="stat-label">Utilisateurs</div>
  </div>
</div>
<?php endif; ?>

<!-- ─── UPCOMING RESERVATIONS ────────────────────────────── -->
<div class="page-header">
  <h1>Prochaines réservations</h1>
</div>

<?php if (empty($upcoming)): ?>
<div class="empty-state">
  <div class="empty-icon">📅</div>
  <h3>Aucune réservation à venir</h3>
  <p>Réservez votre prochaine table en quelques clics.</p>
  <a href="/restaurant_booking/reservations/create.php" class="btn btn-primary">Réserver maintenant</a>
</div>
<?php else: ?>
<div class="table-wrapper">
  <table>
    <thead>
      <tr>
        <th>Date</th>
        <th>Créneau</th>
        <th>Horaire</th>
        <th>Couverts</th>
        <th>Statut</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($upcoming as $r): ?>
      <tr class="list-item">
        <td><?= h($r['date']) ?></td>
        <td><?= h($r['slot_label']) ?></td>
        <td><?= h(substr($r['start_time'],0,5)) ?> – <?= h(substr($r['end_time'],0,5)) ?></td>
        <td><?= (int)$r['guests'] ?></td>
        <td><span class="badge badge-confirmed">Confirmée</span></td>
        <td>
          <div class="gap-row">
            <a href="/restaurant_booking/reservations/edit.php?id=<?= (int)$r['id'] ?>" class="btn btn-sm btn-secondary">Modifier</a>
            <a href="/restaurant_booking/reservations/cancel.php?id=<?= (int)$r['id'] ?>" class="btn btn-sm btn-danger">Annuler</a>
          </div>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php endif; ?>

<?php include __DIR__ . '/../src/templates/footer.php'; ?>
