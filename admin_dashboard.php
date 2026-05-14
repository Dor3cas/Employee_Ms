<?php
require_once 'db.php';
session_start();
if (!isset($_SESSION['admin_id'])) { header("Location: admin_login.php"); exit; }

$db = getDB();
$admin_name = $_SESSION['admin_name'];
$action = $_GET['action'] ?? 'list';
$msg = $msg_type = '';

// DELETE
if ($action === 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $db->prepare("DELETE FROM users WHERE id = ?")->bind_param("i", $id) || null;
    $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $id); $stmt->execute();
    $msg = 'User deleted.'; $msg_type = 'success'; $action = 'list';
}

// CREATE
if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $n = trim($_POST['name'] ?? ''); $e = trim($_POST['email'] ?? '');
    $p = trim($_POST['phone'] ?? ''); $pw = $_POST['password'] ?? '';
    if (!$n || !$e || !$p || !$pw) { $msg = 'All fields required.'; $msg_type = 'error'; }
    elseif (!filter_var($e, FILTER_VALIDATE_EMAIL)) { $msg = 'Invalid email.'; $msg_type = 'error'; }
    else {
        $chk = $db->prepare("SELECT id FROM users WHERE email = ?");
        $chk->bind_param("s", $e); $chk->execute(); $chk->store_result();
        if ($chk->num_rows > 0) { $msg = 'Email already exists.'; $msg_type = 'error'; }
        else {
            $h = password_hash($pw, PASSWORD_DEFAULT);
            $ins = $db->prepare("INSERT INTO users (name,email,phone,password) VALUES (?,?,?,?)");
            $ins->bind_param("ssss", $n, $e, $p, $h); $ins->execute();
            $msg = 'User created.'; $msg_type = 'success'; $action = 'list';
        }
    }
}

// UPDATE
if ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $uid = (int)$_POST['id'];
    $n = trim($_POST['name'] ?? ''); $e = trim($_POST['email'] ?? '');
    $p = trim($_POST['phone'] ?? ''); $pw = $_POST['password'] ?? '';
    if (!$n || !$e || !$p) { $msg = 'Name, email and phone required.'; $msg_type = 'error'; }
    elseif (!filter_var($e, FILTER_VALIDATE_EMAIL)) { $msg = 'Invalid email.'; $msg_type = 'error'; }
    else {
        $chk = $db->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $chk->bind_param("si", $e, $uid); $chk->execute(); $chk->store_result();
        if ($chk->num_rows > 0) { $msg = 'Email used by another user.'; $msg_type = 'error'; }
        else {
            if ($pw) {
                $h = password_hash($pw, PASSWORD_DEFAULT);
                $upd = $db->prepare("UPDATE users SET name=?,email=?,phone=?,password=? WHERE id=?");
                $upd->bind_param("ssssi", $n, $e, $p, $h, $uid);
            } else {
                $upd = $db->prepare("UPDATE users SET name=?,email=?,phone=? WHERE id=?");
                $upd->bind_param("sssi", $n, $e, $p, $uid);
            }
            $upd->execute();
            $msg = 'User updated.'; $msg_type = 'success'; $action = 'list';
        }
    }
}

// FETCH EDIT USER
$edit_user = null;
if ($action === 'edit' && isset($_GET['id'])) {
    $eid = (int)$_GET['id'];
    $stmt = $db->prepare("SELECT id,name,email,phone FROM users WHERE id=?");
    $stmt->bind_param("i", $eid); $stmt->execute();
    $edit_user = $stmt->get_result()->fetch_assoc();
    if (!$edit_user) $action = 'list';
}

// ALL USERS
$users = [];
$res = $db->query("SELECT id,name,email,phone,created_at FROM users ORDER BY created_at DESC");
while ($row = $res->fetch_assoc()) $users[] = $row;
$total = count($users);
$db->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard — UserVault</title>
<link rel="stylesheet" href="shared.css">
</head>
<body>
<div class="layout">

  <aside class="sidebar">
    <div class="sidebar-logo">
      <div class="dot" style="background:#ef4444;">UV</div>
      <span class="brand">Admin Panel</span>
    </div>
    <nav class="nav">
      <a href="admin_dashboard.php" class="<?= $action==='list'?'active':'' ?>">👥 All Users</a>
      <a href="admin_dashboard.php?action=create" class="<?= $action==='create'?'active':'' ?>">➕ Add User</a>
    </nav>
    <div class="sidebar-bottom">
      <div class="user-pill">
        <div class="av" style="background:#ef4444;"><?= strtoupper(substr($admin_name,0,1)) ?></div>
        <div class="info">
          <div class="name"><?= htmlspecialchars($admin_name) ?></div>
          <div class="role">Administrator</div>
        </div>
      </div>
      <a href="admin_logout.php" class="logout">🚪 Logout</a>
    </div>
  </aside>

  <main class="main">

    <?php if ($msg): ?>
      <div class="alert alert-<?= $msg_type ?>"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <?php if ($action === 'list'): ?>

      <div class="page-title">User Management</div>
      <div class="page-sub">Total users: <strong><?= $total ?></strong></div>

      <div class="card">
        <div class="card-header">
          <h3>All Registered Users</h3>
          <a href="admin_dashboard.php?action=create" class="btn btn-primary btn-sm">+ Add User</a>
        </div>
        <?php if (empty($users)): ?>
          <div class="empty-state">No users registered yet.</div>
        <?php else: ?>
        <div class="table-wrap">
          <table>
            <thead>
              <tr><th>#</th><th>Name</th><th>Email</th><th>Phone</th><th>Registered</th><th>Actions</th></tr>
            </thead>
            <tbody>
              <?php foreach ($users as $i => $u): ?>
              <tr>
                <td style="color:var(--muted);font-size:13px;"><?= $i+1 ?></td>
                <td>
                  <div class="td-user">
                    <div class="td-av"><?= strtoupper(substr($u['name'],0,1)) ?></div>
                    <?= htmlspecialchars($u['name']) ?>
                  </div>
                </td>
                <td><?= htmlspecialchars($u['email']) ?></td>
                <td><?= htmlspecialchars($u['phone']) ?></td>
                <td style="color:var(--muted);font-size:13px;"><?= date('M j, Y', strtotime($u['created_at'])) ?></td>
                <td>
                  <div class="actions">
                    <a href="admin_dashboard.php?action=edit&id=<?= $u['id'] ?>" class="btn btn-outline btn-sm">Edit</a>
                    <a href="admin_dashboard.php?action=delete&id=<?= $u['id'] ?>"
                       class="btn btn-danger btn-sm"
                       onclick="return confirm('Delete <?= htmlspecialchars(addslashes($u['name'])) ?>?')">Delete</a>
                  </div>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <?php endif; ?>
      </div>

    <?php elseif ($action === 'create'): ?>

      <div class="page-title">Add New User</div>
      <div class="page-sub">Create a new user account.</div>

      <div class="card">
        <div class="card-header">
          <h3>User Details</h3>
          <a href="admin_dashboard.php" class="btn btn-ghost btn-sm">← Back</a>
        </div>
        <div class="card-body">
          <form method="POST" action="admin_dashboard.php?action=create">
            <div class="form-row">
              <div class="field"><label>Full Name</label><input type="text" name="name" value="<?= htmlspecialchars($_POST['name']??'') ?>" required></div>
              <div class="field"><label>Phone Number</label><input type="tel" name="phone" value="<?= htmlspecialchars($_POST['phone']??'') ?>" required></div>
              <div class="field span2"><label>Email Address</label><input type="email" name="email" value="<?= htmlspecialchars($_POST['email']??'') ?>" required></div>
              <div class="field span2"><label>Password</label><input type="password" name="password" placeholder="Minimum 6 characters" required></div>
            </div>
            <button type="submit" class="btn btn-primary">Create User</button>
          </form>
        </div>
      </div>

    <?php elseif ($action === 'edit' && $edit_user): ?>

      <div class="page-title">Edit User</div>
      <div class="page-sub">Editing: <?= htmlspecialchars($edit_user['name']) ?></div>

      <div class="card">
        <div class="card-header">
          <h3>User Details</h3>
          <a href="admin_dashboard.php" class="btn btn-ghost btn-sm">← Back</a>
        </div>
        <div class="card-body">
          <form method="POST" action="admin_dashboard.php?action=update">
            <input type="hidden" name="id" value="<?= $edit_user['id'] ?>">
            <div class="form-row">
              <div class="field"><label>Full Name</label><input type="text" name="name" value="<?= htmlspecialchars($edit_user['name']) ?>" required></div>
              <div class="field"><label>Phone Number</label><input type="tel" name="phone" value="<?= htmlspecialchars($edit_user['phone']) ?>" required></div>
              <div class="field span2"><label>Email Address</label><input type="email" name="email" value="<?= htmlspecialchars($edit_user['email']) ?>" required></div>
              <div class="field span2">
                <label>New Password <span style="font-weight:400;font-size:12px;color:var(--muted);">(leave blank to keep current)</span></label>
                <input type="password" name="password" placeholder="Enter new password">
              </div>
            </div>
            <button type="submit" class="btn btn-primary">Save Changes</button>
          </form>
        </div>
      </div>

    <?php endif; ?>
  </main>

</div>
</body>
</html>
