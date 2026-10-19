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
$rename_map = [
    'CBA MAESTRO' => 'CBA',
    'OMMY DIZZY'  => 'OMMY',
    'BRK KABULU'  => 'JIMMY'
];
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

// Load league players
$players = [];
$result = $conn->query("SELECT * FROM league_players ORDER BY points DESC, wins DESC, id ASC");
if ($result) {
    while ($row = $result->fetch_assoc()) { $players[] = $row; }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MILLZ GAMES - eFootball League</title>
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

<!-- TICKER -->
<div class="live-ticker">
    <div class="ticker-label">🔴 LIVE</div>
    <div class="ticker-track">
        <div class="ticker-content">
            <span>🏆 MILLZ Premier League — 16 Players</span>
            <span>💰 Entry: TZS 2,000 — Winner: TZS 15,000</span>
            <span>📊 Points Table Format</span>
            <span>📱 Join via WhatsApp</span>
            <span>🏆 MILLZ Premier League — 16 Players</span>
            <span>💰 Entry: TZS 2,000 — Winner: TZS 15,000</span>
            <span>📊 Points Table Format</span>
            <span>📱 Join via WhatsApp</span>
        </div>
    </div>
</div>

<!-- NAVBAR -->
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

<!-- SEARCH POPUP -->
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
            <span class="suggestion-tag" onclick="quickSearch('cba')">CBA</span>
            <span class="suggestion-tag" onclick="quickSearch('ommy')">OMMY</span>
            <span class="suggestion-tag" onclick="quickSearch('jimmy')">JIMMY</span>
            <span class="suggestion-tag" onclick="quickSearch('league')">League</span>
        </div>
        <div class="search-results" id="searchResults"></div>
    </div>
</div>

<!-- SIDEBAR -->
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
        <li><a href="league.php" class="active">🏆 eFootball League</a></li>
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

<!-- PAGE HEADER -->
<section class="page-header">
    <div class="container">
        <span class="badge">🏆 EFOOTBALL LEAGUE</span>
        <h1>MILLZ <span class="accent">Premier League</span></h1>
        <p>16 Players · Points Table · Winner takes <strong>TZS 15,000</strong></p>
    </div>
</section>

<!-- LEAGUE INFO -->
<section class="section">
    <div class="container">
        <div class="league-info-box">
            <div class="league-info-grid">
                <div class="league-fact">
                    <span class="fact-icon">👥</span>
                    <span class="fact-label">Players</span>
                    <span class="fact-value">16</span>
                </div>
                <div class="league-fact">
                    <span class="fact-icon">💰</span>
                    <span class="fact-label">Entry Fee</span>
                    <span class="fact-value">TZS 2,000</span>
                </div>
                <div class="league-fact">
                    <span class="fact-icon">🏆</span>
                    <span class="fact-label">Winner Prize</span>
                    <span class="fact-value">TZS 15,000</span>
                </div>
                <div class="league-fact">
                    <span class="fact-icon">📊</span>
                    <span class="fact-label">Format</span>
                    <span class="fact-value">Points Table</span>
                </div>
            </div>
            <div class="league-join">
                <h3>Ready to Join?</h3>
                <p>Contact the Admin on WhatsApp to register and pay</p>
                <a href="https://wa.me/<?= $admin_whatsapp ?>?text=<?= urlencode('Hello MILLZ ADMIN, I want to join the eFootball League. My name is: ') ?>"
                   target="_blank" rel="noopener" class="btn-whatsapp">
                    <span class="wa-icon">💬</span> Join Tournament on WhatsApp
                </a>
                <p style="font-size:12px;color:var(--muted);margin-top:12px;font-style:italic;">Your WhatsApp will open with a pre-filled message — just send it!</p>
            </div>
        </div>
    </div>
</section>

<!-- POINTS TABLE -->
<section class="section dark">
    <div class="container">
        <div class="section-head">
            <h2>📊 Points <span class="accent">Table</span></h2>
            <p>Standings will update as matches are played</p>
        </div>

        <div class="points-table-wrap">
            <table class="points-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Player</th>
                        <th>P</th>
                        <th>W</th>
                        <th>D</th>
                        <th>L</th>
                        <th>Pts</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($players) > 0): ?>
                        <?php foreach ($players as $index => $p): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td class="player-cell"><?= htmlspecialchars($p['name'] === 'TBD' ? '— TBD —' : $p['name']) ?></td>
                                <td><?= (int)$p['played'] ?></td>
                                <td><?= (int)$p['wins'] ?></td>
                                <td><?= (int)$p['draws'] ?></td>
                                <td><?= (int)$p['losses'] ?></td>
                                <td class="pts"><?= (int)$p['points'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <?php for ($i = 1; $i <= 16; $i++): ?>
                            <tr>
                                <td><?= $i ?></td>
                                <td class="player-cell">— TBD —</td>
                                <td>0</td><td>0</td><td>0</td><td>0</td><td class="pts">0</td>
                            </tr>
                        <?php endfor; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <p style="text-align:center;color:var(--muted);font-size:13px;margin-top:20px;">
            <strong style="color:var(--green);">P</strong> = Played · <strong style="color:var(--green);">W</strong> = Wins · <strong style="color:var(--green);">D</strong> = Draws · <strong style="color:var(--green);">L</strong> = Losses · <strong style="color:var(--green);">Pts</strong> = Points
        </p>
    </div>
</section>

<!-- HOW TO JOIN -->
<section class="section">
    <div class="container">
        <div class="section-head">
            <h2>How to <span class="accent">Join</span></h2>
            <p>3 simple steps</p>
        </div>
        <div class="steps-grid">
            <div class="step"><span class="step-num">1</span><h3>Contact Admin</h3><p>Click the WhatsApp button above</p></div>
            <div class="step"><span class="step-num">2</span><h3>Pay TZS 2,000</h3><p>Admin sends you payment details</p></div>
            <div class="step"><span class="step-num">3</span><h3>Get Added</h3><p>Your name appears in the points table</p></div>
        </div>
    </div>
</section>

<!-- BOTTOM CTA -->
<section class="betting-cta">
    <div class="container">
        <div class="betting-box">
            <div class="betting-text">
                <span class="badge-live">🏆 16 SLOTS ONLY</span>
                <h2>Spots Fill <span class="accent">Fast!</span></h2>
                <p>Only 16 players can join. First come, first served. Contact the Admin now to secure your spot.</p>
            </div>
            <a href="https://wa.me/<?= $admin_whatsapp ?>?text=<?= urlencode('Hello MILLZ ADMIN, I want to join the eFootball League. My name is: ') ?>"
               target="_blank" rel="noopener" class="btn-whatsapp big">
                <span class="wa-icon">💬</span> Join on WhatsApp
            </a>
        </div>
    </div>
</section>

<!-- FOOTER -->
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
            data.push({ name: pro.name, subtitle: 'eFootball Pro · TZS ' + Number(pro.price).toLocaleString(), icon: '🎯', link: 'playwithpro.php', searchText: pro.name.toLowerCase() });
        });
    }
    const pages = [
        { name: 'Home', subtitle: 'Millz Games', icon: '🏠', link: 'index.php' },
        { name: 'Games', subtitle: 'Coming soon', icon: '🎮', link: 'games.php' },
        { name: 'Play with Pro', subtitle: 'Spin & book', icon: '🎯', link: 'playwithpro.php' },
        { name: 'eFootball League', subtitle: '16 players', icon: '🏆', link: 'league.php' },
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

// ===== STARS =====
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

// ===== ORBS =====
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
</script>
</body>
</html>