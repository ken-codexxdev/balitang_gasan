<?php
require_once __DIR__ . '/config/db.php';

$slug = $_GET['slug'] ?? '';
if (!$slug) { header('Location: ' . SITE_URL); exit; }

// Fetch article
$stmt = $pdo->prepare("
    SELECT a.*, c.name AS cat_name, c.slug AS cat_slug, u.name AS author_name
    FROM articles a
    JOIN categories c ON c.id = a.category_id
    JOIN users u ON u.id = a.author_id
    WHERE a.slug = ? AND a.status = 'published'
");
$stmt->execute([$slug]);
$article = $stmt->fetch();
if (!$article) { 
  http_response_code(404); 
  include __DIR__ . '/article_error.php'; 
  exit; 
}

// Ping view count (simple increment)
$pdo->prepare("UPDATE articles SET views = views + 1 WHERE id = ?")->execute([$article['id']]);

// Approved comments
$comments = $pdo->prepare("SELECT * FROM comments WHERE article_id = ? AND status = 'approved' ORDER BY created_at DESC");
$comments->execute([$article['id']]);
$comments = $comments->fetchAll();

// Related articles (same category, different)
$related = $pdo->prepare("
    SELECT a.id, a.title, a.slug, a.image, a.published_at
    FROM articles a
    WHERE a.category_id = ? AND a.id != ? AND a.status = 'published'
    ORDER BY a.published_at DESC LIMIT 3
");
$related->execute([$article['category_id'], $article['id']]);
$related = $related->fetchAll();

// All categories for sidebar
$cat_counts = $pdo->query("
    SELECT c.name, c.slug, COUNT(a.id) AS total
    FROM categories c
    LEFT JOIN articles a ON a.category_id = c.id AND a.status='published'
    GROUP BY c.id ORDER BY c.name
")->fetchAll();

$page_title = $article['title'];
$page_desc  = strip_tags($article['excerpt'] ?: '');
$base = SITE_URL;
?>
<?php include __DIR__ . '/includes/header.php'; ?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="article-id" content="<?= $article['id'] ?>">
    <link rel="stylesheet" href="<?= $base ?>/assets/css/style.css">
  </head>
  <body>
      <div class="container py-4">
      <!-- Breadcrumb -->
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="<?= $base ?>/index.php">Home</a></li>
          <li class="breadcrumb-item">
            <a href="<?= $base ?>/category.php?category=<?= urlencode($article['cat_slug']) ?>"><?= htmlspecialchars($article['cat_name']) ?></a>
          </li>
          <li class="breadcrumb-item active"><?= htmlspecialchars(substr($article['title'], 0, 50)) ?>…</li>
        </ol>
      </nav>

      <div class="row g-4">
        <!-- ── Main Article ── -->
        <div class="col-lg-8">
          <article>
            <span class="badge-cat mb-3 d-inline-block"><?= htmlspecialchars($article['cat_name']) ?></span>
            <h1 class="article-main-title"><?= htmlspecialchars($article['title']) ?></h1>

            <div class="article-meta-bar">
              <span><i class="bi bi-person-fill"></i> <?= htmlspecialchars($article['byline'] ?: $article['author_name']) ?></span>
              <span><i class="bi bi-calendar3"></i> <?= date('F j, Y', strtotime($article['published_at'])) ?></span>
              <span><i class="bi bi-eye"></i> <?= number_format($article['views']) ?> views</span>
              <span><i class="bi bi-chat-dots"></i> <?= count($comments) ?> comments</span>
            </div>

            <?php if ($article['image']): ?>
              <img class="article-featured-img" src="<?= UPLOAD_URL . htmlspecialchars($article['image']) ?>" alt="<?= htmlspecialchars($article['title']) ?>">
            <?php endif; ?>

            <div class="article-body">
              <?= $article['body'] ?>
            </div>

            <!-- Share -->
            <div class="d-flex align-items-center gap-3 py-3 border-top mt-4">
              <span class="fw-bold" style="font-family:var(--font-ui);font-size:13px;">Share:</span>
              <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode(SITE_URL . '/article.php?slug=' . $article['slug']) ?>"
                target="_blank" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-facebook text-primary"></i>
              </a>
              <a href="https://twitter.com/intent/tweet?url=<?= urlencode(SITE_URL . '/article.php?slug=' . $article['slug']) ?>&text=<?= urlencode($article['title']) ?>"
                target="_blank" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-twitter-x"></i>
              </a>
            </div>
          </article>

          <!-- ── Comments ── -->
          <section class="mt-4" id="comments">
            <div class="section-title"><i class="bi bi-chat-dots-fill"></i> Comments (<?= count($comments) ?>)</div>

            <?php if ($comments): ?>
              <?php foreach ($comments as $c): ?>
              <div class="comment-item">
                <div class="d-flex align-items-center gap-2 mb-1">
                  <span class="comment-author"><?= htmlspecialchars($c['commenter_name']) ?></span>
                  <span class="comment-date"><?= date('M j, Y · g:i a', strtotime($c['created_at'])) ?></span>
                </div>
                <div class="comment-body"><?= nl2br(htmlspecialchars($c['body'])) ?></div>
              </div>
              <?php endforeach; ?>
            <?php else: ?>
              <p class="text-muted" style="font-size:14px;">No comments yet. Be the first to comment!</p>
            <?php endif; ?>

            <!-- Comment Form -->
            <div class="comment-form-section mt-4">
              <h5 style="font-family:var(--font-head);font-weight:700;margin-bottom:16px;">Leave a Comment</h5>
              <form id="comment-form">
                <input type="hidden" name="article_id" value="<?= $article['id'] ?>">
                <div class="row g-3 mb-3">
                  <div class="col-md-6">
                    <label class="form-label">Name *</label>
                    <input type="text" name="name" class="form-control" required placeholder="Your full name">
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Email *</label>
                    <input type="email" name="email" class="form-control" required placeholder="your@email.com">
                  </div>
                </div>
                <div class="mb-3">
                  <label class="form-label">Comment *</label>
                  <textarea name="body" class="form-control" rows="4" required placeholder="Write your comment here…"></textarea>
                </div>
                <button type="submit" class="btn btn-danger px-4">Post Comment</button>
                <div id="comment-msg"></div>
              </form>
            </div>
          </section>

        </div><!-- /col-lg-8 -->

        <!-- ── Sidebar ── -->
        <div class="col-lg-4">

          <!-- Categories Widget -->
          <div class="sidebar-widget">
            <div class="sidebar-widget-title">Categories</div>
            <ul class="sidebar-cat-list">
              <?php foreach ($cat_counts as $cc): ?>
              <li>
                <a href="<?= $base ?>/category.php?category=<?= urlencode($cc['slug']) ?>">
                  <?= htmlspecialchars($cc['name']) ?>
                  <span class="sidebar-cat-count"><?= $cc['total'] ?></span>
                </a>
              </li>
              <?php endforeach; ?>
            </ul>
          </div>

          <!-- Related Articles -->
          <?php if ($related): ?>
          <div class="sidebar-widget">
            <div class="sidebar-widget-title">Related Articles</div>
            <?php foreach ($related as $r): ?>
            <div class="news-list-item">
              <?php if ($r['image']): ?>
                <img class="news-list-img" src="<?= UPLOAD_URL . htmlspecialchars($r['image']) ?>" alt="">
              <?php else: ?>
                <div class="news-list-img d-flex align-items-center justify-content-center bg-light text-secondary"><i class="bi bi-image"></i></div>
              <?php endif; ?>
              <div class="news-list-content">
                <div class="news-list-title">
                  <a href="<?= $base ?>/article.php?slug=<?= urlencode($r['slug']) ?>"><?= htmlspecialchars($r['title']) ?></a>
                </div>
                <div class="news-list-date"><?= date('M j, Y', strtotime($r['published_at'])) ?></div>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>

        </div>
      </div>
    </div>

    <?php include __DIR__ . '/includes/footer.php'; ?>
  </body>
</html>
