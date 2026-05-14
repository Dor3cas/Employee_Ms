<?php
require_once 'db.php';
session_start();
if (isset($_SESSION['user_id'])) { header("Location: dashboard.php"); exit; }

$error = $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $pass  = $_POST['password'] ?? '';
    $cpass = $_POST['confirm_password'] ?? '';

    if (!$name || !$email || !$phone || !$pass)
        $error = 'All fields are required.';
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL))
        $error = 'Please enter a valid email address.';
    elseif (strlen($pass) < 6)
        $error = 'Password must be at least 6 characters.';
    elseif ($pass !== $cpass)
        $error = 'Passwords do not match.';
    else {
        $db = getDB();
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email); $stmt->execute(); $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $error = 'An account with this email already exists.';
        } else {
            $h = password_hash($pass, PASSWORD_DEFAULT);
            $ins = $db->prepare("INSERT INTO users (name, email, phone, password) VALUES (?, ?, ?, ?)");
            $ins->bind_param("ssss", $name, $email, $phone, $h);
            $ins->execute() ? $success = 'Account created! You can now sign in.' : $error = 'Something went wrong. Try again.';
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
<title>Create Account — UserVault</title>
<link rel="stylesheet" href="shared.css">
</head>
<body>
<div class="auth-page">
  <div class="auth-box">
    <div class="auth-logo">
      <div class="dot">UV</div>
      <span class="brand">UserVault</span>
    </div>
    <h2>Create Account</h2>
    <p class="hint">Fill in your details to get started.</p>

    <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>

    <form method="POST">
      <div class="field"><label>Full Name</label><input type="text" name="name" placeholder="John Doe" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required></div>
      <div class="field"><label>Email Address</label><input type="email" name="email" placeholder="john@example.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required></div>
      <div class="field"><label>Phone Number</label><input type="tel" name="phone" placeholder="+250 700 000 000" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>" required></div>
      <div class="field"><label>Password</label><input type="password" name="password" placeholder="Minimum 6 characters" required></div>
      <div class="field"><label>Confirm Password</label><input type="password" name="confirm_password" placeholder="Repeat your password" required></div>
      <button type="submit" class="btn btn-primary btn-full">Create Account</button>
    </form>

    <div class="auth-footer">Already have an account? <a href="login.php">Sign in</a></div>
  </div>
</div>
</body>
</html>
