/**
 * Homepage service board: each listing count flips like a split-flap display, once, on load.
 * The real number is always in the markup (data-flap) so it is readable without JavaScript.
 */
const STEP_MS = 55;
const FLIPS_BEFORE_LANDING = 7;

function flipCell(cell, text) {
    cell.textContent = text;
    cell.classList.remove('is-flipping');
    void cell.offsetWidth; // restart the CSS animation
    cell.classList.add('is-flipping');
}

export function initServiceBoard() {
    const counters = document.querySelectorAll('[data-flap]');
    if (!counters.length || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

    counters.forEach((counter, rowIndex) => {
        const finalText = counter.dataset.flap;
        counter.setAttribute('aria-label', finalText);
        counter.textContent = '';

        const cells = [...finalText].map(() => {
            const cell = document.createElement('span');
            cell.className = 'flap-cell';
            cell.setAttribute('aria-hidden', 'true');
            counter.appendChild(cell);
            return cell;
        });

        cells.forEach((cell, cellIndex) => {
            cell.textContent = '0';
            let flips = 0;
            const start = rowIndex * 120 + cellIndex * 70;

            setTimeout(() => {
                const timer = setInterval(() => {
                    flips += 1;
                    const landed = flips > FLIPS_BEFORE_LANDING + cellIndex * 2;
                    flipCell(cell, landed ? finalText[cellIndex] : String(Math.floor(Math.random() * 10)));
                    if (landed) clearInterval(timer);
                }, STEP_MS);
            }, start);
        });
    });
}
