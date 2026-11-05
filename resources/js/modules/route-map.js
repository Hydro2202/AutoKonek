/**
 * Shows the real driving route from the visitor's GPS position to a listing, on the page.
 *
 *  - The position comes from the browser (it asks for permission first).
 *  - The route and turn-by-turn steps come from the public OSRM routing service.
 *  - The map is made of OpenStreetMap tile pictures with the route drawn over them.
 * Nothing is requested until the visitor clicks a button, so opening a listing contacts no third party.
 *
 * Markup: see the "Getting there" section of resources/views/site/establishments/show.blade.php.
 */
import { locate } from './geolocation';

const ROUTING_URL = 'https://router.project-osrm.org/route/v1/driving';
const TILE_URL = 'https://tile.openstreetmap.org';
const TILE_SIZE = 256;
const SVG_NS = 'http://www.w3.org/2000/svg';

/** Longitude and latitude to pixel position on the whole-world map at a zoom level (Web Mercator). */
function project([lng, lat], zoom) {
    const scale = TILE_SIZE * 2 ** zoom;
    const sin = Math.sin((lat * Math.PI) / 180);
    return [((lng + 180) / 360) * scale, (0.5 - Math.log((1 + sin) / (1 - sin)) / (4 * Math.PI)) * scale];
}

/** The largest zoom level at which all points fit inside the box. */
function fitZoom(points, width, height, padding, maxZoom) {
    for (let zoom = maxZoom; zoom > 3; zoom -= 1) {
        const xs = points.map((point) => project(point, zoom)[0]);
        const ys = points.map((point) => project(point, zoom)[1]);
        const fits = Math.max(...xs) - Math.min(...xs) <= width - padding * 2
            && Math.max(...ys) - Math.min(...ys) <= height - padding * 2;
        if (fits) return zoom;
    }
    return 3;
}

function svgElement(name, attributes) {
    const element = document.createElementNS(SVG_NS, name);
    Object.entries(attributes).forEach(([key, value]) => element.setAttribute(key, value));
    return element;
}

/** Draws tiles, the optional route line, and the start and end markers into the container. */
function drawMap(container, { route = [], start = null, end, maxZoom = 17 }) {
    const width = container.clientWidth;
    const height = container.clientHeight;
    const points = [...route, ...(start ? [start] : []), end];

    const zoom = fitZoom(points, width, height, 48, maxZoom);
    const pixels = points.map((point) => project(point, zoom));
    const centerX = (Math.min(...pixels.map((p) => p[0])) + Math.max(...pixels.map((p) => p[0]))) / 2;
    const centerY = (Math.min(...pixels.map((p) => p[1])) + Math.max(...pixels.map((p) => p[1]))) / 2;
    const left = centerX - width / 2;
    const top = centerY - height / 2;
    const toScreen = (point) => project(point, zoom).map((value, index) => value - (index === 0 ? left : top));

    const layer = document.createElement('div');
    layer.className = 'absolute inset-0 overflow-hidden bg-steel-100';

    const tiles = 2 ** zoom;
    for (let tx = Math.floor(left / TILE_SIZE); tx <= Math.floor((left + width) / TILE_SIZE); tx += 1) {
        for (let ty = Math.floor(top / TILE_SIZE); ty <= Math.floor((top + height) / TILE_SIZE); ty += 1) {
            if (ty < 0 || ty >= tiles) continue;
            const tile = document.createElement('img');
            tile.src = `${TILE_URL}/${zoom}/${((tx % tiles) + tiles) % tiles}/${ty}.png`;
            tile.alt = '';
            tile.draggable = false;
            tile.className = 'absolute max-w-none bg-transparent';
            Object.assign(tile.style, { left: `${tx * TILE_SIZE - left}px`, top: `${ty * TILE_SIZE - top}px`, width: `${TILE_SIZE}px`, height: `${TILE_SIZE}px` });
            layer.appendChild(tile);
        }
    }

    const svg = svgElement('svg', { class: 'absolute inset-0 size-full', viewBox: `0 0 ${width} ${height}`, 'aria-hidden': 'true' });

    if (route.length > 1) {
        const d = route.map((point, index) => `${index ? 'L' : 'M'}${toScreen(point).map((n) => n.toFixed(1)).join(' ')}`).join('');
        const line = { d, fill: 'none', 'stroke-linecap': 'round', 'stroke-linejoin': 'round' };
        svg.appendChild(svgElement('path', { ...line, stroke: '#ffffff', 'stroke-width': 10 }));
        svg.appendChild(svgElement('path', { ...line, stroke: '#142B45', 'stroke-width': 5 }));
    }

    if (start) {
        const [x, y] = toScreen(start);
        svg.appendChild(svgElement('circle', { cx: x, cy: y, r: 8, fill: '#16A34A', stroke: '#ffffff', 'stroke-width': 3 }));
    }

    const [ex, ey] = toScreen(end);
    svg.appendChild(svgElement('path', { d: `M${ex} ${ey}c-14-14-14-30 0-34c14 4 14 20 0 34z`, fill: '#F28C28', stroke: '#ffffff', 'stroke-width': 2.5, transform: 'translate(0 0)' }));
    svg.appendChild(svgElement('circle', { cx: ex, cy: ey - 21, r: 4.5, fill: '#0a1828' }));

    const credit = document.createElement('span');
    credit.className = 'absolute right-1 bottom-1 rounded bg-white/85 px-1.5 py-0.5 text-[10px] text-ink';
    credit.textContent = '© OpenStreetMap contributors';

    container.replaceChildren(layer, svg, credit);
}

const formatDistance = (meters) => (meters < 1000
    ? `${Math.max(10, Math.round(meters / 10) * 10)} m`
    : `${(meters / 1000).toFixed(meters < 10000 ? 1 : 0)} km`);

const formatDuration = (seconds) => {
    const minutes = Math.max(1, Math.round(seconds / 60));
    return minutes < 60 ? `${minutes} min` : `${Math.floor(minutes / 60)} h ${minutes % 60} min`;
};

const capitalize = (text) => text.charAt(0).toUpperCase() + text.slice(1);

/** Turns one OSRM step into a sentence such as "Turn left onto Quezon Avenue". */
function describe(step, destinationName) {
    const { type, modifier, exit } = step.maneuver;
    const onto = step.name ? ` onto ${step.name}` : '';
    const on = step.name ? ` on ${step.name}` : '';
    const turn = modifier === 'uturn' ? 'make a U-turn' : modifier ? `turn ${modifier}` : 'continue';

    switch (type) {
        case 'depart': return `Start${on}`;
        case 'turn': return capitalize(turn) + onto;
        case 'end of road': return `At the end of the road, ${turn}${onto}`;
        case 'fork': return `Keep ${modifier ?? 'on course'}${onto}`;
        case 'merge': return `Merge${modifier ? ` ${modifier}` : ''}${onto}`;
        case 'on ramp':
        case 'off ramp': return `Take the ramp${onto}`;
        case 'roundabout':
        case 'rotary':
        case 'roundabout turn': return `At the roundabout, take exit ${exit ?? ''}${onto}`.replace('exit  ', 'the exit ');
        case 'continue': return `Continue${modifier && modifier !== 'straight' ? ` ${modifier}` : ''}${on}`;
        case 'arrive': return `Arrive at ${destinationName}`;
        default: return `Continue${onto}`;
    }
}

export function initRouteMap() {
    document.querySelectorAll('[data-route-panel]').forEach((panel) => {
        const end = [parseFloat(panel.dataset.lng), parseFloat(panel.dataset.lat)];
        const name = panel.dataset.name;
        const stage = panel.querySelector('[data-route-stage]');
        const summary = panel.querySelector('[data-route-summary]');
        const steps = panel.querySelector('[data-route-steps]');
        const message = panel.querySelector('[data-route-message]');
        const googleLink = panel.querySelector('[data-google-link]');

        let redraw = null;
        let running = false;

        const say = (text) => { message.textContent = text; };

        const showSummary = (text) => {
            summary.hidden = false;
            summary.replaceChildren(document.createTextNode(text));
        };

        const showMapOnly = () => {
            say('');
            stage.classList.remove('flex');
            redraw = () => drawMap(stage, { end, maxZoom: 16 });
            redraw();
        };

        const startRoute = async () => {
            if (running) return;
            running = true;
            say('');
            panel.scrollIntoView({ behavior: 'smooth', block: 'center' });

            try {
                showSummary('Finding your position...');
                const { coords } = await locate();
                const start = [coords.longitude, coords.latitude];

                showSummary('Calculating the route...');
                const response = await fetch(`${ROUTING_URL}/${start.join(',')};${end.join(',')}?overview=full&geometries=geojson&steps=true`);
                const data = await response.json();
                if (!response.ok || data.code !== 'Ok') throw new Error('No driving route was found between you and this place.');

                const route = data.routes[0];
                stage.classList.remove('flex');
                redraw = () => drawMap(stage, { route: route.geometry.coordinates, start, end });
                redraw();

                showSummary(`${formatDistance(route.distance)} by road, about ${formatDuration(route.duration)} without traffic.`);

                steps.hidden = false;
                steps.replaceChildren(...route.legs[0].steps.map((step) => {
                    const item = document.createElement('li');
                    item.className = 'flex items-baseline justify-between gap-4';
                    const text = document.createElement('span');
                    text.textContent = describe(step, name);
                    const distance = document.createElement('span');
                    distance.className = 'shrink-0 text-muted';
                    distance.textContent = step.maneuver.type === 'arrive' ? '' : formatDistance(step.distance);
                    item.append(text, distance);
                    return item;
                }));

                // Google Maps now opens with this exact start point, so it shows the same route.
                if (googleLink) {
                    const url = new URL(googleLink.href);
                    url.searchParams.set('origin', `${coords.latitude},${coords.longitude}`);
                    googleLink.href = url.href;
                }
            } catch (error) {
                summary.hidden = true;
                say(`${error.message} You can still open the route in Google Maps below.`);
            } finally {
                running = false;
            }
        };

        panel.querySelector('[data-route-map-only]')?.addEventListener('click', showMapOnly);
        document.querySelectorAll('[data-route-start]').forEach((button) => button.addEventListener('click', startRoute));

        // Keep the drawing sharp when the window changes size.
        let resizeTimer;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(() => redraw?.(), 200);
        });

        // Arriving from a listing card's "Directions" button starts the route straight away.
        if (panel.dataset.auto === '1') startRoute();
    });
}
