<?php
require_once __DIR__ . '/../config/db.php';
require_login();

$categories = $pdo->query("SELECT id, name FROM categories ORDER BY name")->fetchAll();
$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title      = trim($_POST['title'] ?? '');
    $byline     = trim($_POST['byline'] ?? '');
    $category   = (int)($_POST['category_id'] ?? 0);
    $body       = $_POST['body'] ?? '';
    $excerpt    = trim($_POST['excerpt'] ?? '');
    $status     = in_array($_POST['status'] ?? '', ['published','draft']) ? $_POST['status'] : 'draft';
    $user_id    = current_user()['id'];

    if (!$title || !$category || !$body) {
        $error = 'Title, category, and body are required.';
    } else {
        // Generate unique slug
        $slug = slugify($title);
        $base_slug = $slug;
        $i = 1;
        while (true) {
            $chk = $pdo->prepare("SELECT id FROM articles WHERE slug = ?");
            $chk->execute([$slug]);
            if (!$chk->fetch()) break;
            $slug = $base_slug . '-' . $i++;
        }

        // Handle image upload
        $image_name = null;
        if (!empty($_FILES['image']['name'])) {
            $file     = $_FILES['image'];
            $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowed  = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            if (!in_array($ext, $allowed)) {
                $error = 'Only JPG, PNG, GIF, WEBP images are allowed.';
            } elseif ($file['size'] > 5 * 1024 * 1024) {
                $error = 'Image must be under 5MB.';
            } else {
                $image_name = uniqid('art_', true) . '.' . $ext;
                $dest = UPLOAD_DIR . $image_name;
                if (!move_uploaded_file($file['tmp_name'], $dest)) {
                    $error = 'Failed to upload image. Check folder permissions.';
                    $image_name = null;
                }
            }
        }

        if (!$error) {
            // Publication date: use custom input if provided, else auto-set for published articles
            $custom_date = trim($_POST['published_at'] ?? '');
            if ($custom_date) {
                // Validate the datetime format from the input
                $dt = DateTime::createFromFormat('Y-m-d\TH:i', $custom_date);
                $pub_date = $dt ? $dt->format('Y-m-d H:i:s') : date('Y-m-d H:i:s');
            } else {
                $pub_date = ($status === 'published') ? date('Y-m-d H:i:s') : null;
            }
            $auto_excerpt = $excerpt ?: substr(strip_tags($body), 0, 200);

            $stmt = $pdo->prepare("
                INSERT INTO articles (title, slug, byline, category_id, author_id, body, excerpt, image, status, published_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$title, $slug, $byline, $category, $user_id, $body, $auto_excerpt, $image_name, $status, $pub_date]);
            $new_id = $pdo->lastInsertId();

            $_SESSION['success'] = 'Article "' . $title . '" has been created successfully!';
            header('Location: ' . SITE_URL . '/admin/articles.php');
            exit;
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
    <title>Create Article — <?= SITE_NAME ?> Admin</title>
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
          <span class="admin-topbar-title"><i class="bi bi-plus-circle me-2 text-danger"></i>Create New Article</span>
        </div>
        <div class="admin-topbar-actions">
          <a href="<?= $base ?>/admin/articles.php"><i class="bi bi-arrow-left"></i> Back</a>
        </div>
      </div>

      <div class="admin-content">
        <?php if ($error): ?>
          <div class="alert alert-danger alert-sm mb-3"><i class="bi bi-exclamation-circle me-1"></i><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
          <div class="row g-4">
            <!-- Left Column: Main Content -->
            <div class="col-lg-8">

              <div class="admin-card mb-4">
                <div class="admin-card-header"><i class="bi bi-type"></i> Article Content</div>
                <div class="admin-card-body">
                  <div class="mb-3">
                    <label class="form-label">Headline / Title <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control" placeholder="Enter article headline…"
                          value="<?= htmlspecialchars($_POST['title'] ?? '') ?>" required>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Byline (Author Credit)</label>
                    <input type="text" name="byline" class="form-control" placeholder="e.g. By Juan dela Cruz"
                          value="<?= htmlspecialchars($_POST['byline'] ?? '') ?>">
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Article Body <span class="text-danger">*</span></label>
                    <textarea name="body" id="body" class="form-control" rows="16"><?= htmlspecialchars($_POST['body'] ?? '') ?></textarea>
                  </div>
                  <div class="mb-0">
                    <label class="form-label">Excerpt <span class="text-muted" style="font-weight:400;">(optional — auto-generated if blank)</span></label>
                    <textarea name="excerpt" class="form-control" rows="3" placeholder="Brief summary shown in article listings…"><?= htmlspecialchars($_POST['excerpt'] ?? '') ?></textarea>
                  </div>
                </div>
              </div>

            </div><!-- /col-lg-8 -->

            <!-- Right Column: Sidebar Options -->
            <div class="col-lg-4">

              <!-- Publish -->
              <div class="admin-card mb-4">
                <div class="admin-card-header"><i class="bi bi-cloud-upload"></i> Publish</div>
                <div class="admin-card-body">
                  <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                      <option value="draft" <?= ($_POST['status'] ?? '') !== 'published' ? 'selected' : '' ?>>Save as Draft</option>
                      <option value="published" <?= ($_POST['status'] ?? '') === 'published' ? 'selected' : '' ?>>Publish Now</option>
                    </select>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">
                      <i class="bi bi-calendar-date me-1 text-danger"></i>Publication Date &amp; Time
                    </label>
                    <input type="datetime-local" name="published_at" class="form-control"
                          value="<?= htmlspecialchars($_POST['published_at'] ?? date('Y-m-d\TH:i')) ?>">
                    <div class="form-text">Leave as-is to use the current date and time, or set a custom date.</div>
                  </div>
                  <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-danger fw-bold"><i class="bi bi-floppy me-1"></i> Save Article</button>
                    <a href="<?= $base ?>/admin/articles.php" class="btn btn-outline-secondary">Cancel</a>
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
                      <option value="<?= $cat['id'] ?>" <?= ($_POST['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
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
                  <input type="file" name="image" id="image-upload" class="form-control mb-2" accept="image/*" onchange="previewImage(this)">
                  <div class="form-text">JPG, PNG, GIF or WEBP. Max 5MB.</div>
                  <img id="img-preview" src="" alt="Preview">
                </div>
              </div>

            </div><!-- /col-lg-4 -->
          </div>
        </form>
      </div>
    </div>

    <!-- TinyMCE (API) -->
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
        skin: 'oxide',
        content_css: 'default',
        content_style: 'body { font-family: Georgia, serif; font-size:16px; line-height:1.7; color:#2c2c2c; max-width:100%; }'
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
    </script>
  </body>
</html>
