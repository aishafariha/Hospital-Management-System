<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/icons.php';
$pageTitle = 'Create Account';

if (is_logged_in()) { header('Location: index.php'); exit; }

$error = null;
$pdo = getPDO();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['csrf_token'] ?? '')) {
        $error = 'Session expired. Please refresh and try again.';
    } else {
        $fullName = input('full_name');
        $email    = strtolower(input('email'));
        $phone    = input('phone');
        $password = (string) ($_POST['password'] ?? '');
        $confirm  = (string) ($_POST['confirm_password'] ?? '');

        if ($fullName === '' || $email === '' || $password === '') {
            $error = 'Please fill in all required fields.';
        } elseif (!valid_email($email)) {
            $error = 'Please enter a valid email address.';
        } elseif (strlen($password) < 8) {
            $error = 'Password must be at least 8 characters long.';
        } elseif ($password !== $confirm) {
            $error = 'Passwords do not match.';
        } else {
            $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $check->execute([$email]);
            if ($check->fetch()) {
                $error = 'An account with this email already exists.';
            } else {
                $hash = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $pdo->prepare("INSERT INTO users (full_name, email, phone, password_hash, role) VALUES (?,?,?,?, 'patient')");
                $stmt->execute([$fullName, $email, $phone, $hash]);

                $userId = (int) $pdo->lastInsertId();
                session_regenerate_id(true);
                $_SESSION['user_id'] = $userId;
                header('Location: index.php');
                exit;
            }
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>
<section class="auth-wrap">
  <canvas id="hero-canvas" style="position:absolute;inset:0;opacity:.4;"></canvas>
  <div class="glass-card auth-card">
    <h2>Create your account</h2>
    <p class="sub">Join HealthSheba for doctor visits, medicine delivery, and more.</p>
    <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
    <form method="post" action="register.php" novalidate>
      <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
      <div class="field">
        <label for="full_name">Full name</label>
        <input type="text" id="full_name" name="full_name" required maxlength="150" autocomplete="name">
      </div>
      <div class="field">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" required maxlength="150" autocomplete="email">
      </div>
      <div class="field">
        <label for="phone">Phone (optional)</label>
        <input type="text" id="phone" name="phone" maxlength="30" autocomplete="tel">
      </div>
      <div class="field">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required minlength="8" autocomplete="new-password">
      </div>
      <div class="field">
        <label for="confirm_password">Confirm password</label>
        <input type="password" id="confirm_password" name="confirm_password" required minlength="8" autocomplete="new-password">
      </div>
      <button type="submit" class="btn btn-primary btn-block"><?= icon('lock') ?> Sign Up</button>
    </form>
    <div class="auth-switch">Already have an account? <a href="login.php">Log in</a></div>
  </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
