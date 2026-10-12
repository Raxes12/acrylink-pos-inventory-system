import { registerPage } from '../page-lifecycle.js';

registerPage(import.meta.url, scope => {
let invSort = { key: null, dir: 'asc' };

function sortInventory(key) {
    const invTbody = document.getElementById('inventoryTable')?.querySelector('tbody');
    if (!invTbody) return;
    invSort.dir = invSort.key === key && invSort.dir === 'asc' ? 'desc' : 'asc';
    invSort.key = key;

    const numeric = ['price', 'cost', 'stock', 'reorder', 'critical'].includes(key);
    const rows = Array.from(invTbody.querySelectorAll('tr[data-live-row]'));
    rows.sort((a, b) => {
        const av = a.dataset[key] || '';
        const bv = b.dataset[key] || '';
        const result = numeric
            ? (parseFloat(av) || 0) - (parseFloat(bv) || 0)
            : av.localeCompare(bv, undefined, { sensitivity: 'base' });
        return invSort.dir === 'asc' ? result : -result;
    });
    rows.forEach(row => invTbody.appendChild(row));
    document.querySelectorAll('.sort-btn').forEach(btn => {
        btn.classList.toggle('is-asc', btn.dataset.sort === key && invSort.dir === 'asc');
        btn.classList.toggle('is-desc', btn.dataset.sort === key && invSort.dir === 'desc');
    });
    window.vmcToast?.(`Sorted by ${key}`, 'info');
}

scope.listen(document, 'click', event => {
    const sortButton = event.target.closest('.sort-btn');
    if (sortButton) sortInventory(sortButton.dataset.sort);
});

const adjustModal = document.getElementById('stockAdjustModal');
const adjustForm = document.getElementById('stockAdjustForm');
const adjustProduct = document.getElementById('stockAdjustProduct');
const adjustCurrent = document.getElementById('stockAdjustCurrent');
const adjustPreview = document.getElementById('stockAdjustPreview');
let adjustCurrentQuantity = 0;
let adjustModalTrigger = null;

function updateAdjustmentPreview() {
    if (!adjustForm || !adjustPreview) return;
    const submit = adjustForm.querySelector('button[type="submit"]');
    const type = adjustForm.elements.type?.value;
    const quantity = Number.parseInt(adjustForm.elements.quantity?.value, 10);
    if (!Number.isFinite(quantity) || quantity < 1) {
        adjustPreview.textContent = 'Enter a quantity to preview the resulting stock.';
        adjustPreview.classList.remove('is-invalid');
        if (submit) submit.disabled = true;
        return;
    }

    const change = type === 'stock_out' ? -quantity : quantity;
    const resulting = adjustCurrentQuantity + change;
    adjustPreview.textContent = resulting < 0
        ? `Cannot remove ${quantity} units: only ${adjustCurrentQuantity} units are currently available.`
        : `${adjustCurrentQuantity} ${change >= 0 ? '+' : '-'} ${Math.abs(change)} = ${resulting} units after this change`;
    adjustPreview.classList.toggle('is-invalid', resulting < 0);
    if (submit) submit.disabled = resulting < 0;
}

function closeAdjustModal() {
    if (!adjustModal) return;
    adjustModal.hidden = true;
    document.body.classList.remove('inventory-modal-open');
    adjustModalTrigger?.focus?.();
    adjustModalTrigger = null;
}

scope.listen(document, 'click', event => {
    const btn = event.target.closest('.stock-adjust-btn');
    if (btn) {
        if (!adjustModal || !adjustForm) return;
        adjustModalTrigger = btn;
        adjustCurrentQuantity = Number.parseInt(btn.dataset.current, 10) || 0;
        adjustForm.action = btn.dataset.action || '';
        if (adjustProduct) adjustProduct.textContent = btn.dataset.product || '';
        if (adjustCurrent) adjustCurrent.textContent = `Current stock: ${adjustCurrentQuantity} units`;
        adjustForm.reset();
        updateAdjustmentPreview();
        adjustModal.hidden = false;
        document.body.classList.add('inventory-modal-open');
        adjustForm.querySelector('[name=type]')?.focus();
    }
});

adjustForm?.querySelector('[name=type]')?.addEventListener('change', updateAdjustmentPreview);
adjustForm?.querySelector('[name=quantity]')?.addEventListener('input', updateAdjustmentPreview);

document.querySelectorAll('[data-close-adjust]').forEach(el => el.addEventListener('click', closeAdjustModal));
scope.listen(document, 'keydown', event => {
    if (event.key === 'Escape' && adjustModal?.hidden === false) closeAdjustModal();
});

});
