<?php
session_start();
require_once '../includes/db.php';
if (!isset($_SESSION['admin_id'])) { header('Location: login.php'); exit; }
$admin_id = $_SESSION['admin_id'];

$success = '';
$error = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Create contest
    if (isset($_POST['create_contest'])) {
        $title = trim($_POST['title']);
        $desc = trim($_POST['description']);
        if (!$title) { $error = "Contest title is required."; }
        else {
            $stmt = $conn->prepare("INSERT INTO contests (title, description, status, created_by) VALUES (?,?,'upcoming',?)");
            $stmt->bind_param("ssi", $title, $desc, $admin_id);
            $stmt->execute();
            $contest_id = $conn->insert_id;
            // Insert prizes
            $prizes = $_POST['prizes'] ?? [];
            foreach ($prizes as $pos => $label) {
                $label = trim($label);
                if (!$label) continue;
                $pos = intval($pos);
                $stmt_p = $conn->prepare("INSERT INTO contest_prizes (contest_id, position, prize_label) VALUES (?,?,?) ON DUPLICATE KEY UPDATE prize_label=VALUES(prize_label)");
                $stmt_p->bind_param("iis", $contest_id, $pos, $label);
                $stmt_p->execute();
            }
            // Insert questions
            $questions = $_POST['questions'] ?? [];
            foreach ($questions as $q) {
                if (empty($q['question'])) continue;
                $opt_c = $q['option_c'] ?? '';
                $opt_d = $q['option_d'] ?? '';
                $order = $q['order'] ?? 0;
                $stmt2 = $conn->prepare("INSERT INTO contest_questions (contest_id, question, option_a, option_b, option_c, option_d, correct_answer, question_order) VALUES (?,?,?,?,?,?,?,?)");
                $stmt2->bind_param("issssssi", $contest_id, $q['question'], $q['option_a'], $q['option_b'], $opt_c, $opt_d, $q['correct_answer'], $order);
                $stmt2->execute();
            }
            $success = "Contest created successfully!";
        }
    }

    // Start contest
    if (isset($_POST['start_contest'])) {
        $cid = intval($_POST['contest_id']);
        $conn->query("UPDATE contests SET status='active', started_at=NOW() WHERE id=$cid");
        $success = "Contest started!";
    }

    // End contest
    if (isset($_POST['end_contest'])) {
        $cid = intval($_POST['contest_id']);
        $conn->query("UPDATE contests SET status='ended', ended_at=NOW() WHERE id=$cid");
        $success = "Contest ended!";
    }

    // Delete contest
    if (isset($_POST['delete_contest'])) {
        $cid = intval($_POST['contest_id']);
        $conn->query("DELETE FROM contests WHERE id=$cid");
        $success = "Contest deleted.";
    }
}

// Fetch all contests
$contests_q = $conn->query("SELECT c.*, COUNT(DISTINCT cp.id) as participant_count FROM contests c LEFT JOIN contest_participants cp ON c.id=cp.contest_id GROUP BY c.id ORDER BY c.created_at DESC");
$contests = [];
while ($r = $contests_q->fetch_assoc()) $contests[] = $r;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Arrange Contest - Admin</title>
<link rel="stylesheet" href="../assets/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
.contest-card { background:white;border-radius:var(--radius);border:1px solid var(--gray-border);padding:20px;margin-bottom:16px; }
.contest-card-header { display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:12px; }
.status-active { background:#dcfce7;color:#16a34a;padding:4px 10px;border-radius:50px;font-size:0.78rem;font-weight:700; }
.status-upcoming { background:#fef9c3;color:#ca8a04;padding:4px 10px;border-radius:50px;font-size:0.78rem;font-weight:700; }
.status-ended { background:#f3f4f6;color:#6b7280;padding:4px 10px;border-radius:50px;font-size:0.78rem;font-weight:700; }
.question-block { background:#f9fafb;border-radius:var(--radius-sm);padding:16px;margin-bottom:12px;border:1px solid var(--gray-border);position:relative; }
.remove-q-btn { position:absolute;top:10px;right:10px;background:none;border:none;color:#dc2626;cursor:pointer;font-size:1rem; }
.modal-overlay { display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:1000;align-items:center;justify-content:center; }
.modal-overlay.open { display:flex; }
.modal-box { background:white;border-radius:var(--radius);padding:32px;max-width:700px;width:90%;max-height:90vh;overflow-y:auto; }
.form-group { margin-bottom:16px; }
.form-group label { display:block;font-weight:700;font-size:0.88rem;margin-bottom:6px; }
.form-group input, .form-group textarea, .form-group select { width:100%;padding:10px 14px;border:1px solid var(--gray-border);border-radius:var(--radius-sm);font-size:0.9rem; }
.options-grid { display:grid;grid-template-columns:1fr 1fr;gap:8px; }
</style>
</head>
<body>
<div class="dashboard-layout">
  <aside class="sidebar">
    <div class="sidebar-brand"><img src="../assets/images/logo.png" alt="LearnSpace"></div>
    <ul class="sidebar-nav">
      <li><a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
      <li><a href="manage-users.php"><i class="fas fa-users"></i> Manage Users</a></li>
      <li><a href="manage-instructors.php"><i class="fas fa-chalkboard-teacher"></i> Manage Instructors</a></li>
      <li><a href="courses.php"><i class="fas fa-book"></i> Courses</a></li>
      <li><a href="pending-courses.php"><i class="fas fa-clock"></i> Pending Courses</a></li>
      <li><a href="remove-courses.php"><i class="fas fa-trash-alt"></i> Remove Courses</a></li>
      <li><a href="contest.php" class="active"><i class="fas fa-trophy"></i> Arrange Contest</a></li>
      <li><a href="contest-result.php" id="nav-results"><i class="fas fa-medal"></i> Contest Results</a></li>
      <li><a href="manage-coding-problems.php"><i class="fas fa-code"></i> Manage Coding Problems</a></li>
      <li><a href="view-messages.php"><i class="fas fa-envelope"></i> View Messages</a></li>
      <li><a href="logout.php" style="margin-top:20px"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
    </ul>
  </aside>
  <div class="main-content">
    <div class="main-header">
      <h1><i class="fas fa-trophy" style="color:var(--primary)"></i> Arrange a Contest</h1>
      <button class="btn btn-primary" onclick="document.getElementById('createModal').classList.add('open')">
        <i class="fas fa-plus"></i> Create New Contest
      </button>
    </div>
    <div class="page-body">
      <?php if ($success): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= $success ?></div><?php endif; ?>
      <?php if ($error): ?><div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?= $error ?></div><?php endif; ?>

      <?php if (empty($contests)): ?>
      <div style="text-align:center;padding:60px;color:var(--gray)">
        <i class="fas fa-trophy" style="font-size:3rem;display:block;margin-bottom:16px;opacity:0.2"></i>
        <h3>No contests yet</h3>
        <p>Click "Create New Contest" to get started!</p>
      </div>
      <?php else: ?>
      <?php foreach ($contests as $c): ?>
      <div class="contest-card">
        <div class="contest-card-header">
          <div>
            <h3 style="margin-bottom:6px"><?= htmlspecialchars($c['title']) ?></h3>
            <p style="color:var(--gray);font-size:0.88rem"><?= htmlspecialchars($c['description'] ?? '') ?></p>
          </div>
          <span class="status-<?= $c['status'] ?>"><?= ucfirst($c['status']) ?></span>
        </div>
        <div style="display:flex;gap:16px;font-size:0.85rem;color:var(--gray);margin-bottom:16px">
          <span><i class="fas fa-users"></i> <?= $c['participant_count'] ?> participants</span>
          <span><i class="fas fa-calendar"></i> Created <?= date('M d, Y', strtotime($c['created_at'])) ?></span>
          <?php if ($c['started_at']): ?><span><i class="fas fa-play"></i> Started <?= date('M d H:i', strtotime($c['started_at'])) ?></span><?php endif; ?>
          <?php if ($c['ended_at']): ?><span><i class="fas fa-stop"></i> Ended <?= date('M d H:i', strtotime($c['ended_at'])) ?></span><?php endif; ?>
        </div>
        <div style="display:flex;gap:10px;flex-wrap:wrap">
          <?php if ($c['status'] === 'upcoming'): ?>
          <form method="POST" style="display:inline">
            <input type="hidden" name="contest_id" value="<?= $c['id'] ?>">
            <button type="submit" name="start_contest" class="btn btn-success btn-sm" onclick="return confirm('Start this contest now?')">
              <i class="fas fa-play"></i> Start Contest
            </button>
          </form>
          <?php endif; ?>
          <?php if ($c['status'] === 'active'): ?>
          <form method="POST" style="display:inline">
            <input type="hidden" name="contest_id" value="<?= $c['id'] ?>">
            <button type="submit" name="end_contest" class="btn btn-danger btn-sm" onclick="return confirm('End this contest now?')">
              <i class="fas fa-stop"></i> End Contest
            </button>
          </form>
          <?php endif; ?>
          <a href="contest-result.php?id=<?= $c['id'] ?>" class="btn btn-outline btn-sm">
            <i class="fas fa-medal"></i> View Results
          </a>
          <?php if ($c['status'] === 'upcoming'): ?>
          <form method="POST" style="display:inline" onsubmit="return confirm('Delete this contest permanently?')">
            <input type="hidden" name="contest_id" value="<?= $c['id'] ?>">
            <button type="submit" name="delete_contest" class="btn btn-danger btn-sm">
              <i class="fas fa-trash"></i> Delete
            </button>
          </form>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- CREATE CONTEST MODAL -->
<div class="modal-overlay" id="createModal">
  <div class="modal-box">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
      <h2><i class="fas fa-trophy" style="color:var(--primary)"></i> Create New Contest</h2>
      <button onclick="document.getElementById('createModal').classList.remove('open')" style="background:none;border:none;font-size:1.5rem;cursor:pointer;color:var(--gray)">&times;</button>
    </div>
    <form method="POST" id="contestForm">
      <div class="form-group">
        <label>Contest Title *</label>
        <input type="text" name="title" placeholder="e.g. Web Development Challenge 2025" required>
      </div>
      <div class="form-group">
        <label>Description</label>
        <textarea name="description" rows="2" placeholder="Brief description of the contest..."></textarea>
      </div>

      <hr style="margin:20px 0">
      <h3 style="margin-bottom:14px"><i class="fas fa-medal" style="color:#f59e0b"></i> Prizes (Optional)</h3>
      <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px;margin-bottom:4px">
        <div class="form-group">
          <label>🥇 1st Place</label>
          <input type="text" name="prizes[1]" placeholder="e.g. $100 Gift Card">
        </div>
        <div class="form-group">
          <label>🥈 2nd Place</label>
          <input type="text" name="prizes[2]" placeholder="e.g. $50 Gift Card">
        </div>
        <div class="form-group">
          <label>🥉 3rd Place</label>
          <input type="text" name="prizes[3]" placeholder="e.g. Certificate">
        </div>
      </div>

      <hr style="margin:20px 0">
      <h3 style="margin-bottom:16px"><i class="fas fa-question-circle" style="color:var(--primary)"></i> MCQ Questions</h3>

      <div id="questionsContainer">
        <!-- Questions will be added here -->
      </div>
      
      <button type="button" class="btn btn-outline btn-sm" onclick="addQuestion()" style="margin-top:10px;"><i class="fas fa-plus"></i> Add Question</button>

      <div style="display:flex;gap:12px;margin-top:20px">
        <button type="submit" name="create_contest" class="btn btn-primary"><i class="fas fa-save"></i> Create Contest</button>
        <button type="button" class="btn btn-outline" onclick="document.getElementById('createModal').classList.remove('open')">Cancel</button>
      </div>
    </form>
  </div>
</div>

<script>
let qCount = 0;
function addQuestion() {
  qCount++;
  const i = qCount;
  const div = document.createElement('div');
  div.className = 'question-block';
  div.id = 'q_' + i;
  div.innerHTML = `
    <button type="button" class="remove-q-btn" onclick="document.getElementById('q_${i}').remove()"><i class="fas fa-times-circle"></i></button>
    <div class="form-group">
      <label>Question ${i}</label>
      <input type="text" name="questions[${i}][question]" placeholder="Enter your question..." required>
      <input type="hidden" name="questions[${i}][order]" value="${i}">
    </div>
    <div class="options-grid" style="margin-bottom:10px">
      <div class="form-group" style="margin-bottom:0">
        <label>Option A *</label>
        <input type="text" name="questions[${i}][option_a]" placeholder="Option A" required>
      </div>
      <div class="form-group" style="margin-bottom:0">
        <label>Option B *</label>
        <input type="text" name="questions[${i}][option_b]" placeholder="Option B" required>
      </div>
      <div class="form-group" style="margin-bottom:0">
        <label>Option C</label>
        <input type="text" name="questions[${i}][option_c]" placeholder="Option C (optional)">
      </div>
      <div class="form-group" style="margin-bottom:0">
        <label>Option D</label>
        <input type="text" name="questions[${i}][option_d]" placeholder="Option D (optional)">
      </div>
    </div>
    <div class="form-group">
      <label>Correct Answer *</label>
      <select name="questions[${i}][correct_answer]" required>
        <option value="a">A</option>
        <option value="b">B</option>
        <option value="c">C</option>
        <option value="d">D</option>
      </select>
    </div>
  `;
  document.getElementById('questionsContainer').appendChild(div);
}
// Add one question by default
addQuestion();
</script>
<script src='https://cdn.jotfor.ms/agent/embedjs/019d85b564bd7b53bf17ecb93621ce83ef1b/embed.js'>
</script>
</body>
</html>
