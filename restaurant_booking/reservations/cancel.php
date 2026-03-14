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

if ($res['status'] === 'CANCELLED') {
    flashSet('warning', 'Cette réservation est déjà annulée.');
    header('Location: /restaurant_booking/reservations/index.php');
    exit;
}

// Process cancellation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrfValidate();

    $stmtUpd = $db->prepare("UPDATE reservations SET status = 'CANCELLED' WHERE id = ?");
    $stmtUpd->bind_param('i', $id);
    $stmtUpd->execute();
    $stmtUpd->close();

    flashSet('success', 'Réservation annulée avec succès.');
    header('Location: /restaurant_booking/reservations/index.php');
    exit;
}

$pageTitle = 'Annuler la réservation';
include __DIR__ . '/../src/templates/header.php';
?>

<div class="page-header">
  <h1>Annuler la réservation #<?= (int)$res['id'] ?></h1>
  <p>Cette action est irréversible. Veuillez confirmer.</p>
</div>

<div class="form-card">
  <div style="margin-bottom: 1.5rem;">
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
  </div>

  <form method="POST">
    <input type="hidden" name="csrf" value="<?= h(csrfToken()) ?>">
    <div class="form-actions">
      <button type="submit" class="btn btn-danger">🗑 Confirmer l'annulation</button>
      <a href="/restaurant_booking/reservations/index.php" class="btn btn-secondary">Retour</a>
    </div>
  </form>
</div>

<?php include __DIR__ . '/../src/templates/footer.php'; ?>
