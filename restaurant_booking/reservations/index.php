<?php
require_once __DIR__ . '/../src/config/db.php';
require_once __DIR__ . '/../src/helpers/auth.php';
require_once __DIR__ . '/../src/helpers/flash.php';
require_once __DIR__ . '/../src/helpers/security.php';

requireLogin();

$db     = getDB();
$userId = currentUserId();

// Filters
$statusFilter = $_GET['status'] ?? '';

$sql    = 'SELECT r.*, ts.label AS slot_label, ts.start_time, ts.end_time
           FROM reservations r
           JOIN time_slots ts ON ts.id = r.slot_id
           WHERE r.user_id = ?';
$params = [$userId];
$types  = 'i';

if ($statusFilter === 'CONFIRMED' || $statusFilter === 'CANCELLED') {
    $sql .= ' AND r.status = ?';
    $params[] = $statusFilter;
    $types   .= 's';
}

$sql .= ' ORDER BY r.date DESC, ts.start_time ASC';

$stmt = $db->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$reservations = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$pageTitle = 'Mes réservations';
include __DIR__ . '/../src/templates/header.php';
?>

<div class="page-header page-header-actions">
  <div>
    <h1>Mes réservations</h1>
    <p><?= count($reservations) ?> réservation(s) trouvée(s)</p>
  </div>
  <a href="/restaurant_booking/reservations/create.php" class="btn btn-primary">+ Nouvelle réservation</a>
</div>

<!-- ─── FILTER BAR ───────────────────────────────────────── -->
<form class="filter-bar" id="filterBar" method="GET">
  <div class="form-group">
    <label for="status">Statut</label>
    <select id="status" name="status" onchange="this.form.submit()">
      <option value="">Tous</option>
      <option value="CONFIRMED" <?= $statusFilter === 'CONFIRMED' ? 'selected' : '' ?>>Confirmées</option>
      <option value="CANCELLED" <?= $statusFilter === 'CANCELLED' ? 'selected' : '' ?>>Annulées</option>
    </select>
  </div>
</form>

<?php if (empty($reservations)): ?>
<div class="empty-state">
  <div class="empty-icon">📭</div>
  <h3>Aucune réservation</h3>
  <p>Vous n'avez pas encore de réservation.</p>
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
        <th>Téléphone</th>
        <th>Statut</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($reservations as $r): ?>
      <tr class="list-item">
        <td><?= h($r['date']) ?></td>
        <td><?= h($r['slot_label']) ?></td>
        <td><?= h(substr($r['start_time'],0,5)) ?> – <?= h(substr($r['end_time'],0,5)) ?></td>
        <td><?= (int)$r['guests'] ?></td>
        <td><?= h($r['phone']) ?></td>
        <td>
          <?php if ($r['status'] === 'CONFIRMED'): ?>
            <span class="badge badge-confirmed">Confirmée</span>
          <?php else: ?>
            <span class="badge badge-cancelled">Annulée</span>
          <?php endif; ?>
        </td>
        <td>
          <?php if ($r['status'] === 'CONFIRMED'): ?>
          <div class="gap-row">
            <a href="/restaurant_booking/reservations/edit.php?id=<?= (int)$r['id'] ?>" class="btn btn-sm btn-secondary">Modifier</a>
            <a href="/restaurant_booking/reservations/cancel.php?id=<?= (int)$r['id'] ?>" class="btn btn-sm btn-danger">Annuler</a>
          </div>
          <?php else: ?>
            <span style="color: var(--text-muted);">—</span>
          <?php endif; ?>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php endif; ?>

<?php include __DIR__ . '/../src/templates/footer.php'; ?>
