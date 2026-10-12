<?php // login.php
require_once __DIR__ . '/includes/db.php';
if (currentUser()) { header('Location: closet.php'); exit; }
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  try {
    $email = strtolower(trim($_POST['email'] ?? ''));
    $pass = $_POST['password'] ?? '';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) throw new Exception("Enter a valid email.");
    $db = getDB();
    $s = $db->prepare("SELECT * FROM users WHERE email=?"); $s->execute([$email]);
    $u = $s->fetch(PDO::FETCH_ASSOC);
    if (!$u || !password_verify($pass, $u['password_hash'])) throw new Exception("Wrong email or password.");
    $_SESSION['user'] = ['id' => (int)$u['id'], 'name' => $u['name'], 'email' => $u['email']];
    flash("Welcome back, {$u['name']}!");
    header('Location: closet.php'); exit;
  } catch (Exception $ex) { flash($ex->getMessage(), 'error'); }
}
include __DIR__ . '/includes/header.php';
?>
<div class="auth-wrap">
  <form method="post" class="card p-3 auth-card">
    <h2>Log in</h2>
    <p class="sub">See only your clothes and your fits.</p>
    <div class="mb-2"><label>Email*</label><input name="email" type="email" class="form-control" required placeholder="ava@mail.com"></div>
    <div class="mb-2"><label>Password*</label><input name="password" type="password" class="form-control" required></div>
    <button class="btn btn-primary w-100">Log in</button>
    <p class="auth-swap">New here? <a href="signup.php">Create an account</a></p>
  </form>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
