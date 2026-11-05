/** Toast messages: dismiss on click, and success toasts leave on their own. */
export function initFlash() {
    const remove = (toast) => {
        toast.classList.add('toast-leave');
        setTimeout(() => toast.remove(), 220);
    };

    document.querySelectorAll('[data-toast]').forEach((toast) => {
        toast.querySelector('[data-toast-close]')?.addEventListener('click', () => remove(toast));

        if (toast.dataset.toast === 'success') {
            setTimeout(() => remove(toast), 6000);
        }
    });
}
