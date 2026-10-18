<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/icons.php';
$pageTitle = 'Book Appointment';
require_login();

$pdo = getPDO();
$doctors  = $pdo->query("SELECT d.*, s.name AS spec_name FROM doctors d JOIN doctor_specializations s ON s.id=d.specialization_id ORDER BY d.full_name")->fetchAll();
$services = $pdo->query("SELECT * FROM hospital_services WHERE category='online' ORDER BY name")->fetchAll();
$rooms    = $pdo->query("SELECT * FROM rooms WHERE is_available=1 ORDER BY room_type, room_number")->fetchAll();

$paymentMethods = [
    'bkash'  => 'bKash',
    'nagad'  => 'Nagad',
    'rocket' => 'Rocket',
    'bank'   => 'Bank Transfer',
    'cash'   => 'Cash on Visit',
];

$nursingServiceId = null;
foreach ($services as $s) {
    if (stripos($s['name'], 'nursing') !== false) { $nursingServiceId = (int) $s['id']; break; }
}

$preselectDoctor  = (int) ($_GET['doctor'] ?? 0);
$preselectService = (int) ($_GET['service'] ?? 0);
$preselectMode    = in_array($_GET['mode'] ?? '', ['online','home_visit','in_hospital'], true) ? $_GET['mode'] : 'online';
$preselectRoom    = (int) ($_GET['room'] ?? 0);

$error = null;
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['csrf_token'] ?? '')) {
        $error = 'Session expired. Please refresh and try again.';
    } else {
        $doctorId = (int) input('doctor_id');
        $serviceId = input('service_id') !== '' ? (int) input('service_id') : null;
        $date = input('appointment_date');
        $time = input('appointment_time');
        $mode = in_array(input('mode'), ['online','home_visit','in_hospital'], true) ? input('mode') : 'online';
        $roomId = ($mode === 'in_hospital' && input('room_id') !== '') ? (int) input('room_id') : null;
        $paymentMethod = array_key_exists(input('payment_method'), $paymentMethods) ? input('payment_method') : 'bkash';
        $notes = input('notes');

        // Fold nursing-specific details into the notes field, since they only
        // apply when the Home Nursing Service is the selected service.
        if ($nursingServiceId && $serviceId === $nursingServiceId) {
            $careType = input('nursing_care_type');
            $duration = input('nursing_duration_days');
            $nursingNotes = input('nursing_instructions');
            $parts = [];
            if ($careType !== '') $parts[] = 'Care type: ' . $careType;
            if ($duration !== '') $parts[] = 'Duration: ' . $duration . ' day(s)';
            if ($nursingNotes !== '') $parts[] = 'Nursing instructions: ' . $nursingNotes;
            if ($parts) {
                $notes = trim(implode(' | ', $parts) . ($notes !== '' ? ' | ' . $notes : ''));
                $notes = substr($notes, 0, 255);
            }
        }

        $dateOk = (bool) DateTime::createFromFormat('Y-m-d', $date);
        $timeOk = (bool) DateTime::createFromFormat('H:i', $time);

        // confirm doctor exists (prepared statement — prevents injection)
        $docCheck = $pdo->prepare("SELECT id, consultation_fee FROM doctors WHERE id = ?");
        $docCheck->execute([$doctorId]);
        $doctor = $docCheck->fetch();

        $room = null;
        if ($roomId) {
            $roomCheck = $pdo->prepare("SELECT id, price_per_day FROM rooms WHERE id = ? AND is_available = 1");
            $roomCheck->execute([$roomId]);
            $room = $roomCheck->fetch();
        }

        if (!$doctor) {
            $error = 'Please select a valid doctor.';
        } elseif (!$dateOk || !$timeOk) {
            $error = 'Please choose a valid date and time.';
        } elseif (DateTime::createFromFormat('Y-m-d', $date) < new DateTime('today')) {
            $error = 'Appointment date cannot be in the past.';
        } elseif ($roomId && !$room) {
            $error = 'Please select an available room, or leave it unselected.';
        } else {
            $pdo->beginTransaction();
            try {
                $stmt = $pdo->prepare("INSERT INTO appointments (patient_id, doctor_id, service_id, room_id, appointment_date, appointment_time, mode, notes) VALUES (?,?,?,?,?,?,?,?)");
                $stmt->execute([$_SESSION['user_id'], $doctorId, $serviceId, $roomId, $date, $time, $mode, $notes]);
                $appointmentId = (int) $pdo->lastInsertId();

                $amount = (float) $doctor['consultation_fee'] + ($room ? (float) $room['price_per_day'] : 0);

                $txn = $pdo->prepare("INSERT INTO transactions (user_id, appointment_id, amount, payment_method, status) VALUES (?,?,?,?, 'pending')");
                $txn->execute([$_SESSION['user_id'], $appointmentId, $amount, $paymentMethod]);

                $pdo->commit();
                $success = $room
                    ? 'Your appointment and room reservation have been booked! Head to Billing to complete payment via ' . $paymentMethods[$paymentMethod] . '.'
                    : 'Your appointment has been booked! Head to Billing to complete payment via ' . $paymentMethods[$paymentMethod] . '.';
            } catch (Exception $e) {
                $pdo->rollBack();
                $error = 'Something went wrong while booking. Please try again.';
            }
        }
    }
}

$myAppointments = $pdo->prepare("
    SELECT a.*, d.full_name AS doctor_name, s.name AS service_name, r.room_number, r.room_type
    FROM appointments a
    JOIN doctors d ON d.id = a.doctor_id
    LEFT JOIN hospital_services s ON s.id = a.service_id
    LEFT JOIN rooms r ON r.id = a.room_id
    WHERE a.patient_id = ?
    ORDER BY a.created_at DESC LIMIT 5
");
$myAppointments->execute([$_SESSION['user_id']]);
$myAppointments = $myAppointments->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>
<section class="section" style="padding-top:130px;">
  <div class="container" style="max-width:760px;">
    <span class="eyebrow">Book a Visit</span>
    <h2 class="section-title">Schedule your appointment</h2>
    <p class="section-sub">Pick a doctor, choose how you'd like to be seen, and we'll take care of the rest.</p>

    <div class="glass-card" style="padding:36px;">
      <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
      <?php if ($success): ?><div class="alert alert-success"><?= e($success) ?></div><?php endif; ?>

      <form method="post" action="appointment.php">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

        <div class="field">
          <label for="doctor_id">Doctor</label>
          <select id="doctor_id" name="doctor_id" required>
            <option value="">Select a doctor</option>
            <?php foreach ($doctors as $d): ?>
              <option value="<?= (int)$d['id'] ?>" <?= $preselectDoctor === (int)$d['id'] ? 'selected' : '' ?>>
                <?= e($d['full_name']) ?> — <?= e($d['spec_name']) ?> (৳<?= number_format((float)$d['consultation_fee']) ?>)
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="field">
          <label for="service_id">Related service (optional)</label>
          <select id="service_id" name="service_id" <?= $nursingServiceId ? "onchange=\"document.getElementById('nursing-fields').style.display = this.value === '{$nursingServiceId}' ? 'block' : 'none';\"" : '' ?>>
            <option value="">None</option>
            <?php foreach ($services as $s): ?>
              <option value="<?= (int)$s['id'] ?>" <?= $preselectService === (int)$s['id'] ? 'selected' : '' ?>><?= e($s['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <?php if ($nursingServiceId): ?>
        <div id="nursing-fields" class="glass-card" style="padding:20px 22px;margin-bottom:18px;<?= $preselectService === $nursingServiceId ? '' : 'display:none;' ?>background:rgba(14,124,123,0.06);">
          <p class="eyebrow" style="margin-bottom:14px;">Nursing Care Details</p>
          <div class="field">
            <label for="nursing_care_type">Type of care needed</label>
            <select id="nursing_care_type" name="nursing_care_type">
              <option value="">Select&hellip;</option>
              <option value="General Care">General Care</option>
              <option value="Post-Surgery Care">Post-Surgery Care</option>
              <option value="Elderly Care">Elderly Care</option>
              <option value="Pediatric Care">Pediatric Care</option>
              <option value="Palliative / End-of-Life Care">Palliative / End-of-Life Care</option>
              <option value="Wound Dressing">Wound Dressing</option>
              <option value="Injection / Medication Administration">Injection / Medication Administration</option>
            </select>
          </div>
          <div class="field">
            <label for="nursing_duration_days">Duration (days)</label>
            <input type="number" id="nursing_duration_days" name="nursing_duration_days" min="1" max="90" placeholder="e.g. 3">
          </div>
          <div class="field" style="margin-bottom:0;">
            <label for="nursing_instructions">Care instructions for the nurse</label>
            <textarea id="nursing_instructions" name="nursing_instructions" rows="2" maxlength="200" placeholder="Mobility level, medication schedule, allergies, etc."></textarea>
          </div>
        </div>
        <?php endif; ?>

        <div class="field">
          <label for="mode">Visit type</label>
          <select id="mode" name="mode" onchange="document.getElementById('room-field').style.display = this.value === 'in_hospital' ? 'block' : 'none';">
            <option value="online" <?= $preselectMode === 'online' ? 'selected' : '' ?>>Online Video Consultation</option>
            <option value="home_visit" <?= $preselectMode === 'home_visit' ? 'selected' : '' ?>>Home Visit</option>
            <option value="in_hospital" <?= $preselectMode === 'in_hospital' ? 'selected' : '' ?>>In-Hospital Visit</option>
          </select>
        </div>

        <div class="field" id="room-field" style="<?= $preselectMode === 'in_hospital' ? '' : 'display:none;' ?>">
          <label for="room_id">Reserve a room (optional)</label>
          <select id="room_id" name="room_id">
            <option value="">No room needed</option>
            <?php foreach ($rooms as $r): ?>
              <option value="<?= (int)$r['id'] ?>" <?= $preselectRoom === (int)$r['id'] ? 'selected' : '' ?>>
                <?= e($r['room_number']) ?> — <?= e(ucfirst($r['room_type'])) ?> (৳<?= number_format((float)$r['price_per_day']) ?>/day)
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
          <div class="field">
            <label for="appointment_date">Date</label>
            <input type="date" id="appointment_date" name="appointment_date" required min="<?= date('Y-m-d') ?>">
          </div>
          <div class="field">
            <label for="appointment_time">Time</label>
            <input type="time" id="appointment_time" name="appointment_time" required>
          </div>
        </div>

        <div class="field">
          <label for="payment_method">Preferred payment method</label>
          <select id="payment_method" name="payment_method">
            <?php foreach ($paymentMethods as $key => $label): ?>
              <option value="<?= e($key) ?>"><?= e($label) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="field">
          <label for="notes">Notes for the doctor (optional)</label>
          <textarea id="notes" name="notes" rows="3" maxlength="255" placeholder="Symptoms, concerns, or anything else..."></textarea>
        </div>

        <button type="submit" class="btn btn-primary btn-block"><?= icon('appointment') ?> Confirm Booking</button>
      </form>
    </div>

    <?php if ($myAppointments): ?>
    <div style="display:flex;justify-content:space-between;align-items:center;margin-top:48px;">
      <h3 style="margin:0;">Your recent appointments</h3>
      <a href="transactions.php" class="card-link">View billing →</a>
    </div>
    <div style="display:flex;flex-direction:column;gap:14px;margin-top:16px;">
      <?php foreach ($myAppointments as $a): ?>
        <div class="glass-card" style="padding:18px 22px;display:flex;justify-content:space-between;align-items:center;">
          <div>
            <strong><?= e($a['doctor_name']) ?></strong><br>
            <span style="color:var(--text-soft);font-size:.9rem;">
              <?= e($a['appointment_date']) ?> at <?= e(substr($a['appointment_time'],0,5)) ?>
              · <?= e(str_replace('_',' ', $a['mode'])) ?>
              <?= $a['service_name'] ? '· ' . e($a['service_name']) : '' ?>
              <?= $a['room_number'] ? '· Room ' . e($a['room_number']) . ' (' . e(ucfirst($a['room_type'])) . ')' : '' ?>
            </span>
          </div>
          <span class="eyebrow" style="margin:0;"><?= e(ucfirst($a['status'])) ?></span>
        </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
