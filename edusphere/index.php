<?php
session_start();

// Handle Contact Form Submission directly on the landing page
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'contact_submit') {
    $name    = trim($_POST['name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? 'general');
    $message = trim($_POST['message'] ?? '');

    if (!empty($name) && !empty($email) && !empty($message)) {
        $_SESSION['contact_success'] = "Thank you, " . htmlspecialchars($name) . "! Your message has been received. Our team will get back to you within 24 hours.";
    } else {
        $_SESSION['contact_error'] = "Please fill in all required fields.";
    }
    header("Location: index.php#contact");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>EduSphere - Next-Gen E-Learning Platform</title>
  <link rel="stylesheet" href="home.css">
</head>
<body>

  <!-- Subtle Ambient Glow Highlights -->
  <div class="subtle-glow glow-top-center"></div>
  <div class="subtle-glow glow-accent"></div>

  <!-- Sticky Navbar with Anchor Links -->
  <header class="navbar-container">
    <div class="navbar">
      <a href="#hero" class="logo">Edu<span>Sphere</span></a>
      
      <button class="menu-toggle" id="menuToggle" aria-label="Toggle navigation">
        <span></span><span></span><span></span>
      </button>

      <nav>
        <ul class="nav-links" id="navLinks">
          <li><a href="#hero" class="nav-link active">Home</a></li>
          <li><a href="#about" class="nav-link">About Us</a></li>
          <li><a href="#contact" class="nav-link">Contact Us</a></li>
          <li><a href="login.php" class="btn btn-login">Login</a></li>
          <li><a href="register.php" class="btn btn-register">Register</a></li>
        </ul>
      </nav>
    </div>
  </header>

  <main>
    <!-- ==============================================
         1. HERO SECTION (ENHANCED)
         ============================================== -->
    <section class="hero" id="hero">
      <div class="hero-content">
        <div class="badge-pill">
          <span class="badge-dot"></span>
          <span>Join 50,000+ Active Learners</span>
        </div>

        <h1>Elevate Your Skills With <span>EduSphere</span></h1>
        
        <p>Master in-demand software, engineering, and creative skills through bite-sized, mentor-led courses built for real-world impact.</p>
        
        <div class="hero-actions">
          <a href="register.php" class="btn btn-primary">Start Learning Now →</a>
          <a href="#about" class="btn btn-secondary">Explore Platform</a>
        </div>

        <!-- Trust Stats Strip -->
        <div class="hero-stats">
          <div class="stat-mini">
            <h4>120+</h4>
            <span>Curated Tracks</span>
          </div>
          <div class="stat-divider"></div>
          <div class="stat-mini">
            <h4>98%</h4>
            <span>Completion Rate</span>
          </div>
          <div class="stat-divider"></div>
          <div class="stat-mini">
            <h4>4.9 ★</h4>
            <span>Student Rating</span>
          </div>
        </div>
      </div>

      <!-- Hero Visual with Ambient Plate & Floating Metric Badges -->
      <div class="hero-visual-wrapper">
        <div class="visual-glow-plate"></div>

        <img src="images/hpimage.png" alt="EduSphere Hero Graphic" onerror="this.src='https://illustrations.popertee.com/illustrations/education-isometric.svg'">

        <!-- Floating Chip 1: Top Left -->
        <div class="floating-chip chip-top">
          <span class="chip-icon">⚡</span>
          <div>
            <strong>Interactive Lessons</strong>
            <small>Video & Lesson Notes</small>
          </div>
        </div>

        <!-- Floating Chip 2: Bottom Right -->
        <div class="floating-chip chip-bottom">
          <span class="chip-icon">👨‍🏫</span>
          <div>
            <strong>Expert Mentors</strong>
            <small>Active Industry Tutors</small>
          </div>
        </div>
      </div>
    </section>

    <!-- ==============================================
         2. ABOUT US SECTION
         ============================================== -->
    <section class="landing-section" id="about">
      <div class="section-header">
        <span class="badge-pill">🚀 What Powers EduSphere</span>
        <h2>Empowering Learners Through <span>Modern Education</span></h2>
        <p>EduSphere connects aspiring developers and creators with specialized mentors in a high-performance, distraction-free environment.</p>
      </div>

      <!-- Feature / Values Grid -->
      <div class="values-grid">
        <div class="value-card">
          <span class="value-icon">🎯</span>
          <h3>Curated Learning Tracks</h3>
          <p>Structured lesson milestones and real-world code guides designed to build portfolio-ready skills.</p>
        </div>

        <div class="value-card">
          <span class="value-icon">⚡</span>
          <h3>Adaptive Media Delivery</h3>
          <p>Instructors can upload video lessons, lesson guides, or both to cater to diverse learning styles.</p>
        </div>

        <div class="value-card">
          <span class="value-icon">🛡️</span>
          <h3>Supervised Governance</h3>
          <p>Admins coordinate course assignments, monitor enrollments, and maintain high standards across all tracks.</p>
        </div>
      </div>

      <!-- Multi-Role Architecture Overview -->
      <div class="roles-section">
        <div>
          <h3 style="font-size: 1.4rem; font-weight: 800; color: var(--text-main); margin-bottom: 0.3rem;">How EduSphere Operates</h3>
          <p style="color: var(--text-muted); font-size: 0.92rem;">A three-tier role system providing tailored workspaces for everyone involved:</p>
        </div>

        <div class="roles-grid">
          <div class="role-box">
            <h4 style="color: #58a6ff;">🎓 For Students</h4>
            <p>Browse catalog tracks, enroll in one click, watch lecture videos, and access study notes anywhere.</p>
          </div>

          <div class="role-box">
            <h4 style="color: #ec4899;">👨‍🏫 For Teachers</h4>
            <p>Manage lectures for assigned courses, publish video links, and compose lesson notes seamlessly.</p>
          </div>

          <div class="role-box">
            <h4 style="color: #a855f7;">⚙️ For Administrators</h4>
            <p>Create course shells, assign instructors, view enrolled learners, and manage system directories.</p>
          </div>
        </div>
      </div>
    </section>

    <!-- ==============================================
         3. CONTACT US SECTION
         ============================================== -->
    <section class="landing-section" id="contact">
      <div class="section-header">
        <span class="badge-pill">💬 Support & Inquiries</span>
        <h2>Get in <span>Touch</span></h2>
        <p>Have questions regarding tracks, accounts, or platform features? Our team is here to help.</p>
      </div>

      <div class="contact-grid">
        <!-- Direct Help Desks -->
        <div class="contact-info-card">
          <h3 style="font-size: 1.25rem; font-weight: 700; color: var(--text-main); margin-bottom: 0.4rem;">Contact Information</h3>
          <p style="color: var(--text-muted); font-size: 0.9rem; line-height: 1.6;">Reach out directly or send us a message using the form.</p>

          <div class="channel-item">
            <div class="channel-icon">✉️</div>
            <div>
              <h4>Student Support</h4>
              <p>support@edusphere.com</p>
              <span>Response within 24 hours</span>
            </div>
          </div>

          <div class="channel-item">
            <div class="channel-icon">👨‍🏫</div>
            <div>
              <h4>Instructor Onboarding</h4>
              <p>teach@edusphere.com</p>
              <span>Course publishing and assignment queries</span>
            </div>
          </div>

          <div class="channel-item">
            <div class="channel-icon">📍</div>
            <div>
              <h4>Operations Lab</h4>
              <p>EduSphere Technology Lab, Suite 400</p>
              <span>Monday - Friday, 9:00 AM - 6:00 PM</span>
            </div>
          </div>
        </div>

        <!-- Contact Submission Form -->
        <div class="auth-card" style="max-width: 100%;">
          <div class="auth-header" style="margin-bottom: 1.2rem;">
            <h3 style="font-size: 1.3rem; font-weight: 700; color: var(--text-main);">Send a Message</h3>
            <p>We usually respond to inquiries within one business day.</p>
          </div>

          <?php if (isset($_SESSION['contact_success'])): ?>
            <div class="alert alert-success">
              <?= htmlspecialchars($_SESSION['contact_success']); unset($_SESSION['contact_success']); ?>
            </div>
          <?php endif; ?>

          <?php if (isset($_SESSION['contact_error'])): ?>
            <div class="alert alert-error">
              <?= htmlspecialchars($_SESSION['contact_error']); unset($_SESSION['contact_error']); ?>
            </div>
          <?php endif; ?>

          <form action="index.php" method="POST">
            <input type="hidden" name="action" value="contact_submit">

            <div class="input-group">
              <label for="name">Your Name *</label>
              <div class="input-container">
                <input type="text" id="name" name="name" placeholder="Jordan Lee" required>
              </div>
            </div>

            <div class="input-group">
              <label for="email">Email Address *</label>
              <div class="input-container">
                <input type="email" id="email" name="email" placeholder="jordan@example.com" required>
              </div>
            </div>

            <div class="input-group">
              <label for="subject">Topic</label>
              <div class="input-container">
                <select id="subject" name="subject">
                  <option value="General Question">General Platform Question</option>
                  <option value="Course Issue">Course Content / Video Playback</option>
                  <option value="Teacher Onboarding">Instructor Application</option>
                  <option value="Account Help">Account & Authentication Help</option>
                </select>
              </div>
            </div>

            <div class="input-group">
              <label for="message">Message *</label>
              <div class="input-container">
                <textarea id="message" name="message" rows="4" placeholder="How can we assist you?" required></textarea>
              </div>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 0.5rem;">Submit Inquiry</button>
          </form>
        </div>
      </div>

      <!-- FAQ Accordion -->
      <div class="faq-section">
        <div style="text-align: center; max-width: 600px; margin: 0 auto 2.5rem;">
          <h3 style="font-size: 1.6rem; font-weight: 800; color: var(--text-main);">Frequently Asked Questions</h3>
          <p style="color: var(--text-muted); font-size: 0.92rem; margin-top: 0.25rem;">Common answers to help you get started right away.</p>
        </div>

        <div style="max-width: 820px; margin: 0 auto;">
          <div class="faq-item">
            <div class="faq-question" onclick="toggleFaq(this)">
              How do I enroll in a course?
              <span>+</span>
            </div>
            <div class="faq-answer">
              Register as a <strong>Student</strong>, navigate to the <strong>Explore Courses</strong> tab in your workspace, and click "+ Enroll in Course". It will instantly appear under your "My Courses" tab.
            </div>
          </div>

          <div class="faq-item">
            <div class="faq-question" onclick="toggleFaq(this)">
              Can I access courses and study notes on mobile devices?
              <span>+</span>
            </div>
            <div class="faq-answer">
              Yes! EduSphere is built with responsive viewports, clean embedded video players, and formatted lesson notes so you can learn on any mobile phone, tablet, or laptop.
            </div>
          </div>

          <div class="faq-item">
            <div class="faq-question" onclick="toggleFaq(this)">
              How do I become an instructor on EduSphere?
              <span>+</span>
            </div>
            <div class="faq-answer">
              Choose the <strong>Teacher</strong> profile on the registration page. Once registered, an Administrator can assign courses to your account so you can start publishing video lectures and lesson content.
            </div>
          </div>
        </div>
      </div>
    </section>
  </main>

  <!-- ==============================================
       FOOTER
       ============================================== -->
  <footer class="site-footer">
    <div class="footer-inner">
      <!-- Col 1: Brand & Tagline -->
      <div class="footer-col">
        <a href="#hero" class="logo">Edu<span>Sphere</span></a>
        <p class="footer-desc">
          Next-generation e-learning platform delivering bite-sized, mentor-led engineering tracks and digital skills for real-world impact.
        </p>
        <div class="footer-badges">
          <span class="badge badge-primary">🎓 50k+ Learners</span>
          <span class="badge badge-success">✓ Verified Mentors</span>
        </div>
      </div>

      <!-- Col 2: Platform Navigation -->
      <div class="footer-col">
        <h4>Platform</h4>
        <ul class="footer-links">
          <li><a href="#hero">Home</a></li>
          <li><a href="#about">About Platform</a></li>
          <li><a href="#contact">Contact Support</a></li>
          <li><a href="#contact">Frequently Asked Questions</a></li>
        </ul>
      </div>

      <!-- Col 3: Portals & Access -->
      <div class="footer-col">
        <h4>User Portals</h4>
        <ul class="footer-links">
          <li><a href="login.php">Student Workspace</a></li>
          <li><a href="login.php">Instructor Dashboard</a></li>
          <li><a href="login.php">Administrator Center</a></li>
          <li><a href="register.php">Create Free Account</a></li>
        </ul>
      </div>

      <!-- Col 4: Quick Contact -->
      <div class="footer-col">
        <h4>Get in Touch</h4>
        <ul class="footer-contact-list">
          <li>
            <span class="contact-label">Support:</span>
            <a href="mailto:support@edusphere.com">support@edusphere.com</a>
          </li>
          <li>
            <span class="contact-label">Instructors:</span>
            <a href="mailto:teach@edusphere.com">teach@edusphere.com</a>
          </li>
          <li>
            <span class="contact-label">Location:</span>
            <span style="color: var(--text-muted);">Technology Lab, Suite 400</span>
          </li>
        </ul>
      </div>
    </div>

    <!-- Bottom Copyright & Back to Top -->
    <div class="footer-bottom">
      <div class="footer-bottom-inner">
        <p>&copy; <?= date('Y') ?> EduSphere. All rights reserved.</p>
        <div>
          <a href="#hero" class="back-to-top">Back to top ↑</a>
        </div>
      </div>
    </div>
  </footer>

  <script>
    // Mobile hamburger menu toggle
    const menuToggle = document.getElementById('menuToggle');
    const navLinks = document.getElementById('navLinks');
    menuToggle.addEventListener('click', () => {
      navLinks.classList.toggle('active');
    });

    // Close mobile nav when an anchor link is clicked
    document.querySelectorAll('.nav-link').forEach(link => {
      link.addEventListener('click', () => {
        navLinks.classList.remove('active');
      });
    });

    // FAQ Accordion Toggle
    function toggleFaq(header) {
      const item = header.parentElement;
      item.classList.toggle('open');
    }

    // Dynamic active state highlight on scroll
    const sections = document.querySelectorAll('section[id]');
    const navItems = document.querySelectorAll('.nav-links a.nav-link');

    window.addEventListener('scroll', () => {
      let currentSection = '';
      sections.forEach(section => {
        const sectionTop = section.offsetTop - 120;
        if (pageYOffset >= sectionTop) {
          currentSection = section.getAttribute('id');
        }
      });

      navItems.forEach(link => {
        link.classList.remove('active');
        if (link.getAttribute('href') === '#' + currentSection) {
          link.classList.add('active');
        }
      });
    });
  </script>

</body>
</html>