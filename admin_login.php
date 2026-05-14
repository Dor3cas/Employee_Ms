<?php
require_once 'db.php';
session_start();
if (isset($_SESSION['admin_id'])) { header("Location: admin_dashboard.php"); exit; }

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $pass = $_POST['password'] ?? '';

    if (!$name || !$pass) {
        $error = 'Please enter your name and password.';
    } else {
        $db = getDB();
        $stmt = $db->prepare("SELECT id, password FROM admins WHERE name = ?");
        $stmt->bind_param("s", $name); $stmt->execute(); $stmt->store_result();
        $stmt->bind_result($id, $hashed); $stmt->fetch();

        if ($stmt->num_rows === 0 || !password_verify($pass, $hashed)) {
            $error = 'Invalid admin credentials.';
        } else {
            $_SESSION['admin_id'] = $id;
            $_SESSION['admin_name'] = $name;
            header("Location: admin_dashboard.php"); exit;
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
<title>Admin Login — UserVault</title>
<link rel="stylesheet" href="shared.css">
</head>
<body>
<div class="auth-page">
  <div class="auth-box">
    <div class="auth-logo">
      <div class="dot" style="background:#ef4444;">UV</div>
      <span class="brand">Admin Panel</span>
    </div>
    <h2>Admin Login</h2>
    <p class="hint">Restricted to authorised administrators only.</p>

    <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <form method="POST">
      <div class="field"><label>Admin Name</label><input type="text" name="name" placeholder="Admin" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required autofocus></div>
      <div class="field"><label>Password</label><input type="password" name="password" placeholder="Admin password" required></div>
      <button type="submit" class="btn btn-full" style="background:#ef4444;color:#fff;margin-top:8px;padding:11px;">Access Dashboard</button>
    </form>

    <div class="auth-footer"><a href="index.php">← Back to main site</a></div>
  </div>
</div>
</body>
</html>
