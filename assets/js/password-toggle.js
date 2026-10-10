function resetPasswordVisibility(input) {
    if (!input) return;
    input.type = 'password';
    const button = document.querySelector(`[data-password-toggle][aria-controls="${input.id}"]`);
    if (button) {
        button.textContent = 'Show';
        button.setAttribute('aria-label', 'Show password');
    }
}

document.addEventListener('click', event => {
    const button = event.target.closest('[data-password-toggle]');
    if (!button) return;

    const input = document.getElementById(button.getAttribute('aria-controls'));
    if (!input) return;

    const isVisible = input.type === 'text';
    input.type = isVisible ? 'password' : 'text';
    button.textContent = isVisible ? 'Show' : 'Hide';
    button.setAttribute('aria-label', `${isVisible ? 'Show' : 'Hide'} password`);
});
