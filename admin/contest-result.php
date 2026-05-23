<?php
session_start();
require_once '../includes/db.php';
if (!isset($_SESSION['admin_id'])) { header('Location: login.php'); exit; }

// Get all contests for the selector
$all_contests_q = $conn->query("SELECT id, title, status FROM contests ORDER BY created_at DESC");
$all_contests = [];
while ($r = $all_contests_q->fetch_assoc()) $all_contests[] = $r;

// Default to first ended/active contest, or whatever is selected
$contest_id = intval($_GET['id'] ?? ($all_contests[0]['id'] ?? 0));

$contest = null;
$leaders = [];
$prizes = [];
$questions = [];

if ($contest_id) {
    $contest = $conn->query("SELECT * FROM contests WHERE id=$contest_id")->fetch_assoc();

    if ($contest) {
        // Prizes
        $pq = $conn->query("SELECT * FROM contest_prizes WHERE contest_id=$contest_id ORDER BY position ASC");
        while ($p = $pq->fetch_assoc()) $prizes[$p['position']] = $p['prize_label'];

        // Leaderboard with user details
        $lq = $conn->query("SELECT cp.*, u.full_name, u.email 
            FROM contest_participants cp 
            JOIN users u ON cp.user_id = u.id 
            WHERE cp.contest_id=$contest_id 
            ORDER BY cp.score DESC, cp.submitted_at ASC");
        $rank = 0;
        while ($r = $lq->fetch_assoc()) {
            $rank++;
            $r['rank'] = $rank;
            $r['prize'] = $prizes[$rank] ?? null;
            $leaders[] = $r;
        }

        // Questions count
        $q_count = $conn->query("SELECT COUNT(*) as c FROM contest_questions WHERE contest_id=$contest_id")->fetch_assoc()['c'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Contest Results - Admin</title>
<link rel="stylesheet" href="../assets/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
.contest-selector { padding:10px 14px;border:1px solid var(--gray-border);border-radius:var(--radius-sm);font-size:0.9rem;min-width:300px;outline:none; }
.contest-selector:focus { border-color:var(--primary); }
.stat-card { background:white;border-radius:var(--radius);border:1px solid var(--gray-border);padding:20px;text-align:center;flex:1;min-width:120px; }
.stat-card .num { font-size:2.2rem;font-weight:900;color:var(--primary); }
.stat-card .lbl { font-size:0.78rem;color:var(--gray);text-transform:uppercase;letter-spacing:0.05em;margin-top:4px; }
.prize-podium { display:flex;justify-content:center;align-items:flex-end;gap:20px;margin:28px 0; }
.podium-block { text-align:center; }
.podium-stand { border-radius:var(--radius-sm) var(--radius-sm) 0 0;width:120px;margin-top:10px; }
.podium-name { font-weight:700;font-size:0.88rem;max-width:120px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap; }
.podium-score { color:var(--primary);font-weight:800;font-size:0.92rem; }
.podium-prize { font-size:0.78rem;color:#7c3aed;background:#ede9fe;padding:3px 8px;border-radius:50px;margin-top:4px;display:inline-block; }
.result-table th { background:#f9fafb;font-size:0.8rem;text-transform:uppercase;letter-spacing:0.05em;color:var(--gray); }
.result-table td { vertical-align:middle; }
.rank-cell { font-size:1.3rem;text-align:center;width:60px; }
.score-bar-wrap { background:#f3f4f6;border-radius:50px;height:8px;flex:1;overflow:hidden;min-width:80px; }
.score-bar-fill { height:8px;border-radius:50px;background:linear-gradient(90deg,var(--primary),#ff6b6b); }
.prize-tag { font-size:0.75rem;background:#fef3c7;color:#92400e;padding:3px 8px;border-radius:50px;border:1px solid #fde68a;white-space:nowrap; }
.status-active { background:#dcfce7;color:#16a34a;padding:4px 10px;border-radius:50px;font-size:0.78rem;font-weight:700; }
.status-upcoming { background:#fef9c3;color:#ca8a04;padding:4px 10px;border-radius:50px;font-size:0.78rem;font-weight:700; }
.status-ended { background:#f3f4f6;color:#6b7280;padding:4px 10px;border-radius:50px;font-size:0.78rem;font-weight:700; }
.empty-state { text-align:center;padding:60px;color:var(--gray); }
.empty-state i { font-size:3rem;display:block;margin-bottom:16px;opacity:0.2; }
@media print {
  .sidebar, .main-header, .no-print { display:none!important; }
  .main-content { margin:0!important;padding:0!important; }
  .result-table th, .result-table td { font-size:0.82rem; }
}
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
      <li><a href="contest-result.php" class="active"><i class="fas fa-medal"></i> Contest Results</a></li>
      <li><a href="manage-coding-problems.php"><i class="fas fa-code"></i> Manage Coding Problems</a></li>
      <li><a href="view-messages.php"><i class="fas fa-envelope"></i> View Messages</a></li>
      <li><a href="logout.php" style="margin-top:20px"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
    </ul>
  </aside>

  <div class="main-content">
    <div class="main-header no-print">
      <div>
        <h1><i class="fas fa-medal" style="color:var(--primary)"></i> Contest Results</h1>
        <p style="color:var(--gray);font-size:0.88rem;margin-top:4px">Full leaderboard with student details, marks, positions &amp; prizes</p>
      </div>
      <div style="display:flex;gap:10px;align-items:center">
        <?php if (!empty($all_contests)): ?>
        <select class="contest-selector" onchange="location.href='contest-result.php?id='+this.value">
          <option value="">— Select a Contest —</option>
          <?php foreach ($all_contests as $ac): ?>
          <option value="<?= $ac['id'] ?>" <?= $ac['id']==$contest_id ? 'selected' : '' ?>>
            <?= htmlspecialchars($ac['title']) ?> (<?= ucfirst($ac['status']) ?>)
          </option>
          <?php endforeach; ?>
        </select>
        <?php endif; ?>
        <?php if ($contest): ?>
        <button class="btn btn-outline btn-sm no-print" onclick="window.print()">
          <i class="fas fa-print"></i> Print
        </button>
        <?php endif; ?>
      </div>
    </div>

    <div class="page-body">

      <?php if (empty($all_contests)): ?>
      <div class="empty-state">
        <i class="fas fa-trophy"></i>
        <h3>No Contests Yet</h3>
        <p>Create a contest first from the <a href="contest.php">Arrange Contest</a> page.</p>
      </div>

      <?php elseif (!$contest): ?>
      <div class="empty-state">
        <i class="fas fa-mouse-pointer"></i>
        <h3>Select a Contest</h3>
        <p>Choose a contest from the dropdown above to view its results.</p>
      </div>

      <?php else: ?>

      <!-- Contest Banner -->
      <div style="background:linear-gradient(135deg,var(--primary),#ff6b6b);border-radius:var(--radius);padding:24px;color:white;margin-bottom:24px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:16px">
        <div>
          <div style="font-size:0.78rem;opacity:0.8;text-transform:uppercase;letter-spacing:0.08em;margin-bottom:4px">Contest Results</div>
          <h2 style="margin:0;font-size:1.6rem"><?= htmlspecialchars($contest['title']) ?></h2>
          <?php if ($contest['description']): ?>
          <p style="opacity:0.85;margin-top:6px;font-size:0.9rem"><?= htmlspecialchars($contest['description']) ?></p>
          <?php endif; ?>
          <div style="display:flex;gap:16px;margin-top:10px;font-size:0.83rem;opacity:0.9">
            <?php if ($contest['started_at']): ?>
            <span><i class="fas fa-play"></i> <?= date('M d, Y H:i', strtotime($contest['started_at'])) ?></span>
            <?php endif; ?>
            <?php if ($contest['ended_at']): ?>
            <span><i class="fas fa-stop"></i> <?= date('M d, Y H:i', strtotime($contest['ended_at'])) ?></span>
            <?php endif; ?>
          </div>
        </div>
        <span class="status-<?= $contest['status'] ?>" style="align-self:flex-start"><?= ucfirst($contest['status']) ?></span>
      </div>

      <!-- Stats Row -->
      <div style="display:flex;gap:16px;flex-wrap:wrap;margin-bottom:24px">
        <div class="stat-card">
          <div class="num"><?= count($leaders) ?></div>
          <div class="lbl"><i class="fas fa-users"></i> Participants</div>
        </div>
        <div class="stat-card">
          <div class="num"><?= $q_count ?></div>
          <div class="lbl"><i class="fas fa-question-circle"></i> Questions</div>
        </div>
        <?php if (!empty($leaders)): ?>
        <div class="stat-card">
          <div class="num"><?= $leaders[0]['score'] ?>/<?= $leaders[0]['total'] ?></div>
          <div class="lbl"><i class="fas fa-crown"></i> Top Score</div>
        </div>
        <div class="stat-card">
          <div class="num"><?= number_format(array_sum(array_column($leaders,'score')) / count($leaders), 1) ?></div>
          <div class="lbl"><i class="fas fa-chart-bar"></i> Avg Score</div>
        </div>
        <?php endif; ?>
        <?php if (!empty($prizes)): ?>
        <div class="stat-card">
          <div class="num" style="font-size:1.4rem">🏆</div>
          <div class="lbl">Prizes Set</div>
        </div>
        <?php endif; ?>
      </div>

      <?php if (!empty($leaders)): ?>

      <!-- Prizes Display -->
      <?php if (!empty($prizes)): ?>
      <div style="background:white;border:1px solid var(--gray-border);border-radius:var(--radius);padding:24px;margin-bottom:24px">
        <h3 style="margin-bottom:18px"><i class="fas fa-gift" style="color:#f59e0b"></i> Prize Distribution</h3>
        <div style="display:flex;gap:16px;flex-wrap:wrap">
          <?php
          $medals = [1=>'🥇',2=>'🥈',3=>'🥉'];
          $prize_colors = [1=>'#fef3c7',2=>'#f3f4f6',3=>'#fef3c7'];
          foreach ($prizes as $pos => $label):
            $winner = $leaders[$pos-1] ?? null;
          ?>
          <div style="flex:1;min-width:180px;background:<?= $prize_colors[$pos] ?? '#f9fafb' ?>;border-radius:var(--radius-sm);padding:16px;border:1px solid <?= $pos===1?'#fde68a':($pos===2?'#e5e7eb':'#fde68a') ?>">
            <div style="font-size:1.8rem;margin-bottom:6px"><?= $medals[$pos] ?? '#'.$pos ?></div>
            <div style="font-weight:800;font-size:0.92rem;color:<?= $pos===1?'#92400e':($pos===2?'#374151':'#92400e') ?>"><?= htmlspecialchars($label) ?></div>
            <?php if ($winner): ?>
            <div style="margin-top:8px;font-size:0.82rem;color:var(--gray)">
              <i class="fas fa-user"></i> <?= htmlspecialchars($winner['full_name']) ?>
            </div>
            <?php else: ?>
            <div style="margin-top:8px;font-size:0.78rem;color:var(--gray);font-style:italic">No winner yet</div>
            <?php endif; ?>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>

      <!-- Top 3 Podium -->
      <?php if (count($leaders) >= 2): ?>
      <div style="background:white;border:1px solid var(--gray-border);border-radius:var(--radius);padding:24px;margin-bottom:24px;text-align:center">
        <h3 style="margin-bottom:4px"><i class="fas fa-trophy" style="color:#f59e0b"></i> Top Performers</h3>
        <div class="prize-podium">
          <?php
          $podium_order = [1, 0, 2]; // 2nd, 1st, 3rd
          $heights = ['100px', '140px', '80px'];
          $bg_colors = ['#9ca3af','#f59e0b','#b45309'];
          $medal_icons = ['🥈','🥇','🥉'];
          foreach ($podium_order as $pi => $idx):
            if (!isset($leaders[$idx])) continue;
            $pl = $leaders[$idx];
          ?>
          <div class="podium-block">
            <div style="font-size:2rem;margin-bottom:4px"><?= $medal_icons[$pi] ?></div>
            <div class="podium-name"><?= htmlspecialchars($pl['full_name']) ?></div>
            <div style="font-size:0.75rem;color:var(--gray);margin-bottom:4px"><?= htmlspecialchars($pl['email']) ?></div>
            <div class="podium-score"><?= $pl['score'] ?>/<?= $pl['total'] ?> (<?= number_format($pl['percentage'],1) ?>%)</div>
            <?php if (isset($prizes[$idx+1])): ?>
            <div class="podium-prize"><i class="fas fa-gift"></i> <?= htmlspecialchars($prizes[$idx+1]) ?></div>
            <?php endif; ?>
            <div class="podium-stand" style="background:<?= $bg_colors[$pi] ?>;height:<?= $heights[$pi] ?>"></div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>

      <!-- Full Results Table -->
      <div style="background:white;border:1px solid var(--gray-border);border-radius:var(--radius);overflow:hidden;margin-bottom:24px">
        <div style="padding:18px 20px;border-bottom:1px solid var(--gray-border);display:flex;justify-content:space-between;align-items:center">
          <h3 style="margin:0"><i class="fas fa-list-ol" style="color:var(--primary)"></i> Full Results &amp; Rankings</h3>
          <span style="font-size:0.83rem;color:var(--gray)"><?= count($leaders) ?> participant<?= count($leaders)!==1?'s':'' ?></span>
        </div>
        <div class="table-wrapper">
          <table class="result-table">
            <thead>
              <tr>
                <th style="width:60px;text-align:center">Rank</th>
                <th>Student Name</th>
                <th>Email</th>
                <th>Marks Acquired</th>
                <th>Percentage</th>
                <th>Progress</th>
                <th>Prize</th>
                <th>Submitted</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($leaders as $l): ?>
              <tr style="<?= $l['rank']===1 ? 'background:#fffbeb;' : ($l['rank']===2 ? 'background:#f9fafb;' : '') ?>">
                <td class="rank-cell">
                  <?php
                  if ($l['rank']===1) echo '🥇';
                  elseif ($l['rank']===2) echo '🥈';
                  elseif ($l['rank']===3) echo '🥉';
                  else echo '<strong style="color:var(--gray)">#'.$l['rank'].'</strong>';
                  ?>
                </td>
                <td>
                  <div style="font-weight:700"><?= htmlspecialchars($l['full_name']) ?></div>
                </td>
                <td style="font-size:0.85rem;color:var(--gray)"><?= htmlspecialchars($l['email']) ?></td>
                <td>
                  <strong style="font-size:1rem"><?= $l['score'] ?></strong>
                  <span style="color:var(--gray);font-size:0.85rem"> / <?= $l['total'] ?></span>
                </td>
                <td>
                  <strong style="color:<?= $l['percentage']>=70?'#16a34a':($l['percentage']>=40?'#ca8a04':'#dc2626') ?>">
                    <?= number_format($l['percentage'],1) ?>%
                  </strong>
                </td>
                <td style="min-width:100px">
                  <div style="display:flex;align-items:center;gap:8px">
                    <div class="score-bar-wrap">
                      <div class="score-bar-fill" style="width:<?= $l['percentage'] ?>%"></div>
                    </div>
                  </div>
                </td>
                <td>
                  <?php if ($l['prize']): ?>
                  <span class="prize-tag"><i class="fas fa-gift"></i> <?= htmlspecialchars($l['prize']) ?></span>
                  <?php else: ?>
                  <span style="color:var(--gray);font-size:0.78rem">—</span>
                  <?php endif; ?>
                </td>
                <td style="font-size:0.8rem;color:var(--gray)"><?= date('M d, Y H:i', strtotime($l['submitted_at'])) ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

      <?php else: ?>
      <div class="empty-state">
        <i class="fas fa-users"></i>
        <h3>No Participants Yet</h3>
        <p>Results will appear here once learners participate in this contest.</p>
        <?php if ($contest['status'] === 'upcoming'): ?>
        <p style="margin-top:8px"><a href="contest.php" class="btn btn-outline btn-sm"><i class="fas fa-play"></i> Start Contest</a></p>
        <?php endif; ?>
      </div>
      <?php endif; ?>

      <?php endif; ?>

    </div>
  </div>
</div>
<script src='https://cdn.jotfor.ms/agent/embedjs/019d85b564bd7b53bf17ecb93621ce83ef1b/embed.js'>
</script>
</body>
</html>
