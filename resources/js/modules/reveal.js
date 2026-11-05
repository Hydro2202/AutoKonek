/** Adds "is-visible" to .draw-line elements the first time they scroll into view. */
export function initReveal() {
    const items = document.querySelectorAll('.draw-line');
    if (!items.length) return;

    if (!('IntersectionObserver' in window)) {
        items.forEach((item) => item.classList.add('is-visible'));
        return;
    }

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.4 });

    items.forEach((item) => observer.observe(item));
}