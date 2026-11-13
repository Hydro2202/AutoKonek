/**
 * Fuel price watch: a trading-style weekly price chart for gasoline, diesel and kerosene.
 *
 * The page already lists every price (tiles + table); this adds:
 *  - prices that count up when the board scrolls into view,
 *  - a sparkline per fuel tile,
 *  - the main chart: a glowing line that draws itself, a soft area, DOE adjustment bars
 *    (red up = hike, green down = rollback) and a pulsing "latest" point,
 *  - a crosshair + tooltip on hover, and the same readout with the arrow keys.
 * Labels are set with textContent only. Motion is skipped for reduced-motion users.
 */
const SVG = 'http://www.w3.org/2000/svg';
const reducedMotion = () => window.matchMedia('(prefers-reduced-motion: reduce)').matches;

const peso = (value) => '₱' + value.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
const shortDate = (iso) => new Date(iso + 'T00:00:00').toLocaleDateString('en-PH', { month: 'short', day: 'numeric' });
const longDate = (iso) => new Date(iso + 'T00:00:00').toLocaleDateString('en-PH', { month: 'long', day: 'numeric', year: 'numeric' });

function el(name, attributes = {}, parent = null) {
    const node = document.createElementNS(SVG, name);
    Object.entries(attributes).forEach(([key, value]) => node.setAttribute(key, value));
    if (parent) parent.appendChild(node);
    return node;
}

function describeChange(change) {
    if (change === null || change === undefined) return 'First week on record';
    if (change > 0) return `▲ ${peso(change)} hike`;
    if (change < 0) return `▼ ${peso(Math.abs(change))} rollback`;
    return 'No change';
}

/** Rounded steps for the price axis. */
function niceTicks(min, max, count = 4) {
    const rawStep = (max - min) / count;
    const magnitude = 10 ** Math.floor(Math.log10(rawStep));
    const step = [1, 2, 2.5, 5, 10].map((m) => m * magnitude).find((s) => s >= rawStep) ?? rawStep;
    const ticks = [];
    for (let value = Math.floor(min / step) * step; value <= max + 1e-9; value += step) ticks.push(Number(value.toFixed(2)));
    return ticks;
}

function countUp(node, to) {
    if (reducedMotion()) return;
    const duration = 1100;
    const from = to * 0.82;
    const started = performance.now();
    const frame = (now) => {
        const t = Math.min(1, (now - started) / duration);
        const eased = 1 - (1 - t) ** 3;
        node.textContent = peso(from + (to - from) * eased);
        if (t < 1) requestAnimationFrame(frame);
    };
    requestAnimationFrame(frame);
}

function drawSparkline(svg, points) {
    if (points.length < 2) return;
    const prices = points.map((p) => p.price);
    const min = Math.min(...prices);
    const max = Math.max(...prices);
    const x = (i) => (i / (points.length - 1)) * 120;
    const y = (price) => 32 - ((price - min) / (max - min || 1)) * 28;
    const d = points.map((p, i) => `${i ? 'L' : 'M'}${x(i).toFixed(1)},${y(p.price).toFixed(1)}`).join('');
    const last = points[points.length - 1];
    const color = (last.change ?? 0) > 0 ? 'var(--color-hike)' : (last.change ?? 0) < 0 ? 'var(--color-rollback)' : 'rgb(255 255 255 / 0.6)';

    svg.replaceChildren();
    el('path', { d, fill: 'none', stroke: color, 'stroke-width': 2, 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'vector-effect': 'non-scaling-stroke' }, svg);
    el('circle', { cx: x(points.length - 1), cy: y(last.price), r: 2.5, fill: color }, svg);
}

function createChart(board, data) {
    const panel = board.querySelector('[data-fuel-panel]');
    const host = board.querySelector('[data-fuel-chart]');
    const tooltip = board.querySelector('[data-fuel-tooltip]');
    const live = board.querySelector('[data-fuel-live]');
    const stats = board.querySelector('[data-fuel-stats]');
    const tabs = [...board.querySelectorAll('[data-fuel-tab]')];
    const ranges = [...board.querySelectorAll('[data-fuel-range]')];

    const state = { fuel: tabs[0].dataset.fuelTab, weeks: 0, index: null };
    let svg = null;
    let layout = null;

    panel.hidden = false;

    const visiblePoints = () => {
        const all = data[state.fuel].points;
        return state.weeks > 0 ? all.slice(-state.weeks) : all;
    };

    function renderStats(points) {
        const prices = points.map((p) => p.price);
        const first = points[0].price;
        const last = points[points.length - 1].price;
        const net = last - first;
        const rows = [
            ['High', peso(Math.max(...prices))],
            ['Low', peso(Math.min(...prices))],
            ['Net change', `${net > 0 ? '+' : net < 0 ? '−' : ''}${peso(Math.abs(net))}`],
            ['Weeks', String(points.length)],
        ];
        stats.replaceChildren();
        rows.forEach(([term, value]) => {
            const wrap = document.createElement('div');
            const dt = document.createElement('dt');
            const dd = document.createElement('dd');
            dt.textContent = term;
            dd.textContent = value;
            if (term === 'Net change') dd.style.color = net > 0 ? 'var(--color-hike)' : net < 0 ? 'var(--color-rollback)' : '';
            wrap.append(dt, dd);
            stats.appendChild(wrap);
        });
    }

    function render(animate) {
        const points = visiblePoints();
        const width = Math.max(host.clientWidth, 280);
        const compact = width < 520;
        const margin = { top: 14, right: 18, bottom: 26, left: compact ? 46 : 54 };
        const priceHeight = compact ? 190 : 250;
        const barHeight = compact ? 64 : 96;
        const gap = 18;
        const height = margin.top + priceHeight + gap + barHeight + margin.bottom;

        const prices = points.map((p) => p.price);
        const pad = Math.max((Math.max(...prices) - Math.min(...prices)) * 0.06, 0.5);
        const ticks = niceTicks(Math.min(...prices) - pad, Math.max(...prices) + pad, 5);
        const yMin = ticks[0];
        const yMax = ticks[ticks.length - 1];
        const innerWidth = width - margin.left - margin.right;
        const step = points.length > 1 ? innerWidth / (points.length - 1) : 0;
        const x = (i) => margin.left + (points.length > 1 ? i * step : innerWidth / 2);
        const y = (price) => margin.top + (1 - (price - yMin) / (yMax - yMin)) * priceHeight;

        const changes = points.map((p) => p.change ?? 0);
        const maxChange = Math.max(...changes.map(Math.abs), 1);
        const barZero = margin.top + priceHeight + gap + barHeight / 2;
        const barScale = (barHeight / 2 - 2) / maxChange;

        layout = { points, x, y, width, height, margin, priceHeight, barZero };

        if (svg) svg.remove();
        svg = el('svg', {
            viewBox: `0 0 ${width} ${height}`,
            height,
            role: 'img',
            tabindex: 0,
            'aria-label': `${data[state.fuel].label} price per liter, ${points.length} weeks from ${longDate(points[0].date)} to ${longDate(points[points.length - 1].date)}. Latest ${peso(prices[prices.length - 1])}. Use the left and right arrow keys to read each week.`,
        });
        host.insertBefore(svg, tooltip);

        const defs = el('defs', {}, svg);
        const gradient = el('linearGradient', { id: 'fuel-area-fill', x1: 0, y1: 0, x2: 0, y2: 1 }, defs);
        el('stop', { offset: '0%', 'stop-color': '#F28C28', 'stop-opacity': 0.38 }, gradient);
        el('stop', { offset: '100%', 'stop-color': '#F28C28', 'stop-opacity': 0 }, gradient);

        // Recessive grid and price axis.
        ticks.forEach((tick) => {
            el('line', { class: 'fuel-gridline', x1: margin.left, x2: width - margin.right, y1: y(tick), y2: y(tick) }, svg);
            el('text', { class: 'fuel-axis', x: margin.left - 8, y: y(tick) + 4, 'text-anchor': 'end' }, svg).textContent = '₱' + tick.toFixed(0);
        });

        // Date axis: a handful of labels, always including the newest week.
        const labelEvery = Math.max(1, Math.ceil(points.length / (compact ? 4 : 7)));
        points.forEach((p, i) => {
            if (i % labelEvery !== 0 && i !== points.length - 1) return;
            if (i !== points.length - 1 && points.length - 1 - i < labelEvery / 2) return;
            el('text', { class: 'fuel-axis', x: x(i), y: height - 6, 'text-anchor': i === 0 ? 'start' : i === points.length - 1 ? 'end' : 'middle' }, svg).textContent = shortDate(p.date);
        });

        // Area + line.
        const line = points.map((p, i) => `${i ? 'L' : 'M'}${x(i).toFixed(1)},${y(p.price).toFixed(1)}`).join('');
        const area = el('path', { class: 'fuel-area', d: `${line}L${x(points.length - 1)},${margin.top + priceHeight}L${x(0)},${margin.top + priceHeight}Z`, fill: 'url(#fuel-area-fill)' }, svg);
        const path = el('path', { class: 'fuel-line', d: line }, svg);

        // Adjustment "volume" bars, from a zero line: hikes up, rollbacks down.
        el('line', { class: 'fuel-gridline', x1: margin.left, x2: width - margin.right, y1: barZero, y2: barZero }, svg);
        el('text', { class: 'fuel-axis', x: margin.left - 8, y: barZero + 4, 'text-anchor': 'end' }, svg).textContent = '±0';
        const barWidth = Math.max(3, Math.min(14, step * 0.45 || 14));
        const bars = [];
        points.forEach((p, i) => {
            if (!p.change) return;
            const h = Math.max(2, Math.abs(p.change) * barScale);
            const hike = p.change > 0;
            bars.push(el('rect', {
                class: `fuel-bar ${hike ? 'is-hike' : 'is-rollback'}`,
                x: x(i) - barWidth / 2,
                y: hike ? barZero - h : barZero,
                width: barWidth,
                height: h,
                rx: Math.min(3, barWidth / 2),
            }, svg));
        });

        // Latest point: solid dot with a pulsing halo.
        const lastX = x(points.length - 1);
        const lastY = y(prices[prices.length - 1]);
        el('circle', { class: 'fuel-pulse', cx: lastX, cy: lastY, r: 5 }, svg);
        el('circle', { cx: lastX, cy: lastY, r: 5, fill: '#F28C28', stroke: '#0a1828', 'stroke-width': 2 }, svg);

        // Hover layer: crosshair + focus dot, hidden until used.
        const cross = el('line', { class: 'fuel-crosshair', y1: margin.top, y2: barZero + barHeight / 2, visibility: 'hidden' }, svg);
        const focusDot = el('circle', { r: 6, fill: '#fff', stroke: '#F28C28', 'stroke-width': 3, visibility: 'hidden' }, svg);
        layout.cross = cross;
        layout.focusDot = focusDot;

        renderStats(points);

        if (animate && !reducedMotion()) {
            const length = path.getTotalLength();
            path.style.strokeDasharray = `${length}`;
            path.style.strokeDashoffset = `${length}`;
            area.style.opacity = '0';
            bars.forEach((bar) => { bar.style.transform = 'scaleY(0)'; });
            requestAnimationFrame(() => requestAnimationFrame(() => {
                path.classList.add('is-drawing');
                path.style.strokeDashoffset = '0';
                area.style.opacity = '1';
                bars.forEach((bar, i) => {
                    bar.style.transitionDelay = `${300 + i * 45}ms`;
                    bar.style.transform = 'scaleY(1)';
                });
            }));
        }

        if (state.index !== null) showPoint(Math.min(state.index, points.length - 1), false);
    }

    function showPoint(index, announce) {
        if (!layout) return;
        const { points, x, y, width, cross, focusDot } = layout;
        const point = points[index];
        state.index = index;

        cross.setAttribute('x1', x(index));
        cross.setAttribute('x2', x(index));
        cross.setAttribute('visibility', 'visible');
        focusDot.setAttribute('cx', x(index));
        focusDot.setAttribute('cy', y(point.price));
        focusDot.setAttribute('visibility', 'visible');

        // Tooltip: value first, then the week and the change.
        tooltip.replaceChildren();
        const value = document.createElement('p');
        value.className = 'font-display text-2xl font-semibold leading-none text-white';
        value.textContent = peso(point.price);
        const week = document.createElement('p');
        week.className = 'mt-1 text-white/65';
        week.textContent = `Week of ${longDate(point.date)}`;
        const change = document.createElement('p');
        change.className = 'mt-0.5 font-semibold';
        change.style.color = point.change > 0 ? 'var(--color-hike)' : point.change < 0 ? 'var(--color-rollback)' : 'rgb(255 255 255 / 0.7)';
        change.textContent = describeChange(point.change);
        tooltip.append(value, week, change);
        tooltip.hidden = false;

        const scale = host.clientWidth / width;
        const left = x(index) * scale;
        const tipWidth = tooltip.offsetWidth;
        tooltip.style.left = `${left + 14 + tipWidth > host.clientWidth ? left - tipWidth - 14 : left + 14}px`;
        tooltip.style.top = `${Math.max(0, y(point.price) * scale - 70)}px`;

        if (announce) live.textContent = `${data[state.fuel].label}, week of ${longDate(point.date)}: ${peso(point.price)}. ${describeChange(point.change)}.`;
    }

    function hidePoint() {
        state.index = null;
        tooltip.hidden = true;
        layout?.cross.setAttribute('visibility', 'hidden');
        layout?.focusDot.setAttribute('visibility', 'hidden');
    }

    // The crosshair finds the nearest week, wherever the pointer is.
    host.addEventListener('pointermove', (event) => {
        if (!layout || event.target === tooltip) return;
        const rect = host.getBoundingClientRect();
        const px = ((event.clientX - rect.left) / rect.width) * layout.width;
        const { points, x } = layout;
        let nearest = 0;
        points.forEach((_, i) => { if (Math.abs(x(i) - px) < Math.abs(x(nearest) - px)) nearest = i; });
        showPoint(nearest, false);
    });
    host.addEventListener('pointerleave', () => { if (document.activeElement !== svg) hidePoint(); });

    host.addEventListener('keydown', (event) => {
        if (!layout || event.target !== svg) return;
        const last = layout.points.length - 1;
        const current = state.index ?? last;
        const next = { ArrowLeft: current - 1, ArrowRight: current + 1, Home: 0, End: last }[event.key];
        if (next === undefined) return;
        event.preventDefault();
        showPoint(Math.max(0, Math.min(last, next)), true);
    });
    host.addEventListener('focusin', (event) => { if (event.target === svg) showPoint(state.index ?? layout.points.length - 1, true); });
    host.addEventListener('focusout', hidePoint);

    // Fuel tabs, with arrow-key movement between them.
    const selectTab = (tab, focus) => {
        tabs.forEach((t) => {
            const selected = t === tab;
            t.setAttribute('aria-selected', String(selected));
            t.tabIndex = selected ? 0 : -1;
        });
        panel.setAttribute('aria-labelledby', tab.id);
        if (focus) tab.focus();
        if (state.fuel !== tab.dataset.fuelTab) {
            state.fuel = tab.dataset.fuelTab;
            state.index = null;
            tooltip.hidden = true;
            render(true);
        }
    };
    tabs.forEach((tab, i) => {
        tab.addEventListener('click', () => selectTab(tab, false));
        tab.addEventListener('keydown', (event) => {
            const move = { ArrowRight: 1, ArrowDown: 1, ArrowLeft: -1, ArrowUp: -1 }[event.key];
            if (!move) return;
            event.preventDefault();
            selectTab(tabs[(i + move + tabs.length) % tabs.length], true);
        });
    });

    ranges.forEach((button) => button.addEventListener('click', () => {
        ranges.forEach((b) => b.setAttribute('aria-pressed', String(b === button)));
        state.weeks = Number(button.dataset.fuelRange);
        state.index = null;
        tooltip.hidden = true;
        render(true);
    }));

    let resizeTimer;
    new ResizeObserver(() => {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(() => render(false), 120);
    }).observe(host);

    return { render };
}

export function initFuelTracker() {
    document.querySelectorAll('[data-fuel-tracker]').forEach((board) => {
        const data = JSON.parse(board.querySelector('[data-fuel-data]').textContent);

        board.querySelectorAll('[data-spark]').forEach((svg) => drawSparkline(svg, data[svg.dataset.spark].points));

        const chart = createChart(board, data);
        let started = false;
        const start = () => {
            if (started) return;
            started = true;
            board.querySelectorAll('[data-count-to]').forEach((node) => countUp(node, Number(node.dataset.countTo)));
            chart.render(true);
        };

        // Animate when the board comes into view, not while it is still off screen.
        if ('IntersectionObserver' in window) {
            const observer = new IntersectionObserver((entries) => {
                if (entries.some((entry) => entry.isIntersecting)) {
                    observer.disconnect();
                    start();
                }
            }, { threshold: 0.2 });
            observer.observe(board);
        } else {
            start();
        }
    });
}
