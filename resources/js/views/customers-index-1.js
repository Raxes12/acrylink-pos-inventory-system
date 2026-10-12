import { registerPage } from '../page-lifecycle.js';

registerPage(import.meta.url, scope => {
    const modal = document.getElementById('createCustomerModal');
    let trigger = null;

    const open = source => {
        if (!modal) return;
        trigger = source || document.activeElement;
        modal.style.display = 'flex';
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('has-open-modal');
        scope.requestAnimationFrame(() => modal.querySelector('[name="customer_name"]')?.focus());
    };

    const close = () => {
        if (!modal) return;
        modal.style.display = 'none';
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('has-open-modal');
        if (trigger?.isConnected) trigger.focus();
        trigger = null;
    };

    scope.listen(document, 'click', event => {
        const opener = event.target.closest('[data-open-customer-create]');
        if (opener) {
            event.preventDefault();
            open(opener);
            return;
        }
        if (event.target.closest('[data-close-customer-create]')) {
            close();
            return;
        }
        if (event.target === modal) close();
    });

    scope.listen(document, 'keydown', event => {
        if (event.key === 'Escape' && modal?.getAttribute('aria-hidden') === 'false') close();
    });

    scope.listen(document, 'app:before-navigate', close);
    if (modal?.dataset.openOnError === 'true') scope.requestAnimationFrame(() => open());
});
