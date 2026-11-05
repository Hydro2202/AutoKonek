/**
 * - Submit buttons show a road-line and ignore a second click while the form is sending.
 * - <form data-autosubmit> submits when a field changes (used by directory filters).
 *   A field with data-resets="province,city" first clears those fields, so choosing a new
 *   region does not keep a province that belongs to the old one.
 */
export function initFormFeedback() {
    document.addEventListener('submit', (event) => {
        const form = event.target;
        if (event.defaultPrevented || form.dataset.noLoading !== undefined) return;

        const button = form.querySelector('button[type="submit"]:not([data-no-loading])');
        if (!button) return;

        // Wait a frame so the browser still sends the button's own value.
        requestAnimationFrame(() => {
            button.disabled = true;
            button.classList.add('is-loading');
        });
    });

    // Undo the loading state if the page comes back from the back/forward cache.
    window.addEventListener('pageshow', () => {
        document.querySelectorAll('button.is-loading').forEach((button) => {
            button.disabled = false;
            button.classList.remove('is-loading');
        });
    });

    document.querySelectorAll('form[data-autosubmit]').forEach((form) => {
        form.addEventListener('change', (event) => {
            event.target.dataset.resets?.split(',').forEach((name) => {
                if (form.elements[name]) form.elements[name].value = '';
            });
            form.requestSubmit();
        });
    });
}
