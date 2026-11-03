// Small, dependency-free helpers: confirm dialogs, submit feedback, flash dismissal, mobile menus.

document.addEventListener('DOMContentLoaded', () => {
    const dialog = document.getElementById('confirm-dialog');
    const message = document.getElementById('confirm-message');
    const okButton = document.getElementById('confirm-ok');
    let pendingForm = null;

    // Forms with data-confirm="..." ask for confirmation in an accessible <dialog>.
    document.addEventListener('submit', (event) => {
        const form = event.target;

        if (form.dataset.confirm && dialog && !form.dataset.confirmed) {
            event.preventDefault();
            pendingForm = form;
            message.textContent = form.dataset.confirm;
            dialog.showModal();
            return;
        }

        // Loading feedback: disable the submit button so forms are not sent twice.
        const button = form.querySelector('button[type="submit"]:not([data-no-loading])');
        if (button && !form.dataset.noLoading) {
            requestAnimationFrame(() => {
                button.disabled = true;
                button.dataset.label = button.textContent;
                button.textContent = 'Please wait...';
            });
        }
    });

    okButton?.addEventListener('click', () => {
        if (pendingForm) {
            pendingForm.dataset.confirmed = '1';
            pendingForm.requestSubmit();
        }
        dialog.close();
    });

    dialog?.addEventListener('close', () => { pendingForm = null; });

    // Restore buttons when the page is shown again from the back/forward cache.
    window.addEventListener('pageshow', () => {
        document.querySelectorAll('button[data-label]').forEach((b) => {
            b.disabled = false;
            b.textContent = b.dataset.label;
        });
        document.querySelectorAll('form[data-confirmed]').forEach((f) => delete f.dataset.confirmed);
    });

    // Flash messages: close button + auto hide for success.
    document.querySelectorAll('[data-flash]').forEach((el) => {
        el.querySelector('[data-flash-close]')?.addEventListener('click', () => el.remove());
        if (el.dataset.flash === 'success') {
            setTimeout(() => el.remove(), 6000);
        }
    });

    // Generic toggles (mobile navigation, sidebar): <button data-toggle="#id">
    document.querySelectorAll('[data-toggle]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const target = document.querySelector(btn.dataset.toggle);
            if (!target) return;
            const open = target.classList.toggle('hidden') === false;
            btn.setAttribute('aria-expanded', String(open));
        });
    });

    // Image upload preview names (owner forms).
    document.querySelectorAll('input[type="file"][data-preview]').forEach((input) => {
        const out = document.querySelector(input.dataset.preview);
        input.addEventListener('change', () => {
            if (!out) return;
            out.textContent = [...input.files].map((f) => f.name).join(', ') || 'No file chosen';
        });
    });
});
