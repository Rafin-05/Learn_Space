<?php
session_start();
require_once '../includes/db.php';
if (!isset($_SESSION['admin_id'])) { header('Location: login.php'); exit; }

$id = intval($_GET['id'] ?? 0);
$course = $conn->query("SELECT c.*, i.full_name as instructor_name, i.email as instructor_email,
    COUNT(DISTINCT e.id) as student_count,
    COALESCE(AVG(r.rating),0) as avg_rating
    FROM courses c
    JOIN instructors i ON c.instructor_id = i.id
    LEFT JOIN enrollments e ON c.id = e.course_id
    LEFT JOIN ratings r ON c.id = r.course_id
    WHERE c.id = $id
    GROUP BY c.id")->fetch_assoc();

if (!$course) { echo "Course not found"; exit; }

$units = $conn->query("SELECT u.*, COUNT(l.id) as lesson_count FROM units u LEFT JOIN lessons l ON u.id=l.unit_id WHERE u.course_id=$id GROUP BY u.id ORDER BY u.unit_order");
$enrolled_students = $conn->query("SELECT u.full_name, u.email, e.enrolled_at FROM enrollments e JOIN users u ON e.user_id=u.id WHERE e.course_id=$id ORDER BY e.enrolled_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Course Detail - Admin</title>
<link rel="stylesheet" href="../assets/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<div class="dashboard-layout">
  <aside class="sidebar">
    <div class="sidebar-brand"><img src="../assets/images/logo.png" alt="LearnSpace" ></div>
    <ul class="sidebar-nav">
      <li><a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
      <li><a href="manage-users.php"><i class="fas fa-users"></i> Manage Users</a></li>
      <li><a href="manage-instructors.php"><i class="fas fa-chalkboard-teacher"></i> Manage Instructors</a></li>
      <li><a href="courses.php" class="active"><i class="fas fa-book"></i> Courses</a></li>
      <li><a href="pending-courses.php"><i class="fas fa-clock"></i> Pending Courses</a></li>
      <li><a href="remove-courses.php"><i class="fas fa-trash-alt"></i> Remove Courses</a></li>
      <li><a href="contest.php"><i class="fas fa-trophy"></i> Arrange Contest</a></li>
      <li><a href="view-messages.php"><i class="fas fa-envelope"></i> View Messages</a></li>
      <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
    </ul>
  </aside>
  <div class="main-content">
    <div class="main-header">
      <h1><i class="fas fa-book-open"></i> Course Detail</h1>
      <div style="display:flex;gap:8px">
        <?php if($course['status']==='pending'): ?>
        <a href="course-action.php?id=<?= $id ?>&action=approve" class="btn btn-success btn-sm" onclick="return confirm('Approve?')"><i class="fas fa-check"></i> Approve</a>
        <a href="course-action.php?id=<?= $id ?>&action=reject" class="btn btn-danger btn-sm" onclick="return confirm('Reject?')"><i class="fas fa-times"></i> Reject</a>
        <?php endif; ?>
        <a href="courses.php" class="btn btn-outline btn-sm"><i class="fas fa-arrow-left"></i> Back</a>
      </div>
    </div>
    <div class="page-body">
      <!-- Course Info -->
      <div style="display:grid;grid-template-columns:2fr 1fr;gap:24px;margin-bottom:24px">
        <div class="card" style="padding:24px">
          <div style="display:flex;justify-content:space-between;align-items:start;margin-bottom:16px">
            <div style="display:flex;gap:16px;">
              <?php if($course['thumbnail']): ?>
                 <img src="../assets/images/<?= htmlspecialchars($course['thumbnail']) ?>" style="width:120px;height:80px;object-fit:cover;border-radius:8px;" alt="Thumbnail">
              <?php endif; ?>
              <h2 style="font-size:1.4rem"><?= htmlspecialchars($course['title']) ?></h2>
            </div>
            <span class="badge badge-<?= $course['status'] ?>"><?= ucfirst($course['status']) ?></span>
          </div>
          <p style="color:var(--gray);margin-bottom:16px;line-height:1.8"><?= nl2br(htmlspecialchars($course['description'])) ?></p>
          <div style="display:flex;gap:20px;flex-wrap:wrap">
            <div><strong>Instructor:</strong> <?= htmlspecialchars($course['instructor_name']) ?></div>
            <div><strong>Email:</strong> <?= htmlspecialchars($course['instructor_email']) ?></div>
            <div><strong>Created:</strong> <?= date('M d, Y', strtotime($course['created_at'])) ?></div>
          </div>
        </div>
        <div>
          <div class="stat-card" style="margin-bottom:12px">
            <div class="stat-icon red"><i class="fas fa-users"></i></div>
            <div class="stat-info"><h3><?= $course['student_count'] ?></h3><p>Enrolled Students</p></div>
          </div>
          <div class="stat-card">
            <div class="stat-icon yellow"><i class="fas fa-star"></i></div>
            <div class="stat-info"><h3><?= number_format($course['avg_rating'],1) ?></h3><p>Average Rating</p></div>
          </div>
        </div>
      </div>

      <!-- Units/Lessons -->
      <div class="section-title">📚 Course Content</div>
      <?php while($unit = $units->fetch_assoc()): ?>
      <div class="card" style="margin-bottom:12px;padding:16px">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
          <strong><i class="fas fa-layer-group" style="color:var(--primary)"></i> <?= htmlspecialchars($unit['title']) ?></strong>
          <span class="badge badge-approved"><?= $unit['lesson_count'] ?> lessons</span>
        </div>
        <?php
        $lessons = $conn->query("SELECT * FROM lessons WHERE unit_id={$unit['id']} ORDER BY lesson_order");
        while($lesson = $lessons->fetch_assoc()): ?>
        <div style="padding:8px 16px;background:var(--gray-light);border-radius:8px;margin-top:6px;display:flex;justify-content:space-between;align-items:center">
          <span><i class="fas fa-play-circle" style="color:var(--primary)"></i> <?= htmlspecialchars($lesson['title']) ?></span>
          <a href="<?= htmlspecialchars($lesson['lesson_link']) ?>" target="_blank" class="btn btn-outline btn-sm" style="font-size:0.75rem">View Link</a>
        </div>
        <?php endwhile; ?>
      </div>
      <?php endwhile; ?>
      <?php
        $quizzes = $conn->query("SELECT * FROM quizzes WHERE course_id=$id");
        while($qz = $quizzes->fetch_assoc()):
      ?>
      <div class="card" style="margin-bottom:12px;padding:16px;border-color:var(--primary);">
        <div style="display:flex;justify-content:space-between;align-items:center;">
          <strong><i class="fas fa-question-circle" style="color:var(--primary)"></i> <?= htmlspecialchars($qz['title']) ?> (Quiz)</strong>
          <span class="badge badge-approved"><?= $qz['pass_percentage'] ?>% Pass Mark</span>
        </div>
      </div>
      <?php endwhile; ?>

      <!-- Enrolled Students -->
      <div class="section-title" style="margin-top:24px">👥 Enrolled Students</div>
      <div class="table-wrapper">
        <table>
          <thead><tr><th>#</th><th>Name</th><th>Email</th><th>Enrolled On</th></tr></thead>
          <tbody>
            <?php $i=1; while($s = $enrolled_students->fetch_assoc()): ?>
            <tr>
              <td><?= $i++ ?></td>
              <td><?= htmlspecialchars($s['full_name']) ?></td>
              <td><?= htmlspecialchars($s['email']) ?></td>
              <td><?= date('M d, Y', strtotime($s['enrolled_at'])) ?></td>
            </tr>
            <?php endwhile; ?>
            <?php if ($i===1): ?><tr><td colspan="4" style="text-align:center;color:var(--gray)">No students enrolled yet</td></tr><?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<script src='https://cdn.jotfor.ms/agent/embedjs/019d85b564bd7b53bf17ecb93621ce83ef1b/embed.js'>
</script>
</body>
</html>
