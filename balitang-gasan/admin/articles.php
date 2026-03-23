<?php
require_once __DIR__ . '/../config/db.php';
require_login();

$page    = max(1, (int)($_GET['page'] ?? 1));
$per     = 15;
$off     = ($page - 1) * $per;
$search  = trim($_GET['search'] ?? '');
$status  = $_GET['status'] ?? '';
$cat_id  = (int)($_GET['cat'] ?? 0);

// Build WHERE clause
$where  = "1=1";
$params = [];
if ($search) {
    $where .= " AND (a.title LIKE ? OR a.byline LIKE ?)";
    $like   = '%' . $search . '%';
    $params[] = $like;
    $params[] = $like;
}
if (in_array($status, ['published', 'draft'])) {
    $where .= " AND a.status = ?";
    $params[] = $status;
}
if ($cat_id) {
    $where .= " AND a.category_id = ?";
    $params[] = $cat_id;
}

// Count
$count_stmt = $pdo->prepare("SELECT COUNT(*) FROM articles a WHERE $where");
$count_stmt->execute($params);
$total = (int)$count_stmt->fetchColumn();
$pages = ceil($total / $per);

// Articles
$arts_stmt = $pdo->prepare("
    SELECT a.id, a.title, a.slug, a.status, a.views, a.published_at, a.created_at,
           c.name AS cat_name, c.id AS cat_id, u.name AS author_name
    FROM articles a
    JOIN categories c ON c.id = a.category_id
    JOIN users u ON u.id = a.author_id
    WHERE $where
    ORDER BY a.created_at DESC
    LIMIT $per OFFSET $off
");
$arts_stmt->execute($params);
$articles = $arts_stmt->fetchAll();

// Categories for filter
$all_cats = $pdo->query("SELECT id, name FROM categories ORDER BY name")->fetchAll();

$success_msg = $_SESSION['success'] ?? '';
$error_msg   = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);

$base = SITE_URL;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Articles — <?= SITE_NAME ?> Admin</title>
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
      <span class="admin-topbar-title"><i class="bi bi-newspaper me-2 text-danger"></i>Articles</span>
    </div>
    <div class="admin-topbar-actions">
      <a href="<?= $base ?>/admin/create.php" class="btn-admin-primary">
        <i class="bi bi-plus-circle"></i> New Article
      </a>
    </div>
  </div>

  <div class="admin-content">
    <?php if ($success_msg): ?>
      <div class="alert alert-success alert-auto-dismiss alert-sm mb-3"><i class="bi bi-check-circle me-1"></i><?= htmlspecialchars($success_msg) ?></div>
    <?php endif; ?>
    <?php if ($error_msg): ?>
      <div class="alert alert-danger alert-sm mb-3"><i class="bi bi-exclamation-circle me-1"></i><?= htmlspecialchars($error_msg) ?></div>
    <?php endif; ?>

    <!-- Filter / Search Bar -->
    <form method="GET" class="table-toolbar mb-0">
      <input type="text" name="search" class="form-control" placeholder="Search title or byline…" value="<?= htmlspecialchars($search) ?>">
      <select name="status" class="form-select">
        <option value="">All Status</option>
        <option value="published" <?= $status === 'published' ? 'selected' : '' ?>>Published</option>
        <option value="draft" <?= $status === 'draft' ? 'selected' : '' ?>>Draft</option>
      </select>
      <select name="cat" class="form-select">
        <option value="">All Categories</option>
        <?php foreach ($all_cats as $c): ?>
          <option value="<?= $c['id'] ?>" <?= $cat_id === $c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['name']) ?></option>
        <?php endforeach; ?>
      </select>
      <button type="submit" class="btn btn-danger px-3"><i class="bi bi-search"></i> Filter</button>
      <a href="<?= $base ?>/admin/articles.php" class="btn btn-outline-secondary px-3">Clear</a>
    </form>

    <div class="admin-table">
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead>
            <tr>
              <th>#</th>
              <th>Title</th>
              <th>Category</th>
              <th>Author</th>
              <th>Status</th>
              <th>Views</th>
              <th>Date</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($articles): ?>
              <?php foreach ($articles as $i => $art): ?>
              <tr>
                <td><?= $off + $i + 1 ?></td>
                <td style="max-width:280px;">
                  <a href="<?= $base ?>/article.php?slug=<?= urlencode($art['slug']) ?>" target="_blank" class="fw-semibold text-dark" style="font-size:13px;line-height:1.3;display:block;">
                    <?= htmlspecialchars(substr($art['title'], 0, 70)) ?><?= strlen($art['title']) > 70 ? '…' : '' ?>
                  </a>
                </td>
                <td><?= htmlspecialchars($art['cat_name']) ?></td>
                <td><?= htmlspecialchars($art['author_name']) ?></td>
                <td><span class="status-badge <?= $art['status'] ?>"><?= ucfirst($art['status']) ?></span></td>
                <td><?= number_format($art['views']) ?></td>
                <td style="white-space:nowrap;font-size:12px;color:var(--text-muted);">
                  <?= date('M j, Y', strtotime($art['created_at'])) ?>
                </td>
                <td style="white-space:nowrap;">
                  <a href="<?= $base ?>/admin/edit.php?id=<?= $art['id'] ?>" class="btn btn-sm btn-outline-secondary" title="Edit"><i class="bi bi-pencil"></i></a>
                  <?php if ($art['status'] === 'draft'): ?>
                    <a href="<?= $base ?>/admin/toggle_status.php?id=<?= $art['id'] ?>&status=published" class="btn btn-sm btn-outline-success ms-1" title="Publish"><i class="bi bi-cloud-upload"></i></a>
                  <?php else: ?>
                    <a href="<?= $base ?>/admin/toggle_status.php?id=<?= $art['id'] ?>&status=draft" class="btn btn-sm btn-outline-warning ms-1" title="Unpublish"><i class="bi bi-cloud-download"></i></a>
                  <?php endif; ?>
                  <a href="<?= $base ?>/admin/delete_article.php?id=<?= $art['id'] ?>"
                     class="btn btn-sm btn-outline-danger ms-1" title="Delete"
                     onclick="return confirm('Are you sure you want to delete this article? This cannot be undone.')">
                    <i class="bi bi-trash"></i>
                  </a>
                </td>
              </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="8" class="text-center py-5 text-muted">
                  <i class="bi bi-newspaper" style="font-size:32px;display:block;margin-bottom:8px;color:#ddd;"></i>
                  No articles found.
                  <a href="<?= $base ?>/admin/create.php" class="btn btn-danger btn-sm ms-2">Create One</a>
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
                <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status) ?>&cat=<?= $cat_id ?>">
                  <?= $i ?>
                </a>
              </li>
            <?php endfor; ?>
          </ul>
        </nav>
      </div>
      <?php endif; ?>

    </div><!-- /admin-table -->

    <p class="mt-3" style="font-size:12px;color:var(--text-muted);">
      Showing <?= count($articles) ?> of <?= $total ?> articles
    </p>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function toggleSidebar() { document.getElementById('adminSidebar').classList.toggle('open'); }
// Auto-dismiss alerts
document.querySelectorAll('.alert-auto-dismiss').forEach(el => {
  setTimeout(() => { el.style.opacity='0'; setTimeout(() => el.remove(),500); }, 4000);
});
</script>
</body>
</html>
