<?php
session_start();
require_once 'db.php';

// Auth Guard: Teacher / Tutor only
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['teacher', 'tutor'])) {
    header("Location: login.php");
    exit();
}

$teacher_id = (int)$_SESSION['user_id'];
$course_id  = (int)($_GET['course_id'] ?? 0);

if ($course_id <= 0) {
    header("Location: teacher_dashboard.php");
    exit();
}

// Security Check: Verify this course is assigned to this teacher
$courseStmt = $pdo->prepare("SELECT * FROM courses WHERE id = :id AND teacher_id = :teacher_id LIMIT 1");
$courseStmt->execute([':id' => $course_id, ':teacher_id' => $teacher_id]);
$course = $courseStmt->fetch();

if (!$course) {
    $_SESSION['flash_error'] = "You are not authorized to manage this course.";
    header("Location: teacher_dashboard.php");
    exit();
}

// Handle Lecture Actions (Add, Edit, Delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_lecture') {
        $title     = trim($_POST['title'] ?? '');
        $content   = trim($_POST['content'] ?? '');
        $video_url = trim($_POST['video_url'] ?? '');

        if (empty($title)) {
            $_SESSION['flash_error'] = "Lecture title cannot be empty.";
        } elseif (empty($content) && empty($video_url)) {
            $_SESSION['flash_error'] = "Please provide at least a video URL or lesson notes.";
        } else {
            $stmt = $pdo->prepare("INSERT INTO lectures (course_id, title, content, video_url) VALUES (:course_id, :title, :content, :video_url)");
            $stmt->execute([
                ':course_id' => $course_id,
                ':title'     => $title,
                ':content'   => $content,
                ':video_url' => $video_url
            ]);
            $_SESSION['flash_success'] = "Lecture '$title' added successfully!";
        }
        header("Location: teacher_manage_course.php?course_id=$course_id");
        exit();
    }

    if ($action === 'edit_lecture') {
        $lecture_id = (int)($_POST['lecture_id'] ?? 0);
        $title      = trim($_POST['title'] ?? '');
        $content    = trim($_POST['content'] ?? '');
        $video_url  = trim($_POST['video_url'] ?? '');

        $verify = $pdo->prepare("SELECT id FROM lectures WHERE id = :id AND course_id = :course_id LIMIT 1");
        $verify->execute([':id' => $lecture_id, ':course_id' => $course_id]);

        if (!$verify->fetch()) {
            $_SESSION['flash_error'] = "Failed to update: lecture not found.";
        } elseif (empty($title)) {
            $_SESSION['flash_error'] = "Lecture title cannot be empty.";
        } elseif (empty($content) && empty($video_url)) {
            $_SESSION['flash_error'] = "Please provide at least a video URL or lesson notes.";
        } else {
            $stmt = $pdo->prepare("UPDATE lectures SET title = :title, content = :content, video_url = :video_url WHERE id = :id");
            $stmt->execute([
                ':title'     => $title,
                ':content'   => $content,
                ':video_url' => $video_url,
                ':id'        => $lecture_id
            ]);
            $_SESSION['flash_success'] = "Lecture updated successfully!";
        }
        header("Location: teacher_manage_course.php?course_id=$course_id");
        exit();
    }

    if ($action === 'delete_lecture') {
        $lecture_id = (int)($_POST['lecture_id'] ?? 0);

        $verify = $pdo->prepare("SELECT id FROM lectures WHERE id = :id AND course_id = :course_id LIMIT 1");
        $verify->execute([':id' => $lecture_id, ':course_id' => $course_id]);

        if ($verify->fetch()) {
            $stmt = $pdo->prepare("DELETE FROM lectures WHERE id = :id");
            $stmt->execute([':id' => $lecture_id]);
            $_SESSION['flash_success'] = "Lecture deleted successfully.";
        }
        header("Location: teacher_manage_course.php?course_id=$course_id");
        exit();
    }
}

// Fetch all lectures for this course
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
    <!-- Obsidian Sidebar -->
    <aside class="sidebar" id="sidebar">
      <div>
        <div class="sidebar-header">
          <a href="index.php" class="logo">Edu<span>Sphere</span></a>
        </div>

        <ul class="sidebar-nav">
          <li>
            <a href="teacher_dashboard.php" class="sidebar-link">
              <svg viewBox="0 0 24 24">
                <path d="M19 12H5M12 19l-7-7 7-7"></path>
              </svg>
              <span>Back to My Courses</span>
            </a>
          </li>
          <li>
            <a href="teacher_manage_course.php?course_id=<?= $course_id ?>" class="sidebar-link active">
              <svg viewBox="0 0 24 24">
                <line x1="8" y1="6" x2="21" y2="6"></line>
                <line x1="8" y1="12" x2="21" y2="12"></line>
                <line x1="8" y1="18" x2="21" y2="18"></line>
                <line x1="3" y1="6" x2="3.01" y2="6"></line>
                <line x1="3" y1="12" x2="3.01" y2="12"></line>
                <line x1="3" y1="18" x2="3.01" y2="18"></line>
              </svg>
              <span>Course Lectures</span>
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
          Managing: <span><?= htmlspecialchars($course['title']) ?></span>
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

        <!-- Course Header Area -->
        <div class="crud-header">
          <div>
            <h1 style="font-size: 1.75rem; font-weight: 800; letter-spacing: -0.5px; margin-bottom: 0.3rem;">
              <?= htmlspecialchars($course['title']) ?>
            </h1>
            <p style="max-width: 720px; line-height: 1.6;">
              <?= htmlspecialchars($course['description'] ?: 'No course description provided.') ?>
            </p>
          </div>
          <button class="btn btn-primary" onclick="openAddModal()">+ Add New Lecture</button>
        </div>

        <!-- Lectures Table Container -->
        <div class="table-container">
          <table class="data-table">
            <thead>
              <tr>
                <th style="width: 60px;">#</th>
                <th>Lecture Title</th>
                <th style="width: 160px;">Video Link</th>
                <th style="width: 140px;">Notes</th>
                <th style="width: 160px; text-align: right;">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($lectures)): ?>
                <tr>
                  <td colspan="5" style="text-align: center; color: var(--text-muted); padding: 3rem 2rem;">
                    No lectures added to this course yet. Click <strong>"+ Add New Lecture"</strong> to upload lessons.
                  </td>
                </tr>
              <?php else: ?>
                <?php foreach ($lectures as $idx => $l): ?>
                  <tr>
                    <td style="color: var(--text-dim);">#<?= $idx + 1 ?></td>
                    <td style="font-weight: 600; color: var(--text-main);"><?= htmlspecialchars($l['title']) ?></td>
                    <td>
                      <?php if (!empty($l['video_url'])): ?>
                        <a href="<?= htmlspecialchars($l['video_url']) ?>" target="_blank" style="color: var(--primary-text); text-decoration: none; font-size: 0.88rem; font-weight: 600;">
                          ▶ Watch Video
                        </a>
                      <?php else: ?>
                        <span style="color: var(--text-dim); font-size: 0.9rem;">—</span>
                      <?php endif; ?>
                    </td>
                    <td>
                      <?php if (!empty(trim($l['content']))): ?>
                        <span class="badge badge-success">Attached</span>
                      <?php else: ?>
                        <span style="color: var(--text-dim); font-size: 0.9rem;">—</span>
                      <?php endif; ?>
                    </td>
                    <td style="text-align: right; white-space: nowrap;">
                      <button class="btn btn-secondary" style="padding: 0.35rem 0.75rem; font-size: 0.8rem; margin-right: 0.35rem;" onclick='openEditModal(<?= json_encode($l) ?>)'>
                        Edit
                      </button>
                      <form action="teacher_manage_course.php?course_id=<?= $course_id ?>" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this lecture?');">
                        <input type="hidden" name="action" value="delete_lecture">
                        <input type="hidden" name="lecture_id" value="<?= $l['id'] ?>">
                        <button type="submit" class="btn btn-logout" style="padding: 0.35rem 0.75rem; display: inline-flex;">
                          Delete
                        </button>
                      </form>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </main>
    </div>
  </div>

  <!-- Add Lecture Modal -->
  <div class="modal-overlay" id="addModal">
    <div class="modal-card">
      <div class="modal-header">
        <h3>Add New Lecture</h3>
        <button class="modal-close" onclick="closeAddModal()">&times;</button>
      </div>
      <form action="teacher_manage_course.php?course_id=<?= $course_id ?>" method="POST" onsubmit="return validateLectureForm(this);">
        <input type="hidden" name="action" value="add_lecture">

        <div class="form-group">
          <label>Lecture Title *</label>
          <input type="text" name="title" required placeholder="e.g. Lesson 1: Introduction">
        </div>

        <div class="form-group">
          <label>Video Stream URL (Optional)</label>
          <input type="url" name="video_url" placeholder="https://www.youtube.com/watch?v=...">
        </div>

        <div class="form-group">
          <label>Lecture Notes / Content (Optional)</label>
          <textarea name="content" rows="4" placeholder="Add text explanations, code snippets, or instructions..."></textarea>
        </div>

        <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 0.5rem;">Upload Lecture</button>
      </form>
    </div>
  </div>

  <!-- Edit Lecture Modal -->
  <div class="modal-overlay" id="editModal">
    <div class="modal-card">
      <div class="modal-header">
        <h3>Edit Lecture</h3>
        <button class="modal-close" onclick="closeEditModal()">&times;</button>
      </div>
      <form action="teacher_manage_course.php?course_id=<?= $course_id ?>" method="POST" onsubmit="return validateLectureForm(this);">
        <input type="hidden" name="action" value="edit_lecture">
        <input type="hidden" name="lecture_id" id="edit_lecture_id">

        <div class="form-group">
          <label>Lecture Title *</label>
          <input type="text" name="title" id="edit_title" required>
        </div>

        <div class="form-group">
          <label>Video Stream URL (Optional)</label>
          <input type="url" name="video_url" id="edit_video_url" placeholder="https://www.youtube.com/watch?v=...">
        </div>

        <div class="form-group">
          <label>Lecture Notes (Optional)</label>
          <textarea name="content" id="edit_content" rows="4"></textarea>
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

    function openAddModal() { document.getElementById('addModal').classList.add('active'); }
    function closeAddModal() { document.getElementById('addModal').classList.remove('active'); }

    function openEditModal(lecture) {
      document.getElementById('edit_lecture_id').value = lecture.id;
      document.getElementById('edit_title').value = lecture.title;
      document.getElementById('edit_content').value = lecture.content || '';
      document.getElementById('edit_video_url').value = lecture.video_url || '';
      document.getElementById('editModal').classList.add('active');
    }
    function closeEditModal() { document.getElementById('editModal').classList.remove('active'); }

    // Validation guard: Ensures either video or notes is filled
    function validateLectureForm(form) {
      const video = form.video_url.value.trim();
      const content = form.content.value.trim();

      if (!video && !content) {
        alert("Please provide at least a video URL or lesson notes.");
        return false;
      }
      return true;
    }
  </script>

</body>
</html>