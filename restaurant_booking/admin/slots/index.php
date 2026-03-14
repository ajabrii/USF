<?php
require_once __DIR__ . '/../../src/config/db.php';
require_once __DIR__ . '/../../src/helpers/auth.php';
require_once __DIR__ . '/../../src/helpers/flash.php';
require_once __DIR__ . '/../../src/helpers/security.php';

requireAdmin();

$db = getDB();

// ─── Handle CREATE slot ─────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    csrfValidate();

    $action = $_POST['action'];

    if ($action === 'create') {
        $label    = trim($_POST['label'] ?? '');
        $start    = trim($_POST['start_time'] ?? '');
        $end      = trim($_POST['end_time'] ?? '');
        $capacity = (int)($_POST['capacity_max'] ?? 30);

        if ($label === '' || $start === '' || $end === '') {
            flashSet('error', 'Tous les champs sont obligatoires.');
        } elseif ($capacity < 1) {
            flashSet('error', 'La capacité doit être supérieure à 0.');
        } else {
            $stmt = $db->prepare('INSERT INTO time_slots (label, start_time, end_time, capacity_max) VALUES (?, ?, ?, ?)');
            $stmt->bind_param('sssi', $label, $start, $end, $capacity);
            $stmt->execute();
            $stmt->close();
            flashSet('success', 'Créneau créé avec succès.');
        }
        header('Location: /restaurant_booking/admin/slots/index.php');
        exit;
    }

    if ($action === 'edit') {
        $slotId   = (int)($_POST['slot_id'] ?? 0);
        $label    = trim($_POST['label'] ?? '');
        $start    = trim($_POST['start_time'] ?? '');
        $end      = trim($_POST['end_time'] ?? '');
        $capacity = (int)($_POST['capacity_max'] ?? 30);

        if ($slotId > 0 && $label !== '' && $start !== '' && $end !== '' && $capacity > 0) {
            $stmt = $db->prepare('UPDATE time_slots SET label = ?, start_time = ?, end_time = ?, capacity_max = ? WHERE id = ?');
            $stmt->bind_param('sssii', $label, $start, $end, $capacity, $slotId);
            $stmt->execute();
            $stmt->close();
            flashSet('success', 'Créneau mis à jour.');
        } else {
            flashSet('error', 'Données invalides.');
        }
        header('Location: /restaurant_booking/admin/slots/index.php');
        exit;
    }

    if ($action === 'toggle') {
        $slotId = (int)($_POST['slot_id'] ?? 0);
        if ($slotId > 0) {
            $stmt = $db->prepare('UPDATE time_slots SET is_active = NOT is_active WHERE id = ?');
            $stmt->bind_param('i', $slotId);
            $stmt->execute();
            $stmt->close();
            flashSet('success', 'Statut du créneau modifié.');
        }
        header('Location: /restaurant_booking/admin/slots/index.php');
        exit;
    }

    if ($action === 'delete') {
        $slotId = (int)($_POST['slot_id'] ?? 0);
        if ($slotId > 0) {
            // Check for existing confirmed reservations
            $stmtCheck = $db->prepare("SELECT COUNT(*) AS c FROM reservations WHERE slot_id = ? AND status = 'CONFIRMED'");
            $stmtCheck->bind_param('i', $slotId);
            $stmtCheck->execute();
            $count = (int)$stmtCheck->get_result()->fetch_assoc()['c'];
            $stmtCheck->close();

            if ($count > 0) {
                flashSet('error', 'Impossible de supprimer un créneau avec des réservations confirmées (' . $count . ').');
            } else {
                $stmt = $db->prepare('DELETE FROM time_slots WHERE id = ?');
                $stmt->bind_param('i', $slotId);
                $stmt->execute();
                $stmt->close();
                flashSet('success', 'Créneau supprimé.');
            }
        }
        header('Location: /restaurant_booking/admin/slots/index.php');
        exit;
    }
}

// Fetch all slots
$slots = $db->query('SELECT * FROM time_slots ORDER BY start_time ASC')->fetch_all(MYSQLI_ASSOC);

// Are we editing a slot?
$editSlot = null;
$editId   = (int)($_GET['edit'] ?? 0);
if ($editId > 0) {
    $stmtEdit = $db->prepare('SELECT * FROM time_slots WHERE id = ?');
    $stmtEdit->bind_param('i', $editId);
    $stmtEdit->execute();
    $editSlot = $stmtEdit->get_result()->fetch_assoc();
    $stmtEdit->close();
}

$pageTitle = 'Admin — Créneaux';
include __DIR__ . '/../../src/templates/header.php';
?>

<div class="page-header page-header-actions">
  <div>
    <h1>Gestion des créneaux</h1>
    <p><?= count($slots) ?> créneau(x) configuré(s)</p>
  </div>
</div>

<!-- ─── CREATE / EDIT FORM ───────────────────────────────── -->
<div class="form-card" style="margin-bottom: 2rem;">
  <h2 style="font-size: 1.1rem; margin-bottom: 1rem;">
    <?= $editSlot ? '✏️ Modifier le créneau #' . (int)$editSlot['id'] : '➕ Nouveau créneau' ?>
  </h2>
  <form method="POST" id="mainForm" novalidate>
    <input type="hidden" name="csrf" value="<?= h(csrfToken()) ?>">
    <input type="hidden" name="action" value="<?= $editSlot ? 'edit' : 'create' ?>">
    <?php if ($editSlot): ?>
    <input type="hidden" name="slot_id" value="<?= (int)$editSlot['id'] ?>">
    <?php endif; ?>

    <div class="form-group">
      <label for="label">Nom du créneau</label>
      <input type="text" id="label" name="label" required placeholder="ex: Déjeuner – 1er service" value="<?= h($editSlot['label'] ?? '') ?>">
    </div>

    <div class="form-row">
      <div class="form-group">
        <label for="start_time">Début</label>
        <input type="time" id="start_time" name="start_time" required value="<?= h($editSlot['start_time'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label for="end_time">Fin</label>
        <input type="time" id="end_time" name="end_time" required value="<?= h($editSlot['end_time'] ?? '') ?>">
      </div>
    </div>

    <div class="form-group">
      <label for="capacity_max">Capacité maximale (couverts)</label>
      <input type="number" id="capacity_max" name="capacity_max" min="1" required value="<?= h($editSlot['capacity_max'] ?? '30') ?>">
    </div>

    <div class="form-actions">
      <button type="submit" class="btn btn-primary"><?= $editSlot ? '💾 Enregistrer' : '➕ Créer' ?></button>
      <?php if ($editSlot): ?>
      <a href="/restaurant_booking/admin/slots/index.php" class="btn btn-secondary">Annuler</a>
      <?php endif; ?>
    </div>
  </form>
</div>

<!-- ─── SLOTS LIST ───────────────────────────────────────── -->
<?php if (empty($slots)): ?>
<div class="empty-state">
  <div class="empty-icon">🕐</div>
  <h3>Aucun créneau</h3>
  <p>Créez votre premier créneau horaire ci-dessus.</p>
</div>
<?php else: ?>
<div class="card-grid">
  <?php foreach ($slots as $s): ?>
  <div class="slot-card card">
    <div class="slot-label"><?= h($s['label']) ?></div>
    <div class="slot-time">🕐 <?= h(substr($s['start_time'],0,5)) ?> – <?= h(substr($s['end_time'],0,5)) ?></div>
    <div class="slot-capacity">👥 Max <?= (int)$s['capacity_max'] ?> couverts</div>
    <div>
      <?php if ((int)$s['is_active']): ?>
        <span class="badge badge-active">Actif</span>
      <?php else: ?>
        <span class="badge badge-inactive">Inactif</span>
      <?php endif; ?>
    </div>
    <div class="slot-actions">
      <a href="?edit=<?= (int)$s['id'] ?>" class="btn btn-sm btn-secondary">✏️ Modifier</a>
      <form method="POST" style="display:inline;">
        <input type="hidden" name="csrf" value="<?= h(csrfToken()) ?>">
        <input type="hidden" name="action" value="toggle">
        <input type="hidden" name="slot_id" value="<?= (int)$s['id'] ?>">
        <button type="submit" class="btn btn-sm btn-secondary"><?= (int)$s['is_active'] ? '🔒 Désactiver' : '🔓 Activer' ?></button>
      </form>
      <form method="POST" style="display:inline;">
        <input type="hidden" name="csrf" value="<?= h(csrfToken()) ?>">
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="slot_id" value="<?= (int)$s['id'] ?>">
        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer ce créneau ?')">🗑</button>
      </form>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<?php include __DIR__ . '/../../src/templates/footer.php'; ?>
