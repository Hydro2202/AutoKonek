/** <button data-toggle="#id"> shows or hides another element (mobile menus, sidebars). */
export function initToggles() {
    document.querySelectorAll('[data-toggle]').forEach((button) => {
        const target = document.querySelector(button.dataset.toggle);
        if (!target) return;

        button.addEventListener('click', () => {
            const isOpen = target.classList.toggle('hidden') === false;
            button.setAttribute('aria-expanded', String(isOpen));
        });
    });
}
