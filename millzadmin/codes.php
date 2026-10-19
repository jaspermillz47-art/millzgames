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

// GENERATE CODE
if (isset($_POST['generate'])) {
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $custom = strtoupper(trim($_POST['custom_code'] ?? ''));

    if (!empty($custom)) {
        $code = $custom;
    } else {
        $code = 'MILLZ-' . strtoupper(substr(md5(uniqid()), 0, 6));
    }

    $stmt = $conn->prepare("INSERT INTO access_codes (code, name, phone) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $code, $name, $phone);
    if ($stmt->execute()) {
        $msg = '✅ Code generated: ' . $code;
    } else {
        $msg = 'Error: ' . $conn->error;
        $msg_type = 'error';
    }
}

// DELETE
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM access_codes WHERE id = $id");
    header('Location: codes.php'); exit;
}

// RESET (kwa mwezi mpya)
if (isset($_GET['reset'])) {
    $id = (int)$_GET['reset'];
    $conn->query("UPDATE access_codes 
                  SET status = 'active', 
                      device_id = NULL, 
                      used_at = NULL, 
                      activated_at = NULL, 
                      expires_at = NULL,
                      last_seen = NULL
                  WHERE id = $id");
    header('Location: codes.php'); exit;
}

// DELETE ALL USED
if (isset($_GET['clear_used'])) {
    $conn->query("DELETE FROM access_codes WHERE status = 'used'");
    header('Location: codes.php'); exit;
}

$codes = [];
$res = $conn->query("SELECT * FROM access_codes ORDER BY id DESC");
while ($row = $res->fetch_assoc()) $codes[] = $row;

$total_active = 0;
$total_used = 0;
$total_expired = 0;
$now = new DateTime();
foreach ($codes as $c) {
    if ($c['status'] === 'active') $total_active++;
    if ($c['status'] === 'used') {
        if (!empty($c['expires_at']) && new DateTime($c['expires_at']) < $now) {
            $total_expired++;
        } else {
            $total_used++;
        }
    }
}

$admin_name = $_SESSION['admin_username'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MILLZ ADMIN - Access Codes</title>
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;700;900&family=Rajdhani:wght@400;500;600;700&family=Bungee&display=swap" rel="stylesheet">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
:root { --bg: #1a0f00; --card: #2a1800; --border: #3a2400; --amber: #ff9500; --green: #00ff88; --red: #ff0033; --text: #fff; --muted: #b89860; }
body { font-family: 'Rajdhani', sans-serif; background: var(--bg); color: var(--text); min-height: 100vh; }
@keyframes gradientShift { 0% { background-position: 0% 50%; } 50% { background-position: 100% 50%; } 100% { background-position: 0% 50%; } }
.top-bar { background: linear-gradient(90deg, #2a1800, #1a0f00); border-bottom: 1px solid var(--border); padding: 15px 25px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; }
.top-bar .logo { font-family: 'Bungee', cursive; font-size: 20px; color: var(--green); text-shadow: 0 0 15px rgba(0,255,136,0.5); letter-spacing: 2px; }
.user-info { display: flex; align-items: center; gap: 10px; font-size: 13px; color: var(--muted); flex-wrap: wrap; }
.user-info strong { color: var(--green); }
.back-btn, .logout-btn { padding: 7px 14px; background: transparent; border: 2px solid var(--red); color: var(--red); border-radius: 8px; font-family: 'Orbitron', sans-serif; font-size: 10px; font-weight: 900; letter-spacing: 1px; text-decoration: none; transition: 0.3s; cursor: pointer; }
.back-btn { border-color: var(--green); color: var(--green); }
.back-btn:hover { background: var(--green); color: #000; }
.logout-btn:hover { background: var(--red); color: #fff; }
.container { max-width: 1100px; margin: 0 auto; padding: 30px 20px; }
.page-title { font-family: 'Orbitron', sans-serif; font-size: 24px; margin-bottom: 6px; }
.page-sub { color: var(--muted); font-size: 14px; margin-bottom: 30px; }
.alert { padding: 14px 20px; border-radius: 10px; margin-bottom: 20px; font-weight: 600; font-size: 14px; }
.alert.success { background: rgba(0,255,136,0.15); border: 1px solid var(--green); color: var(--green); }
.alert.error { background: rgba(255,0,51,0.15); border: 1px solid var(--red); color: #ff6666; }
.card { background: var(--card); border: 1px solid var(--border); border-radius: 16px; padding: 24px; margin-bottom: 24px; position: relative; overflow: hidden; }
.card::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px; background: linear-gradient(90deg, var(--amber), var(--green), var(--amber)); background-size: 200% 100%; animation: gradientShift 4s ease infinite; }
.card-title { font-family: 'Orbitron', sans-serif; font-size: 15px; margin-bottom: 18px; color: var(--green); letter-spacing: 1px; }
.form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 14px; margin-bottom: 16px; }
.form-group label { display: block; font-size: 10px; letter-spacing: 1.5px; text-transform: uppercase; color: var(--muted); margin-bottom: 6px; font-family: 'Orbitron', sans-serif; }
.form-group input { width: 100%; padding: 11px 14px; border-radius: 8px; border: 2px solid var(--border); background: #1a0f00; color: #fff; font-size: 14px; font-family: 'Rajdhani', sans-serif; }
.form-group input:focus { outline: none; border-color: var(--green); box-shadow: 0 0 15px rgba(0,255,136,0.3); }
.btn { padding: 12px 24px; border: none; border-radius: 8px; font-family: 'Orbitron', sans-serif; font-weight: 900; font-size: 11px; letter-spacing: 1.5px; cursor: pointer; transition: 0.3s; text-decoration: none; display: inline-block; text-align: center; }
.btn-primary { background: linear-gradient(90deg, var(--green), #33ffa5, var(--green)); background-size: 300% 100%; animation: gradientShift 3s ease infinite; color: #000; }
.btn-primary:hover { transform: translateY(-2px); box-shadow: 0 10px 25px rgba(0,255,136,0.4); }
.btn-danger { background: transparent; border: 2px solid var(--red); color: var(--red); }
.btn-danger:hover { background: var(--red); color: #fff; }
.btn-amber { background: linear-gradient(90deg, var(--amber), #ffb340, var(--amber)); background-size: 300% 100%; animation: gradientShift 3s ease infinite; color: #000; }
.btn-small { padding: 6px 12px; font-size: 9px; }
.stats-row { display: flex; gap: 12px; margin-bottom: 20px; flex-wrap: wrap; }
.stat-pill { padding: 10px 18px; background: var(--card); border: 1px solid var(--border); border-radius: 30px; font-size: 13px; color: var(--muted); }
.stat-pill strong { color: var(--green); font-family: 'Orbitron', sans-serif; }
.code-card { background: var(--card); border: 1px solid var(--border); border-radius: 12px; padding: 18px; margin-bottom: 10px; display: flex; justify-content: space-between; align-items: center; gap: 14px; flex-wrap: wrap; transition: 0.3s; }
.code-card:hover { border-color: var(--amber); }
.code-card.expired { border-color: var(--red); opacity: 0.7; }
.code-display { font-family: 'Orbitron', sans-serif; font-size: 18px; font-weight: 900; color: var(--green); letter-spacing: 3px; text-shadow: 0 0 15px rgba(0,255,136,0.5); }
.code-card.expired .code-display { color: var(--red); text-shadow: 0 0 15px rgba(255,0,51,0.5); }
.code-meta { color: var(--muted); font-size: 12px; margin-top: 4px; line-height: 1.7; }
.code-meta strong { color: var(--amber); }
.code-meta .expired-text { color: var(--red); font-weight: 700; }
.code-status { padding: 4px 12px; border-radius: 20px; font-size: 10px; font-weight: 900; font-family: 'Orbitron', sans-serif; letter-spacing: 1px; }
.status-active { background: rgba(0,255,136,0.2); color: var(--green); border: 1px solid var(--green); }
.status-used { background: rgba(255,149,0,0.2); color: var(--amber); border: 1px solid var(--amber); }
.status-expired { background: rgba(255,0,51,0.2); color: var(--red); border: 1px solid var(--red); }
@media (max-width: 700px) {
    .top-bar { padding: 12px 15px; }
    .top-bar .logo { font-size: 15px; }
    .back-btn, .logout-btn { padding: 5px 10px; font-size: 9px; }
    .page-title { font-size: 18px; }
    .card { padding: 18px; }
    .code-display { font-size: 14px; letter-spacing: 2px; }
    .code-card { padding: 14px; }
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
    <h1 class="page-title">🔑 Access Codes</h1>
    <p class="page-sub">Kila code ni kwa mtu mmoja — inaisha baada ya siku 30</p>

    <?php if (!empty($msg)): ?>
        <div class="alert <?= $msg_type ?>"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <div class="stats-row">
        <span class="stat-pill">Total: <strong><?= count($codes) ?></strong></span>
        <span class="stat-pill">Active: <strong><?= $total_active ?></strong></span>
        <span class="stat-pill">In Use: <strong><?= $total_used ?></strong></span>
        <span class="stat-pill">Expired: <strong><?= $total_expired ?></strong></span>
    </div>

    <div class="card">
        <h2 class="card-title">➕ Generate New Code</h2>
        <form method="POST">
            <div class="form-grid">
                <div class="form-group">
                    <label>Customer Name</label>
                    <input type="text" name="name" placeholder="e.g. Jasper">
                </div>
                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="text" name="phone" placeholder="e.g. 0683179360">
                </div>
                <div class="form-group" style="grid-column: 1 / -1;">
                    <label>Custom Code (optional — leave empty for auto)</label>
                    <input type="text" name="custom_code" placeholder="e.g. MILLZ2025" style="text-transform:uppercase;">
                </div>
            </div>
            <button type="submit" name="generate" class="btn btn-primary">🔑 GENERATE CODE (30 DAYS)</button>
        </form>
    </div>

    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;flex-wrap:wrap;gap:12px;">
        <h2 style="font-family:'Orbitron';font-size:16px;color:var(--green);">📋 All Codes (<?= count($codes) ?>)</h2>
        <?php if ($total_used > 0): ?>
            <a href="?clear_used=1" class="btn btn-danger btn-small" onclick="return confirm('Delete all IN-USE codes?')">🗑 Clear In-Use</a>
        <?php endif; ?>
    </div>

    <?php if (count($codes) > 0): ?>
        <?php foreach ($codes as $c): ?>
            <?php
            $is_expired = false;
            $days_left = null;
            if ($c['status'] === 'used' && !empty($c['expires_at'])) {
                $exp = new DateTime($c['expires_at']);
                if ($exp < $now) {
                    $is_expired = true;
                } else {
                    $diff = $now->diff($exp);
                    $days_left = $diff->days;
                }
            }
            ?>
            <div class="code-card <?= $is_expired ? 'expired' : '' ?>">
                <div style="flex: 1; min-width: 250px;">
                    <div class="code-display"><?= htmlspecialchars($c['code']) ?></div>
                    <div class="code-meta">
                        <?php if ($c['name']): ?>👤 <strong><?= htmlspecialchars($c['name']) ?></strong> · <?php endif; ?>
                        <?php if ($c['phone']): ?>📱 <strong><?= htmlspecialchars($c['phone']) ?></strong><br><?php endif; ?>
                        
                        <?php if ($c['status'] === 'active'): ?>
                            🕐 Created: <?= date('M j, Y H:i', strtotime($c['created_at'])) ?><br>
                            ⏳ Expires after 30 days of activation
                        <?php elseif ($c['status'] === 'used'): ?>
                            🕐 Activated: <?= $c['activated_at'] ? date('M j, Y', strtotime($c['activated_at'])) : '—' ?><br>
                            <?php if ($is_expired): ?>
                                <span class="expired-text">❌ EXPIRED on <?= date('M j, Y', strtotime($c['expires_at'])) ?></span>
                            <?php else: ?>
                                ✅ Active · <strong><?= $days_left ?> day<?= $days_left == 1 ? '' : 's' ?> left</strong> (expires <?= date('M j, Y', strtotime($c['expires_at'])) ?>)
                            <?php endif; ?>
                            <?php if (!empty($c['device_id'])): ?>
                                <br>💻 Device: <strong><?= htmlspecialchars(substr($c['device_id'], 0, 20)) ?>...</strong>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
                <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                    <span class="code-status status-<?= $is_expired ? 'expired' : htmlspecialchars($c['status']) ?>">
                        <?= $is_expired ? 'EXPIRED' : strtoupper($c['status']) ?>
                    </span>
                    <?php if ($c['status'] === 'used'): ?>
                        <a href="?reset=<?= $c['id'] ?>" class="btn btn-amber btn-small" onclick="return confirm('Reset code for new 30 days?')">♻️ RENEW</a>
                    <?php endif; ?>
                    <a href="?delete=<?= $c['id'] ?>" class="btn btn-danger btn-small" onclick="return confirm('Delete code?')">🗑</a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="card">
            <p style="color:var(--muted);text-align:center;padding:30px;">No codes yet. Generate your first code! ☝️</p>
        </div>
    <?php endif; ?>
</div>

</body>
</html>