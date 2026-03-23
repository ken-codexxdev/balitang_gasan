<?php
// admin/includes/sidebar.php
$current_page = basename($_SERVER['PHP_SELF']);
$base = SITE_URL;
$user = current_user();
?>
<aside class="admin-sidebar" id="adminSidebar">
  <!-- Brand -->
  <div class="sidebar-brand">
    <span class="sidebar-brand-icon"><i class="bi bi-newspaper"></i></span>
    <div>
      <div class="sidebar-brand-name">BALITANG <span>GASEÑO</span></div>
      <div class="sidebar-brand-sub">Admin Panel</div>
    </div>
  </div>

  <!-- User -->
  <div class="sidebar-user">
    <div class="sidebar-user-avatar"><?= strtoupper(substr($user['name'] ?? 'A', 0, 1)) ?></div>
    <div>
      <div class="sidebar-user-name"><?= htmlspecialchars($user['name'] ?? 'Admin') ?></div>
      <div class="sidebar-user-role"><?= ucfirst($user['role'] ?? 'editor') ?></div>
    </div>
  </div>

  <!-- Navigation -->
  <nav class="sidebar-nav">
    <div class="sidebar-nav-label">Main</div>
    <a href="<?= $base ?>/admin/dashboard.php" class="sidebar-nav-link <?= $current_page === 'dashboard.php' ? 'active' : '' ?>">
      <i class="bi bi-speedometer2"></i> Dashboard
    </a>
    <a href="<?= $base ?>/admin/articles.php" class="sidebar-nav-link <?= in_array($current_page, ['articles.php','create.php','edit.php']) ? 'active' : '' ?>">
      <i class="bi bi-newspaper"></i> Articles
    </a>
    <a href="<?= $base ?>/admin/create.php" class="sidebar-nav-link <?= $current_page === 'create.php' ? 'active' : '' ?>">
      <i class="bi bi-plus-circle"></i> New Article
    </a>

    <div class="sidebar-nav-label">Manage</div>
    <a href="<?= $base ?>/admin/comments.php" class="sidebar-nav-link <?= $current_page === 'comments.php' ? 'active' : '' ?>">
      <i class="bi bi-chat-dots"></i> Comments
    </a>
    <a href="<?= $base ?>/admin/categories.php" class="sidebar-nav-link <?= $current_page === 'categories.php' ? 'active' : '' ?>">
      <i class="bi bi-tag"></i> Categories
    </a>
    <a href="<?= $base ?>/admin/subscribers.php" class="sidebar-nav-link <?= $current_page === 'subscribers.php' ? 'active' : '' ?>">
      <i class="bi bi-envelope-check"></i> Subscribers
    </a>

    <div class="sidebar-nav-label">System</div>
    <a href="<?= $base ?>/admin/profile.php" class="sidebar-nav-link <?= $current_page === 'profile.php' ? 'active' : '' ?>">
      <i class="bi bi-person-gear"></i> My Profile
    </a>
  </nav>

  <!-- Footer Links -->
  <div class="sidebar-footer">
    <a href="<?= $base ?>/index.php" target="_blank"><i class="bi bi-box-arrow-up-right"></i> View Website</a>
    <a href="<?= $base ?>/admin/logout.php"><i class="bi bi-box-arrow-right"></i> Sign Out</a>
  </div>
</aside>
