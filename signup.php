<?php // signup.php — create account; first user claims existing unowned items
require_once __DIR__ . '/includes/db.php';
if (currentUser()) { header('Location: closet.php'); exit; }
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  try {
    $name = trim($_POST['name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $pass = $_POST['password'] ?? '';
    if (!preg_match('/^[A-Za-z0-9 ]{2,40}$/', $name)) throw new Exception("Name: 2-40 letters/numbers.");
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) throw new Exception("Enter a valid email.");
    if (strlen($pass) < 6) throw new Exception("Password must be 6+ characters.");
    if ($pass !== ($_POST['confirm'] ?? '')) throw new Exception("Passwords do not match.");
    $db = getDB();
    $exists = $db->prepare("SELECT id FROM users WHERE email=?"); $exists->execute([$email]);
    if ($exists->fetch()) throw new Exception("That email already has an account. Log in instead.");
    $stmt = $db->prepare("INSERT INTO users(name,email,password_hash,created_at) VALUES(?,?,?,?)");
    $stmt->execute([$name, $email, password_hash($pass, PASSWORD_DEFAULT), date('Y-m-d H:i:s')]);
    $uid = dbLastId($db, 'users');
    // First account on this install inherits the demo/legacy items
    if ((int)$db->query("SELECT COUNT(*) FROM users")->fetchColumn() === 1) {
      $db->exec("UPDATE items SET user_id=$uid WHERE user_id IS NULL");
      $db->exec("UPDATE outfits SET user_id=$uid WHERE user_id IS NULL");
    }
    $_SESSION['user'] = ['id' => $uid, 'name' => $name, 'email' => $email];
    flash("Welcome, $name! Your closet is ready.");
    header('Location: closet.php'); exit;
  } catch (Exception $ex) { flash($ex->getMessage(), 'error'); }
}
include __DIR__ . '/includes/header.php';
?>
<div class="auth-wrap">
  <form method="post" id="signupForm" class="card p-3 auth-card">
    <h2>Sign up</h2>
    <p class="sub">Your clothes stay yours — private to your account.</p>
    <div class="mb-2"><label>Name*</label><input name="name" class="form-control" required minlength="2" maxlength="40" placeholder="Ava"></div>
    <div class="mb-2"><label>Email*</label><input name="email" type="email" class="form-control" required placeholder="ava@mail.com"></div>
    <div class="mb-2"><label>Password* (6+ characters)</label><input name="password" type="password" class="form-control" required minlength="6"></div>
    <div class="mb-2"><label>Confirm password*</label><input name="confirm" type="password" class="form-control" required minlength="6"></div>
    <button class="btn btn-primary w-100">Create account</button>
    <p class="auth-swap">Have an account? <a href="login.php">Log in</a></p>
  </form>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
