<?php
session_start();

$admin_whatsapp = "255683179360";

include 'config/db.php';

$pros = [];
$result = $conn->query("SELECT * FROM pros ORDER BY id ASC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $pros[] = $row;
    }
}

// ===== RENAME MAP =====
$rename_map = [
    'CBA MAESTRO' => 'CBA',
    'OMMY DIZZY'  => 'OMMY',
    'BRK KABULU'  => 'JIMMY'
];

foreach ($pros as $i => &$pro) {
    if (isset($rename_map[$pro['name']])) {
        $pro['name'] = $rename_map[$pro['name']];
    }
    if (!empty($pro['icon'])) {
        $pro['avatar'] = "assets/images/pros/" . $pro['icon'];
    } else {
        $seed = urlencode($pro['name']);
        $bg = ltrim($pro['color'], '#');
        $pro['avatar'] = "https://api.dicebear.com/7.x/bottts/svg?seed={$seed}&backgroundColor={$bg}&radius=50";
    }
}
unset($pro);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MILLZ GAMES - Play with Pro</title>
<link rel="stylesheet" href="assets/css/style.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;700;900&family=Rajdhani:wght@400;500;600;700&family=Bebas+Neue&family=Bungee&display=swap" rel="stylesheet">
</head>
<body>

<!-- ===== EFFECTS ===== -->
<div class="effects-layer">
    <div class="stars" id="stars"></div>
    <div class="orb-layer" id="orbs"></div>
</div>

<!-- ===== LIGHTNING ===== -->
<div class="lightning-container" id="lightning"></div>

<!-- ===== LIVE TICKER ===== -->
<div class="live-ticker">
    <div class="ticker-label">🔴 LIVE</div>
    <div class="ticker-track">
        <div class="ticker-content">
            <span>🎰 Spin & Win — Try your luck!</span>
            <span>🎯 10 Pros Available — TZS 1,000 per match</span>
            <span>⚡ Book instantly on WhatsApp</span>
            <span>💰 Winner takes the cash</span>
            <span>🎰 Spin & Win — Try your luck!</span>
            <span>🎯 10 Pros Available — TZS 1,000 per match</span>
            <span>⚡ Book instantly on WhatsApp</span>
            <span>💰 Winner takes the cash</span>
        </div>
    </div>
</div>

<!-- ===== NAVBAR ===== -->
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

<!-- ===== SEARCH OVERLAY ===== -->
<div class="search-overlay" id="searchOverlay">
    <button class="search-close-top" onclick="toggleSearch()">✕</button>
    <div class="search-hero">
        <div class="search-icon-big">🔍</div>
        <h2 class="search-title">SEARCH MILLZ GAMES</h2>
        <div class="search-inner">
            <input type="text" id="searchInput" placeholder="Type to search pros..." autocomplete="off" oninput="liveSearch(this.value)">
        </div>
        <div class="search-suggestions">
            <span class="suggestion-label">Popular Searches</span>
            <span class="suggestion-tag" onclick="quickSearch('skudu')">SKUDU</span>
            <span class="suggestion-tag" onclick="quickSearch('millz')">MILLZ</span>
            <span class="suggestion-tag" onclick="quickSearch('cba')">CBA</span>
            <span class="suggestion-tag" onclick="quickSearch('ommy')">OMMY</span>
            <span class="suggestion-tag" onclick="quickSearch('jimmy')">JIMMY</span>
        </div>
        <div class="search-results" id="searchResults"></div>
    </div>
</div>

<!-- ===== SIDEBAR ===== -->
<div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>
<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <span class="sidebar-logo">MILLZ GAMES</span>
        <button class="sidebar-close" onclick="toggleSidebar()">✕</button>
    </div>
    <ul class="sidebar-links">
        <li><a href="index.php">🏠 Home</a></li>
        <li><a href="games.php">🎮 Games</a></li>
        <li><a href="playwithpro.php" class="active">🎯 Play with Pro</a></li>
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

<!-- ===== PAGE HEADER ===== -->
<section class="page-header">
    <div class="container">
        <span class="badge">🎰 SLOT SPINNER</span>
        <h1>Play with <span class="accent">a Pro</span></h1>
        <p>Spin the ring — see who you get!</p>
    </div>
</section>

<!-- ===== SLOT SPINNER ===== -->
<section class="section slot-section">
    <div class="container">
        <div class="slot-stage">
            <div class="slot-pointer">
                <div class="pointer-arrow">▼</div>
                <div class="pointer-glow"></div>
            </div>
            <div class="slot-ring" id="slotRing">
                <?php foreach ($pros as $i => $pro): ?>
                    <div class="slot-item" style="--angle: <?= $i * 36 ?>deg;" data-angle="<?= $i * 36 ?>">
                        <div class="slot-avatar-wrap">
                            <img src="<?= htmlspecialchars($pro['avatar']) ?>"
                                 alt="<?= htmlspecialchars($pro['name']) ?>"
                                 class="slot-avatar"
                                 onerror="this.src='https://api.dicebear.com/7.x/bottts/svg?seed=<?= urlencode($pro['name']) ?>&backgroundColor=ffd700&radius=50'">
                            <span class="slot-name"><?= htmlspecialchars($pro['name']) ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="slot-center">
                <span class="slot-logo">🎰</span>
            </div>
        </div>

        <div class="spin-wrap">
            <button class="spin-big-btn" id="spinBtn" onclick="spinSlot()">🎰 SPIN</button>
        </div>

        <p class="wheel-hint">Tap to spin — good luck!</p>
    </div>
</section>

<!-- ===== WIN POPUP ===== -->
<div class="win-popup-overlay" id="winPopup">
    <div class="win-popup">
        <button class="win-close" onclick="closeWinPopup()">✕</button>
        <span class="win-badge">🎉 YOU GOT</span>
        <div class="win-avatar-wrap">
            <img id="winAvatar" src="" alt="Winner" class="win-avatar-img">
        </div>
        <h2 class="win-name" id="winName">SKUDU</h2>
        <p class="win-price">Entry: <strong id="winPrice">TZS 1,000</strong></p>
        <div class="win-buttons">
            <a href="#" id="winBookBtn" target="_blank" rel="noopener" class="btn-whatsapp">
                <span class="wa-icon">💬</span> Book on WhatsApp
            </a>
        </div>
    </div>
</div>

<!-- ===== HOW IT WORKS ===== -->
<section class="section">
    <div class="container">
        <div class="section-head">
            <h2>How It <span class="accent">Works</span></h2>
            <p>3 simple steps</p>
        </div>
        <div class="steps-grid">
            <div class="step"><span class="step-num">1</span><h3>Spin</h3><p>Tap SPIN to get a Pro</p></div>
            <div class="step"><span class="step-num">2</span><h3>Contact on WhatsApp</h3><p>Admin confirms and sends payment details</p></div>
            <div class="step"><span class="step-num">3</span><h3>Play & Win</h3><p>Beat the Pro. Winner takes the cash</p></div>
        </div>
    </div>
</section>

<!-- ===== FOOTER ===== -->
<footer>
    <div class="container footer-grid">
        <div>
            <h3>MILLZ GAMES</h3>
            <p>The pitch is ours. Play eFootball. Win real money.</p>
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

<!-- ===== AUDIO ===== -->
<audio id="tickSound" preload="auto">
    <source src="https://cdn.pixabay.com/audio/2022/03/15/audio_c8c8a73467.mp3" type="audio/mpeg">
</audio>
<audio id="winSound" preload="auto">
    <source src="https://cdn.pixabay.com/audio/2021/08/04/audio_0625c1539c.mp3" type="audio/mpeg">
</audio>

<script>
const PROS = <?= json_encode($pros) ?>;
const ADMIN_WHATSAPP = "<?= $admin_whatsapp ?>";

// ===== SIDEBAR =====
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('open');
    document.getElementById('sidebarOverlay').classList.toggle('open');
    document.body.style.overflow = document.getElementById('sidebar').classList.contains('open') ? 'hidden' : '';
}

// ===== SEARCH OVERLAY =====
function toggleSearch() {
    const overlay = document.getElementById('searchOverlay');
    if (!overlay) return;
    overlay.classList.toggle('open');
    if (overlay.classList.contains('open')) {
        document.body.style.overflow = 'hidden';
        setTimeout(() => {
            const inp = document.getElementById('searchInput');
            if (inp) inp.focus();
        }, 200);
    } else {
        document.body.style.overflow = '';
        clearSearch();
    }
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
            data.push({ name: pro.name, subtitle: 'eFootball Pro · TZS ' + Number(pro.price).toLocaleString(), icon: '🎯', link: 'playwithpro.php', searchText: pro.name.toLowerCase() });
        });
    }
    const pages = [
        { name: 'Home', subtitle: 'Millz Games', icon: '🏠', link: 'index.php' },
        { name: 'Games', subtitle: 'Coming soon', icon: '🎮', link: 'games.php' },
        { name: 'Play with Pro', subtitle: 'Spin & book', icon: '🎯', link: 'playwithpro.php' },
        { name: 'eFootball League', subtitle: '16 players', icon: '🏆', link: 'league.php' },
        { name: 'Beat the Pro', subtitle: 'Challenge', icon: '⚡', link: 'beatthepro.php' },
        { name: 'Betting Tips', subtitle: 'Daily tips', icon: '💰', link: 'categories.php' }
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

// ===== SLOT SPINNER =====
let slotRotation = 0;
let isSlotSpinning = false;
function startTicking() {
    const t = document.getElementById('tickSound');
    if (!t) return;
    t.currentTime = 0; t.volume = 0.35; t.loop = true;
    t.play().catch(() => {});
}
function stopTicking() {
    const t = document.getElementById('tickSound');
    if (t) { t.pause(); t.currentTime = 0; }
}
function spinSlot() {
    if (isSlotSpinning) return;
    const ring = document.getElementById('slotRing');
    const btn = document.getElementById('spinBtn');
    if (!ring || !PROS.length) return;
    isSlotSpinning = true;
    btn.disabled = true;
    document.querySelectorAll('.slot-avatar.winner').forEach(el => el.classList.remove('winner'));
    startTicking();
    const winnerIndex = Math.floor(Math.random() * PROS.length);
    const segmentAngle = 360 / PROS.length;
    const targetAngle = 360 - (winnerIndex * segmentAngle);
    const extraSpins = 6 + Math.floor(Math.random() * 4);
    const finalRotation = slotRotation + (extraSpins * 360) + ((targetAngle - (slotRotation % 360) + 360) % 360);
    slotRotation = finalRotation;
    ring.style.transform = 'translate(-50%, -50%) rotate(' + finalRotation + 'deg)';
    const items = document.querySelectorAll('.slot-item');
    items.forEach(item => {
        const wrap = item.querySelector('.slot-avatar-wrap');
        if (!wrap) return;
        const baseAngle = parseFloat(item.dataset.angle || '0');
        const counterRotation = -(baseAngle + finalRotation);
        wrap.style.transition = 'transform 6s cubic-bezier(0.17, 0.67, 0.16, 1)';
        wrap.style.transform = 'rotate(' + counterRotation + 'deg)';
    });
    setTimeout(() => {
        stopTicking();
        const ws = document.getElementById('winSound');
        if (ws) { ws.currentTime = 0; ws.volume = 0.7; ws.play().catch(() => {}); }
        if (items[winnerIndex]) {
            const img = items[winnerIndex].querySelector('.slot-avatar');
            if (img) img.classList.add('winner');
        }
        showWinPopup(PROS[winnerIndex]);
        btn.disabled = false;
        isSlotSpinning = false;
    }, 6200);
}
function showWinPopup(pro) {
    const popup = document.getElementById('winPopup');
    document.getElementById('winAvatar').src = pro.avatar;
    document.getElementById('winName').textContent = pro.name;
    document.getElementById('winPrice').textContent = 'TZS ' + Number(pro.price).toLocaleString();
    const msg = encodeURIComponent('Hello MILLZ ADMIN, I spun the slot and got ' + pro.name + '. I want to book a match for TZS ' + Number(pro.price).toLocaleString() + '. My name is: ');
    document.getElementById('winBookBtn').href = 'https://wa.me/' + ADMIN_WHATSAPP + '?text=' + msg;
    popup.classList.add('open');
}
function closeWinPopup() {
    document.getElementById('winPopup').classList.remove('open');
    document.querySelectorAll('.slot-avatar.winner').forEach(el => el.classList.remove('winner'));
}
document.addEventListener('click', (e) => {
    const popup = document.getElementById('winPopup');
    if (popup && e.target === popup) closeWinPopup();
});

// ===== STARS =====
function createStars() {
    const c = document.getElementById('stars'); if (!c) return;
    const colors = ['', 'gold'];
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

// ===== ENERGY ORBS =====
function createOrbs() {
    const c = document.getElementById('orbs'); if (!c) return;
    const colors = ['gold', 'light', 'white'];
    for (let i = 0; i < 6; i++) {
        const o = document.createElement('div');
        o.className = 'orb ' + colors[Math.floor(Math.random() * colors.length)];
        const size = 150 + Math.random() * 250;
        o.style.width = size + 'px';
        o.style.height = size + 'px';
        o.style.left = Math.random() * 90 + '%';
        o.style.top = Math.random() * 90 + '%';
        o.style.animationDuration = (15 + Math.random() * 15) + 's';
        o.style.animationDelay = Math.random() * 8 + 's';
        c.appendChild(o);
    }
}
createOrbs();

// ===== LIGHTNING =====
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

// ===== METEORS =====
function createMeteor() {
    const layer = document.querySelector('.effects-layer');
    if (!layer) return;
    const m = document.createElement('div');
    m.className = 'meteor';
    m.style.left = (Math.random() * 80 + 10) + '%';
    m.style.top = '-10%';
    layer.appendChild(m);
    setTimeout(() => m.remove(), 3000);
}
setInterval(createMeteor, 3500);
</script>
</body>
</html>