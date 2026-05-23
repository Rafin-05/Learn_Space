<?php
session_start();
require_once '../includes/db.php';
if (!isset($_SESSION['admin_id'])) { header('Location: login.php'); exit; }

$courses = $conn->query("SELECT c.*, i.full_name as instructor_name,
    COUNT(DISTINCT e.id) as student_count,
    COALESCE(AVG(r.rating),0) as avg_rating
    FROM courses c
    JOIN instructors i ON c.instructor_id = i.id
    LEFT JOIN enrollments e ON c.id = e.course_id
    LEFT JOIN ratings r ON c.id = r.course_id
    GROUP BY c.id
    ORDER BY c.created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>All Courses - Admin</title>
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
      <li><a href="contest-result.php"><i class="fas fa-medal"></i> Contest Results</a></li>
      <li><a href="manage-coding-problems.php"><i class="fas fa-code"></i> Manage Coding Problems</a></li>
      <li><a href="view-messages.php"><i class="fas fa-envelope"></i> View Messages</a></li>
      <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
    </ul>
  </aside>
  <div class="main-content">
    <div class="main-header"><h1><i class="fas fa-book"></i> All Courses</h1></div>
    <div class="page-body">
      <div class="table-wrapper">
        <table>
          <thead>
            <tr><th>#</th><th>Course</th><th>Instructor</th><th>Students</th><th>Rating</th><th>Status</th><th>Actions</th></tr>
          </thead>
          <tbody>
            <?php $i=1; while($c = $courses->fetch_assoc()): ?>
            <tr>
              <td><?= $i++ ?></td>
              <td><strong><?= htmlspecialchars($c['title']) ?></strong></td>
              <td><?= htmlspecialchars($c['instructor_name']) ?></td>
              <td><i class="fas fa-users" style="color:var(--primary)"></i> <?= $c['student_count'] ?></td>
              <td><span class="stars">★</span> <?= number_format($c['avg_rating'],1) ?></td>
              <td><span class="badge badge-<?= $c['status'] ?>"><?= ucfirst($c['status']) ?></span></td>
              <td>
                <a href="course-detail.php?id=<?= $c['id'] ?>" class="btn btn-outline btn-sm"><i class="fas fa-eye"></i> View</a>
                <?php if($c['status']==='pending'): ?>
                <a href="course-action.php?id=<?= $c['id'] ?>&action=approve" class="btn btn-success btn-sm" onclick="return confirm('Approve?')"><i class="fas fa-check"></i></a>
                <a href="course-action.php?id=<?= $c['id'] ?>&action=reject" class="btn btn-danger btn-sm" onclick="return confirm('Reject?')"><i class="fas fa-times"></i></a>
                <?php endif; ?>
              </td>
            </tr>
            <?php endwhile; ?>
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
