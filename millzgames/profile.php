<?php
session_start();
include 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();
$user = $res->fetch_assoc();

if (!$user) {
    session_destroy();
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MILLZ GAMES - My Profile</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;700;900&family=Rajdhani:wght@400;500;600;700&family=Bungee&display=swap" rel="stylesheet">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
:root {
    --bg: #000000;
    --bg-2: #0a0000;
    --card: #1a0000;
    --border: #330000;
    --red: #ff0033;
    --red-dark: #cc0029;
    --red-light: #ff3366;
    --text: #ffffff;
    --muted: #a0a0a0;
}
body { font-family: 'Rajdhani', sans-serif; background: var(--bg); color: var(--text); min-height: 100vh; overflow-x: hidden; }
h1, h2, h3 { font-family: 'Orbitron', sans-serif; letter-spacing: 1px; }
a { text-decoration: none; color: inherit; }

@keyframes gradientShift { 0% { background-position: 0% 50%; } 50% { background-position: 100% 50%; } 100% { background-position: 0% 50%; } }
@keyframes twinkle { 0%, 100% { opacity: 0.2; transform: scale(0.8); } 50% { opacity: 1; transform: scale(1.3); } }
@keyframes floatY { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-10px); } }

.effects-layer { position: fixed; inset: 0; z-index: 0; pointer-events: none; overflow: hidden; }
.stars { position: absolute; inset: 0; }
.star { position: absolute; width: 2px; height: 2px; background: #fff; border-radius: 50%; animation: twinkle 3s ease-in-out infinite; box-shadow: 0 0 6px rgba(255,255,255,0.8); }

.profile-wrap { position: relative; z-index: 1; max-width: 700px; margin: 0 auto; padding: 60px 20px; }
.profile-card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 20px;
    padding: 40px 30px;
    text-align: center;
    box-shadow: 0 0 60px rgba(255,0,51,0.25);
    position: relative;
    overflow: hidden;
}
.profile-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 3px;
    background: linear-gradient(90deg, var(--red), var(--red-light), var(--red));
    background-size: 200% 100%;
    animation: gradientShift 4s ease infinite;
}
.profile-avatar {
    width: 130px;
    height: 130px;
    margin: 0 auto 22px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: 'Orbitron', sans-serif;
    font-weight: 900;
    font-size: 56px;
    color: #fff;
    box-shadow: 0 0 0 5px rgba(255,0,51,0.3), 0 0 60px rgba(255,0,51,0.7);
    animation: floatY 3s ease-in-out infinite;
    text-shadow: 0 2px 4px rgba(0,0,0,0.6);
    background: linear-gradient(135deg, <?= htmlspecialchars($user['avatar_color']) ?>, #000);
}
.profile-name {
    font-family: 'Bungee', cursive;
    font-size: clamp(28px, 5vw, 44px);
    letter-spacing: 2px;
    line-height: 1;
    margin-bottom: 8px;
    background: linear-gradient(90deg, #ff0033, #ff3366, #ff0033);
    background-size: 300% 100%;
    animation: gradientShift 3s ease infinite;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    text-transform: uppercase;
}
.profile-phone {
    color: var(--muted);
    font-size: 15px;
    margin-bottom: 24px;
}
.profile-stats {
    display: flex;
    gap: 20px;
    justify-content: center;
    flex-wrap: wrap;
    margin: 30px 0;
}
.stat-box {
    background: var(--bg-2);
    border: 1px solid var(--border);
    border-radius: 14px;
    padding: 16px 22px;
    min-width: 110px;
    transition: 0.3s;
}
.stat-box:hover {
    border-color: var(--red);
    transform: translateY(-4px);
    box-shadow: 0 10px 30px rgba(255,0,51,0.3);
}
.stat-num {
    display: block;
    font-family: 'Orbitron', sans-serif;
    font-size: 24px;
    font-weight: 900;
    background: linear-gradient(90deg, var(--red), var(--red-light), var(--red));
    background-size: 200% 100%;
    animation: gradientShift 3s ease infinite;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-bottom: 4px;
}
.stat-label {
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: var(--muted);
}
.profile-actions {
    display: flex;
    gap: 12px;
    justify-content: center;
    flex-wrap: wrap;
    margin-top: 20px;
}
.btn-solid, .btn-outline {
    display: inline-block;
    padding: 12px 28px;
    border-radius: 10px;
    font-weight: 700;
    font-size: 14px;
    cursor: pointer;
    transition: 0.3s;
    border: 2px solid transparent;
    font-family: 'Orbitron', sans-serif;
    letter-spacing: 1px;
    text-align: center;
}
.btn-solid {
    background: linear-gradient(90deg, var(--red), var(--red-light), var(--red-dark), var(--red));
    background-size: 300% 100%;
    animation: gradientShift 4s ease infinite;
    color: #fff;
    box-shadow: 0 0 20px rgba(255,0,51,0.7);
    font-weight: 900;
}
.btn-solid:hover {
    box-shadow: 0 0 35px rgba(255,0,51,1);
    transform: translateY(-2px);
}
.btn-outline {
    background: transparent;
    color: var(--red);
    border: 2px solid var(--red);
}
.btn-outline:hover {
    background: var(--red);
    color: #fff;
    box-shadow: 0 0 25px rgba(255,0,51,0.6);
}
</style>
</head>
<body>

<div class="effects-layer">
    <div class="stars" id="stars"></div>
</div>

<div class="profile-wrap">
    <div class="profile-card">
        <div class="profile-avatar">
            <?= strtoupper(substr($user['username'], 0, 1)) ?>
        </div>
        <h1 class="profile-name"><?= htmlspecialchars($user['username']) ?></h1>
        <p class="profile-phone">📱 <?= htmlspecialchars($user['phone']) ?></p>

        <div class="profile-stats">
            <div class="stat-box">
                <span class="stat-num">0</span>
                <span class="stat-label">Matches</span>
            </div>
            <div class="stat-box">
                <span class="stat-num">0</span>
                <span class="stat-label">Wins</span>
            </div>
            <div class="stat-box">
                <span class="stat-num">TZS 0</span>
                <span class="stat-label">Earned</span>
            </div>
        </div>

        <div class="profile-actions">
            <a href="playwithpro.php" class="btn-solid">🎯 Play Now</a>
            <a href="community.php" class="btn-outline">💬 Community</a>
            <a href="logout.php" class="btn-outline">🚪 Logout</a>
        </div>
    </div>
</div>

<script>
function createStars() {
    const c = document.getElementById('stars'); if (!c) return;
    for (let i = 0; i < 70; i++) {
        const s = document.createElement('div');
        s.className = 'star';
        s.style.left = Math.random() * 100 + '%';
        s.style.top = Math.random() * 100 + '%';
        const size = 1 + Math.random() * 3;
        s.style.width = size + 'px'; s.style.height = size + 'px';
        s.style.animationDelay = Math.random() * 3 + 's';
        s.style.animationDuration = (2 + Math.random() * 3) + 's';
        c.appendChild(s);
    }
}
createStars();
</script>
</body>
</html>