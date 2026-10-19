<?php
session_start();
include 'config/db.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// Auto-create admin if not exists
$check = $conn->query("SELECT id FROM admins LIMIT 1");
if ($check->num_rows === 0) {
    $hashed = password_hash('millz2025', PASSWORD_DEFAULT);
    $conn->query("INSERT INTO admins (username, password, role) VALUES ('admin', '$hashed', 'super')");
}

// Stats
$total_users = $conn->query("SELECT COUNT(*) as c FROM users")->fetch_assoc()['c'];
$total_pros = $conn->query("SELECT COUNT(*) as c FROM pros")->fetch_assoc()['c'];
$total_tipsters = $conn->query("SELECT COUNT(*) as c FROM tipsters")->fetch_assoc()['c'];
$total_tips = $conn->query("SELECT COUNT(*) as c FROM tips")->fetch_assoc()['c'];
$total_products = $conn->query("SELECT COUNT(*) as c FROM products")->fetch_assoc()['c'];
$total_messages = $conn->query("SELECT COUNT(*) as c FROM messages")->fetch_assoc()['c'];
$total_league = $conn->query("SELECT COUNT(*) as c FROM league_players WHERE name != 'TBD'")->fetch_assoc()['c'];
$total_games = $conn->query("SELECT COUNT(*) as c FROM games_store")->fetch_assoc()['c'];
$total_codes = $conn->query("SELECT COUNT(*) as c FROM access_codes")->fetch_assoc()['c'];
$active_codes = $conn->query("SELECT COUNT(*) as c FROM access_codes WHERE status='active'")->fetch_assoc()['c'];

$admin_name = $_SESSION['admin_username'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MILLZ ADMIN - Dashboard</title>
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;700;900&family=Rajdhani:wght@400;500;600;700&family=Bungee&display=swap" rel="stylesheet">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
:root { --bg: #1a0f00; --card: #2a1800; --border: #3a2400; --amber: #ff9500; --green: #00ff88; --red: #ff0033; --text: #fff; --muted: #b89860; }
body { font-family: 'Rajdhani', sans-serif; background: var(--bg); color: var(--text); min-height: 100vh; }
@keyframes gradientShift { 0% { background-position: 0% 50%; } 50% { background-position: 100% 50%; } 100% { background-position: 0% 50%; } }

.top-bar {
    background: linear-gradient(90deg, #2a1800, #1a0f00);
    border-bottom: 1px solid var(--border);
    padding: 15px 25px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: sticky;
    top: 0;
    z-index: 100;
    flex-wrap: wrap;
    gap: 10px;
}
.top-bar .logo {
    font-family: 'Bungee', cursive;
    font-size: 22px;
    background: linear-gradient(90deg, #ff9500, #00ff88, #ff9500);
    background-size: 300% 100%;
    animation: gradientShift 4s ease infinite;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    letter-spacing: 2px;
}
.top-bar .user-info { display: flex; align-items: center; gap: 12px; font-size: 14px; color: var(--muted); flex-wrap: wrap; }
.top-bar .user-info strong { color: var(--green); }
.logout-btn {
    padding: 8px 18px;
    background: transparent;
    border: 2px solid var(--red);
    color: var(--red);
    border-radius: 8px;
    font-family: 'Orbitron', sans-serif;
    font-size: 11px;
    font-weight: 900;
    letter-spacing: 1px;
    text-decoration: none;
    transition: 0.3s;
}
.logout-btn:hover { background: var(--red); color: #fff; }

.container { max-width: 1200px; margin: 0 auto; padding: 30px 20px; }

.welcome-box {
    background: linear-gradient(135deg, rgba(255,149,0,0.15), rgba(0,255,136,0.12));
    border: 1px solid var(--amber);
    border-radius: 16px;
    padding: 30px;
    margin-bottom: 30px;
    text-align: center;
}
.welcome-box h1 { font-family: 'Orbitron', sans-serif; font-size: 28px; margin-bottom: 8px; }
.welcome-box h1 span { color: var(--green); }
.welcome-box p { color: var(--muted); font-size: 14px; }

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 16px;
    margin-bottom: 40px;
}
.stat-card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 14px;
    padding: 20px;
    position: relative;
    overflow: hidden;
    transition: 0.3s;
}
.stat-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 3px;
    background: linear-gradient(90deg, var(--amber), var(--green), var(--amber));
    background-size: 200% 100%;
    animation: gradientShift 4s ease infinite;
}
.stat-card:hover { transform: translateY(-5px); border-color: var(--green); box-shadow: 0 15px 35px rgba(0,255,136,0.2); }
.stat-card .stat-icon { font-size: 28px; margin-bottom: 10px; }
.stat-card .stat-num {
    font-family: 'Orbitron', sans-serif;
    font-size: 28px;
    font-weight: 900;
    color: var(--green);
    text-shadow: 0 0 15px rgba(0,255,136,0.5);
    display: block;
    margin-bottom: 4px;
}
.stat-card .stat-label { color: var(--muted); font-size: 12px; text-transform: uppercase; letter-spacing: 1px; }

.section-title {
    font-family: 'Orbitron', sans-serif;
    font-size: 18px;
    margin-bottom: 20px;
    color: #fff;
    display: flex;
    align-items: center;
    gap: 10px;
}
.section-title::before {
    content: '';
    width: 4px;
    height: 22px;
    background: linear-gradient(180deg, var(--amber), var(--green));
    border-radius: 2px;
}

.menu-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 16px;
}
.menu-card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 14px;
    padding: 22px;
    text-decoration: none;
    color: var(--text);
    transition: 0.3s;
    display: flex;
    align-items: center;
    gap: 16px;
}
.menu-card:hover {
    transform: translateY(-4px);
    border-color: var(--green);
    background: rgba(0,255,136,0.06);
    box-shadow: 0 15px 35px rgba(0,255,136,0.2);
}
.menu-card .menu-icon {
    font-size: 28px;
    width: 55px;
    height: 55px;
    min-width: 55px;
    border-radius: 12px;
    background: linear-gradient(135deg, var(--amber), var(--green));
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid var(--green);
}
.menu-card .menu-info h3 {
    font-family: 'Orbitron', sans-serif;
    font-size: 15px;
    margin-bottom: 4px;
    color: #fff;
}
.menu-card .menu-info p { color: var(--muted); font-size: 12px; }

@media (max-width: 600px) {
    .welcome-box h1 { font-size: 22px; }
    .stat-num { font-size: 22px; }
    .top-bar { padding: 12px 15px; }
    .top-bar .logo { font-size: 18px; }
    .user-info { font-size: 12px; }
}
</style>
</head>
<body>

<div class="top-bar">
    <div class="logo">MILLZ ADMIN</div>
    <div class="user-info">
        👤 <strong><?= htmlspecialchars($admin_name) ?></strong>
        <a href="logout.php" class="logout-btn">LOGOUT</a>
    </div>
</div>

<div class="container">

    <div class="welcome-box">
        <h1>Welcome back, <span><?= htmlspecialchars($admin_name) ?></span> 👋</h1>
        <p>Manage MILLZ GAMES from here</p>
    </div>

    <div class="section-title">📊 Overview</div>
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">👥</div>
            <span class="stat-num"><?= $total_users ?></span>
            <span class="stat-label">Users</span>
        </div>
        <div class="stat-card">
            <div class="stat-icon">🎮</div>
            <span class="stat-num"><?= $total_games ?></span>
            <span class="stat-label">Games</span>
        </div>
        <div class="stat-card">
            <div class="stat-icon">🔑</div>
            <span class="stat-num"><?= $total_codes ?></span>
            <span class="stat-label">Access Codes</span>
        </div>
        <div class="stat-card">
            <div class="stat-icon">✅</div>
            <span class="stat-num"><?= $active_codes ?></span>
            <span class="stat-label">Active Codes</span>
        </div>
        <div class="stat-card">
            <div class="stat-icon">🎯</div>
            <span class="stat-num"><?= $total_pros ?></span>
            <span class="stat-label">Pros</span>
        </div>
        <div class="stat-card">
            <div class="stat-icon">💰</div>
            <span class="stat-num"><?= $total_tipsters ?></span>
            <span class="stat-label">Tipsters</span>
        </div>
        <div class="stat-card">
            <div class="stat-icon">📝</div>
            <span class="stat-num"><?= $total_tips ?></span>
            <span class="stat-label">Tips</span>
        </div>
        <div class="stat-card">
            <div class="stat-icon">🛒</div>
            <span class="stat-num"><?= $total_products ?></span>
            <span class="stat-label">Products</span>
        </div>
        <div class="stat-card">
            <div class="stat-icon">💬</div>
            <span class="stat-num"><?= $total_messages ?></span>
            <span class="stat-label">Messages</span>
        </div>
        <div class="stat-card">
            <div class="stat-icon">🏆</div>
            <span class="stat-num"><?= $total_league ?></span>
            <span class="stat-label">League Players</span>
        </div>
    </div>

    <div class="section-title">🎮 Games Store</div>
    <div class="menu-grid" style="margin-bottom:40px;">
        <a href="games.php" class="menu-card">
            <div class="menu-icon">🎮</div>
            <div class="menu-info">
                <h3>Manage Games</h3>
                <p>Add games + download links</p>
            </div>
        </a>
        <a href="codes.php" class="menu-card">
            <div class="menu-icon">🔑</div>
            <div class="menu-info">
                <h3>Access Codes</h3>
                <p>Generate codes for paid users</p>
            </div>
        </a>
    </div>

    <div class="section-title">⚡ Site Management</div>
    <div class="menu-grid">
        <a href="tips.php" class="menu-card">
            <div class="menu-icon">💰</div>
            <div class="menu-info">
                <h3>Manage Tips</h3>
                <p>Add / edit betting tips</p>
            </div>
        </a>
        <a href="pros.php" class="menu-card">
            <div class="menu-icon">🎯</div>
            <div class="menu-info">
                <h3>Manage Pros</h3>
                <p>Add / edit pro players</p>
            </div>
        </a>
        <a href="tipsters.php" class="menu-card">
            <div class="menu-icon">🏅</div>
            <div class="menu-info">
                <h3>Manage Tipsters</h3>
                <p>Add / edit tipsters</p>
            </div>
        </a>
        <a href="league.php" class="menu-card">
            <div class="menu-icon">🏆</div>
            <div class="menu-info">
                <h3>Manage League</h3>
                <p>Players & scores</p>
            </div>
        </a>
        <a href="products.php" class="menu-card">
            <div class="menu-icon">🛒</div>
            <div class="menu-info">
                <h3>Manage Shop</h3>
                <p>Products & orders</p>
            </div>
        </a>
        <a href="users.php" class="menu-card">
            <div class="menu-icon">👥</div>
            <div class="menu-info">
                <h3>Users</h3>
                <p>See registered users</p>
            </div>
        </a>
        <a href="messages.php" class="menu-card">
            <div class="menu-icon">💬</div>
            <div class="menu-info">
                <h3>Community</h3>
                <p>See chat messages</p>
            </div>
        </a>
        <a href="http://localhost/millzgames/" target="_blank" class="menu-card">
            <div class="menu-icon">🌐</div>
            <div class="menu-info">
                <h3>View Site</h3>
                <p>Open public website</p>
            </div>
        </a>
    </div>

</div>

</body>
</html>