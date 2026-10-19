<?php
session_start();
include 'config/db.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$msg = '';
$msg_type = 'success';

// ===== ADD TIP =====
if (isset($_POST['add_tip'])) {
    $tipster_id = (int)$_POST['tipster_id'];
    $match_name = trim($_POST['match_name']);
    $league = trim($_POST['league']);
    $tip = trim($_POST['tip']);
    $odds = !empty($_POST['odds']) ? (float)$_POST['odds'] : null;
    $bet_link = trim($_POST['bet_link']);
    $match_date = !empty($_POST['match_date']) ? $_POST['match_date'] : null;
    $status = $_POST['status'] ?? 'pending';

    if (empty($tipster_id) || empty($match_name) || empty($tip)) {
        $msg = 'Tipster, match and tip are required.';
        $msg_type = 'error';
    } else {
        $stmt = $conn->prepare("INSERT INTO tips (tipster_id, match_name, league, tip, odds, bet_link, match_date, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssdsss", $tipster_id, $match_name, $league, $tip, $odds, $bet_link, $match_date, $status);
        if ($stmt->execute()) {
            $conn->query("UPDATE tipsters SET total_tips = total_tips + 1 WHERE id = $tipster_id");
            $msg = '✅ Tip added successfully!';
        } else {
            $msg = 'Error: ' . $conn->error;
            $msg_type = 'error';
        }
    }
}

// ===== DELETE =====
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM tips WHERE id = $id");
    header('Location: tips.php'); exit;
}

// ===== WON =====
if (isset($_GET['won'])) {
    $id = (int)$_GET['won'];
    $conn->query("UPDATE tips SET status = 'won' WHERE id = $id");
    $conn->query("UPDATE tipsters t SET total_wins = total_wins + 1 WHERE id = (SELECT tipster_id FROM tips WHERE id = $id)");
    header('Location: tips.php'); exit;
}

// ===== LOST =====
if (isset($_GET['lost'])) {
    $id = (int)$_GET['lost'];
    $conn->query("UPDATE tips SET status = 'lost' WHERE id = $id");
    header('Location: tips.php'); exit;
}

$tipsters = [];
$res = $conn->query("SELECT * FROM tipsters ORDER BY name ASC");
while ($row = $res->fetch_assoc()) $tipsters[] = $row;

$tips = [];
$res = $conn->query("SELECT t.*, tp.name AS tipster_name, tp.color AS tipster_color FROM tips t LEFT JOIN tipsters tp ON t.tipster_id = tp.id ORDER BY t.created_at DESC");
while ($row = $res->fetch_assoc()) $tips[] = $row;

$admin_name = $_SESSION['admin_username'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MILLZ ADMIN - Manage Tips</title>
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;700;900&family=Rajdhani:wght@400;500;600;700&family=Bungee&display=swap" rel="stylesheet">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
:root { --bg: #000; --card: #0f0f0f; --border: #2a2a2a; --red: #ff0033; --red-light: #ff3366; --red-dark: #cc0029; --green: #00cc66; --text: #fff; --muted: #888; }
body { font-family: 'Rajdhani', sans-serif; background: var(--bg); color: var(--text); min-height: 100vh; }
@keyframes gradientShift { 0% { background-position: 0% 50%; } 50% { background-position: 100% 50%; } 100% { background-position: 0% 50%; } }

.top-bar { background: linear-gradient(90deg, #0f0f0f, #000); border-bottom: 1px solid var(--border); padding: 15px 25px; display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 100; }
.top-bar .logo { font-family: 'Bungee', cursive; font-size: 20px; background: linear-gradient(90deg, #ff0033, #00cc66, #ff0033); background-size: 300% 100%; animation: gradientShift 4s ease infinite; -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; letter-spacing: 2px; }
.top-bar .user-info { display: flex; align-items: center; gap: 12px; font-size: 13px; color: var(--muted); }
.top-bar .user-info strong { color: var(--green); }
.back-btn, .logout-btn { padding: 7px 14px; background: transparent; border: 2px solid var(--red); color: var(--red); border-radius: 8px; font-family: 'Orbitron', sans-serif; font-size: 10px; font-weight: 900; letter-spacing: 1px; text-decoration: none; transition: 0.3s; }
.back-btn { border-color: var(--green); color: var(--green); }
.back-btn:hover { background: var(--green); color: #000; }
.logout-btn:hover { background: var(--red); color: #fff; }

.container { max-width: 1200px; margin: 0 auto; padding: 30px 20px; }
.page-title { font-family: 'Orbitron', sans-serif; font-size: 24px; margin-bottom: 6px; color: #fff; }
.page-sub { color: var(--muted); font-size: 14px; margin-bottom: 30px; }

.alert { padding: 14px 20px; border-radius: 10px; margin-bottom: 20px; font-weight: 600; font-size: 14px; }
.alert.success { background: rgba(0,204,102,0.15); border: 1px solid var(--green); color: var(--green); }
.alert.error { background: rgba(255,0,51,0.15); border: 1px solid var(--red); color: var(--red-light); }

.form-card { background: var(--card); border: 1px solid var(--border); border-radius: 16px; padding: 28px; margin-bottom: 30px; position: relative; overflow: hidden; }
.form-card::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px; background: linear-gradient(90deg, var(--red), var(--green), var(--red)); background-size: 200% 100%; animation: gradientShift 4s ease infinite; }
.form-title { font-family: 'Orbitron', sans-serif; font-size: 16px; margin-bottom: 20px; color: var(--green); }
.form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 16px; }
.form-group label { display: block; font-size: 11px; letter-spacing: 1.5px; text-transform: uppercase; color: var(--muted); margin-bottom: 6px; font-family: 'Orbitron', sans-serif; }
.form-group input, .form-group select, .form-group textarea { width: 100%; padding: 11px 14px; border-radius: 8px; border: 2px solid var(--border); background: #1a1a1a; color: #fff; font-size: 14px; font-family: 'Rajdhani', sans-serif; transition: 0.3s; }
.form-group input:focus, .form-group select:focus { outline: none; border-color: var(--green); box-shadow: 0 0 15px rgba(0,204,102,0.3); }

.btn { padding: 12px 28px; border: none; border-radius: 8px; font-family: 'Orbitron', sans-serif; font-weight: 900; font-size: 12px; letter-spacing: 1.5px; cursor: pointer; transition: 0.3s; text-decoration: none; display: inline-block; text-align: center; }
.btn-primary { background: linear-gradient(90deg, var(--red), var(--green), var(--red)); background-size: 300% 100%; animation: gradientShift 3s ease infinite; color: #fff; }
.btn-primary:hover { transform: translateY(-2px); box-shadow: 0 10px 25px rgba(0,204,102,0.4); }
.btn-success { background: var(--green); color: #fff; }
.btn-success:hover { background: #1eb356; }
.btn-danger { background: transparent; border: 2px solid var(--red); color: var(--red); }
.btn-danger:hover { background: var(--red); color: #fff; }
.btn-small { padding: 7px 14px; font-size: 10px; }

.tips-list { display: flex; flex-direction: column; gap: 12px; }
.tip-row { background: var(--card); border: 1px solid var(--border); border-radius: 14px; padding: 18px 22px; display: grid; grid-template-columns: auto 1fr auto; gap: 20px; align-items: center; transition: 0.3s; position: relative; overflow: hidden; }
.tip-row::before { content: ''; position: absolute; left: 0; top: 0; bottom: 0; width: 3px; background: linear-gradient(180deg, var(--red), var(--green)); }
.tip-row:hover { border-color: var(--green); transform: translateX(4px); }
.tip-avatar { width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-family: 'Orbitron', sans-serif; font-weight: 900; font-size: 20px; color: #fff; border: 2px solid var(--red); flex-shrink: 0; }
.tip-body { min-width: 0; }
.tip-tipster { font-family: 'Orbitron', sans-serif; font-size: 12px; color: var(--green); letter-spacing: 1px; margin-bottom: 4px; }
.tip-match { font-size: 16px; font-weight: 700; color: #fff; margin-bottom: 4px; }
.tip-detail { color: var(--muted); font-size: 13px; display: flex; gap: 12px; flex-wrap: wrap; align-items: center; }
.status-badge { padding: 2px 10px; border-radius: 20px; font-size: 10px; font-weight: 900; letter-spacing: 1px; font-family: 'Orbitron', sans-serif; text-transform: uppercase; }
.status-pending { background: rgba(255,193,7,0.2); color: #ffc107; border: 1px solid #ffc107; }
.status-won { background: rgba(0,204,102,0.2); color: var(--green); border: 1px solid var(--green); }
.status-lost { background: rgba(255,0,51,0.2); color: var(--red); border: 1px solid var(--red); }
.tip-actions { display: flex; gap: 8px; flex-wrap: wrap; }

.empty-tips { text-align: center; padding: 60px 20px; background: var(--card); border: 2px dashed var(--border); border-radius: 16px; color: var(--muted); }
.empty-tips .icon { font-size: 60px; margin-bottom: 12px; opacity: 0.6; }

@media (max-width: 700px) {
    .top-bar { padding: 12px 15px; }
    .top-bar .logo { font-size: 15px; }
    .user-info { font-size: 11px; gap: 6px; }
    .back-btn, .logout-btn { padding: 5px 10px; font-size: 9px; }
    .tip-row { grid-template-columns: auto 1fr; }
    .tip-actions { grid-column: span 2; justify-content: flex-start; }
    .form-card { padding: 20px; }
    .page-title { font-size: 18px; }
}
</style>
</head>
<body>

<div class="top-bar">
    <div class="logo">MILLZ ADMIN</div>
    <div class="user-info">
        👤 <strong><?= htmlspecialchars($admin_name) ?></strong>
        <a href="dashboard.php" class="back-btn">← DASH</a>
        <a href="logout.php" class="logout-btn">LOGOUT</a>
    </div>
</div>

<div class="container">
    <h1 class="page-title">💰 Manage Betting Tips</h1>
    <p class="page-sub">Add, edit and track tip results</p>

    <?php if (!empty($msg)): ?>
        <div class="alert <?= $msg_type ?>"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <div class="form-card">
        <h2 class="form-title">➕ Add New Tip</h2>
        <form method="POST">
            <div class="form-grid">
                <div class="form-group">
                    <label>Tipster *</label>
                    <select name="tipster_id" required>
                        <option value="">-- Select Tipster --</option>
                        <?php foreach ($tipsters as $t): ?>
                            <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>League</label>
                    <input type="text" name="league" placeholder="e.g. Premier League">
                </div>
                <div class="form-group" style="grid-column: 1 / -1;">
                    <label>Match *</label>
                    <input type="text" name="match_name" placeholder="e.g. Man Utd vs Man City" required>
                </div>
                <div class="form-group" style="grid-column: 1 / -1;">
                    <label>Tip *</label>
                    <input type="text" name="tip" placeholder="e.g. Over 2.5 goals" required>
                </div>
                <div class="form-group">
                    <label>Odds</label>
                    <input type="number" step="0.01" name="odds" placeholder="1.85">
                </div>
                <div class="form-group">
                    <label>Match Date</label>
                    <input type="date" name="match_date">
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status">
                        <option value="pending">Pending</option>
                        <option value="won">Won</option>
                        <option value="lost">Lost</option>
                    </select>
                </div>
                <div class="form-group" style="grid-column: 1 / -1;">
                    <label>Bet Link</label>
                    <input type="text" name="bet_link" placeholder="https://betway.co.tz/...">
                </div>
            </div>
            <button type="submit" name="add_tip" class="btn btn-primary">➕ ADD TIP</button>
        </form>
    </div>

    <h2 style="font-family: 'Orbitron', sans-serif; font-size: 16px; margin-bottom: 16px; color: var(--green);">📋 All Tips (<?= count($tips) ?>)</h2>

    <?php if (count($tips) > 0): ?>
        <div class="tips-list">
            <?php foreach ($tips as $tip): ?>
                <div class="tip-row">
                    <div class="tip-avatar" style="background: linear-gradient(135deg, <?= htmlspecialchars($tip['tipster_color'] ?? '#ff0033') ?>, #000);">
                        <?= strtoupper(substr($tip['tipster_name'] ?? '?', 0, 1)) ?>
                    </div>
                    <div class="tip-body">
                        <div class="tip-tipster"><?= htmlspecialchars($tip['tipster_name'] ?? 'Unknown') ?></div>
                        <div class="tip-match">⚽ <?= htmlspecialchars($tip['match_name']) ?></div>
                        <div class="tip-detail">
                            <span>🎯 <?= htmlspecialchars($tip['tip']) ?></span>
                            <?php if ($tip['odds']): ?><span>📊 Odds: <?= $tip['odds'] ?></span><?php endif; ?>
                            <?php if ($tip['match_date']): ?><span>📅 <?= htmlspecialchars($tip['match_date']) ?></span><?php endif; ?>
                            <span class="status-badge status-<?= htmlspecialchars($tip['status']) ?>"><?= strtoupper($tip['status']) ?></span>
                        </div>
                    </div>
                    <div class="tip-actions">
                        <?php if ($tip['status'] === 'pending'): ?>
                            <a href="?won=<?= $tip['id'] ?>" class="btn btn-success btn-small">✓ WON</a>
                            <a href="?lost=<?= $tip['id'] ?>" class="btn btn-danger btn-small">✗ LOST</a>
                        <?php endif; ?>
                        <a href="?delete=<?= $tip['id'] ?>" class="btn btn-danger btn-small" onclick="return confirm('Delete this tip?')">🗑</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty-tips">
            <div class="icon">💰</div>
            <p>No tips yet. Add your first tip above! ☝️</p>
        </div>
    <?php endif; ?>
</div>

</body>
</html>