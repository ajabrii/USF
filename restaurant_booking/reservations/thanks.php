<?php
require_once __DIR__ . '/../src/config/db.php';
require_once __DIR__ . '/../src/helpers/auth.php';
require_once __DIR__ . '/../src/helpers/flash.php';
require_once __DIR__ . '/../src/helpers/security.php';

requireLogin();

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: /restaurant_booking/reservations/index.php');
    exit;
}

$db   = getDB();
$stmt = $db->prepare('
    SELECT r.*, ts.label AS slot_label, ts.start_time, ts.end_time
    FROM reservations r
    JOIN time_slots ts ON ts.id = r.slot_id
    WHERE r.id = ?
');
$stmt->bind_param('i', $id);
$stmt->execute();
$res = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$res) {
    http_response_code(404);
    include __DIR__ . '/../public/404.php';
    exit;
}

requireOwnerOrAdmin((int)$res['user_id']);

$pageTitle = 'Réservation confirmée';
include __DIR__ . '/../src/templates/header.php';
?>

<div class="confirm-card" id="confirmPage">
  <div class="confirm-icon" id="confIcon">✅</div>
  <h2>Réservation confirmée !</h2>
  <p style="color: var(--text-secondary); margin-bottom: 1.5rem;">Votre table est réservée. Voici le récapitulatif :</p>

  <div class="conf-detail">
    <span class="conf-label">Date</span>
    <span class="conf-value"><?= h($res['date']) ?></span>
  </div>
  <div class="conf-detail">
    <span class="conf-label">Créneau</span>
    <span class="conf-value"><?= h($res['slot_label']) ?></span>
  </div>
  <div class="conf-detail">
    <span class="conf-label">Horaire</span>
    <span class="conf-value"><?= h(substr($res['start_time'],0,5)) ?> – <?= h(substr($res['end_time'],0,5)) ?></span>
  </div>
  <div class="conf-detail">
    <span class="conf-label">Couverts</span>
    <span class="conf-value"><?= (int)$res['guests'] ?></span>
  </div>
  <div class="conf-detail">
    <span class="conf-label">Téléphone</span>
    <span class="conf-value"><?= h($res['phone']) ?></span>
  </div>
  <?php if (!empty($res['notes'])): ?>
  <div class="conf-detail">
    <span class="conf-label">Notes</span>
    <span class="conf-value"><?= h($res['notes']) ?></span>
  </div>
  <?php endif; ?>

  <div class="form-actions" style="justify-content: center; margin-top: 1.5rem;">
    <a href="/restaurant_booking/reservations/index.php" class="btn btn-primary">📋 Mes réservations</a>
    <a href="/restaurant_booking/reservations/create.php" class="btn btn-secondary">+ Nouvelle réservation</a>
  </div>
</div>

<?php include __DIR__ . '/../src/templates/footer.php'; ?>
