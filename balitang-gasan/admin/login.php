<?php
require_once __DIR__ . '/../config/db.php';

// Redirect if already logged in
if (is_logged_in()) {
    header('Location: ' . SITE_URL . '/admin/dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$email || !$password) {
        $error = 'Please fill in all fields.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user']    = [
                'id'   => $user['id'],
                'name' => $user['name'],
                'role' => $user['role'],
            ];
            // Redirect to dashboard
            $redirect = $_GET['redirect'] ?? SITE_URL . '/admin/dashboard.php';
            echo '<script>
                alert("Login successful! Welcome back, ' . addslashes($user['name']) . '!");
                window.location.href = "' . addslashes($redirect) . '";
            </script>';
            exit;
        } else {
            $error = 'Invalid email or password. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login — <?= SITE_NAME ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Playfair+Display:wght@700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/admin.css">
</head>
<body>
<div class="login-wrap">
  <div class="login-box">
    <div class="login-brand">
      <i class="bi bi-newspaper login-brand-icon"></i>
      <div class="login-brand-name">BALITANG<span> GASEÑO</span></div>
      <div class="login-sub"><i>Administrator Sign In</i></div>
    </div>


    <?php if ($error): ?>
      <div class="alert alert-danger alert-sm mb-3">
        <i class="bi bi-exclamation-circle me-1"></i><?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <form method="POST">
      <div class="mb-3">
        <label class="form-label">Email</label>
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-envelope"></i></span>
          <input type="email" name="email" class="form-control" placeholder=" "
                 value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required autofocus>
        </div>
      </div>
      <div class="mb-4">
        <label class="form-label">Password</label>
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-lock"></i></span>
          <input type="password" name="password" id="pwd" class="form-control" placeholder=" " required>
          <button type="button" class="input-group-text" onclick="togglePwd()"><i class="bi bi-eye" id="eye-icon"></i></button>
        </div>
      </div>
      <button type="submit" class="btn btn-danger w-100 fw-bold py-2">
        <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
      </button>
    </form>

    <hr class="my-3">
    <div class="text-center">
      <a href="<?= SITE_URL ?>/index.php" style="font-size:13px;color:#888;">
        <i class="bi bi-arrow-left me-1"></i>Back to Home Page
      </a>
    </div>

  </div>
</div>

<script>
function togglePwd() {
  const p = document.getElementById('pwd');
  const e = document.getElementById('eye-icon');
  if (p.type === 'password') { p.type = 'text'; e.className = 'bi bi-eye-slash'; }
  else { p.type = 'password'; e.className = 'bi bi-eye'; }
}
</script>
</body>
</html>
