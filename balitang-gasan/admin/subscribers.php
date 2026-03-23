<?php
require_once __DIR__ . '/../config/db.php';
require_login();

$page = max(1, (int)($_GET['page'] ?? 1));
$per  = 25;
$off  = ($page - 1) * $per;
$search = trim($_GET['search'] ?? '');

$where  = "1=1";
$params = [];
if ($search) {
    $where .= " AND email LIKE ?";
    $params[] = '%' . $search . '%';
}

$total = (int)$pdo->prepare("SELECT COUNT(*) FROM subscribers WHERE $where")->execute($params);
$count_s = $pdo->prepare("SELECT COUNT(*) FROM subscribers WHERE $where");
$count_s->execute($params);
$total = (int)$count_s->fetchColumn();
$pages = ceil($total / $per);

$stmt = $pdo->prepare("SELECT * FROM subscribers WHERE $where ORDER BY subscribed_at DESC LIMIT $per OFFSET $off");
$stmt->execute($params);
$subscribers = $stmt->fetchAll();

// Handle delete
if (isset($_GET['delete'])) {
    $del_id = (int)$_GET['delete'];
    $pdo->prepare("DELETE FROM subscribers WHERE id = ?")->execute([$del_id]);
    $_SESSION['success'] = 'Subscriber removed.';
    header('Location: ' . SITE_URL . '/admin/subscribers.php');
    exit;
}

$success = $_SESSION['success'] ?? '';
unset($_SESSION['success']);

$base = SITE_URL;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Subscribers — <?= SITE_NAME ?> Admin</title>
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
      <span class="admin-topbar-title"><i class="bi bi-envelope-check me-2 text-danger"></i>Newsletter Subscribers</span>
    </div>
    <div class="admin-topbar-actions">
      <span style="font-size:13px;color:var(--text-muted);"><i class="bi bi-people me-1"></i><?= $total ?> total subscribers</span>
    </div>
  </div>

  <div class="admin-content">
    <?php if ($success): ?>
      <div class="alert alert-success alert-sm alert-auto-dismiss mb-3"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="GET" class="table-toolbar mb-0">
      <input type="text" name="search" class="form-control" placeholder="Search by email…" value="<?= htmlspecialchars($search) ?>">
      <button type="submit" class="btn btn-danger px-3"><i class="bi bi-search"></i></button>
      <a href="<?= $base ?>/admin/subscribers.php" class="btn btn-outline-secondary">Clear</a>
    </form>

    <div class="admin-table">
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead>
            <tr>
              <th>#</th>
              <th>Email Address</th>
              <th>Subscribed On</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($subscribers): ?>
              <?php foreach ($subscribers as $i => $sub): ?>
              <tr>
                <td><?= $off + $i + 1 ?></td>
                <td>
                  <a href="mailto:<?= htmlspecialchars($sub['email']) ?>" style="font-size:13px;">
                    <?= htmlspecialchars($sub['email']) ?>
                  </a>
                </td>
                <td style="font-size:13px;color:var(--text-muted);">
                  <?= date('F j, Y · g:i a', strtotime($sub['subscribed_at'])) ?>
                </td>
                <td>
                  <a href="?delete=<?= $sub['id'] ?>&search=<?= urlencode($search) ?>"
                     class="btn btn-sm btn-outline-danger"
                     onclick="return confirm('Remove this subscriber?')" title="Remove">
                    <i class="bi bi-trash"></i>
                  </a>
                </td>
              </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="4" class="text-center py-5 text-muted">
                  <i class="bi bi-envelope-x" style="font-size:32px;display:block;margin-bottom:8px;color:#ddd;"></i>
                  No subscribers found.
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

      <?php if ($pages > 1): ?>
      <div class="p-3 border-top">
        <nav>
          <ul class="pagination mb-0 justify-content-end">
            <?php for ($i = 1; $i <= $pages; $i++): ?>
              <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
              </li>
            <?php endfor; ?>
          </ul>
        </nav>
      </div>
      <?php endif; ?>
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
