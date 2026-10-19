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

$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$q_lower = strtolower($q);

$tipsters = [];
$res = $conn->query("SELECT * FROM tipsters ORDER BY id ASC");
while ($row = $res->fetch_assoc()) $tipsters[] = $row;

$products = [];
$res = $conn->query("SELECT * FROM products WHERE status='active'");
while ($row = $res->fetch_assoc()) $products[] = $row;

$pages = [
    ['name' => 'Home', 'subtitle' => 'Millz Games Homepage', 'icon' => '🏠', 'link' => 'index.php'],
    ['name' => 'Games', 'subtitle' => 'All games — coming soon', 'icon' => '🎮', 'link' => 'games.php'],
    ['name' => 'Play with Pro', 'subtitle' => 'Spin & book a pro', 'icon' => '🎯', 'link' => 'playwithpro.php'],
    ['name' => 'eFootball League', 'subtitle' => '16 players · TZS 2,000', 'icon' => '🏆', 'link' => 'league.php'],
    ['name' => 'Beat the Pro', 'subtitle' => 'Challenge a pro', 'icon' => '⚡', 'link' => 'beatthepro.php'],
    ['name' => '2v2 Team Battle', 'subtitle' => 'Team up & win', 'icon' => '👥', 'link' => 'team.php'],
    ['name' => 'Betting Tips', 'subtitle' => 'Daily tips from 5 tipsters', 'icon' => '💰', 'link' => 'categories.php'],
    ['name' => 'Shop with Us', 'subtitle' => 'Controllers, jerseys, gear', 'icon' => '🛒', 'link' => 'shop.php'],
    ['name' => 'Community', 'subtitle' => 'Chat with players', 'icon' => '💬', 'link' => 'community.php']
];

$matched_pros = []; $matched_tipsters = []; $matched_products = []; $matched_pages = [];

if (!empty($q_lower)) {
    foreach ($pros as $p) if (strpos(strtolower($p['name']), $q_lower) !== false) $matched_pros[] = $p;
    foreach ($tipsters as $t) if (strpos(strtolower($t['name'] . ' ' . ($t['bio'] ?? '')), $q_lower) !== false) $matched_tipsters[] = $t;
    foreach ($products as $p) if (strpos(strtolower($p['name'] . ' ' . ($p['description'] ?? '')), $q_lower) !== false) $matched_products[] = $p;
    foreach ($pages as $p) if (strpos(strtolower($p['name'] . ' ' . $p['subtitle']), $q_lower) !== false) $matched_pages[] = $p;
}

$total_results = count($matched_pros) + count($matched_tipsters) + count($matched_products) + count($matched_pages);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MILLZ GAMES - Search: <?= htmlspecialchars($q) ?></title>
<link rel="stylesheet" href="assets/css/style.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;700;900&family=Rajdhani:wght@400;500;600;700&family=Bebas+Neue&family=Bungee&display=swap" rel="stylesheet">
<style>
.results-list { max-width: 900px; margin: 0 auto 40px; display: flex; flex-direction: column; gap: 12px; }
.result-row { display: flex; align-items: center; gap: 16px; background: var(--card); border: 1px solid var(--border); border-radius: 14px; padding: 14px 20px; transition: 0.3s; position: relative; overflow: hidden; }
.result-row::before { content: ''; position: absolute; left: 0; top: 0; bottom: 0; width: 3px; background: linear-gradient(180deg, var(--red), var(--green)); opacity: 0; transition: 0.3s; }
.result-row:hover { transform: translateX(6px); border-color: var(--red); box-shadow: 0 10px 30px rgba(255,0,51,0.25); }
.result-row:hover::before { opacity: 1; }
.result-icon { width: 50px; height: 50px; min-width: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 24px; background: linear-gradient(135deg, var(--red), var(--green)); border: 2px solid var(--green); color: #fff; overflow: hidden; }
.result-icon img { width: 100%; height: 100%; border-radius: 50%; object-fit: cover; }
.result-info { flex: 1; min-width: 0; }
.result-name { font-family: 'Orbitron', sans-serif; font-size: 16px; font-weight: 900; color: #fff; margin-bottom: 2px; letter-spacing: 0.5px; }
.result-sub { color: var(--muted); font-size: 13px; }
.result-badge { display: inline-block; padding: 3px 10px; background: rgba(0,204,102,0.15); border: 1px solid var(--green); border-radius: 20px; color: var(--green); font-size: 10px; font-weight: 900; letter-spacing: 1px; font-family: 'Orbitron', sans-serif; text-transform: uppercase; }
.result-arrow { color: var(--green); font-size: 20px; transition: 0.3s; }
.result-row:hover .result-arrow { transform: translateX(5px); }
.empty-search { max-width: 700px; margin: 0 auto; text-align: center; padding: 60px 30px; background: var(--card); border: 2px dashed var(--border); border-radius: 20px; }
.empty-icon { font-size: 80px; margin-bottom: 20px; animation: floatLogo 2s ease-in-out infinite; filter: drop-shadow(0 0 25px var(--green)); display: inline-block; }
.empty-search h2 { font-family: 'Bungee', cursive; font-size: clamp(24px, 4vw, 34px); letter-spacing: 2px; color: var(--green); text-shadow: 0 0 20px rgba(0,204,102,0.5); margin-bottom: 12px; text-transform: uppercase; }
.empty-search p { color: var(--muted); font-size: 15px; margin-bottom: 26px; }
.suggestion-tags { display: flex; flex-wrap: wrap; gap: 10px; justify-content: center; margin-top: 20px; }
.tag { padding: 8px 16px; background: rgba(255,0,51,0.08); border: 1px solid var(--red); border-radius: 30px; color: var(--red); font-size: 13px; transition: 0.3s; font-weight: 600; }
.tag:hover { background: var(--red); color: #fff; transform: translateY(-2px); box-shadow: 0 8px 20px rgba(255,0,51,0.4); }
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
            <span>🔍 Search MILLZ GAMES</span>
            <span>🎯 Find pros, tipsters, products</span>
            <span>⚡ Fast results</span>
            <span>🔍 Search MILLZ GAMES</span>
            <span>🎯 Find pros, tipsters, products</span>
            <span>⚡ Fast results</span>
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
            <input type="text" id="searchInput" placeholder="Type to search..." autocomplete="off" value="<?= htmlspecialchars($q) ?>" oninput="liveSearch(this.value)">
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
        <li><a href="community.php">💬 Community</a></li>
    </ul>
    <div class="sidebar-auth">
        <a href="login.php" class="btn-outline full">Login</a>
        <a href="register.php" class="btn-solid full">Sign Up</a>
    </div>
</aside>

<section class="page-header">
    <div class="container">
        <span class="badge">🔍 SEARCH RESULTS</span>
        <h1>Results for <span class="accent">"<?= htmlspecialchars($q ?: '...') ?>"</span></h1>
        <p><?= $total_results ?> result<?= $total_results === 1 ? '' : 's' ?> found</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <?php if (empty($q)): ?>
            <div class="empty-search">
                <div class="empty-icon">🔍</div>
                <h2>Start Searching</h2>
                <p>Type something in the search bar to find pros, tipsters, products, or pages.</p>
                <div class="suggestion-tags">
                    <a href="search.php?q=skudu" class="tag">skudu</a>
                    <a href="search.php?q=pro" class="tag">pro</a>
                    <a href="search.php?q=league" class="tag">league</a>
                    <a href="search.php?q=tips" class="tag">tips</a>
                    <a href="search.php?q=shop" class="tag">shop</a>
                </div>
            </div>
        <?php elseif ($total_results === 0): ?>
            <div class="empty-search">
                <div class="empty-icon">😕</div>
                <h2>No Results</h2>
                <p>Nothing found for "<strong style="color: var(--red);"><?= htmlspecialchars($q) ?></strong>". Try another search.</p>
                <div class="suggestion-tags">
                    <a href="search.php?q=skudu" class="tag">skudu</a>
                    <a href="search.php?q=pro" class="tag">pro</a>
                    <a href="search.php?q=league" class="tag">league</a>
                    <a href="search.php?q=tips" class="tag">tips</a>
                    <a href="search.php?q=shop" class="tag">shop</a>
                </div>
            </div>
        <?php else: ?>

            <?php if (count($matched_pros) > 0): ?>
                <div class="section-head">
                    <h2>🎯 Pro Players <span class="accent">(<?= count($matched_pros) ?>)</span></h2>
                </div>
                <div class="results-list">
                    <?php foreach ($matched_pros as $pro): ?>
                        <a href="playwithpro.php" class="result-row">
                            <div class="result-icon"><img src="<?= htmlspecialchars($pro['avatar']) ?>" alt=""></div>
                            <div class="result-info">
                                <div class="result-name"><?= htmlspecialchars($pro['name']) ?></div>
                                <div class="result-sub">eFootball Pro · TZS <?= number_format($pro['price']) ?> per match</div>
                            </div>
                            <span class="result-badge">PLAY</span>
                            <span class="result-arrow">→</span>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (count($matched_tipsters) > 0): ?>
                <div class="section-head" style="margin-top: 40px;">
                    <h2>💰 Tipsters <span class="accent">(<?= count($matched_tipsters) ?>)</span></h2>
                </div>
                <div class="results-list">
                    <?php foreach ($matched_tipsters as $t): ?>
                        <a href="tipster.php?name=<?= urlencode($t['name']) ?>" class="result-row">
                            <div class="result-icon" style="background: linear-gradient(135deg, <?= htmlspecialchars($t['color']) ?>, #000);"><?= strtoupper(substr($t['name'], 0, 1)) ?></div>
                            <div class="result-info">
                                <div class="result-name"><?= htmlspecialchars($t['name']) ?></div>
                                <div class="result-sub"><?= htmlspecialchars($t['bio'] ?? 'Betting tips') ?></div>
                            </div>
                            <span class="result-badge">TIPS</span>
                            <span class="result-arrow">→</span>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (count($matched_products) > 0): ?>
                <div class="section-head" style="margin-top: 40px;">
                    <h2>🛒 Products <span class="accent">(<?= count($matched_products) ?>)</span></h2>
                </div>
                <div class="results-list">
                    <?php foreach ($matched_products as $p): ?>
                        <a href="shop.php" class="result-row">
                            <div class="result-icon">🎮</div>
                            <div class="result-info">
                                <div class="result-name"><?= htmlspecialchars($p['name']) ?></div>
                                <div class="result-sub">TZS <?= number_format($p['price']) ?></div>
                            </div>
                            <span class="result-badge">SHOP</span>
                            <span class="result-arrow">→</span>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (count($matched_pages) > 0): ?>
                <div class="section-head" style="margin-top: 40px;">
                    <h2>📄 Pages <span class="accent">(<?= count($matched_pages) ?>)</span></h2>
                </div>
                <div class="results-list">
                    <?php foreach ($matched_pages as $p): ?>
                        <a href="<?= htmlspecialchars($p['link']) ?>" class="result-row">
                            <div class="result-icon"><?= $p['icon'] ?></div>
                            <div class="result-info">
                                <div class="result-name"><?= htmlspecialchars($p['name']) ?></div>
                                <div class="result-sub"><?= htmlspecialchars($p['subtitle']) ?></div>
                            </div>
                            <span class="result-badge">PAGE</span>
                            <span class="result-arrow">→</span>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

        <?php endif; ?>
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
        { name: 'Search', subtitle: 'Search', icon: '🔍', link: 'search.php' }
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