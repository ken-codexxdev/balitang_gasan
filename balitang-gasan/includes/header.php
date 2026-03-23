<?php
// includes/header.php — shared public header
require_once __DIR__ . '/../config/db.php';

// Fetch all categories for navigation
$cats = $pdo->query("SELECT id, name, slug FROM categories ORDER BY name ASC")->fetchAll();

// Active category from URL
$active_cat = $_GET['category'] ?? '';

// Search query
$search_q = htmlspecialchars(trim($_GET['q'] ?? ''));

// Determine base path for links (works in subdirectories)
$base = SITE_URL;
?>


<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? htmlspecialchars($page_title) . ' — ' . SITE_NAME : SITE_NAME  ?></title>
    <meta name="description" content="<?= isset($page_desc) ? htmlspecialchars($page_desc) : 'Your premier source for local news in Gasan, Marinduque.' ?>">

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
  <!-- ===== TOP BAR ===== -->
    <div class="topbar">
      <div class="container">
        <div class="d-flex justify-content-between align-items-center">
          <span class="topbar-date"><i class="bi bi-calendar3 me-1"></i><?= date('F j, Y') ?> | Gasan, Marinduque, Philippines</span>
          <div class="topbar-right">
            <a href="<?= $base ?>/search.php" title="Search"><i class="bi bi-search"></i></a>
            <?php if (is_logged_in()): ?>
              <a href="<?= $base ?>/admin/dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a>
              <a href="<?= $base ?>/admin/logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
            <?php else: ?>
              <a href="<?= $base ?>/admin/login.php"><i class="bi bi-person-circle" onclick="alert('You are about to enter the Balitang Gaseño Admin Panel. Please be advised this is for administrator only. Thank you.')"></i></a>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>

    <!-- ===== MAIN HEADER ===== -->
    <header class="main-header">
      <div class="container">
        <div class="d-flex align-items-center justify-content-between py-3">
          <a href="<?= $base ?>/index.php" class="brand-link text-decoration-none">
            <div class="brand-logo">
              <span class="brand-icon"><i class="bi bi-newspaper"></i></span>
              <div>
                <div class="brand-name">BALITANG<span> GASEÑO</span></div>
                <div class="brand-tagline">Pusong Gasan, Balitang Makabayan</div>
              </div>
            </div>
          </a>
          <!-- Search bar (desktop) -->
          <form action="<?= $base ?>/search.php" method="GET" class="search-form d-none d-lg-flex">
            <input type="text" name="q" class="form-control search-input" placeholder="Search articles…" value="<?= $search_q ?>">
            <button type="submit" class="btn btn-search"><i class="bi bi-search"></i></button>
          </form>
        </div>
      </div>
    </header>

    <!-- ===== NAVIGATION ===== -->
    <nav class="main-nav navbar navbar-expand-lg">
      <div class="container">
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
          <i class="bi bi-list text-white fs-4"></i>
        </button>
        <div class="collapse navbar-collapse" id="mainNav">
          <ul class="navbar-nav me-auto">
            <li class="nav-item">
              <a class="nav-link <?= !$active_cat ? 'active' : '' ?>" href="<?= $base ?>/index.php">Home</a>
            </li>
            <?php foreach ($cats as $cat): ?>
            <li class="nav-item">
              <a class="nav-link <?= ($active_cat === $cat['slug']) ? 'active' : '' ?>"
                href="<?= $base ?>/category.php?category=<?= urlencode($cat['slug']) ?>">
                <?= htmlspecialchars($cat['name']) ?>
              </a>
            </li>
            <?php endforeach; ?>
            <li class="nav-item">
              <a class="nav-link" href="<?= $base ?>/contact.php">Contact Us</a>
            </li>
          </ul>
        </div>
      </div>
    </nav>
  </body>
</html>