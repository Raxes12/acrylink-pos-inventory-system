import { registerPage } from '../page-lifecycle.js';

registerPage(import.meta.url, scope => {
    const createModal = document.getElementById('createUserModal');
    const editModal = document.getElementById('editUserModal');
    const editForm = editModal?.querySelector('[data-user-edit-form]');
    const editData = (() => {
        try { return JSON.parse(document.getElementById('userEditData')?.textContent || '{}'); }
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

    const populateEditForm = (user, preserveValues = false) => {
        if (!editForm || !user) return false;
        editForm.action = user.update_url;
        editForm.elements._user_edit_id.value = user.id;
        editModal.querySelector('[data-edit-user-avatar]').textContent = (user.name || 'U').trim().slice(0, 2).toUpperCase();
        if (!preserveValues) {
            editForm.elements.name.value = user.name || '';
            editForm.elements.username.value = user.username || '';
            editForm.elements.email.value = user.email || '';
            editForm.elements.role_id.value = user.role_id || '';
            editForm.elements.status.value = user.status || 'active';
            editForm.querySelector('[name="mfa_enabled"][type="checkbox"]').checked = Boolean(user.mfa_enabled);
            editForm.elements.password.value = '';
            editForm.elements.password_confirmation.value = '';
        }
        return true;
    };

    scope.listen(document, 'click', event => {
        const createTrigger = event.target.closest('[data-open-user-create]');
        if (createTrigger) {
            event.preventDefault();
            open(createModal, createTrigger);
            return;
        }
        const editTrigger = event.target.closest('[data-open-user-edit]');
        if (editTrigger) {
            const user = editData[editTrigger.dataset.userId];
            if (!user) return;
            event.preventDefault();
            if (populateEditForm(user)) open(editModal, editTrigger, '#editUserName');
            return;
        }
        if (event.target.closest('[data-close-user-create]') || event.target === createModal) close(createModal);
        if (event.target.closest('[data-close-user-edit]') || event.target === editModal) close(editModal);
    });

    scope.listen(document, 'keydown', event => {
        if (event.key === 'Escape' && activeModal) close(activeModal);
    });

    scope.listen(document, 'app:before-navigate', () => {
        close(createModal);
        close(editModal);
    });
    if (createModal?.dataset.openOnError === 'true') scope.requestAnimationFrame(() => open(createModal));
    if (editModal?.dataset.openOnError === 'true') {
        const user = editData[editModal.dataset.errorUserId];
        if (populateEditForm(user, true)) scope.requestAnimationFrame(() => open(editModal, null, '#editUserName'));
    }
});
