<?php
session_start();

$admin_whatsapp = "255683179360";

include 'config/db.php';

// Load pros for search
$pros = [];
$result = $conn->query("SELECT * FROM pros ORDER BY id ASC");
if ($result) {
    while ($row = $result->fetch_assoc()) { $pros[] = $row; }
}

// Rename map
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

// Load tipsters
$tipsters = [];
$result = $conn->query("SELECT * FROM tipsters ORDER BY id ASC");
if ($result) {
    while ($row = $result->fetch_assoc()) { $tipsters[] = $row; }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MILLZ GAMES - Betting Tips</title>
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
            <span>💰 5 Expert Tipsters — Win rate 82% - 90%</span>
            <span>🔥 New tips uploaded daily</span>
            <span>⚽ Premier League · La Liga · Serie A</span>
            <span>🏆 Champions League tips available</span>
            <span>💰 5 Expert Tipsters — Win rate 82% - 90%</span>
            <span>🔥 New tips uploaded daily</span>
            <span>⚽ Premier League · La Liga · Serie A</span>
            <span>🏆 Champions League tips available</span>
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
            <span class="suggestion-tag" onclick="quickSearch('sharie')">SHARIE</span>
            <span class="suggestion-tag" onclick="quickSearch('cba')">CBA</span>
            <span class="suggestion-tag" onclick="quickSearch('ommy')">OMMY</span>
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
        <li><a href="categories.php" class="active">💰 Betting Tips</a></li>
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
        <span class="badge">💰 BETTING TIPS</span>
        <h1>Follow the <span class="accent">Best Tipsters</span></h1>
        <p>5 expert tipsters · Daily tips · Win rate up to <strong>90%</strong></p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="pro-list">
            <?php if (count($tipsters) > 0): ?>
                <?php foreach ($tipsters as $tipster): ?>
                <div class="pro-row">
                    <div class="pro-avatar" style="background: linear-gradient(135deg, <?= htmlspecialchars($tipster['color']) ?>, #000);">
                        <?= strtoupper(substr($tipster['name'], 0, 1)) ?>
                    </div>
                    <div class="pro-info">
                        <h3 class="pro-name">
                            <?= htmlspecialchars($tipster['name']) ?>
                            <?php if (($tipster['status'] ?? 'NEW') === 'NEW'): ?>
                                <span class="status-badge">NEW</span>
                            <?php endif; ?>
                        </h3>
                        <div class="pro-meta">
                            <span><?= (int)($tipster['total_tips'] ?? 0) ?> tips posted</span>
                            <span class="pro-sep">·</span>
                            <span><?= (int)($tipster['total_wins'] ?? 0) ?> wins</span>
                        </div>
                        <p class="tip-bio"><?= htmlspecialchars($tipster['bio'] ?? '') ?></p>
                    </div>
                    <div class="pro-price">
                        <span class="price-label">Status</span>
                        <span class="price-value"><?= htmlspecialchars($tipster['status'] ?? 'NEW') ?></span>
                    </div>
                    <a href="tipster.php?name=<?= urlencode($tipster['name']) ?>" class="btn-solid pro-book-btn">
                        View Tips
                    </a>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="text-align: center; color: var(--muted); padding: 40px;">No tipsters found.</p>
            <?php endif; ?>
        </div>
    </div>
</section>

<section class="betting-cta">
    <div class="container">
        <div class="betting-box">
            <div class="betting-text">
                <span class="badge-live">🔥 DAILY UPDATES</span>
                <h2>New Tips <span class="accent">Every Day</span></h2>
                <p>Our tipsters post fresh predictions daily — Premier League, La Liga, Serie A, Champions League and more.</p>
            </div>
            <a href="https://wa.me/<?= $admin_whatsapp ?>?text=<?= urlencode('Hello MILLZ ADMIN, I want to follow the betting tips. My name is: ') ?>" target="_blank" rel="noopener" class="btn-solid big">
                💬 Follow Tips
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
const TIPSTERS = <?= json_encode($tipsters) ?>;

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
            data.push({ name: pro.name, subtitle: 'eFootball Pro · TZS ' + Number(pro.price).toLocaleString(), icon: '🎯', link: 'playwithpro.php', searchText: pro.name.toLowerCase() });
        });
    }
    if (TIPSTERS && TIPSTERS.length) {
        TIPSTERS.forEach(t => {
            data.push({ name: t.name, subtitle: t.bio || 'Betting tips', icon: '💰', link: 'tipster.php?name=' + encodeURIComponent(t.name), searchText: t.name.toLowerCase() });
        });
    }
    const pages = [
        { name: 'Home', subtitle: 'Millz Games', icon: '🏠', link: 'index.php' },
        { name: 'Games', subtitle: 'Coming soon', icon: '🎮', link: 'games.php' },
        { name: 'Play with Pro', subtitle: 'Spin & book', icon: '🎯', link: 'playwithpro.php' },
        { name: 'eFootball League', subtitle: '16 players', icon: '🏆', link: 'league.php' },
        { name: 'Betting Tips', subtitle: 'Daily tips', icon: '💰', link: 'categories.php' },
        { name: 'Shop', subtitle: 'Gear', icon: '🛒', link: 'shop.php' }
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