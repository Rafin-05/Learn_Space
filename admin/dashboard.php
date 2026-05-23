<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// Stats
$stats = [];
$stats['users'] = $conn->query("SELECT COUNT(*) as c FROM users")->fetch_assoc()['c'];
$stats['instructors'] = $conn->query("SELECT COUNT(*) as c FROM instructors")->fetch_assoc()['c'];
$stats['courses'] = $conn->query("SELECT COUNT(*) as c FROM courses WHERE status='approved'")->fetch_assoc()['c'];
$stats['pending'] = $conn->query("SELECT COUNT(*) as c FROM courses WHERE status='pending'")->fetch_assoc()['c'];
$stats['enrollments'] = $conn->query("SELECT COUNT(*) as c FROM enrollments")->fetch_assoc()['c'];

// Recent courses pending
$pending_courses = $conn->query("SELECT c.*, i.full_name as instructor_name FROM courses c JOIN instructors i ON c.instructor_id = i.id WHERE c.status='pending' ORDER BY c.created_at DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard - LearnSpace</title>
<link rel="stylesheet" href="../assets/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<div class="dashboard-layout">

  <!-- SIDEBAR -->
  <aside class="sidebar">
    <div class="sidebar-brand"><img src="../assets/images/logo.png" alt="LearnSpace" ></div>
    <ul class="sidebar-nav">
      <li><a href="dashboard.php" class="active"><i class="fas fa-home"></i> Dashboard</a></li>
      <li><a href="manage-users.php"><i class="fas fa-users"></i> Manage Users</a></li>
      <li><a href="manage-instructors.php"><i class="fas fa-chalkboard-teacher"></i> Manage Instructors</a></li>
      <li><a href="courses.php"><i class="fas fa-book"></i> Courses</a></li>
      <li><a href="pending-courses.php"><i class="fas fa-clock"></i> Pending Courses <?php if($stats['pending']>0): ?><span style="background:var(--primary);color:white;border-radius:50px;padding:2px 8px;font-size:0.75rem;margin-left:4px"><?= $stats['pending'] ?></span><?php endif; ?></a></li>
      <li><a href="remove-courses.php"><i class="fas fa-trash-alt"></i> Remove Courses</a></li>
      <li><a href="contest.php"><i class="fas fa-trophy"></i> Arrange Contest</a></li>
      <li><a href="contest-result.php"><i class="fas fa-medal"></i> Contest Results</a></li>
      <li><a href="manage-coding-problems.php"><i class="fas fa-code"></i> Manage Coding Problems</a></li>
      <li><a href="view-messages.php"><i class="fas fa-envelope"></i> View Messages</a></li>
      <li><a href="logout.php" style="margin-top:20px"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
    </ul>
  </aside>

  <!-- MAIN -->
  <div class="main-content">
    <div class="main-header">
      <h1>📊 Dashboard Overview</h1>
      <div style="display:flex;align-items:center;gap:10px">
        <div class="avatar" style="background:var(--primary-bg)">🛡️</div>
        <span style="font-weight:700">Admin</span>
      </div>
    </div>
    <div class="page-body">

      <!-- STATS -->
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon red"><i class="fas fa-users"></i></div>
          <div class="stat-info">
            <h3><?= $stats['users'] ?></h3>
            <p>Total Learners</p>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon blue"><i class="fas fa-chalkboard-teacher"></i></div>
          <div class="stat-info">
            <h3><?= $stats['instructors'] ?></h3>
            <p>Instructors</p>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon green"><i class="fas fa-book-open"></i></div>
          <div class="stat-info">
            <h3><?= $stats['courses'] ?></h3>
            <p>Active Courses</p>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon yellow"><i class="fas fa-clock"></i></div>
          <div class="stat-info">
            <h3><?= $stats['pending'] ?></h3>
            <p>Pending Approval</p>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon green"><i class="fas fa-user-graduate"></i></div>
          <div class="stat-info">
            <h3><?= $stats['enrollments'] ?></h3>
            <p>Total Enrollments</p>
          </div>
        </div>
      </div>

      <!-- PENDING COURSES -->
      <?php if ($pending_courses && $pending_courses->num_rows > 0): ?>
      <div class="section-title"><i class="fas fa-clock" style="color:var(--warning)"></i> Courses Awaiting Approval</div>
      <div class="table-wrapper" style="margin-bottom:32px">
        <table>
          <thead>
            <tr>
              <th>Course</th>
              <th>Instructor</th>
              <th>Submitted</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php while($c = $pending_courses->fetch_assoc()): ?>
            <tr>
              <td><strong><?= htmlspecialchars($c['title']) ?></strong></td>
              <td><?= htmlspecialchars($c['instructor_name']) ?></td>
              <td><?= date('M d, Y', strtotime($c['created_at'])) ?></td>
              <td>
                <a href="course-action.php?id=<?= $c['id'] ?>&action=approve" class="btn btn-success btn-sm" onclick="return confirm('Approve this course?')"><i class="fas fa-check"></i> Approve</a>
                <a href="course-action.php?id=<?= $c['id'] ?>&action=reject" class="btn btn-danger btn-sm" onclick="return confirm('Reject this course?')"><i class="fas fa-times"></i> Reject</a>
                <a href="course-detail.php?id=<?= $c['id'] ?>" class="btn btn-outline btn-sm"><i class="fas fa-eye"></i> View</a>
              </td>
            </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
      <?php endif; ?>

      <!-- QUICK LINKS -->
      <div class="section-title">Quick Actions</div>
      <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:16px">
        <a href="manage-users.php" class="stat-card" style="text-decoration:none; cursor:pointer;">
          <div class="stat-icon red"><i class="fas fa-users-cog"></i></div>
          <div class="stat-info"><h3 style="font-size:1rem">Manage Users</h3><p>View & block learners</p></div>
        </a>
        <a href="manage-instructors.php" class="stat-card" style="text-decoration:none">
          <div class="stat-icon blue"><i class="fas fa-user-tie"></i></div>
          <div class="stat-info"><h3 style="font-size:1rem">Instructors</h3><p>Manage instructors</p></div>
        </a>
        <a href="courses.php" class="stat-card" style="text-decoration:none">
          <div class="stat-icon green"><i class="fas fa-book"></i></div>
          <div class="stat-info"><h3 style="font-size:1rem">All Courses</h3><p>View course info</p></div>
        </a>
        <a href="pending-courses.php" class="stat-card" style="text-decoration:none">
          <div class="stat-icon yellow"><i class="fas fa-clipboard-list"></i></div>
          <div class="stat-info"><h3 style="font-size:1rem">Pending Courses</h3><p>Approve/reject</p></div>
        </a>
      </div>

    </div>
  </div>
</div>
<script src='https://cdn.jotfor.ms/agent/embedjs/019d85b564bd7b53bf17ecb93621ce83ef1b/embed.js'>
</script>
</body>
</html>
