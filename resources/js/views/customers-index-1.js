import { registerPage } from '../page-lifecycle.js';

registerPage(import.meta.url, scope => {
    const createModal = document.getElementById('createCustomerModal');
    const editModal = document.getElementById('editCustomerModal');
    const editForm = editModal?.querySelector('[data-customer-edit-form]');
    const editData = (() => {
        try { return JSON.parse(document.getElementById('customerEditData')?.textContent || '{}'); }
        catch { return {}; }
    })();
    let trigger = null;
    let activeModal = null;

    const open = (modal, source, focusSelector = '[name="customer_name"]') => {
        if (!modal) return;
        trigger = source || document.activeElement;
        activeModal = modal;
        modal.style.display = 'flex';
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('has-open-modal');
        scope.requestAnimationFrame(() => modal.querySelector(focusSelector)?.focus());
    };

    const close = modal => {
        if (!modal) return;
        modal.style.display = 'none';
        modal.setAttribute('aria-hidden', 'true');
        if (activeModal === modal) activeModal = null;
        if (!activeModal) document.body.classList.remove('has-open-modal');
        if (trigger?.isConnected) trigger.focus();
        trigger = null;
    };

    const populateEdit = (customer, preserveValues = false) => {
        if (!editForm || !customer) return false;
        editForm.action = customer.update_url;
        editForm.elements._customer_edit_id.value = customer.id;
        editModal.querySelector('[data-edit-customer-avatar]').textContent = (customer.name || 'C').trim().slice(0, 2).toUpperCase();
        editModal.querySelector('[data-edit-customer-code]').textContent = `${customer.customer_code} · Update contact details and credit settings.`;
        if (!preserveValues) {
            ['customer_name', 'phone', 'email', 'address', 'status', 'credit_limit', 'default_credit_term_days'].forEach(name => {
                editForm.elements[name].value = customer[name] ?? '';
            });
            editForm.elements.credit_enabled.value = customer.credit_enabled ? '1' : '0';
        }
        return true;
    };

    scope.listen(document, 'click', event => {
        const opener = event.target.closest('[data-open-customer-create]');
        if (opener) {
            event.preventDefault();
            open(createModal, opener);
            return;
        }
        const editOpener = event.target.closest('[data-open-customer-edit]');
        if (editOpener) {
            const customer = editData[editOpener.dataset.customerId];
            if (!customer) return;
            event.preventDefault();
            if (populateEdit(customer)) open(editModal, editOpener, '#edit_customer_name');
            return;
        }
        if (event.target.closest('[data-close-customer-create]')) {
            close(createModal);
            return;
        }
        if (event.target.closest('[data-close-customer-edit]')) { close(editModal); return; }
        if (event.target === createModal) close(createModal);
        if (event.target === editModal) close(editModal);
    });

    scope.listen(document, 'keydown', event => {
        if (event.key === 'Escape' && activeModal) close(activeModal);
    });

    scope.listen(document, 'app:before-navigate', () => { close(createModal); close(editModal); });
    if (createModal?.dataset.openOnError === 'true') scope.requestAnimationFrame(() => open(createModal));
    if (editModal?.dataset.openOnError === 'true') {
        const customer = editData[editModal.dataset.errorCustomerId];
        if (populateEdit(customer, true)) scope.requestAnimationFrame(() => open(editModal, null, '#edit_customer_name'));
    }
});
