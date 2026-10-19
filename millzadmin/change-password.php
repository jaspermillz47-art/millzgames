<?php
session_start();
include 'config/db.php';

$msg = '';
$msg_type = 'success';

if (isset($_SESSION['admin_id'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $new_password = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm'] ?? '';

        if (strlen($new_password) < 6) {
            $msg = 'Password must be at least 6 characters.';
            $msg_type = 'error';
        } elseif ($new_password !== $confirm) {
            $msg = 'Passwords do not match.';
            $msg_type = 'error';
        } else {
            $hashed = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE admins SET password = ? WHERE id = ?");
            $stmt->bind_param("si", $hashed, $_SESSION['admin_id']);
            if ($stmt->execute()) {
                $msg = '✅ Password changed! Now DELETE this file (change-password.php).';
            } else {
                $msg = 'Error: ' . $conn->error;
                $msg_type = 'error';
            }
        }
    }
} else {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Change Password</title>
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;700;900&family=Rajdhani:wght@400;500;600;700&family=Bungee&display=swap" rel="stylesheet">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
:root { --bg: #000; --card: #0f0f0f; --border: #2a2a2a; --red: #ff0033; --green: #00cc66; --text: #fff; --muted: #888; }
body { font-family: 'Rajdhani', sans-serif; background: var(--bg); color: var(--text); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
@keyframes gradientShift { 0% { background-position: 0% 50%; } 50% { background-position: 100% 50%; } 100% { background-position: 0% 50%; } }
.wrap { width: 100%; max-width: 440px; }
.logo { text-align: center; font-family: 'Bungee'; font-size: 28px; color: var(--green); text-shadow: 0 0 20px rgba(0,204,102,0.6); margin-bottom: 24px; letter-spacing: 2px; }
.card { background: var(--card); border: 1px solid var(--border); border-radius: 16px; padding: 32px; position: relative; overflow: hidden; box-shadow: 0 0 60px rgba(255,0,51,0.15); }
.card::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px; background: linear-gradient(90deg, var(--red), var(--green), var(--red)); background-size: 200% 100%; animation: gradientShift 4s ease infinite; }
h1 { font-family: 'Orbitron'; font-size: 18px; text-align: center; margin-bottom: 24px; color: #fff; }
.alert { padding: 12px 16px; border-radius: 8px; font-size: 14px; margin-bottom: 16px; font-weight: 600; }
.alert.success { background: rgba(0,204,102,0.15); border: 1px solid var(--green); color: var(--green); }
.alert.error { background: rgba(255,0,51,0.15); border: 1px solid var(--red); color: #ff6666; }
.form-group { margin-bottom: 16px; }
.form-group label { display: block; font-size: 11px; letter-spacing: 2px; text-transform: uppercase; color: var(--muted); margin-bottom: 6px; font-family: 'Orbitron'; }
.form-group input { width: 100%; padding: 13px 16px; border-radius: 8px; border: 2px solid var(--border); background: #1a1a1a; color: #fff; font-size: 15px; font-family: 'Rajdhani', sans-serif; }
.form-group input:focus { outline: none; border-color: var(--green); box-shadow: 0 0 20px rgba(0,204,102,0.3); }
.btn { width: 100%; padding: 14px; margin-top: 8px; background: linear-gradient(90deg, var(--red), var(--green), var(--red)); background-size: 300% 100%; animation: gradientShift 3s ease infinite; color: #fff; border: none; border-radius: 8px; font-family: 'Orbitron'; font-weight: 900; font-size: 13px; letter-spacing: 2px; cursor: pointer; transition: 0.3s; }
.btn:hover { transform: translateY(-2px); box-shadow: 0 10px 30px rgba(0,204,102,0.4); }
.back { display: block; text-align: center; margin-top: 16px; color: var(--muted); font-size: 13px; text-decoration: none; }
.back:hover { color: var(--green); }
</style>
</head>
<body>

<div class="wrap">
    <div class="logo">MILLZ ADMIN</div>
    <div class="card">
        <h1>🔐 Change Password</h1>

        <?php if (!empty($msg)): ?>
            <div class="alert <?= $msg_type ?>"><?= htmlspecialchars($msg) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>New Password</label>
                <input type="password" name="new_password" placeholder="Min 6 characters" minlength="6" required autofocus>
            </div>
            <div class="form-group">
                <label>Confirm New Password</label>
                <input type="password" name="confirm" placeholder="Repeat password" minlength="6" required>
            </div>
            <button type="submit" class="btn">CHANGE PASSWORD</button>
        </form>

        <a href="dashboard.php" class="back">← Back to Dashboard</a>
    </div>
</div>

</body>
</html>