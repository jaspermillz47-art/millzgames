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
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MILLZ GAMES - 2v2 Team Battle</title>
<link rel="stylesheet" href="assets/css/style.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;700;900&family=Rajdhani:wght@400;500;600;700&family=Bebas+Neue&family=Bungee&display=swap" rel="stylesheet">
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
            <span>👥 2v2 Team Battle — Team Up & Win</span>
            <span>💰 Entry TZS 2,000 — Win TZS 6,000</span>
            <span>🎮 Team with a friend, defeat other duos</span>
            <span>💬 Register via WhatsApp</span>
            <span>👥 2v2 Team Battle — Team Up & Win</span>
            <span>💰 Entry TZS 2,000 — Win TZS 6,000</span>
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
            <span class="suggestion-tag" onclick="quickSearch('team')">Team</span>
            <span class="suggestion-tag" onclick="quickSearch('2v2')">2v2</span>
            <span class="suggestion-tag" onclick="quickSearch('league')">League</span>
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
        <li><a href="team.php" class="active">👥 2v2 Team Battle</a></li>
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
        <span class="badge">👥 2v2 TEAM BATTLE</span>
        <h1>Team Up & <span class="accent">Win</span></h1>
        <p>Entry TZS 2,000 — Win <strong>TZS 6,000</strong></p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="betting-box" style="margin-bottom: 40px;">
            <div class="betting-text">
                <span class="badge-live">👥 2v2 TEAM BATTLE</span>
                <h2>Team Up & <span class="accent">Win</span></h2>
                <p>Team up with a friend. Face other duos in eFootball. The winning team takes home TZS 6,000 — split it or keep it, your call.</p>
                <a href="https://wa.me/<?= $admin_whatsapp ?>?text=<?= urlencode('Hello MILLZ ADMIN, I want to register for 2v2 Team Battle. My name is: ') ?>"
                   target="_blank" rel="noopener" class="btn-whatsapp" style="margin-top:16px;">
                    <span class="wa-icon">💬</span> Register Team on WhatsApp
                </a>
            </div>
            <div style="display:flex;gap:20px;flex-wrap:wrap;">
                <div style="text-align:center;">
                    <div style="font-family:'Orbitron';font-size:22px;color:var(--green);font-weight:900;">2</div>
                    <div style="font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:1px;">Per Team</div>
                </div>
                <div style="text-align:center;">
                    <div style="font-family:'Orbitron';font-size:22px;color:var(--green);font-weight:900;">2K</div>
                    <div style="font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:1px;">Entry</div>
                </div>
                <div style="text-align:center;">
                    <div style="font-family:'Orbitron';font-size:22px;color:var(--green);font-weight:900;">6K</div>
                    <div style="font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:1px;">Win</div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section dark">
    <div class="container">
        <div class="section-head">
            <h2>Battle <span class="accent">Rules</span></h2>
            <p>Know the rules before you play</p>
        </div>
        <div class="cat-grid" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));">
            <div class="cat-card">
                <div class="cat-icon">👥</div>
                <h3>2 Players Per Team</h3>
                <p>You + one friend. Pick a team name.</p>
            </div>
            <div class="cat-card">
                <div class="cat-icon">💰</div>
                <h3>TZS 2,000 Entry</h3>
                <p>Paid once per team (TZS 1,000 per player).</p>
            </div>
            <div class="cat-card">
                <div class="cat-icon">🎮</div>
                <h3>eFootball Only</h3>
                <p>Standard match rules apply.</p>
            </div>
            <div class="cat-card">
                <div class="cat-icon">🏆</div>
                <h3>Winner Takes TZS 6,000</h3>
                <p>Beat the other team — take the prize.</p>
            </div>
            <div class="cat-card">
                <div class="cat-icon">⏱️</div>
                <h3>10-Min Matches</h3>
                <p>6 min per half. Extra time if draw.</p>
            </div>
            <div class="cat-card">
                <div class="cat-icon">📱</div>
                <h3>WhatsApp Coordination</h3>
                <p>Admin arranges match time.</p>
            </div>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section-head">
            <h2>How to <span class="accent">Register</span></h2>
            <p>3 simple steps</p>
        </div>
        <div class="steps-grid">
            <div class="step"><span class="step-num">1</span><h3>Get Your Team</h3><p>Find a friend. Pick a team name</p></div>
            <div class="step"><span class="step-num">2</span><h3>Message Admin</h3><p>Click Register → WhatsApp opens</p></div>
            <div class="step"><span class="step-num">3</span><h3>Pay & Play</h3><p>Pay TZS 2,000, get matched up</p></div>
        </div>
    </div>
</section>

<section class="betting-cta">
    <div class="container">
        <div class="betting-box">
            <div class="betting-text">
                <span class="badge-live">🎮 TEAM UP NOW</span>
                <h2>Ready to <span class="accent">Team Up?</span></h2>
                <p>Grab a friend, contact the Admin, and get matched against other duos.</p>
            </div>
            <a href="https://wa.me/<?= $admin_whatsapp ?>?text=<?= urlencode('Hello MILLZ ADMIN, I want to register for 2v2 Team Battle. My name is: ') ?>"
               target="_blank" rel="noopener" class="btn-whatsapp big">
                <span class="wa-icon">💬</span> Register on WhatsApp
            </a>
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
        { name: 'Play with Pro', subtitle: 'Spin & book', icon: '🎯', link: 'playwithpro.php' },
        { name: '2v2 Team Battle', subtitle: 'Team up & win', icon: '👥', link: 'team.php' },
        { name: 'eFootball League', subtitle: '16 players', icon: '🏆', link: 'league.php' }
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