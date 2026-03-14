<?php
require_once __DIR__ . '/../../src/config/db.php';
require_once __DIR__ . '/../../src/helpers/auth.php';
require_once __DIR__ . '/../../src/helpers/flash.php';
require_once __DIR__ . '/../../src/helpers/security.php';

requireAdmin();

$db = getDB();

// Filters
$dateFilter   = $_GET['date']   ?? '';
$statusFilter = $_GET['status'] ?? '';
$slotFilter   = (int)($_GET['slot_id'] ?? 0);

$sql    = 'SELECT r.*, ts.label AS slot_label, ts.start_time, ts.end_time, u.email AS user_email
           FROM reservations r
           JOIN time_slots ts ON ts.id = r.slot_id
           JOIN users u ON u.id = r.user_id
           WHERE 1=1';
$params = [];
$types  = '';

if ($dateFilter !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateFilter)) {
    $sql .= ' AND r.date = ?';
    $params[] = $dateFilter;
    $types   .= 's';
}
if ($statusFilter === 'CONFIRMED' || $statusFilter === 'CANCELLED') {
    $sql .= ' AND r.status = ?';
    $params[] = $statusFilter;
    $types   .= 's';
}
if ($slotFilter > 0) {
    $sql .= ' AND r.slot_id = ?';
    $params[] = $slotFilter;
    $types   .= 'i';
}

$sql .= ' ORDER BY r.date DESC, ts.start_time ASC';

$stmt = $db->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$reservations = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// For slot filter dropdown
$allSlots = $db->query('SELECT * FROM time_slots ORDER BY start_time ASC')->fetch_all(MYSQLI_ASSOC);

// Handle admin cancel
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_id'])) {
    csrfValidate();
    $cancelId = (int)$_POST['cancel_id'];
    $stmtCancel = $db->prepare("UPDATE reservations SET status = 'CANCELLED' WHERE id = ?");
    $stmtCancel->bind_param('i', $cancelId);
    $stmtCancel->execute();
    $stmtCancel->close();
    flashSet('success', 'Réservation #' . $cancelId . ' annulée.');
    header('Location: /restaurant_booking/admin/reservations/index.php?' . http_build_query($_GET));
    exit;
}

$pageTitle = 'Admin — Réservations';
include __DIR__ . '/../../src/templates/header.php';
?>

<div class="page-header page-header-actions">
  <div>
    <h1>Gestion des réservations</h1>
    <p><?= count($reservations) ?> résultat(s)</p>
  </div>
</div>

<!-- ─── FILTER BAR ───────────────────────────────────────── -->
<form class="filter-bar" id="filterBar" method="GET">
  <div class="form-group">
    <label for="date">Date</label>
    <input type="date" id="date" name="date" value="<?= h($dateFilter) ?>">
  </div>
  <div class="form-group">
    <label for="status">Statut</label>
    <select id="status" name="status">
      <option value="">Tous</option>
      <option value="CONFIRMED" <?= $statusFilter === 'CONFIRMED' ? 'selected' : '' ?>>Confirmées</option>
      <option value="CANCELLED" <?= $statusFilter === 'CANCELLED' ? 'selected' : '' ?>>Annulées</option>
    </select>
  </div>
  <div class="form-group">
    <label for="slot_id">Créneau</label>
    <select id="slot_id" name="slot_id">
      <option value="">Tous</option>
      <?php foreach ($allSlots as $s): ?>
      <option value="<?= (int)$s['id'] ?>" <?= $slotFilter === (int)$s['id'] ? 'selected' : '' ?>>
        <?= h($s['label']) ?>
      </option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="form-group">
    <button type="submit" class="btn btn-primary btn-sm" style="margin-top: 1.4rem;">Filtrer</button>
  </div>
</form>

<?php if (empty($reservations)): ?>
<div class="empty-state">
  <div class="empty-icon">📭</div>
  <h3>Aucune réservation trouvée</h3>
  <p>Modifiez vos filtres ou attendez les nouvelles réservations.</p>
</div>
<?php else: ?>
<div class="table-wrapper">
  <table>
    <thead>
      <tr>
        <th>#</th>
        <th>Utilisateur</th>
        <th>Date</th>
        <th>Créneau</th>
        <th>Horaire</th>
        <th>Couverts</th>
        <th>Téléphone</th>
        <th>Statut</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($reservations as $r): ?>
      <tr class="list-item">
        <td><?= (int)$r['id'] ?></td>
        <td><?= h($r['user_email']) ?></td>
        <td><?= h($r['date']) ?></td>
        <td><?= h($r['slot_label']) ?></td>
        <td><?= h(substr($r['start_time'],0,5)) ?> – <?= h(substr($r['end_time'],0,5)) ?></td>
        <td><?= (int)$r['guests'] ?></td>
        <td><?= h($r['phone']) ?></td>
        <td>
          <?php if ($r['status'] === 'CONFIRMED'): ?>
            <span class="badge badge-confirmed" id="statusBadge">Confirmée</span>
          <?php else: ?>
            <span class="badge badge-cancelled">Annulée</span>
          <?php endif; ?>
        </td>
        <td>
          <?php if ($r['status'] === 'CONFIRMED'): ?>
          <form method="POST" style="display:inline;">
            <input type="hidden" name="csrf" value="<?= h(csrfToken()) ?>">
            <input type="hidden" name="cancel_id" value="<?= (int)$r['id'] ?>">
            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Annuler cette réservation ?')">Annuler</button>
          </form>
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

<?php include __DIR__ . '/../../src/templates/footer.php'; ?>
