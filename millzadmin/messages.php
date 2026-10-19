<?php
session_start();
include 'config/db.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// DELETE MESSAGE
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM messages WHERE id = $id");
    header('Location: messages.php'); exit;
}

// CLEAR ALL
if (isset($_GET['clear'])) {
    $conn->query("TRUNCATE TABLE messages");
    header('Location: messages.php'); exit;
}

$messages = [];
$res = $conn->query("SELECT * FROM messages ORDER BY created_at DESC LIMIT 200");
while ($row = $res->fetch_assoc()) $messages[] = $row;

$total = $conn->query("SELECT COUNT(*) as c FROM messages")->fetch_assoc()['c'];
$admin_name = $_SESSION['admin_username'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MILLZ ADMIN - Messages</title>
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;700;900&family=Rajdhani:wght@400;500;600;700&family=Bungee&display=swap" rel="stylesheet">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
:root { --bg: #000; --card: #0f0f0f; --border: #2a2a2a; --red: #ff0033; --red-light: #ff3366; --green: #00cc66; --text: #fff; --muted: #888; }
body { font-family: 'Rajdhani', sans-serif; background: var(--bg); color: var(--text); min-height: 100vh; }
@keyframes gradientShift { 0% { background-position: 0% 50%; } 50% { background-position: 100% 50%; } 100% { background-position: 0% 50%; } }
@keyframes slideIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
.top-bar { background: linear-gradient(90deg, #0f0f0f, #000); border-bottom: 1px solid var(--border); padding: 15px 25px; display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 100; }
.top-bar .logo { font-family: 'Bungee', cursive; font-size: 20px; background: linear-gradient(90deg, #ff0033, #00cc66, #ff0033); background-size: 300% 100%; animation: gradientShift 4s ease infinite; -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; letter-spacing: 2px; }
.user-info { display: flex; align-items: center; gap: 12px; font-size: 13px; color: var(--muted); }
.user-info strong { color: var(--green); }
.back-btn, .logout-btn, .clear-btn { padding: 7px 14px; background: transparent; border: 2px solid var(--red); color: var(--red); border-radius: 8px; font-family: 'Orbitron', sans-serif; font-size: 10px; font-weight: 900; letter-spacing: 1px; text-decoration: none; transition: 0.3s; }
.back-btn { border-color: var(--green); color: var(--green); }
.back-btn:hover { background: var(--green); color: #000; }
.clear-btn:hover, .logout-btn:hover { background: var(--red); color: #fff; }
.container { max-width: 1000px; margin: 0 auto; padding: 30px 20px; }
.page-title { font-family: 'Orbitron', sans-serif; font-size: 24px; margin-bottom: 6px; }
.page-sub { color: var(--muted); font-size: 14px; margin-bottom: 30px; }

.stats-row { display: flex; gap: 12px; margin-bottom: 24px; flex-wrap: wrap; align-items: center; }
.stat-pill { background: var(--card); border: 1px solid var(--border); border-radius: 30px; padding: 10px 20px; font-size: 13px; color: var(--muted); }
.stat-pill strong { color: var(--green); font-family: 'Orbitron', sans-serif; }

.messages-list { display: flex; flex-direction: column; gap: 12px; }
.msg-card {
    background: var(--card); border: 1px solid var(--border);
    border-radius: 14px; padding: 18px 22px;
    display: flex; gap: 16px; align-items: flex-start;
    transition: 0.3s; position: relative; overflow: hidden;
    animation: slideIn 0.3s ease;
}
.msg-card::before {
    content: ''; position: absolute; left: 0; top: 0; bottom: 0;
    width: 3px; background: linear-gradient(180deg, var(--red), var(--green));
}
.msg-card:hover { border-color: var(--green); transform: translateX(4px); }

.msg-avatar {
    width: 50px; height: 50px; min-width: 50px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-family: 'Orbitron', sans-serif; font-weight: 900;
    font-size: 20px; color: #fff;
    background: linear-gradient(135deg, var(--red), var(--green));
    border: 2px solid var(--green);
}
.msg-body { flex: 1; min-width: 0; }
.msg-header { display: flex; align-items: center; gap: 12px; margin-bottom: 6px; flex-wrap: wrap; }
.msg-username { font-family: 'Orbitron', sans-serif; font-size: 14px; color: var(--green); letter-spacing: 0.5px; }
.msg-time { font-size: 12px; color: var(--muted); }
.msg-text { color: var(--text); font-size: 15px; word-wrap: break-word; line-height: 1.5; }
.msg-actions { display: flex; gap: 8px; flex-shrink: 0; }

.btn { padding: 8px 14px; border: none; border-radius: 8px; font-family: 'Orbitron', sans-serif; font-weight: 900; font-size: 10px; letter-spacing: 1px; cursor: pointer; transition: 0.3s; text-decoration: none; display: inline-block; text-align: center; }
.btn-danger { background: transparent; border: 2px solid var(--red); color: var(--red); }
.btn-danger:hover { background: var(--red); color: #fff; }

.empty { text-align: center; padding: 60px 20px; background: var(--card); border: 2px dashed var(--border); border-radius: 16px; color: var(--muted); }
.empty .icon { font-size: 60px; margin-bottom: 12px; opacity: 0.6; }

@media (max-width: 700px) {
    .top-bar { padding: 12px 15px; }
    .top-bar .logo { font-size: 15px; }
    .user-info { font-size: 11px; gap: 6px; }
    .back-btn, .logout-btn, .clear-btn { padding: 5px 10px; font-size: 9px; }
    .page-title { font-size: 18px; }
    .msg-card { padding: 14px; gap: 12px; }
    .msg-avatar { width: 42px; height: 42px; min-width: 42px; font-size: 16px; }
    .msg-text { font-size: 13px; }
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
    <h1 class="page-title">💬 Community Messages</h1>
    <p class="page-sub">See and moderate the MILLZ community chat</p>

    <div class="stats-row">
        <span class="stat-pill">Total Messages: <strong><?= $total ?></strong></span>
        <?php if ($total > 0): ?>
            <a href="?clear=1" class="btn btn-danger" onclick="return confirm('Delete ALL messages? This cannot be undone!')">🗑 CLEAR ALL</a>
        <?php endif; ?>
    </div>

    <?php if (count($messages) > 0): ?>
        <div class="messages-list">
            <?php foreach ($messages as $m): ?>
                <div class="msg-card">
                    <div class="msg-avatar">
                        <?= strtoupper(substr($m['username'] ?? 'G', 0, 1)) ?>
                    </div>
                    <div class="msg-body">
                        <div class="msg-header">
                            <span class="msg-username"><?= htmlspecialchars($m['username'] ?? 'Guest') ?></span>
                            <span class="msg-time">🕐 <?= date('M j, Y · H:i', strtotime($m['created_at'])) ?></span>
                        </div>
                        <div class="msg-text"><?= htmlspecialchars($m['message']) ?></div>
                    </div>
                    <div class="msg-actions">
                        <a href="?delete=<?= $m['id'] ?>" class="btn btn-danger" onclick="return confirm('Delete this message?')">🗑</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty">
            <div class="icon">💬</div>
            <p>No messages yet. Community is quiet! 🤫</p>
        </div>
    <?php endif; ?>
</div>

</body>
</html>