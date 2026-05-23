<?php
session_start();
require_once '../includes/db.php';
if (!isset($_SESSION['admin_id'])) { header('Location: login.php'); exit; }

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_problem'])) {
        $title = trim($_POST['title']);
        $description = trim($_POST['description']);
        $language = trim($_POST['coding_language']);
        $boilerplate = trim($_POST['coding_boilerplate']);
        $expected_input = trim($_POST['coding_expected_input']);
        $expected = trim($_POST['coding_expected_output']);
        $difficulty = trim($_POST['difficulty']);

        if (!$title || !$description || !$expected) {
            $error = "Title, description, and expected output are required.";
        } else {
            $stmt = $conn->prepare("INSERT INTO coding_problems (title, description, coding_language, coding_boilerplate, coding_expected_input, coding_expected_output, difficulty) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssss", $title, $description, $language, $boilerplate, $expected_input, $expected, $difficulty);
            if ($stmt->execute()) {
                $success = "Coding problem created successfully!";
            } else {
                $error = "Error creating problem.";
            }
        }
    }

    if (isset($_POST['delete_problem'])) {
        $pid = intval($_POST['problem_id']);
        $conn->query("DELETE FROM coding_problems WHERE id=$pid");
        $success = "Coding problem deleted successfully!";
    }

    if (isset($_POST['edit_problem'])) {
        $pid = intval($_POST['problem_id']);
        $title = trim($_POST['title']);
        $description = trim($_POST['description']);
        $language = trim($_POST['coding_language']);
        $boilerplate = trim($_POST['coding_boilerplate']);
        $expected_input = trim($_POST['coding_expected_input']);
        $expected = trim($_POST['coding_expected_output']);
        $difficulty = trim($_POST['difficulty']);

        if (!$title || !$description || !$expected) {
            $error = "Title, description, and expected output are required.";
        } else {
            $stmt = $conn->prepare("UPDATE coding_problems SET title=?, description=?, coding_language=?, coding_boilerplate=?, coding_expected_input=?, coding_expected_output=?, difficulty=? WHERE id=?");
            $stmt->bind_param("sssssssi", $title, $description, $language, $boilerplate, $expected_input, $expected, $difficulty, $pid);
            if ($stmt->execute()) {
                $success = "Coding problem updated successfully!";
            } else {
                $error = "Error updating problem.";
            }
        }
    }
}

// Fetch all problems
$problems_q = $conn->query("SELECT p.*, (SELECT COUNT(*) FROM problem_completions WHERE problem_id=p.id AND status='solved') as solved_count FROM coding_problems p ORDER BY p.created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Coding Problems - Admin</title>
<link rel="stylesheet" href="../assets/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
.problem-card { background:white;border-radius:var(--radius);border:1px solid var(--gray-border);padding:20px;margin-bottom:16px; }
.problem-card-header { display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:12px; }
.difficulty-easy { background:#dcfce7;color:#16a34a;padding:4px 10px;border-radius:50px;font-size:0.78rem;font-weight:700; }
.difficulty-medium { background:#fef9c3;color:#ca8a04;padding:4px 10px;border-radius:50px;font-size:0.78rem;font-weight:700; }
.difficulty-hard { background:#fee2e2;color:#dc2626;padding:4px 10px;border-radius:50px;font-size:0.78rem;font-weight:700; }
.modal-overlay { display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:1000;align-items:center;justify-content:center; }
.modal-overlay.open { display:flex; }
.modal-box { background:white;border-radius:var(--radius);padding:32px;max-width:800px;width:95%;max-height:90vh;overflow-y:auto; }
.form-group { margin-bottom:16px; }
.form-group label { display:block;font-weight:700;font-size:0.88rem;margin-bottom:6px; }
.form-group input, .form-group textarea, .form-group select { width:100%;padding:10px 14px;border:1px solid var(--gray-border);border-radius:var(--radius-sm);font-size:0.9rem; font-family:inherit;}
.form-group textarea { font-family: monospace; }
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
      <li><a href="contest.php"><i class="fas fa-trophy"></i> Arrange Contest</a></li>
      <li><a href="contest-result.php"><i class="fas fa-medal"></i> Contest Results</a></li>
      <li><a href="manage-coding-problems.php" class="active"><i class="fas fa-code"></i> Manage Coding Problems</a></li>
      <li><a href="view-messages.php"><i class="fas fa-envelope"></i> View Messages</a></li>
      <li><a href="logout.php" style="margin-top:20px"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
    </ul>
  </aside>
  <div class="main-content">
    <div class="main-header">
      <h1><i class="fas fa-code" style="color:var(--primary)"></i> Manage Coding Problems</h1>
      <div>
        <a href="leaderboard.php" class="btn btn-outline" style="margin-right:10px;"><i class="fas fa-medal" style="color:#f59e0b"></i> View Leaderboard</a>
        <button class="btn btn-primary" onclick="document.getElementById('createModal').classList.add('open')">
          <i class="fas fa-plus"></i> Create New Problem
        </button>
      </div>
    </div>
    <div class="page-body">
      <?php if ($success): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= $success ?></div><?php endif; ?>
      <?php if ($error): ?><div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?= $error ?></div><?php endif; ?>

      <?php if ($problems_q->num_rows === 0): ?>
      <div style="text-align:center;padding:60px;color:var(--gray)">
        <i class="fas fa-code" style="font-size:3rem;display:block;margin-bottom:16px;opacity:0.2"></i>
        <h3>No coding problems yet</h3>
        <p>Click "Create New Problem" to get started!</p>
      </div>
      <?php else: ?>
      <?php while ($p = $problems_q->fetch_assoc()): ?>
      <div class="problem-card">
        <div class="problem-card-header">
          <div>
            <h3 style="margin-bottom:6px"><?= htmlspecialchars($p['title']) ?></h3>
            <p style="color:var(--gray);font-size:0.88rem"><?= mb_strimwidth(htmlspecialchars($p['description']), 0, 100, "...") ?></p>
          </div>
          <span class="difficulty-<?= $p['difficulty'] ?>"><?= ucfirst($p['difficulty']) ?></span>
        </div>
        <div style="display:flex;gap:16px;font-size:0.85rem;color:var(--gray);margin-bottom:16px">
          <span><i class="fas fa-check-circle"></i> <?= $p['solved_count'] ?> solved</span>
          <span><i class="fas fa-calendar"></i> Added <?= date('M d, Y', strtotime($p['created_at'])) ?></span>
        </div>
        <div style="display:flex;gap:10px;">
          <button class="btn btn-outline btn-sm" onclick='openEditModal(<?= htmlspecialchars(json_encode($p), ENT_QUOTES, "UTF-8") ?>)'>
            <i class="fas fa-edit"></i> Edit
          </button>
          <form method="POST" onsubmit="return confirm('Delete this problem permanently?')">
            <input type="hidden" name="problem_id" value="<?= $p['id'] ?>">
            <button type="submit" name="delete_problem" class="btn btn-danger btn-sm">
              <i class="fas fa-trash"></i> Delete
            </button>
          </form>
        </div>
      </div>
      <?php endwhile; ?>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- CREATE PROBLEM MODAL -->
<div class="modal-overlay" id="createModal">
  <div class="modal-box">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
      <h2><i class="fas fa-code" style="color:var(--primary)"></i> Create Coding Problem</h2>
      <button onclick="document.getElementById('createModal').classList.remove('open')" style="background:none;border:none;font-size:1.5rem;cursor:pointer;color:var(--gray)">&times;</button>
    </div>
    <form method="POST">
      <div class="form-group">
        <label>Problem Title *</label>
        <input type="text" name="title" placeholder="e.g. Sum of Two Numbers" required>
      </div>
      <div class="form-group">
        <label>Description *</label>
        <textarea name="description" rows="4" placeholder="Describe the problem, input format, and output format..." style="font-family:inherit" required></textarea>
      </div>
      
      <div style="display:flex; gap:16px;">
        <div class="form-group" style="flex:1;">
          <label>Language</label>
          <select name="coding_language">
            <option value="62">Java</option>
            <option value="54">C++</option>
            <option value="71">Python</option>
            <option value="93">JavaScript (Node.js)</option>
            <option value="68">PHP</option>
            <option value="50">C</option>
          </select>
        </div>
        <div class="form-group" style="flex:1;">
          <label>Difficulty</label>
          <select name="difficulty">
            <option value="easy">Easy</option>
            <option value="medium">Medium</option>
            <option value="hard">Hard</option>
          </select>
        </div>
      </div>

      <div class="form-group">
        <label>Initial Boilerplate Code (Optional)</label>
        <textarea name="coding_boilerplate" rows="6" placeholder="function solve() {&#10;  // write code here&#10;}"></textarea>
      </div>

      <div class="form-group">
        <label>Expected Standard Input (stdin) (Optional)</label>
        <textarea name="coding_expected_input" rows="3" placeholder="Input values separated by spaces or newlines"></textarea>
      </div>
      
      <div class="form-group">
        <label>Expected Final Output (Exact Match) *</label>
        <textarea name="coding_expected_output" rows="3" placeholder="Expected output string" required></textarea>
      </div>

      <div style="display:flex;gap:12px;margin-top:20px">
        <button type="submit" name="create_problem" class="btn btn-primary"><i class="fas fa-save"></i> Create Problem</button>
        <button type="button" class="btn btn-outline" onclick="document.getElementById('createModal').classList.remove('open')">Cancel</button>
      </div>
    </form>
  </div>
</div>

<!-- EDIT PROBLEM MODAL -->
<div class="modal-overlay" id="editModal">
  <div class="modal-box">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
      <h2><i class="fas fa-edit" style="color:var(--primary)"></i> Edit Coding Problem</h2>
      <button onclick="document.getElementById('editModal').classList.remove('open')" style="background:none;border:none;font-size:1.5rem;cursor:pointer;color:var(--gray)">&times;</button>
    </div>
    <form method="POST">
      <input type="hidden" name="problem_id" id="edit_pid">
      <div class="form-group">
        <label>Problem Title *</label>
        <input type="text" name="title" id="edit_title" required>
      </div>
      <div class="form-group">
        <label>Description *</label>
        <textarea name="description" id="edit_desc" rows="4" style="font-family:inherit" required></textarea>
      </div>
      
      <div style="display:flex; gap:16px;">
        <div class="form-group" style="flex:1;">
          <label>Language</label>
          <select name="coding_language" id="edit_lang">
            <option value="62">Java</option>
            <option value="54">C++</option>
            <option value="71">Python</option>
            <option value="93">JavaScript (Node.js)</option>
            <option value="68">PHP</option>
            <option value="50">C</option>
          </select>
        </div>
        <div class="form-group" style="flex:1;">
          <label>Difficulty</label>
          <select name="difficulty" id="edit_diff">
            <option value="easy">Easy</option>
            <option value="medium">Medium</option>
            <option value="hard">Hard</option>
          </select>
        </div>
      </div>

      <div class="form-group">
        <label>Initial Boilerplate Code (Optional)</label>
        <textarea name="coding_boilerplate" id="edit_boiler" rows="6"></textarea>
      </div>

      <div class="form-group">
        <label>Expected Standard Input (stdin) (Optional)</label>
        <textarea name="coding_expected_input" id="edit_input" rows="3"></textarea>
      </div>
      
      <div class="form-group">
        <label>Expected Final Output (Exact Match) *</label>
        <textarea name="coding_expected_output" id="edit_output" rows="3" required></textarea>
      </div>

      <div style="display:flex;gap:12px;margin-top:20px">
        <button type="submit" name="edit_problem" class="btn btn-primary"><i class="fas fa-save"></i> Save Changes</button>
        <button type="button" class="btn btn-outline" onclick="document.getElementById('editModal').classList.remove('open')">Cancel</button>
      </div>
    </form>
  </div>
</div>

<script>
function openEditModal(data) {
    document.getElementById('edit_pid').value = data.id;
    document.getElementById('edit_title').value = data.title;
    document.getElementById('edit_desc').value = data.description;
    
    // Select correct language
    let langSelect = document.getElementById('edit_lang');
    for(let i=0; i<langSelect.options.length; i++) {
        if(langSelect.options[i].value == data.coding_language) {
            langSelect.selectedIndex = i;
            break;
        }
    }
    
    // Select correct difficulty
    let diffSelect = document.getElementById('edit_diff');
    for(let i=0; i<diffSelect.options.length; i++) {
        if(diffSelect.options[i].value == data.difficulty) {
            diffSelect.selectedIndex = i;
            break;
        }
    }
    
    document.getElementById('edit_boiler').value = data.coding_boilerplate || '';
    document.getElementById('edit_input').value = data.coding_expected_input || '';
    document.getElementById('edit_output').value = data.coding_expected_output || '';
    
    document.getElementById('editModal').classList.add('open');
}
</script>
<script src='https://cdn.jotfor.ms/agent/embedjs/019d85b564bd7b53bf17ecb93621ce83ef1b/embed.js'>
</script>
</body>
</html>
