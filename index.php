<?php
require_once __DIR__ . '/../includes/functions.php';
$pdo = getPDO();
$config = ['title' => 'Dashboard'];
require_once __DIR__ . '/../includes/admin_layout_header.php';

$stats = [
    'Total Users'         => (int) $pdo->query("SELECT COUNT(*) c FROM users")->fetch()['c'],
    'Patients'            => (int) $pdo->query("SELECT COUNT(*) c FROM users WHERE role='patient'")->fetch()['c'],
    'Physicians'          => (int) $pdo->query("SELECT COUNT(*) c FROM users WHERE role='physician'")->fetch()['c'],
    'Doctors on Roster'   => (int) $pdo->query("SELECT COUNT(*) c FROM doctors")->fetch()['c'],
    'Appointments'        => (int) $pdo->query("SELECT COUNT(*) c FROM appointments")->fetch()['c'],
    'Pending Appointments'=> (int) $pdo->query("SELECT COUNT(*) c FROM appointments WHERE status='pending'")->fetch()['c'],
    'Rooms Available'     => (int) $pdo->query("SELECT COUNT(*) c FROM rooms WHERE is_available=1")->fetch()['c'],
    'Revenue Collected'   => '৳' . number_format((float) $pdo->query("SELECT COALESCE(SUM(amount),0) s FROM transactions WHERE status='paid'")->fetch()['s']),
];

$recentAppointments = $pdo->query("
    SELECT a.id, a.appointment_date, a.appointment_time, a.status, a.mode,
           u.full_name AS patient_name, d.full_name AS doctor_name
    FROM appointments a
    JOIN users u ON u.id = a.patient_id
    JOIN doctors d ON d.id = a.doctor_id
    ORDER BY a.created_at DESC LIMIT 8
")->fetchAll();
?>
<div class="admin-content">
  <h1>Welcome back, <?= e(explode(' ', $user['full_name'])[0]) ?></h1>
  <p class="section-sub" style="margin-bottom:30px;">Here's what's happening across the hospital right now.</p>

  <div class="admin-stat-grid">
    <?php foreach ($stats as $label => $val): ?>
      <div class="glass-card admin-stat-card">
        <span class="stat-value"><?= e((string) $val) ?></span>
        <span class="stat-label"><?= e($label) ?></span>
      </div>
    <?php endforeach; ?>
  </div>

  <h3 style="margin:38px 0 16px;">Recent appointments</h3>
  <div class="glass-card" style="padding:0;overflow:auto;">
    <table class="admin-table">
      <thead><tr><th>Patient</th><th>Doctor</th><th>Date</th><th>Mode</th><th>Status</th></tr></thead>
      <tbody>
        <?php if (!$recentAppointments): ?>
          <tr><td colspan="5" style="text-align:center;color:var(--text-soft);padding:26px;">No appointments booked yet.</td></tr>
        <?php endif; ?>
        <?php foreach ($recentAppointments as $a): ?>
          <tr>
            <td><?= e($a['patient_name']) ?></td>
            <td><?= e($a['doctor_name']) ?></td>
            <td><?= e($a['appointment_date']) ?> at <?= e(substr($a['appointment_time'], 0, 5)) ?></td>
            <td><?= e(str_replace('_', ' ', $a['mode'])) ?></td>
            <td><?= e(ucfirst($a['status'])) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require_once __DIR__ . '/../includes/admin_layout_footer.php'; ?>
