<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/icons.php';
$pageTitle = 'My Profile';
require_login();

$pdo = getPDO();
$error = null;
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['csrf_token'] ?? '')) {
        $error = 'Session expired. Please refresh and try again.';
    } else {
        $fullName = input('full_name');
        $phone    = input('phone');
        $address  = input('address');
        $city     = input('city');
        $dob      = input('date_of_birth');
        $gender   = in_array(input('gender'), ['male','female','other'], true) ? input('gender') : null;
        $blood    = input('blood_group');

        if ($fullName === '') {
            $error = 'Full name is required.';
        } else {
            $pdo->prepare("UPDATE users SET full_name = ?, phone = ? WHERE id = ?")
                ->execute([$fullName, $phone, $_SESSION['user_id']]);

            $existing = $pdo->prepare("SELECT id FROM user_details WHERE user_id = ?");
            $existing->execute([$_SESSION['user_id']]);
            $dobValue = $dob !== '' ? $dob : null;

            if ($existing->fetch()) {
                $pdo->prepare("UPDATE user_details SET address=?, city=?, date_of_birth=?, gender=?, blood_group=? WHERE user_id=?")
                    ->execute([$address, $city, $dobValue, $gender, $blood, $_SESSION['user_id']]);
            } else {
                $pdo->prepare("INSERT INTO user_details (user_id, address, city, date_of_birth, gender, blood_group) VALUES (?,?,?,?,?,?)")
                    ->execute([$_SESSION['user_id'], $address, $city, $dobValue, $gender, $blood]);
            }
            $success = 'Your profile has been updated.';
        }
    }
}

$stmt = $pdo->prepare("
    SELECT u.full_name, u.email, u.phone, d.address, d.city, d.date_of_birth, d.gender, d.blood_group
    FROM users u
    LEFT JOIN user_details d ON d.user_id = u.id
    WHERE u.id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$profile = $stmt->fetch();

require_once __DIR__ . '/includes/header.php';
?>
<section class="section" style="padding-top:130px;">
  <div class="container" style="max-width:640px;">
    <span class="eyebrow">Your Account</span>
    <h2 class="section-title">My profile</h2>
    <p class="section-sub">Keep your contact and personal details up to date.</p>

    <div class="glass-card" style="padding:36px;">
      <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
      <?php if ($success): ?><div class="alert alert-success"><?= e($success) ?></div><?php endif; ?>

      <form method="post">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

        <div class="field">
          <label for="full_name">Full name</label>
          <input type="text" id="full_name" name="full_name" required maxlength="150" value="<?= e($profile['full_name']) ?>">
        </div>
        <div class="field">
          <label>Email</label>
          <input type="email" value="<?= e($profile['email']) ?>" disabled>
        </div>
        <div class="field">
          <label for="phone">Phone</label>
          <input type="text" id="phone" name="phone" maxlength="30" value="<?= e($profile['phone']) ?>">
        </div>
        <div class="field">
          <label for="address">Address</label>
          <input type="text" id="address" name="address" maxlength="255" value="<?= e($profile['address']) ?>">
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
          <div class="field">
            <label for="city">City</label>
            <input type="text" id="city" name="city" maxlength="100" value="<?= e($profile['city']) ?>">
          </div>
          <div class="field">
            <label for="date_of_birth">Date of birth</label>
            <input type="date" id="date_of_birth" name="date_of_birth" value="<?= e($profile['date_of_birth']) ?>">
          </div>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
          <div class="field">
            <label for="gender">Gender</label>
            <select id="gender" name="gender">
              <option value="">Prefer not to say</option>
              <option value="male" <?= $profile['gender']==='male'?'selected':'' ?>>Male</option>
              <option value="female" <?= $profile['gender']==='female'?'selected':'' ?>>Female</option>
              <option value="other" <?= $profile['gender']==='other'?'selected':'' ?>>Other</option>
            </select>
          </div>
          <div class="field">
            <label for="blood_group">Blood group</label>
            <input type="text" id="blood_group" name="blood_group" maxlength="5" placeholder="e.g. O+" value="<?= e($profile['blood_group']) ?>">
          </div>
        </div>

        <button type="submit" class="btn btn-primary btn-block">Save Changes</button>
      </form>
    </div>
  </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
