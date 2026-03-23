<?php
require_once __DIR__ . '/config/db.php';

// ── Fetch hero / featured article (latest published)
$hero = $pdo->query("
    SELECT a.*, c.name AS cat_name, c.slug AS cat_slug
    FROM articles a
    JOIN categories c ON c.id = a.category_id
    WHERE a.status = 'published'
    ORDER BY a.published_at DESC
    LIMIT 1
")->fetch();

// ── Latest News (left column – excluding hero)
$stmt = $pdo->prepare("
    SELECT a.id, a.title, a.slug, a.excerpt, a.image, a.published_at, a.byline,
           c.name AS cat_name, c.slug AS cat_slug
    FROM articles a
    JOIN categories c ON c.id = a.category_id
    WHERE a.status = 'published' AND a.id != :hero_id
    ORDER BY a.published_at DESC
    LIMIT 4
");
$stmt->execute([':hero_id' => $hero['id'] ?? 0]);
$latest = $stmt->fetchAll();

// ── Popular News (most viewed, right column)
$popular = $pdo->query("
    SELECT a.id, a.title, a.slug, a.image, a.published_at,
           c.name AS cat_name, c.slug AS cat_slug
    FROM articles a
    JOIN categories c ON c.id = a.category_id
    WHERE a.status = 'published'
    ORDER BY a.views DESC
    LIMIT 6
")->fetchAll();

// ── Featured Grid (middle section — 6 articles)
$featured_ids = array_merge(
    [$hero['id'] ?? 0],
    array_column($latest, 'id')
);
$in_clause = implode(',', array_fill(0, count($featured_ids), '?'));
$stmt2 = $pdo->prepare("
    SELECT a.id, a.title, a.slug, a.excerpt, a.image, a.published_at, a.byline,
           c.name AS cat_name, c.slug AS cat_slug
    FROM articles a
    JOIN categories c ON c.id = a.category_id
    WHERE a.status = 'published' AND a.id NOT IN ($in_clause)
    ORDER BY a.published_at DESC
    LIMIT 6
");
$stmt2->execute($featured_ids);
$featured_grid = $stmt2->fetchAll();

// ── Mini strip (bottom 4 articles — different categories)
$mini = $pdo->query("
    SELECT a.id, a.title, a.slug, a.image, a.published_at,
           c.name AS cat_name, c.slug AS cat_slug
    FROM articles a
    JOIN categories c ON c.id = a.category_id
    WHERE a.status = 'published'
    ORDER BY RAND()
    LIMIT 4
")->fetchAll();

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

    <!-- ===== HERO BANNER ===== -->
    <?php if ($hero): ?>
    <section class="hero-section">
      <?php if ($hero['image']): ?>
        <img class="hero-bg" src="<?= UPLOAD_URL . htmlspecialchars($hero['image']) ?>" alt="">
      <?php else: ?>
        <div class="hero-bg" style="background:linear-gradient(135deg,#1a1a2e 0%,#c0392b 100%);opacity:1;"></div>
      <?php endif; ?>
      <div class="hero-gradient"></div>
      <div class="container hero-content">
        <div class="row">
          <div class="col-lg-7">
            <span class="hero-cat-badge"><?= htmlspecialchars($hero['cat_name']) ?></span>
            <h1 class="hero-title">
              <a href="<?= $base ?>/article.php?slug=<?= urlencode($hero['slug']) ?>" class="text-white">
                <?= htmlspecialchars($hero['title']) ?>
              </a>
            </h1>
            <?php if ($hero['excerpt']): ?>
              <p class="hero-excerpt"><?= htmlspecialchars($hero['excerpt']) ?></p>
            <?php endif; ?>
            <div class="hero-meta mt-2">
              <i class="bi bi-person-fill me-1"></i><?= htmlspecialchars($hero['byline'] ?: 'By Staff Reporter') ?>
              &nbsp;·&nbsp;
              <i class="bi bi-clock me-1"></i><?= date('F j, Y', strtotime($hero['published_at'])) ?>
            </div>
          </div>
        </div>
      </div>
    </section>
    <?php endif; ?>

    <!-- ===== MAIN CONTENT ===== -->
    <div class="content-section">
    <div class="container">
      <div class="row g-4">

        <!-- ── LEFT: Featured Article ── -->
        <div class="col-lg-3 col-md-6">
          <?php
          // Use first from latest as featured left
          $feat = $latest[0] ?? null;
          if ($feat):
          ?>
          <div class="featured-left-card">
            <?php if ($feat['image']): ?>
              <img class="featured-left-img" src="<?= UPLOAD_URL . htmlspecialchars($feat['image']) ?>" alt="">
            <?php else: ?>
              <div class="featured-left-img d-flex align-items-center justify-content-center bg-light text-secondary fs-1"><i class="bi bi-newspaper"></i></div>
            <?php endif; ?>
            <div class="featured-left-body">
              <div class="featured-left-cat"><?= htmlspecialchars($feat['cat_name']) ?></div>
              <h3 class="featured-left-title">
                <a href="<?= $base ?>/article.php?slug=<?= urlencode($feat['slug']) ?>"><?= htmlspecialchars($feat['title']) ?></a>
              </h3>
              <p class="featured-left-excerpt"><?= htmlspecialchars(substr(strip_tags($feat['excerpt'] ?: $feat['title']), 0, 160)) ?>…</p>
              <div class="featured-left-meta">
                <?= htmlspecialchars($feat['byline'] ?: 'By Staff Reporter') ?> &nbsp;·&nbsp;
                <?= date('M j, Y', strtotime($feat['published_at'])) ?>
              </div>
            </div>
          </div>
          <?php endif; ?>
        </div>

        <!-- ── MIDDLE: Latest News ── -->
        <div class="col-lg-5">
          <div class="sidebar-widget">
            <div class="section-title"><i class="bi bi-lightning-fill"></i> Latest News</div>
            <?php foreach (array_slice($latest, 1) as $art): ?>
            <div class="news-list-item">
              <?php if ($art['image']): ?>
                <img class="news-list-img" src="<?= UPLOAD_URL . htmlspecialchars($art['image']) ?>" alt="">
              <?php else: ?>
                <div class="news-list-img d-flex align-items-center justify-content-center bg-light text-secondary"><i class="bi bi-image"></i></div>
              <?php endif; ?>
              <div class="news-list-content">
                <div class="news-list-cat"><?= htmlspecialchars($art['cat_name']) ?></div>
                <div class="news-list-title">
                  <a href="<?= $base ?>/article.php?slug=<?= urlencode($art['slug']) ?>"><?= htmlspecialchars($art['title']) ?></a>
                </div>
                <div class="news-list-date">
                  <?= htmlspecialchars($art['byline'] ?: 'By Staff Reporter') ?> &nbsp;·&nbsp; <?= date('M j, Y', strtotime($art['published_at'])) ?>
                </div>
              </div>
            </div>
            <?php endforeach; ?>
            <a href="<?= $base ?>/category.php?category=news" class="read-more-link d-block mt-3 text-end">More News <i class="bi bi-arrow-right"></i></a>
          </div>
        </div>

        <!-- ── RIGHT: Popular News ── -->
        <div class="col-lg-4">
          <div class="sidebar-widget">
            <div class="section-title"><i class="bi bi-fire"></i> Popular News</div>
            <?php foreach ($popular as $p): ?>
            <div class="news-list-item">
              <?php if ($p['image']): ?>
                <img class="news-list-img" src="<?= UPLOAD_URL . htmlspecialchars($p['image']) ?>" alt="">
              <?php else: ?>
                <div class="news-list-img d-flex align-items-center justify-content-center bg-light text-secondary"><i class="bi bi-image"></i></div>
              <?php endif; ?>
              <div class="news-list-content">
                <div class="news-list-cat"><?= htmlspecialchars($p['cat_name']) ?></div>
                <div class="news-list-title">
                  <a href="<?= $base ?>/article.php?slug=<?= urlencode($p['slug']) ?>"><?= htmlspecialchars($p['title']) ?></a>
                </div>
                <div class="news-list-date"><?= date('M j, Y', strtotime($p['published_at'])) ?></div>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
        </div>

      </div><!-- /row -->
    </div><!-- /container -->
    </div>

    <!-- ===== FEATURED GRID ===== -->
    <?php if ($featured_grid): ?>
    <div class="py-4" style="background:var(--bg-light);">
    <div class="container">
      <div class="section-title"><i class="bi bi-grid-3x3-gap-fill"></i> More Stories</div>
      <div class="row g-3">
        <?php foreach ($featured_grid as $art): ?>
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
                <span><i class="bi bi-person"></i> <?= htmlspecialchars($art['byline'] ?: 'Staff Reporter') ?></span>
                <span><i class="bi bi-calendar3"></i> <?= date('M j, Y', strtotime($art['published_at'])) ?></span>
              </div>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    </div>
    <?php endif; ?>

    <!-- ===== MINI STRIP ===== -->
    <?php if ($mini): ?>
    <div class="py-4 bg-white border-top border-bottom">
    <div class="container">
      <div class="row g-3">
        <?php foreach ($mini as $m): ?>
        <div class="col-lg-3 col-md-6">
          <a href="<?= $base ?>/article.php?slug=<?= urlencode($m['slug']) ?>" class="mini-card text-decoration-none">
            <?php if ($m['image']): ?>
              <img class="mini-card-img" src="<?= UPLOAD_URL . htmlspecialchars($m['image']) ?>" alt="">
            <?php else: ?>
              <div class="mini-card-img d-flex align-items-center justify-content-center bg-light text-secondary"><i class="bi bi-image"></i></div>
            <?php endif; ?>
            <div>
              <div class="mini-card-cat"><?= htmlspecialchars($m['cat_name']) ?></div>
              <div class="mini-card-title"><?= htmlspecialchars($m['title']) ?></div>
              <div class="mini-card-date"><?= date('M j, Y', strtotime($m['published_at'])) ?></div>
            </div>
          </a>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    </div>
    <?php endif; ?>

    <?php include __DIR__ . '/includes/footer.php'; ?>
  </body>
</html>
