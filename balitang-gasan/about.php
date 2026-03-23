<?php
require_once __DIR__ . '/config/db.php';
$page_title = 'About Us';
$page_desc  = 'Meet the dedicated team behind Balitang Gaseño — your trusted local journalism platform in Gasan, Marinduque.';
$base = SITE_URL;
?>
<?php include __DIR__ . '/includes/header.php'; ?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?= $base ?>/assets/css/about-design.css">
  </head>
  <body>
    <section class="about-hero">
      <div class="container about-hero-content">
        <nav aria-label="breadcrumb" class="mb-3">
          <ol class="breadcrumb" style="background:none;padding:0;margin:0;">
            <li class="breadcrumb-item"><a href="<?= $base ?>/index.php" style="color:rgba(255,255,255,.5);">Home</a></li>
            <li class="breadcrumb-item active" style="color:rgba(255,255,255,.8);">About Us</li>
          </ol>
        </nav>
        <span class="about-hero-badge"><i class="bi bi-newspaper me-1"></i> Gasan, Marinduque</span>
        <h1>Totoong Balita, <span>Walang Labis</span>.<br>Told by Gaseño</h1>
        <p class="about-hero-sub">
          Balitang Gaseño is a dedicated web-based local journalism platform committed to delivering timely, accurate, and community-centered news to the people of Gasan, Marinduque.
        </p>
        <div class="about-hero-stats">
          <div>
            <div class="hero-stat-val">2024</div>
            <div class="hero-stat-label">Founded</div>
          </div>
          <div>
            <div class="hero-stat-val">4+</div>
            <div class="hero-stat-label">Team Members</div>
          </div>
          <div>
            <div class="hero-stat-val">100%</div>
            <div class="hero-stat-label">Local Coverage</div>
          </div>
          <div>
            <div class="hero-stat-val">Free</div>
            <div class="hero-stat-label">Always Accessible</div>
          </div>
        </div>
      </div>
    </section>

    <!-- ===== MISSION / VALUES ===== -->
    <section class="mission-section">
      <div class="container">
        <div class="text-center mb-5">
          <div class="section-title justify-content-center"><i class="bi bi-bullseye"></i> Our Mission & Values</div>
          <p style="font-family:var(--font-ui);font-size:15px;color:var(--text-muted);max-width:580px;margin:0 auto;">
            We believe every community deserves a reliable, accessible, and honest source of local news. These are the principles that guide everything we do.
          </p>
        </div>
        <div class="row g-4">
          <div class="col-md-6 col-lg-3">
            <div class="mission-card">
              <div class="mission-icon red"><i class="bi bi-shield-check"></i></div>
              <h5>Truth &amp; Accuracy</h5>
              <p>We verify every fact before publishing. Our commitment to truth is non-negotiable — Gasan deserves journalism it can trust.</p>
            </div>
          </div>
          <div class="col-md-6 col-lg-3">
            <div class="mission-card">
              <div class="mission-icon blue"><i class="bi bi-people"></i></div>
              <h5>Community First</h5>
              <p>Every story we tell centers the people of Gasan. We amplify local voices, celebrate community achievements, and hold power accountable.</p>
            </div>
          </div>
          <div class="col-md-6 col-lg-3">
            <div class="mission-card">
              <div class="mission-icon green"><i class="bi bi-lightning"></i></div>
              <h5>Timeliness</h5>
              <p>News waits for no one. We deliver stories promptly so the Gasan community stays informed and engaged with what matters.</p>
            </div>
          </div>
          <div class="col-md-6 col-lg-3">
            <div class="mission-card">
              <div class="mission-icon gold"><i class="bi bi-eye"></i></div>
              <h5>Transparency</h5>
              <p>We are open about our sources, our methods, and our process. Honest journalism builds the community trust we rely on.</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ===== MEET THE TEAM ===== -->
    <section class="team-section">
      <div class="container">
        <div class="text-center">
          <div class="section-title justify-content-center"><i class="bi bi-person-badge"></i> Meet the Team</div>
          <h2 class="team-section-title">The People Behind Balitang Gaseño</h2>
          <p class="team-section-sub">Four passionate locals working together to keep the Gasan community informed, engaged, and connected.</p>
        </div>

        <div class="row g-4">

          <!-- ── 1. Editor ── -->
          <div class="col-lg-6 col-xl-3">
            <div class="team-card">
              <div class="team-card-img-wrap" style="background:linear-gradient(135deg,#1a1a2e 0%,#2c1654 100%);">
                <!-- SVG Avatar: Editor (woman, dark hair) -->
                <svg class="team-avatar-svg" width="160" height="200" viewBox="0 0 160 200" xmlns="http://www.w3.org/2000/svg">
                  <img src="profile-pictures/barong-tagalog.png" alt="img2">
                </svg>
                <div class="team-role-ribbon">Editor-in-Chief</div>
              </div>
              <div class="team-card-body">
                <div class="team-name">Ken Zander Paragas</div>
                <div class="team-role-text"><i class="bi bi-pencil-square me-1"></i>Editor-in-Chief</div>
                <p class="team-bio">
                  A seasoned journalist with over eight years of experience in local and regional media, Ken Zander Paragas leads Balitang Gaseño with passion and precision. A proud native of Gasan, he holds a degree in Communication Arts from Marinduque State College and has dedicated her career to elevating local storytelling. He oversees editorial direction, ensures factual accuracy, and mentors the newsroom team.
                </p>
                <div class="team-skills">
                  <span class="team-skill-tag">Editorial Leadership</span>
                  <span class="team-skill-tag">Fact-Checking</span>
                  <span class="team-skill-tag">Investigative Reporting</span>
                </div>
                <div class="team-socials">
                  <a href="#" class="team-social-btn" title="Facebook"><i class="bi bi-facebook"></i></a>
                  <a href="#" class="team-social-btn" title="Twitter / X"><i class="bi bi-twitter-x"></i></a>
                  <a href="mailto:editor@balitanggaseño.news.ph" class="team-social-btn" title="Email"><i class="bi bi-envelope"></i></a>
                </div>
              </div>
            </div>
          </div>

          <!-- ── 2. Reporter ── -->
          <div class="col-lg-6 col-xl-3">
            <div class="team-card">
              <div class="team-card-img-wrap" style="background:linear-gradient(135deg,#0f3460 0%,#16213e 100%);">
                <!-- SVG Avatar: Reporter (man, short hair) -->
                <svg class="team-avatar-svg" width="160" height="200" viewBox="0 0 160 200" xmlns="http://www.w3.org/2000/svg">
                  <img src="profile-pictures/danica-pic.jpg" alt="img2">
                </svg>
                <div class="team-role-ribbon">Field Reporter</div>
              </div>
              <div class="team-card-body">
                <div class="team-name">Danica Frias</div>
                <div class="team-role-text"><i class="bi bi-mic me-1"></i>Field Reporter</div>
                <p class="team-bio">
                  Danica Frias is Balitang Gaseño's boots-on-the-ground reporter, covering everything from barangay assemblies to municipal council sessions and community events. A Gasan native, she studied Journalism at the University of the Philippines and brings a deep personal connection to every story she covers. Her approachable personality and sharp instincts make her a trusted face in the community.
                </p>
                <div class="team-skills">
                  <span class="team-skill-tag">Field Reporting</span>
                  <span class="team-skill-tag">Interviewing</span>
                  <span class="team-skill-tag">Breaking News</span>
                </div>
                <div class="team-socials">
                  <a href="#" class="team-social-btn" title="Facebook"><i class="bi bi-facebook"></i></a>
                  <a href="#" class="team-social-btn" title="Twitter / X"><i class="bi bi-twitter-x"></i></a>
                  <a href="mailto:reporter@balitanggaseño.news.ph" class="team-social-btn" title="Email"><i class="bi bi-envelope"></i></a>
                </div>
              </div>
            </div>
          </div>

          <!-- ── 3. Content Writer ── -->
          <div class="col-lg-6 col-xl-3">
            <div class="team-card">
              <div class="team-card-img-wrap" style="background:linear-gradient(135deg,#1a3a1a 0%,#0d2b1a 100%);">
                <!-- SVG Avatar: Content Writer (woman, curly hair) -->
                <svg class="team-avatar-svg" width="160" height="200" viewBox="0 0 160 200" xmlns="http://www.w3.org/2000/svg">
                    <img src="profile-pictures/barong-tagalog.png" alt="img2">
                </svg>
                <div class="team-role-ribbon">Content Writer</div>
              </div>
              <div class="team-card-body">
                <div class="team-name">Jessica De Belen</div>
                <div class="team-role-text"><i class="bi bi-pen me-1"></i>Content Writer</div>
                <p class="team-bio">
                  Jessica De Belen brings warmth and clarity to every article she writes. Specializing in human-interest stories, community features, and lifestyle content, she has a gift for turning everyday moments into compelling narratives. She graduated with honors from the Philippine Normal University with a degree in English Language Studies and returned Gasan to tell the stories of her hometown. She also manages the platform's social media presence.
                </p>
                <div class="team-skills">
                  <span class="team-skill-tag">Feature Writing</span>
                  <span class="team-skill-tag">SEO Content</span>
                  <span class="team-skill-tag">Social Media</span>
                </div>
                <div class="team-socials">
                  <a href="#" class="team-social-btn" title="Facebook"><i class="bi bi-facebook"></i></a>
                  <a href="#" class="team-social-btn" title="Instagram"><i class="bi bi-instagram"></i></a>
                  <a href="mailto:writer@balitanggaseño.news.ph" class="team-social-btn" title="Email"><i class="bi bi-envelope"></i></a>
                </div>
              </div>
            </div>
          </div>

          <!-- ── 4. Multimedia Specialist ── -->
          <div class="col-lg-6 col-xl-3">
            <div class="team-card">
              <div class="team-card-img-wrap" style="background:linear-gradient(135deg,#3d1a00 0%,#1a0a00 100%);">
                <!-- SVG Avatar: Multimedia Specialist (man, camera) -->
                <svg class="team-avatar-svg" width="160" height="200" viewBox="0 0 160 200" xmlns="http://www.w3.org/2000/svg">
                  <img src="profile-pictures/jber-pic.jpg" alt="img2">
                </svg>
                <div class="team-role-ribbon">Multimedia Specialist</div>
              </div>
              <div class="team-card-body">
                <div class="team-name">J-ber Magparangalan</div>
                <div class="team-role-text"><i class="bi bi-camera-video me-1"></i>Multimedia Specialist</div>
                <p class="team-bio">
                  J-ber Magparangalan is the creative eye of Gasan News, responsible for photography, video production, graphic design, and the overall visual identity of the platform. He studied Multimedia Arts at a polytechnic university in Manila before returning to his hometown. J-ber documents Gasan's moments — from fiesta celebrations to municipal sessions — ensuring every story is paired with compelling visuals that draw readers in.
                </p>
                <div class="team-skills">
                  <span class="team-skill-tag">Photography</span>
                  <span class="team-skill-tag">Video Editing</span>
                  <span class="team-skill-tag">Graphic Design</span>
                </div>
                <div class="team-socials">
                  <a href="#" class="team-social-btn" title="Facebook"><i class="bi bi-facebook"></i></a>
                  <a href="#" class="team-social-btn" title="Instagram"><i class="bi bi-instagram"></i></a>
                  <a href="mailto:multimedia@balitanggaseño.news.ph" class="team-social-btn" title="Email"><i class="bi bi-envelope"></i></a>
                </div>
              </div>
            </div>
          </div>

        </div><!-- /row -->
      </div><!-- /container -->
    </section>

    <!-- ===== OUR STORY TIMELINE ===== -->
    <section class="history-section">
      <div class="container">
        <div class="row g-5 align-items-start">
          <div class="col-lg-5">
            <div class="section-title"><i class="bi bi-clock-history"></i> Our Story</div>
            <h2 style="font-family:var(--font-head);font-size:1.9rem;font-weight:800;color:var(--dark);margin-bottom:16px;">
              Born from a Community Need
            </h2>
            <p style="font-family:var(--font-ui);font-size:15px;color:var(--text-muted);line-height:1.75;margin-bottom:16px;">
              Balitang Gaseño was founded by a group of local journalists and communication professionals who recognized that their hometown lacked a dedicated, credible source of local news. Important community information was scattered across unverified social media posts, regional outlets, and word-of-mouth.
            </p>
            <p style="font-family:var(--font-ui);font-size:15px;color:var(--text-muted);line-height:1.75;">
              Today, Balitang Gaseño serves as the municipality's digital newsroom — a platform where every barangay event, government ordinance, sports achievement, and human story is given the coverage it deserves.
            </p>
            <div class="mt-4 p-4 rounded" style="background:rgba(192,57,43,.06);border-left:4px solid var(--red);">
              <p style="font-family:var(--font-head);font-size:1.1rem;font-style:italic;color:var(--dark);margin:0;">
                "We are not just reporting news — we are building a record of Gasan's living history for generations to come."
              </p>
              <div style="font-family:var(--font-ui);font-size:13px;color:var(--text-muted);margin-top:10px;">
                — Ken Zander Paragas, Editor-in-Chief
              </div>
            </div>
          </div>
          <div class="col-lg-7">
            <div class="ps-lg-4">
              <div class="section-title"><i class="bi bi-bookmark"></i> Milestones</div>
              <div class="timeline">
                <div class="timeline-item">
                  <div class="timeline-dot"></div>
                  <div class="timeline-year">Early 2024</div>
                  <div class="timeline-text"><strong>Concept &amp; Planning</strong> — Four local media practitioners came together to discuss the need for a dedicated Balitang Gaseño platform. Research began on community information needs.</div>
                </div>
                <div class="timeline-item">
                  <div class="timeline-dot"></div>
                  <div class="timeline-year">Mid 2024</div>
                  <div class="timeline-text"><strong>Development Phase</strong> — The Balitang Gaseño website was designed and built, with database architecture, content management system, and editorial workflow established.</div>
                </div>
                <div class="timeline-item">
                  <div class="timeline-dot"></div>
                  <div class="timeline-year">Late 2024</div>
                  <div class="timeline-text"><strong>Official Launch</strong> — Balitang Gaseño went live, publishing its first batch of articles covering local governance, community events, and barangay happenings.</div>
                </div>
                <div class="timeline-item">
                  <div class="timeline-dot"></div>
                  <div class="timeline-year">2025</div>
                  <div class="timeline-text"><strong>Community Growth</strong> — Readership grew steadily as Gasanons discovered the platform. Commenting, newsletter subscriptions, and category-specific coverage were expanded.</div>
                </div>
                <div class="timeline-item">
                  <div class="timeline-dot"></div>
                  <div class="timeline-year">Today</div>
                  <div class="timeline-text"><strong>Continuing the Mission</strong> — Balitang Gaseño continues to serve as the municipality's trusted digital newsroom, telling the stories of the community every single day.</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ===== CTA / CONTACT STRIP ===== -->
    <section style="background:var(--dark);padding:52px 0;">
      <div class="container text-center">
        <h3 style="font-family:var(--font-head);font-size:1.8rem;font-weight:800;color:#fff;margin-bottom:10px;">
          Have a Story Tip?
        </h3>
        <p style="font-family:var(--font-ui);font-size:15px;color:rgba(255,255,255,.65);margin-bottom:28px;max-width:480px;margin-left:auto;margin-right:auto;">
          We welcome community members to share news tips, story ideas, and local announcements. Every lead helps us serve Gasan better.
        </p>
        <div class="d-flex gap-3 justify-content-center flex-wrap">
          <a href="<?= $base ?>/contact.php" class="btn btn-danger btn-lg fw-bold px-5">
            <i class="bi bi-envelope me-2"></i>Contact Us
          </a>
          <a href="<?= $base ?>/index.php" class="btn btn-outline-light btn-lg px-5">
            <i class="bi bi-newspaper me-2"></i>Read the News
          </a>
        </div>
      </div>
    </section>
    <?php include __DIR__ . '/includes/footer.php'; ?>
  </body>
</html>