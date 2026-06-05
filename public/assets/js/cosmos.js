/* cosmos.js — Ambient space aesthetics: pulsating stars, shooting stars.
   Aesthetic-only. Does not read or write any data, touch the DOM state
   used by main.js, or interfere with admin functions. */

(function () {
    'use strict';

    // Skip entirely in admin panel
    if (document.body.classList.contains('admin-body')) return;
    var prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var saveData = !!(navigator.connection && navigator.connection.saveData);
    var lowMemory = typeof navigator.deviceMemory === 'number' && navigator.deviceMemory <= 4;
    var lowCpu = typeof navigator.hardwareConcurrency === 'number' && navigator.hardwareConcurrency <= 4;
    var lowPowerMode = prefersReducedMotion || saveData || lowMemory || lowCpu;

    if (lowPowerMode) {
        document.documentElement.classList.add('low-power');
    }

    // ─── Pulsating DOM stars (reduced count, slower) ──────────────────────

    (function buildStars() {
        var container = document.createElement('div');
        container.id  = 'cosmos-stars';
        container.setAttribute('aria-hidden', 'true');
        document.body.appendChild(container);

        var count = lowPowerMode ? 14 : 28;

        for (var i = 0; i < count; i++) {
            var isAmber  = Math.random() < 0.35;
            var isBright = Math.random() < 0.07;
            var size     = isBright
                ? (2 + Math.random() * 1.2)
                : (0.7 + Math.random() * 0.9);

            var star = document.createElement('span');
            star.className = 'cosmos-star cosmos-star--' + (isAmber ? 'amber' : 'cold');

            // Slow durations: 10–24 s — gentle shimmer, not a flicker
            var duration = lowPowerMode ? (14 + Math.random() * 16) : (10 + Math.random() * 14);
            var delay    = -(Math.random() * 15);

            star.style.cssText =
                'left:'               + (Math.random() * 100).toFixed(1) + '%;' +
                'top:'                + (Math.random() * 100).toFixed(1) + '%;' +
                'width:'              + size.toFixed(1) + 'px;'                  +
                'height:'             + size.toFixed(1) + 'px;'                  +
                'animation-duration:' + duration.toFixed(1) + 's;'               +
                'animation-delay:'    + delay.toFixed(1) + 's;';

            container.appendChild(star);
        }
    })();

    // ─── Stagger artwork glow animations (::after lives on the card) ─────
    // CSS doesn't let us target ::after delay directly; inject a sheet instead.

    (function staggerGlows() {
        var cards = document.querySelectorAll('.artwork-card, .collection-card');
        if (!cards.length || lowPowerMode) return;
        var rules = [];
        cards.forEach(function (card, i) {
            var delay = ((i * 1.3) % 11).toFixed(1) + 's';
            // Unique class per card so we can write a targeted rule
            var cls = 'cosmos-gc-' + i;
            card.classList.add(cls);
            rules.push('.' + cls + '::after { animation-delay: ' + delay + '; }');
        });
        if (rules.length) {
            var style = document.createElement('style');
            style.textContent = rules.join('\n');
            document.head.appendChild(style);
        }
    })();

    if (lowPowerMode) return;

    // ─── Shooting stars (canvas — loop only runs when a star is active) ───

    var canvas = document.createElement('canvas');
    canvas.id  = 'cosmos-canvas';
    canvas.setAttribute('aria-hidden', 'true');
    canvas.style.cssText =
        'position:fixed;inset:0;pointer-events:none;z-index:9999;' +
        'width:100vw;height:100vh;';
    document.body.appendChild(canvas);

    var ctx = canvas.getContext('2d');

    function resize() {
        canvas.width  = window.innerWidth;
        canvas.height = window.innerHeight;
    }
    resize();
    window.addEventListener('resize', resize);

    var shootingStars = [];
    var loopRunning   = false;

    function spawnShootingStar() {
        var angle       = (Math.PI / 7) + Math.random() * (Math.PI / 5.5);
        var speed       = 9 + Math.random() * 11;
        var tailLen     = 90 + Math.random() * 160;
        var totalFrames = 50 + Math.floor(Math.random() * 30);

        shootingStars.push({
            x:           Math.random() * window.innerWidth  * 0.82,
            y:           Math.random() * window.innerHeight * 0.32,
            dx:          Math.cos(angle) * speed,
            dy:          Math.sin(angle) * speed,
            tailLen:     tailLen,
            frame:       0,
            totalFrames: totalFrames,
            angle:       angle,
        });

        if (!loopRunning) {
            loopRunning = true;
            requestAnimationFrame(loop);
        }
    }

    function drawShootingStars() {
        for (var i = shootingStars.length - 1; i >= 0; i--) {
            var s        = shootingStars[i];
            var progress = s.frame / s.totalFrames;
            var alpha    = progress < 0.12
                ? progress / 0.12
                : progress > 0.62
                    ? (1 - progress) / 0.38
                    : 1;

            var currentTail = s.tailLen * Math.min(progress * 3.5, 1);
            var tx = s.x - Math.cos(s.angle) * currentTail;
            var ty = s.y - Math.sin(s.angle) * currentTail;

            var tg = ctx.createLinearGradient(tx, ty, s.x, s.y);
            tg.addColorStop(0,    'rgba(200,155,60,0)');
            tg.addColorStop(0.45, 'rgba(220,195,110,' + (alpha * 0.28) + ')');
            tg.addColorStop(0.82, 'rgba(255,245,190,' + (alpha * 0.70) + ')');
            tg.addColorStop(1,    'rgba(255,255,240,' + alpha + ')');

            ctx.beginPath();
            ctx.strokeStyle = tg;
            ctx.lineWidth   = 1.6;
            ctx.moveTo(tx, ty);
            ctx.lineTo(s.x, s.y);
            ctx.stroke();

            var hg = ctx.createRadialGradient(s.x, s.y, 0, s.x, s.y, 5);
            hg.addColorStop(0, 'rgba(255,255,240,' + alpha + ')');
            hg.addColorStop(0.4, 'rgba(255,235,140,' + (alpha * 0.7) + ')');
            hg.addColorStop(1, 'rgba(200,155,60,0)');
            ctx.fillStyle = hg;
            ctx.beginPath();
            ctx.arc(s.x, s.y, 5, 0, Math.PI * 2);
            ctx.fill();

            s.x    += s.dx;
            s.y    += s.dy;
            s.frame++;

            if (s.frame >= s.totalFrames) shootingStars.splice(i, 1);
        }
    }

    function loop() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        drawShootingStars();

        if (shootingStars.length > 0) {
            requestAnimationFrame(loop);
        } else {
            loopRunning = false; // pause until next star spawns
        }
    }

    // 3 per minute: first after 6 s, then ~20 s apart with jitter
    setTimeout(spawnShootingStar, 6000);

    (function scheduleNext() {
        var delay = 17000 + Math.random() * 6000;
        setTimeout(function () {
            spawnShootingStar();
            scheduleNext();
        }, delay);
    })();

})();
