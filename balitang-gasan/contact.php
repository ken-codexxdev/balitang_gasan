<?php
require_once __DIR__ . '/config/db.php';
$page_title = 'Contact Us';
$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    if (!$name || !$email || !$subject || !$message) {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        // In a real deployment, send email here via mail() or PHPMailer
        $success = 'Thank you, ' . htmlspecialchars($name) . '! Your message has been received. We will get back to you shortly.';
    }
}
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
    <div style="background:var(--dark);color:#fff;padding:40px 0 24px;">
      <div class="container">
        <h1 style="font-family:var(--font-head);font-size:2rem;font-weight:800;">CONTACT US</h1>
        <p style="color:rgba(255,255,255,.7);">Get in touch with the Balitang Gaseño team.</p>
      </div>
    </div>

    <div class="container py-4">
      <div class="row g-4">
        <div class="col-lg-7">
          <div class="admin-card" style="background:#fff;border:1px solid var(--border);border-radius:6px;">
            <div style="padding:20px;border-bottom:1px solid var(--border);font-weight:700;font-family:var(--font-ui);">
              <i class="bi bi-envelope me-2 text-danger"></i>Send a Message
            </div>
            <div style="padding:24px;">
              <?php if ($success): ?>
                <div class="alert alert-success"><i class="bi bi-check-circle me-1"></i><?= $success ?></div>
              <?php endif; ?>
              <?php if ($error): ?>
                <div class="alert alert-danger"><i class="bi bi-exclamation-circle me-1"></i><?= htmlspecialchars($error) ?></div>
              <?php endif; ?>
              <form method="POST">
                <div class="row g-3 mb-3">
                  <div class="col-md-6">
                    <label class="form-label">Name *</label>
                    <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Email *</label>
                    <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                  </div>
                </div>
                <div class="mb-3">
                  <label class="form-label">Subject *</label>
                  <input type="text" name="subject" class="form-control" required value="<?= htmlspecialchars($_POST['subject'] ?? '') ?>">
                </div>
                <div class="mb-3">
                  <label class="form-label">Message *</label>
                  <textarea name="message" class="form-control" rows="6" required><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
                </div>
                <button type="submit" class="btn btn-danger px-4 fw-bold">Send Message <i class="bi bi-send ms-1"></i></button>
              </form>
            </div>
          </div>
        </div>

        <div class="col-lg-5">
          <div class="sidebar-widget">
            <div class="sidebar-widget-title">Our Office</div>
            <p style="font-family:var(--font-ui);font-size:14px;line-height:1.7;color:var(--text-muted);">
              <i class="bi bi-geo-alt-fill text-danger me-2"></i>
              Brgy. Pangi, Gasan, Marinduque, Philippines<br>
              <i class="bi bi-envelope-fill text-danger me-2"></i>
              balitang.gaseño@news.ph<br>
              <i class="bi bi-telephone-fill text-danger me-2"></i>
              (042) 7654 321
            </p>
            <hr>
            <div class="sidebar-widget-title">Newsroom Hours</div>
            <p style="font-family:var(--font-ui);font-size:14px;line-height:1.7;color:var(--text-muted);">
              Monday – Friday: 8:00 AM – 5:00 PM<br>
              Saturday: 9:00 AM – 12:00 PM<br>
              Sunday: Closed
            </p>
            <hr>
            <div class="sidebar-widget-title">Tip Line</div>
            <p style="font-family:var(--font-ui);font-size:14px;line-height:1.7;color:var(--text-muted);">
              Have a news tip? We welcome community submissions and story leads. Use the form to send us your tip securely.
            </p>
          </div>
        </div>
      </div>
    </div>

    <?php include __DIR__ . '/includes/footer.php'; ?>
  </body>
</html>

