<?php
session_start();
require_once '../includes/db.php';
if (!isset($_SESSION['instructor_id'])) { header('Location: login.php'); exit; }
$ins_id = $_SESSION['instructor_id'];
$_has_resolved = $conn->query("SELECT COUNT(*) as c FROM support_messages WHERE sender_type='instructor' AND sender_id=$ins_id AND status='resolved'")->fetch_assoc()['c'] > 0;

$id = intval($_GET['id'] ?? 0);
$course = $conn->query("SELECT c.*, COUNT(DISTINCT e.id) as student_count, COALESCE(AVG(r.rating),0) as avg_rating
    FROM courses c
    LEFT JOIN enrollments e ON c.id=e.course_id
    LEFT JOIN ratings r ON c.id=r.course_id
    WHERE c.id=$id AND c.instructor_id=$ins_id
    GROUP BY c.id")->fetch_assoc();

if (!$course) { header('Location: my-courses.php'); exit; }

$units = $conn->query("SELECT * FROM units WHERE course_id=$id ORDER BY unit_order");
$enrolled = $conn->query("SELECT u.full_name, u.email, e.enrolled_at FROM enrollments e JOIN users u ON e.user_id=u.id WHERE e.course_id=$id ORDER BY e.enrolled_at DESC");
$reviews = $conn->query("SELECT r.*, u.full_name FROM ratings r JOIN users u ON r.user_id=u.id WHERE r.course_id=$id ORDER BY r.created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?= htmlspecialchars($course['title']) ?> - Instructor</title>
<link rel="stylesheet" href="../assets/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
.unit-block { background:var(--gray-light); border-radius:var(--radius); padding:16px; margin-bottom:12px; border:1px solid var(--gray-border); }
.lesson-item { background:white; border-radius:var(--radius-sm); padding:12px 16px; margin-top:8px; display:flex; justify-content:space-between; align-items:center; border:1px solid var(--gray-border); }
</style>
</head>
<body>
<div class="dashboard-layout">
  <aside class="sidebar">
    <div class="sidebar-brand"><img src="../assets/images/logo.png" alt="LearnSpace" ></div>
    <ul class="sidebar-nav">
      <li><a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
      <li><a href="add-course.php"><i class="fas fa-plus-circle"></i> Add Course</a></li>
      <li><a href="my-courses.php" class="active"><i class="fas fa-book"></i> My Courses</a></li>
      <li><a href="report-issue.php"><i class="fas fa-flag"></i> Report an Issue<?php if($_has_resolved): ?> <span title="One or more issues resolved" style="display:inline-flex;align-items:center;justify-content:center;width:18px;height:18px;background:#16a34a;color:white;border-radius:50%;font-size:.7rem;font-weight:900;margin-left:4px">!</span><?php endif; ?></a></li>
      <li><a href="profile.php"><i class="fas fa-user"></i> My Profile</a></li>
      <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
    </ul>
  </aside>
  <div class="main-content">
    <div class="main-header">
      <h1><i class="fas fa-book-open"></i> <?= htmlspecialchars($course['title']) ?></h1>
      <div style="display:flex;gap:8px;align-items:center">
        <a href="add-quiz.php?course_id=<?= $id ?>" class="btn btn-primary btn-sm"><i class="fas fa-clipboard-question"></i> Manage Quiz</a>
        <span class="badge badge-<?= $course['status'] ?>"><?= ucfirst($course['status']) ?></span>
        <a href="my-courses.php" class="btn btn-outline btn-sm"><i class="fas fa-arrow-left"></i> Back</a>
      </div>
    </div>
    <div class="page-body">

      <!-- Stats -->
      <div class="stats-grid" style="margin-bottom:24px">
        <div class="stat-card">
          <div class="stat-icon red"><i class="fas fa-users"></i></div>
          <div class="stat-info"><h3><?= $course['student_count'] ?></h3><p>Enrolled Students</p></div>
        </div>
        <div class="stat-card">
          <div class="stat-icon yellow"><i class="fas fa-star"></i></div>
          <div class="stat-info"><h3><?= number_format($course['avg_rating'],1) ?>/5</h3><p>Average Rating</p></div>
        </div>
        <div class="stat-card">
          <div class="stat-icon green"><i class="fas fa-calendar"></i></div>
          <div class="stat-info"><h3><?= date('M d',strtotime($course['created_at'])) ?></h3><p>Date Created</p></div>
        </div>
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px">
        <!-- Course Content -->
        <div>
          <div class="section-title">📚 Course Content</div>
          <?php while($unit=$units->fetch_assoc()): ?>
          <div class="unit-block">
            <strong><i class="fas fa-layer-group" style="color:var(--primary)"></i> <?= htmlspecialchars($unit['title']) ?></strong>
            <?php $lessons=$conn->query("SELECT * FROM lessons WHERE unit_id={$unit['id']} ORDER BY lesson_order");
            while($l=$lessons->fetch_assoc()): ?>
            <div class="lesson-item">
              <span><i class="fas fa-play-circle" style="color:var(--primary)"></i> <?= htmlspecialchars($l['title']) ?></span>
              <a href="<?= htmlspecialchars($l['lesson_link']) ?>" target="_blank" class="btn btn-outline btn-sm" style="font-size:0.75rem"><i class="fas fa-external-link-alt"></i></a>
            </div>
            <?php endwhile; ?>
          </div>
          <?php endwhile; ?>
          <?php
            $quizzes = $conn->query("SELECT * FROM quizzes WHERE course_id=$id");
            while($qz = $quizzes->fetch_assoc()):
          ?>
          <div class="unit-block" style="border-color:var(--primary);">
            <strong><i class="fas fa-question-circle" style="color:var(--primary)"></i> <?= htmlspecialchars($qz['title']) ?></strong>
            <p style="font-size:0.8rem;color:var(--gray);margin-top:5px;">Pass Mark: <?= $qz['pass_percentage'] ?>%</p>
          </div>
          <?php endwhile; ?>
        </div>

        <!-- Enrolled Students + Reviews -->
        <div>
          <div class="section-title">👥 Enrolled Students</div>
          <div class="card" style="margin-bottom:20px;max-height:280px;overflow-y:auto">
            <?php $count=0; while($s=$enrolled->fetch_assoc()): $count++; ?>
            <div style="display:flex;align-items:center;gap:12px;padding:12px 16px;border-bottom:1px solid var(--gray-border)">
              <div class="avatar"><?= strtoupper(substr($s['full_name'],0,1)) ?></div>
              <div>
                <div style="font-weight:700;font-size:0.9rem"><?= htmlspecialchars($s['full_name']) ?></div>
                <div style="font-size:0.78rem;color:var(--gray)"><?= date('M d, Y',strtotime($s['enrolled_at'])) ?></div>
              </div>
            </div>
            <?php endwhile; ?>
            <?php if($count===0): ?><p style="text-align:center;padding:20px;color:var(--gray)">No students yet</p><?php endif; ?>
          </div>

          <div class="section-title">⭐ Student Reviews</div>
          <div class="card" style="max-height:280px;overflow-y:auto">
            <?php $rcount=0; while($rev=$reviews->fetch_assoc()): $rcount++; ?>
            <div style="padding:12px 16px;border-bottom:1px solid var(--gray-border)">
              <div style="display:flex;justify-content:space-between;margin-bottom:4px">
                <strong style="font-size:0.88rem"><?= htmlspecialchars($rev['full_name']) ?></strong>
                <span class="stars"><?= str_repeat('★',$rev['rating']).str_repeat('☆',5-$rev['rating']) ?></span>
              </div>
              <?php if($rev['review']): ?><p style="font-size:0.83rem;color:var(--gray)"><?= htmlspecialchars($rev['review']) ?></p><?php endif; ?>
            </div>
            <?php endwhile; ?>
            <?php if($rcount===0): ?><p style="text-align:center;padding:20px;color:var(--gray)">No reviews yet</p><?php endif; ?>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>
<script src='https://cdn.jotfor.ms/agent/embedjs/019d85b564bd7b53bf17ecb93621ce83ef1b/embed.js'>
</script>
</body>
</html>
