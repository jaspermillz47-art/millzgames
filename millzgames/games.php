<?php
session_start();
$admin_whatsapp = "255683179360";
$halopesa = "0627041240";
$unlock_price = "5,000";
include 'config/db.php';

// Handle unlock code submission
$unlock_error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['unlock_code'])) {
    $code = strtoupper(trim($_POST['unlock_code']));
    if (empty($code)) {
        $unlock_error = 'Ingiza code yako.';
    } else {
        $stmt = $conn->prepare("SELECT * FROM access_codes WHERE code = ? AND status = 'active' LIMIT 1");
        $stmt->bind_param("s", $code);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        if ($row) {
            $_SESSION['unlocked'] = true;
            $_SESSION['unlock_code'] = $code;
            $conn->query("UPDATE access_codes SET status = 'used', used_at = NOW() WHERE id = " . (int)$row['id']);
            header('Location: games.php');
            exit;
        } else {
            $unlock_error = 'Code si sahihi au imetumika tayari.';
        }
    }
}

// Load pros for search
$pros = [];
$result = $conn->query("SELECT * FROM pros ORDER BY id ASC");
if ($result) { while ($row = $result->fetch_assoc()) { $pros[] = $row; } }

$rename_map = ['CBA MAESTRO' => 'CBA', 'OMMY DIZZY' => 'OMMY', 'BRK KABULU' => 'JIMMY'];
foreach ($pros as &$pro) {
    if (isset($rename_map[$pro['name']])) $pro['name'] = $rename_map[$pro['name']];
    if (!empty($pro['icon'])) {
        $pro['avatar'] = "assets/images/pros/" . $pro['icon'];
    } else {
        $seed = urlencode($pro['name']);
        $bg = ltrim($pro['color'], '#');
        $pro['avatar'] = "https://api.dicebear.com/7.x/bottts/svg?seed={$seed}&backgroundColor={$bg}&radius=50";
    }
}
unset($pro);

// Check if user is unlocked
$unlocked = isset($_SESSION['unlocked']) && $_SESSION['unlocked'] === true;

// Load games
$games = [];
$result = $conn->query("SELECT * FROM games_store WHERE status='active' ORDER BY id DESC");
if ($result) { while ($row = $result->fetch_assoc()) { $games[] = $row; } }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MILLZ GAMES - Games Store</title>
<link rel="stylesheet" href="assets/css/style.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;700;900&family=Rajdhani:wght@400;500;600;700&family=Bebas+Neue&family=Bungee&display=swap" rel="stylesheet">
<style>
/* HERO */
.unlock-hero {
    background: linear-gradient(135deg, rgba(0,255,136,0.12), rgba(255,149,0,0.12));
    border: 2px solid var(--green);
    border-radius: 24px;
    padding: 40px 30px;
    margin: 0 auto 40px;
    max-width: 800px;
    text-align: center;
    box-shadow: 0 0 60px rgba(0,255,136,0.2);
    position: relative;
    overflow: hidden;
}
.unlock-hero::before {
    content: '';
    position: absolute;
    inset: -50%;
    background: conic-gradient(from 0deg, transparent, rgba(0,255,136,0.15), transparent, rgba(255,149,0,0.15), transparent);
    animation: rotate 8s linear infinite;
    pointer-events: none;
}
.unlock-hero > * { position: relative; z-index: 1; }
.unlock-hero .icon-big {
    font-size: 70px;
    margin-bottom: 16px;
    display: inline-block;
    animation: floatLogo 2s ease-in-out infinite;
    filter: drop-shadow(0 0 25px var(--amber));
}
.unlock-hero h1 {
    font-family: 'Bungee', cursive;
    font-size: clamp(24px, 5vw, 40px);
    color: var(--green);
    text-shadow: 0 0 25px rgba(0,255,136,0.7);
    text-transform: uppercase;
    margin-bottom: 12px;
    letter-spacing: 2px;
}
.unlock-hero p { color: var(--muted); font-size: 16px; margin-bottom: 12px; }
.unlock-price-big {
    font-family: 'Orbitron', sans-serif;
    font-size: clamp(36px, 7vw, 60px);
    font-weight: 900;
    background: linear-gradient(90deg, var(--amber), var(--green), var(--amber));
    background-size: 300% 100%;
    animation: gradientShift 3s ease infinite;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin: 16px 0;
    filter: drop-shadow(0 0 20px rgba(255,149,0,0.5));
}
.unlock-note {
    margin-top: 20px; padding: 14px;
    background: rgba(0,0,0,0.4);
    border-radius: 12px;
    color: var(--muted);
    font-size: 13px; line-height: 1.9;
    text-align: left;
}
.unlock-note strong { color: var(--amber); }

/* CODE FORM */
.code-form {
    display: flex;
    gap: 10px;
    max-width: 520px;
    margin: 24px auto 0;
    flex-wrap: wrap;
}
.code-form .code-input {
    flex: 1;
    min-width: 220px;
    padding: 18px 20px;
    border: 2px solid var(--border);
    background: var(--bg-2);
    color: #fff;
    border-radius: 12px;
    font-family: 'Orbitron', sans-serif;
    font-size: 18px;
    text-align: center;
    letter-spacing: 3px;
    text-transform: uppercase;
    font-weight: 900;
    transition: 0.3s;
}
.code-form .code-input:focus {
    outline: none;
    border-color: var(--green);
    box-shadow: 0 0 25px rgba(0,255,136,0.4);
}
.code-form .code-input::placeholder {
    letter-spacing: 2px;
    font-size: 14px;
    opacity: 0.6;
}
.code-form .btn-solid {
    padding: 18px 32px;
    border-radius: 12px;
    white-space: nowrap;
}
.alert-error {
    padding: 14px 20px;
    border-radius: 10px;
    margin-top: 16px;
    background: rgba(255,0,51,0.15);
    border: 1px solid var(--red);
    color: #ff6666;
    font-weight: 600;
    font-size: 14px;
}

/* UNLOCKED BANNER */
.unlocked-banner {
    background: linear-gradient(135deg, rgba(0,255,136,0.15), rgba(0,255,136,0.05));
    border: 2px solid var(--green);
    border-radius: 20px;
    padding: 30px;
    margin: 0 auto 40px;
    max-width: 800px;
    text-align: center;
    box-shadow: 0 0 60px rgba(0,255,136,0.25);
}
.unlocked-banner .icon { font-size: 60px; margin-bottom: 12px; filter: drop-shadow(0 0 25px var(--green)); display: inline-block; }
.unlocked-banner h2 {
    font-family: 'Bungee', cursive;
    font-size: clamp(20px, 4vw, 30px);
    color: var(--green);
    text-shadow: 0 0 20px rgba(0,255,136,0.6);
    text-transform: uppercase;
    margin-bottom: 8px;
}
.unlocked-banner p { color: var(--muted); font-size: 15px; }

/* GAME CARD */
.game-card-shop {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 16px;
    overflow: hidden;
    transition: 0.3s;
    position: relative;
    cursor: pointer;
}
.game-card-shop:hover {
    transform: translateY(-6px);
    border-color: var(--amber);
    box-shadow: 0 20px 40px rgba(255,149,0,0.3);
}
.game-img-shop {
    height: 200px;
    background-size: cover;
    background-position: center;
    background-color: #1a0f00;
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 60px;
    overflow: hidden;
}
.game-img-shop::after {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(180deg, transparent 40%, rgba(26,15,0,0.9));
}
.game-tag-free {
    position: absolute;
    top: 12px;
    left: 12px;
    padding: 5px 14px;
    background: var(--green);
    color: #000;
    font-size: 11px;
    font-weight: 900;
    letter-spacing: 1px;
    border-radius: 20px;
    font-family: 'Orbitron', sans-serif;
    z-index: 4;
    box-shadow: 0 0 20px rgba(0,255,136,0.6);
}
.game-tag-unlocked {
    position: absolute;
    top: 12px;
    left: 12px;
    padding: 5px 14px;
    background: var(--green);
    color: #000;
    font-size: 11px;
    font-weight: 900;
    letter-spacing: 1px;
    border-radius: 20px;
    font-family: 'Orbitron', sans-serif;
    z-index: 4;
}
.game-body-shop { padding: 18px; position: relative; z-index: 2; }
.game-body-shop h3 {
    font-family: 'Orbitron', sans-serif;
    font-size: 15px;
    color: #fff;
    margin-bottom: 6px;
}
.game-desc { color: var(--muted); font-size: 13px; margin-bottom: 12px; min-height: 34px; }
.game-features-list { display: flex; flex-wrap: wrap; gap: 4px; margin-bottom: 14px; }
.feature-tag {
    padding: 3px 8px;
    background: rgba(0,255,136,0.1);
    border: 1px solid var(--green);
    border-radius: 12px;
    color: var(--green);
    font-size: 10px; font-weight: 700;
    font-family: 'Orbitron', sans-serif;
}
.size-badge {
    display: inline-block;
    padding: 2px 8px;
    background: rgba(255,149,0,0.15);
    border: 1px solid var(--amber);
    border-radius: 10px;
    color: var(--amber);
    font-size: 10px; font-weight: 700;
    font-family: 'Orbitron', sans-serif;
    margin-left: 6px;
}

/* MODAL */
.game-modal {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.92);
    backdrop-filter: blur(10px);
    z-index: 99999;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 20px;
    overflow-y: auto;
}
.game-modal.open { display: flex; }
.game-modal-inner {
    position: relative;
    background: linear-gradient(180deg, var(--card), var(--bg));
    border: 2px solid var(--green);
    border-radius: 24px;
    max-width: 500px;
    width: 100%;
    padding: 40px 30px 30px;
    text-align: center;
    box-shadow: 0 0 80px rgba(0,255,136,0.4);
    animation: popIn 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
    overflow: hidden;
}
.game-modal-inner::before {
    content: '';
    position: absolute;
    top: -50%; left: -50%;
    width: 200%; height: 200%;
    background: conic-gradient(from 0deg, transparent, rgba(0,255,136,0.12), transparent, rgba(255,149,0,0.12), transparent);
    animation: rotate 8s linear infinite;
    pointer-events: none;
}
.game-modal-inner > * { position: relative; z-index: 1; }
.modal-close {
    position: absolute;
    top: 14px; right: 14px;
    background: transparent;
    border: 2px solid var(--red);
    color: var(--red);
    width: 36px; height: 36px;
    border-radius: 50%;
    font-size: 18px;
    cursor: pointer;
    transition: 0.3s;
    z-index: 3;
    display: flex;
    align-items: center;
    justify-content: center;
}
.modal-close:hover { background: var(--red); color: #fff; transform: rotate(90deg); }
.modal-img {
    width: 130px;
    height: 130px;
    margin: 0 auto 18px;
    border-radius: 20px;
    background-size: cover;
    background-position: center;
    background-color: var(--bg-2);
    border: 3px solid var(--amber);
    box-shadow: 0 0 30px rgba(255,149,0,0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 60px;
}
.modal-badge {
    display: inline-block;
    padding: 6px 20px;
    background: linear-gradient(90deg, var(--amber), var(--green));
    color: #000;
    font-family: 'Orbitron', sans-serif;
    font-weight: 900;
    font-size: 11px;
    letter-spacing: 2px;
    border-radius: 30px;
    margin-bottom: 16px;
}
.modal-title {
    font-family: 'Bungee', cursive;
    font-size: 24px;
    color: #fff;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 8px;
}
.modal-desc {
    color: var(--muted);
    font-size: 14px;
    margin-bottom: 20px;
    line-height: 1.7;
}
.modal-price {
    font-family: 'Orbitron', sans-serif;
    font-size: 42px;
    font-weight: 900;
    background: linear-gradient(90deg, var(--amber), var(--green), var(--amber));
    background-size: 300% 100%;
    animation: gradientShift 3s ease infinite;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin: 16px 0;
    filter: drop-shadow(0 0 20px rgba(255,149,0,0.5));
}
.modal-info {
    background: rgba(0,0,0,0.4);
    border-radius: 12px;
    padding: 14px;
    margin-bottom: 20px;
    font-size: 13px;
    color: var(--muted);
    text-align: left;
    line-height: 1.9;
}
.modal-info strong { color: var(--amber); }
.modal-actions {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

/* RESPONSIVE */
@media (max-width: 600px) {
    .code-form { flex-direction: column; }
    .code-form .btn-solid { width: 100%; }
    .code-form .code-input { min-width: 100%; }
}
</style>
</head>
<body>

<div class="effects-layer">
    <div class="stars" id="stars"></div>
    <div class="orb-layer" id="orbs"></div>
</div>
<div class="lightning-container" id="lightning"></div>

<div class="live-ticker">
    <div class="ticker-label">🔴 LIVE</div>
    <div class="ticker-track">
        <div class="ticker-content">
            <span>🎮 MILLZ GAMES STORE — Ingiza code yako kuanza!</span>
            <span>💰 Lipa TZS <?= $unlock_price ?> — Unlock zote</span>
            <span>📱 HaloPesa <?= $halopesa ?> · WhatsApp 0683179360</span>
            <span>🎮 MILLZ GAMES STORE — Ingiza code yako kuanza!</span>
            <span>💰 Lipa TZS <?= $unlock_price ?> — Unlock zote</span>
            <span>📱 HaloPesa <?= $halopesa ?> · WhatsApp 0683179360</span>
        </div>
    </div>
</div>

<nav class="navbar">
    <div class="nav-container">
        <button class="nav-icon-btn hamburger-btn" onclick="toggleSidebar()" aria-label="Menu">
            <span></span><span></span><span></span>
        </button>
        <a href="index.php" class="logo"><span class="logo-text">MILLZ GAMES</span></a>
        <div class="nav-right">
            <button class="nav-icon-btn" onclick="toggleSearch()" aria-label="Search">🔍</button>
            <button class="nav-icon-btn" onclick="toggleTheme()" aria-label="Theme">🌙</button>
        </div>
    </div>
</nav>

<div class="search-overlay" id="searchOverlay">
    <button class="search-close-top" onclick="toggleSearch()">✕</button>
    <div class="search-hero">
        <h2 class="search-title">🔍 SEARCH MILLZ GAMES</h2>
        <div class="search-inner">
            <input type="text" id="searchInput" placeholder="Search games..." autocomplete="off" oninput="liveSearch(this.value)">
        </div>
        <div class="search-suggestions">
            <span class="suggestion-label">Popular</span>
            <span class="suggestion-tag" onclick="quickSearch('Bright')">Bright</span>
            <span class="suggestion-tag" onclick="quickSearch('GTA')">GTA</span>
            <span class="suggestion-tag" onclick="quickSearch('Football')">Football</span>
        </div>
        <div class="search-results" id="searchResults"></div>
    </div>
</div>

<div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>
<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <span class="sidebar-logo">MILLZ GAMES</span>
        <button class="sidebar-close" onclick="toggleSidebar()">✕</button>
    </div>
    <ul class="sidebar-links">
        <li><a href="index.php">🏠 Home</a></li>
        <li><a href="games.php" class="active">🎮 Games</a></li>
        <li><a href="playwithpro.php">🎯 Play with Pro</a></li>
        <li><a href="league.php">🏆 eFootball League</a></li>
        <li><a href="beatthepro.php">⚡ Beat the Pro</a></li>
        <li><a href="team.php">👥 2v2 Team Battle</a></li>
        <li><a href="categories.php">💰 Betting Tips</a></li>
        <li><a href="shop.php">🛒 Shop with Us</a></li>
        <li><a href="community.php">💬 Community</a></li>
    </ul>
    <div class="sidebar-auth">
        <a href="login.php" class="btn-outline full">Login</a>
        <a href="register.php" class="btn-solid full">Sign Up</a>
    </div>
</aside>

<section class="page-header">
    <div class="container">
        <span class="badge">🎮 GAMES STORE</span>
        <h1>Games <span class="accent">Marketplace</span></h1>
        <p>Lipa <strong>TZS <?= $unlock_price ?></strong> — download games ZOTE</p>
    </div>
</section>

<?php if ($unlocked): ?>
<!-- UNLOCKED BANNER -->
<section class="section">
    <div class="container">
        <div class="unlocked-banner">
            <div class="icon">✅</div>
            <h2>Ume-Unlock Games Zote!</h2>
            <p>Download game yoyote hapa chini — bila kikomo!</p>
        </div>
    </div>
</section>
<?php else: ?>
<!-- UNLOCK HERO WITH CODE FORM -->
<section class="section">
    <div class="container">
        <div class="unlock-hero">
            <div class="icon-big">🔒</div>
            <h1>Unlock Games Zote</h1>
            <p>Ingiza Access Code yako hapa chini</p>
            <div class="unlock-price-big">TZS <?= $unlock_price ?></div>

            <!-- CODE INPUT FORM -->
            <form method="POST" action="games.php" class="code-form">
                <input type="text" name="unlock_code" class="code-input" placeholder="MILLZ-XXXXXX" maxlength="30" required autocomplete="off">
                <button type="submit" class="btn-solid">🔓 UNLOCK</button>
            </form>

            <?php if (!empty($unlock_error)): ?>
                <div class="alert-error">❌ <?= htmlspecialchars($unlock_error) ?></div>
            <?php endif; ?>

            <div class="unlock-note">
                💰 <strong>HaloPesa:</strong> <?= $halopesa ?><br>
                📱 <strong>WhatsApp:</strong> 0683179360<br>
                1️⃣ Lipa TZS <?= $unlock_price ?> kwa HaloPesa<br>
                2️⃣ Tuma screenshot WhatsApp<br>
                3️⃣ Pokea Access Code<br>
                4️⃣ Ingiza code hapa juu → Unlock ZOTE
                <div style="margin-top:16px;text-align:center;">
                    <a href="https://wa.me/<?= $admin_whatsapp ?>?text=<?= urlencode('Hello MILLZ ADMIN, nataka kununua Access Code ya games zote. Nitalipa TZS ' . $unlock_price . ' kwa HaloPesa ' . $halopesa . '. Jina langu: ') ?>" target="_blank" rel="noopener" class="btn-whatsapp" style="font-size:13px;padding:12px 24px;">
                        <span class="wa-icon">💬</span> Lipa & Pata Code
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- GAMES GRID -->
<section class="section dark">
    <div class="container">
        <div class="section-head">
            <h2>🎮 <span class="accent">Games Zote</span></h2>
            <p>Bonyeza game yoyote kuona maelezo</p>
        </div>

        <?php if (count($games) > 0): ?>
            <div class="cat-grid" style="grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));">
                <?php foreach ($games as $game): ?>
                    <?php $features = !empty($game['features']) ? explode('|', $game['features']) : []; ?>
                    <div class="game-card-shop" onclick="openGameModal(<?= $game['id'] ?>)">
                        <div class="game-img-shop" <?= !empty($game['image']) ? 'style="background-image:url(\'assets/images/games/' . htmlspecialchars($game['image']) . '\')"' : '' ?>>
                            <?php if (empty($game['image'])): ?>🎮<?php endif; ?>
                            
                            <?php if ($unlocked): ?>
                                <span class="game-tag-unlocked">🔓 UNLOCKED</span>
                            <?php else: ?>
                                <span class="game-tag-free">✅ FREE</span>
                            <?php endif; ?>
                        </div>
                        <div class="game-body-shop">
                            <h3>
                                <?= htmlspecialchars($game['name']) ?>
                                <?php if ($game['file_size']): ?>
                                    <span class="size-badge"><?= htmlspecialchars($game['file_size']) ?></span>
                                <?php endif; ?>
                            </h3>
                            <p class="game-desc"><?= htmlspecialchars($game['description'] ?? '') ?></p>
                            
                            <?php if (count($features) > 0 && !empty($features[0])): ?>
                                <div class="game-features-list">
                                    <?php foreach (array_slice($features, 0, 3) as $f): ?>
                                        <span class="feature-tag">✅ <?= htmlspecialchars(trim($f)) ?></span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($unlocked && !empty($game['download_link'])): ?>
                                <a href="<?= htmlspecialchars($game['download_link']) ?>" target="_blank" rel="noopener" class="btn-solid full" style="background:linear-gradient(90deg,var(--green),var(--green-light));color:#000;" onclick="event.stopPropagation();">
                                    📥 Download Now
                                </a>
                            <?php else: ?>
                                <button class="btn-solid full" onclick="event.stopPropagation(); openGameModal(<?= $game['id'] ?>);">
                                    👆 Bonyeza Kuona
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="betting-box" style="max-width:700px;margin:0 auto;flex-direction:column;text-align:center;">
                <div style="font-size:80px;margin-bottom:20px;filter:drop-shadow(0 0 20px var(--amber));">🎮</div>
                <h2 style="font-family:'Bungee';font-size:28px;color:var(--green);margin-bottom:12px;text-transform:uppercase;">Games Coming Soon</h2>
                <p style="color:var(--muted);margin-bottom:24px;">Admin anaongeza games hivi karibuni. Check back soon!</p>
                <a href="https://wa.me/<?= $admin_whatsapp ?>?text=<?= urlencode('Hello MILLZ ADMIN, notify me when games are added. My name is: ') ?>" target="_blank" rel="noopener" class="btn-whatsapp">
                    <span class="wa-icon">💬</span> Notify Me
                </a>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php if (!$unlocked): ?>
<section class="section">
    <div class="container">
        <div class="section-head">
            <h2>Jinsi <span class="accent">Inavyofanya Kazi</span></h2>
            <p>Hatua 4 rahisi</p>
        </div>
        <div class="steps-grid">
            <div class="step"><span class="step-num">1</span><h3>Bonyeza Game</h3><p>Chagua game yoyote</p></div>
            <div class="step"><span class="step-num">2</span><h3>Lipa TZS <?= $unlock_price ?></h3><p>HaloPesa <?= $halopesa ?></p></div>
            <div class="step"><span class="step-num">3</span><h3>Tuma Screenshot</h3><p>WhatsApp 0683179360</p></div>
            <div class="step"><span class="step-num">4</span><h3>Ingiza Code</h3><p>Unlock games zote milele</p></div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- MODAL POPUP -->
<div class="game-modal" id="gameModal" onclick="if(event.target===this) closeGameModal();">
    <div class="game-modal-inner">
        <button class="modal-close" onclick="closeGameModal()">✕</button>
        <div class="modal-img" id="modalImg">🎮</div>
        <span class="modal-badge">🔒 LOCKED GAME</span>
        <h2 class="modal-title" id="modalTitle">Game Name</h2>
        <p class="modal-desc" id="modalDesc">Description...</p>
        <div class="modal-price">TZS <?= $unlock_price ?></div>
        <p style="color:var(--muted);font-size:13px;margin-bottom:20px;">Lipa mara moja — download games ZOTE</p>
        <div class="modal-info">
            💰 <strong>HaloPesa:</strong> <?= $halopesa ?><br>
            📱 <strong>WhatsApp:</strong> 0683179360<br>
            🔓 Baada ya kulipa, utapokea <strong>Access Code</strong>
        </div>
        <div class="modal-actions">
            <a href="#" id="modalWhatsApp" target="_blank" rel="noopener" class="btn-whatsapp big">
                <span class="wa-icon">💬</span> Lipa & Pata Code
            </a>
        </div>
    </div>
</div>

<footer>
    <div class="container footer-grid">
        <div>
            <h3>MILLZ GAMES</h3>
            <p>The pitch is ours. Play eFootball. Win real money.</p>
            <p style="margin-top:10px;font-size:12px;">HaloPesa: <strong style="color:var(--amber);"><?= $halopesa ?></strong></p>
        </div>
        <div>
            <h4>Quick Links</h4>
            <a href="games.php">Games</a>
            <a href="playwithpro.php">Play with Pro</a>
            <a href="league.php">eFootball League</a>
            <a href="shop.php">Shop</a>
        </div>
        <div>
            <h4>Support</h4>
            <a href="community.php">Community</a>
            <a href="https://wa.me/<?= $admin_whatsapp ?>" target="_blank">WhatsApp</a>
            <a href="#">Terms</a>
            <a href="#">Privacy</a>
        </div>
        <div>
            <h4>Follow Us</h4>
            <a href="#">📺 YouTube</a>
            <a href="#">📘 Facebook</a>
            <a href="#">📷 Instagram</a>
            <a href="#">💬 WhatsApp</a>
        </div>
    </div>
    <div class="footer-bottom"><p>© 2025 MILLZ GAMES. All rights reserved.</p></div>
</footer>

<script>
const PROS = <?= json_encode($pros) ?>;
const GAMES = <?= json_encode($games) ?>;
const ADMIN_WHATSAPP = "<?= $admin_whatsapp ?>";
const HALOPESA = "<?= $halopesa ?>";
const UNLOCK_PRICE = "<?= $unlock_price ?>";

function openGameModal(id) {
    const game = GAMES.find(g => g.id == id);
    if (!game) return;

    const modal = document.getElementById('gameModal');
    const img = document.getElementById('modalImg');
    const title = document.getElementById('modalTitle');
    const desc = document.getElementById('modalDesc');
    const wa = document.getElementById('modalWhatsApp');

    if (game.image) {
        img.style.backgroundImage = 'url(assets/images/games/' + game.image + ')';
        img.textContent = '';
    } else {
        img.style.backgroundImage = 'none';
        img.textContent = '🎮';
    }

    title.textContent = game.name;
    desc.textContent = game.description || 'Premium game — lipa TZS ' + UNLOCK_PRICE + ' kupata zote.';

    const msg = encodeURIComponent('Hello MILLZ ADMIN, nataka kununua Access Code ya games zote. Nitalipa TZS ' + UNLOCK_PRICE + ' kwa HaloPesa ' + HALOPESA + '. Jina langu: ');
    wa.href = 'https://wa.me/' + ADMIN_WHATSAPP + '?text=' + msg;

    modal.classList.add('open');
    document.body.style.overflow = 'hidden';
}

function closeGameModal() {
    document.getElementById('gameModal').classList.remove('open');
    document.body.style.overflow = '';
}

function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('open');
    document.getElementById('sidebarOverlay').classList.toggle('open');
    document.body.style.overflow = document.getElementById('sidebar').classList.contains('open') ? 'hidden' : '';
}
function toggleSearch() {
    const overlay = document.getElementById('searchOverlay');
    if (!overlay) return;
    overlay.classList.toggle('open');
    if (overlay.classList.contains('open')) {
        document.body.style.overflow = 'hidden';
        setTimeout(() => { const i = document.getElementById('searchInput'); if (i) i.focus(); }, 200);
    } else { document.body.style.overflow = ''; clearSearch(); }
}
function quickSearch(query) {
    const input = document.getElementById('searchInput');
    if (input) { input.value = query; liveSearch(query); }
}
function clearSearch() {
    const i = document.getElementById('searchInput');
    const r = document.getElementById('searchResults');
    if (i) i.value = '';
    if (r) r.innerHTML = '';
}
function getSearchData() {
    const data = [];
    if (PROS && PROS.length) {
        PROS.forEach(pro => {
            data.push({ name: pro.name, subtitle: 'eFootball Pro', icon: '🎯', link: 'playwithpro.php', searchText: pro.name.toLowerCase() });
        });
    }
    if (GAMES && GAMES.length) {
        GAMES.forEach(g => {
            data.push({ name: g.name, subtitle: 'Game · ' + (g.file_size || ''), icon: '🎮', link: 'games.php', searchText: g.name.toLowerCase() });
        });
    }
    const pages = [
        { name: 'Home', subtitle: 'Millz Games', icon: '🏠', link: 'index.php' },
        { name: 'Games', subtitle: 'All games', icon: '🎮', link: 'games.php' },
        { name: 'Play with Pro', subtitle: 'Spin & book', icon: '🎯', link: 'playwithpro.php' },
        { name: 'League', subtitle: '16 players', icon: '🏆', link: 'league.php' }
    ];
    pages.forEach(p => {
        data.push({ name: p.name, subtitle: p.subtitle, icon: p.icon, link: p.link, searchText: (p.name + ' ' + p.subtitle).toLowerCase() });
    });
    return data;
}
function liveSearch(query) {
    const resultsBox = document.getElementById('searchResults');
    if (!resultsBox) return;
    query = (query || '').trim().toLowerCase();
    if (query.length === 0) { resultsBox.innerHTML = ''; return; }
    const matches = getSearchData().filter(item => item.searchText.includes(query));
    if (matches.length === 0) {
        resultsBox.innerHTML = '<div class="search-no-results">🔍 No results for "<strong>' + query + '</strong>"</div>';
        return;
    }
    let html = '';
    matches.forEach(p => {
        html += '<a href="' + p.link + '" class="search-result-item"><div class="search-result-icon">' + p.icon + '</div><div class="search-result-text"><strong>' + p.name + '</strong><span>' + p.subtitle + '</span></div><div class="search-result-arrow">→</div></a>';
    });
    resultsBox.innerHTML = html;
}
function toggleTheme() {
    document.body.classList.toggle('light-theme');
    event.currentTarget.innerHTML = document.body.classList.contains('light-theme') ? '☀️' : '🌙';
}
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        const modal = document.getElementById('gameModal');
        if (modal && modal.classList.contains('open')) closeGameModal();
        const sb = document.getElementById('searchOverlay');
        if (sb && sb.classList.contains('open')) toggleSearch();
        const sidebar = document.getElementById('sidebar');
        if (sidebar && sidebar.classList.contains('open')) toggleSidebar();
    }
});
function createStars() {
    const c = document.getElementById('stars'); if (!c) return;
    const colors = ['', 'green', 'amber'];
    for (let i = 0; i < 60; i++) {
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
    for (let i = 0; i < 6; i++) {
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