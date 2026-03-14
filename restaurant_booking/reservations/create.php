<?php
require_once __DIR__ . '/../src/config/db.php';
require_once __DIR__ . '/../src/helpers/auth.php';
require_once __DIR__ . '/../src/helpers/flash.php';
require_once __DIR__ . '/../src/helpers/security.php';

requireLogin();

$db     = getDB();
$userId = currentUserId();

// Fetch active time slots
$slotsResult = $db->query('SELECT * FROM time_slots WHERE is_active = 1 ORDER BY start_time ASC');
$slots = $slotsResult->fetch_all(MYSQLI_ASSOC);

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrfValidate();

    $date    = trim($_POST['date'] ?? '');
    $slotId  = (int)($_POST['slot_id'] ?? 0);
    $guests  = (int)($_POST['guests'] ?? 0);
    $phone   = trim($_POST['phone'] ?? '');
    $notes   = trim($_POST['notes'] ?? '');

    // Validation
    if ($date === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        $errors[] = 'Date invalide.';
    } elseif ($date < date('Y-m-d')) {
        $errors[] = 'La date ne peut pas être dans le passé.';
    }
    if ($slotId <= 0) $errors[] = 'Veuillez choisir un créneau.';
    if ($guests < 1 || $guests > 20) $errors[] = 'Nombre de couverts : entre 1 et 20.';
    if ($phone === '') $errors[] = 'Le numéro de téléphone est obligatoire.';

    // Capacity check
    if (empty($errors) && $slotId > 0) {
        // Get slot capacity
        $stmtCap = $db->prepare('SELECT capacity_max FROM time_slots WHERE id = ? AND is_active = 1');
        $stmtCap->bind_param('i', $slotId);
        $stmtCap->execute();
        $slotData = $stmtCap->get_result()->fetch_assoc();
        $stmtCap->close();

        if (!$slotData) {
            $errors[] = 'Créneau introuvable ou inactif.';
        } else {
            $stmtTotal = $db->prepare("SELECT COALESCE(SUM(guests),0) AS total_guests FROM reservations WHERE slot_id = ? AND date = ? AND status = 'CONFIRMED'");
            $stmtTotal->bind_param('is', $slotId, $date);
            $stmtTotal->execute();
            $totalGuests = (int)$stmtTotal->get_result()->fetch_assoc()['total_guests'];
            $stmtTotal->close();

            if ($totalGuests + $guests > (int)$slotData['capacity_max']) {
                $errors[] = 'Capacité insuffisante pour ce créneau ('. ($slotData['capacity_max'] - $totalGuests) .' places restantes).';
            }
        }
    }

    if (empty($errors)) {
        $stmtIns = $db->prepare('INSERT INTO reservations (user_id, slot_id, date, guests, phone, notes) VALUES (?, ?, ?, ?, ?, ?)');
        $stmtIns->bind_param('iisiss', $userId, $slotId, $date, $guests, $phone, $notes);
        $stmtIns->execute();
        $newId = $stmtIns->insert_id;
        $stmtIns->close();

        flashSet('success', 'Réservation confirmée !');
        header('Location: /restaurant_booking/reservations/thanks.php?id=' . $newId);
        exit;
    }
}

$pageTitle = 'Nouvelle réservation';
include __DIR__ . '/../src/templates/header.php';
?>

<div class="page-header">
  <h1>Nouvelle réservation</h1>
  <p>Choisissez votre créneau et réservez votre table.</p>
</div>

<div class="form-card" id="reservationForm">
  <?php if (!empty($errors)): ?>
    <div class="alert alert-error">
      <?php foreach ($errors as $e): ?><div><?= h($e) ?></div><?php endforeach; ?>
    </div>
  <?php endif; ?>

  <form method="POST" id="mainForm" novalidate>
    <input type="hidden" name="csrf" value="<?= h(csrfToken()) ?>">

    <div class="form-group" id="dateField">
      <label for="date">Date</label>
      <input type="date" id="date" name="date" required min="<?= date('Y-m-d') ?>" value="<?= h($_POST['date'] ?? '') ?>">
    </div>

    <div class="form-group" id="slotField">
      <label for="slot_id">Créneau</label>
      <select id="slot_id" name="slot_id" required>
        <option value="">— Sélectionner un créneau —</option>
        <?php foreach ($slots as $s): ?>
        <option value="<?= (int)$s['id'] ?>" <?= ((int)($_POST['slot_id'] ?? 0) === (int)$s['id']) ? 'selected' : '' ?>>
          <?= h($s['label']) ?> (<?= h(substr($s['start_time'],0,5)) ?> – <?= h(substr($s['end_time'],0,5)) ?>) — max <?= (int)$s['capacity_max'] ?> couverts
        </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="form-row">
      <div class="form-group" id="guestField">
        <label for="guests">Nombre de couverts</label>
        <input type="number" id="guests" name="guests" min="1" max="20" required value="<?= h($_POST['guests'] ?? '2') ?>">
      </div>

      <div class="form-group">
        <label for="phone">Téléphone</label>
        <input type="tel" id="phone" name="phone" required placeholder="+212 6XX XXX XXX" value="<?= h($_POST['phone'] ?? '') ?>">
      </div>
    </div>

    <div class="form-group">
      <label for="notes">Notes (optionnel)</label>
      <textarea id="notes" name="notes" placeholder="Allergies, occasion spéciale, préférences de table…"><?= h($_POST['notes'] ?? '') ?></textarea>
    </div>

    <div class="form-actions" id="submitBtn">
      <button type="submit" class="btn btn-primary">✓ Confirmer la réservation</button>
      <a href="/restaurant_booking/public/" class="btn btn-secondary">Annuler</a>
    </div>
  </form>
</div>

<?php include __DIR__ . '/../src/templates/footer.php'; ?>
