<?php
require_once 'db.php';
session_start();
if (isset($_SESSION['user_id'])) { header("Location: dashboard.php"); exit; }

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';

    if (!$email || !$pass) {
        $error = 'Please enter your email and password.';
    } else {
        $db = getDB();
        $stmt = $db->prepare("SELECT id, name, password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email); $stmt->execute(); $stmt->store_result();
        $stmt->bind_result($id, $name, $hashed); $stmt->fetch();

        if ($stmt->num_rows === 0 || !password_verify($pass, $hashed)) {
            $error = 'Invalid email or password.';
        } else {
            $_SESSION['user_id'] = $id;
            $_SESSION['user_name'] = $name;
            header("Location: dashboard.php"); exit;
        }
        $db->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sign In — UserVault</title>
<link rel="stylesheet" href="shared.css">
</head>
<body>
<div class="auth-page">
  <div class="auth-box">
    <div class="auth-logo">
      <div class="dot">UV</div>
      <span class="brand">UserVault</span>
    </div>
    <h2>Welcome Back</h2>
    <p class="hint">Sign in to access your dashboard.</p>

    <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <form method="POST">
      <div class="field"><label>Email Address</label><input type="email" name="email" placeholder="john@example.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required autofocus></div>
      <div class="field"><label>Password</label><input type="password" name="password" placeholder="Your password" required></div>
      <button type="submit" class="btn btn-primary btn-full">Sign In</button>
    </form>

    <div class="auth-footer">No account yet? <a href="register.php">Create one</a></div>
  </div>
</div>
</body>
</html>
