<?php
require_once __DIR__ . '/../config/db.php';
require_login();

$user_id = current_user()['id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_profile') {
        $name  = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        if (!$name || !$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please provide a valid name and email.';
        } else {
            // Check email not taken by another user
            $chk = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $chk->execute([$email, $user_id]);
            if ($chk->fetch()) {
                $error = 'That email is already in use by another account.';
            } else {
                $pdo->prepare("UPDATE users SET name=?, email=? WHERE id=?")->execute([$name, $email, $user_id]);
                $_SESSION['user']['name'] = $name;
                $success = 'Profile updated successfully.';
                $user['name']  = $name;
                $user['email'] = $email;
            }
        }

    } elseif ($action === 'change_password') {
        $current = $_POST['current_password'] ?? '';
        $new_pw  = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if (!$current || !$new_pw || !$confirm) {
            $error = 'All password fields are required.';
        } elseif (!password_verify($current, $user['password'])) {
            $error = 'Current password is incorrect.';
        } elseif ($new_pw !== $confirm) {
            $error = 'New passwords do not match.';
        } elseif (strlen($new_pw) < 8) {
            $error = 'Password must be at least 8 characters.';
        } else {
            $hash = password_hash($new_pw, PASSWORD_DEFAULT);
            $pdo->prepare("UPDATE users SET password=? WHERE id=?")->execute([$hash, $user_id]);
            $success = 'Password changed successfully.';
        }
    }
}

$base = SITE_URL;
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile — <?= SITE_NAME ?> Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Playfair+Display:wght@700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= $base ?>/assets/css/admin.css">
  </head>
  <body>
    <?php include __DIR__ . '/includes/sidebar.php'; ?>

    <div class="admin-main">
      <div class="admin-topbar">
        <div class="d-flex align-items-center gap-3">
          <button class="sidebar-toggle" onclick="toggleSidebar()"><i class="bi bi-list"></i></button>
          <span class="admin-topbar-title"><i class="bi bi-person-gear me-2 text-danger"></i>My Profile</span>
        </div>
      </div>

      <div class="admin-content">
        <?php if ($error): ?>
          <div class="alert alert-danger alert-sm mb-3"><i class="bi bi-exclamation-circle me-1"></i><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
          <div class="alert alert-success alert-sm alert-auto-dismiss mb-3"><i class="bi bi-check-circle me-1"></i><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <div class="row g-4">
          <!-- Profile Info -->
          <div class="col-lg-6">
            <div class="admin-card">
              <div class="admin-card-header"><i class="bi bi-person-circle"></i> Profile Information</div>
              <div class="admin-card-body">
                <div class="text-center mb-4">
                  <div style="width:72px;height:72px;background:var(--red);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:32px;color:#fff;font-weight:800;margin:0 auto 10px;">
                    <?= strtoupper(substr($user['name'] ?? 'A', 0, 1)) ?>
                  </div>
                  <span class="status-badge <?= $user['role'] ?? 'editor' ?>"><?= ucfirst($user['role'] ?? 'editor') ?></span>
                </div>
                <form method="POST">
                  <input type="hidden" name="action" value="update_profile">
                  <div class="mb-3">
                    <label class="form-label">Position</label>
                    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['name'] ?? '') ?>" required>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Role</label>
                    <input type="text" class="form-control" value="<?= ucfirst($user['role'] ?? 'editor') ?>" disabled>
                  </div>
                  <div class="mt-3 d-grid">
                    <button type="submit" class="btn btn-danger fw-bold"><i class="bi bi-floppy me-1"></i> Update Profile</button>
                  </div>
                </form>
              </div>
            </div>
          </div>

          <!-- Change Password -->
          <div class="col-lg-6">
            <div class="admin-card">
              <div class="admin-card-header"><i class="bi bi-lock"></i> Change Password</div>
              <div class="admin-card-body">
                <form method="POST">
                  <input type="hidden" name="action" value="change_password">
                  <div class="mb-3">
                    <label class="form-label">Current Password</label>
                    <input type="password" name="current_password" class="form-control" required placeholder="••••••••">
                  </div>
                  <div class="mb-3">
                    <label class="form-label">New Password</label>
                    <input type="password" name="new_password" class="form-control" required placeholder="Min. 8 characters" minlength="8">
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Confirm New Password</label>
                    <input type="password" name="confirm_password" class="form-control" required placeholder="Re-enter new password">
                  </div>
                  <div class="d-grid">
                    <button type="submit" class="btn btn-outline-danger fw-bold"><i class="bi bi-key me-1"></i> Change Password</button>
                  </div>
                </form>
              </div>
            </div>

            <!-- Stats -->
            <div class="admin-card mt-4">
              <div class="admin-card-header"><i class="bi bi-bar-chart"></i> My Activity</div>
              <div class="admin-card-body">
                <?php
                $my_articles = $pdo->prepare("SELECT COUNT(*) FROM articles WHERE author_id = ?");
                $my_articles->execute([$user_id]);
                $my_count = $my_articles->fetchColumn();
                $my_views = $pdo->prepare("SELECT COALESCE(SUM(views),0) FROM articles WHERE author_id = ?");
                $my_views->execute([$user_id]);
                $my_view_count = $my_views->fetchColumn();
                ?>
                <div class="row text-center g-3">
                  <div class="col-6">
                    <div style="font-size:28px;font-weight:800;color:var(--red);"><?= $my_count ?></div>
                    <div style="font-size:12px;color:var(--text-muted);text-transform:uppercase;letter-spacing:.06em;">My Articles</div>
                  </div>
                  <div class="col-6">
                    <div style="font-size:28px;font-weight:800;color:var(--red);"><?= number_format($my_view_count) ?></div>
                    <div style="font-size:12px;color:var(--text-muted);text-transform:uppercase;letter-spacing:.06em;">Total Views</div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function toggleSidebar() { document.getElementById('adminSidebar').classList.toggle('open'); }
    document.querySelectorAll('.alert-auto-dismiss').forEach(el => {
      setTimeout(() => { el.style.opacity='0'; setTimeout(()=>el.remove(),500); }, 4000);
    });
    </script>
  </body>
</html>
