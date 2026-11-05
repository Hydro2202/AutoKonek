/**
 * Numbers on dashboards count up once when the page opens: <span data-count-up="1234">1,234</span>.
 * The real value is always in the markup, so it is correct without JavaScript and for screen readers.
 */
const DURATION_MS = 700;

export function initCountUp() {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

    document.querySelectorAll('[data-count-up]').forEach((element) => {
        const target = parseInt(element.dataset.countUp, 10);
        if (!Number.isFinite(target) || target < 2) return;

        element.setAttribute('aria-label', element.textContent.trim());
        const start = performance.now();

        const tick = (now) => {
            const progress = Math.min(1, (now - start) / DURATION_MS);
            const eased = 1 - (1 - progress) ** 3; // ease out
            element.textContent = Math.round(target * eased).toLocaleString('en-US');
            if (progress < 1) requestAnimationFrame(tick);
        };

        element.textContent = '0';
        requestAnimationFrame(tick);
    });
}