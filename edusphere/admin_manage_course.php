<?php
session_start();
require_once 'db.php';

// Auth Guard: Admin only
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$course_id = (int)($_GET['course_id'] ?? 0);

if ($course_id <= 0) {
    header("Location: admin_dashboard.php");
    exit();
}

// Handle Course Updates & Deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'edit_course') {
        $title       = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $teacher_id  = !empty($_POST['teacher_id']) ? (int)$_POST['teacher_id'] : null;

        if (!empty($title)) {
            $updateStmt = $pdo->prepare("
                UPDATE courses 
                SET title = :title, description = :description, teacher_id = :teacher_id 
                WHERE id = :id
            ");
            $updateStmt->execute([
                ':title'       => $title,
                ':description' => $description,
                ':teacher_id'  => $teacher_id,
                ':id'          => $course_id
            ]);
            $_SESSION['flash_success'] = "Course details and instructor assignment updated!";
        } else {
            $_SESSION['flash_error'] = "Course title cannot be empty.";
        }
        header("Location: admin_manage_course.php?course_id=$course_id");
        exit();
    }

    if ($action === 'delete_course') {
        $stmt = $pdo->prepare("DELETE FROM courses WHERE id = :id");
        $stmt->execute([':id' => $course_id]);
        $_SESSION['flash_success'] = "Course deleted successfully.";
        header("Location: admin_dashboard.php");
        exit();
    }
}

// Fetch Course Info with Instructor Details
$courseStmt = $pdo->prepare("
    SELECT c.*, u.fullname AS teacher_name, u.email AS teacher_email 
    FROM courses c 
    LEFT JOIN users u ON c.teacher_id = u.id 
    WHERE c.id = :id 
    LIMIT 1
");
$courseStmt->execute([':id' => $course_id]);
$course = $courseStmt->fetch();

if (!$course) {
    $_SESSION['flash_error'] = "Course not found.";
    header("Location: admin_dashboard.php");
    exit();
}

// Fetch All Teachers
$teachersStmt = $pdo->query("SELECT id, fullname, email FROM users WHERE role = 'teacher' ORDER BY fullname ASC");
$teachers = $teachersStmt->fetchAll();

// Fetch Enrolled Students
$enrolledStudentsStmt = $pdo->prepare("
    SELECT u.id, u.fullname, u.email, e.enrolled_at 
    FROM enrollments e 
    JOIN users u ON e.student_id = u.id 
    WHERE e.course_id = :course_id 
    ORDER BY e.enrolled_at DESC
");
$enrolledStudentsStmt->execute([':course_id' => $course_id]);
$enrolledStudents = $enrolledStudentsStmt->fetchAll();

// Fetch Lectures
$lecturesStmt = $pdo->prepare("SELECT * FROM lectures WHERE course_id = :course_id ORDER BY id ASC");
$lecturesStmt->execute([':course_id' => $course_id]);
$lectures = $lecturesStmt->fetchAll();

$firstInitial = strtoupper(substr($_SESSION['user_name'], 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Course - <?= htmlspecialchars($course['title']) ?></title>
  <link rel="stylesheet" href="dashboard.css">
</head>
<body>

  <div class="dashboard-layout">
    <aside class="sidebar" id="sidebar">
      <div>
        <div class="sidebar-header">
          <a href="index.php" class="logo">Edu<span>Sphere</span></a>
        </div>

        <ul class="sidebar-nav">
          <li>
            <a href="admin_dashboard.php" class="sidebar-link">
              <svg viewBox="0 0 24 24"><path d="M19 12H5M12 19l-7-7 7-7"></path></svg>
              <span>Back to Dashboard</span>
            </a>
          </li>
          <li>
            <a href="admin_manage_course.php?course_id=<?= $course_id ?>" class="sidebar-link active">
              <svg viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"></path></svg>
              <span>Course Overview</span>
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

    <div class="dashboard-content">
      <header class="dashboard-topbar">
        <!-- Mobile Sidebar Hamburger Button -->
        <button class="sidebar-toggle-btn" id="sidebarToggle" aria-label="Toggle Navigation">
          <svg viewBox="0 0 24 24"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
        </button>

        <div class="topbar-welcome">
          Course: <span><?= htmlspecialchars($course['title']) ?></span>
        </div>
      </header>

      <main class="dashboard-body">
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

        <!-- Responsive Action Bar -->
        <div class="crud-header" style="margin-bottom: 1.8rem;">
          <div>
            <h1 style="font-size: 1.75rem; font-weight: 800; color: var(--text-main); margin-bottom: 0.25rem;">
              <?= htmlspecialchars($course['title']) ?>
            </h1>
            <p style="color: var(--text-muted); font-size: 0.95rem; max-width: 750px;">
              <?= htmlspecialchars($course['description'] ?: 'No course description provided.') ?>
            </p>
          </div>
          <div style="display: flex; gap: 0.6rem; flex-wrap: wrap;">
            <button class="btn btn-primary" onclick="openEditModal()">Edit Course & Assign Teacher</button>
            <form action="admin_manage_course.php?course_id=<?= $course_id ?>" method="POST" onsubmit="return confirm('Are you sure you want to delete this course? All associated lectures and enrollments will be permanently deleted.');" style="margin: 0;">
              <input type="hidden" name="action" value="delete_course">
              <button type="submit" class="btn btn-secondary" style="color: var(--danger-text); border-color: var(--danger-border); background: var(--danger-bg);">
                Delete Course
              </button>
            </form>
          </div>
        </div>

        <!-- Instructor Summary Box -->
        <div style="background-color: var(--bg-card); border: 1px solid var(--border-subtle); border-radius: var(--radius-md); padding: 1.25rem 1.6rem; margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
          <div>
            <span style="font-size: 0.72rem; color: var(--text-dim); text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px;">Assigned Instructor</span>
            <h3 style="font-size: 1.15rem; font-weight: 700; color: var(--text-main); margin-top: 0.2rem;">
              <?= $course['teacher_name'] ? htmlspecialchars($course['teacher_name']) : '<span style="color: var(--danger-text);">Unassigned</span>' ?>
            </h3>
            <?php if ($course['teacher_email']): ?>
              <p style="font-size: 0.85rem; color: var(--text-muted);"><?= htmlspecialchars($course['teacher_email']) ?></p>
            <?php endif; ?>
          </div>
          <div style="display: flex; gap: 1.5rem;">
            <div style="text-align: center;">
              <span style="display: block; font-size: 1.6rem; font-weight: 800; color: var(--primary-text);"><?= count($enrolledStudents) ?></span>
              <span style="font-size: 0.75rem; color: var(--text-dim); text-transform: uppercase; font-weight: 600;">Enrolled</span>
            </div>
            <div style="text-align: center;">
              <span style="display: block; font-size: 1.6rem; font-weight: 800; color: var(--text-main);"><?= count($lectures) ?></span>
              <span style="font-size: 0.75rem; color: var(--text-dim); text-transform: uppercase; font-weight: 600;">Lectures</span>
            </div>
          </div>
        </div>

        <!-- Sub-Tabs Navigation -->
        <div class="tab-nav-bar">
          <button class="tab-btn active" id="tabBtnStudents" onclick="showSubTab('subStudents')">
            Enrolled Students (<?= count($enrolledStudents) ?>)
          </button>
          <button class="tab-btn" id="tabBtnLectures" onclick="showSubTab('subLectures')">
            Course Lectures (<?= count($lectures) ?>)
          </button>
        </div>

        <!-- Sub-Tab 1: Students -->
        <div id="subStudents" class="sub-tab-content active">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; flex-wrap: wrap; gap: 0.75rem;">
            <p style="color: var(--text-muted); font-size: 0.88rem;">Learners currently enrolled in this track</p>
            <input type="text" id="studentSearch" class="search-filter-input" placeholder="Search student name or email..." onkeyup="filterStudents()">
          </div>

          <div class="scrollable-table-box">
            <table class="data-table">
              <thead>
                <tr>
                  <th style="width: 80px;">ID</th>
                  <th>Full Name</th>
                  <th>Email Address</th>
                  <th style="width: 200px;">Enrollment Date</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($enrolledStudents)): ?>
                  <tr><td colspan="4" style="text-align: center; padding: 2.5rem; color: var(--text-muted);">No students enrolled yet.</td></tr>
                <?php else: ?>
                  <?php foreach ($enrolledStudents as $s): ?>
                    <tr class="student-row">
                      <td style="color: var(--text-dim); font-weight: 600;">#<?= $s['id'] ?></td>
                      <td class="student-name"><strong><?= htmlspecialchars($s['fullname']) ?></strong></td>
                      <td class="student-email" style="color: var(--text-muted);"><?= htmlspecialchars($s['email']) ?></td>
                      <td style="color: var(--text-dim); font-size: 0.85rem;"><?= date('M d, Y h:i A', strtotime($s['enrolled_at'])) ?></td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Sub-Tab 2: Lectures -->
        <div id="subLectures" class="sub-tab-content">
          <div style="margin-bottom: 1rem;">
            <p style="color: var(--text-muted); font-size: 0.88rem;">Curriculum lectures uploaded by the assigned instructor</p>
          </div>

          <div class="scrollable-table-box">
            <table class="data-table">
              <thead>
                <tr>
                  <th style="width: 60px;">#</th>
                  <th>Lecture Title</th>
                  <th style="width: 160px;">Video Link</th>
                  <th style="width: 140px;">Notes</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($lectures)): ?>
                  <tr><td colspan="4" style="text-align: center; padding: 2.5rem; color: var(--text-muted);">No lectures uploaded yet.</td></tr>
                <?php else: ?>
                  <?php foreach ($lectures as $idx => $l): ?>
                    <tr>
                      <td style="color: var(--text-dim); font-weight: 600;">#<?= $idx + 1 ?></td>
                      <td><strong><?= htmlspecialchars($l['title']) ?></strong></td>
                      <td>
                        <?php if (!empty($l['video_url'])): ?>
                          <a href="<?= htmlspecialchars($l['video_url']) ?>" target="_blank" style="color: var(--primary-text); text-decoration: none; font-size: 0.85rem; font-weight: 600;">
                            Watch Video
                          </a>
                        <?php else: ?>
                          <span style="color: var(--text-dim); font-size: 0.85rem;">—</span>
                        <?php endif; ?>
                      </td>
                      <td>
                        <?php if (!empty(trim($l['content']))): ?>
                          <span class="badge badge-success">Attached</span>
                        <?php else: ?>
                          <span style="color: var(--text-dim); font-size: 0.85rem;">—</span>
                        <?php endif; ?>
                      </td>
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

  <!-- Edit Course Modal -->
  <div class="modal-overlay" id="editModal">
    <div class="modal-card">
      <div class="modal-header">
        <h3>Edit Course</h3>
        <button class="modal-close" onclick="closeEditModal()">&times;</button>
      </div>
      <form action="admin_manage_course.php?course_id=<?= $course_id ?>" method="POST">
        <input type="hidden" name="action" value="edit_course">

        <div class="form-group">
          <label for="courseTitle">Course Title *</label>
          <input type="text" id="courseTitle" name="title" value="<?= htmlspecialchars($course['title']) ?>" required>
        </div>

        <div class="form-group">
          <label for="courseDesc">Description</label>
          <textarea id="courseDesc" name="description" rows="3"><?= htmlspecialchars($course['description'] ?: '') ?></textarea>
        </div>

        <div class="form-group">
          <label for="teacherSelect">Assign Instructor</label>
          <select id="teacherSelect" name="teacher_id">
            <option value="">-- Unassigned (No Teacher) --</option>
            <?php foreach ($teachers as $t): ?>
              <option value="<?= $t['id'] ?>" <?= ((int)$t['id'] === (int)$course['teacher_id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($t['fullname']) ?> (<?= htmlspecialchars($t['email']) ?>)
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 0.5rem;">Save Changes</button>
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

    function openEditModal() { document.getElementById('editModal').classList.add('active'); }
    function closeEditModal() { document.getElementById('editModal').classList.remove('active'); }

    function showSubTab(tabId) {
      document.getElementById('subStudents').style.display = (tabId === 'subStudents') ? 'block' : 'none';
      document.getElementById('subLectures').style.display = (tabId === 'subLectures') ? 'block' : 'none';

      document.getElementById('tabBtnStudents').classList.toggle('active', tabId === 'subStudents');
      document.getElementById('tabBtnLectures').classList.toggle('active', tabId === 'subLectures');
    }

    function filterStudents() {
      const query = document.getElementById('studentSearch').value.toLowerCase();
      const rows = document.querySelectorAll('.student-row');
      rows.forEach(row => {
        const name = row.querySelector('.student-name').textContent.toLowerCase();
        const email = row.querySelector('.student-email').textContent.toLowerCase();
        row.style.display = (name.includes(query) || email.includes(query)) ? '' : 'none';
      });
    }
  </script>

</body>
</html>