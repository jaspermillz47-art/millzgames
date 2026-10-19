<?php
session_start();
include 'config/db.php';

$error = '';

if (isset($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Fill in all fields.';
    } else {
        $stmt = $conn->prepare("SELECT * FROM admins WHERE username = ? LIMIT 1");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $res = $stmt->get_result();
        $admin = $res->fetch_assoc();

        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['admin_role'] = $admin['role'];
            $conn->query("UPDATE admins SET last_login = NOW() WHERE id = " . (int)$admin['id']);
            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Invalid username or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MILLZ ADMIN - Login</title>
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;700;900&family=Rajdhani:wght@400;500;600;700&family=Bungee&display=swap" rel="stylesheet">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
:root {
    --bg: #000;
    --card: #0f0f0f;
    --border: #2a2a2a;
    --red: #ff0033;
    --red-light: #ff3366;
    --red-dark: #cc0029;
    --green: #00cc66;
    --text: #fff;
    --muted: #888;
}
body { font-family: 'Rajdhani', sans-serif; background: var(--bg); color: var(--text); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
@keyframes gradientShift { 0% { background-position: 0% 50%; } 50% { background-position: 100% 50%; } 100% { background-position: 0% 50%; } }
@keyframes floatY { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-5px); } }
.auth-wrap { width: 100%; max-width: 440px; }
.auth-logo { text-align: center; margin-bottom: 28px; }
.auth-logo .logo-text {
    font-family: 'Bungee', cursive;
    font-size: 38px;
    letter-spacing: 2px;
    background: linear-gradient(90deg, #ff0033, #00cc66, #ff0033);
    background-size: 300% 100%;
    animation: gradientShift 4s ease infinite, floatY 3s ease-in-out infinite;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    filter: drop-shadow(0 0 15px rgba(255,0,51,0.7));
}
.auth-logo p { color: var(--muted); font-size: 12px; letter-spacing: 5px; margin-top: 8px; font-family: 'Orbitron', sans-serif; }
.auth-card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 35px 30px;
    box-shadow: 0 0 60px rgba(255,0,51,0.15);
    position: relative;
    overflow: hidden;
}
.auth-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 3px;
    background: linear-gradient(90deg, var(--red), var(--green), var(--red));
    background-size: 200% 100%;
    animation: gradientShift 4s ease infinite;
}
.auth-title { text-align: center; font-size: 20px; margin-bottom: 6px; color: #fff; font-family: 'Orbitron', sans-serif; }
.auth-sub { text-align: center; color: var(--muted); font-size: 13px; margin-bottom: 24px; }
.alert { padding: 12px 16px; border-radius: 8px; font-size: 14px; margin-bottom: 16px; background: rgba(255,0,51,0.15); border: 1px solid var(--red); color: var(--red-light); font-weight: 600; }
.form-group { margin-bottom: 16px; }
.form-group label { display: block; font-size: 11px; letter-spacing: 2px; text-transform: uppercase; color: var(--muted); margin-bottom: 6px; font-family: 'Orbitron', sans-serif; }
.form-group input { width: 100%; padding: 13px 16px; border-radius: 8px; border: 2px solid var(--border); background: #1a1a1a; color: #fff; font-size: 15px; font-family: 'Rajdhani', sans-serif; transition: 0.3s; }
.form-group input:focus { outline: none; border-color: var(--green); box-shadow: 0 0 20px rgba(0,204,102,0.3); }
.auth-btn {
    width: 100%; padding: 14px; margin-top: 8px;
    background: linear-gradient(90deg, var(--red), var(--green), var(--red));
    background-size: 300% 100%;
    animation: gradientShift 3s ease infinite;
    color: #fff; border: none; border-radius: 8px;
    font-family: 'Orbitron', sans-serif; font-weight: 900;
    font-size: 14px; letter-spacing: 2px; cursor: pointer; transition: 0.3s;
}
.auth-btn:hover { transform: translateY(-2px); box-shadow: 0 10px 30px rgba(0,204,102,0.4); }
.hint { margin-top: 20px; padding: 12px; background: rgba(0,204,102,0.08); border: 1px dashed var(--green); border-radius: 8px; color: var(--muted); font-size: 12px; text-align: center; line-height: 1.7; }
.hint strong { color: var(--green); }
</style>
</head>
<body>

<div class="auth-wrap">
    <div class="auth-logo">
        <div class="logo-text">MILLZ ADMIN</div>
        <p>CONTROL PANEL</p>
    </div>

    <div class="auth-card">
        <h2 class="auth-title">Admin Login</h2>
        <p class="auth-sub">Authorized personnel only</p>

        <?php if (!empty($error)): ?>
            <div class="alert"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" placeholder="admin" required autofocus>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Your password" required>
            </div>
            <button type="submit" class="auth-btn">LOGIN</button>
        </form>

        <div class="hint">
            <strong>First time?</strong><br>
            Login with <strong>admin</strong> / <strong>millz2025</strong><br>
            Then change password in dashboard.
        </div>
    </div>
</div>

</body>
</html>