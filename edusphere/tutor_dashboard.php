<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'tutor') {
    header("Location: login.php");
    exit();
}

$firstInitial = strtoupper(substr($_SESSION['user_name'], 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Instructor Portal - EduSphere</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>

  <!-- Ambient Dual Glow Effects -->
  <div class="ambient-glow glow-1"></div>
  <div class="ambient-glow glow-2"></div>

  <div class="dashboard-layout">
    <!-- Sidebar Navigation -->
    <aside class="sidebar" id="sidebar">
      <div>
        <div class="sidebar-header">
          <a href="index.php" class="logo">Edu<span>Sphere</span></a>
        </div>

        <ul class="sidebar-nav">
          <li>
            <a href="tutor_dashboard.php" class="sidebar-link active">
              <svg viewBox="0 0 24 24">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                <circle cx="12" cy="7" r="4"></circle>
              </svg>
              <span>My Account</span>
            </a>
          </li>
        </ul>
      </div>

      <!-- Bottom Profile Card + Divider Line + Logout -->
      <div>
        <!-- Profile info above the line -->
        <div class="sidebar-user-card">
          <div class="user-avatar" style="background:linear-gradient(135deg, #ec4899, #8b5cf6);"><?= $firstInitial ?></div>
          <div class="user-details">
            <span class="user-name"><?= htmlspecialchars($_SESSION['user_name']) ?></span>
            <span class="user-role">Tutor</span>
          </div>
        </div>

        <!-- Horizontal line & Logout button -->
        <div class="sidebar-footer">
          <a href="logout.php" class="btn-logout">
            <svg style="width:18px;height:18px;stroke:currentColor;stroke-width:2;fill:none;stroke-linecap:round;stroke-linejoin:round;" viewBox="0 0 24 24">
              <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
              <polyline points="16 17 21 12 16 7"></polyline>
              <line x1="21" y1="12" x2="9" y2="12"></line>
            </svg>
            <span>Logout</span>
          </a>
        </div>
      </div>
    </aside>

    <!-- Main Content Area -->
    <div class="dashboard-content">
      <header class="dashboard-topbar">
        <button class="sidebar-toggle-btn" id="sidebarToggle" aria-label="Toggle Sidebar">
          <svg style="width:24px;height:24px;stroke:currentColor;stroke-width:2;fill:none;" viewBox="0 0 24 24">
            <line x1="3" y1="12" x2="21" y2="12"></line>
            <line x1="3" y1="6" x2="21" y2="6"></line>
            <line x1="3" y1="18" x2="21" y2="18"></line>
          </svg>
        </button>

        

        <!-- Plain text Welcome on Top Right -->
        <div class="topbar-welcome">
          Welcome, <span><?= htmlspecialchars($_SESSION['user_name']) ?></span>
        </div>
      </header>

      <main class="dashboard-body">
        <h1 style="font-size:2.2rem; font-weight:800; margin-bottom:0.6rem;">My Account</h1>
        <p style="color:var(--text-muted); font-size:1.05rem; line-height:1.7; max-width:600px;">
          Manage your instructor profile, view enrolled student metrics, upload lessons, and review payouts.
        </p>

        <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap:1.5rem; margin-top:2.5rem;">
          <div style="background:var(--bg-surface); border:1px solid var(--border-subtle); padding:1.8rem; border-radius:var(--radius-md); backdrop-filter:blur(20px);">
            <h3 style="font-size:1.15rem; margin-bottom:0.5rem;">Created Courses</h3>
            <p style="font-size:2.2rem; font-weight:800; color:#818cf8;">0</p>
            <p style="color:var(--text-dim); font-size:0.85rem; margin-top:0.4rem;">Start drafting your syllabus</p>
          </div>

          <div style="background:var(--bg-surface); border:1px solid var(--border-subtle); padding:1.8rem; border-radius:var(--radius-md); backdrop-filter:blur(20px);">
            <h3 style="font-size:1.15rem; margin-bottom:0.5rem;">Active Students</h3>
            <p style="font-size:2.2rem; font-weight:800; color:#ec4899;">0</p>
            <p style="color:var(--text-dim); font-size:0.85rem; margin-top:0.4rem;">Total enrollments across your curriculum</p>
          </div>
        </div>
      </main>
    </div>
  </div>

  <script>
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');
    if (sidebarToggle) {
      sidebarToggle.addEventListener('click', () => {
        sidebar.classList.toggle('open');
      });
    }
  </script>

</body>
</html>