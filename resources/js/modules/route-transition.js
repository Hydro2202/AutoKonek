/**
 * Page transition: a car drives onto the screen and keeps driving off it once the next page has loaded.
 * It is only used for the essential journeys, a link marked data-transition (for example a listing or a
 * service category). Every other link and every form behaves normally.
 * Skipped for visitors who prefer reduced motion.
 *
 *   <a href="..." data-transition data-rt-label="Sample Fuel Stop">
 */
const STORAGE_KEY = 'ak-rt';
const LEAVE_MS = 430;
const FALLBACK_MS = 1000;

export function initRouteTransition() {
    const overlay = document.getElementById('route-transition');
    if (!overlay) return;

    const root = document.documentElement;
    const label = overlay.querySelector('[data-rt-label]');
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    const store = {
        get: () => { try { return sessionStorage.getItem(STORAGE_KEY); } catch { return null; } },
        set: (value) => { try { sessionStorage.setItem(STORAGE_KEY, value); } catch { /* storage unavailable */ } },
        clear: () => { try { sessionStorage.removeItem(STORAGE_KEY); } catch { /* storage unavailable */ } },
    };

    const reset = () => {
        overlay.classList.remove('is-on', 'is-leaving', 'is-arriving', 'is-fading');
        root.classList.remove('rt-pending');
        store.clear();
    };

    // Arriving: the overlay is already visible (set in <head>), so finish the drive.
    if (root.classList.contains('rt-pending')) {
        const destination = store.get();

        if (reduceMotion) {
            reset();
        } else {
            if (destination) label.textContent = destination;
            overlay.classList.add('is-on', 'is-arriving');

            let finished = false;
            const finish = () => {
                if (finished) return;
                finished = true;
                overlay.classList.add('is-fading');
                setTimeout(reset, 220);
            };

            overlay.querySelector('.rt-car').addEventListener('animationend', finish, { once: true });
            setTimeout(finish, FALLBACK_MS);
        }
    }

    // Leaving: start the drive, then follow the link.
    document.addEventListener('click', (event) => {
        if (reduceMotion || event.defaultPrevented) return;
        if (event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;

        const link = event.target.closest('a[data-transition][href]');
        if (!link || (link.target && link.target !== '_self') || link.hasAttribute('download')) return;

        const url = new URL(link.href, window.location.href);
        const isSamePage = url.pathname === window.location.pathname && url.search === window.location.search;
        if (!/^https?:$/.test(url.protocol) || url.origin !== window.location.origin || isSamePage) return;

        event.preventDefault();

        const name = (link.dataset.rtLabel || link.textContent || '').trim().replace(/\s+/g, ' ').slice(0, 40) || 'the next page';
        label.textContent = name;
        store.set(name);
        overlay.classList.remove('is-arriving', 'is-fading');
        overlay.classList.add('is-on', 'is-leaving');

        setTimeout(() => window.location.assign(url.href), LEAVE_MS);
    });

    // Coming back with the browser's Back button must not leave the overlay on screen.
    window.addEventListener('pageshow', (event) => {
        if (event.persisted) reset();
    });
}
