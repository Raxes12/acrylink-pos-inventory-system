/**
 * VMC Auto Parts – Shared Layout JS
 *
 * This is the main JS entry point for the authenticated app shell.
 * It boots: theme toggle, live clock, toast notifications, ripple
 * effects, form-submit loading states, and ESC-to-close modals.
 *
 * Vite will tree-shake, minify, and fingerprint this for production.
 */

import './views/layouts-app-1.js';
import './navigation.js';
import './live-filters.js';
import './incident-alerts.js';

document.addEventListener('click', event => {
    const toggle = event.target.closest('[data-editor-password-toggle]');
    if (!toggle) return;

    const input = document.getElementById(toggle.getAttribute('aria-controls'));
    if (!(input instanceof HTMLInputElement)) return;

    const willShow = input.type === 'password';
    input.type = willShow ? 'text' : 'password';
    toggle.setAttribute('aria-pressed', String(willShow));
    toggle.setAttribute('aria-label', `${willShow ? 'Hide' : 'Show'} ${input.name === 'password_confirmation' ? 'password confirmation' : 'new password'}`);
    input.focus({ preventScroll: true });
});
