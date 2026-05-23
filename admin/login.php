<?php
session_start();
require_once '../includes/db.php';

if (isset($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $error = 'Please enter username and password.';
    } else {
        $stmt = $conn->prepare("SELECT * FROM admins WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        if ($result && $result['password'] === $password) {
            $_SESSION['admin_id'] = $result['id'];
            $_SESSION['admin_username'] = $result['username'];
            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Invalid username or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login - LearnSpace</title>
<link rel="stylesheet" href="../assets/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
body {
  background: linear-gradient(135deg, #1a0000 0%, #3d0000 50%, #1a0000 100%);
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
}

.login-container {
  width: 100%;
  max-width: 440px;
  padding: 20px;
}

.login-card {
  background: white;
  border-radius: 24px;
  padding: 48px 40px;
  box-shadow: 0 30px 80px rgba(0,0,0,0.4);
}

.login-logo {
  text-align: center;
  margin-bottom: 8px;
}

.login-logo .logo-big {
  width: 72px;
  height: 72px;
  background: var(--primary);
  border-radius: 20px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 2rem;
  margin-bottom: 12px;
  box-shadow: 0 8px 24px rgba(204,0,0,0.4);
}

.login-logo h2 {
  font-size: 1.6rem;
  color: var(--black);
}

.login-logo h2 span { color: var(--primary); }

.login-logo p {
  color: var(--gray);
  font-size: 0.88rem;
  margin-top: 4px;
}

.admin-badge {
  background: var(--primary-bg);
  border: 1px solid rgba(204,0,0,0.2);
  border-radius: 50px;
  padding: 6px 16px;
  color: var(--primary);
  font-size: 0.8rem;
  font-weight: 700;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  margin: 16px 0 24px;
}

.input-icon-wrap {
  position: relative;
}

.input-icon-wrap .form-control {
  padding-left: 44px;
}

.input-icon-wrap .input-icon {
  position: absolute;
  left: 14px;
  top: 50%;
  transform: translateY(-50%);
  color: var(--gray);
  font-size: 1rem;
}

.login-btn {
  width: 100%;
  padding: 14px;
  font-size: 1rem;
  border-radius: 12px;
  margin-top: 8px;
}

.back-link {
  text-align: center;
  margin-top: 20px;
  font-size: 0.88rem;
  color: var(--gray);
}

.back-link a { color: var(--primary); font-weight: 700; }
</style>
</head>
<body>
<div class="login-container">
  <div class="login-card">
    <div class="login-logo">
      <div class="logo-big">🛡️</div>
      <img src="../assets/images/logo.png" alt="LearnSpace" >
      <p>Administration Portal</p>
      <div class="admin-badge"><i class="fas fa-shield-alt"></i> Admin Access Only</div>
    </div>

    <?php if ($error): ?>
    <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
      <div class="form-group">
        <label>Username</label>
        <div class="input-icon-wrap">
          <i class="fas fa-user input-icon"></i>
          <input type="text" name="username" class="form-control" placeholder="Enter admin username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
        </div>
      </div>
      <div class="form-group">
        <label>Password</label>
        <div class="input-icon-wrap">
          <i class="fas fa-lock input-icon"></i>
          <input type="password" name="password" class="form-control" placeholder="Enter password" required>
        </div>
      </div>
      <button type="submit" class="btn btn-primary login-btn">
        <i class="fas fa-sign-in-alt"></i> Login to Admin Panel
      </button>
    </form>

    <div class="back-link">
      <a href="../index.php"><i class="fas fa-arrow-left"></i> Back to Homepage</a>
    </div>
  </div>
</div>
<script src='https://cdn.jotfor.ms/agent/embedjs/019d85b564bd7b53bf17ecb93621ce83ef1b/embed.js'>
</script>
</body>
</html>
