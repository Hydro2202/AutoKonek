/**
 * Uses the visitor's GPS (browser geolocation). Nothing is requested until a button is clicked,
 * and the browser asks for permission first.
 *
 *  - [data-near-me]        in the directory filter form: sort by distance from the visitor.
 *  - [data-use-location]   in the owner's listing form: fill in the shop's latitude and longitude.
 * locate() is also used by the route map on listing pages (route-map.js).
 */
const OPTIONS = { enableHighAccuracy: true, timeout: 12000, maximumAge: 60000 };

const ERRORS = {
    1: 'Location permission was blocked. Allow location for this site in your browser settings, then try again.',
    2: 'Your device could not work out its position. Check that location is turned on.',
    3: 'Finding your position took too long. Please try again.',
};

export function locate() {
    return new Promise((resolve, reject) => {
        if (!('geolocation' in navigator)) {
            reject(new Error('This browser cannot share its location.'));
            return;
        }
        navigator.geolocation.getCurrentPosition(resolve, (error) => reject(new Error(ERRORS[error.code] ?? 'Could not get your location.')), OPTIONS);
    });
}

/** Disable a button and show progress text while we wait for the GPS. */
async function withBusyButton(button, busyText, task) {
    const original = button.innerHTML;
    button.disabled = true;
    button.textContent = busyText;
    try {
        return await task();
    } finally {
        button.disabled = false;
        button.innerHTML = original;
    }
}

export function initGeolocation() {
    // Directory: "Near me"
    document.querySelectorAll('[data-near-me]').forEach((button) => {
        const form = button.closest('form');
        const message = form.querySelector('[data-geo-message]');

        button.addEventListener('click', async () => {
            message.textContent = '';
            try {
                const { coords } = await withBusyButton(button, 'Finding you...', locate);
                // These fields start disabled so an unused filter adds nothing to the URL.
                Object.assign(form.elements.lat, { value: coords.latitude.toFixed(6), disabled: false });
                Object.assign(form.elements.lng, { value: coords.longitude.toFixed(6), disabled: false });
                const sort = form.elements.sort;
                if (![...sort.options].some((option) => option.value === 'nearest')) sort.add(new Option('Nearest to me', 'nearest'), 1);
                sort.value = 'nearest';
                form.requestSubmit();
            } catch (error) {
                message.textContent = error.message;
            }
        });
    });

    // Owner form: "Use my current location"
    document.querySelectorAll('[data-use-location]').forEach((button) => {
        const form = button.closest('form');
        const message = form.querySelector('[data-geo-message]');

        button.addEventListener('click', async () => {
            message.textContent = '';
            try {
                const { coords } = await withBusyButton(button, 'Finding you...', locate);
                form.elements.latitude.value = coords.latitude.toFixed(6);
                form.elements.longitude.value = coords.longitude.toFixed(6);
                message.textContent = `Position saved in the form (accurate to about ${Math.round(coords.accuracy)} m). Stand at the business entrance for the best result.`;
            } catch (error) {
                message.textContent = error.message;
            }
        });
    });
}
