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
<title>MILLZ GAMES - Gaming Website | The Pitch Is Ours.</title>
<link rel="stylesheet" href="assets/css/style.css?v=2025">
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
            <span>⚽ eFootball League — 16 Players — Entry TZS 2,000 — Join Now!</span>
            <span>🏆 Winner takes TZS 15,000 — Register today</span>
            <span>🎯 Play with Pro — Entry TZS 1,000</span>
            <span>💰 Betting Tips — Check them now!</span>
            <span>⚽ eFootball League — 16 Players — Entry TZS 2,000 — Join Now!</span>
            <span>🏆 Winner takes TZS 15,000 — Register today</span>
            <span>🎯 Play with Pro — Entry TZS 1,000</span>
            <span>💰 Betting Tips — Check them now!</span>
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

<!-- ===== SEARCH OVERLAY (FULL SCREEN) ===== -->
<div class="search-overlay" id="searchOverlay">
    <button class="search-close-top" onclick="toggleSearch()">✕</button>
    <div class="search-hero">
        <div class="search-icon-big">🔍</div>
        <h2 class="search-title">SEARCH MILLZ GAMES</h2>
        <div class="search-inner">
            <input type="text" id="searchInput" placeholder="Type to search pros, games, tips..." autocomplete="off" oninput="liveSearch(this.value)">
        </div>
        <div class="search-suggestions">
            <span class="suggestion-label">Popular Searches</span>
            <span class="suggestion-tag" onclick="quickSearch('skudu')">SKUDU</span>
            <span class="suggestion-tag" onclick="quickSearch('pro')">Pro</span>
            <span class="suggestion-tag" onclick="quickSearch('league')">League</span>
            <span class="suggestion-tag" onclick="quickSearch('tips')">Tips</span>
            <span class="suggestion-tag" onclick="quickSearch('shop')">Shop</span>
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
        <li><a href="index.php" class="active">🏠 Home</a></li>
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

<!-- ===== HERO ===== -->
<section class="hero">
    <div class="hero-slideshow">
        <div class="hero-slide active" style="background-image:url('assets/images/hero-bg.jpg')"></div>
        <div class="hero-slide" style="background-image:url('assets/images/game1.jpg')"></div>
        <div class="hero-slide" style="background-image:url('assets/images/game2.jpg')"></div>
        <div class="hero-slide" style="background-image:url('assets/images/game3.jpg')"></div>
    </div>
    <div class="hero-overlay"></div>
    <div class="hero-content">
        <span class="badge">🔥 GAMING WEBSITE</span>
        <h1>
            <span class="hero-small">MILLZ GAMES · GAMING WEBSITE</span>
            THE PITCH IS OURS.
        </h1>
        <p class="hero-money">WIN REAL MONEY.</p>
        <p class="hero-tips">BETTING TIPS BELOW ↓</p>
        <p>Challenge Pro players. Join tournaments. Cash out instantly via Airtel Money.</p>
        <div class="hero-buttons">
            <a href="playwithpro.php" class="btn-solid big">Start Playing</a>
            <a href="playwithpro.php" class="btn-outline big">Play with Pro</a>
        </div>
        <div class="hero-stats">
            <div><strong>10K+</strong><span>Players</span></div>
            <div><strong>500+</strong><span>Matches/Day</span></div>
            <div><strong>TZS 15M+</strong><span>Prizes Paid</span></div>
        </div>
    </div>
    <div class="slide-dots">
        <span class="dot active" onclick="goToSlide(0)"></span>
        <span class="dot" onclick="goToSlide(1)"></span>
        <span class="dot" onclick="goToSlide(2)"></span>
        <span class="dot" onclick="goToSlide(3)"></span>
    </div>
</section>

<!-- ===== BETTING CTA ===== -->
<section class="betting-cta">
    <div class="container">
        <div class="betting-box">
            <div class="betting-text">
                <span class="badge-live">💰 BETTING TIPS</span>
                <h2>Daily Football & <span class="accent">Betting Tips</span></h2>
                <p>Get expert tips for every match — Premier League, La Liga, Serie A & more.</p>
            </div>
            <a href="categories.php" class="btn-solid big">View Tips →</a>
        </div>
    </div>
</section>

<!-- ===== CATEGORIES ===== -->
<section class="section">
    <div class="container">
        <div class="section-head">
            <h2>Browse <span class="accent">Categories</span></h2>
            <p>Everything you need — games, leagues, pros, and gear</p>
        </div>
        <div class="cat-grid">
            <a href="games.php" class="cat-card">
                <div class="cat-icon">🎮</div>
                <h3>Games</h3>
                <p>More games coming soon</p>
                <span class="cat-badge">SOON</span>
            </a>
            <a href="playwithpro.php" class="cat-card">
                <div class="cat-icon">🎯</div>
                <h3>Play with Pro</h3>
                <p>Spin & book — TZS 1,000</p>
            </a>
            <a href="league.php" class="cat-card">
                <div class="cat-icon">🏆</div>
                <h3>eFootball League</h3>
                <p>16 players · TZS 2,000 entry</p>
            </a>
            <a href="beatthepro.php" class="cat-card">
                <div class="cat-icon">⚡</div>
                <h3>Beat the Pro</h3>
                <p>Challenge & win big</p>
            </a>
            <a href="team.php" class="cat-card">
                <div class="cat-icon">👥</div>
                <h3>2v2 Team Battle</h3>
                <p>TZS 2,000 entry</p>
            </a>
            <a href="categories.php" class="cat-card">
                <div class="cat-icon">💰</div>
                <h3>Betting Tips</h3>
                <p>Daily football tips</p>
            </a>
            <a href="shop.php" class="cat-card">
                <div class="cat-icon">🛒</div>
                <h3>Shop with Us</h3>
                <p>Controllers, jerseys</p>
            </a>
            <a href="community.php" class="cat-card">
                <div class="cat-icon">💬</div>
                <h3>Community</h3>
                <p>Chat with players</p>
            </a>
        </div>
    </div>
</section>

<!-- ===== FEATURED PROS ===== -->
<section class="section dark">
    <div class="container">
        <div class="section-head">
            <h2>⭐ Featured <span class="accent">Pros</span></h2>
            <p>Spin & book instantly</p>
        </div>
        <div class="pros-strip">
            <?php foreach ($pros as $pro): ?>
                <a href="playwithpro.php" class="pro-strip-card">
                    <img src="<?= htmlspecialchars($pro['avatar']) ?>"
                         alt="<?= htmlspecialchars($pro['name']) ?>"
                         class="pro-strip-avatar"
                         onerror="this.src='https://api.dicebear.com/7.x/bottts/svg?seed=<?= urlencode($pro['name']) ?>&backgroundColor=ffd700&radius=50'">
                    <div class="pro-strip-name"><?= htmlspecialchars($pro['name']) ?></div>
                    <div class="pro-strip-price">TZS <?= number_format($pro['price']) ?></div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ===== LEAGUE ===== -->
<section class="section">
    <div class="container">
        <div class="section-head">
            <h2>🏆 eFootball <span class="accent">League</span></h2>
            <p>16 players · Points table · Winner takes TZS 15,000</p>
        </div>
        <div class="league-preview">
            <div class="league-info">
                <h3>MILLZ Premier League</h3>
                <ul class="league-details">
                    <li>👥 <strong>16 Players</strong></li>
                    <li>💰 <strong>Entry: TZS 2,000</strong></li>
                    <li>🏆 <strong>Winner: TZS 15,000</strong></li>
                    <li>📊 <strong>Points Table Format</strong></li>
                    <li>📅 <strong>Season: Ongoing</strong></li>
                </ul>
                <a href="league.php" class="btn-solid big">Join League</a>
            </div>
            <div class="league-img" style="background-image:url('assets/images/game2.jpg')"></div>
        </div>
    </div>
</section>

<!-- ===== COUNTDOWN ===== -->
<section class="countdown-section">
    <div class="container">
        <div class="countdown-box">
            <div class="countdown-info">
                <span class="badge-live">🏆 NEXT TOURNAMENT</span>
                <h2>MILLZ <span class="accent">Championship</span></h2>
                <p>16 Players · Entry TZS 2,000 · Winner TZS 15,000</p>
            </div>
            <div class="countdown-timer" id="countdown">
                <div class="time-block"><span class="time-num" id="days">02</span><span class="time-label">Days</span></div>
                <div class="time-block"><span class="time-num" id="hours">14</span><span class="time-label">Hours</span></div>
                <div class="time-block"><span class="time-num" id="minutes">45</span><span class="time-label">Mins</span></div>
                <div class="time-block"><span class="time-num" id="seconds">30</span><span class="time-label">Secs</span></div>
            </div>
            <a href="league.php" class="btn-solid big">Register Now</a>
        </div>
    </div>
</section>

<!-- ===== LEADERBOARD ===== -->
<section class="section dark">
    <div class="container">
        <div class="section-head">
            <h2>🏆 Top <span class="accent">Players</span> This Week</h2>
            <p>Real winners. Real cashouts.</p>
        </div>
        <div class="leaderboard">
            <div class="leader-row top-1">
                <span class="rank">🥇</span>
                <div class="player-info"><div class="avatar">A</div><div><strong>Alex M.</strong><span>32 wins · 89% win rate</span></div></div>
                <span class="winnings">TZS 45,000</span>
            </div>
            <div class="leader-row top-2">
                <span class="rank">🥈</span>
                <div class="player-info"><div class="avatar">S</div><div><strong>Sarah K.</strong><span>28 wins · 82% win rate</span></div></div>
                <span class="winnings">TZS 32,000</span>
            </div>
            <div class="leader-row top-3">
                <span class="rank">🥉</span>
                <div class="player-info"><div class="avatar">J</div><div><strong>John D.</strong><span>25 wins · 78% win rate</span></div></div>
                <span class="winnings">TZS 28,000</span>
            </div>
            <div class="leader-row">
                <span class="rank">4</span>
                <div class="player-info"><div class="avatar">M</div><div><strong>Mike R.</strong><span>22 wins · 74% win rate</span></div></div>
                <span class="winnings">TZS 21,000</span>
            </div>
            <div class="leader-row">
                <span class="rank">5</span>
                <div class="player-info"><div class="avatar">D</div><div><strong>David T.</strong><span>19 wins · 70% win rate</span></div></div>
                <span class="winnings">TZS 17,500</span>
            </div>
        </div>
    </div>
</section>

<!-- ===== SHOP CTA ===== -->
<section class="shop-cta">
    <div class="container">
        <div class="shop-cta-inner">
            <div>
                <h2>🛒 Shop with <span class="accent">MILLZ GAMES</span></h2>
                <p>Controllers, jerseys, gaming gear — delivered to your door.</p>
            </div>
            <a href="shop.php" class="btn-solid big">Visit Shop</a>
        </div>
    </div>
</section>

<!-- ===== COMMUNITY ===== -->
<section class="section">
    <div class="container">
        <div class="section-head">
            <h2>💬 Join the <span class="accent">Community</span></h2>
            <p>Chat with players. Share tips. Find teammates.</p>
        </div>
        <div class="community-cta">
            <a href="community.php" class="btn-outline big">Enter Community →</a>
        </div>
    </div>
</section>

<!-- ===== FOOTER ===== -->
<footer>
    <div class="container footer-grid">
        <div>
            <h3>MILLZ GAMES</h3>
            <p>The pitch is ours. Play eFootball. Win real money. Cash out via Airtel Money.</p>
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

<script>
const PROS = <?= json_encode($pros) ?>;

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
    if (input) {
        input.value = query;
        liveSearch(query);
    }
}

function clearSearch() {
    const i = document.getElementById('searchInput');
    const r = document.getElementById('searchResults');
    if (i) i.value = '';
    if (r) r.innerHTML = '';
}

// ===== SEARCH DATA =====
function getSearchData() {
    const data = [];
    if (PROS && PROS.length) {
        PROS.forEach(pro => {
            data.push({ name: pro.name, subtitle: 'eFootball Pro · TZS ' + Number(pro.price).toLocaleString(), icon: '🎯', link: 'playwithpro.php', searchText: pro.name.toLowerCase() });
        });
    }
    const pages = [
        { name: 'Home', subtitle: 'Millz Games Homepage', icon: '🏠', link: 'index.php' },
        { name: 'Games', subtitle: 'Coming soon', icon: '🎮', link: 'games.php' },
        { name: 'Play with Pro', subtitle: 'Spin & book a pro', icon: '🎯', link: 'playwithpro.php' },
        { name: 'eFootball League', subtitle: '16 players · TZS 2,000', icon: '🏆', link: 'league.php' },
        { name: 'Beat the Pro', subtitle: 'Challenge a pro', icon: '⚡', link: 'beatthepro.php' },
        { name: '2v2 Team Battle', subtitle: 'Play with a friend', icon: '👥', link: 'team.php' },
        { name: 'Betting Tips', subtitle: 'Daily tips', icon: '💰', link: 'categories.php' },
        { name: 'Shop with Us', subtitle: 'Controllers, jerseys', icon: '🛒', link: 'shop.php' },
        { name: 'Community', subtitle: 'Chat with players', icon: '💬', link: 'community.php' }
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

// ===== THEME =====
function toggleTheme() {
    document.body.classList.toggle('light-theme');
    event.currentTarget.innerHTML = document.body.classList.contains('light-theme') ? '☀️' : '🌙';
}

// ===== ESC CLOSE =====
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        const sb = document.getElementById('searchOverlay');
        if (sb && sb.classList.contains('open')) toggleSearch();
        const sidebar = document.getElementById('sidebar');
        if (sidebar && sidebar.classList.contains('open')) toggleSidebar();
    }
});

// ===== HERO SLIDESHOW =====
let currentSlide = 0;
const slides = document.querySelectorAll('.hero-slide');
const dots = document.querySelectorAll('.dot');
function showSlide(index) {
    slides.forEach((s, i) => s.classList.toggle('active', i === index));
    dots.forEach((d, i) => d.classList.toggle('active', i === index));
    currentSlide = index;
}
function nextSlide() { if (slides.length === 0) return; currentSlide = (currentSlide + 1) % slides.length; showSlide(currentSlide); }
function goToSlide(index) { showSlide(index); }
if (slides.length > 0) setInterval(nextSlide, 5000);

// ===== COUNTDOWN =====
function startCountdown() {
    const daysEl = document.getElementById('days');
    const hoursEl = document.getElementById('hours');
    const minsEl = document.getElementById('minutes');
    const secsEl = document.getElementById('seconds');
    if (!daysEl) return;
    const target = new Date().getTime() + (2 * 24 * 60 * 60 * 1000) + (14 * 60 * 60 * 1000) + (45 * 60 * 1000);
    setInterval(() => {
        const diff = target - new Date().getTime();
        const days = Math.floor(diff / (1000 * 60 * 60 * 24));
        const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((diff % (1000 * 60)) / 1000);
        daysEl.textContent = String(days).padStart(2, '0');
        hoursEl.textContent = String(hours).padStart(2, '0');
        minsEl.textContent = String(minutes).padStart(2, '0');
        secsEl.textContent = String(seconds).padStart(2, '0');
    }, 1000);
}
startCountdown();

// ===== STARS =====
function createStars() {
    const c = document.getElementById('stars'); if (!c) return;
    const colors = ['', 'gold'];
    for (let i = 0; i < 70; i++) {
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