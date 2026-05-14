<?php
require_once 'db.php';
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }

$db = getDB();
$uid = $_SESSION['user_id'];

$stmt = $db->prepare("SELECT name, email, phone FROM users WHERE id = ?");
$stmt->bind_param("i", $uid); $stmt->execute();
$stmt->bind_result($name, $email, $phone); $stmt->fetch(); $stmt->close();

$error = $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $n = trim($_POST['name'] ?? '');
    $e = trim($_POST['email'] ?? '');
    $p = trim($_POST['phone'] ?? '');
    $newpw = $_POST['new_password'] ?? '';
    $curpw = $_POST['current_password'] ?? '';

    if (!$n || !$e || !$p)
        $error = 'Name, email and phone are required.';
    elseif (!filter_var($e, FILTER_VALIDATE_EMAIL))
        $error = 'Invalid email address.';
    else {
        $chk = $db->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $chk->bind_param("si", $e, $uid); $chk->execute(); $chk->store_result();
        if ($chk->num_rows > 0) {
            $error = 'That email is already used by another account.';
        } elseif ($newpw) {
            $vchk = $db->prepare("SELECT password FROM users WHERE id = ?");
            $vchk->bind_param("i", $uid); $vchk->execute();
            $vchk->bind_result($hashed); $vchk->fetch(); $vchk->close();
            if (!password_verify($curpw, $hashed))
                $error = 'Current password is incorrect.';
            elseif (strlen($newpw) < 6)
                $error = 'New password must be at least 6 characters.';
            else {
                $h = password_hash($newpw, PASSWORD_DEFAULT);
                $upd = $db->prepare("UPDATE users SET name=?,email=?,phone=?,password=? WHERE id=?");
                $upd->bind_param("ssssi", $n, $e, $p, $h, $uid); $upd->execute();
                $success = 'Profile and password updated!';
            }
        } else {
            $upd = $db->prepare("UPDATE users SET name=?,email=?,phone=? WHERE id=?");
            $upd->bind_param("sssi", $n, $e, $p, $uid); $upd->execute();
            $success = 'Profile updated successfully!';
        }
        if (!$error) {
            $_SESSION['user_name'] = $n;
            $name = $n; $email = $e; $phone = $p;
        }
    }
}

$db->close();
$initial = strtoupper(substr($name, 0, 1));
$hour = (int)date('H');
$greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Dashboard — UserVault</title>
<link rel="stylesheet" href="shared.css">
</head>
<body>
<div class="layout">

  <aside class="sidebar">
    <div class="sidebar-logo">
      <div class="dot">UV</div>
      <span class="brand">UserVault</span>
    </div>
    <nav class="nav">
      <a href="#" class="active">🏠 Dashboard</a>
      <a href="#edit">✏️ Edit Profile</a>
    </nav>
    <div class="sidebar-bottom">
      <div class="user-pill">
        <div class="av"><?= $initial ?></div>
        <div class="info">
          <div class="name"><?= htmlspecialchars($name) ?></div>
          <div class="role">User</div>
        </div>
      </div>
      <a href="logout.php" class="logout">🚪 Logout</a>
    </div>
  </aside>

  <main class="main">
    <div class="page-title"><?= $greeting ?>, <?= htmlspecialchars(explode(' ', $name)[0]) ?>! 👋</div>
    <div class="page-sub">Here's your account information.</div>

    <!-- Info tiles -->
    <div class="info-grid">
      <div class="info-tile">
        <div class="label">Full Name</div>
        <div class="value"><?= htmlspecialchars($name) ?></div>
      </div>
      <div class="info-tile">
        <div class="label">Email</div>
        <div class="value"><?= htmlspecialchars($email) ?></div>
      </div>
      <div class="info-tile">
        <div class="label">Phone</div>
        <div class="value"><?= htmlspecialchars($phone) ?></div>
      </div>
    </div>

    <!-- Edit profile -->
    <div class="card" id="edit">
      <div class="card-header"><h3>✏️ Edit Profile</h3></div>
      <div class="card-body">

        <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>

        <form method="POST">
          <div class="form-row">
            <div class="field">
              <label>Full Name</label>
              <input type="text" name="name" value="<?= htmlspecialchars($name) ?>" required>
            </div>
            <div class="field">
              <label>Phone Number</label>
              <input type="tel" name="phone" value="<?= htmlspecialchars($phone) ?>" required>
            </div>
            <div class="field span2">
              <label>Email Address</label>
              <input type="email" name="email" value="<?= htmlspecialchars($email) ?>" required>
            </div>
          </div>

          <hr class="divider">
          <p style="font-size:13px;color:var(--muted);margin-bottom:16px;">Leave password fields blank to keep your current password.</p>

          <div class="form-row">
            <div class="field">
              <label>Current Password</label>
              <input type="password" name="current_password" placeholder="Required to change password">
            </div>
            <div class="field">
              <label>New Password</label>
              <input type="password" name="new_password" placeholder="Min. 6 characters">
            </div>
          </div>

          <button type="submit" class="btn btn-primary">Save Changes</button>
        </form>
      </div>
    </div>
  </main>

</div>
</body>
</html>
