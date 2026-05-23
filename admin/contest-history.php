<?php
session_start();
require_once '../includes/db.php';
if (!isset($_SESSION['admin_id'])) { header('Location: login.php'); exit; }

$contest_id = intval($_GET['id'] ?? 0);
if (!$contest_id) { header('Location: contest.php'); exit; }

$contest = $conn->query("SELECT * FROM contests WHERE id=$contest_id")->fetch_assoc();
if (!$contest) { header('Location: contest.php'); exit; }

// Questions
$questions_q = $conn->query("SELECT * FROM contest_questions WHERE contest_id=$contest_id ORDER BY question_order");
$questions = [];
while ($q = $questions_q->fetch_assoc()) $questions[] = $q;

// Leaderboard
$leaders_q = $conn->query("SELECT cp.*, u.full_name, u.email FROM contest_participants cp 
    JOIN users u ON cp.user_id = u.id 
    WHERE cp.contest_id=$contest_id 
    ORDER BY cp.score DESC, cp.submitted_at ASC");
$leaders = [];
while ($r = $leaders_q->fetch_assoc()) $leaders[] = $r;

// All contests list for dropdown
$all_contests = $conn->query("SELECT id, title, status FROM contests ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Contest Results - Admin</title>
<link rel="stylesheet" href="../assets/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
.leaderboard-rank { font-size:1.5rem;width:50px;text-align:center; }
.rank-1 { color:#f59e0b; }
.rank-2 { color:#6b7280; }
.rank-3 { color:#b45309; }
.score-bar-wrap { background:#f3f4f6;border-radius:50px;height:8px;flex:1;overflow:hidden; }
.score-bar-fill { height:8px;border-radius:50px;background:linear-gradient(90deg,var(--primary),#ff6b6b); }
.contest-selector { padding:10px 14px;border:1px solid var(--gray-border);border-radius:var(--radius-sm);font-size:0.9rem;min-width:280px; }
</style>
</head>
<body>
<div class="dashboard-layout">
  <aside class="sidebar">
    <div class="sidebar-brand"><img src="../assets/images/logo.png" alt="LearnSpace" ></div>
    <ul class="sidebar-nav">
      <li><a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
      <li><a href="manage-users.php"><i class="fas fa-users"></i> Manage Users</a></li>
      <li><a href="manage-instructors.php"><i class="fas fa-chalkboard-teacher"></i> Manage Instructors</a></li>
      <li><a href="courses.php"><i class="fas fa-book"></i> Courses</a></li>
      <li><a href="pending-courses.php"><i class="fas fa-clock"></i> Pending Courses</a></li>
      <li><a href="remove-courses.php"><i class="fas fa-trash-alt"></i> Remove Courses</a></li>
      <li><a href="contest.php" class="active"><i class="fas fa-trophy"></i> Arrange Contest</a></li>
      <li><a href="contest-result.php"><i class="fas fa-medal"></i> Contest Results</a></li>
      <li><a href="manage-coding-problems.php"><i class="fas fa-code"></i> Manage Coding Problems</a></li>
      <li><a href="view-messages.php"><i class="fas fa-envelope"></i> View Messages</a></li>
      <li><a href="logout.php" style="margin-top:20px"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
    </ul>
  </aside>
  <div class="main-content">
    <div class="main-header">
      <div>
        <a href="contest.php" style="color:var(--gray);font-size:0.88rem;display:flex;align-items:center;gap:6px;margin-bottom:8px">
          <i class="fas fa-arrow-left"></i> Back to Contests
        </a>
        <h1><i class="fas fa-chart-bar" style="color:var(--primary)"></i> Contest History & Leaderboard</h1>
      </div>
      <div>
        <select class="contest-selector" onchange="location.href='contest-history.php?id='+this.value">
          <?php while ($ac = $all_contests->fetch_assoc()): ?>
          <option value="<?= $ac['id'] ?>" <?= $ac['id']==$contest_id?'selected':'' ?>>
            <?= htmlspecialchars($ac['title']) ?> (<?= ucfirst($ac['status']) ?>)
          </option>
          <?php endwhile; ?>
        </select>
      </div>
    </div>
    <div class="page-body">

      <!-- Contest Info -->
      <div class="card" style="padding:20px;margin-bottom:24px;display:flex;gap:24px;flex-wrap:wrap;align-items:center">
        <div>
          <div style="font-size:0.78rem;color:var(--gray);font-weight:700;text-transform:uppercase">Contest</div>
          <div style="font-size:1.2rem;font-weight:800"><?= htmlspecialchars($contest['title']) ?></div>
          <?php if ($contest['description']): ?>
          <div style="color:var(--gray);font-size:0.88rem"><?= htmlspecialchars($contest['description']) ?></div>
          <?php endif; ?>
        </div>
        <div style="margin-left:auto;display:flex;gap:32px;text-align:center">
          <div>
            <div style="font-size:2rem;font-weight:900;color:var(--primary)"><?= count($leaders) ?></div>
            <div style="font-size:0.78rem;color:var(--gray)">Participants</div>
          </div>
          <div>
            <div style="font-size:2rem;font-weight:900;color:var(--primary)"><?= count($questions) ?></div>
            <div style="font-size:0.78rem;color:var(--gray)">Questions</div>
          </div>
          <div>
            <div style="font-size:1rem;font-weight:700;margin-top:6px">
              <span class="badge <?= $contest['status']==='active'?'badge-approved':($contest['status']==='ended'?'badge-rejected':'badge-pending') ?>">
                <?= ucfirst($contest['status']) ?>
              </span>
            </div>
          </div>
        </div>
      </div>

      <?php if (empty($leaders)): ?>
      <div style="text-align:center;padding:60px;color:var(--gray)">
        <i class="fas fa-users" style="font-size:3rem;display:block;margin-bottom:16px;opacity:0.2"></i>
        <h3>No participants yet</h3>
        <p>Leaderboard will appear once learners participate.</p>
      </div>
      <?php else: ?>
      <!-- Top 3 Podium -->
      <?php if (count($leaders) >= 3): ?>
      <div style="display:flex;justify-content:center;gap:20px;margin-bottom:32px;align-items:flex-end">
        <?php $podium = [$leaders[1] ?? null, $leaders[0] ?? null, $leaders[2] ?? null];
        $heights = ['120px','150px','100px'];
        $medals = ['🥈','🥇','🥉'];
        foreach ([$podium[0],$podium[1],$podium[2]] as $pi => $pl): if (!$pl) continue; ?>
        <div style="text-align:center">
          <div style="font-size:2rem;margin-bottom:8px"><?= $medals[$pi] ?></div>
          <div style="font-weight:700;font-size:0.9rem"><?= htmlspecialchars($pl['full_name']) ?></div>
          <div style="color:var(--primary);font-weight:800"><?= $pl['score'] ?>/<?= $pl['total'] ?></div>
          <div style="background:var(--primary);border-radius:var(--radius-sm) var(--radius-sm) 0 0;height:<?= $heights[$pi] ?>;width:100px;margin-top:8px;opacity:<?= $pi==1?'1':'0.7' ?>"></div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

      <!-- Full Leaderboard -->
      <div class="card">
        <div class="table-wrapper">
          <table>
            <thead>
              <tr><th>Rank</th><th>Learner</th><th>Email</th><th>Score</th><th>Progress</th><th>Submitted</th></tr>
            </thead>
            <tbody>
              <?php foreach ($leaders as $rank => $l): ?>
              <tr>
                <td>
                  <span class="leaderboard-rank <?= $rank===0?'rank-1':($rank===1?'rank-2':($rank===2?'rank-3':'')) ?>">
                    <?php if ($rank < 3) echo ['🥇','🥈','🥉'][$rank]; else echo '#'.($rank+1); ?>
                  </span>
                </td>
                <td><strong><?= htmlspecialchars($l['full_name']) ?></strong></td>
                <td style="color:var(--gray);font-size:0.85rem"><?= htmlspecialchars($l['email']) ?></td>
                <td><strong><?= $l['score'] ?></strong> / <?= $l['total'] ?> <small style="color:var(--gray)">(<?= number_format($l['percentage'],1) ?>%)</small></td>
                <td style="min-width:120px">
                  <div style="display:flex;align-items:center;gap:8px">
                    <div class="score-bar-wrap"><div class="score-bar-fill" style="width:<?= $l['percentage'] ?>%"></div></div>
                  </div>
                </td>
                <td style="font-size:0.82rem;color:var(--gray)"><?= date('M d, H:i', strtotime($l['submitted_at'])) ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
      <?php endif; ?>

      <!-- Questions Review -->
      <?php if (!empty($questions)): ?>
      <div class="section-title" style="margin-top:32px"><i class="fas fa-question-circle" style="color:var(--primary)"></i> Questions (<?= count($questions) ?>)</div>
      <?php foreach ($questions as $qi => $q): ?>
      <div class="card" style="padding:16px;margin-bottom:12px">
        <div style="font-weight:700;margin-bottom:10px">Q<?= $qi+1 ?>. <?= htmlspecialchars($q['question']) ?></div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px">
          <?php foreach (['a','b','c','d'] as $opt):
            $optval = $q['option_'.$opt];
            if (!$optval) continue;
            $is_correct = $q['correct_answer'] === $opt;
          ?>
          <div style="padding:8px 12px;border-radius:var(--radius-sm);border:1px solid <?= $is_correct ? 'var(--success)' : 'var(--gray-border)' ?>;background:<?= $is_correct ? '#f0fdf4' : 'white' ?>">
            <strong style="color:<?= $is_correct ? 'var(--success)' : 'var(--gray)' ?>"><?= strtoupper($opt) ?>.</strong>
            <?= htmlspecialchars($optval) ?>
            <?php if ($is_correct): ?> <i class="fas fa-check" style="color:var(--success);margin-left:6px"></i><?php endif; ?>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endforeach; ?>
      <?php endif; ?>

    </div>
  </div>
</div>
<script src='https://cdn.jotfor.ms/agent/embedjs/019d85b564bd7b53bf17ecb93621ce83ef1b/embed.js'>
</script>
</body>
</html>
