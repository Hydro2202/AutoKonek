/**
 * Click-to-load map. The OpenStreetMap iframe is only added after the visitor asks for it,
 * so opening a listing does not contact any third party.
 *
 * <button data-map-load data-src="https://www.openstreetmap.org/export/embed.html?..." data-title="Map of ...">
 * inside a container with [data-map-frame].
 */
export function initMapEmbed() {
    document.querySelectorAll('[data-map-load]').forEach((button) => {
        button.addEventListener('click', () => {
            const frame = button.closest('[data-map-frame]');

            const iframe = document.createElement('iframe');
            iframe.src = button.dataset.src;
            iframe.title = button.dataset.title;
            iframe.loading = 'lazy';
            iframe.referrerPolicy = 'no-referrer';
            iframe.className = 'size-full border-0';

            frame.replaceChildren(iframe);
        });
    });
}
