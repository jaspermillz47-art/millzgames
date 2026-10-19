<?php
session_start();
$admin_whatsapp = "255683179360";
$halopesa = "0627041240";
$unlock_price = "5,000";
include 'config/db.php';

$error = '';
$success = '';

// Check session
$unlocked = isset($_SESSION['unlocked']) && $_SESSION['unlocked'] === true;
$unlock_code = $_SESSION['unlock_code'] ?? '';
$unlock_expires = $_SESSION['unlock_expires'] ?? '';

// Auto-expire session after 30 days
if ($unlocked && !empty($unlock_expires)) {
    if (strtotime($unlock_expires) < time()) {
        $_SESSION['unlocked'] = false;
        unset($_SESSION['unlocked'], $_SESSION['unlock_code'], $_SESSION['unlock_expires']);
        $unlocked = false;
        $error = 'Code yako imeisha muda wake. Tafadhali lipa tena kwa mwezi mpya.';
    }
}

// Handle unlock
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['code'])) {
    $code = strtoupper(trim($_POST['code']));

    if (empty($code)) {
        $error = 'Ingiza code yako.';
    } else {
        $stmt = $conn->prepare("SELECT * FROM access_codes WHERE code = ? LIMIT 1");
        $stmt->bind_param("s", $code);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();

        if (!$row) {
            $error = '❌ Code haipo. Angalia tena.';
        } else {
            // Case 1: Code imetumika tayari kwa mtu mwingine
            if ($row['status'] === 'used') {
                // Check if same device
                $current_device = $_SESSION['device_id'] ?? '';
                if (!empty($row['device_id']) && $row['device_id'] === $current_device) {
                    // Same device — check if expired
                    if (!empty($row['expires_at']) && strtotime($row['expires_at']) < time()) {
                        $error = '❌ Code yako imeisha muda wake (siku 30). Tafadhali lipa tena.';
                    } else {
                        // Still valid — login
                        $_SESSION['unlocked'] = true;
                        $_SESSION['unlock_code'] = $code;
                        $_SESSION['unlock_expires'] = $row['expires_at'];
                        header('Location: games.php');
                        exit;
                    }
                } else {
                    $error = '❌ Code hii imetumika tayari kwa mtu mwingine. Kila code ni kwa mtu mmoja tu.';
                }
            }
            // Case 2: Code haijatumika — fresh
            else if ($row['status'] === 'active') {
                // Device fingerprint — simple (from browser)
                $device_id = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
                $device_id = md5($device_id . ($_SERVER['REMOTE_ADDR'] ?? '') . 'millz-salt');

                $expires_at = date('Y-m-d H:i:s', strtotime('+30 days'));

                // Update code
                $stmt = $conn->prepare("UPDATE access_codes SET status = 'used', device_id = ?, activated_at = NOW(), expires_at = ? WHERE id = ?");
                $stmt->bind_param("ssi", $device_id, $expires_at, $row['id']);
                $stmt->execute();

                // Set session
                $_SESSION['unlocked'] = true;
                $_SESSION['unlock_code'] = $code;
                $_SESSION['unlock_expires'] = $expires_at;
                $_SESSION['device_id'] = $device_id;

                header('Location: games.php');
                exit;
            }
            // Case 3: Status nyingine
            else {
                $error = '❌ Code hii haiwezi kutumika. Wasiliana na admin.';
            }
        }
    }
}

// Generate device_id kama haipo
if (empty($_SESSION['device_id'])) {
    $device_id = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    $_SESSION['device_id'] = md5($device_id . ($_SERVER['REMOTE_ADDR'] ?? '') . 'millz-salt');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MILLZ GAMES - Unlock Games</title>
<link rel="stylesheet" href="assets/css/style.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;700;900&family=Rajdhani:wght@400;500;600;700&family=Bebas+Neue&family=Bungee&display=swap" rel="stylesheet">
<style>
.unlock-card {
    max-width: 520px;
    margin: 0 auto;
    background: var(--card);
    border: 2px solid var(--green);
    border-radius: 24px;
    padding: 40px 30px;
    text-align: center;
    box-shadow: 0 0 60px rgba(0,255,136,0.25);
    position: relative;
    overflow: hidden;
}
.unlock-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 4px;
    background: linear-gradient(90deg, var(--amber), var(--green), var(--amber));
    background-size: 200% 100%;
    animation: gradientShift 4s ease infinite;
}
.unlock-icon {
    font-size: 70px;
    margin-bottom: 16px;
    display: inline-block;
    filter: drop-shadow(0 0 25px var(--green));
    animation: floatLogo 2s ease-in-out infinite;
}
.unlock-card h1 {
    font-family: 'Bungee', cursive;
    font-size: 26px;
    color: var(--green);
    text-shadow: 0 0 20px rgba(0,255,136,0.6);
    text-transform: uppercase;
    margin-bottom: 8px;
    letter-spacing: 1px;
}
.unlock-card p { color: var(--muted); font-size: 14px; margin-bottom: 24px; }
.code-input {
    width: 100%;
    padding: 18px 20px;
    border: 2px solid var(--border);
    background: var(--bg-2);
    color: #fff;
    border-radius: 12px;
    font-family: 'Orbitron', sans-serif;
    font-size: 20px;
    text-align: center;
    letter-spacing: 4px;
    text-transform: uppercase;
    font-weight: 900;
    transition: 0.3s;
    margin-bottom: 16px;
}
.code-input:focus { outline: none; border-color: var(--green); box-shadow: 0 0 25px rgba(0,255,136,0.4); }
.code-input::placeholder { letter-spacing: 2px; font-size: 16px; }
.alert {
    padding: 14px 18px;
    border-radius: 10px;
    margin-bottom: 20px;
    font-weight: 600;
    font-size: 13px;
    line-height: 1.6;
    text-align: left;
}
.alert.error { background: rgba(255,0,51,0.15); border: 1px solid var(--red); color: #ff6666; }
.alert.success { background: rgba(0,255,136,0.15); border: 1px solid var(--green); color: var(--green); }
.info-box {
    margin-top: 24px;
    padding: 16px;
    background: rgba(255,149,0,0.08);
    border: 1px dashed var(--amber);
    border-radius: 12px;
    font-size: 13px;
    color: var(--muted);
    line-height: 1.9;
    text-align: left;
}
.info-box strong { color: var(--amber); }
.info-box .step { display: block; padding: 4px 0; }
.back-link {
    display: inline-block;
    margin-top: 20px;
    color: var(--muted);
    font-size: 13px;
    transition: 0.3s;
}
.back-link:hover { color: var(--amber); }
.verified-badge {
    display: inline-block;
    padding: 6px 14px;
    background: rgba(0,255,136,0.15);
    border: 1px solid var(--green);
    border-radius: 20px;
    color: var(--green);
    font-size: 11px;
    font-family: 'Orbitron', sans-serif;
    font-weight: 700;
    letter-spacing: 1px;
    margin-bottom: 16px;
}
</style>
</head>
<body>

<div class="effects-layer">
    <div class="stars" id="stars"></div>
    <div class="orb-layer" id="orbs"></div>
</div>
<div class="lightning-container" id="lightning"></div>

<nav class="navbar">
    <div class="nav-container">
        <a href="games.php" class="nav-icon-btn" aria-label="Back">←</a>
        <a href="index.php" class="logo"><span class="logo-text">MILLZ GAMES</span></a>
        <div class="nav-right"></div>
    </div>
</nav>

<section class="section" style="padding-top: 100px; min-height: 80vh;">
    <div class="container">
        <div class="unlock-card">
            <div class="unlock-icon">🔑</div>
            <h1>Unlock Games</h1>
            <p>Ingiza Access Code yako (siku 30)</p>

            <div class="verified-badge">🔒 1 CODE = 1 USER ONLY</div>

            <?php if (!empty($error)): ?>
                <div class="alert error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST">
                <input type="text" name="code" class="code-input" placeholder="MILLZ-XXXXXX" maxlength="30" required autofocus autocomplete="off">
                <button type="submit" class="btn-solid full big">🔓 UNLOCK GAMES</button>
            </form>

            <div class="info-box">
                <strong>💡 Huna code bado?</strong>
                <span class="step">1️⃣ Lipa TZS <?= $unlock_price ?> kwa <strong>HaloPesa <?= $halopesa ?></strong></span>
                <span class="step">2️⃣ Tuma screenshot WhatsApp <strong>0683179360</strong></span>
                <span class="step">3️⃣ Admin anakutumia code yako</span>
                <span class="step">4️⃣ Ingiza code hapa → Unlock ZOTE kwa siku 30</span>
                <div style="margin-top: 16px; text-align: center;">
                    <a href="https://wa.me/<?= $admin_whatsapp ?>?text=<?= urlencode('Hello MILLZ ADMIN, nataka kununua Access Code ya games zote. Nitalipa TZS ' . $unlock_price . ' kwa HaloPesa ' . $halopesa . '. Jina langu: ') ?>" target="_blank" rel="noopener" class="btn-whatsapp" style="font-size: 13px; padding: 12px 24px;">
                        <span class="wa-icon">💬</span> Lipa & Pata Code
                    </a>
                </div>
            </div>

            <a href="games.php" class="back-link">← Rudi kwenye Games</a>
        </div>
    </div>
</section>

<script>
function createStars() {
    const c = document.getElementById('stars'); if (!c) return;
    const colors = ['', 'green', 'amber'];
    for (let i = 0; i < 40; i++) {
        const s = document.createElement('div');
        s.className = 'star ' + colors[Math.floor(Math.random() * colors.length)];
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

function createOrbs() {
    const c = document.getElementById('orbs'); if (!c) return;
    const colors = ['amber', 'green', 'white'];
    for (let i = 0; i < 4; i++) {
        const o = document.createElement('div');
        o.className = 'orb ' + colors[Math.floor(Math.random() * colors.length)];
        const size = 150 + Math.random() * 250;
        o.style.width = size + 'px'; o.style.height = size + 'px';
        o.style.left = Math.random() * 90 + '%';
        o.style.top = Math.random() * 90 + '%';
        o.style.animationDuration = (15 + Math.random() * 15) + 's';
        o.style.animationDelay = Math.random() * 8 + 's';
        c.appendChild(o);
    }
}
createOrbs();

function createLightning() {
    const c = document.getElementById('lightning'); if (!c) return;
    const b = document.createElement('div');
    b.className = 'lightning-bolt';
    b.style.left = (10 + Math.random() * 80) + '%';
    b.style.transform = 'scaleX(' + (0.7 + Math.random() * 1) + ')';
    c.appendChild(b);
    const f = document.createElement('div');
    f.className = 'flash-overlay';
    document.body.appendChild(f);
    setTimeout(() => { b.remove(); f.remove(); }, 600);
}
function scheduleLightning() {
    setTimeout(() => {
        createLightning();
        if (Math.random() > 0.7) setTimeout(createLightning, 250);
        scheduleLightning();
    }, 5000 + Math.random() * 4000);
}
scheduleLightning();
</script>
</body>
</html>