<?php
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/icons.php';
require_once __DIR__ . '/admin_functions.php';
require_login();
require_admin();

$user = current_user();
$pageTitle = ($config['title'] ?? 'Dashboard') . ' — Admin';
$currentFile = basename($_SERVER['PHP_SELF']);

$navItems = [
    ['index.php', 'admin', 'Dashboard'],
    ['doctors.php', 'doctor', 'Doctors'],
    ['specializations.php', 'specialization', 'Specializations'],
    ['services.php', 'services', 'Hospital Services'],
    ['rooms.php', 'room', 'Room Management'],
    ['discounts.php', 'discount', 'Discounts'],
    ['appointments.php', 'appointment', 'Appointments'],
    ['transactions.php', 'transaction', 'Transactions'],
    ['patients.php', 'patients', 'Patients'],
    ['physicians.php', 'physicians', 'Physicians'],
    ['users.php', 'users', 'Users'],
    ['user_details.php', 'userdetails', 'User Details'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($pageTitle) ?> | HealthSheba</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="admin-body">
<div class="admin-shell">
  <aside class="admin-sidebar">
    <a href="../index.php" class="brand" style="color:#fff;margin-bottom:34px;display:block;">HealthSheba<span class="dot">.</span>Health</a>
    <nav class="admin-nav">
      <?php foreach ($navItems as [$file, $ic, $label]): ?>
        <a href="<?= e($file) ?>" class="admin-nav-link<?= $currentFile === $file ? ' active' : '' ?>">
          <?= icon($ic) ?><span><?= e($label) ?></span>
        </a>
      <?php endforeach; ?>
    </nav>
    <div class="admin-sidebar-foot">
      <div><?= e($user['full_name']) ?><br><span style="opacity:.6;font-size:.78rem;">Administrator</span></div>
      <a href="../logout.php">Logout</a>
    </div>
  </aside>
  <main class="admin-main">
