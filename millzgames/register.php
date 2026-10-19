<?php
session_start();
include 'config/db.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';

    // Validation
    if (empty($username) || empty($phone) || empty($password)) {
        $error = 'Tafadhali jaza sehemu zote.';
    } elseif (strlen($username) < 3) {
        $error = 'Jina linahitaji herufi 3 au zaidi.';
    } elseif (!preg_match('/^[0-9]{10,15}$/', $phone)) {
        $error = 'Namba ya simu si sahihi (10-15 digits).';
    } elseif (strlen($password) < 6) {
        $error = 'Neno la siri linahitaji herufi 6 au zaidi.';
    } elseif ($password !== $confirm) {
        $error = 'Neno la siri hazifanani.';
    } else {
        // Check if username exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR phone = ? LIMIT 1");
        $stmt->bind_param("ss", $username, $phone);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res->num_rows > 0) {
            $error = 'Jina au namba ya simu imetumika tayari.';
        } else {
            // Insert
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $color = '#' . substr(md5($username), 0, 6);
            $stmt = $conn->prepare("INSERT INTO users (username, phone, password, avatar_color) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $username, $phone, $hashed, $color);
            if ($stmt->execute()) {
                $success = 'Umefanikiwa kujiunga! Unaweza kuingia sasa.';
            } else {
                $error = 'Kuna tatizo. Jaribu tena.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MILLZ GAMES - Sign Up</title>
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
body { font-family: 'Rajdhani', sans-serif; background: var(--bg); color: var(--text); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; overflow-x: hidden; }
h1, h2, h3 { font-family: 'Orbitron', sans-serif; letter-spacing: 1px; }
a { text-decoration: none; color: inherit; }
img { max-width: 100%; display: block; }

@keyframes gradientShift { 0% { background-position: 0% 50%; } 50% { background-position: 100% 50%; } 100% { background-position: 0% 50%; } }
@keyframes twinkle { 0%, 100% { opacity: 0.2; transform: scale(0.8); } 50% { opacity: 1; transform: scale(1.3); } }
@keyframes pulse { 0%, 100% { box-shadow: 0 0 0 0 rgba(255,0,51,0.7); } 50% { box-shadow: 0 0 0 15px rgba(255,0,51,0); } }

.effects-layer { position: fixed; inset: 0; z-index: 0; pointer-events: none; overflow: hidden; }
.stars { position: absolute; inset: 0; }
.star { position: absolute; width: 2px; height: 2px; background: #fff; border-radius: 50%; animation: twinkle 3s ease-in-out infinite; box-shadow: 0 0 6px rgba(255,255,255,0.8); }

.auth-wrap { position: relative; z-index: 1; width: 100%; max-width: 460px; }
.auth-logo {
    text-align: center;
    margin-bottom: 30px;
}
.auth-logo .logo-text {
    font-family: 'Bungee', cursive;
    font-size: clamp(28px, 6vw, 42px);
    letter-spacing: 2px;
    background: linear-gradient(90deg, #ff0033, #ff3366, #ff0033);
    background-size: 300% 100%;
    animation: gradientShift 4s ease infinite;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    filter: drop-shadow(0 0 15px rgba(255,0,51,0.8));
    text-transform: uppercase;
}
.auth-logo p {
    color: var(--muted);
    font-size: 14px;
    margin-top: 6px;
    letter-spacing: 3px;
    text-transform: uppercase;
    font-family: 'Orbitron', sans-serif;
}

.auth-card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 20px;
    padding: 35px 30px;
    box-shadow: 0 0 60px rgba(255,0,51,0.25);
    position: relative;
    overflow: hidden;
}
.auth-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 3px;
    background: linear-gradient(90deg, var(--red), var(--red-light), var(--red));
    background-size: 200% 100%;
    animation: gradientShift 4s ease infinite;
}

.auth-title {
    text-align: center;
    font-size: 22px;
    margin-bottom: 6px;
    color: #fff;
}
.auth-subtitle {
    text-align: center;
    color: var(--muted);
    font-size: 14px;
    margin-bottom: 26px;
}

.alert {
    padding: 12px 16px;
    border-radius: 10px;
    font-size: 14px;
    margin-bottom: 18px;
    font-weight: 600;
}
.alert-error {
    background: rgba(255, 0, 51, 0.15);
    border: 1px solid var(--red);
    color: var(--red-light);
}
.alert-success {
    background: rgba(37, 211, 102, 0.15);
    border: 1px solid #25d366;
    color: #25d366;
}

.form-group { margin-bottom: 16px; }
.form-group label {
    display: block;
    font-size: 12px;
    letter-spacing: 1px;
    text-transform: uppercase;
    color: var(--muted);
    margin-bottom: 6px;
    font-family: 'Orbitron', sans-serif;
    font-weight: 700;
}
.form-group input {
    width: 100%;
    padding: 13px 16px;
    border-radius: 10px;
    border: 2px solid var(--border);
    background: var(--bg-2);
    color: var(--text);
    font-size: 15px;
    font-family: 'Rajdhani', sans-serif;
    transition: 0.3s;
}
.form-group input:focus {
    outline: none;
    border-color: var(--red);
    box-shadow: 0 0 20px rgba(255,0,51,0.4);
}
.form-group input::placeholder { color: var(--muted); }

.auth-btn {
    width: 100%;
    padding: 15px;
    margin-top: 8px;
    background: linear-gradient(90deg, var(--red), var(--red-light), var(--red-dark), var(--red));
    background-size: 300% 100%;
    animation: gradientShift 4s ease infinite;
    color: #fff;
    border: none;
    border-radius: 10px;
    font-family: 'Orbitron', sans-serif;
    font-weight: 900;
    font-size: 15px;
    letter-spacing: 2px;
    cursor: pointer;
    box-shadow: 0 0 25px rgba(255,0,51,0.6);
    transition: 0.3s;
}
.auth-btn:hover {
    box-shadow: 0 0 40px rgba(255,0,51,1);
    transform: translateY(-2px);
}

.auth-footer {
    text-align: center;
    margin-top: 22px;
    color: var(--muted);
    font-size: 14px;
}
.auth-footer a {
    color: var(--red);
    font-weight: 700;
    margin-left: 4px;
    transition: 0.3s;
}
.auth-footer a:hover {
    text-shadow: 0 0 15px var(--red);
}
.back-link {
    display: block;
    text-align: center;
    margin-top: 20px;
    color: var(--muted);
    font-size: 13px;
}
.back-link:hover { color: var(--red); }
</style>
</head>
<body>

<div class="effects-layer">
    <div class="stars" id="stars"></div>
</div>

<div class="auth-wrap">
    <div class="auth-logo">
        <div class="logo-text">MILLZ GAMES</div>
        <p>Create your account</p>
    </div>

    <div class="auth-card">
        <h2 class="auth-title">Sign Up</h2>
        <p class="auth-subtitle">Join MILLZ GAMES today</p>

        <?php if (!empty($error)): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?> <a href="login.php" style="text-decoration: underline;">Login →</a></div>
        <?php endif; ?>

        <form method="POST" action="register.php">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" placeholder="e.g. MillzKing" maxlength="30" required>
            </div>

            <div class="form-group">
                <label>Phone Number</label>
                <input type="tel" name="phone" placeholder="e.g. 0683179360" maxlength="15" required>
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Min 6 characters" minlength="6" required>
            </div>

            <div class="form-group">
                <label>Confirm Password</label>
                <input type="password" name="confirm" placeholder="Repeat password" minlength="6" required>
            </div>

            <button type="submit" class="auth-btn">SIGN UP</button>
        </form>

        <div class="auth-footer">
            Already have an account?<a href="login.php">Login</a>
        </div>
    </div>

    <a href="index.php" class="back-link">← Back to Home</a>
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