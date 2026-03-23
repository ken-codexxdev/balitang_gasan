<?php
require_once __DIR__ . '/../config/db.php';
require_login();

$error   = '';
$success = '';
$edit_cat = null;

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add' || $action === 'edit') {
        $name = trim($_POST['name'] ?? '');
        $desc = trim($_POST['description'] ?? '');
        $cid  = (int)($_POST['cat_id'] ?? 0);

        if (!$name) {
            $error = 'Category name is required.';
        } else {
            $slug = slugify($name);
            if ($action === 'add') {
                try {
                    $pdo->prepare("INSERT INTO categories (name, slug, description) VALUES (?,?,?)")
                        ->execute([$name, $slug, $desc]);
                    $success = 'Category "' . $name . '" added successfully.';
                } catch (PDOException $e) {
                    $error = 'A category with that name/slug already exists.';
                }
            } else {
                $pdo->prepare("UPDATE categories SET name=?, slug=?, description=? WHERE id=?")
                    ->execute([$name, $slug, $desc, $cid]);
                $success = 'Category updated.';
            }
        }
    } elseif ($action === 'delete') {
        $cid = (int)($_POST['cat_id'] ?? 0);
        // Check if articles exist
        $cnt = $pdo->prepare("SELECT COUNT(*) FROM articles WHERE category_id = ?");
        $cnt->execute([$cid]);
        if ($cnt->fetchColumn() > 0) {
            $error = 'Cannot delete: this category has articles. Reassign them first.';
        } else {
            $pdo->prepare("DELETE FROM categories WHERE id = ?")->execute([$cid]);
            $success = 'Category deleted.';
        }
    }
}

// Load edit
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([(int)$_GET['edit']]);
    $edit_cat = $stmt->fetch();
}

// All categories with count
$categories = $pdo->query("
    SELECT c.*, COUNT(a.id) AS article_count
    FROM categories c
    LEFT JOIN articles a ON a.category_id = c.id
    GROUP BY c.id ORDER BY c.name
")->fetchAll();

$base = SITE_URL;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Categories — <?= SITE_NAME ?> Admin</title>
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
      <span class="admin-topbar-title"><i class="bi bi-tag me-2 text-danger"></i>Categories</span>
    </div>
  </div>

  <div class="admin-content">
    <?php if ($error): ?>
      <div class="alert alert-danger alert-sm mb-3"><i class="bi bi-exclamation-circle me-1"></i><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
      <div class="alert alert-success alert-sm alert-auto-dismiss mb-3"><i class="bi bi-check-circle me-1"></i><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <div class="row g-4">
      <!-- Form -->
      <div class="col-lg-4">
        <div class="admin-card">
          <div class="admin-card-header">
            <i class="bi bi-<?= $edit_cat ? 'pencil' : 'plus-circle' ?>"></i>
            <?= $edit_cat ? 'Edit Category' : 'Add New Category' ?>
          </div>
          <div class="admin-card-body">
            <form method="POST">
              <input type="hidden" name="action" value="<?= $edit_cat ? 'edit' : 'add' ?>">
              <?php if ($edit_cat): ?>
                <input type="hidden" name="cat_id" value="<?= $edit_cat['id'] ?>">
              <?php endif; ?>
              <div class="mb-3">
                <label class="form-label">Category Name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control"
                       value="<?= htmlspecialchars($edit_cat['name'] ?? '') ?>" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($edit_cat['description'] ?? '') ?></textarea>
              </div>
              <div class="d-grid gap-2">
                <button type="submit" class="btn btn-danger fw-bold">
                  <i class="bi bi-floppy me-1"></i><?= $edit_cat ? 'Update Category' : 'Add Category' ?>
                </button>
                <?php if ($edit_cat): ?>
                  <a href="<?= $base ?>/admin/categories.php" class="btn btn-outline-secondary">Cancel</a>
                <?php endif; ?>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- Table -->
      <div class="col-lg-8">
        <div class="admin-table">
          <div class="table-responsive">
            <table class="table table-hover mb-0">
              <thead>
                <tr>
                  <th>Name</th>
                  <th>Slug</th>
                  <th>Description</th>
                  <th>Articles</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($categories as $cat): ?>
                <tr>
                  <td><strong style="font-size:13px;"><?= htmlspecialchars($cat['name']) ?></strong></td>
                  <td><code style="font-size:12px;"><?= htmlspecialchars($cat['slug']) ?></code></td>
                  <td style="font-size:13px;max-width:200px;"><?= htmlspecialchars(substr($cat['description'] ?? '', 0, 60)) ?></td>
                  <td>
                    <a href="<?= $base ?>/admin/articles.php?cat=<?= $cat['id'] ?>" class="badge bg-danger text-decoration-none">
                      <?= $cat['article_count'] ?>
                    </a>
                  </td>
                  <td style="white-space:nowrap;">
                    <a href="?edit=<?= $cat['id'] ?>" class="btn btn-sm btn-outline-secondary" title="Edit">
                      <i class="bi bi-pencil"></i>
                    </a>
                    <form method="POST" class="d-inline ms-1" onsubmit="return confirm('Delete this category?')">
                      <input type="hidden" name="action" value="delete">
                      <input type="hidden" name="cat_id" value="<?= $cat['id'] ?>">
                      <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                        <i class="bi bi-trash"></i>
                      </button>
                    </form>
                    <a href="<?= $base ?>/category.php?category=<?= urlencode($cat['slug']) ?>" target="_blank"
                       class="btn btn-sm btn-outline-secondary ms-1" title="View on site">
                      <i class="bi bi-box-arrow-up-right"></i>
                    </a>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
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
