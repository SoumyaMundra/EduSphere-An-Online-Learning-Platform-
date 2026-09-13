<?php
session_start();
require_once 'db.php';

// Auth Guard: Student / Learner only
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['student', 'learner'])) {
    header("Location: login.php");
    exit();
}

$student_id = (int)$_SESSION['user_id'];
$course_id  = (int)($_GET['id'] ?? 0);

if ($course_id <= 0) {
    header("Location: student_dashboard.php");
    exit();
}

// Verify enrollment gate
$enrollCheck = $pdo->prepare("SELECT id FROM enrollments WHERE student_id = :student_id AND course_id = :course_id LIMIT 1");
$enrollCheck->execute([':student_id' => $student_id, ':course_id' => $course_id]);

if (!$enrollCheck->fetch()) {
    $_SESSION['flash_error'] = "You must be enrolled in this course to view its curriculum.";
    header("Location: student_dashboard.php?tab=explore");
    exit();
}

// Fetch Course details & instructor name
$courseStmt = $pdo->prepare("
    SELECT c.*, u.fullname AS teacher_name, u.email AS teacher_email
    FROM courses c
    LEFT JOIN users u ON c.teacher_id = u.id
    WHERE c.id = :course_id
    LIMIT 1
");
$courseStmt->execute([':course_id' => $course_id]);
$course = $courseStmt->fetch();

if (!$course) {
    header("Location: student_dashboard.php");
    exit();
}

// Fetch all lectures for this course
$lecturesStmt = $pdo->prepare("SELECT * FROM lectures WHERE course_id = :course_id ORDER BY id ASC");
$lecturesStmt->execute([':course_id' => $course_id]);
$lectures = $lecturesStmt->fetchAll();

// Determine currently active lecture (defaults to first lecture)
$active_lecture_id = (int)($_GET['lecture'] ?? ($lectures[0]['id'] ?? 0));
$currentLecture = null;
foreach ($lectures as $lec) {
    if ((int)$lec['id'] === $active_lecture_id) {
        $currentLecture = $lec;
        break;
    }
}
if (!$currentLecture && !empty($lectures)) {
    $currentLecture = $lectures[0];
}

// Helper to convert standard YouTube URLs into embeddable URLs
function getEmbedUrl($url) {
    if (empty($url)) return '';
    if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/ ]{11})/i', $url, $match)) {
        return 'https://www.youtube.com/embed/' . $match[1];
    }
    return $url;
}

$firstInitial = strtoupper(substr($_SESSION['user_name'], 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($course['title']) ?> - EduSphere</title>
  <link rel="stylesheet" href="dashboard.css">
</head>
<body>

  <div class="dashboard-layout">
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
      <div>
        <div class="sidebar-header">
          <a href="index.php" class="logo">Edu<span>Sphere</span></a>
        </div>

        <ul class="sidebar-nav">
          <li>
            <a href="student_dashboard.php?tab=my_courses" class="sidebar-link">
              <svg viewBox="0 0 24 24">
                <path d="M19 12H5M12 19l-7-7 7-7"></path>
              </svg>
              <span>Back to My Courses</span>
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

    <!-- Main Course Viewer Area -->
    <div class="dashboard-content">
      <header class="dashboard-topbar">
        <!-- Mobile Sidebar Toggle -->
        <button class="sidebar-toggle-btn" id="sidebarToggle" aria-label="Toggle Navigation">
          <svg viewBox="0 0 24 24"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
        </button>

        <div class="topbar-welcome">
          Course Track: <span><?= htmlspecialchars($course['title']) ?></span>
        </div>
      </header>

      <main class="dashboard-body">
        <div class="course-viewer-grid">
          
          <!-- Left Column: Video Player & Lecture Notes -->
          <section class="viewer-main-col">
            <div class="viewer-video-container">
              <?php if (!empty($currentLecture['video_url'])): ?>
                <?php $embed = getEmbedUrl($currentLecture['video_url']); ?>
                <?php if (strpos($embed, 'youtube.com/embed') !== false): ?>
                  <iframe src="<?= htmlspecialchars($embed) ?>" allowfullscreen allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"></iframe>
                <?php else: ?>
                  <div style="height:100%; display:flex; flex-direction:column; align-items:center; justify-content:center; padding:1.5rem; text-align:center;">
                    <p style="margin-bottom:0.8rem; color:var(--text-muted);">External media link:</p>
                    <a href="<?= htmlspecialchars($currentLecture['video_url']) ?>" target="_blank" class="btn btn-primary">
                      ▶ Open Video Stream
                    </a>
                  </div>
                <?php endif; ?>
              <?php else: ?>
                <div style="height:100%; display:flex; flex-direction:column; align-items:center; justify-content:center; padding:1.5rem; text-align:center; color:var(--text-dim);">
                  <svg viewBox="0 0 24 24" style="width:48px;height:48px;stroke:currentColor;fill:none;stroke-width:1.5;margin-bottom:0.8rem;">
                    <polygon points="5 3 19 12 5 21 5 3"></polygon>
                  </svg>
                  <p>No video attached to this lecture.</p>
                </div>
              <?php endif; ?>
            </div>

            <div class="viewer-details-card">
              <span class="badge badge-primary">Current Lesson</span>
              <h1 style="font-size:1.6rem; font-weight:800; margin: 0.6rem 0 0.4rem; color:var(--text-main);">
                <?= htmlspecialchars($currentLecture['title'] ?? 'No Lectures Available') ?>
              </h1>
              
              <p style="color:var(--text-dim); font-size:0.85rem; margin-bottom:1.5rem;">
                Taught by <strong style="color:var(--text-muted);"><?= htmlspecialchars($course['teacher_name'] ?: 'Unassigned Instructor') ?></strong>
              </p>

              <div>
                <h3 style="font-size:1.05rem; font-weight:700; margin-bottom:0.6rem; color:var(--text-main);">Lesson Notes & Guide</h3>
                <?php if (!empty($currentLecture['content'])): ?>
                  <p style="color:var(--text-muted); font-size:0.92rem; line-height:1.75; white-space:pre-line;">
                    <?= htmlspecialchars($currentLecture['content']) ?>
                  </p>
                <?php else: ?>
                  <p style="color:var(--text-dim); font-style:italic; font-size:0.88rem;">No lesson notes provided for this unit.</p>
                <?php endif; ?>
              </div>
            </div>
          </section>

          <!-- Right Column: Course Curriculum Playlist -->
          <aside class="viewer-playlist-col">
            <div class="playlist-card">
              <div class="playlist-header">
                <h2 style="font-size:1.05rem; font-weight:700;">Course Content</h2>
                <span class="badge badge-secondary"><?= count($lectures) ?> Lessons</span>
              </div>

              <div class="playlist-items">
                <?php if (empty($lectures)): ?>
                  <p style="color:var(--text-dim); padding:1rem; font-style:italic;">No lectures posted yet.</p>
                <?php else: ?>
                  <?php foreach ($lectures as $idx => $lec): ?>
                    <?php $isActive = ((int)$lec['id'] === (int)($currentLecture['id'] ?? 0)); ?>
                    <a href="course_view.php?id=<?= $course_id ?>&lecture=<?= $lec['id'] ?>" 
                       class="playlist-item <?= $isActive ? 'active' : '' ?>">
                      <div class="playlist-item-num"><?= $idx + 1 ?></div>
                      <div class="playlist-item-info">
                        <h4><?= htmlspecialchars($lec['title']) ?></h4>
                        <span><?= !empty($lec['video_url']) ? '▶ Video Included' : '📄 Reading' ?></span>
                      </div>
                    </a>
                  <?php endforeach; ?>
                <?php endif; ?>
              </div>
            </div>
          </aside>

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
  </script>

</body>
</html>