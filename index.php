<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>EduSphere - Next-Gen E-Learning</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>

  <!-- Ambient Glow Effects -->
  <div class="ambient-glow glow-1"></div>
  <div class="ambient-glow glow-2"></div>

  <!-- Persistent Responsive Navbar -->
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
          <li><a href="index.php" class="active">Home</a></li>
          <li><a href="about.php">About Us</a></li>
          <li><a href="contact.php">Contact Us</a></li>
          <li><a href="login.php" class="btn btn-login">Login</a></li>
          <li><a href="register.php" class="btn btn-register">Register</a></li>
        </ul>
      </nav>
    </div>
  </header>

  <main>
    <section class="hero">
      <div class="hero-content">
        <div class="badge">✨ Join 50,000+ Active Learners</div>
        <h1>Elevate Your Skills With <span class="gradient-text">EduSphere</span></h1>
        <p>Master in-demand software, engineering, and creative skills through bite-sized, mentor-led courses built for real-world impact.</p>
        <div class="hero-actions">
          <a href="register.php" class="btn btn-primary">Start Learning Now</a>
          <a href="about.php" class="btn btn-secondary">Explore Tracks</a>
        </div>
      </div>

      <div class="hero-image">
        <img src="images/hpimage.png" alt="EduSphere Hero Graphic" onerror="this.src='https://illustrations.popertee.com/illustrations/education-isometric.svg'">
      </div>
    </section>
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