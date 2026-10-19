<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include 'config/db.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$msg = '';
$msg_type = 'success';

// ===== ADD PLAYER =====
if (isset($_POST['add_player'])) {
    $name = trim($_POST['name']);
    if (empty($name)) {
        $msg = 'Name is required.';
        $msg_type = 'error';
    } else {
        $stmt = $conn->prepare("INSERT INTO league_players (name, played, wins, draws, losses, points, goals_for, goals_against, paid) VALUES (?, 0, 0, 0, 0, 0, 0, 0, 1)");
        if ($stmt) {
            $stmt->bind_param("s", $name);
            if ($stmt->execute()) {
                $msg = '✅ Player added: ' . htmlspecialchars($name);
            } else {
                $msg = 'Error: ' . $stmt->error;
                $msg_type = 'error';
            }
        } else {
            $msg = 'Error: ' . $conn->error;
            $msg_type = 'error';
        }
    }
}

// ===== ENTER MATCH RESULT =====
if (isset($_POST['save_result'])) {
    $p1_id = (int)$_POST['p1_id'];
    $p2_id = (int)$_POST['p2_id'];
    $p1_score = (int)$_POST['p1_score'];
    $p2_score = (int)$_POST['p2_score'];

    if ($p1_id === $p2_id || $p1_id === 0 || $p2_id === 0) {
        $msg = 'Choose two different players.';
        $msg_type = 'error';
    } else {
        $p1 = $conn->query("SELECT * FROM league_players WHERE id = $p1_id")->fetch_assoc();
        $p2 = $conn->query("SELECT * FROM league_players WHERE id = $p2_id")->fetch_assoc();

        if ($p1 && $p2) {
            if ($p1_score > $p2_score) {
                $conn->query("UPDATE league_players SET played = played + 1, wins = wins + 1, points = points + 3, goals_for = goals_for + $p1_score, goals_against = goals_against + $p2_score WHERE id = $p1_id");
                $conn->query("UPDATE league_players SET played = played + 1, losses = losses + 1, goals_for = goals_for + $p2_score, goals_against = goals_against + $p1_score WHERE id = $p2_id");
            } elseif ($p2_score > $p1_score) {
                $conn->query("UPDATE league_players SET played = played + 1, wins = wins + 1, points = points + 3, goals_for = goals_for + $p2_score, goals_against = goals_against + $p1_score WHERE id = $p2_id");
                $conn->query("UPDATE league_players SET played = played + 1, losses = losses + 1, goals_for = goals_for + $p1_score, goals_against = goals_against + $p2_score WHERE id = $p1_id");
            } else {
                $conn->query("UPDATE league_players SET played = played + 1, draws = draws + 1, points = points + 1, goals_for = goals_for + $p1_score, goals_against = goals_against + $p1_score WHERE id = $p1_id");
                $conn->query("UPDATE league_players SET played = played + 1, draws = draws + 1, points = points + 1, goals_for = goals_for + $p2_score, goals_against = goals_against + $p2_score WHERE id = $p2_id");
            }

            $conn->query("INSERT INTO matches (player1_id, player2_id, player1_score, player2_score, match_date, status) VALUES ($p1_id, $p2_id, $p1_score, $p2_score, NOW(), 'finished')");

            $msg = '✅ Saved: ' . htmlspecialchars($p1['name']) . ' ' . $p1_score . ' - ' . $p2_score . ' ' . htmlspecialchars($p2['name']);
        }
    }
}

// ===== DELETE PLAYER =====
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM league_players WHERE id = $id");
    header('Location: league.php'); exit;
}

// ===== RESET ALL =====
if (isset($_GET['reset'])) {
    $conn->query("UPDATE league_players SET played=0, wins=0, draws=0, losses=0, points=0, goals_for=0, goals_against=0");
    $conn->query("DELETE FROM matches");
    header('Location: league.php'); exit;
}

// ===== UPDATE PLAYER =====
if (isset($_POST['update_player'])) {
    $id = (int)$_POST['id'];
    $name = trim($_POST['name']);
    $stmt = $conn->prepare("UPDATE league_players SET name=? WHERE id=?");
    $stmt->bind_param("si", $name, $id);
    $stmt->execute();
    header('Location: league.php'); exit;
}

$players = [];
$res = $conn->query("SELECT * FROM league_players ORDER BY points DESC, wins DESC, goals_for DESC, id ASC");
if ($res) { while ($row = $res->fetch_assoc()) $players[] = $row; }

$matches = [];
$res = $conn->query("SELECT m.*, p1.name AS p1_name, p2.name AS p2_name FROM matches m LEFT JOIN league_players p1 ON m.player1_id = p1.id LEFT JOIN league_players p2 ON m.player2_id = p2.id ORDER BY m.created_at DESC LIMIT 10");
if ($res) { while ($row = $res->fetch_assoc()) $matches[] = $row; }

$edit = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $edit = $conn->query("SELECT * FROM league_players WHERE id = $id")->fetch_assoc();
}

$admin_name = $_SESSION['admin_username'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MILLZ ADMIN - League</title>
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;700;900&family=Rajdhani:wght@400;500;600;700&family=Bungee&display=swap" rel="stylesheet">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
:root { --bg: #000; --card: #0f0f0f; --border: #2a2a2a; --red: #ff0033; --red-light: #ff3366; --green: #00cc66; --text: #fff; --muted: #888; }
body { font-family: 'Rajdhani', sans-serif; background: var(--bg); color: var(--text); min-height: 100vh; }
@keyframes gradientShift { 0% { background-position: 0% 50%; } 50% { background-position: 100% 50%; } 100% { background-position: 0% 50%; } }
.top-bar { background: linear-gradient(90deg, #0f0f0f, #000); border-bottom: 1px solid var(--border); padding: 15px 25px; display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 100; flex-wrap: wrap; gap: 10px; }
.top-bar .logo { font-family: 'Bungee', cursive; font-size: 20px; background: linear-gradient(90deg, #ff0033, #00cc66, #ff0033); background-size: 300% 100%; animation: gradientShift 4s ease infinite; -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; letter-spacing: 2px; }
.user-info { display: flex; align-items: center; gap: 10px; font-size: 13px; color: var(--muted); flex-wrap: wrap; }
.user-info strong { color: var(--green); }
.back-btn, .logout-btn, .reset-btn { padding: 7px 14px; background: transparent; border: 2px solid var(--red); color: var(--red); border-radius: 8px; font-family: 'Orbitron', sans-serif; font-size: 10px; font-weight: 900; letter-spacing: 1px; text-decoration: none; transition: 0.3s; cursor: pointer; }
.back-btn { border-color: var(--green); color: var(--green); }
.back-btn:hover { background: var(--green); color: #000; }
.reset-btn:hover, .logout-btn:hover { background: var(--red); color: #fff; }
.container { max-width: 1300px; margin: 0 auto; padding: 30px 20px; }
.page-title { font-family: 'Orbitron', sans-serif; font-size: 24px; margin-bottom: 6px; }
.page-sub { color: var(--muted); font-size: 14px; margin-bottom: 30px; }
.alert { padding: 14px 20px; border-radius: 10px; margin-bottom: 20px; font-weight: 600; font-size: 14px; }
.alert.success { background: rgba(0,204,102,0.15); border: 1px solid var(--green); color: var(--green); }
.alert.error { background: rgba(255,0,51,0.15); border: 1px solid var(--red); color: #ff6666; }
.card { background: var(--card); border: 1px solid var(--border); border-radius: 16px; padding: 24px; margin-bottom: 24px; position: relative; overflow: hidden; }
.card::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px; background: linear-gradient(90deg, var(--red), var(--green), var(--red)); background-size: 200% 100%; animation: gradientShift 4s ease infinite; }
.card-title { font-family: 'Orbitron', sans-serif; font-size: 15px; margin-bottom: 18px; color: var(--green); letter-spacing: 1px; }
.form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 14px; margin-bottom: 16px; align-items: end; }
.form-group label { display: block; font-size: 10px; letter-spacing: 1.5px; text-transform: uppercase; color: var(--muted); margin-bottom: 6px; font-family: 'Orbitron', sans-serif; }
.form-group input, .form-group select { width: 100%; padding: 11px 14px; border-radius: 8px; border: 2px solid var(--border); background: #1a1a1a; color: #fff; font-size: 14px; font-family: 'Rajdhani', sans-serif; transition: 0.3s; }
.form-group input:focus, .form-group select:focus { outline: none; border-color: var(--green); box-shadow: 0 0 15px rgba(0,204,102,0.3); }
.score-input { display: flex; align-items: center; gap: 8px; }
.score-input input { text-align: center; font-family: 'Orbitron'; font-weight: 900; font-size: 20px; padding: 8px; }
.score-input .dash { font-family: 'Orbitron'; font-size: 24px; color: var(--red); font-weight: 900; }
.btn { padding: 12px 24px; border: none; border-radius: 8px; font-family: 'Orbitron', sans-serif; font-weight: 900; font-size: 11px; letter-spacing: 1.5px; cursor: pointer; transition: 0.3s; text-decoration: none; display: inline-block; text-align: center; }
.btn-primary { background: linear-gradient(90deg, var(--red), var(--green), var(--red)); background-size: 300% 100%; animation: gradientShift 3s ease infinite; color: #fff; }
.btn-primary:hover { transform: translateY(-2px); box-shadow: 0 10px 25px rgba(0,204,102,0.4); }
.btn-danger { background: transparent; border: 2px solid var(--red); color: var(--red); }
.btn-danger:hover { background: var(--red); color: #fff; }
.btn-small { padding: 6px 12px; font-size: 9px; }
.table-wrap { background: var(--card); border: 1px solid var(--border); border-radius: 16px; padding: 5px; overflow-x: auto; }
.table { width: 100%; border-collapse: collapse; min-width: 750px; }
.table thead { background: linear-gradient(90deg, var(--red), var(--green), var(--red)); background-size: 300% 100%; animation: gradientShift 5s ease infinite; }
.table th { padding: 12px 8px; text-align: center; color: #fff; font-family: 'Orbitron', sans-serif; font-weight: 900; font-size: 11px; letter-spacing: 1px; text-transform: uppercase; }
.table th:nth-child(2) { text-align: left; }
.table td { padding: 11px 8px; text-align: center; color: var(--text); font-size: 14px; border-bottom: 1px solid var(--border); }
.table td.player-cell { text-align: left; font-weight: 700; }
.table tbody tr:hover { background: rgba(0,204,102,0.05); }
.table .pts { font-family: 'Orbitron'; font-weight: 900; color: var(--green); font-size: 15px; }
.table tbody tr:nth-child(1) td.player-cell { color: var(--green); }
.table tbody tr:nth-child(2) td.player-cell { color: #ffcc00; }
.table tbody tr:nth-child(3) td.player-cell { color: #cd7f32; }
.match-list { display: flex; flex-direction: column; gap: 8px; }
.match-row { display: flex; align-items: center; justify-content: space-between; padding: 12px 16px; background: #1a1a1a; border: 1px solid var(--border); border-radius: 10px; gap: 12px; flex-wrap: wrap; font-size: 14px; }
.match-row .m-players { color: #fff; font-weight: 700; }
.match-row .m-score { font-family: 'Orbitron'; font-weight: 900; color: var(--green); font-size: 16px; }
.match-row .m-date { color: var(--muted); font-size: 12px; }
.info-box { background: rgba(0,204,102,0.08); border: 1px dashed var(--green); border-radius: 10px; padding: 14px; color: var(--muted); font-size: 13px; margin-bottom: 20px; line-height: 1.7; }
.info-box strong { color: var(--green); }
@media (max-width: 700px) {
    .top-bar { padding: 12px 15px; }
    .top-bar .logo { font-size: 15px; }
    .user-info { font-size: 11px; }
    .back-btn, .logout-btn, .reset-btn { padding: 5px 10px; font-size: 9px; }
    .page-title { font-size: 18px; }
    .card { padding: 18px; }
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
    <h1 class="page-title">🏆 Manage League</h1>
    <p class="page-sub">Add players and enter match results — points update automatically</p>

    <?php if (!empty($msg)): ?>
        <div class="alert <?= $msg_type ?>"><?= $msg ?></div>
    <?php endif; ?>

    <div class="info-box">
        <strong>📌 Jinsi inavyofanya kazi:</strong><br>
        1. Ongeza wachezaji kwanza (SKUDU, CBA, n.k.)<br>
        2. Baada ya mechi → ingiza score (mfano: SKUDU 3 - 1 CBA)<br>
        3. Points table ina-update <strong>yenyewe</strong> — wins, points, goals zote
    </div>

    <!-- ADD PLAYER -->
    <div class="card">
        <h2 class="card-title">➕ Add New Player</h2>
        <form method="POST">
            <div class="form-grid">
                <div class="form-group" style="grid-column: span 2;">
                    <label>Player Name</label>
                    <input type="text" name="name" placeholder="e.g. SKUDU" required>
                </div>
                <div class="form-group">
                    <button type="submit" name="add_player" class="btn btn-primary" style="width:100%;">ADD PLAYER</button>
                </div>
            </div>
        </form>
    </div>

    <!-- ENTER MATCH RESULT -->
    <?php if (count($players) >= 2): ?>
    <div class="card">
        <h2 class="card-title">📝 Enter Match Result</h2>
        <form method="POST">
            <div class="form-grid" style="grid-template-columns: 1fr auto 1fr;">
                <div class="form-group">
                    <label>Player 1</label>
                    <select name="p1_id" required>
                        <option value="">-- Select --</option>
                        <?php foreach ($players as $p): ?>
                            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Score</label>
                    <div class="score-input">
                        <input type="number" name="p1_score" min="0" value="0" style="width:70px;">
                        <span class="dash">-</span>
                        <input type="number" name="p2_score" min="0" value="0" style="width:70px;">
                    </div>
                </div>
                <div class="form-group">
                    <label>Player 2</label>
                    <select name="p2_id" required>
                        <option value="">-- Select --</option>
                        <?php foreach ($players as $p): ?>
                            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <button type="submit" name="save_result" class="btn btn-primary">💾 SAVE RESULT</button>
        </form>
    </div>
    <?php else: ?>
    <div class="card">
        <p style="color: var(--muted); text-align: center; padding: 10px;">Add at least 2 players to enter match results.</p>
    </div>
    <?php endif; ?>

    <!-- EDIT PLAYER -->
    <?php if ($edit): ?>
    <div class="card">
        <h2 class="card-title">✏️ Edit Player: <?= htmlspecialchars($edit['name']) ?></h2>
        <form method="POST">
            <input type="hidden" name="id" value="<?= $edit['id'] ?>">
            <div class="form-grid">
                <div class="form-group" style="grid-column: span 2;">
                    <label>Player Name</label>
                    <input type="text" name="name" value="<?= htmlspecialchars($edit['name']) ?>" required>
                </div>
                <div class="form-group">
                    <button type="submit" name="update_player" class="btn btn-primary" style="width:100%;">💾 UPDATE</button>
                </div>
            </div>
        </form>
        <a href="league.php" class="btn btn-danger" style="margin-top:10px;">Cancel</a>
    </div>
    <?php endif; ?>

    <!-- POINTS TABLE -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 12px;">
        <h2 style="font-family: 'Orbitron', sans-serif; font-size: 16px; color: var(--green);">📊 Points Table (<?= count($players) ?> players)</h2>
        <?php if (count($players) > 0): ?>
            <a href="?reset=1" class="btn btn-danger btn-small" onclick="return confirm('Reset ALL scores to 0?')">🔄 RESET ALL</a>
        <?php endif; ?>
    </div>

    <?php if (count($players) > 0): ?>
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Player</th>
                        <th>P</th>
                        <th>W</th>
                        <th>D</th>
                        <th>L</th>
                        <th>GF</th>
                        <th>GA</th>
                        <th>Pts</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($players as $index => $p): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td class="player-cell"><?= htmlspecialchars($p['name']) ?></td>
                            <td><?= $p['played'] ?></td>
                            <td><?= $p['wins'] ?></td>
                            <td><?= $p['draws'] ?></td>
                            <td><?= $p['losses'] ?></td>
                            <td><?= $p['goals_for'] ?></td>
                            <td><?= $p['goals_against'] ?></td>
                            <td class="pts"><?= $p['points'] ?></td>
                            <td>
                                <a href="?edit=<?= $p['id'] ?>" class="btn btn-primary btn-small">✏️</a>
                                <a href="?delete=<?= $p['id'] ?>" class="btn btn-danger btn-small" onclick="return confirm('Delete player?')">🗑</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="card">
            <p style="color: var(--muted); text-align: center; padding: 30px;">No players yet. Add your first player above! ☝️</p>
        </div>
    <?php endif; ?>

    <!-- RECENT MATCHES -->
    <?php if (count($matches) > 0): ?>
    <div class="card" style="margin-top: 30px;">
        <h2 class="card-title">📋 Recent Matches</h2>
        <div class="match-list">
            <?php foreach ($matches as $m): ?>
                <div class="match-row">
                    <span class="m-players"><?= htmlspecialchars($m['p1_name'] ?? '?') ?></span>
                    <span class="m-score"><?= $m['player1_score'] ?> - <?= $m['player2_score'] ?></span>
                    <span class="m-players"><?= htmlspecialchars($m['p2_name'] ?? '?') ?></span>
                    <span class="m-date"><?= date('M j, H:i', strtotime($m['created_at'])) ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

</body>
</html>