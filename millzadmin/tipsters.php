<?php
session_start();
include 'config/db.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$msg = '';
$msg_type = 'success';

// ADD
if (isset($_POST['add_tipster'])) {
    $name = trim($_POST['name']);
    $bio = trim($_POST['bio']);
    $color = $_POST['color'] ?? '#ff0033';

    if (empty($name)) {
        $msg = 'Name is required.';
        $msg_type = 'error';
    } else {
        $stmt = $conn->prepare("INSERT INTO tipsters (name, bio, color, status) VALUES (?, ?, ?, 'NEW')");
        $stmt->bind_param("sss", $name, $bio, $color);
        if ($stmt->execute()) {
            $msg = '✅ Tipster added!';
        } else {
            $msg = 'Error: ' . $conn->error;
            $msg_type = 'error';
        }
    }
}

// DELETE
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM tipsters WHERE id = $id");
    header('Location: tipsters.php'); exit;
}

// UPDATE
if (isset($_POST['update_tipster'])) {
    $id = (int)$_POST['id'];
    $name = trim($_POST['name']);
    $bio = trim($_POST['bio']);
    $color = $_POST['color'] ?? '#ff0033';
    $status = $_POST['status'] ?? 'NEW';
    $stmt = $conn->prepare("UPDATE tipsters SET name=?, bio=?, color=?, status=? WHERE id=?");
    $stmt->bind_param("ssssi", $name, $bio, $color, $status, $id);
    $stmt->execute();
    header('Location: tipsters.php'); exit;
}

$tipsters = [];
$res = $conn->query("SELECT * FROM tipsters ORDER BY id ASC");
while ($row = $res->fetch_assoc()) $tipsters[] = $row;

$edit = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $edit = $conn->query("SELECT * FROM tipsters WHERE id = $id")->fetch_assoc();
}

$admin_name = $_SESSION['admin_username'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MILLZ ADMIN - Tipsters</title>
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;700;900&family=Rajdhani:wght@400;500;600;700&family=Bungee&display=swap" rel="stylesheet">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
:root { --bg: #000; --card: #0f0f0f; --border: #2a2a2a; --red: #ff0033; --red-light: #ff3366; --green: #00cc66; --text: #fff; --muted: #888; }
body { font-family: 'Rajdhani', sans-serif; background: var(--bg); color: var(--text); min-height: 100vh; }
@keyframes gradientShift { 0% { background-position: 0% 50%; } 50% { background-position: 100% 50%; } 100% { background-position: 0% 50%; } }
.top-bar { background: linear-gradient(90deg, #0f0f0f, #000); border-bottom: 1px solid var(--border); padding: 15px 25px; display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 100; }
.top-bar .logo { font-family: 'Bungee', cursive; font-size: 20px; background: linear-gradient(90deg, #ff0033, #00cc66, #ff0033); background-size: 300% 100%; animation: gradientShift 4s ease infinite; -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; letter-spacing: 2px; }
.user-info { display: flex; align-items: center; gap: 12px; font-size: 13px; color: var(--muted); }
.user-info strong { color: var(--green); }
.back-btn, .logout-btn { padding: 7px 14px; background: transparent; border: 2px solid var(--red); color: var(--red); border-radius: 8px; font-family: 'Orbitron', sans-serif; font-size: 10px; font-weight: 900; letter-spacing: 1px; text-decoration: none; transition: 0.3s; }
.back-btn { border-color: var(--green); color: var(--green); }
.back-btn:hover { background: var(--green); color: #000; }
.logout-btn:hover { background: var(--red); color: #fff; }
.container { max-width: 1100px; margin: 0 auto; padding: 30px 20px; }
.page-title { font-family: 'Orbitron', sans-serif; font-size: 24px; margin-bottom: 6px; }
.page-sub { color: var(--muted); font-size: 14px; margin-bottom: 30px; }
.alert { padding: 14px 20px; border-radius: 10px; margin-bottom: 20px; font-weight: 600; font-size: 14px; }
.alert.success { background: rgba(0,204,102,0.15); border: 1px solid var(--green); color: var(--green); }
.alert.error { background: rgba(255,0,51,0.15); border: 1px solid var(--red); color: var(--red-light); }
.form-card { background: var(--card); border: 1px solid var(--border); border-radius: 16px; padding: 28px; margin-bottom: 30px; position: relative; overflow: hidden; }
.form-card::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px; background: linear-gradient(90deg, var(--red), var(--green), var(--red)); background-size: 200% 100%; animation: gradientShift 4s ease infinite; }
.form-title { font-family: 'Orbitron', sans-serif; font-size: 16px; margin-bottom: 20px; color: var(--green); }
.form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 16px; }
.form-group label { display: block; font-size: 11px; letter-spacing: 1.5px; text-transform: uppercase; color: var(--muted); margin-bottom: 6px; font-family: 'Orbitron', sans-serif; }
.form-group input, .form-group select { width: 100%; padding: 11px 14px; border-radius: 8px; border: 2px solid var(--border); background: #1a1a1a; color: #fff; font-size: 14px; font-family: 'Rajdhani', sans-serif; }
.form-group input:focus, .form-group select:focus { outline: none; border-color: var(--green); box-shadow: 0 0 15px rgba(0,204,102,0.3); }
.form-group input[type="color"] { height: 45px; padding: 4px; cursor: pointer; }
.btn { padding: 12px 28px; border: none; border-radius: 8px; font-family: 'Orbitron', sans-serif; font-weight: 900; font-size: 12px; letter-spacing: 1.5px; cursor: pointer; transition: 0.3s; text-decoration: none; display: inline-block; text-align: center; }
.btn-primary { background: linear-gradient(90deg, var(--red), var(--green), var(--red)); background-size: 300% 100%; animation: gradientShift 3s ease infinite; color: #fff; }
.btn-primary:hover { transform: translateY(-2px); box-shadow: 0 10px 25px rgba(0,204,102,0.4); }
.btn-danger { background: transparent; border: 2px solid var(--red); color: var(--red); }
.btn-danger:hover { background: var(--red); color: #fff; }
.btn-small { padding: 7px 14px; font-size: 10px; }

.tipsters-list { display: flex; flex-direction: column; gap: 12px; }
.tipster-row { background: var(--card); border: 1px solid var(--border); border-radius: 14px; padding: 18px 22px; display: flex; align-items: center; gap: 18px; transition: 0.3s; }
.tipster-row:hover { border-color: var(--green); transform: translateX(4px); }
.tipster-avatar { width: 55px; height: 55px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-family: 'Orbitron', sans-serif; font-weight: 900; font-size: 22px; color: #fff; border: 2px solid var(--green); flex-shrink: 0; }
.tipster-body { flex: 1; min-width: 0; }
.tipster-name { font-family: 'Orbitron', sans-serif; font-size: 16px; color: #fff; margin-bottom: 4px; display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
.tipster-bio { color: var(--muted); font-size: 13px; font-style: italic; }
.status-badge { padding: 3px 10px; border-radius: 20px; font-size: 10px; font-weight: 900; letter-spacing: 1px; font-family: 'Orbitron', sans-serif; background: var(--green); color: #000; }
.tipster-stats { display: flex; flex-direction: column; gap: 4px; text-align: right; min-width: 90px; }
.stat-line { font-size: 12px; color: var(--muted); }
.stat-line strong { color: var(--green); }
.tipster-actions { display: flex; gap: 8px; }

@media (max-width: 700px) {
    .top-bar { padding: 12px 15px; }
    .top-bar .logo { font-size: 15px; }
    .user-info { font-size: 11px; gap: 6px; }
    .back-btn, .logout-btn { padding: 5px 10px; font-size: 9px; }
    .tipster-row { flex-wrap: wrap; }
    .tipster-stats { text-align: left; min-width: auto; }
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
    <h1 class="page-title">🏅 Manage Tipsters</h1>
    <p class="page-sub">Add and edit betting tipsters</p>

    <?php if (!empty($msg)): ?>
        <div class="alert <?= $msg_type ?>"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <div class="form-card">
        <h2 class="form-title"><?= $edit ? '✏️ Edit Tipster' : '➕ Add New Tipster' ?></h2>
        <form method="POST">
            <?php if ($edit): ?>
                <input type="hidden" name="id" value="<?= $edit['id'] ?>">
            <?php endif; ?>
            <div class="form-grid">
                <div class="form-group">
                    <label>Name *</label>
                    <input type="text" name="name" value="<?= $edit ? htmlspecialchars($edit['name']) : '' ?>" placeholder="e.g. SHARIE" required>
                </div>
                <div class="form-group">
                    <label>Color</label>
                    <input type="color" name="color" value="<?= $edit ? htmlspecialchars($edit['color']) : '#ff0033' ?>">
                </div>
                <div class="form-group" style="grid-column: 1 / -1;">
                    <label>Bio</label>
                    <input type="text" name="bio" value="<?= $edit ? htmlspecialchars($edit['bio']) : '' ?>" placeholder="e.g. Premier League specialist">
                </div>
                <?php if ($edit): ?>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status">
                        <option value="NEW" <?= $edit['status'] === 'NEW' ? 'selected' : '' ?>>NEW</option>
                        <option value="VERIFIED" <?= $edit['status'] === 'VERIFIED' ? 'selected' : '' ?>>VERIFIED</option>
                        <option value="PRO" <?= $edit['status'] === 'PRO' ? 'selected' : '' ?>>PRO</option>
                    </select>
                </div>
                <?php endif; ?>
            </div>
            <?php if ($edit): ?>
                <button type="submit" name="update_tipster" class="btn btn-primary">💾 UPDATE</button>
                <a href="tipsters.php" class="btn btn-danger" style="margin-left: 8px;">Cancel</a>
            <?php else: ?>
                <button type="submit" name="add_tipster" class="btn btn-primary">➕ ADD TIPSTER</button>
            <?php endif; ?>
        </form>
    </div>

    <h2 style="font-family: 'Orbitron', sans-serif; font-size: 16px; margin-bottom: 16px; color: var(--green);">📋 All Tipsters (<?= count($tipsters) ?>)</h2>

    <div class="tipsters-list">
        <?php foreach ($tipsters as $t): ?>
            <div class="tipster-row">
                <div class="tipster-avatar" style="background: linear-gradient(135deg, <?= htmlspecialchars($t['color']) ?>, #000);">
                    <?= strtoupper(substr($t['name'], 0, 1)) ?>
                </div>
                <div class="tipster-body">
                    <div class="tipster-name">
                        <?= htmlspecialchars($t['name']) ?>
                        <span class="status-badge"><?= htmlspecialchars($t['status']) ?></span>
                    </div>
                    <div class="tipster-bio"><?= htmlspecialchars($t['bio']) ?></div>
                </div>
                <div class="tipster-stats">
                    <span class="stat-line">📝 Tips: <strong><?= $t['total_tips'] ?></strong></span>
                    <span class="stat-line">✓ Wins: <strong><?= $t['total_wins'] ?></strong></span>
                </div>
                <div class="tipster-actions">
                    <a href="?edit=<?= $t['id'] ?>" class="btn btn-primary btn-small">✏️</a>
                    <a href="?delete=<?= $t['id'] ?>" class="btn btn-danger btn-small" onclick="return confirm('Delete this tipster?')">🗑</a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

</body>
</html>