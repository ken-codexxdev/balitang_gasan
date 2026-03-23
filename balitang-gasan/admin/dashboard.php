<?php
require_once __DIR__ . '/../config/db.php';
require_login();

// Stats
$total_articles   = $pdo->query("SELECT COUNT(*) FROM articles")->fetchColumn();
$published_count  = $pdo->query("SELECT COUNT(*) FROM articles WHERE status='published'")->fetchColumn();
$draft_count      = $pdo->query("SELECT COUNT(*) FROM articles WHERE status='draft'")->fetchColumn();
$pending_comments = $pdo->query("SELECT COUNT(*) FROM comments WHERE status='pending'")->fetchColumn();
$total_comments   = $pdo->query("SELECT COUNT(*) FROM comments")->fetchColumn();
$subscribers      = $pdo->query("SELECT COUNT(*) FROM subscribers")->fetchColumn();
$total_views      = $pdo->query("SELECT COALESCE(SUM(views),0) FROM articles")->fetchColumn();

// Recent articles
$recent_articles = $pdo->query("
    SELECT a.id, a.title, a.slug, a.status, a.views, a.published_at, a.created_at,
           c.name AS cat_name, u.name AS author_name
    FROM articles a
    JOIN categories c ON c.id = a.category_id
    JOIN users u ON u.id = a.author_id
    ORDER BY a.created_at DESC LIMIT 8
")->fetchAll();

// Recent comments
$recent_comments = $pdo->query("
    SELECT cm.*, a.title AS article_title, a.slug AS article_slug
    FROM comments cm
    JOIN articles a ON a.id = cm.article_id
    ORDER BY cm.created_at DESC LIMIT 5
")->fetchAll();

// Articles per category (for chart data)
$cat_stats = $pdo->query("
    SELECT c.name, COUNT(a.id) AS total
    FROM categories c
    LEFT JOIN articles a ON a.category_id = c.id AND a.status='published'
    GROUP BY c.id ORDER BY total DESC
")->fetchAll();

$base = SITE_URL;
$user = current_user();
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — <?= SITE_NAME ?> Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Playfair+Display:wght@700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= $base ?>/assets/css/admin.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
  </head>
  <body>
    <?php include __DIR__ . '/includes/sidebar.php'; ?>

    <div class="admin-main">
      <!-- Top Bar -->
      <div class="admin-topbar">
        <div class="d-flex align-items-center gap-3">
          <button class="sidebar-toggle" onclick="toggleSidebar()"><i class="bi bi-list"></i></button>
          <span class="admin-topbar-title"><i class="bi bi-speedometer2 me-2 text-danger"></i>Dashboard</span>
        </div>
        <div class="admin-topbar-actions">
          <a href="<?= $base ?>/admin/create.php" class="btn-admin-primary">
            <i class="bi bi-plus-circle"></i> New Article
          </a>
          <a href="<?= $base ?>/index.php" target="_blank">
            <i class="bi bi-box-arrow-up-right"></i> View Site
          </a>
        </div>
      </div>

      <div class="admin-content">
        <p class="mb-4" style="color:var(--text-muted);font-size:14px;">
          Welcome back, <strong><?= htmlspecialchars($user['name']) ?></strong>! Here's what's happening on Balitang Gaseño.
        </p>

        <!-- Stat Cards -->
        <div class="row g-3 mb-4">
          <div class="col-xl-3 col-md-6">
            <div class="stat-card">
              <div class="stat-icon red"><i class="bi bi-newspaper"></i></div>
              <div>
                <div class="stat-value"><?= number_format($total_articles) ?></div>
                <div class="stat-label">Total Articles</div>
              </div>
            </div>
          </div>
          <div class="col-xl-3 col-md-6">
            <div class="stat-card">
              <div class="stat-icon green"><i class="bi bi-check-circle"></i></div>
              <div>
                <div class="stat-value"><?= number_format($published_count) ?></div>
                <div class="stat-label">Published</div>
              </div>
            </div>
          </div>
          <div class="col-xl-3 col-md-6">
            <div class="stat-card">
              <div class="stat-icon blue"><i class="bi bi-eye"></i></div>
              <div>
                <div class="stat-value"><?= number_format($total_views) ?></div>
                <div class="stat-label">Total Views</div>
              </div>
            </div>
          </div>
          <div class="col-xl-3 col-md-6">
            <div class="stat-card">
              <div class="stat-icon purple"><i class="bi bi-chat-dots"></i></div>
              <div>
                <div class="stat-value"><?= number_format($pending_comments) ?></div>
                <div class="stat-label">Pending Comments</div>
              </div>
            </div>
          </div>
        </div>

        <div class="row g-4 mb-4">
          <!-- Extra stats row -->
          <div class="col-md-4">
            <div class="stat-card">
              <div class="stat-icon red"><i class="bi bi-file-earmark-text"></i></div>
              <div>
                <div class="stat-value"><?= number_format($draft_count) ?></div>
                <div class="stat-label">Drafts</div>
              </div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="stat-card">
              <div class="stat-icon green"><i class="bi bi-chat-square-dots"></i></div>
              <div>
                <div class="stat-value"><?= number_format($total_comments) ?></div>
                <div class="stat-label">Total Comments</div>
              </div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="stat-card">
              <div class="stat-icon blue"><i class="bi bi-envelope-check"></i></div>
              <div>
                <div class="stat-value"><?= number_format($subscribers) ?></div>
                <div class="stat-label">Subscribers</div>
              </div>
            </div>
          </div>
        </div>

        <div class="row g-4">
          <!-- Recent Articles Table -->
          <div class="col-lg-8">
            <div class="admin-card">
              <div class="admin-card-header">
                <i class="bi bi-clock-history"></i> Recent Articles
                <a href="<?= $base ?>/admin/articles.php" class="ms-auto btn btn-sm btn-outline-secondary" style="font-size:12px;">View All</a>
              </div>
              <div class="admin-card-body p-0">
                <div class="table-responsive">
                  <table class="table table-hover mb-0">
                    <thead>
                      <tr>
                        <th style="padding:12px 16px;font-size:11px;text-transform:uppercase;letter-spacing:.08em;color:var(--text-muted);background:var(--bg-light);">Title</th>
                        <th style="padding:12px 16px;font-size:11px;text-transform:uppercase;letter-spacing:.08em;color:var(--text-muted);background:var(--bg-light);">Category</th>
                        <th style="padding:12px 16px;font-size:11px;text-transform:uppercase;letter-spacing:.08em;color:var(--text-muted);background:var(--bg-light);">Status</th>
                        <th style="padding:12px 16px;font-size:11px;text-transform:uppercase;letter-spacing:.08em;color:var(--text-muted);background:var(--bg-light);">Views</th>
                        <th style="padding:12px 16px;font-size:11px;text-transform:uppercase;letter-spacing:.08em;color:var(--text-muted);background:var(--bg-light);">Date</th>
                        <th style="padding:12px 16px;font-size:11px;text-transform:uppercase;letter-spacing:.08em;color:var(--text-muted);background:var(--bg-light);">Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($recent_articles as $art): ?>
                      <tr>
                        <td style="padding:12px 16px;font-size:13px;max-width:240px;">
                          <a href="<?= $base ?>/article.php?slug=<?= urlencode($art['slug']) ?>" target="_blank"
                            class="fw-semibold text-dark" style="line-height:1.3;display:block;">
                            <?= htmlspecialchars(substr($art['title'], 0, 55)) ?><?= strlen($art['title']) > 55 ? '…' : '' ?>
                          </a>
                        </td>
                        <td style="padding:12px 16px;font-size:13px;white-space:nowrap;"><?= htmlspecialchars($art['cat_name']) ?></td>
                        <td style="padding:12px 16px;">
                          <span class="status-badge <?= $art['status'] ?>"><?= ucfirst($art['status']) ?></span>
                        </td>
                        <td style="padding:12px 16px;font-size:13px;"><?= number_format($art['views']) ?></td>
                        <td style="padding:12px 16px;font-size:12px;color:var(--text-muted);white-space:nowrap;">
                          <?= date('M j, Y', strtotime($art['created_at'])) ?>
                        </td>
                        <td style="padding:12px 16px;white-space:nowrap;">
                          <a href="<?= $base ?>/admin/edit.php?id=<?= $art['id'] ?>" class="btn btn-sm btn-outline-secondary" title="Edit"><i class="bi bi-pencil"></i></a>
                          <a href="<?= $base ?>/admin/delete_article.php?id=<?= $art['id'] ?>" class="btn btn-sm btn-outline-danger ms-1"
                            onclick="return confirm('Delete this article?')" title="Delete"><i class="bi bi-trash"></i></a>
                        </td>
                      </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>

          <!-- Right Column -->
          <div class="col-lg-4">
            <!-- Category Chart -->
            <div class="admin-card mb-4">
              <div class="admin-card-header"><i class="bi bi-pie-chart"></i> Articles by Category</div>
              <div class="admin-card-body">
                <canvas id="catChart" height="200"></canvas>
              </div>
            </div>

            <!-- Recent Comments -->
            <div class="admin-card">
              <div class="admin-card-header">
                <i class="bi bi-chat-dots"></i> Pending Comments
                <a href="<?= $base ?>/admin/comments.php" class="ms-auto btn btn-sm btn-outline-secondary" style="font-size:12px;">View All</a>
              </div>
              <div class="admin-card-body p-0">
                <?php $pending_list = array_filter($recent_comments, fn($c) => $c['status'] === 'pending'); ?>
                <?php if ($pending_list): ?>
                  <?php foreach (array_slice($pending_list, 0, 4) as $cm): ?>
                  <div style="padding:12px 16px;border-bottom:1px solid var(--border);">
                    <div class="d-flex justify-content-between align-items-start">
                      <div>
                        <strong style="font-size:13px;"><?= htmlspecialchars($cm['commenter_name']) ?></strong>
                        <div style="font-size:12px;color:var(--text-muted);margin:2px 0;">
                          on <em><?= htmlspecialchars(substr($cm['article_title'], 0, 30)) ?>…</em>
                        </div>
                        <div style="font-size:13px;color:#555;margin-top:3px;">
                          <?= htmlspecialchars(substr($cm['body'], 0, 60)) ?>…
                        </div>
                      </div>
                    </div>
                    <div class="d-flex gap-1 mt-2">
                      <a href="<?= $base ?>/admin/comment_action.php?id=<?= $cm['id'] ?>&action=approved" class="btn btn-xs btn-success" style="font-size:11px;padding:2px 8px;">Approve</a>
                      <a href="<?= $base ?>/admin/comment_action.php?id=<?= $cm['id'] ?>&action=rejected" class="btn btn-xs btn-danger" style="font-size:11px;padding:2px 8px;">Reject</a>
                    </div>
                  </div>
                  <?php endforeach; ?>
                <?php else: ?>
                  <div class="p-3 text-center text-muted" style="font-size:13px;">No pending comments</div>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
      </div><!-- /admin-content -->
    </div><!-- /admin-main -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Category chart
    const catData = <?= json_encode(array_values($cat_stats)) ?>;
    new Chart(document.getElementById('catChart'), {
      type: 'doughnut',
      data: {
        labels: catData.map(d => d.name),
        datasets: [{
          data: catData.map(d => d.total),
          backgroundColor: ['#c0392b','#e74c3c','#e67e22','#f39c12','#27ae60','#2980b9','#8e44ad','#16a085'],
          borderWidth: 2,
          borderColor: '#fff'
        }]
      },
      options: {
        responsive: true,
        plugins: { legend: { position: 'bottom', labels: { font: { size: 11 }, padding: 8 } } },
        cutout: '60%'
      }
    });

    // Sidebar toggle for mobile
    function toggleSidebar() {
      document.getElementById('adminSidebar').classList.toggle('open');
    }
    </script>
  </body>
</html>
