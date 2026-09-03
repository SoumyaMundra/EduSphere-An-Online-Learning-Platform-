<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Contact Us - EduSphere</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>

  <div class="ambient-glow glow-1"></div>
  <div class="ambient-glow glow-2"></div>

  <header class="navbar-container">
    <div class="navbar">
      <a href="index.php" class="logo">Edu<span>Sphere</span></a>
      <button class="menu-toggle" id="menuToggle" aria-label="Toggle navigation">
        <span></span>
        <span></span>
        <span></span>
      </button>
      <nav>
        <ul class="nav-links" id="navLinks">
          <li><a href="index.php">Home</a></li>
          <li><a href="about.php">About Us</a></li>
          <li><a href="contact.php" class="active">Contact Us</a></li>
          <li><a href="login.php" class="btn btn-login">Login</a></li>
          <li><a href="register.php" class="btn btn-register">Register</a></li>
        </ul>
      </nav>
    </div>
  </header>

  <main style="max-width:960px; margin: 5rem auto; padding: 0 2rem; position:relative; z-index:1;">
    <h1 style="font-size:2.8rem; font-weight:800; margin-bottom:1.2rem;">Get in <span style="background:var(--gradient); -webkit-background-clip:text; -webkit-text-fill-color:transparent;">Touch</span></h1>
    <p style="color:var(--text-muted); font-size:1.15rem; line-height:1.8;">
      Have inquiries about our learning tracks, platform features, or instructor onboarding? Reach out to our team anytime.
    </p>
  </main>

  <script>
    const menuToggle = document.getElementById('menuToggle');
    const navLinks = document.getElementById('navLinks');
    menuToggle.addEventListener('click', () => {
      navLinks.classList.toggle('active');
    });
  </script>

</body>
</html>