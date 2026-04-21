document.addEventListener('DOMContentLoaded', () => {
    // Back to top
    const btn = document.querySelector('[data-back-top]');
    if (btn) {
        const toggle = () => btn.classList.toggle('is-visible', window.scrollY > 360);
        window.addEventListener('scroll', toggle, { passive: true });
        toggle();
        btn.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
    }

    // Header hide on scroll down, show on scroll up
    const hdr = document.querySelector('.site-header');
    if (hdr) {
        let lastY = window.scrollY;
        window.addEventListener('scroll', () => {
            const y = window.scrollY;
            hdr.classList.toggle('hdr--scrolled', y > 10);
            if (y > lastY && y > 60) hdr.classList.add('hdr--hidden');
            else hdr.classList.remove('hdr--hidden');
            lastY = y;
        }, { passive: true });
    }

    // Ripple effect
    document.querySelectorAll('a, button').forEach((node) => {
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

    // Scroll reveal for generic blocks
    const revealEls = document.querySelectorAll('[data-reveal]');
    if (revealEls.length) {
        const obs = new IntersectionObserver(entries => {
            entries.forEach(e => e.target.classList.toggle('is-visible', e.isIntersecting));
        }, { threshold: 0.12 });
        revealEls.forEach(el => obs.observe(el));
    }
});
