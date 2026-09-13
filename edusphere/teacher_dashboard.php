<?php
session_start();
require_once 'db.php';

// Auth Guard: Teacher / Tutor only
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['teacher', 'tutor'])) {
    header("Location: login.php");
    exit();
}

$teacher_id = (int)$_SESSION['user_id'];

// Fetch Courses assigned to this teacher with lecture counts
$assignedCoursesStmt = $pdo->prepare("
    SELECT c.*, COUNT(l.id) AS lecture_count
    FROM courses c
    LEFT JOIN lectures l ON c.id = l.course_id
    WHERE c.teacher_id = :teacher_id
    GROUP BY c.id
    ORDER BY c.title ASC
");
$assignedCoursesStmt->execute([':teacher_id' => $teacher_id]);
$assignedCourses = $assignedCoursesStmt->fetchAll();

// Total lecture count across all assigned courses
$totalLectures = array_sum(array_column($assignedCourses, 'lecture_count'));

$firstInitial = strtoupper(substr($_SESSION['user_name'], 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Teacher Portal - EduSphere</title>
  <link rel="stylesheet" href="dashboard.css">
</head>
<body>

  <div class="dashboard-layout">
    <!-- Obsidian Sidebar -->
    <aside class="sidebar" id="sidebar">
      <div>
        <div class="sidebar-header">
          <a href="index.php" class="logo">Edu<span>Sphere</span></a>
        </div>

        <ul class="sidebar-nav">
          <li>
            <a href="teacher_dashboard.php" class="sidebar-link active">
              <svg viewBox="0 0 24 24">
                <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
              </svg>
              <span>My Assigned Courses</span>
            </a>
          </li>
        </ul>
      </div>

      <div>
        <div class="sidebar-user-card">
          <div class="user-avatar"><?= $firstInitial ?></div>
          <div>
            <div class="user-name"><?= htmlspecialchars($_SESSION['user_name']) ?></div>
            <div class="user-role">Instructor</div>
          </div>
        </div>

        <div class="sidebar-footer">
          <a href="logout.php" class="btn-logout">Logout</a>
        </div>
      </div>
    </aside>

    <!-- Main Workspace -->
    <div class="dashboard-content">
      <header class="dashboard-topbar">
        <!-- Mobile Sidebar Hamburger Button -->
        <button class="sidebar-toggle-btn" id="sidebarToggle" aria-label="Toggle Navigation">
          <svg viewBox="0 0 24 24"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
        </button>

        <div class="topbar-welcome">
          Instructor: <span><?= htmlspecialchars($_SESSION['user_name']) ?></span>
        </div>
      </header>

      <main class="dashboard-body">
        <!-- Flash Alerts -->
        <?php if (isset($_SESSION['flash_success'])): ?>
          <div class="alert alert-success">
            <?= htmlspecialchars($_SESSION['flash_success']); unset($_SESSION['flash_success']); ?>
          </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['flash_error'])): ?>
          <div class="alert alert-error">
            <?= htmlspecialchars($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?>
          </div>
        <?php endif; ?>

        <div class="dashboard-header-area">
          <h1>Assigned Curriculum</h1>
          <p>Select any assigned course to upload lessons, notes, and stream links.</p>
        </div>

        <!-- Metric Stat Cards -->
        <div class="stat-cards-grid">
          <div class="stat-card">
            <h3>Assigned Courses</h3>
            <p><?= count($assignedCourses) ?></p>
            <span class="stat-card-hint">Active syllabus tracks</span>
          </div>

          <div class="stat-card">
            <h3>Uploaded Lectures</h3>
            <p><?= $totalLectures ?></p>
            <span class="stat-card-hint">Total published lessons</span>
          </div>
        </div>

        <!-- Course Cards Area -->
        <div class="crud-header">
          <div>
            <h2>My Course Tracks</h2>
            <p>Courses assigned to your profile by the administrator</p>
          </div>
        </div>

        <?php if (empty($assignedCourses)): ?>
          <div class="table-container" style="padding: 2.5rem 2rem; text-align: center; color: var(--text-muted);">
            <p>You currently have no courses assigned to your account. Please ask the Administrator to assign you a course shell.</p>
          </div>
        <?php else: ?>
          <div class="course-grid">
            <?php foreach ($assignedCourses as $c): ?>
              <div class="course-card-box">
                <div>
                  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.6rem;">
                    <span class="badge badge-primary"><?= (int)$c['lecture_count'] ?> Lectures</span>
                    <span class="badge badge-success">Assigned</span>
                  </div>

                  <h3><?= htmlspecialchars($c['title']) ?></h3>

                  <p><?= htmlspecialchars($c['description'] ?: 'No course description provided.') ?></p>
                </div>

                <div>
                  <a href="teacher_manage_course.php?course_id=<?= $c['id'] ?>" class="btn btn-secondary" style="width: 100%;">
                    Manage Lectures & Content →
                  </a>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

      </main>
    </div>
  </div>

  <script>
    // Responsive Mobile Sidebar Toggle
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');

    let backdrop = document.querySelector('.sidebar-backdrop');
    if (!backdrop) {
      backdrop = document.createElement('div');
      backdrop.className = 'sidebar-backdrop';
      document.body.appendChild(backdrop);
    }

    if (sidebarToggle && sidebar) {
      sidebarToggle.addEventListener('click', () => {
        sidebar.classList.toggle('open');
        backdrop.classList.toggle('active');
      });

      backdrop.addEventListener('click', () => {
        sidebar.classList.remove('open');
        backdrop.classList.remove('active');
      });
    }
  </script>

</body>
</html>