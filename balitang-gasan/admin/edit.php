<?php
require_once __DIR__ . '/../config/db.php';
require_login();

$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: ' . SITE_URL . '/admin/articles.php'); exit; }

// Fetch article
$stmt = $pdo->prepare("SELECT * FROM articles WHERE id = ?");
$stmt->execute([$id]);
$article = $stmt->fetch();
if (!$article) { $_SESSION['error'] = 'Article not found.'; header('Location: ' . SITE_URL . '/admin/articles.php'); exit; }

$categories = $pdo->query("SELECT id, name FROM categories ORDER BY name")->fetchAll();
$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title    = trim($_POST['title'] ?? '');
    $byline   = trim($_POST['byline'] ?? '');
    $cat      = (int)($_POST['category_id'] ?? 0);
    $body     = $_POST['body'] ?? '';
    $excerpt  = trim($_POST['excerpt'] ?? '');
    $status   = in_array($_POST['status'] ?? '', ['published','draft']) ? $_POST['status'] : 'draft';

    if (!$title || !$cat || !$body) {
        $error = 'Title, category, and body are required.';
    } else {
        // Update slug if title changed
        $slug = $article['slug'];
        if ($title !== $article['title']) {
            $new_slug = slugify($title);
            $base_slug = $new_slug;
            $i = 1;
            while (true) {
                $chk = $pdo->prepare("SELECT id FROM articles WHERE slug = ? AND id != ?");
                $chk->execute([$new_slug, $id]);
                if (!$chk->fetch()) break;
                $new_slug = $base_slug . '-' . $i++;
            }
            $slug = $new_slug;
        }

        // Image upload
        $image_name = $article['image'];
        $remove_img = isset($_POST['remove_image']);

        if ($remove_img) {
            if ($image_name && file_exists(UPLOAD_DIR . $image_name)) {
                unlink(UPLOAD_DIR . $image_name);
            }
            $image_name = null;
        }

        if (!empty($_FILES['image']['name'])) {
            $file    = $_FILES['image'];
            $ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg','jpeg','png','gif','webp'];
            if (!in_array($ext, $allowed)) {
                $error = 'Only JPG, PNG, GIF, WEBP are allowed.';
            } elseif ($file['size'] > 5 * 1024 * 1024) {
                $error = 'Image must be under 5MB.';
            } else {
                // Delete old
                if ($article['image'] && file_exists(UPLOAD_DIR . $article['image'])) {
                    unlink(UPLOAD_DIR . $article['image']);
                }
                $image_name = uniqid('art_', true) . '.' . $ext;
                if (!move_uploaded_file($file['tmp_name'], UPLOAD_DIR . $image_name)) {
                    $error = 'Failed to upload image.';
                    $image_name = $article['image'];
                }
            }
        }

        if (!$error) {
            // Publication date: use custom input if provided
            $custom_date = trim($_POST['published_at'] ?? '');
            if ($custom_date) {
                $dt = DateTime::createFromFormat('Y-m-d\TH:i', $custom_date);
                $pub_date = $dt ? $dt->format('Y-m-d H:i:s') : $article['published_at'];
            } elseif ($status === 'published' && !$article['published_at']) {
                $pub_date = date('Y-m-d H:i:s'); // first publish, no date set
            } else {
                $pub_date = $article['published_at']; // keep existing
            }

            $auto_excerpt = $excerpt ?: substr(strip_tags($body), 0, 200);

            $upd = $pdo->prepare("
                UPDATE articles
                SET title=?, slug=?, byline=?, category_id=?, body=?, excerpt=?, image=?, status=?, published_at=?
                WHERE id=?
            ");
            $upd->execute([$title, $slug, $byline, $cat, $body, $auto_excerpt, $image_name, $status, $pub_date, $id]);

            // Refresh article data
            $stmt->execute([$id]);
            $article = $stmt->fetch();
            $success = 'Article updated successfully!';
        }
    }
}

$base = SITE_URL;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Article — <?= SITE_NAME ?> Admin</title>
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
      <span class="admin-topbar-title"><i class="bi bi-pencil me-2 text-danger"></i>Edit Article</span>
    </div>
    <div class="admin-topbar-actions">
      <a href="<?= $base ?>/article.php?slug=<?= urlencode($article['slug']) ?>" target="_blank">
        <i class="bi bi-eye"></i> Preview
      </a>
      <a href="<?= $base ?>/admin/articles.php"><i class="bi bi-arrow-left"></i> Back</a>
    </div>
  </div>

  <div class="admin-content">
    <?php if ($error): ?>
      <div class="alert alert-danger alert-sm mb-3"><i class="bi bi-exclamation-circle me-1"></i><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
      <div class="alert alert-success alert-sm alert-auto-dismiss mb-3"><i class="bi bi-check-circle me-1"></i><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
      <div class="row g-4">
        <!-- Main Content -->
        <div class="col-lg-8">
          <div class="admin-card mb-4">
            <div class="admin-card-header"><i class="bi bi-type"></i> Article Content</div>
            <div class="admin-card-body">
              <div class="mb-3">
                <label class="form-label">Headline / Title <span class="text-danger">*</span></label>
                <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($article['title']) ?>" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Byline (Author Credit)</label>
                <input type="text" name="byline" class="form-control" placeholder="e.g. By Juan dela Cruz" value="<?= htmlspecialchars($article['byline'] ?? '') ?>">
              </div>
              <div class="mb-3">
                <label class="form-label">Article Body <span class="text-danger">*</span></label>
                <textarea name="body" id="body" class="form-control" rows="16"><?= htmlspecialchars($article['body']) ?></textarea>
              </div>
              <div class="mb-0">
                <label class="form-label">Excerpt</label>
                <textarea name="excerpt" class="form-control" rows="3"><?= htmlspecialchars($article['excerpt'] ?? '') ?></textarea>
              </div>
            </div>
          </div>
        </div>

        <!-- Sidebar Options -->
        <div class="col-lg-4">

          <!-- Publish Panel -->
          <div class="admin-card mb-4">
            <div class="admin-card-header"><i class="bi bi-cloud-upload"></i> Publish</div>
            <div class="admin-card-body">
              <div class="mb-2" style="font-size:12px;color:var(--text-muted);">
                <i class="bi bi-clock me-1"></i>
                Created: <?= date('M j, Y g:i a', strtotime($article['created_at'])) ?><br>
                <?php if ($article['published_at']): ?>
                  <i class="bi bi-calendar-check me-1"></i>
                  Published: <?= date('M j, Y g:i a', strtotime($article['published_at'])) ?>
                <?php endif; ?>
              </div>
              <div class="mb-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                  <option value="draft" <?= $article['status'] === 'draft' ? 'selected' : '' ?>>Draft</option>
                  <option value="published" <?= $article['status'] === 'published' ? 'selected' : '' ?>>Published</option>
                </select>
              </div>
              <div class="mb-3">
                <label class="form-label">
                  <i class="bi bi-calendar-date me-1 text-danger"></i>Publication Date &amp; Time
                </label>
                <?php
                  // Pre-fill with existing published_at, or current datetime as fallback
                  $pre_date = $article['published_at']
                    ? date('Y-m-d\TH:i', strtotime($article['published_at']))
                    : date('Y-m-d\TH:i');
                ?>
                <input type="datetime-local" name="published_at" class="form-control"
                       value="<?= htmlspecialchars($pre_date) ?>">
                <div class="form-text">Change to update the article's publication date.</div>
              </div>
              <div class="d-grid gap-2">
                <button type="submit" class="btn btn-danger fw-bold"><i class="bi bi-floppy me-1"></i> Update Article</button>
                <a href="<?= $base ?>/admin/delete_article.php?id=<?= $article['id'] ?>" class="btn btn-outline-danger"
                   onclick="return confirm('Delete this article? This cannot be undone.')">
                  <i class="bi bi-trash me-1"></i> Delete Article
                </a>
              </div>
            </div>
          </div>

          <!-- Category -->
          <div class="admin-card mb-4">
            <div class="admin-card-header"><i class="bi bi-tag"></i> Category</div>
            <div class="admin-card-body">
              <select name="category_id" class="form-select" required>
                <option value="">— Select Category —</option>
                <?php foreach ($categories as $cat): ?>
                  <option value="<?= $cat['id'] ?>" <?= $article['category_id'] == $cat['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cat['name']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <!-- Featured Image -->
          <div class="admin-card mb-4">
            <div class="admin-card-header"><i class="bi bi-image"></i> Featured Image</div>
            <div class="admin-card-body">
              <?php if ($article['image']): ?>
                <img src="<?= UPLOAD_URL . htmlspecialchars($article['image']) ?>" alt="" style="width:100%;max-height:160px;object-fit:cover;border-radius:5px;margin-bottom:10px;">
                <div class="form-check mb-2">
                  <input class="form-check-input" type="checkbox" name="remove_image" id="remove_img">
                  <label class="form-check-label" for="remove_img" style="font-size:13px;">Remove current image</label>
                </div>
              <?php endif; ?>
              <input type="file" name="image" id="image-upload" class="form-control mb-2" accept="image/*" onchange="previewImage(this)">
              <div class="form-text">Upload new image to replace the current one.</div>
              <img id="img-preview" src="" alt="Preview">
            </div>
          </div>

        </div><!-- /col-lg-4 -->
      </div><!-- /row -->
    </form>
  </div>
</div>

<script src="https://cdn.tiny.cloud/1/60eehv5ufru7jk9kzt27dtovuqfnbmgq2hgevwnok1a5tdym/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
tinymce.init({
  selector: '#body',
  height: 460,
  menubar: false,
  plugins: ['lists','link','image','charmap','preview','anchor','searchreplace','visualblocks','code','fullscreen','insertdatetime','media','table','help','wordcount'],
  toolbar: 'undo redo | blocks | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist | link image | removeformat | code fullscreen | help',
  branding: false,
  promotion: false,
  content_style: 'body { font-family: Georgia, serif; font-size:16px; line-height:1.7; color:#2c2c2c; }'
});

function previewImage(input) {
  const preview = document.getElementById('img-preview');
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = e => { preview.src = e.target.result; preview.style.display = 'block'; };
    reader.readAsDataURL(input.files[0]);
  }
}

function toggleSidebar() { document.getElementById('adminSidebar').classList.toggle('open'); }
document.querySelectorAll('.alert-auto-dismiss').forEach(el => {
  setTimeout(() => { el.style.opacity='0'; setTimeout(() => el.remove(),500); }, 4000);
});
</script>
</body>
</html>
