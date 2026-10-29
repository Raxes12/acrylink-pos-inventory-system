import { registerPage } from '../page-lifecycle.js';

registerPage(import.meta.url, scope => {
    const createModal = document.getElementById('createSupplierModal');
    const editModal = document.getElementById('editSupplierModal');
    const editForm = editModal?.querySelector('[data-supplier-edit-form]');
    const editData = (() => {
        try { return JSON.parse(document.getElementById('supplierEditData')?.textContent || '{}'); }
        catch { return {}; }
    })();
    let opener = null;
    let activeModal = null;
    const open = (modal, source, focusSelector = '[name="name"]') => {
        if (!modal) return;
        opener = source || document.activeElement;
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
        if (opener?.isConnected) opener.focus();
        opener = null;
    };
    const populateEdit = (supplier, preserveValues = false) => {
        if (!editForm || !supplier) return false;
        editForm.action = supplier.update_url;
        editForm.elements._supplier_edit_id.value = supplier.id;
        editModal.querySelector('[data-edit-supplier-avatar]').textContent = (supplier.name || 'S').trim().slice(0, 2).toUpperCase();
        if (!preserveValues) {
            ['name', 'contact', 'phone', 'email', 'address', 'status', 'payment_method'].forEach(name => {
                editForm.elements[name].value = supplier[name] ?? '';
            });
            editForm.elements.payment_method.dispatchEvent(new Event('change', { bubbles: true }));
        }
        return true;
    };
    scope.listen(document, 'click', event => {
        const trigger = event.target.closest('[data-open-supplier-create]');
        if (trigger) { event.preventDefault(); open(createModal, trigger); return; }
        const editTrigger = event.target.closest('[data-open-supplier-edit]');
        if (editTrigger) {
            const supplier = editData[editTrigger.dataset.supplierId];
            if (!supplier) return;
            event.preventDefault();
            if (populateEdit(supplier)) open(editModal, editTrigger);
            return;
        }
        if (event.target.closest('[data-close-supplier-create]') || event.target === createModal) close(createModal);
        if (event.target.closest('[data-close-supplier-edit]') || event.target === editModal) close(editModal);
    });
    scope.listen(document, 'keydown', event => {
        if (event.key === 'Escape' && activeModal) close(activeModal);
    });
    scope.listen(document, 'app:before-navigate', () => { close(createModal); close(editModal); });
    if (createModal?.dataset.openOnError === 'true') scope.requestAnimationFrame(() => open(createModal));
    if (editModal?.dataset.openOnError === 'true') {
        const supplier = editData[editModal.dataset.errorSupplierId];
        if (populateEdit(supplier, true)) scope.requestAnimationFrame(() => open(editModal));
    }
});
