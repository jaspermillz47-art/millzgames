<?php
session_start();
$admin_whatsapp = "255683179360";
include 'config/db.php';

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

// Handle message post
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['message'])) {
    $username = !empty($_POST['username']) ? trim($_POST['username']) : 'Guest';
    $msg = trim($_POST['message']);
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    if (!empty($msg)) {
        $stmt = $conn->prepare("INSERT INTO messages (user_id, username, message) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $user_id, $username, $msg);
        $stmt->execute();
        header("Location: community.php");
        exit;
    }
}

// Load messages
$messages = [];
$result = $conn->query("SELECT * FROM messages ORDER BY created_at DESC LIMIT 50");
if ($result) { while ($row = $result->fetch_assoc()) { $messages[] = $row; } }
$messages = array_reverse($messages);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MILLZ GAMES - Community</title>
<link rel="stylesheet" href="assets/css/style.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;700;900&family=Rajdhani:wght@400;500;600;700&family=Bebas+Neue&family=Bungee&display=swap" rel="stylesheet">
<style>
@keyframes slideIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
.chat-container { max-width: 900px; margin: 0 auto; background: var(--card); border: 1px solid var(--border); border-radius: 20px; overflow: hidden; box-shadow: 0 0 60px rgba(255,0,51,0.15); }
.chat-header { padding: 20px 25px; background: linear-gradient(90deg, rgba(255,0,51,0.15), rgba(0,204,102,0.08)); border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; gap: 15px; }
.chat-header-left { display: flex; align-items: center; gap: 12px; }
.chat-status-dot { width: 12px; height: 12px; border-radius: 50%; background: #25d366; box-shadow: 0 0 15px #25d366; animation: pulseGreen 2s infinite; }
.chat-header h3 { font-family: 'Orbitron', sans-serif; font-size: 16px; color: #fff; letter-spacing: 1px; }
.chat-header span { color: var(--muted); font-size: 13px; }
.chat-messages { padding: 25px; max-height: 500px; overflow-y: auto; display: flex; flex-direction: column; gap: 16px; scrollbar-width: thin; scrollbar-color: var(--red) var(--card); }
.chat-messages::-webkit-scrollbar { width: 6px; }
.chat-messages::-webkit-scrollbar-thumb { background: var(--red); border-radius: 10px; }
.chat-msg { display: flex; gap: 12px; align-items: flex-start; animation: slideIn 0.3s ease; }
.msg-avatar { width: 42px; height: 42px; min-width: 42px; border-radius: 50%; background: linear-gradient(135deg, var(--red), var(--green)); display: flex; align-items: center; justify-content: center; font-family: 'Orbitron', sans-serif; font-weight: 900; font-size: 16px; color: #fff; border: 2px solid var(--red); box-shadow: 0 0 15px rgba(255,0,51,0.5); }
.msg-body { flex: 1; min-width: 0; }
.msg-name { font-family: 'Orbitron', sans-serif; font-size: 13px; color: var(--green); letter-spacing: 0.5px; margin-bottom: 4px; display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
.msg-time { color: var(--muted); font-size: 11px; font-family: 'Rajdhani', sans-serif; font-weight: 500; }
.msg-text { background: var(--bg-2); border: 1px solid var(--border); border-radius: 12px; padding: 10px 14px; color: var(--text); font-size: 14px; word-wrap: break-word; display: inline-block; max-width: 100%; }
.chat-empty { text-align: center; padding: 40px 20px; color: var(--muted); }
.chat-empty .empty-icon { font-size: 60px; margin-bottom: 14px; opacity: 0.5; }
.chat-form { padding: 20px 25px; border-top: 1px solid var(--border); background: rgba(255,0,51,0.02); }
.chat-form-row { display: flex; gap: 10px; margin-bottom: 10px; }
.chat-form input[type="text"] { flex: 1; padding: 12px 16px; border-radius: 10px; border: 2px solid var(--border); background: var(--bg-2); color: var(--text); font-size: 15px; font-family: 'Rajdhani', sans-serif; transition: 0.3s; }
.chat-form input[type="text"]:focus { outline: none; border-color: var(--green); box-shadow: 0 0 20px rgba(0,204,102,0.3); }
.chat-form input::placeholder { color: var(--muted); }
.chat-form .name-input { max-width: 180px; }
.chat-form .send-btn { padding: 12px 28px; background: linear-gradient(90deg, var(--red), var(--red-light), var(--red-dark), var(--red)); background-size: 300% 100%; animation: gradientShift 4s ease infinite; color: #fff; border: none; border-radius: 10px; font-family: 'Orbitron', sans-serif; font-weight: 900; font-size: 13px; letter-spacing: 1px; cursor: pointer; box-shadow: 0 0 20px rgba(255,0,51,0.5); transition: 0.3s; white-space: nowrap; }
.chat-form .send-btn:hover { box-shadow: 0 0 35px rgba(255,0,51,1); transform: translateY(-2px); }
@media (max-width: 700px) {
    .chat-messages { padding: 18px; max-height: 400px; }
    .chat-form { padding: 15px; }
    .chat-form-row { flex-wrap: wrap; }
    .chat-form .name-input { max-width: 100%; }
    .chat-form .send-btn { width: 100%; }
    .msg-avatar { width: 36px; height: 36px; min-width: 36px; font-size: 14px; }
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
            <span>💬 MILLZ Community Chat</span>
            <span>🎮 Talk with other players</span>
            <span>🏆 Share tips & wins</span>
            <span>👥 Find teammates</span>
            <span>💬 MILLZ Community Chat</span>
            <span>🎮 Talk with other players</span>
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
            <input type="text" id="searchInput" placeholder="Type to search..." autocomplete="off" oninput="liveSearch(this.value)">
        </div>
        <div class="search-suggestions">
            <span class="suggestion-label">Popular Searches</span>
            <span class="suggestion-tag" onclick="quickSearch('skudu')">SKUDU</span>
            <span class="suggestion-tag" onclick="quickSearch('league')">League</span>
            <span class="suggestion-tag" onclick="quickSearch('tips')">Tips</span>
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
        <li><a href="games.php">🎮 Games</a></li>
        <li><a href="playwithpro.php">🎯 Play with Pro</a></li>
        <li><a href="league.php">🏆 eFootball League</a></li>
        <li><a href="beatthepro.php">⚡ Beat the Pro</a></li>
        <li><a href="team.php">👥 2v2 Team Battle</a></li>
        <li><a href="categories.php">💰 Betting Tips</a></li>
        <li><a href="shop.php">🛒 Shop with Us</a></li>
        <li><a href="community.php" class="active">💬 Community</a></li>
    </ul>
    <div class="sidebar-auth">
        <a href="login.php" class="btn-outline full">Login</a>
        <a href="register.php" class="btn-solid full">Sign Up</a>
    </div>
</aside>

<section class="page-header">
    <div class="container">
        <span class="badge">💬 COMMUNITY</span>
        <h1>Join the <span class="accent">Chat</span></h1>
        <p>Talk with players · Share tips · Find teammates</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="chat-container">
            <div class="chat-header">
                <div class="chat-header-left">
                    <div class="chat-status-dot"></div>
                    <div>
                        <h3>MILLZ CHAT ROOM</h3>
                        <span><?= count($messages) ?> messages · All players welcome</span>
                    </div>
                </div>
                <span style="color: var(--muted); font-size: 12px;">🔴 LIVE</span>
            </div>

            <div class="chat-messages" id="chatMessages">
                <?php if (count($messages) > 0): ?>
                    <?php foreach ($messages as $msg): ?>
                        <div class="chat-msg">
                            <div class="msg-avatar"><?= strtoupper(substr($msg['username'] ?: 'G', 0, 1)) ?></div>
                            <div class="msg-body">
                                <div class="msg-name">
                                    <?= htmlspecialchars($msg['username'] ?: 'Guest') ?>
                                    <span class="msg-time"><?= date('M j, H:i', strtotime($msg['created_at'])) ?></span>
                                </div>
                                <div class="msg-text"><?= htmlspecialchars($msg['message']) ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="chat-empty">
                        <div class="empty-icon">💬</div>
                        <p>No messages yet. Be the first to say hello! 👋</p>
                    </div>
                <?php endif; ?>
            </div>

            <form class="chat-form" method="POST" action="community.php">
                <div class="chat-form-row">
                    <input type="text" name="username" class="name-input" placeholder="Your name" maxlength="30" value="<?= htmlspecialchars($_SESSION['username'] ?? '') ?>">
                </div>
                <div class="chat-form-row">
                    <input type="text" name="message" placeholder="Type your message..." maxlength="500" required>
                    <button type="submit" class="send-btn">SEND 🚀</button>
                </div>
            </form>
        </div>
    </div>
</section>

<footer>
    <div class="container footer-grid">
        <div><h3>MILLZ GAMES</h3><p>The pitch is ours. Play eFootball. Win real money.</p></div>
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
            <a href="#">Contact Us</a>
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
    const pages = [
        { name: 'Home', subtitle: 'Millz Games', icon: '🏠', link: 'index.php' },
        { name: 'Community', subtitle: 'Chat with players', icon: '💬', link: 'community.php' },
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
        const sb = document.getElementById('searchOverlay');
        if (sb && sb.classList.contains('open')) toggleSearch();
        const sidebar = document.getElementById('sidebar');
        if (sidebar && sidebar.classList.contains('open')) toggleSidebar();
    }
});
window.addEventListener('load', () => {
    const chat = document.getElementById('chatMessages');
    if (chat) chat.scrollTop = chat.scrollHeight;
});
function createStars() {
    const c = document.getElementById('stars'); if (!c) return;
    const colors = ['', 'green'];
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
    const colors = ['red', 'green', 'white'];
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