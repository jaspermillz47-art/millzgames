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

$products = [];
$result = $conn->query("SELECT * FROM products WHERE status='active' ORDER BY id DESC");
if ($result) { while ($row = $result->fetch_assoc()) { $products[] = $row; } }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MILLZ GAMES - Shop with Us</title>
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
            <span>🛒 Shop with MILLZ GAMES</span>
            <span>👕 Jerseys, controllers, gear</span>
            <span>⚡ Order via WhatsApp</span>
            <span>🚚 Delivered to your door</span>
            <span>🛒 Shop with MILLZ GAMES</span>
            <span>👕 Jerseys, controllers, gear</span>
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
            <span class="suggestion-tag" onclick="quickSearch('jersey')">Jersey</span>
            <span class="suggestion-tag" onclick="quickSearch('controller')">Controller</span>
            <span class="suggestion-tag" onclick="quickSearch('shop')">Shop</span>
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
        <li><a href="shop.php" class="active">🛒 Shop with Us</a></li>
        <li><a href="community.php">💬 Community</a></li>
    </ul>
    <div class="sidebar-auth">
        <a href="login.php" class="btn-outline full">Login</a>
        <a href="register.php" class="btn-solid full">Sign Up</a>
    </div>
</aside>

<section class="page-header">
    <div class="container">
        <span class="badge">🛒 SHOP WITH US</span>
        <h1>MILLZ <span class="accent">Shop</span></h1>
        <p>Controllers, jerseys, gaming gear — delivered to your door</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <?php if (count($products) > 0): ?>
            <div class="cat-grid" style="grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));">
                <?php foreach ($products as $product): ?>
                    <div class="cat-card" style="padding:0;overflow:hidden;text-align:left;">
                        <div style="height:180px;background:linear-gradient(135deg,#111,#000);<?= !empty($product['image']) ? "background-image:url('assets/images/products/" . htmlspecialchars($product['image']) . "');background-size:cover;background-position:center;" : '' ?>display:flex;align-items:center;justify-content:center;font-size:60px;position:relative;">
                            <?php if (empty($product['image'])): ?>🎮<?php endif; ?>
                            <span style="position:absolute;top:12px;right:12px;padding:3px 10px;background:var(--green);color:#000;font-size:10px;font-weight:900;letter-spacing:1px;border-radius:20px;font-family:'Orbitron';">
                                <?= $product['stock'] > 0 ? 'IN STOCK' : 'SOLD OUT' ?>
                            </span>
                        </div>
                        <div style="padding:18px;">
                            <h3 style="font-size:16px;margin-bottom:6px;color:#fff;"><?= htmlspecialchars($product['name']) ?></h3>
                            <p style="color:var(--muted);font-size:13px;margin-bottom:12px;min-height:34px;"><?= htmlspecialchars($product['description'] ?? '') ?></p>
                            <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;">
                                <span style="font-family:'Orbitron';font-size:18px;color:var(--green);font-weight:900;">TZS <?= number_format($product['price']) ?></span>
                                <a href="https://wa.me/<?= $admin_whatsapp ?>?text=<?= urlencode('Hello MILLZ ADMIN, I want to order ' . $product['name'] . ' for TZS ' . number_format($product['price']) . '. My name is: ') ?>"
                                   target="_blank" rel="noopener" class="btn-whatsapp" style="padding:8px 16px;font-size:11px;">
                                    💬 Order
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="betting-box" style="max-width:700px;margin:0 auto;flex-direction:column;text-align:center;">
                <div style="font-size:80px;margin-bottom:20px;filter:drop-shadow(0 0 20px var(--green));">🛒</div>
                <h2 style="font-family:'Bungee';font-size:32px;color:var(--green);margin-bottom:12px;text-transform:uppercase;">Shop Coming Soon</h2>
                <p style="color:var(--muted);margin-bottom:24px;font-size:15px;">We're loading the shop with jerseys, controllers, and gaming gear. Check back soon!</p>
                <a href="https://wa.me/<?= $admin_whatsapp ?>?text=<?= urlencode('Hello MILLZ ADMIN, notify me when the shop opens. My name is: ') ?>"
                   target="_blank" rel="noopener" class="btn-whatsapp">
                    <span class="wa-icon">💬</span> Notify Me on WhatsApp
                </a>
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="section dark">
    <div class="container">
        <div class="section-head">
            <h2>Why <span class="accent">Shop</span> With Us</h2>
            <p>Quality gear, fast delivery</p>
        </div>
        <div class="cat-grid" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));">
            <div class="cat-card">
                <div class="cat-icon">🚚</div>
                <h3>Fast Delivery</h3>
                <p>Delivered to your door anywhere in Tanzania</p>
            </div>
            <div class="cat-card">
                <div class="cat-icon">💯</div>
                <h3>Quality Products</h3>
                <p>Only genuine gear — controllers, jerseys, accessories</p>
            </div>
            <div class="cat-card">
                <div class="cat-icon">💬</div>
                <h3>Order via WhatsApp</h3>
                <p>Simple — just message us and we confirm</p>
            </div>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section-head">
            <h2>Order <span class="accent">Process</span></h2>
            <p>3 simple steps</p>
        </div>
        <div class="steps-grid">
            <div class="step"><span class="step-num">1</span><h3>Browse</h3><p>Pick what you want from the shop</p></div>
            <div class="step"><span class="step-num">2</span><h3>Message</h3><p>Click "Order" → WhatsApp opens with details</p></div>
            <div class="step"><span class="step-num">3</span><h3>Receive</h3><p>Pay and get it delivered to your door</p></div>
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
const PRODUCTS = <?= json_encode($products) ?>;

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
    if (PRODUCTS && PRODUCTS.length) {
        PRODUCTS.forEach(p => {
            data.push({ name: p.name, subtitle: 'TZS ' + Number(p.price).toLocaleString(), icon: '🛒', link: 'shop.php', searchText: p.name.toLowerCase() });
        });
    }
    const pages = [
        { name: 'Home', subtitle: 'Millz Games', icon: '🏠', link: 'index.php' },
        { name: 'Play with Pro', subtitle: 'Spin & book', icon: '🎯', link: 'playwithpro.php' },
        { name: 'eFootball League', subtitle: '16 players', icon: '🏆', link: 'league.php' },
        { name: 'Shop with Us', subtitle: 'Controllers, jerseys', icon: '🛒', link: 'shop.php' }
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