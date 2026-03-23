<?php
require_once __DIR__ . '/../config/db.php';
require_login();

$page   = max(1, (int)($_GET['page'] ?? 1));
$per    = 20;
$off    = ($page - 1) * $per;
$filter = $_GET['status'] ?? '';
$search = trim($_GET['search'] ?? '');

$where  = "1=1";
$params = [];
if (in_array($filter, ['pending','approved','rejected'])) {
    $where .= " AND cm.status = ?";
    $params[] = $filter;
}
if ($search) {
    $where .= " AND (cm.commenter_name LIKE ? OR cm.body LIKE ?)";
    $like = '%' . $search . '%';
    $params[] = $like;
    $params[] = $like;
}

$count_stmt = $pdo->prepare("SELECT COUNT(*) FROM comments cm WHERE $where");
$count_stmt->execute($params);
$total = (int)$count_stmt->fetchColumn();
$pages = ceil($total / $per);

$stmt = $pdo->prepare("
    SELECT cm.*, a.title AS article_title, a.slug AS article_slug
    FROM comments cm
    JOIN articles a ON a.id = cm.article_id
    WHERE $where
    ORDER BY cm.created_at DESC
    LIMIT $per OFFSET $off
");
$stmt->execute($params);
$comments = $stmt->fetchAll();

// Status counts
$pending_n  = $pdo->query("SELECT COUNT(*) FROM comments WHERE status='pending'")->fetchColumn();
$approved_n = $pdo->query("SELECT COUNT(*) FROM comments WHERE status='approved'")->fetchColumn();
$rejected_n = $pdo->query("SELECT COUNT(*) FROM comments WHERE status='rejected'")->fetchColumn();

$success = $_SESSION['success'] ?? '';
unset($_SESSION['success']);

$base = SITE_URL;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Comments — <?= SITE_NAME ?> Admin</title>
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
      <span class="admin-topbar-title"><i class="bi bi-chat-dots me-2 text-danger"></i>Comments</span>
    </div>
  </div>

  <div class="admin-content">
    <?php if ($success): ?>
      <div class="alert alert-success alert-sm alert-auto-dismiss mb-3"><i class="bi bi-check-circle me-1"></i><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <!-- Quick status tabs -->
    <div class="d-flex gap-2 mb-3 flex-wrap">
      <a href="?status=" class="btn btn-sm <?= !$filter ? 'btn-dark' : 'btn-outline-secondary' ?>">All (<?= $pending_n + $approved_n + $rejected_n ?>)</a>
      <a href="?status=pending" class="btn btn-sm <?= $filter==='pending' ? 'btn-warning' : 'btn-outline-warning' ?>">Pending (<?= $pending_n ?>)</a>
      <a href="?status=approved" class="btn btn-sm <?= $filter==='approved' ? 'btn-success' : 'btn-outline-success' ?>">Approved (<?= $approved_n ?>)</a>
      <a href="?status=rejected" class="btn btn-sm <?= $filter==='rejected' ? 'btn-danger' : 'btn-outline-danger' ?>">Rejected (<?= $rejected_n ?>)</a>
    </div>

    <!-- Search -->
    <form method="GET" class="table-toolbar mb-0">
      <input type="hidden" name="status" value="<?= htmlspecialchars($filter) ?>">
      <input type="text" name="search" class="form-control" placeholder="Search by name or content…" value="<?= htmlspecialchars($search) ?>">
      <button type="submit" class="btn btn-danger px-3"><i class="bi bi-search"></i></button>
      <a href="?status=<?= urlencode($filter) ?>" class="btn btn-outline-secondary">Clear</a>
    </form>

    <div class="admin-table">
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead>
            <tr>
              <th>Commenter</th>
              <th>Comment</th>
              <th>Article</th>
              <th>Status</th>
              <th>Date</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($comments): ?>
              <?php foreach ($comments as $cm): ?>
              <tr>
                <td style="white-space:nowrap;">
                  <div style="font-size:13px;font-weight:600;"><?= htmlspecialchars($cm['commenter_name']) ?></div>
                  <div style="font-size:12px;color:var(--text-muted);"><?= htmlspecialchars($cm['commenter_email']) ?></div>
                </td>
                <td style="max-width:280px;">
                  <span style="font-size:13px;line-height:1.4;">
                    <?= htmlspecialchars(substr($cm['body'], 0, 120)) ?><?= strlen($cm['body']) > 120 ? '…' : '' ?>
                  </span>
                </td>
                <td style="max-width:180px;">
                  <a href="<?= $base ?>/article.php?slug=<?= urlencode($cm['article_slug']) ?>" target="_blank"
                     style="font-size:12px;color:var(--red);">
                    <?= htmlspecialchars(substr($cm['article_title'], 0, 45)) ?>…
                  </a>
                </td>
                <td><span class="status-badge <?= $cm['status'] ?>"><?= ucfirst($cm['status']) ?></span></td>
                <td style="font-size:12px;color:var(--text-muted);white-space:nowrap;"><?= date('M j, Y', strtotime($cm['created_at'])) ?></td>
                <td style="white-space:nowrap;">
                  <?php if ($cm['status'] !== 'approved'): ?>
                    <a href="<?= $base ?>/admin/comment_action.php?id=<?= $cm['id'] ?>&action=approved&ref=comments"
                       class="btn btn-xs btn-success" style="font-size:11px;padding:3px 8px;" title="Approve">
                      <i class="bi bi-check-lg"></i>
                    </a>
                  <?php endif; ?>
                  <?php if ($cm['status'] !== 'rejected'): ?>
                    <a href="<?= $base ?>/admin/comment_action.php?id=<?= $cm['id'] ?>&action=rejected&ref=comments"
                       class="btn btn-xs btn-warning ms-1" style="font-size:11px;padding:3px 8px;" title="Reject">
                      <i class="bi bi-x-lg"></i>
                    </a>
                  <?php endif; ?>
                  <a href="<?= $base ?>/admin/comment_action.php?id=<?= $cm['id'] ?>&action=delete&ref=comments"
                     class="btn btn-xs btn-danger ms-1" style="font-size:11px;padding:3px 8px;" title="Delete"
                     onclick="return confirm('Delete this comment permanently?')">
                    <i class="bi bi-trash"></i>
                  </a>
                </td>
              </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="6" class="text-center py-5 text-muted">
                  <i class="bi bi-chat-dots" style="font-size:32px;display:block;margin-bottom:8px;color:#ddd;"></i>
                  No comments found.
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
                <a class="page-link" href="?page=<?= $i ?>&status=<?= urlencode($filter) ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
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
