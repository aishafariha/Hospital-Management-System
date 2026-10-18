<?php
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/icons.php';
$user = current_user();
$pageTitle = $pageTitle ?? 'Fariha Healthcare';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($pageTitle) ?> | HealthSheba</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<nav class="navbar">
  <div class="nav-inner">
    <a href="index.php" class="brand">HealthSheba<span class="dot">.</span>Health</a>
    <ul class="nav-links">
      <li><a href="index.php#services">Online Services</a></li>
      <li><a href="index.php#hospital">In-Hospital</a></li>
      <li><a href="index.php#doctors">Doctors</a></li>
      <li><a href="index.php#comments">Reviews</a></li>
    </ul>
    <div class="nav-actions">
      <button class="theme-toggle" id="theme-toggle" type="button" aria-label="Toggle dark mode">🌙</button>
      <?php if ($user): ?>
        <?php if ($user['role'] === 'admin'): ?>
          <a href="admin/index.php" class="btn btn-ghost"><?= icon('admin') ?> Admin Panel</a>
        <?php endif; ?>
        <a href="profile.php" class="btn btn-ghost">My Profile</a>
        <a href="transactions.php" class="btn btn-ghost">Billing</a>
        <a href="appointment.php" class="btn btn-ghost">Book Now</a>
        <a href="logout.php" class="btn btn-primary">Logout</a>
      <?php else: ?>
        <a href="login.php" class="btn btn-ghost">Log In</a>
        <a href="register.php" class="btn btn-primary">Sign Up</a>
      <?php endif; ?>
    </div>
  </div>
</nav>
