<?php
require_once __DIR__ . '/config/db.php';
http_response_code(404);
$page_title = 'Page Not Found';
?>
<?php include __DIR__ . '/includes/header.php'; ?>
<div class="container py-5 text-center">
  <div style="font-size:120px;font-weight:900;color:#ddd;font-family:var(--font-head);line-height:1;">404</div>
  <h2 style="font-family:var(--font-head);font-size:1.8rem;color:var(--dark);">Page Not Found</h2>
  <p style="font-family:var(--font-ui);color:var(--text-muted);margin-bottom:24px;">
    The article or page you're looking for may have been removed or does not exist.
  </p>
  <a href="<?= SITE_URL ?>/index.php" class="btn btn-danger px-4">
    <i class="bi bi-house me-1"></i> Go to Homepage
  </a>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
