/**
 * Any <form data-confirm="Are you sure?"> asks for confirmation in the shared <dialog>
 * (resources/views/components/ui/confirm-dialog.blade.php) before it is submitted.
 */
export function initConfirmDialog() {
    const dialog = document.getElementById('confirm-dialog');
    if (!dialog) return;

    const message = document.getElementById('confirm-message');
    const okButton = document.getElementById('confirm-ok');
    let pendingForm = null;

    document.addEventListener('submit', (event) => {
        const form = event.target;

        if (form.dataset.confirm && !form.dataset.confirmed) {
            event.preventDefault();
            pendingForm = form;
            message.textContent = form.dataset.confirm;
            dialog.showModal();
        }
    });

    okButton.addEventListener('click', () => {
        if (pendingForm) {
            pendingForm.dataset.confirmed = '1';
            pendingForm.requestSubmit();
        }
        dialog.close();
    });

    dialog.addEventListener('close', () => {
        pendingForm = null;
    });

    // Forms restored from the back/forward cache must ask again.
    window.addEventListener('pageshow', () => {
        document.querySelectorAll('form[data-confirmed]').forEach((form) => delete form.dataset.confirmed);
    });
}
