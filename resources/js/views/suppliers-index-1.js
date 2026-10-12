import { registerPage } from '../page-lifecycle.js';

registerPage(import.meta.url, scope => {
    const modal = document.getElementById('createSupplierModal');
    let opener = null;
    const open = source => {
        if (!modal) return;
        opener = source || document.activeElement;
        modal.style.display = 'flex';
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('has-open-modal');
        scope.requestAnimationFrame(() => modal.querySelector('[name="name"]')?.focus());
    };
    const close = () => {
        if (!modal) return;
        modal.style.display = 'none';
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('has-open-modal');
        if (opener?.isConnected) opener.focus();
        opener = null;
    };
    scope.listen(document, 'click', event => {
        const trigger = event.target.closest('[data-open-supplier-create]');
        if (trigger) { event.preventDefault(); open(trigger); return; }
        if (event.target.closest('[data-close-supplier-create]') || event.target === modal) close();
    });
    scope.listen(document, 'keydown', event => {
        if (event.key === 'Escape' && modal?.getAttribute('aria-hidden') === 'false') close();
    });
    scope.listen(document, 'app:before-navigate', close);
    if (modal?.dataset.openOnError === 'true') scope.requestAnimationFrame(() => open());
});
