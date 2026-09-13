<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - EduSphere</title>
  <link rel="stylesheet" href="home.css">
</head>
<body>

  <!-- Subtle Ambient Glow Highlights -->
  <div class="subtle-glow glow-top-center"></div>
  <div class="subtle-glow glow-accent"></div>

  <!-- Sticky Top Navbar -->
  <header class="navbar-container">
    <div class="navbar">
      <a href="index.php" class="logo">Edu<span>Sphere</span></a>
      <button class="menu-toggle" id="menuToggle" aria-label="Toggle navigation">
        <span></span><span></span><span></span>
      </button>
      <nav>
        <ul class="nav-links" id="navLinks">
          <li><a href="index.php#hero">Home</a></li>
          <li><a href="index.php#about">About Us</a></li>
          <li><a href="index.php#contact">Contact Us</a></li>
          <li><a href="login.php" class="btn btn-login active">Login</a></li>
          <li><a href="register.php" class="btn btn-register">Register</a></li>
        </ul>
      </nav>
    </div>
  </header>

  <!-- Centered Login Workspace -->
  <main class="auth-wrapper">
    <div class="auth-card">
      <div class="auth-header">
        <h2>Welcome Back</h2>
        <p>Enter your credentials to continue learning</p>
      </div>

      <!-- Flash Notification Banners -->
      <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
          <?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
        </div>
      <?php endif; ?>

      <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-error">
          <?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
        </div>
      <?php endif; ?>

      <form action="process_login.php" method="POST">
        <div class="input-group">
          <label for="email">Email Address</label>
          <div class="input-container">
            <input type="email" id="email" name="email" placeholder="name@example.com" required autocomplete="email">
          </div>
        </div>

        <div class="input-group">
          <label for="password">Password</label>
          <div class="input-container">
            <input type="password" id="password" name="password" placeholder="••••••••" required autocomplete="current-password">
            <button type="button" class="password-toggle" onclick="togglePassword('password', this)" aria-label="Toggle Password Visibility">
              <svg viewBox="0 0 24 24">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                <circle cx="12" cy="12" r="3"></circle>
              </svg>
            </button>
          </div>
        </div>

        <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 0.5rem;">Sign In</button>
      </form>

      <div class="auth-footer">
        New to EduSphere? <a href="register.php">Create an account</a>
      </div>
    </div>
  </main>

  <script>
    const menuToggle = document.getElementById('menuToggle');
    const navLinks = document.getElementById('navLinks');
    menuToggle.addEventListener('click', () => { navLinks.classList.toggle('active'); });

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