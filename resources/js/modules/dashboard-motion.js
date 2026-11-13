/**
 * Dashboard numbers count up from zero, once, when the page opens.
 * The real number is already in the markup (data-count), so it reads without JavaScript;
 * screen readers get the final number only, never the moving one.
 */
export function initDashboardMotion() {
    const counters = document.querySelectorAll('[data-count]');
    if (!counters.length || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

    counters.forEach((node, index) => {
        const target = Number(node.dataset.count);
        const decimals = Number(node.dataset.decimals || 0);
        if (!Number.isFinite(target) || target === 0) return;

        const format = (value) => value.toLocaleString('en-PH', { minimumFractionDigits: decimals, maximumFractionDigits: decimals });

        // Keep the final value for assistive technology; animate a visual copy.
        const finalText = document.createElement('span');
        finalText.className = 'sr-only';
        finalText.textContent = format(target);
        const visual = document.createElement('span');
        visual.setAttribute('aria-hidden', 'true');
        visual.textContent = format(0);
        node.replaceChildren(visual, finalText);

        const duration = 1000 + Math.min(index, 6) * 60;
        const delay = 150 + Math.min(index, 8) * 70;
        let started = null;

        const frame = (now) => {
            started ??= now;
            const t = Math.min(1, (now - started) / duration);
            const eased = 1 - (1 - t) ** 4;
            visual.textContent = format(target * eased);
            if (t < 1) requestAnimationFrame(frame);
            else visual.textContent = format(target);
        };
        setTimeout(() => requestAnimationFrame(frame), delay);
    });
}
