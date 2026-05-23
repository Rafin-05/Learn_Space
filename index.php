<?php
session_start();
require_once 'includes/db.php';

// Fetch approved courses for display
$courses_query = "SELECT c.*, i.full_name as instructor_name, 
    COALESCE(AVG(r.rating), 0) as avg_rating,
    COUNT(DISTINCT e.id) as student_count
    FROM courses c 
    JOIN instructors i ON c.instructor_id = i.id 
    LEFT JOIN ratings r ON c.id = r.course_id
    LEFT JOIN enrollments e ON c.id = e.course_id
    WHERE c.status = 'approved'
    GROUP BY c.id
    ORDER BY c.created_at DESC LIMIT 8";
$courses_result = $conn->query($courses_query);
$courses = [];
if ($courses_result) {
    while ($row = $courses_result->fetch_assoc()) {
        $courses[] = $row;
    }
}

$icons = ['💻','📊','🎨','📈','🔬','📱','🌐','🎯'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>LearnSpace - Master New Skills, Elevate Your Future</title>
<link rel="stylesheet" href="assets/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
/* HERO */
.hero {
  position: relative;
  min-height: calc(100vh - 72px);
  display: flex;
  align-items: center;
  overflow: hidden;
}

.hero-bg {
  position: absolute;
  inset: 0;
  background-image: url('assets/images/hero-cover.png');
  background-size: cover;
  background-position: center right;
  background-repeat: no-repeat;
  z-index: 0;
}

.hero-bg::after {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(
    to right,
    rgba(10, 10, 20, 0.82) 0%,
    rgba(10, 10, 20, 0.65) 45%,
    rgba(10, 10, 20, 0.15) 75%,
    transparent 100%
  );
}

.hero-right {
  position: relative;
  z-index: 2;
  display: flex;
  flex-direction: column;
  justify-content: center;
  padding: 80px 60px;
  max-width: 620px;
}

.hero-tagline {
  font-size: 0.85rem;
  font-weight: 800;
  color: #ff8080;
  text-transform: uppercase;
  letter-spacing: 0.1em;
  margin-bottom: 12px;
  display: flex;
  align-items: center;
  gap: 8px;
}

.hero-tagline::before {
  content: '';
  width: 30px;
  height: 3px;
  background: var(--primary);
  border-radius: 2px;
}

.hero-title {
  font-size: 3.2rem;
  font-family: 'Poppins', sans-serif;
  font-weight: 900;
  color: #ffffff;
  line-height: 1.1;
  margin-bottom: 16px;
}

.hero-subtitle {
  font-size: 1.4rem;
  font-weight: 700;
  color: rgba(255,255,255,0.9);
  margin-bottom: 12px;
}

.hero-subtitle strong { color: #ff8080; }

.hero-desc {
  color: rgba(255,255,255,0.72);
  font-size: 1rem;
  margin-bottom: 32px;
  max-width: 420px;
  line-height: 1.8;
}

.hero-buttons {
  display: flex;
  gap: 16px;
  margin-bottom: 40px;
  flex-wrap: wrap;
}




.mini-info h4 {
  font-size: 0.85rem;
  font-weight: 700;
  margin-bottom: 2px;
  color: var(--black);
}

.mini-info .mini-author {
  font-size: 0.75rem;
  color: var(--gray);
  margin-bottom: 4px;
}

.mini-meta {
  display: flex;
  align-items: center;
  justify-content: space-between;
  font-size: 0.78rem;
}

.mini-price { color: var(--primary); font-weight: 800; }
.mini-rating { color: #f59e0b; font-weight: 700; }

/* STATS BAR */
.stats-bar {
  background: var(--primary);
  color: white;
  padding: 24px 60px;
  display: flex;
  justify-content: space-around;
  flex-wrap: wrap;
  gap: 20px;
}

.stats-bar-item {
  text-align: center;
}

.stats-bar-item h3 {
  font-size: 2rem;
  font-weight: 900;
  margin-bottom: 4px;
}

.stats-bar-item p {
  font-size: 0.85rem;
  opacity: 0.85;
}

/* COURSES SECTION */
.section {
  padding: 60px;
}

.section-header {
  display: flex;
  align-items: flex-end;
  justify-content: space-between;
  margin-bottom: 32px;
  flex-wrap: wrap;
  gap: 16px;
}

.section-header-left h2 {
  font-size: 2rem;
  color: var(--black);
  margin-bottom: 8px;
}

.section-header-left p {
  color: var(--gray);
  font-size: 0.95rem;
}

.courses-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
  gap: 24px;
}

/* FEATURES */
.features-section {
  background: var(--gray-light);
  padding: 60px;
}

.features-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
  gap: 24px;
}

.feature-card {
  background: white;
  border-radius: var(--radius);
  padding: 28px;
  text-align: center;
  border: 1px solid var(--gray-border);
  transition: var(--transition);
}

.feature-card:hover {
  border-color: var(--primary);
  transform: translateY(-4px);
  box-shadow: var(--shadow);
}

.feature-icon {
  width: 64px;
  height: 64px;
  background: var(--primary-bg);
  border-radius: var(--radius);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.8rem;
  margin: 0 auto 16px;
  color: var(--primary);
}

.feature-card h3 { font-size: 1.05rem; margin-bottom: 10px; }
.feature-card p { font-size: 0.88rem; color: var(--gray); line-height: 1.7; }

/* FOOTER */
footer {
  background: var(--black);
  color: white;
  padding: 50px 60px 24px;
}

.footer-grid {
  display: grid;
  grid-template-columns: 2fr 1fr 1fr 1fr;
  gap: 40px;
  margin-bottom: 40px;
}

.footer-brand p {
  color: rgba(255,255,255,0.6);
  font-size: 0.9rem;
  margin-top: 12px;
  line-height: 1.8;
  max-width: 280px;
}

.footer-col h4 {
  color: white;
  font-size: 0.95rem;
  margin-bottom: 16px;
}

.footer-col ul {
  list-style: none;
}

.footer-col ul li {
  margin-bottom: 10px;
}

.footer-col ul li a {
  color: rgba(255,255,255,0.6);
  font-size: 0.88rem;
  transition: var(--transition);
}

.footer-col ul li a:hover {
  color: var(--primary-light);
}

.footer-bottom {
  border-top: 1px solid rgba(255,255,255,0.1);
  padding-top: 24px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 12px;
}

.footer-bottom p {
  color: rgba(255,255,255,0.4);
  font-size: 0.85rem;
}

.no-courses {
  grid-column: 1/-1;
  text-align: center;
  padding: 60px;
  color: var(--gray);
}

.no-courses i { font-size: 3rem; margin-bottom: 12px; display: block; color: var(--gray-border); }
</style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar">
  <a href="index.php" class="navbar-brand">
    <img src="assets/images/logo.png" alt="LearnSpace" >
  </a>
  <ul class="nav-links">
    <li><a href="index.php" class="active">Home</a></li>
    <li><a href="learner/courses.php">Courses</a></li>
    <li><a href="#about">About Us</a></li>
    <li><a href="#features">Features</a></li>
  </ul>
  <div class="nav-actions">
    <?php if (isset($_SESSION['user_id'])): ?>
      <a href="learner/dashboard.php" class="btn btn-primary">My Learning</a>
    <?php elseif (isset($_SESSION['instructor_id'])): ?>
      <a href="instructor/dashboard.php" class="btn btn-primary">Dashboard</a>
    <?php elseif (isset($_SESSION['admin_id'])): ?>
      <a href="admin/dashboard.php" class="btn btn-primary">Admin Panel</a>
    <?php else: ?>
      <a href="learner/login.php" class="btn btn-outline">Login</a>
      <a href="learner/register.php" class="btn btn-primary">Join Free</a>
    <?php endif; ?>
  </div>
</nav>

<!-- HERO -->
<section class="hero">
  <div class="hero-bg"></div>
  <div class="hero-right">
    <div class="hero-tagline">Online Learning Platform</div>
    <h1 class="hero-title">LearnSpace</h1>
    <div class="hero-subtitle">Master New Skills, <strong>Elevate Your Future.</strong></div>
    <p class="hero-desc">Unlock unlimited potential with expert-led online courses across technology, business, and creativity.</p>
    <div class="hero-buttons">
      <a href="learner/courses.php" class="btn btn-primary btn-lg">
        <i class="fas fa-rocket"></i> Explore Courses
      </a>
      <a href="learner/register.php" class="btn btn-outline btn-lg" style="color:white;border-color:rgba(255,255,255,0.5);">
        <i class="fas fa-user-plus"></i> Join for Free
      </a>
    </div>
  </div>
</section>

<!-- STATS BAR -->
<div class="stats-bar">
  <div class="stats-bar-item">
    <h3>10,000+</h3>
    <p>Active Learners</p>
  </div>
  <div class="stats-bar-item">
    <h3>500+</h3>
    <p>Online Courses</p>
  </div>
  <div class="stats-bar-item">
    <h3>200+</h3>
    <p>Expert Instructors</p>
  </div>
  <div class="stats-bar-item">
    <h3>95%</h3>
    <p>Satisfaction Rate</p>
  </div>
</div>

<!-- COURSES SECTION -->
<section class="section" id="courses">
  <div class="section-header">
    <div class="section-header-left">
      <h2>🔥 Featured Courses</h2>
      <p>Hand-picked courses from our top instructors</p>
    </div>
    <a href="learner/courses.php" class="btn btn-outline">View All Courses →</a>
  </div>
  <div class="courses-grid">
    <?php if (empty($courses)): ?>
    <div class="no-courses">
      <i class="fas fa-book-open"></i>
      <h3>No courses yet</h3>
      <p>Be the first to create a course!</p>
      <a href="instructor/register.php" class="btn btn-primary" style="margin-top:16px">Become an Instructor</a>
    </div>
    <?php else: ?>
    <?php foreach ($courses as $i => $course): ?>
    <div class="course-card">
      <div class="course-card-thumb">
        <?php if ($course['thumbnail']): ?>
          <img src="assets/images/<?= htmlspecialchars($course['thumbnail']) ?>" alt="<?= htmlspecialchars($course['title']) ?>">
        <?php else: ?>
          <?= $icons[$i % count($icons)] ?>
        <?php endif; ?>
      </div>
      <div class="course-card-body">
        <div class="course-card-title"><?= htmlspecialchars($course['title']) ?></div>
        <div class="course-card-instructor"><i class="fas fa-user-tie"></i> <?= htmlspecialchars($course['instructor_name']) ?></div>
        <div class="course-card-meta">
          <span class="stars">
            <?php
            $r = round($course['avg_rating']);
            for ($s=1;$s<=5;$s++) echo $s<=$r ? '★' : '☆';
            ?> <small style="color:var(--gray)">(<?= number_format($course['avg_rating'],1) ?>)</small>
          </span>
          <span class="badge badge-approved">Free</span>
        </div>
        <div style="margin-top:12px; display:flex; gap:8px; align-items:center; justify-content:space-between;">
          <small style="color:var(--gray)"><i class="fas fa-users"></i> <?= $course['student_count'] ?> students</small>
          <a href="learner/course-detail.php?id=<?= $course['id'] ?>" class="btn btn-primary btn-sm">Enroll Now</a>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
  </div>
</section>

<!-- FEATURES -->
<section class="features-section" id="features">
  <div class="section-header" style="padding:0 0 0 0">
    <div class="section-header-left">
      <h2>Why Choose LearnSpace?</h2>
      <p>Everything you need to learn, grow, and succeed</p>
    </div>
  </div>
  <div class="features-grid">
    <div class="feature-card">
      <div class="feature-icon"><i class="fas fa-chalkboard-teacher"></i></div>
      <h3>Expert Instructors</h3>
      <p>Learn from industry experts with real-world experience in their fields.</p>
    </div>
    <div class="feature-card">
      <div class="feature-icon"><i class="fas fa-certificate"></i></div>
      <h3>Earn Certificates</h3>
      <p>Complete courses and earn certificates to showcase your achievements.</p>
    </div>
    <div class="feature-card">
      <div class="feature-icon"><i class="fas fa-infinity"></i></div>
      <h3>Lifetime Access</h3>
      <p>Enroll once and access your courses forever, learn at your own pace.</p>
    </div>
    <div class="feature-card">
      <div class="feature-icon"><i class="fas fa-mobile-alt"></i></div>
      <h3>Learn Anywhere</h3>
      <p>Access courses on any device, anytime, anywhere in the world.</p>
    </div>
  </div>
</section>
<script src='https://cdn.jotfor.ms/agent/embedjs/019d85b564bd7b53bf17ecb93621ce83ef1b/embed.js'>
</script>
<!-- FOOTER -->
<footer id="about">
  <div class="footer-grid">
    <div class="footer-brand">
      <div class="navbar-brand" style="color:white; font-size:1.4rem;">
        <img src="assets/images/logo.png" alt="LearnSpace" style="height:80px;object-fit:contain;filter:brightness(0) invert(1);">
      </div>
      <p>Empowering learners worldwide with quality education. Master new skills and elevate your future with LearnSpace.</p>
    </div>
    <div class="footer-col">
      <h4>Platform</h4>
      <ul>
        <li><a href="learner/courses.php">Courses</a></li>
        <li><a href="learner/register.php">Join Free</a></li>
        <li><a href="instructor/register.php">Teach on LearnSpace</a></li>
      </ul>
    </div>
    <div class="footer-col">
      <h4>Account</h4>
      <ul>
        <li><a href="learner/login.php">Learner Login</a></li>
        <li><a href="instructor/login.php">Instructor Login</a></li>
        <li><a href="admin/login.php">Admin Login</a></li>
      </ul>
    </div>
    <div class="footer-col">
      <h4>Support</h4>
      <ul>
        <li><a href="#">Help Center</a></li>
        <li><a href="#">Contact Us</a></li>
        <li><a href="#">Privacy Policy</a></li>
      </ul>
    </div>
  </div>
  <div class="footer-bottom">
    <p>© 2026 LearnSpace. All rights reserved.</p>
    <p>Built with ❤️ for university project</p>
  </div>
</footer>

</body>
</html>
