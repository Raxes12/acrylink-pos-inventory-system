(() => {
    // Password visibility toggle
    document.querySelectorAll('[data-password-toggle]').forEach((toggle) => {
        const input = toggle.closest('.input-wrap')?.querySelector('input');
        if (!input) return;

        toggle.addEventListener('click', () => {
            const showing = input.type === 'text';

            input.type = showing ? 'password' : 'text';

            toggle.setAttribute('aria-pressed', String(!showing));
            toggle.setAttribute(
                'aria-label',
                showing ? 'Show password' : 'Hide password'
            );

            input.focus();
        });
    });

    const password = document.getElementById('password');
    const capsLockHint = document.querySelector('[data-caps-lock]');
    const syncCapsLock = event => {
        if (!capsLockHint || typeof event.getModifierState !== 'function') return;
        capsLockHint.hidden = !event.getModifierState('CapsLock');
    };
    password?.addEventListener('keydown', syncCapsLock);
    password?.addEventListener('keyup', syncCapsLock);
    password?.addEventListener('blur', () => {
        if (capsLockHint) capsLockHint.hidden = true;
    });

    const loginForm = document.querySelector('[data-login-form]');
    const loginSubmit = document.querySelector('[data-login-submit]');
    const loginSubmitLabel = document.querySelector('[data-login-submit-label]');

    if (!loginForm || !loginSubmit || !loginSubmitLabel) return;

    /*
     * Laravel redirects back to the login page when authentication fails.
     * Blade adds this attribute when $errors contains a validation/auth error.
     */
    const loginFailed = loginForm.dataset.loginFailed === 'true';

    /*
     * If Laravel returned an authentication error, show
     * "Access Denied" instead of "Access verified".
     */
    if (loginFailed) {
        loginSubmit.classList.remove('is-loading');
        loginSubmit.classList.remove('is-success');
        loginSubmit.classList.add('is-error');

        loginSubmit.setAttribute('aria-busy', 'false');
        loginSubmitLabel.textContent = 'Access Denied';

        window.setTimeout(() => {
            loginSubmit.classList.remove('is-error');
            loginSubmitLabel.textContent = 'Sign in';
        }, 1800);
    }

    /*
     * Normal login submission
     */
    loginForm.addEventListener('submit', () => {
        if (loginForm.dataset.submitting === 'true') return;

        loginForm.dataset.submitting = 'true';

        loginSubmit.classList.remove('is-success');
        loginSubmit.classList.remove('is-error');
        loginSubmit.classList.add('is-loading');

        loginSubmit.setAttribute('aria-busy', 'true');

        loginSubmitLabel.textContent = 'Verifying access';
    });
})();
