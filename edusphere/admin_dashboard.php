<?php
session_start();
require_once 'db.php';

// Auth Guard: Admin only
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Handle Add Course Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_course') {
        $title       = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $teacher_id  = !empty($_POST['teacher_id']) ? (int)$_POST['teacher_id'] : null;

        if (!empty($title)) {
            $stmt = $pdo->prepare("INSERT INTO courses (title, description, teacher_id) VALUES (:title, :description, :teacher_id)");
            $stmt->execute([
                ':title'       => $title,
                ':description' => $description,
                ':teacher_id'  => $teacher_id
            ]);
            $_SESSION['flash_success'] = "Course '$title' created successfully!";
        } else {
            $_SESSION['flash_error'] = "Course title cannot be empty.";
        }
        header("Location: admin_dashboard.php");
        exit();
    }
}

// Fetch System Statistics
$totalCourses  = $pdo->query("SELECT COUNT(*) FROM courses")->fetchColumn();
$totalTeachers = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'teacher'")->fetchColumn();
$totalStudents = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetchColumn();

// Fetch Teachers List for assignment dropdown
$teachersStmt = $pdo->query("
    SELECT u.id, u.fullname, u.email, u.created_at, COUNT(c.id) AS course_count
    FROM users u
    LEFT JOIN courses c ON u.id = c.teacher_id
    WHERE u.role = 'teacher'
    GROUP BY u.id
    ORDER BY u.fullname ASC
");
$teachers = $teachersStmt->fetchAll();

// Fetch Students List
$studentsStmt = $pdo->query("
    SELECT id, fullname, email, created_at 
    FROM users 
    WHERE role = 'student' 
    ORDER BY created_at DESC
");
$students = $studentsStmt->fetchAll();

// Fetch Courses with assigned Teacher
$coursesStmt = $pdo->query("
    SELECT c.*, u.fullname AS teacher_name
    FROM courses c
    LEFT JOIN users u ON c.teacher_id = u.id
    ORDER BY c.created_at DESC
");
$courses = $coursesStmt->fetchAll();

$firstInitial = strtoupper(substr($_SESSION['user_name'], 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard - EduSphere</title>
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
            <a href="javascript:void(0)" onclick="switchTab('coursesTab')" class="sidebar-link active" id="navCourses">
              <svg viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"></path></svg>
              <span>Manage Courses</span>
            </a>
          </li>
          <li>
            <a href="javascript:void(0)" onclick="switchTab('teachersTab')" class="sidebar-link" id="navTeachers">
              <svg viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
              <span>Teachers Directory</span>
            </a>
          </li>
          <li>
            <a href="javascript:void(0)" onclick="switchTab('studentsTab')" class="sidebar-link" id="navStudents">
              <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
              <span>Students Directory</span>
            </a>
          </li>
        </ul>
      </div>

      <div>
        <div class="sidebar-user-card">
          <div class="user-avatar"><?= $firstInitial ?></div>
          <div>
            <div class="user-name"><?= htmlspecialchars($_SESSION['user_name']) ?></div>
            <div class="user-role">Administrator</div>
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
          Logged in as: <span><?= htmlspecialchars($_SESSION['user_name']) ?></span>
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
          <h1>Admin Overview</h1>
          <p>Monitor platform statistics, manage course curricula, and inspect directories.</p>
        </div>

        <!-- Metric Stat Cards -->
        <div class="stat-cards-grid">
          <div class="stat-card active-card" id="cardCourses" onclick="switchTab('coursesTab')">
            <h3>Total Courses</h3>
            <p><?= $totalCourses ?></p>
            <span class="stat-card-hint">Manage active curriculum</span>
          </div>

          <div class="stat-card" id="cardTeachers" onclick="switchTab('teachersTab')">
            <h3>Registered Teachers</h3>
            <p><?= $totalTeachers ?></p>
            <span class="stat-card-hint">View instructor accounts</span>
          </div>

          <div class="stat-card" id="cardStudents" onclick="switchTab('studentsTab')">
            <h3>Enrolled Students</h3>
            <p><?= $totalStudents ?></p>
            <span class="stat-card-hint">Inspect student directory</span>
          </div>
        </div>

        <!-- TAB 1: ALL PLATFORM COURSES -->
        <div id="coursesTab" class="admin-tab-pane active">
          <div class="crud-header">
            <div>
              <h2>Course Catalog</h2>
              <p>All active tracks and their assigned instructors</p>
            </div>
            <button class="btn btn-primary" onclick="openAddModal()">+ Add New Course</button>
          </div>

          <?php if (empty($courses)): ?>
            <div class="table-container" style="padding: 3rem 2rem; text-align: center; color: var(--text-muted);">
              <p>No courses created yet. Click "+ Add New Course" to get started.</p>
            </div>
          <?php else: ?>
            <div class="course-grid">
              <?php foreach ($courses as $c): ?>
                <div class="course-card-box">
                  <div>
                    <span class="badge badge-primary">
                      <?= $c['teacher_name'] ? 'Instructor: ' . htmlspecialchars($c['teacher_name']) : 'Instructor Unassigned' ?>
                    </span>
                    <h3><?= htmlspecialchars($c['title']) ?></h3>
                    <p><?= htmlspecialchars($c['description'] ?: 'No course description provided.') ?></p>
                  </div>
                  <div>
                    <a href="admin_manage_course.php?course_id=<?= $c['id'] ?>" class="btn btn-secondary" style="width: 100%;">
                      View Details & Enrollments →
                    </a>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>

        <!-- TAB 2: TEACHERS DIRECTORY -->
        <div id="teachersTab" class="admin-tab-pane">
          <div class="crud-header">
            <div>
              <h2>Teachers Directory</h2>
              <p>List of all verified instructor accounts</p>
            </div>
          </div>

          <div class="table-container">
            <table class="data-table">
              <thead>
                <tr>
                  <th style="width: 80px;">ID</th>
                  <th>Full Name</th>
                  <th>Email Address</th>
                  <th>Assigned Courses</th>
                  <th>Joined Date</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($teachers)): ?>
                  <tr><td colspan="5" style="text-align: center; padding: 2.5rem; color: var(--text-muted);">No teachers registered yet.</td></tr>
                <?php else: ?>
                  <?php foreach ($teachers as $t): ?>
                    <tr>
                      <td style="color: var(--text-dim); font-weight: 600;">#<?= $t['id'] ?></td>
                      <td><strong><?= htmlspecialchars($t['fullname']) ?></strong></td>
                      <td style="color: var(--text-muted);"><?= htmlspecialchars($t['email']) ?></td>
                      <td><span class="badge badge-primary"><?= (int)$t['course_count'] ?> course(s)</span></td>
                      <td style="color: var(--text-dim);"><?= date('M d, Y', strtotime($t['created_at'])) ?></td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>

        <!-- TAB 3: STUDENTS DIRECTORY -->
        <div id="studentsTab" class="admin-tab-pane">
          <div class="crud-header">
            <div>
              <h2>Students Directory</h2>
              <p>Registered learners across all tracks</p>
            </div>
          </div>

          <div class="table-container">
            <table class="data-table">
              <thead>
                <tr>
                  <th style="width: 80px;">ID</th>
                  <th>Full Name</th>
                  <th>Email Address</th>
                  <th>Status</th>
                  <th>Joined Date</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($students)): ?>
                  <tr><td colspan="5" style="text-align: center; padding: 2.5rem; color: var(--text-muted);">No students registered yet.</td></tr>
                <?php else: ?>
                  <?php foreach ($students as $s): ?>
                    <tr>
                      <td style="color: var(--text-dim); font-weight: 600;">#<?= $s['id'] ?></td>
                      <td><strong><?= htmlspecialchars($s['fullname']) ?></strong></td>
                      <td style="color: var(--text-muted);"><?= htmlspecialchars($s['email']) ?></td>
                      <td><span class="badge badge-success">Active</span></td>
                      <td style="color: var(--text-dim);"><?= date('M d, Y', strtotime($s['created_at'])) ?></td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>

      </main>
    </div>
  </div>

  <!-- Add Course Modal -->
  <div class="modal-overlay" id="addModal">
    <div class="modal-card">
      <div class="modal-header">
        <h3>Create New Course</h3>
        <button class="modal-close" onclick="closeAddModal()">&times;</button>
      </div>
      <form action="admin_dashboard.php" method="POST">
        <input type="hidden" name="action" value="add_course">
        
        <div class="form-group">
          <label for="courseTitle">Course Title *</label>
          <input type="text" id="courseTitle" name="title" required placeholder="e.g. Modern Full-Stack Development">
        </div>

        <div class="form-group">
          <label for="courseDesc">Description</label>
          <textarea id="courseDesc" name="description" rows="3" placeholder="Summary of curriculum milestones..."></textarea>
        </div>

        <div class="form-group">
          <label for="teacherSelect">Assign Instructor</label>
          <select id="teacherSelect" name="teacher_id">
            <option value="">-- Unassigned (Select later) --</option>
            <?php foreach ($teachers as $t): ?>
              <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['fullname']) ?> (<?= htmlspecialchars($t['email']) ?>)</option>
            <?php endforeach; ?>
          </select>
        </div>

        <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 0.5rem;">Create Course</button>
      </form>
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

    function openAddModal() { document.getElementById('addModal').classList.add('active'); }
    function closeAddModal() { document.getElementById('addModal').classList.remove('active'); }

    function switchTab(tabId) {
      document.querySelectorAll('.admin-tab-pane').forEach(el => el.classList.remove('active'));
      document.querySelectorAll('.stat-card').forEach(el => el.classList.remove('active-card'));
      document.querySelectorAll('.sidebar-nav .sidebar-link').forEach(el => el.classList.remove('active'));

      const targetPane = document.getElementById(tabId);
      if (targetPane) targetPane.classList.add('active');

      if (tabId === 'coursesTab') {
        document.getElementById('cardCourses').classList.add('active-card');
        document.getElementById('navCourses').classList.add('active');
      } else if (tabId === 'teachersTab') {
        document.getElementById('cardTeachers').classList.add('active-card');
        document.getElementById('navTeachers').classList.add('active');
      } else if (tabId === 'studentsTab') {
        document.getElementById('cardStudents').classList.add('active-card');
        document.getElementById('navStudents').classList.add('active');
      }

      // Auto-close sidebar on mobile after tab switch
      if (window.innerWidth <= 868) {
        sidebar.classList.remove('open');
        backdrop.classList.remove('active');
      }
    }
  </script>

</body>
</html>