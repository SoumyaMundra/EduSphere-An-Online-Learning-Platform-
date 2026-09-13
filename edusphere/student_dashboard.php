<?php
session_start();
require_once 'db.php';

// Auth Guard: Student / Learner only
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['student', 'learner'])) {
    header("Location: login.php");
    exit();
}

$student_id = (int)$_SESSION['user_id'];

// Handle Self-Enrollment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'enroll') {
    $course_id = (int)($_POST['course_id'] ?? 0);

    if ($course_id > 0) {
        try {
            $enrollStmt = $pdo->prepare("INSERT IGNORE INTO enrollments (student_id, course_id) VALUES (:student_id, :course_id)");
            $enrollStmt->execute([
                ':student_id' => $student_id,
                ':course_id'  => $course_id
            ]);
            $_SESSION['flash_success'] = "Successfully enrolled in the course! Head over to 'My Courses' to begin.";
        } catch (PDOException $e) {
            $_SESSION['flash_error'] = "Could not complete enrollment. Please try again.";
        }
    }
    header("Location: student_dashboard.php?tab=my_courses");
    exit();
}

// Fetch list of Course IDs this student is already enrolled in
$enrolledCourseIdsStmt = $pdo->prepare("SELECT course_id FROM enrollments WHERE student_id = :student_id");
$enrolledCourseIdsStmt->execute([':student_id' => $student_id]);
$enrolledIds = $enrolledCourseIdsStmt->fetchAll(PDO::FETCH_COLUMN);

// Fetch All Available Courses (For "Explore Courses")
$allCoursesStmt = $pdo->query("
    SELECT c.*, u.fullname AS teacher_name, COUNT(l.id) AS lecture_count
    FROM courses c
    LEFT JOIN users u ON c.teacher_id = u.id
    LEFT JOIN lectures l ON c.id = l.course_id
    GROUP BY c.id
    ORDER BY c.created_at DESC
");
$allCourses = $allCoursesStmt->fetchAll();

// Fetch Enrolled Courses Only (For "My Courses")
$myCourses = [];
if (!empty($enrolledIds)) {
    $placeholders = implode(',', array_fill(0, count($enrolledIds), '?'));
    $myCoursesStmt = $pdo->prepare("
        SELECT c.*, u.fullname AS teacher_name, COUNT(l.id) AS lecture_count
        FROM courses c
        LEFT JOIN users u ON c.teacher_id = u.id
        LEFT JOIN lectures l ON c.id = l.course_id
        WHERE c.id IN ($placeholders)
        GROUP BY c.id
        ORDER BY c.title ASC
    ");
    $myCoursesStmt->execute($enrolledIds);
    $myCourses = $myCoursesStmt->fetchAll();
}

$firstInitial = strtoupper(substr($_SESSION['user_name'], 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Student Portal - EduSphere</title>
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
            <a href="javascript:void(0)" onclick="switchStudentTab('myCoursesTab')" class="sidebar-link active" id="navMyCourses">
              <svg viewBox="0 0 24 24">
                <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
              </svg>
              <span>My Courses</span>
            </a>
          </li>
          <li>
            <a href="javascript:void(0)" onclick="switchStudentTab('exploreCoursesTab')" class="sidebar-link" id="navExplore">
              <svg viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10"></circle>
                <polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"></polygon>
              </svg>
              <span>Explore Courses</span>
            </a>
          </li>
        </ul>
      </div>

      <div>
        <div class="sidebar-user-card">
          <div class="user-avatar"><?= $firstInitial ?></div>
          <div>
            <div class="user-name"><?= htmlspecialchars($_SESSION['user_name']) ?></div>
            <div class="user-role">Student</div>
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
        <!-- Mobile Sidebar Toggle -->
        <button class="sidebar-toggle-btn" id="sidebarToggle" aria-label="Toggle Navigation">
          <svg viewBox="0 0 24 24"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
        </button>

        <div class="topbar-welcome">
          Welcome back, <span><?= htmlspecialchars($_SESSION['user_name']) ?></span>
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

        <!-- TAB 1: MY COURSES -->
        <div id="myCoursesTab" class="student-tab-pane active">
          <div class="dashboard-header-area">
            <h1>My Enrolled Courses</h1>
            <p>Select any enrolled track to view its curriculum and lesson content.</p>
          </div>

          <?php if (empty($myCourses)): ?>
            <div class="table-container" style="padding: 3rem 2rem; text-align: center; color: var(--text-muted);">
              <p style="margin-bottom: 1rem;">You have not enrolled in any courses yet.</p>
              <button onclick="switchStudentTab('exploreCoursesTab')" class="btn btn-primary">Browse & Enroll Now</button>
            </div>
          <?php else: ?>
            <div class="course-grid">
              <?php foreach ($myCourses as $c): ?>
                <div class="course-card-box">
                  <div>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.6rem;">
                      <span class="badge badge-primary"><?= (int)$c['lecture_count'] ?> Lectures</span>
                      <span class="badge badge-success">Enrolled</span>
                    </div>

                    <h3><?= htmlspecialchars($c['title']) ?></h3>

                    <p style="font-size: 0.82rem; color: var(--text-dim); margin-bottom: 0.5rem;">
                      Instructor: <strong style="color: var(--text-muted);"><?= htmlspecialchars($c['teacher_name'] ?: 'Unassigned') ?></strong>
                    </p>

                    <p><?= htmlspecialchars($c['description'] ?: 'No description provided.') ?></p>
                  </div>

                  <div>
                    <a href="course_view.php?id=<?= $c['id'] ?>" class="btn btn-secondary" style="width: 100%;">
                      View Course Content →
                    </a>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>

        <!-- TAB 2: EXPLORE COURSES -->
        <div id="exploreCoursesTab" class="student-tab-pane">
          <div class="dashboard-header-area">
            <h1>Explore Course Catalog</h1>
            <p>Discover industry-aligned courses and enroll in one click.</p>
          </div>

          <?php if (empty($allCourses)): ?>
            <div class="table-container" style="padding: 2.5rem 2rem; text-align: center; color: var(--text-muted);">
              <p>No courses are available right now. Please check back later.</p>
            </div>
          <?php else: ?>
            <div class="course-grid">
              <?php foreach ($allCourses as $c): ?>
                <?php $isEnrolled = in_array($c['id'], $enrolledIds); ?>
                <div class="course-card-box">
                  <div>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.6rem;">
                      <span class="badge badge-primary"><?= (int)$c['lecture_count'] ?> Lectures</span>
                      <?php if ($isEnrolled): ?>
                        <span class="badge badge-success">Enrolled</span>
                      <?php endif; ?>
                    </div>

                    <h3><?= htmlspecialchars($c['title']) ?></h3>

                    <p style="font-size: 0.82rem; color: var(--text-dim); margin-bottom: 0.5rem;">
                      Instructor: <strong style="color: var(--text-muted);"><?= htmlspecialchars($c['teacher_name'] ?: 'Unassigned') ?></strong>
                    </p>

                    <p><?= htmlspecialchars($c['description'] ?: 'No course description provided.') ?></p>
                  </div>

                  <div>
                    <?php if ($isEnrolled): ?>
                      <button onclick="switchStudentTab('myCoursesTab')" class="btn btn-secondary" style="width: 100%;">
                        Go to My Courses
                      </button>
                    <?php else: ?>
                      <form action="student_dashboard.php" method="POST">
                        <input type="hidden" name="action" value="enroll">
                        <input type="hidden" name="course_id" value="<?= $c['id'] ?>">
                        <button type="submit" class="btn btn-primary" style="width: 100%;">
                          + Enroll in Course
                        </button>
                      </form>
                    <?php endif; ?>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>

      </main>
    </div>
  </div>

  <script>
    // Responsive Mobile Sidebar Toggle & Backdrop
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

    function switchStudentTab(tabId) {
      document.querySelectorAll('.student-tab-pane').forEach(el => el.classList.remove('active'));
      document.querySelectorAll('.sidebar-nav .sidebar-link').forEach(el => el.classList.remove('active'));

      const target = document.getElementById(tabId);
      if (target) target.classList.add('active');

      if (tabId === 'myCoursesTab') {
        document.getElementById('navMyCourses').classList.add('active');
      } else if (tabId === 'exploreCoursesTab') {
        document.getElementById('navExplore').classList.add('active');
      }

      // Close mobile drawer upon switching
      if (window.innerWidth <= 868) {
        sidebar.classList.remove('open');
        backdrop.classList.remove('active');
      }
    }

    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('tab') === 'my_courses') {
      switchStudentTab('myCoursesTab');
    }
  </script>

</body>
</html>