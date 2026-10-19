// =====================================================
// MILLZ GAMES — JAVASCRIPT
// =====================================================

// ===== SIDEBAR TOGGLE =====
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('open');
    document.getElementById('sidebarOverlay').classList.toggle('open');
    document.body.style.overflow =
        document.getElementById('sidebar').classList.contains('open') ? 'hidden' : '';
}

// ===== SEARCH TOGGLE =====
function toggleSearch() {
    const searchBar = document.getElementById('searchBar');
    if (!searchBar) return;
    searchBar.classList.toggle('open');

    if (searchBar.classList.contains('open')) {
        window.scrollTo({ top: 0, behavior: 'smooth' });
        setTimeout(() => {
            const inp = document.getElementById('searchInput');
            if (inp) inp.focus();
        }, 350);
    } else {
        clearSearch();
    }
}

function clearSearch() {
    const input = document.getElementById('searchInput');
    const results = document.getElementById('searchResults');
    if (input) input.value = '';
    if (results) results.innerHTML = '';
}

// ===== LIVE SEARCH =====
function getSearchData() {
    const data = [];

    // Pros
    if (typeof PROS !== 'undefined' && PROS.length) {
        PROS.forEach(pro => {
            data.push({
                type: 'Pro Player',
                name: pro.name,
                subtitle: 'eFootball Pro · TZS ' + Number(pro.price).toLocaleString(),
                icon: '🎯',
                link: 'playwithpro.php',
                searchText: pro.name.toLowerCase()
            });
        });
    }

    // Static pages
    const pages = [
        { name: 'Home', subtitle: 'Millz Games Homepage', icon: '🏠', link: 'index.php' },
        { name: 'Games', subtitle: 'All games — coming soon', icon: '🎮', link: 'games.php' },
        { name: 'Play with Pro', subtitle: 'Spin & book a pro', icon: '🎯', link: 'playwithpro.php' },
        { name: 'eFootball League', subtitle: '16 players · TZS 2,000', icon: '🏆', link: 'league.php' },
        { name: 'Beat the Pro', subtitle: 'Challenge a pro', icon: '⚡', link: 'beatthepro.php' },
        { name: '2v2 Team Battle', subtitle: 'Play with a friend', icon: '👥', link: 'team.php' },
        { name: 'Betting Tips', subtitle: 'Daily tips from tipsters', icon: '💰', link: 'categories.php' },
        { name: 'Shop with Us', subtitle: 'Controllers, jerseys, gear', icon: '🛒', link: 'shop.php' },
        { name: 'Community', subtitle: 'Chat with players', icon: '💬', link: 'community.php' },
        { name: 'Login', subtitle: 'Sign in to your account', icon: '🔑', link: 'login.php' },
        { name: 'Register', subtitle: 'Create a new account', icon: '📝', link: 'register.php' }
    ];

    pages.forEach(p => {
        data.push({
            type: 'Page',
            name: p.name,
            subtitle: p.subtitle,
            icon: p.icon,
            link: p.link,
            searchText: (p.name + ' ' + p.subtitle).toLowerCase()
        });
    });

    return data;
}

function liveSearch(query) {
    const resultsBox = document.getElementById('searchResults');
    if (!resultsBox) return;

    query = (query || '').trim().toLowerCase();

    if (query.length === 0) {
        resultsBox.innerHTML = '';
        return;
    }

    const allData = getSearchData();
    const matches = allData.filter(item => item.searchText.includes(query));

    if (matches.length === 0) {
        resultsBox.innerHTML = `
            <div class="search-no-results">
                🔍 No results for "<strong>${query}</strong>"
            </div>
        `;
        return;
    }

    const pros = matches.filter(m => m.type === 'Pro Player');
    const pages = matches.filter(m => m.type === 'Page');

    let html = '';

    if (pros.length > 0) {
        html += `<div class="search-section-title">Pro Players (${pros.length})</div>`;
        pros.forEach(p => {
            html += `
                <a href="${p.link}" class="search-result-item">
                    <div class="search-result-icon">${p.icon}</div>
                    <div class="search-result-text">
                        <strong>${p.name}</strong>
                        <span>${p.subtitle}</span>
                    </div>
                    <div class="search-result-arrow">→</div>
                </a>
            `;
        });
    }

    if (pages.length > 0) {
        html += `<div class="search-section-title">Pages (${pages.length})</div>`;
        pages.forEach(p => {
            html += `
                <a href="${p.link}" class="search-result-item">
                    <div class="search-result-icon">${p.icon}</div>
                    <div class="search-result-text">
                        <strong>${p.name}</strong>
                        <span>${p.subtitle}</span>
                    </div>
                    <div class="search-result-arrow">→</div>
                </a>
            `;
        });
    }

    resultsBox.innerHTML = html;
}

// ===== THEME TOGGLE =====
function toggleTheme() {
    document.body.classList.toggle('light-theme');
    const btn = event.currentTarget;
    if (document.body.classList.contains('light-theme')) {
        btn.innerHTML = '☀️';
        localStorage.setItem('millz-theme', 'light');
    } else {
        btn.innerHTML = '🌙';
        localStorage.setItem('millz-theme', 'dark');
    }
}

window.addEventListener('DOMContentLoaded', () => {
    const saved = localStorage.getItem('millz-theme');
    if (saved === 'light') {
        document.body.classList.add('light-theme');
        const moonBtn = document.querySelector('.nav-right .nav-icon-btn:last-child');
        if (moonBtn) moonBtn.innerHTML = '☀️';
    }
});

// ===== ESC KEY CLOSES =====
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        const sb = document.getElementById('searchBar');
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
function nextSlide() {
    if (slides.length === 0) return;
    currentSlide = (currentSlide + 1) % slides.length;
    showSlide(currentSlide);
}
function goToSlide(index) { showSlide(index); }

if (slides.length > 0) setInterval(nextSlide, 5000);

// ===== COUNTDOWN =====
function startCountdown() {
    const daysEl = document.getElementById('days');
    const hoursEl = document.getElementById('hours');
    const minsEl = document.getElementById('minutes');
    const secsEl = document.getElementById('seconds');
    if (!daysEl) return;

    const target = new Date().getTime()
        + (2 * 24 * 60 * 60 * 1000)
        + (14 * 60 * 60 * 1000)
        + (45 * 60 * 1000);

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

// ===== STAR FIELD =====
function createStars() {
    const container = document.getElementById('stars');
    if (!container) return;
    const colors = ['', 'red', 'yellow'];
    for (let i = 0; i < 70; i++) {
        const star = document.createElement('div');
        const color = colors[Math.floor(Math.random() * colors.length)];
        star.className = 'star ' + color;
        star.style.left = Math.random() * 100 + '%';
        star.style.top = Math.random() * 100 + '%';
        const size = 1 + Math.random() * 3;
        star.style.width = size + 'px';
        star.style.height = size + 'px';
        star.style.animationDelay = Math.random() * 3 + 's';
        star.style.animationDuration = (2 + Math.random() * 3) + 's';
        container.appendChild(star);
    }
}
createStars();

// ===== NEBULA =====
function createNebula() {
    const container = document.getElementById('nebula');
    if (!container) return;
    const colors = ['red', 'gold', 'yellow'];
    for (let i = 0; i < 3; i++) {
        const cloud = document.createElement('div');
        const color = colors[Math.floor(Math.random() * colors.length)];
        cloud.className = 'nebula-cloud ' + color;
        cloud.style.left = Math.random() * 80 + '%';
        cloud.style.top = Math.random() * 80 + '%';
        cloud.style.animationDelay = Math.random() * 10 + 's';
        container.appendChild(cloud);
    }
}
createNebula();

// ===== SMOKE =====
function createSmoke() {
    const container = document.getElementById('smoke');
    if (!container) return;
    const smoke = document.createElement('div');
    smoke.className = 'smoke';
    smoke.style.left = Math.random() * 100 + '%';
    smoke.style.animationDuration = (10 + Math.random() * 10) + 's';
    container.appendChild(smoke);
    setTimeout(() => smoke.remove(), 20000);
}
setInterval(createSmoke, 1500);

// ===== DRONES =====
function createDrone() {
    const container = document.getElementById('drones');
    if (!container) return;
    const drone = document.createElement('div');
    drone.className = 'drone';
    drone.style.left = (Math.random() * 100) + '%';
    drone.style.bottom = (10 + Math.random() * 40) + '%';
    drone.style.animationDuration = (12 + Math.random() * 10) + 's';
    container.appendChild(drone);
    setTimeout(() => drone.remove(), 22000);
}
setInterval(createDrone, 5000);

function createDroneBurst(x, y) {
    const burst = document.createElement('div');
    burst.className = 'drone-burst';
    burst.style.left = x + 'px';
    burst.style.top = y + 'px';
    document.body.appendChild(burst);
    setTimeout(() => burst.remove(), 900);
}

// ===== LIGHTNING =====
function createLightningBolt() {
    const container = document.getElementById('lightning');
    if (!container) return;
    const bolt = document.createElement('div');
    bolt.className = 'lightning-bolt';
    bolt.style.left = (10 + Math.random() * 80) + '%';
    bolt.style.transform = 'scaleX(' + (0.7 + Math.random() * 1) + ')';
    container.appendChild(bolt);

    const flash = document.createElement('div');
    flash.className = 'flash-overlay';
    document.body.appendChild(flash);

    setTimeout(() => { bolt.remove(); flash.remove(); }, 600);
}
function scheduleLightning() {
    const delay = 8000 + Math.random() * 7000;
    setTimeout(() => {
        createLightningBolt();
        if (Math.random() > 0.7) setTimeout(createLightningBolt, 250);
        scheduleLightning();
    }, delay);
}
scheduleLightning();

// ===== SHOCKWAVE ON LOAD =====
window.addEventListener('load', () => {
    const shock = document.getElementById('shockwave');
    if (shock) {
        shock.classList.add('fire');
        setTimeout(() => shock.classList.remove('fire'), 1600);
    }
});

// ===== CLICK BURST =====
document.addEventListener('click', (e) => {
    if (e.target.closest('.btn-solid') || e.target.closest('.btn-outline')) {
        createClickBurst(e.clientX, e.clientY);
        createDroneBurst(e.clientX, e.clientY);
    }
});

function createClickBurst(x, y) {
    const burst = document.createElement('div');
    burst.style.position = 'fixed';
    burst.style.left = x + 'px';
    burst.style.top = y + 'px';
    burst.style.width = '0';
    burst.style.height = '0';
    burst.style.borderRadius = '50%';
    burst.style.border = '3px solid #ff0033';
    burst.style.transform = 'translate(-50%, -50%)';
    burst.style.boxShadow = '0 0 20px #ff0033, 0 0 40px #ff0033';
    burst.style.pointerEvents = 'none';
    burst.style.zIndex = '9999';
    burst.style.transition = 'all 0.6s ease-out';
    document.body.appendChild(burst);
    requestAnimationFrame(() => {
        burst.style.width = '200px';
        burst.style.height = '200px';
        burst.style.opacity = '0';
        burst.style.borderWidth = '1px';
    });
    setTimeout(() => burst.remove(), 700);
}

// ===== METEORS =====
function createMeteor() {
    const layer = document.querySelector('.effects-layer');
    if (!layer) return;
    const meteor = document.createElement('div');
    meteor.className = 'meteor';
    meteor.style.left = (Math.random() * 80 + 10) + '%';
    meteor.style.top = '-10%';
    layer.appendChild(meteor);
    setTimeout(() => meteor.remove(), 3000);
}
setInterval(createMeteor, 3500);

// ===== EMBERS =====
function createEmber() {
    const layer = document.querySelector('.effects-layer');
    if (!layer) return;
    const ember = document.createElement('div');
    ember.className = 'ember';
    ember.style.left = Math.random() * 100 + '%';
    ember.style.bottom = '-10px';
    ember.style.animationDuration = (5 + Math.random() * 5) + 's';
    layer.appendChild(ember);
    setTimeout(() => ember.remove(), 11000);
}
setInterval(createEmber, 500);

// ===== ORBS =====
function createOrbs() {
    const container = document.getElementById('orbs');
    if (!container) return;
    const colors = ['red', 'yellow', 'white', 'gold'];
    for (let i = 0; i < 6; i++) {
        const orb = document.createElement('div');
        const color = colors[Math.floor(Math.random() * colors.length)];
        orb.className = 'orb ' + color;
        const size = 150 + Math.random() * 250;
        orb.style.width = size + 'px';
        orb.style.height = size + 'px';
        orb.style.left = Math.random() * 90 + '%';
        orb.style.top = Math.random() * 90 + '%';
        orb.style.animationDuration = (15 + Math.random() * 15) + 's';
        orb.style.animationDelay = Math.random() * 8 + 's';
        container.appendChild(orb);
    }
}
createOrbs();

// =====================================================
// SLOT SPINNER
// =====================================================
let slotRotation = 0;
let isSlotSpinning = false;

function startTicking() {
    const tickSound = document.getElementById('tickSound');
    if (!tickSound) return;
    tickSound.currentTime = 0;
    tickSound.volume = 0.35;
    tickSound.loop = true;
    tickSound.play().catch(() => {});
}
function stopTicking() {
    const tickSound = document.getElementById('tickSound');
    if (tickSound) { tickSound.pause(); tickSound.currentTime = 0; }
}

function spinSlot() {
    if (isSlotSpinning) return;

    const ring = document.getElementById('slotRing');
    const btn = document.getElementById('spinBtn');
    if (!ring || typeof PROS === 'undefined' || !PROS.length) return;

    isSlotSpinning = true;
    btn.disabled = true;

    document.querySelectorAll('.slot-avatar.winner').forEach(el => el.classList.remove('winner'));

    startTicking();

    const winnerIndex = Math.floor(Math.random() * PROS.length);
    const segmentAngle = 360 / PROS.length;
    const targetAngle = 360 - (winnerIndex * segmentAngle);

    const extraSpins = 6 + Math.floor(Math.random() * 4);
    const finalRotation = slotRotation
        + (extraSpins * 360)
        + ((targetAngle - (slotRotation % 360) + 360) % 360);

    slotRotation = finalRotation;
    ring.style.transform = `translate(-50%, -50%) rotate(${finalRotation}deg)`;

    // Counter-rotate avatars
    const items = document.querySelectorAll('.slot-item');
    items.forEach(item => {
        const wrap = item.querySelector('.slot-avatar-wrap');
        if (!wrap) return;
        const baseAngle = parseFloat(item.dataset.angle || '0');
        const counterRotation = -(baseAngle + finalRotation);
        wrap.style.transition = 'transform 6s cubic-bezier(0.17, 0.67, 0.16, 1)';
        wrap.style.transform = `rotate(${counterRotation}deg)`;
    });

    setTimeout(() => {
        stopTicking();

        const winSound = document.getElementById('winSound');
        if (winSound) {
            winSound.currentTime = 0;
            winSound.volume = 0.7;
            winSound.play().catch(() => {});
        }

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
    const avatar = document.getElementById('winAvatar');
    const name = document.getElementById('winName');
    const price = document.getElementById('winPrice');
    const bookBtn = document.getElementById('winBookBtn');

    avatar.src = pro.avatar;
    name.textContent = pro.name;
    price.textContent = 'TZS ' + Number(pro.price).toLocaleString();

    const msg = encodeURIComponent(
        'Hello MILLZ ADMIN, I spun the slot and got ' + pro.name +
        '. I want to book a match for TZS ' + Number(pro.price).toLocaleString() +
        '. My name is: '
    );
    bookBtn.href = 'https://wa.me/' + ADMIN_WHATSAPP + '?text=' + msg;

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