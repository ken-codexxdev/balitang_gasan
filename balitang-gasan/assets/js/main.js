
document.addEventListener('DOMContentLoaded', function () {

  // ── Newsletter AJAX ───────────────────────────────────────
  const nwForm = document.getElementById('newsletter-form');
  if (nwForm) {
    nwForm.addEventListener('submit', async function (e) {
      e.preventDefault();
      const msg  = document.getElementById('subscribe-msg');
      const email = nwForm.querySelector('input[name=email]').value.trim();
      if (!email) return;

      msg.innerHTML = '<span class="text-warning">Subscribing…</span>';
      try {
        const res  = await fetch('/balitang-gasan/subscribe.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: 'email=' + encodeURIComponent(email)
        });
        const data = await res.json();
        if (data.success) {
          msg.innerHTML = '<span class="text-success"><i class="bi bi-check-circle me-1"></i>' + data.message + '</span>';
          nwForm.reset();
        } else {
          msg.innerHTML = '<span class="text-danger"><i class="bi bi-exclamation-circle me-1"></i>' + data.message + '</span>';
        }
      } catch (error) {
        msg.innerHTML = '<span class="text-danger">Something went wrong. Try again.</span>';
      }
    });
  }

  // ── Comment Form AJAX ─────────────────────────────────────
  const commentForm = document.getElementById('comment-form');
  if (commentForm) {
    commentForm.addEventListener('submit', async function (e) {
      e.preventDefault();
      const msg  = document.getElementById('comment-msg');
      const btn  = commentForm.querySelector('button[type=submit]');
      const data = new URLSearchParams(new FormData(commentForm));

      btn.disabled = true;
      btn.textContent = 'Submitting…';
      msg.innerHTML = '';

      try {
        const res = await fetch('/balitang-gasan/submit_comment.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: data
        });
        const json = await res.json();
        if (json.success) {
          msg.innerHTML = '<div class="alert alert-success alert-sm mt-2">' + json.message + '</div>';
          commentForm.reset();
        } else {
          msg.innerHTML = '<div class="alert alert-danger alert-sm mt-2">' + json.message + '</div>';
        }
      } catch (error) {
        msg.innerHTML = '<div class="alert alert-danger alert-sm mt-2">Something went wrong.</div>';
      } finally {
        btn.disabled = false;
        btn.textContent = 'Post Comment';
      }
    });
  }

  // ── Sticky Nav Highlight on Scroll ───────────────────────
  // Smooth scroll anchor links
  document.querySelectorAll('a[href^="#"]').forEach(a => {
    a.addEventListener('click', function (e) {
      const target = document.querySelector(this.getAttribute('href'));
      if (target) {
        e.preventDefault();
        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    });
  });

  // ── View counter ping ─────────────────────────────────────
  const articleId = document.querySelector('meta[name=article-id]');
  if (articleId) {
    fetch('/balitang-gasan/count_article-view.php?id=' + articleId.content).catch(() => {});
  }

  // ── Auto-dismiss alerts ───────────────────────────────────
  document.querySelectorAll('.alert-auto-dismiss').forEach(el => {
    setTimeout(() => {
      el.style.transition = 'opacity .5s';
      el.style.opacity = '0';
      setTimeout(() => el.remove(), 500);
    }, 4000);
  });

});
