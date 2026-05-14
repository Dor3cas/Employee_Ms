<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>UserVault</title>
<link rel="stylesheet" href="shared.css">
<style>
  .home-page {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 24px;
  }
  .home-box {
    background: var(--white);
    border-radius: 12px;
    box-shadow: 0 1px 4px rgba(0,0,0,.08), 0 6px 20px rgba(0,0,0,.06);
    padding: 48px 40px;
    width: 100%;
    max-width: 420px;
    text-align: center;
  }
  .home-icon {
    width: 56px; height: 56px;
    background: var(--primary);
    border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 20px;
    font-size: 24px;
  }
  .home-box h1 { font-size: 26px; font-weight: 700; margin-bottom: 8px; }
  .home-box p { font-size: 15px; color: var(--muted); margin-bottom: 32px; line-height: 1.6; }
  .home-btns { display: flex; flex-direction: column; gap: 12px; }
  .home-btns .btn { padding: 12px; font-size: 15px; }
  .admin-link {
    margin-top: 28px;
    padding-top: 20px;
    border-top: 1px solid var(--border);
    font-size: 14px;
    color: var(--muted);
  }
  .admin-link a { font-weight: 500; }
</style>
</head>
<body>
<div class="home-page">
  <div class="home-box">
    <div class="home-icon">👤</div>
    <h1>UserVault</h1>
    <p>A simple system to manage your account. Sign up or log in to get started.</p>

    <div class="home-btns">
      <a href="register.php" class="btn btn-primary">Create an Account</a>
      <a href="login.php" class="btn btn-outline">Sign In</a>
    </div>

    <div class="admin-link">
      Administrator? <a href="admin_login.php">Go to Admin Panel →</a>
    </div>
  </div>
</div>
</body>
</html>
