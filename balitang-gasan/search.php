<?php
require_once __DIR__ . '/config/db.php';

$q    = trim($_GET['q'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$per  = 9;
$off  = ($page - 1) * $per;

$articles = [];
$total    = 0;
$pages    = 0;

if ($q !== '') {
    $like = '%' . $q . '%';
    $count_stmt = $pdo->prepare("
        SELECT COUNT(*) FROM articles
        WHERE status='published' AND (title LIKE ? OR body LIKE ? OR excerpt LIKE ?)
    ");
    $count_stmt->execute([$like, $like, $like]);
    $total = (int)$count_stmt->fetchColumn();
    $pages = ceil($total / $per);

    $arts_stmt = $pdo->prepare("
        SELECT a.*, c.name AS cat_name, c.slug AS cat_slug
        FROM articles a
        JOIN categories c ON c.id = a.category_id
        WHERE a.status='published' AND (a.title LIKE ? OR a.body LIKE ? OR a.excerpt LIKE ?)
        ORDER BY a.published_at DESC
        LIMIT $per OFFSET $off
    ");
    $arts_stmt->execute([$like, $like, $like]);
    $articles = $arts_stmt->fetchAll();
}

$page_title = $q ? 'Search: ' . $q : 'Search';
$base = SITE_URL;
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
    <!-- Search Hero -->
    <section class="search-hero">
      <div class="container">
        <h2 style="font-family:var(--font-head);font-size:1.8rem;font-weight:800;margin-bottom:20px;">Search Articles</h2>
        <form action="<?= $base ?>/search.php" method="GET" class="search-hero-form d-flex" style="max-width:560px;">
          <input type="text" name="q" class="form-control" placeholder="Search Balitang Gaseño…" value="<?= htmlspecialchars($q) ?>">
          <button type="submit" class="btn-search-lg">Search</button>
        </form>
      </div>
    </section>

    <div class="container py-4">
      <?php if ($q !== ''): ?>
        <p style="font-family:var(--font-ui);font-size:14px;color:var(--text-muted);">
          <?= $total ?> result<?= $total !== 1 ? 's' : '' ?> for "<strong><?= htmlspecialchars($q) ?></strong>"
        </p>
        <?php if ($articles): ?>
          <div class="row g-3">
            <?php foreach ($articles as $art): ?>
            <div class="col-lg-4 col-md-6">
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
              <?php for ($i = 1; $i <= $pages; $i++): ?>
                <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                  <a class="page-link" href="?q=<?= urlencode($q) ?>&page=<?= $i ?>"><?= $i ?></a>
                </li>
              <?php endfor; ?>
            </ul>
          </nav>
          <?php endif; ?>

        <?php else: ?>
          <div class="empty-state">
            <i class="bi bi-search"></i>
            <p>No articles found for your search.</p>
          </div>
        <?php endif; ?>
      <?php endif; ?>
    </div>

    <?php include __DIR__ . '/includes/footer.php'; ?>
  </body>
</html>