/**
 * Cookie banner. AutoKonek only sets essential cookies (login session, form security token),
 * so the choice recorded here is "acknowledged". The cookie is stored in the browser for
 * 180 days. If optional cookies are ever added, gate them on window.AutoKonekConsent.hasConsent().
 */
const MAX_AGE_DAYS = 180;

function readCookie(name) {
    return document.cookie.split('; ').find((row) => row.startsWith(name + '='))?.split('=')[1] ?? null;
}

function writeCookie(name, value, days) {
    const secure = location.protocol === 'https:' ? '; Secure' : '';
    document.cookie = `${name}=${value}; Max-Age=${days * 86400}; Path=/; SameSite=Lax${secure}`;
}

export function initCookieConsent() {
    const banner = document.getElementById('cookie-banner');
    if (!banner) return;

    const name = banner.dataset.cookie;
    const withdraw = banner.querySelector('[data-cookie-withdraw]');
    const status = banner.querySelector('[data-cookie-status]');

    const show = () => {
        banner.hidden = false;
        withdraw.hidden = readCookie(name) === null;
        status.hidden = readCookie(name) === null;
    };
    const hide = () => { banner.hidden = true; };

    window.AutoKonekConsent = {
        hasConsent: () => readCookie(name) === 'accepted',
        open: show,
    };

    if (readCookie(name) === null) show();

    banner.querySelector('[data-cookie-accept]').addEventListener('click', () => {
        writeCookie(name, 'accepted', MAX_AGE_DAYS);
        hide();
    });

    withdraw.addEventListener('click', () => {
        document.cookie = `${name}=; Max-Age=0; Path=/; SameSite=Lax`;
        hide();
    });

    // Footer link "Cookie settings"
    document.querySelectorAll('[data-cookie-settings]').forEach((link) => {
        link.addEventListener('click', (event) => {
            event.preventDefault();
            show();
        });
    });
}
