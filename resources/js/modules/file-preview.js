/** Shows the chosen file names under an <input type="file" data-preview="#target">. */
export function initFilePreview() {
    document.querySelectorAll('input[type="file"][data-preview]').forEach((input) => {
        const output = document.querySelector(input.dataset.preview);
        if (!output) return;

        const fallback = output.textContent;

        input.addEventListener('change', () => {
            const names = [...input.files].map((file) => file.name);
            output.textContent = names.length ? names.join(', ') : fallback;
        });
    });
}
