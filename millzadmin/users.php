<?php
session_start();
include 'config/db.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$msg = '';
$msg_type = 'success';

// DELETE USER
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM users WHERE id = $id");
    header('Location: users.php'); exit;
}

// BAN/UNBAN USER
if (isset($_GET['toggle'])) {
    $id = (int)$_GET['toggle'];
    $res = $conn->query("SELECT status FROM users WHERE id = $id");
    $u = $res->fetch_assoc();
    $new = ($u['status'] === 'active') ? 'banned' : 'active';
    $conn->query("UPDATE users SET status = '$new' WHERE id = $id");
    header('Location: users.php'); exit;
}

// Search
$search = isset($_GET['q']) ? trim($_GET['q']) : '';
$where = '';
if (!empty($search)) {
    $s = $conn->real_escape_string($search);
    $where = "WHERE username LIKE '%$s%' OR phone LIKE '%$s%'";
}

$users = [];
$res = $conn->query("SELECT * FROM users $where ORDER BY id DESC");
while ($row = $res->fetch_assoc()) $users[] = $row;

$total = $conn->query("SELECT COUNT(*) as c FROM users")->fetch_assoc()['c'];
$admin_name = $_SESSION['admin_username'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MILLZ ADMIN - Users</title>
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
.container { max-width: 1200px; margin: 0 auto; padding: 30px 20px; }
.page-title { font-family: 'Orbitron', sans-serif; font-size: 24px; margin-bottom: 6px; }
.page-sub { color: var(--muted); font-size: 14px; margin-bottom: 30px; }

.search-form {
    display: flex; gap: 10px; margin-bottom: 24px;
    background: var(--card); border: 1px solid var(--border);
    border-radius: 12px; padding: 12px;
}
.search-form input {
    flex: 1; padding: 12px 16px; border-radius: 8px;
    border: 2px solid var(--border); background: #1a1a1a;
    color: #fff; font-size: 14px; font-family: 'Rajdhani', sans-serif;
}
.search-form input:focus { outline: none; border-color: var(--green); }
.search-form button {
    padding: 12px 24px; background: linear-gradient(90deg, var(--red), var(--green));
    color: #fff; border: none; border-radius: 8px;
    font-family: 'Orbitron', sans-serif; font-weight: 900;
    font-size: 11px; letter-spacing: 1px; cursor: pointer; transition: 0.3s;
}
.search-form button:hover { transform: translateY(-2px); box-shadow: 0 10px 25px rgba(0,204,102,0.4); }

.stats-row {
    display: flex; gap: 12px; margin-bottom: 24px; flex-wrap: wrap;
}
.stat-pill {
    background: var(--card); border: 1px solid var(--border);
    border-radius: 30px; padding: 10px 20px;
    font-size: 13px; color: var(--muted);
}
.stat-pill strong { color: var(--green); font-family: 'Orbitron', sans-serif; }

.table-wrap { background: var(--card); border: 1px solid var(--border); border-radius: 16px; padding: 5px; overflow-x: auto; }
.table { width: 100%; border-collapse: collapse; min-width: 800px; }
.table thead { background: linear-gradient(90deg, var(--red), var(--green), var(--red)); background-size: 300% 100%; animation: gradientShift 5s ease infinite; }
.table th { padding: 14px 10px; text-align: center; color: #fff; font-family: 'Orbitron', sans-serif; font-weight: 900; font-size: 12px; letter-spacing: 1px; text-transform: uppercase; }
.table th:nth-child(2), .table th:nth-child(3) { text-align: left; }
.table td { padding: 12px 10px; text-align: center; color: var(--text); font-size: 14px; border-bottom: 1px solid var(--border); }
.table td:nth-child(2), .table td:nth-child(3) { text-align: left; }
.table tbody tr:hover { background: rgba(0,204,102,0.05); }
.table .pts { font-family: 'Orbitron', sans-serif; font-weight: 900; color: var(--green); }

.avatar-cell {
    display: flex; align-items: center; gap: 12px;
}
.avatar-circle {
    width: 42px; height: 42px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-family: 'Orbitron', sans-serif; font-weight: 900;
    font-size: 16px; color: #fff; flex-shrink: 0;
    border: 2px solid var(--green);
}

.status-badge { padding: 3px 10px; border-radius: 20px; font-size: 10px; font-weight: 900; font-family: 'Orbitron', sans-serif; text-transform: uppercase; }
.status-active { background: rgba(0,204,102,0.2); color: var(--green); border: 1px solid var(--green); }
.status-banned { background: rgba(255,0,51,0.2); color: var(--red); border: 1px solid var(--red); }

.btn { padding: 8px 14px; border: none; border-radius: 8px; font-family: 'Orbitron', sans-serif; font-weight: 900; font-size: 10px; letter-spacing: 1px; cursor: pointer; transition: 0.3s; text-decoration: none; display: inline-block; text-align: center; }
.btn-danger { background: transparent; border: 2px solid var(--red); color: var(--red); }
.btn-danger:hover { background: var(--red); color: #fff; }
.btn-warn { background: transparent; border: 2px solid #ffcc00; color: #ffcc00; }
.btn-warn:hover { background: #ffcc00; color: #000; }
.btn-ok { background: transparent; border: 2px solid var(--green); color: var(--green); }
.btn-ok:hover { background: var(--green); color: #000; }

.empty { text-align: center; padding: 60px 20px; background: var(--card); border: 2px dashed var(--border); border-radius: 16px; color: var(--muted); }
.empty .icon { font-size: 60px; margin-bottom: 12px; opacity: 0.6; }

@media (max-width: 700px) {
    .top-bar { padding: 12px 15px; }
    .top-bar .logo { font-size: 15px; }
    .user-info { font-size: 11px; gap: 6px; }
    .back-btn, .logout-btn { padding: 5px 10px; font-size: 9px; }
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
    <h1 class="page-title">👥 Registered Users</h1>
    <p class="page-sub">See everyone who signed up on MILLZ GAMES</p>

    <div class="stats-row">
        <span class="stat-pill">Total Users: <strong><?= $total ?></strong></span>
        <?php if (!empty($search)): ?>
            <span class="stat-pill">Found: <strong><?= count($users) ?></strong> for "<?= htmlspecialchars($search) ?>"</span>
        <?php endif; ?>
    </div>

    <form class="search-form" method="GET">
        <input type="text" name="q" placeholder="Search by username or phone..." value="<?= htmlspecialchars($search) ?>">
        <button type="submit">🔍 SEARCH</button>
    </form>

    <?php if (count($users) > 0): ?>
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Phone</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Joined</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td><?= $u['id'] ?></td>
                            <td>
                                <div class="avatar-cell">
                                    <div class="avatar-circle" style="background: linear-gradient(135deg, <?= htmlspecialchars($u['avatar_color'] ?? '#ff0033') ?>, #000);">
                                        <?= strtoupper(substr($u['username'], 0, 1)) ?>
                                    </div>
                                    <strong><?= htmlspecialchars($u['username']) ?></strong>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($u['phone']) ?></td>
                            <td><?= htmlspecialchars($u['role'] ?? 'user') ?></td>
                            <td>
                                <span class="status-badge status-<?= htmlspecialchars($u['status'] ?? 'active') ?>">
                                    <?= htmlspecialchars($u['status'] ?? 'active') ?>
                                </span>
                            </td>
                            <td style="font-size: 12px; color: var(--muted);">
                                <?= date('M j, Y', strtotime($u['created_at'])) ?>
                            </td>
                            <td>
                                <div style="display: flex; gap: 6px; justify-content: center;">
                                    <a href="?toggle=<?= $u['id'] ?>" class="btn <?= ($u['status'] ?? 'active') === 'active' ? 'btn-warn' : 'btn-ok' ?>" onclick="return confirm('Change status?')">
                                        <?= ($u['status'] ?? 'active') === 'active' ? '⏸ BAN' : '▶ UNBAN' ?>
                                    </a>
                                    <a href="?delete=<?= $u['id'] ?>" class="btn btn-danger" onclick="return confirm('Delete this user permanently?')">🗑</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="empty">
            <div class="icon">👥</div>
            <p><?= !empty($search) ? 'No users found for "' . htmlspecialchars($search) . '"' : 'No users yet.' ?></p>
        </div>
    <?php endif; ?>
</div>

</body>
</html>