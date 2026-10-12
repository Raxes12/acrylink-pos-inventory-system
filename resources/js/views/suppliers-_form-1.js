import { registerPage } from '../page-lifecycle.js';

registerPage(import.meta.url, scope => {
function toggleSupplierCheckFields() {
    document.querySelectorAll('.supplier-payment-method').forEach(select => {
        const note = select.closest('.supplier-form')?.querySelector('.supplier-check-note');
        if (note) note.style.display = select.value === 'check' ? '' : 'none';
    });
}

scope.expose({ toggleSupplierCheckFields });

// Initialize the conditional note on create and edit pages.
document.querySelectorAll('.supplier-payment-method').forEach(sel => {
    toggleSupplierCheckFields();
    sel.addEventListener('change', toggleSupplierCheckFields);
});

});
