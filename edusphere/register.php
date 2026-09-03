<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register - EduSphere</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>

  <div class="ambient-glow glow-1"></div>
  <div class="ambient-glow glow-2"></div>

  <header class="navbar-container">
    <div class="navbar">
      <a href="index.php" class="logo">Edu<span>Sphere</span></a>
      <button class="menu-toggle" id="menuToggle" aria-label="Toggle navigation">
        <span></span><span></span><span></span>
      </button>
      <nav>
        <ul class="nav-links" id="navLinks">
          <li><a href="index.php">Home</a></li>
          <li><a href="about.php">About Us</a></li>
          <li><a href="contact.php">Contact Us</a></li>
          <li><a href="login.php" class="btn btn-login">Login</a></li>
          <li><a href="register.php" class="btn btn-register active">Register</a></li>
        </ul>
      </nav>
    </div>
  </header>

  <div class="auth-wrapper">
    <div class="auth-card">
      <div class="auth-header">
        <h2>Join EduSphere</h2>
        <p>Pick your profile track to get started</p>
      </div>

      <!-- Flash Notification Banners -->
      <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-error">
          <?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
        </div>
      <?php endif; ?>

      <form action="process_register.php" method="POST">
        <label style="display:block; font-size:0.85rem; font-weight:600; margin-bottom:0.6rem; color:var(--text-muted);">Select Account Type:</label>
        <div class="role-selection">
          <label class="role-card selected" id="learnerCard">
            <input type="radio" name="role" value="learner" checked onchange="updateRoleUI()">
            <span class="role-icon">🎓</span>
            <span class="role-title">Learner</span>
            <span class="role-desc">Access & study courses</span>
          </label>

          <label class="role-card" id="tutorCard">
            <input type="radio" name="role" value="tutor" onchange="updateRoleUI()">
            <span class="role-icon">👨‍🏫</span>
            <span class="role-title">Become a Tutor</span>
            <span class="role-desc">Upload & teach courses</span>
          </label>
        </div>

        <div class="input-group">
          <label for="fullname">Full Name</label>
          <div class="input-container">
            <input type="text" id="fullname" name="fullname" placeholder="Alex Morgan" required autocomplete="name">
          </div>
        </div>

        <div class="input-group">
          <label for="email">Email Address</label>
          <div class="input-container">
            <input type="email" id="email" name="email" placeholder="alex@example.com" required autocomplete="email">
          </div>
        </div>

        <div class="input-group">
          <label for="password">Password</label>
          <div class="input-container">
            <input type="password" id="password" name="password" placeholder="At least 8 characters" required autocomplete="new-password">
            <button type="button" class="password-toggle" onclick="togglePassword('password', this)" aria-label="Toggle Password Visibility">
              <svg viewBox="0 0 24 24">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                <circle cx="12" cy="12" r="3"></circle>
              </svg>
            </button>
          </div>
        </div>

        <button type="submit" class="btn btn-primary">Create Account</button>
      </form>

      <div class="auth-footer">
        Already have an account? <a href="login.php">Sign In</a>
      </div>
    </div>
  </div>

  <script>
    const menuToggle = document.getElementById('menuToggle');
    const navLinks = document.getElementById('navLinks');
    menuToggle.addEventListener('click', () => { navLinks.classList.toggle('active'); });

    function updateRoleUI() {
      const isLearner = document.querySelector('input[name="role"][value="learner"]').checked;
      const learnerCard = document.getElementById('learnerCard');
      const tutorCard = document.getElementById('tutorCard');

      if (isLearner) {
        learnerCard.classList.add('selected');
        tutorCard.classList.remove('selected');
      } else {
        tutorCard.classList.add('selected');
        learnerCard.classList.remove('selected');
      }
    }

    function togglePassword(inputId, btn) {
      const input = document.getElementById(inputId);
      const isPassword = input.type === "password";
      input.type = isPassword ? "text" : "password";
      btn.innerHTML = isPassword 
        ? `<svg viewBox="0 0 24 24"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>`
        : `<svg viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>`;
    }
  </script>

</body>
</html>