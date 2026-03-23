<?php
require_once __DIR__ . '/config/db.php';

$cat_slug = $_GET['category'] ?? '';
$page     = max(1, (int)($_GET['page'] ?? 1));
$per_page = 9;
$offset   = ($page - 1) * $per_page;

// Fetch category
$stmt = $pdo->prepare("SELECT * FROM categories WHERE slug = ?");
$stmt->execute([$cat_slug]);
$category = $stmt->fetch();
if (!$category) { header('Location: ' . SITE_URL); exit; }

// Count
$count_stmt = $pdo->prepare("SELECT COUNT(*) FROM articles WHERE category_id = ? AND status='published'");
$count_stmt->execute([$category['id']]);
$total   = (int)$count_stmt->fetchColumn();
$pages   = ceil($total / $per_page);

// Articles
$arts_stmt = $pdo->prepare("
    SELECT a.*, c.name AS cat_name, c.slug AS cat_slug, u.name AS author_name
    FROM articles a
    JOIN categories c ON c.id = a.category_id
    JOIN users u ON u.id = a.author_id
    WHERE a.category_id = ? AND a.status = 'published'
    ORDER BY a.published_at DESC
    LIMIT $per_page OFFSET $offset
");
$arts_stmt->execute([$category['id']]);
$articles = $arts_stmt->fetchAll();

// All categories for sidebar
$cat_counts = $pdo->query("
    SELECT c.name, c.slug, COUNT(a.id) AS total
    FROM categories c
    LEFT JOIN articles a ON a.category_id = c.id AND a.status='published'
    GROUP BY c.id ORDER BY c.name
")->fetchAll();

$page_title = $category['name'] . ' News';
$base = SITE_URL;
$active_cat = $cat_slug;
?>
<?php include __DIR__ . '/includes/header.php'; ?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?= $base ?>/assets/css/style.css">
  </head>
  <body>
    <!-- Category Hero -->
    <div style="background:var(--dark);color:#fff;padding:40px 0 28px;">
      <div class="container">
        <div class="d-flex align-items-center gap-3 mb-2">
          <span class="hero-cat-badge"><?= htmlspecialchars($category['name']) ?></span>
        </div>
        <h1 style="font-family:var(--font-head);font-size:2.5rem;font-weight:800;margin:0;text-transform: uppercase;">
          <?= htmlspecialchars($category['name']) ?> 
        </h1>
        <?php if ($category['description']): ?>
          <p style="color:rgba(255,255,255,.7);margin-top:8px;font-size:15px;"><?= htmlspecialchars($category['description']) ?></p>
        <?php endif; ?>
        <nav aria-label="breadcrumb" class="mt-2">
          <ol class="breadcrumb" style="background:none;padding:0;margin:0;">
            <li class="breadcrumb-item"><a href="<?= $base ?>/index.php" style="color:rgba(255,255,255,.6);">Home</a></li>
            <li class="breadcrumb-item active" style="color:rgba(255,255,255,.9);"><?= htmlspecialchars($category['name']) ?></li>
          </ol>
        </nav>
      </div>
    </div>

    <div class="container py-4">
      <div class="row g-4">

        <!-- Articles Grid -->
        <div class="col-lg-8">
          <?php if ($articles): ?>
            <div class="row g-3">
              <?php foreach ($articles as $art): ?>
              <div class="col-md-6">
                <div class="article-card">
                  <?php if ($art['image']): ?>
                    <img class="article-card-img" src="<?= UPLOAD_URL . htmlspecialchars($art['image']) ?>" alt="">
                  <?php else: ?>
                    <div class="article-card-img-placeholder"><i class="bi bi-newspaper"></i></div>
                  <?php endif; ?>
                  <div class="article-card-body">
                    <div class="article-card-cat"><?= htmlspecialchars($art['cat_name']) ?></div>
                    <h4 class="article-card-title">
                      <a href="<?= $base ?>/article.php?slug=<?= urlencode($art['slug']) ?>"><?= htmlspecialchars($art['title']) ?></a>
                    </h4>
                    <p class="article-card-excerpt"><?= htmlspecialchars(substr(strip_tags($art['excerpt'] ?: ''), 0, 110)) ?>…</p>
                    <div class="article-card-meta">
                      <span><i class="bi bi-person"></i> <?= htmlspecialchars($art['byline'] ?: $art['author_name']) ?></span>
                      <span><i class="bi bi-calendar3"></i> <?= date('M j, Y', strtotime($art['published_at'])) ?></span>
                    </div>
                  </div>
                </div>
              </div>
              <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($pages > 1): ?>
            <nav class="mt-4">
              <ul class="pagination justify-content-center">
                <?php if ($page > 1): ?>
                  <li class="page-item"><a class="page-link" href="?category=<?= urlencode($cat_slug) ?>&page=<?= $page - 1 ?>"><i class="bi bi-chevron-left"></i></a></li>
                <?php endif; ?>
                <?php for ($i = 1; $i <= $pages; $i++): ?>
                  <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                    <a class="page-link" href="?category=<?= urlencode($cat_slug) ?>&page=<?= $i ?>"><?= $i ?></a>
                  </li>
                <?php endfor; ?>
                <?php if ($page < $pages): ?>
                  <li class="page-item"><a class="page-link" href="?category=<?= urlencode($cat_slug) ?>&page=<?= $page + 1 ?>"><i class="bi bi-chevron-right"></i></a></li>
                <?php endif; ?>
              </ul>
            </nav>
            <?php endif; ?>

          <?php else: ?>
            <div class="empty-state">
              <i class="bi bi-newspaper"></i>
              <p>No articles found in this category yet.</p>
              <a href="<?= $base ?>/index.php" class="btn btn-danger btn-sm">Back to Home</a>
            </div>
          <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
          <div class="sidebar-widget">
            <div class="sidebar-widget-title">All Categories</div>
            <ul class="sidebar-cat-list">
              <?php foreach ($cat_counts as $cc): ?>
              <li>
                <a href="<?= $base ?>/category.php?category=<?= urlencode($cc['slug']) ?>"
                  class="<?= $cc['slug'] === $cat_slug ? 'text-danger fw-bold' : '' ?>">
                  <?= htmlspecialchars($cc['name']) ?>
                  <span class="sidebar-cat-count"><?= $cc['total'] ?></span>
                </a>
              </li>
              <?php endforeach; ?>
            </ul>
          </div>
        </div>
      </div>
    </div>

    <?php include __DIR__ . '/includes/footer.php'; ?>
  </body>
</html>