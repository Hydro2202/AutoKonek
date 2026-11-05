/**
 * Region > province > city selectors for the owner's listing form.
 *
 * <div data-location-picker data-provinces-url="..." data-cities-url="...">
 *   <select data-loc="region">, <div data-loc-wrap="province"><select data-loc="province">, <select data-loc="city">
 *
 * Regions without provinces (Metro Manila) hide the province field and list their cities directly.
 */
async function fetchJson(url, params) {
    const response = await fetch(`${url}?${new URLSearchParams(params)}`, { headers: { Accept: 'application/json' } });
    if (!response.ok) throw new Error('Request failed');
    return response.json();
}

function fill(select, items, placeholder) {
    select.replaceChildren(new Option(placeholder, ''));
    items.forEach((item) => select.add(new Option(item.name, item.id)));
    select.disabled = items.length === 0;
}

export function initLocationPicker() {
    document.querySelectorAll('[data-location-picker]').forEach((picker) => {
        const region = picker.querySelector('[data-loc="region"]');
        const province = picker.querySelector('[data-loc="province"]');
        const provinceWrap = picker.querySelector('[data-loc-wrap="province"]');
        const city = picker.querySelector('[data-loc="city"]');
        const note = picker.querySelector('[data-loc-note]');
        const { provincesUrl, citiesUrl } = picker.dataset;

        const busy = (isBusy) => {
            picker.setAttribute('aria-busy', String(isBusy));
            [region, province, city].forEach((select) => { if (isBusy) select.disabled = true; });
            if (isBusy) city.options[0].text = 'Loading places...';
        };

        const say = (text) => { if (note) note.textContent = text; };

        const loadCities = async (params) => {
            fill(city, await fetchJson(citiesUrl, params), 'Choose a city or municipality');
        };

        region.addEventListener('change', async () => {
            say('');
            fill(province, [], 'Choose a province');
            fill(city, [], 'Choose a city or municipality');
            if (!region.value) { provinceWrap.hidden = false; region.disabled = false; return; }

            busy(true);
            try {
                const provinces = await fetchJson(provincesUrl, { region: region.value });

                if (provinces.length) {
                    provinceWrap.hidden = false;
                    fill(province, provinces, 'Choose a province');
                } else {
                    // No provinces (Metro Manila): go straight to the cities.
                    provinceWrap.hidden = true;
                    await loadCities({ region: region.value });
                }
            } catch {
                say('Could not load places. Check your connection and try again.');
            } finally {
                region.disabled = false;
                busy(false);
                region.disabled = false;
                if (!provinceWrap.hidden) province.disabled = province.options.length <= 1;
                city.disabled = city.options.length <= 1;
            }
        });

        province.addEventListener('change', async () => {
            say('');
            fill(city, [], 'Choose a city or municipality');
            if (!province.value) return;

            busy(true);
            try {
                await loadCities({ province: province.value });
            } catch {
                say('Could not load places. Check your connection and try again.');
            } finally {
                busy(false);
                region.disabled = false;
                province.disabled = province.options.length <= 1;
                city.disabled = city.options.length <= 1;
            }
        });
    });
}
