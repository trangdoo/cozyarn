document.addEventListener('DOMContentLoaded', () => {
    const track = document.querySelector('.carousel-track');
    if (!track) return;
    const slides = Array.from(track.querySelectorAll('.slide-card'));
    if (slides.length === 0) return;
    const prev = document.querySelector('.carousel-nav.prev');
    const next = document.querySelector('.carousel-nav.next');
    const dotsContainer = document.querySelector('[data-dots]');
    const viewport = document.querySelector('.carousel-viewport');

    let index = Math.floor(slides.length / 2);
    let autoplayTimer = null;

    // split h2 text into individual char spans once
    slides.forEach(slide => {
        const h2 = slide.querySelector('.card-content h2');
        if (!h2) return;
        h2.innerHTML = [...h2.textContent].map((ch, i) =>
            ch === ' ' ? '<span class="sc sc--space"> </span>'
                       : `<span class="sc" style="--si:${i}">${ch}</span>`
        ).join('');
    });

    function buildDots() {
        if (!dotsContainer) return;
        dotsContainer.innerHTML = '';
        slides.forEach((_, i) => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.addEventListener('click', () => goTo(i));
            dotsContainer.appendChild(btn);
        });
    }

    function updateDots() {
        if (!dotsContainer) return;
        const btns = Array.from(dotsContainer.children);
        btns.forEach((b, i) => b.classList.toggle('active', i === index));
    }

    function goTo(i) {
        index = ((i % slides.length) + slides.length) % slides.length;

        slides.forEach((s, idx) => {
            s.classList.remove('is-center', 'side');
            if (Math.abs(idx - index) === 1 || Math.abs(idx - index) === slides.length - 1) {
                s.classList.add('side');
            }
        });

        const center = slides[index];
        center.classList.add('is-center');

        // re-trigger text animation on active slide
        const content = center.querySelector('.card-content');
        if (content) {
            content.classList.remove('txt-animate');
            void content.offsetWidth;
            content.classList.add('txt-animate');
        }

        // compute translate to center active slide
        const slideCenter = center.offsetLeft + center.offsetWidth / 2;
        const viewportCenter = viewport.clientWidth / 2;
        const offset = viewportCenter - slideCenter;
        track.style.transform = `translateX(${offset}px)`;

        updateDots();
    }

    function nextSlide() { goTo(index + 1); }
    function prevSlide() { goTo(index - 1); }

    function startAutoplay() {
        stopAutoplay();
        autoplayTimer = window.setInterval(() => {
            nextSlide();
        }, 3200);
    }

    function stopAutoplay() {
        if (autoplayTimer !== null) {
            window.clearInterval(autoplayTimer);
            autoplayTimer = null;
        }
    }

    buildDots();
    goTo(index);
    startAutoplay();

    if (next) next.addEventListener('click', () => { nextSlide(); startAutoplay(); });
    if (prev) prev.addEventListener('click', () => { prevSlide(); startAutoplay(); });
    window.addEventListener('keydown', (event) => {
        if (event.key === 'ArrowRight') { nextSlide(); startAutoplay(); }
        if (event.key === 'ArrowLeft') { prevSlide(); startAutoplay(); }
    });

    // resize handler
    window.addEventListener('resize', () => goTo(index));

    // basic pointer swipe support on track
    let startX = null;
    track.addEventListener('pointerdown', (e) => { startX = e.clientX; track.setPointerCapture(e.pointerId); });
    track.addEventListener('pointerup', (e) => {
        if (startX === null) return;
        const dx = e.clientX - startX;
        if (dx > 40) prevSlide();
        else if (dx < -40) nextSlide();
        startAutoplay();
        startX = null;
    });

    // smooth scroll for nav links
    document.querySelectorAll('[data-nav] a[href^="#"]').forEach(link => {
        link.addEventListener('click', e => {
            const target = document.querySelector(link.getAttribute('href'));
            if (target) { e.preventDefault(); target.scrollIntoView({ behavior: 'smooth' }); }
        });
    });

    // scroll spy — highlight active nav based on section in view
    const navLinks = Array.from(document.querySelectorAll('[data-nav] a[data-section]'));
    const sections = navLinks.map(l => document.getElementById(l.dataset.section)).filter(Boolean);
    const spyObserver = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                navLinks.forEach(l => l.classList.remove('active'));
                const active = navLinks.find(l => l.dataset.section === entry.target.id);
                if (active) active.classList.add('active');
            }
        });
    }, { threshold: 0.3 });
    sections.forEach(s => spyObserver.observe(s));

    // stats bar count-up
    const statsBar = document.querySelector('[data-stats-bar]');
    if (statsBar) {
        const activeTimers = new Map();
        const sbObs = new IntersectionObserver(([e]) => {
            statsBar.classList.toggle('is-visible', e.isIntersecting);
            if (e.isIntersecting) {
                statsBar.querySelectorAll('.stats-bar__num').forEach(el => {
                    if (activeTimers.has(el)) clearInterval(activeTimers.get(el));
                    const target = parseFloat(el.dataset.target);
                    const suffix = el.dataset.suffix || '';
                    const decimal = parseInt(el.dataset.decimal || '0');
                    const steps = 60;
                    const interval = 1800 / steps;
                    let current = 0;
                    const increment = target / steps;
                    const timer = setInterval(() => {
                        current = Math.min(current + increment, target);
                        el.textContent = (decimal > 0 ? current.toFixed(decimal) : Math.floor(current)) + suffix;
                        if (current >= target) { clearInterval(timer); activeTimers.delete(el); }
                    }, interval);
                    activeTimers.set(el, timer);
                });
            } else {
                activeTimers.forEach(t => clearInterval(t));
                activeTimers.clear();
                statsBar.querySelectorAll('.stats-bar__num').forEach(el => {
                    el.textContent = '0';
                });
            }
        }, { threshold: 0.3 });
        sbObs.observe(statsBar);
    }

    // reveal helper — re-triggers every time element enters/leaves viewport
    function revealOn(selector, className) {
        const el = document.querySelector(selector);
        if (!el) return;
        new IntersectionObserver(([e]) => {
            el.classList.toggle(className, e.isIntersecting);
        }, { threshold: 0.1 }).observe(el);
    }
    revealOn('[data-reveal-values]',   'is-visible');
    revealOn('[data-reveal-contact]',  'is-visible');
    revealOn('[data-reveal-timeline]', 'is-visible');
    revealOn('[data-reveal-team]',     'is-visible');
    revealOn('[data-reveal-about]',    'is-visible');
    revealOn('[data-reveal-blog]',     'is-visible');

    // tagline fade-in on scroll
    const tagline = document.querySelector('[data-tagline]');
    if (tagline) {
        new IntersectionObserver(([e]) => {
            tagline.classList.toggle('is-visible', e.isIntersecting);
        }, { threshold: 0.2 }).observe(tagline);
    }

    // bestseller slider + tabs
    const bsSection = document.querySelector('[data-bestseller]');
    if (bsSection) {
        new IntersectionObserver(([e]) => {
            bsSection.classList.toggle('is-visible', e.isIntersecting);
        }, { threshold: 0.1 }).observe(bsSection);

        const pagesEl   = bsSection.querySelector('[data-bs-pages]');
        const navPrev   = bsSection.querySelector('.bs-nav--prev');
        const navNext   = bsSection.querySelector('.bs-nav--next');
        const tabs      = Array.from(bsSection.querySelectorAll('.bs-tab'));
        const allCards  = Array.from(bsSection.querySelectorAll('.bs-card'));
        let visible     = [...allCards];
        let page        = 0;

        function perPage() {
            if (window.innerWidth <= 640)  return 1;
            if (window.innerWidth <= 1024) return 2;
            return 3;
        }

        function pages() { return Math.max(1, Math.ceil(visible.length / perPage())); }

        function buildDots() {
            if (!pagesEl) return;
            pagesEl.innerHTML = '';
            for (let i = 0; i < pages(); i++) {
                const btn = document.createElement('button');
                btn.addEventListener('click', () => goTo(i));
                pagesEl.appendChild(btn);
            }
        }

        function render() {
            const pp = perPage(), start = page * pp;
            allCards.forEach(c => { c.style.display = 'none'; c.classList.remove('card-enter'); });
            visible.slice(start, start + pp).forEach((card, i) => {
                card.style.display = '';
                card.style.animationDelay = `${i * 80}ms`;
                void card.offsetWidth;
                card.classList.add('card-enter');
            });
            if (pagesEl) Array.from(pagesEl.children).forEach((b, i) => b.classList.toggle('active', i === page));
            if (navPrev) navPrev.disabled = page === 0;
            if (navNext) navNext.disabled = page >= pages() - 1;
        }

        function goTo(p) { page = Math.max(0, Math.min(p, pages() - 1)); render(); }

        if (navPrev) navPrev.addEventListener('click', () => goTo(page - 1));
        if (navNext) navNext.addEventListener('click', () => goTo(page + 1));

        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                tabs.forEach(t => t.classList.remove('active'));
                tab.classList.add('active');
                const cat = tab.dataset.tab;
                visible = cat === 'all' ? [...allCards] : allCards.filter(c => c.dataset.category === cat);
                page = 0;
                buildDots();
                render();
            });
        });

        window.addEventListener('resize', () => { buildDots(); render(); });
        buildDots();
        render();
    }

    // ── Universal scroll reveal ──────────────────────────────
    (function () {
        const SELECTORS = [
            '.bs-card',
            '.bp-card',
            '.value-card',
            '.ci-card',
            '.stats-bar__item',
            '.au-left__body',
            '.au-founder',
            '.au-right__body',
            '.au-cta',
            '.au-values li',
            '.contact-form',
            '.contact-map--side',
            '.contact__hero',
            '.section-heading',
            '.section-sub',
            '.section-chip',
            '.contact__title',
            '.contact__wave-line',
            '.contact__sub',
            '.tagline-center',
            '.bestseller__header',
            '.bs-pagination',
        ];

        const rvObs = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                entry.target.classList.toggle('rv--in', entry.isIntersecting);
            });
        }, { threshold: 0.12 });

        document.querySelectorAll(SELECTORS.join(',')).forEach(el => {
            if (el.classList.contains('rv')) return;
            el.classList.add('rv');
            // stagger siblings of the same type in same parent
            const tag = el.classList[0];
            const siblings = Array.from(el.parentNode.children)
                .filter(c => c.classList.contains(tag));
            const idx = siblings.indexOf(el);
            if (idx > 0) el.style.transitionDelay = `${idx * 90}ms`;
            rvObs.observe(el);
        });
    })();

    // char wave on hover for big headings
    function wrapCharsForWave(el) {
        const parts = [];
        function processNode(node) {
            if (node.nodeType === Node.TEXT_NODE) {
                [...node.textContent].forEach(ch => {
                    if (/\s/.test(ch)) {
                        parts.push(ch);
                    } else {
                        const s = document.createElement('span');
                        s.className = 'wc';
                        s.textContent = ch;
                        parts.push(s);
                    }
                });
            } else if (node.nodeName === 'BR') {
                parts.push(node.cloneNode());
            } else if (node.nodeType === Node.ELEMENT_NODE) {
                [...node.childNodes].forEach(processNode);
            }
        }
        [...el.childNodes].forEach(processNode);
        el.innerHTML = '';
        parts.forEach(p => el.appendChild(typeof p === 'string' ? document.createTextNode(p) : p));
        [...el.querySelectorAll('.wc')].forEach((s, i) => s.style.setProperty('--i', i));
    }

    document.querySelectorAll('.au-title, .au-right-title, .bs-card__name').forEach(wrapCharsForWave);

    // reviews — JS scroll marquee with click-to-center
    (function () {
        const section = document.querySelector('.reviews-section');
        if (!section) return;

        const marquee = section.querySelector('.rv-marquee');
        const track   = section.querySelector('.rv-marquee__track');
        if (!marquee || !track) return;

        const SPEED = 0.5;
        let scrollY = 0;
        let targetY = null;
        let paused  = false;
        let rafId   = null;
        let halfH   = 0;

        function init() { halfH = track.scrollHeight / 2; }

        function spotlight() {
            const mRect   = marquee.getBoundingClientRect();
            const centerY = mRect.top + mRect.height / 2;
            const cards   = track.querySelectorAll('.rv-card');
            let closest = null, best = Infinity;
            cards.forEach(c => {
                const r = c.getBoundingClientRect();
                const d = Math.abs((r.top + r.height / 2) - centerY);
                if (d < best) { best = d; closest = c; }
            });
            cards.forEach(c => c.classList.toggle('is-center', c === closest));
        }

        function tick() {
            if (halfH === 0) init();

            if (targetY !== null) {
                const diff = targetY - scrollY;
                if (Math.abs(diff) < 0.8) {
                    scrollY = targetY;
                    if (scrollY >= halfH) scrollY -= halfH;
                    if (scrollY < 0)     scrollY += halfH;
                    targetY = null;
                } else {
                    scrollY += diff * 0.12;
                }
            } else if (!paused) {
                scrollY += SPEED;
                if (scrollY >= halfH) scrollY -= halfH;
            }

            track.style.transform = `translateY(-${scrollY}px)`;
            spotlight();
            rafId = requestAnimationFrame(tick);
        }

        // Click → tween to bring that card to center
        track.querySelectorAll('.rv-card').forEach(card => {
            card.addEventListener('click', () => {
                if (halfH === 0 || card.classList.contains('is-center')) return;
                const mRect   = marquee.getBoundingClientRect();
                const centerY = mRect.top + mRect.height / 2;
                const cRect   = card.getBoundingClientRect();
                const delta   = (cRect.top + cRect.height / 2) - centerY;
                // If backward scroll would go negative, jump to equivalent position in copy2
                if (scrollY + delta < 0) {
                    scrollY += halfH;
                    track.style.transform = `translateY(-${scrollY}px)`;
                }
                targetY = scrollY + delta;
            });
        });

        marquee.addEventListener('mouseenter', () => { paused = true; });
        marquee.addEventListener('mouseleave', () => { paused = false; });

        new IntersectionObserver(([e]) => {
            section.classList.toggle('is-visible', e.isIntersecting);
            if (e.isIntersecting) {
                if (!rafId) { init(); rafId = requestAnimationFrame(tick); }
            } else {
                if (rafId) { cancelAnimationFrame(rafId); rafId = null; }
            }
        }, { threshold: 0.1 }).observe(section);
    })();

    // back-to-top button
    (function () {
        const btn = document.querySelector('[data-back-top]');
        if (!btn) return;
        const toggle = () => btn.classList.toggle('is-visible', window.scrollY > 360);
        window.addEventListener('scroll', toggle, { passive: true });
        toggle();
        btn.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    })();

    // hide header on scroll down, show on scroll up
    (function () {
        const hdr = document.querySelector('.site-header');
        if (!hdr) return;
        let lastY = window.scrollY;
        window.addEventListener('scroll', () => {
            const y = window.scrollY;
            hdr.classList.toggle('hdr--scrolled', y > 10);
            if (y > lastY && y > 60) {
                hdr.classList.add('hdr--hidden');
            } else {
                hdr.classList.remove('hdr--hidden');
            }
            lastY = y;
        }, { passive: true });
    })();

    const interactiveNodes = document.querySelectorAll('a, button');
    interactiveNodes.forEach((node) => {
        node.addEventListener('click', (event) => {
            const target = event.currentTarget;
            const rect = target.getBoundingClientRect();
            const ripple = document.createElement('span');
            ripple.className = 'ripple';
            const size = Math.max(rect.width, rect.height);
            ripple.style.width = `${size}px`;
            ripple.style.height = `${size}px`;
            ripple.style.left = `${event.clientX - rect.left - size / 2}px`;
            ripple.style.top = `${event.clientY - rect.top - size / 2}px`;
            target.appendChild(ripple);
            window.setTimeout(() => ripple.remove(), 550);
        });
    });

});