<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/icons.php';
$pageTitle = 'Log In';

if (is_logged_in()) { header('Location: index.php'); exit; }

$error = null;
$pdo = getPDO();

// very lightweight brute-force throttle (per session)
$_SESSION['login_attempts'] = $_SESSION['login_attempts'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['csrf_token'] ?? '')) {
        $error = 'Session expired. Please refresh and try again.';
    } elseif ($_SESSION['login_attempts'] >= 6) {
        $error = 'Too many attempts. Please wait a moment and try again.';
    } else {
        $email    = strtolower(input('email'));
        $password = (string) ($_POST['password'] ?? '');

        $stmt = $pdo->prepare("SELECT id, full_name, password_hash FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['login_attempts'] = 0;
            header('Location: index.php');
            exit;
        } else {
            $_SESSION['login_attempts']++;
            $error = 'Invalid email or password.'; // generic on purpose
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>
<section class="auth-wrap">
  <canvas id="hero-canvas" style="position:absolute;inset:0;opacity:.4;"></canvas>
  <div class="glass-card auth-card">
    <h2>Welcome back</h2>
    <p class="sub">Log in to manage your appointments and orders.</p>
    <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
    <form method="post" action="login.php" novalidate>
      <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
      <div class="field">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" required autocomplete="email">
      </div>
      <div class="field">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required autocomplete="current-password">
      </div>
      <button type="submit" class="btn btn-primary btn-block"><?= icon('lock') ?> Log In</button>
    </form>
    <div class="auth-switch">New to Fariha? <a href="register.php">Create an account</a></div>
    <div class="auth-switch" style="margin-top:6px;font-size:.78rem;"></div>
  </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
