<?php
// includes/footer.php
$base = SITE_URL;
// Fetch categories for footer
$footer_cats = $pdo->query("SELECT name, slug FROM categories ORDER BY name")->fetchAll();
// Popular articles (most viewed)
$popular = $pdo->query("SELECT id, title, slug, published_at FROM articles WHERE status='published' ORDER BY views DESC LIMIT 4")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700;800&family=Source+Serif+4:wght@300;400;600&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= $base ?>/assets/css/style.css">
  </head>
  <body>
    <section class="newsletter-section">
      <div class="container">
        <div class="row justify-content-center text-center">
          <div class="col-lg-6">
            <h3 class="newsletter-title">Stay Informed with Balitang Gaseño</h3>
            <p class="newsletter-sub">Get the latest news from Gasan delivered to your inbox.</p>
            <form id="newsletter-form" class="newsletter-form" method="POST" action="<?= $base ?>/subscribe.php">
              <div class="input-group">
                <input type="email" name="email" class="form-control" placeholder="Enter your email address…" required>
                <button type="submit" class="btn btn-subscribe">Subscribe <i class="bi bi-arrow-right"></i></button>
              </div>
              <div id="subscribe-msg" class="mt-2 small"></div>
            </form>
          </div>
        </div>
      </div>
    </section>

    <!-- ===== SOCIAL SECTION ===== -->
    <section class="social-section">
      <div class="container text-center">
        <h4 class="social-title">Stay Connected with Us</h4>
        <div class="social-icons">
          <a href="#" class="social-btn facebook"><i class="bi bi-facebook"></i><span>Facebook</span></a>
          <a href="#" class="social-btn instagram"><i class="bi bi-instagram"></i><span>Instagram</span></a>
          <a href="#" class="social-btn twitter"><i class="bi bi-twitter-x"></i><span>Twitter / X</span></a>
          <a href="#" class="social-btn youtube"><i class="bi bi-youtube"></i><span>YouTube</span></a>
        </div>
      </div>
    </section>

    <!-- ===== MAIN FOOTER ===== -->
    <footer class="main-footer">
      <div class="container">
        <div class="row g-4 pt-5 pb-4">

          <!-- Brand -->
          <div class="col-lg-3 col-md-6">
            <div class="footer-brand mb-3">
              <span class="footer-brand-icon"><i class="bi bi-newspaper"></i></span>
              <div>
                <div class="footer-brand-name">BALITANG<span> GASEÑO</span></div>
                <div class="footer-brand-tagline">Pusong Gasan, Balitang Makabayan</div>
              </div>
            </div>
            <p class="footer-about">Your trusted source for timely, accurate, and community-focused journalism in Gasan, Marinduque.</p>
            <div class="footer-social-mini">
              <a href="#"><i class="bi bi-facebook"></i></a>
              <a href="#"><i class="bi bi-instagram"></i></a>
              <a href="#"><i class="bi bi-twitter-x"></i></a>
              <a href="#"><i class="bi bi-youtube"></i></a>
            </div>
          </div>

          <!-- Quick Links -->
          <div class="col-lg-2 col-md-6">
            <h6 class="footer-heading">Quick Links</h6>
            <ul class="footer-links">
              <li><a href="<?= $base ?>/index.php">Home</a></li>
              <li><a href="<?= $base ?>/about.php">About Us</a></li>
              <li><a href="<?= $base ?>/contact.php">Contact</a></li>
              <li><a href="<?= $base ?>/admin/login.php" onclick="alert('You are about to enter the Balitang Gaseño Admin Panel. Please be advised this is for administrator only. Thank you.')">Editor Login</a></li>
            </ul>
          </div>

          <!-- Categories -->
          <div class="col-lg-3 col-md-6">
            <h6 class="footer-heading">Categories</h6>
            <ul class="footer-links two-col">
              <?php foreach ($footer_cats as $fc): ?>
              <li><a href="<?= $base ?>/category.php?category=<?= urlencode($fc['slug']) ?>"><?= htmlspecialchars($fc['name']) ?></a></li>
              <?php endforeach; ?>
            </ul>
          </div>

          <!-- Popular News -->
          <div class="col-lg-4 col-md-6">
            <h6 class="footer-heading">Popular News</h6>
            <?php foreach ($popular as $p): ?>
            <div class="footer-news-item">
              <a href="<?= $base ?>/article.php?slug=<?= urlencode($p['slug']) ?>"><?= htmlspecialchars($p['title']) ?></a>
              <span class="footer-news-date"><?= date('M j, Y', strtotime($p['published_at'])) ?></span>
            </div>
            <?php endforeach; ?>
          </div>

        </div><!-- /row -->

        <div class="footer-bottom">
          <span>&copy; <?= date('Y') ?> | BSI/T 2E - GROUP 5 | Balitang Gaseño. All rights reserved.</span>
          <span>Gasan, Marinduque, Philippines</span>
        </div>

      </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="<?= $base ?>/assets/js/main.js"></script>

  </body>
</html>

