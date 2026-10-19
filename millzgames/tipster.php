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

$tipster_name = isset($_GET['name']) ? trim($_GET['name']) : '';
$tipster = null;

if (!empty($tipster_name)) {
    $stmt = $conn->prepare("SELECT * FROM tipsters WHERE name = ? LIMIT 1");
    $stmt->bind_param("s", $tipster_name);
    $stmt->execute();
    $tipster = $stmt->get_result()->fetch_assoc();
}

$tips = [];
if ($tipster) {
    $stmt = $conn->prepare("SELECT * FROM tips WHERE tipster_id = ? ORDER BY created_at DESC");
    $stmt->bind_param("i", $tipster['id']);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) { $tips[] = $row; }
}

if (!$tipster) {
    header('Location: categories.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MILLZ GAMES - <?= htmlspecialchars($tipster['name']) ?> | Tips</title>
<link rel="stylesheet" href="assets/css/style.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;700;900&family=Rajdhani:wght@400;500;600;700&family=Bebas+Neue&family=Bungee&display=swap" rel="stylesheet">
<style>
.profile-hero { padding: 60px 20px 50px; text-align: center; background: radial-gradient(circle at 50% 30%, rgba(255,0,51,0.25), transparent 60%), var(--bg-2); border-bottom: 1px solid var(--border); position: relative; z-index: 1; }
.profile-avatar { width: 130px; height: 130px; margin: 0 auto 22px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-family: 'Orbitron', sans-serif; font-weight: 900; font-size: 56px; color: #fff; box-shadow: 0 0 0 5px rgba(255,0,51,0.3), 0 0 60px rgba(255,0,51,0.7); animation: floatLogo 3s ease-in-out infinite; }
.profile-name { font-family: 'Bungee', cursive; font-size: clamp(32px, 6vw, 56px); letter-spacing: 2px; line-height: 1; margin-bottom: 12px; color: var(--green); text-shadow: 0 0 25px rgba(0,204,102,0.6); text-transform: uppercase; }
.profile-bio { color: var(--muted); font-size: 16px; font-style: italic; margin-bottom: 24px; }
.profile-stats { display: flex; gap: 30px; justify-content: center; flex-wrap: wrap; margin-top: 20px; }
.profile-stat { background: var(--card); border: 1px solid var(--border); border-radius: 14px; padding: 16px 24px; min-width: 120px; transition: 0.3s; }
.profile-stat:hover { border-color: var(--red); transform: translateY(-4px); box-shadow: 0 10px 30px rgba(255,0,51,0.25); }
.profile-stat .stat-num { display: block; font-family: 'Orbitron', sans-serif; font-size: 24px; font-weight: 900; color: var(--green); text-shadow: 0 0 15px rgba(0,204,102,0.5); margin-bottom: 4px; }
.profile-stat .stat-label { font-size: 12px; text-transform: uppercase; letter-spacing: 1px; color: var(--muted); }
.tips-list { max-width: 900px; margin: 0 auto; display: flex; flex-direction: column; gap: 14px; }
.tip-card { background: var(--card); border: 1px solid var(--border); border-radius: 16px; padding: 22px 24px; transition: 0.3s; position: relative; overflow: hidden; }
.tip-card::before { content: ''; position: absolute; left: 0; top: 0; bottom: 0; width: 4px; background: linear-gradient(180deg, var(--red), var(--green)); }
.tip-card:hover { border-color: var(--red); transform: translateX(4px); box-shadow: 0 10px 30px rgba(255,0,51,0.25); }
.tip-header { display: flex; justify-content: space-between; align-items: center; gap: 16px; margin-bottom: 14px; flex-wrap: wrap; }
.tip-match { font-family: 'Orbitron', sans-serif; font-size: 17px; font-weight: 900; color: #fff; letter-spacing: 0.5px; }
.tip-league { display: inline-block; padding: 3px 12px; background: rgba(0,204,102,0.15); border: 1px solid var(--green); border-radius: 20px; color: var(--green); font-size: 11px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; }
.tip-body { display: flex; align-items: center; justify-content: space-between; gap: 20px; flex-wrap: wrap; }
.tip-text { flex: 1; min-width: 200px; }
.tip-text strong { color: var(--muted); font-size: 12px; text-transform: uppercase; letter-spacing: 1px; display: block; margin-bottom: 4px; }
.tip-text span { font-size: 17px; color: #fff; font-weight: 600; }
.tip-odds { text-align: right; }
.tip-odds strong { color: var(--muted); font-size: 12px; text-transform: uppercase; letter-spacing: 1px; display: block; margin-bottom: 4px; }
.tip-odds span { font-family: 'Orbitron', sans-serif; font-size: 20px; font-weight: 900; color: var(--green); text-shadow: 0 0 10px rgba(0,204,102,0.5); }
.tip-bet-btn { display: inline-block; padding: 10px 20px; background: linear-gradient(90deg, var(--red), var(--red-light), var(--red-dark), var(--red)); background-size: 300% 100%; animation: gradientShift 3s ease infinite; color: #fff; font-family: 'Orbitron', sans-serif; font-weight: 900; font-size: 12px; letter-spacing: 1px; border-radius: 8px; box-shadow: 0 0 20px rgba(255,0,51,0.6); transition: 0.3s; white-space: nowrap; }
.tip-bet-btn:hover { transform: translateY(-2px); box-shadow: 0 0 35px rgba(255,0,51,1); }
.empty-tips { text-align: center; padding: 60px 20px; background: var(--card); border: 2px dashed var(--border); border-radius: 20px; max-width: 900px; margin: 0 auto; }
.empty-tips .empty-icon { font-size: 70px; margin-bottom: 16px; animation: floatLogo 2s ease-in-out infinite; filter: drop-shadow(0 0 20px var(--green)); }
.empty-tips h3 { font-size: 22px; margin-bottom: 10px; color: #fff; font-family: 'Bungee'; letter-spacing: 2px; }
.empty-tips p { color: var(--muted); font-size: 15px; margin-bottom: 24px; }
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
            <span>💰 <?= htmlspecialchars($tipster['name']) ?> — Betting Tips</span>
            <span>📊 <?= count($tips) ?> tips posted</span>
            <span>🔥 New tips added daily</span>
            <span>💰 <?= htmlspecialchars($tipster['name']) ?> — Betting Tips</span>
            <span>📊 <?= count($tips) ?> tips posted</span>
            <span>🔥 New tips added daily</span>
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

<section class="profile-hero">
    <div class="container">
        <div class="profile-avatar" style="background: linear-gradient(135deg, <?= htmlspecialchars($tipster['color']) ?>, #000);">
            <?= strtoupper(substr($tipster['name'], 0, 1)) ?>
        </div>
        <h1 class="profile-name"><?= htmlspecialchars($tipster['name']) ?></h1>
        <p class="profile-bio"><?= htmlspecialchars($tipster['bio'] ?? '') ?></p>

        <div class="profile-stats">
            <div class="profile-stat">
                <span class="stat-num"><?= count($tips) ?></span>
                <span class="stat-label">Tips Posted</span>
            </div>
            <div class="profile-stat">
                <span class="stat-num"><?= (int)($tipster['total_wins'] ?? 0) ?></span>
                <span class="stat-label">Wins</span>
            </div>
            <div class="profile-stat">
                <span class="stat-num"><?= htmlspecialchars($tipster['status'] ?? 'NEW') ?></span>
                <span class="stat-label">Status</span>
            </div>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section-head">
            <h2>Latest <span class="accent">Tips</span></h2>
            <p>Predictions from <?= htmlspecialchars($tipster['name']) ?></p>
        </div>

        <?php if (count($tips) > 0): ?>
            <div class="tips-list">
                <?php foreach ($tips as $tip): ?>
                    <div class="tip-card">
                        <div class="tip-header">
                            <div class="tip-match">⚽ <?= htmlspecialchars($tip['match_name']) ?></div>
                            <?php if (!empty($tip['league'])): ?>
                                <span class="tip-league"><?= htmlspecialchars($tip['league']) ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="tip-body">
                            <div class="tip-text">
                                <strong>Tip</strong>
                                <span><?= htmlspecialchars($tip['tip']) ?></span>
                            </div>
                            <?php if (!empty($tip['odds'])): ?>
                                <div class="tip-odds">
                                    <strong>Odds</strong>
                                    <span><?= number_format($tip['odds'], 2) ?></span>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($tip['bet_link'])): ?>
                                <a href="<?= htmlspecialchars($tip['bet_link']) ?>" target="_blank" rel="noopener" class="tip-bet-btn">Place Bet →</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-tips">
                <div class="empty-icon">💰</div>
                <h3>No tips yet</h3>
                <p><?= htmlspecialchars($tipster['name']) ?> hasn't posted any tips yet. Check back soon!</p>
                <a href="https://wa.me/<?= $admin_whatsapp ?>?text=<?= urlencode('Hello MILLZ ADMIN, please notify me when ' . $tipster['name'] . ' posts new tips. My name is: ') ?>"
                   target="_blank" rel="noopener" class="btn-whatsapp">
                    <span class="wa-icon">💬</span> Notify Me
                </a>
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="betting-cta">
    <div class="container">
        <div class="betting-box">
            <div class="betting-text">
                <span class="badge-live">🔥 STAY UPDATED</span>
                <h2>Get Tips <span class="accent">Daily</span></h2>
                <p>Follow <?= htmlspecialchars($tipster['name']) ?> for daily football predictions and betting tips.</p>
            </div>
            <a href="categories.php" class="btn-solid big">← All Tipsters</a>
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