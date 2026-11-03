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
const confirmModal = document.getElementById('stockConfirmModal');
const adjustProduct = document.getElementById('stockAdjustProduct');
const adjustVariant = document.getElementById('stockAdjustVariant');
const adjustCurrent = document.getElementById('stockAdjustCurrent');
const adjustPreview = document.getElementById('stockAdjustPreview');
const adjustErrors = document.getElementById('stockAdjustErrors');
const reviewButton = adjustForm?.querySelector('[data-review-adjustment]');
const confirmButton = confirmModal?.querySelector('[data-confirm-adjustment]');
const reasonsByType = JSON.parse(adjustModal?.dataset.reasons || '{}');
const typeLabels = JSON.parse(adjustModal?.dataset.typeLabels || '{}');
const oldAdjustment = JSON.parse(adjustModal?.dataset.oldAdjustment || '{}');
const serverErrors = JSON.parse(adjustModal?.dataset.errors || '[]');
const MAX_QUANTITY = 2147483647;
let adjustment = {};
let adjustModalTrigger = null;
let adjustmentConfirmed = false;

function adjustmentToken() {
    if (window.crypto?.randomUUID) return window.crypto.randomUUID();
    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, character => {
        const random = Math.random() * 16 | 0;
        return (character === 'x' ? random : (random & 0x3 | 0x8)).toString(16);
    });
}

function formatStock(quantity) {
    return `${quantity} ${String(adjustment.unit || 'pcs').toUpperCase()}`;
}

function stockStatus(quantity) {
    if (quantity <= 0) return 'Out of Stock';
    if (quantity <= adjustment.critical) return 'Critical';
    if (quantity <= adjustment.minimum) return 'Low Stock';
    return 'Normal';
}

function setText(selector, value, root = document) {
    const element = root.querySelector(selector);
    if (element) element.textContent = value;
}

function populateReasons(selected = '') {
    const reasonSelect = adjustForm?.elements.reason;
    if (!reasonSelect) return;
    const reasons = reasonsByType[adjustForm.elements.type?.value] || {};
    reasonSelect.replaceChildren(new Option('— Select a reason —', ''));
    Object.entries(reasons).forEach(([value, label]) => {
        reasonSelect.add(new Option(label, value, false, value === selected));
    });
}

function updateRemarksRequirement() {
    const remarks = adjustForm?.elements.remarks;
    if (!remarks) return;
    const required = ['other', 'lost_missing'].includes(adjustForm.elements.reason?.value);
    remarks.required = required;
    setText('[data-remarks-requirement]', required ? '(Required)' : '(Optional)', adjustForm);
}

function showAdjustmentErrors(errors = []) {
    if (!adjustErrors) return;
    adjustErrors.replaceChildren();
    if (!errors.length) {
        adjustErrors.hidden = true;
        return;
    }
    const title = document.createElement('strong');
    title.textContent = 'Please review the following:';
    const list = document.createElement('ul');
    errors.forEach(message => {
        const item = document.createElement('li');
        item.textContent = message;
        list.appendChild(item);
    });
    adjustErrors.append(title, list);
    adjustErrors.hidden = false;
}

function updateAdjustmentPreview() {
    if (!adjustForm || !adjustPreview) return;
    const type = adjustForm.elements.type?.value;
    const quantityInput = adjustForm.elements.quantity;
    const rawQuantity = String(quantityInput?.value || '').trim();
    const quantity = Number(rawQuantity);
    const validQuantity = /^\d+$/.test(rawQuantity) && Number.isSafeInteger(quantity) && quantity >= 1 && quantity <= MAX_QUANTITY;
    const change = type === 'stock_out' ? -quantity : quantity;
    const resulting = validQuantity ? adjustment.current + change : adjustment.current;
    const insufficient = validQuantity && resulting < 0;
    const quantityError = document.getElementById('stockAdjustQuantityError');
    let quantityMessage = '';

    if (rawQuantity !== '' && !validQuantity) {
        quantityMessage = 'Enter a whole number between 1 and 2,147,483,647.';
    } else if (insufficient) {
        quantityMessage = 'Insufficient stock. The adjustment quantity exceeds the available inventory.';
    }

    quantityInput?.setCustomValidity(quantityMessage);
    if (quantityError) {
        quantityError.textContent = quantityMessage;
        quantityError.hidden = !quantityMessage;
    }

    setText('[data-preview-current]', formatStock(adjustment.current), adjustPreview);
    setText('[data-preview-change]', validQuantity ? `${change > 0 ? '+' : ''}${change} ${String(adjustment.unit).toUpperCase()}` : 'Enter a quantity', adjustPreview);
    setText('[data-preview-result]', validQuantity && !insufficient ? formatStock(resulting) : '—', adjustPreview);

    const status = validQuantity && !insufficient ? stockStatus(resulting) : 'Waiting for valid quantity';
    const statusElement = adjustPreview.querySelector('[data-preview-status]');
    if (statusElement) {
        statusElement.textContent = status;
        statusElement.dataset.status = status.toLowerCase().replaceAll(' ', '-');
    }

    const warning = adjustPreview.querySelector('[data-preview-warning]');
    if (warning) {
        warning.hidden = true;
        warning.textContent = '';
        if (insufficient) {
            warning.textContent = quantityMessage;
            warning.hidden = false;
        } else if (validQuantity && resulting <= adjustment.critical) {
            warning.textContent = resulting <= 0
                ? 'This adjustment will leave the variant out of stock.'
                : 'Warning: resulting stock will be at the critical level.';
            warning.hidden = false;
        } else if (validQuantity && resulting <= adjustment.minimum) {
            warning.textContent = 'Resulting stock will be below the minimum stock level.';
            warning.hidden = false;
        }
    }

    adjustPreview.classList.toggle('is-invalid', Boolean(quantityMessage));
    adjustPreview.classList.toggle('is-warning', validQuantity && !insufficient && resulting <= adjustment.minimum);
    adjustment.resulting = resulting;
    adjustment.change = change;
    adjustment.valid = validQuantity && !insufficient;

    updateRemarksRequirement();
    const validReason = Boolean(adjustForm.elements.reason?.value);
    const validRemarks = !adjustForm.elements.remarks?.required || Boolean(adjustForm.elements.remarks.value.trim());
    if (reviewButton) reviewButton.disabled = !(adjustment.valid && validReason && validRemarks);
}

function closeAdjustModal() {
    if (!adjustModal) return;
    adjustModal.hidden = true;
    confirmModal && (confirmModal.hidden = true);
    document.body.classList.remove('inventory-modal-open');
    adjustModalTrigger?.focus?.();
    adjustModalTrigger = null;
    adjustmentConfirmed = false;
}

function openAdjustModal(button, previous = null) {
    if (!adjustModal || !adjustForm) return;
    adjustModalTrigger = button;
    adjustment = {
        current: Number.parseInt(button.dataset.current, 10) || 0,
        minimum: Number.parseInt(button.dataset.minimum, 10) || 0,
        critical: Number.parseInt(button.dataset.critical, 10) || 0,
        unit: button.dataset.unit || 'pcs',
        product: button.dataset.product || 'Deleted product',
        variant: button.dataset.variant || 'Unknown variant',
        partNumber: button.dataset.partNumber || 'Not specified',
    };

    adjustForm.reset();
    adjustForm.action = button.dataset.action || '';
    adjustForm.elements.variant_id.value = button.dataset.variantId || '';
    adjustForm.elements.adjustment_token.value = previous?.adjustment_token || adjustmentToken();
    if (previous?.type) adjustForm.elements.type.value = previous.type;
    populateReasons(previous?.reason || '');
    adjustForm.elements.quantity.value = previous?.quantity || '';
    adjustForm.elements.remarks.value = previous?.remarks || '';
    adjustProduct.textContent = adjustment.product;
    adjustVariant.textContent = `${adjustment.variant} / ${adjustment.partNumber}`;
    adjustCurrent.textContent = formatStock(adjustment.current);
    showAdjustmentErrors(previous ? serverErrors : []);
    updateAdjustmentPreview();
    adjustModal.hidden = false;
    document.body.classList.add('inventory-modal-open');
    adjustForm.querySelector('[name=type]')?.focus();
}

function openConfirmation() {
    if (!adjustForm?.reportValidity() || !adjustment.valid || !confirmModal) return;
    const reasonSelect = adjustForm.elements.reason;
    setText('[data-confirm-product]', adjustment.product, confirmModal);
    setText('[data-confirm-variant]', `${adjustment.variant} / ${adjustment.partNumber}`, confirmModal);
    setText('[data-confirm-type]', typeLabels[adjustForm.elements.type.value] || adjustForm.elements.type.value, confirmModal);
    setText('[data-confirm-reason]', reasonSelect.options[reasonSelect.selectedIndex]?.text || '—', confirmModal);
    setText('[data-confirm-quantity]', `${adjustment.change > 0 ? '+' : ''}${adjustment.change} ${String(adjustment.unit).toUpperCase()}`, confirmModal);
    setText('[data-confirm-current]', formatStock(adjustment.current), confirmModal);
    setText('[data-confirm-result]', formatStock(adjustment.resulting), confirmModal);
    adjustModal.hidden = true;
    confirmModal.hidden = false;
    confirmButton?.focus();
}

function closeConfirmation() {
    if (!confirmModal || !adjustModal) return;
    confirmModal.hidden = true;
    adjustModal.hidden = false;
    reviewButton?.focus();
}

function trapFocus(event, modal) {
    const focusable = Array.from(modal.querySelectorAll('button:not([disabled]), select:not([disabled]), input:not([disabled]), textarea:not([disabled]), [href]'));
    if (!focusable.length) return;
    const first = focusable[0];
    const last = focusable[focusable.length - 1];
    if (event.shiftKey && document.activeElement === first) {
        event.preventDefault();
        last.focus();
    } else if (!event.shiftKey && document.activeElement === last) {
        event.preventDefault();
        first.focus();
    }
}

scope.listen(document, 'click', event => {
    const btn = event.target.closest('.stock-adjust-btn');
    if (btn) openAdjustModal(btn);
});

scope.listen(adjustForm?.querySelector('[name=type]'), 'change', () => {
    populateReasons();
    updateAdjustmentPreview();
});
scope.listen(adjustForm?.querySelector('[name=reason]'), 'change', updateAdjustmentPreview);
scope.listen(adjustForm?.querySelector('[name=quantity]'), 'input', updateAdjustmentPreview);
scope.listen(adjustForm?.querySelector('[name=remarks]'), 'input', updateAdjustmentPreview);
scope.listen(reviewButton, 'click', openConfirmation);
scope.listen(adjustForm, 'submit', event => {
    if (!adjustmentConfirmed) {
        event.preventDefault();
        openConfirmation();
        return;
    }
    reviewButton && (reviewButton.disabled = true);
    if (confirmButton) {
        confirmButton.disabled = true;
        setText('[data-confirm-label]', 'Applying…', confirmButton);
    }
});
scope.listen(confirmButton, 'click', () => {
    adjustmentConfirmed = true;
    adjustForm?.requestSubmit();
});

document.querySelectorAll('[data-close-adjust]').forEach(el => el.addEventListener('click', closeAdjustModal));
document.querySelectorAll('[data-close-confirm]').forEach(el => el.addEventListener('click', closeConfirmation));
scope.listen(document, 'keydown', event => {
    if (event.key === 'Escape' && confirmModal?.hidden === false) closeConfirmation();
    else if (event.key === 'Escape' && adjustModal?.hidden === false) closeAdjustModal();
    else if (event.key === 'Tab' && confirmModal?.hidden === false) trapFocus(event, confirmModal);
    else if (event.key === 'Tab' && adjustModal?.hidden === false) trapFocus(event, adjustModal);
});

if (serverErrors.length && oldAdjustment.variant_id) {
    const previousTrigger = Array.from(document.querySelectorAll('.stock-adjust-btn'))
        .find(button => button.dataset.variantId === String(oldAdjustment.variant_id));
    if (previousTrigger) openAdjustModal(previousTrigger, oldAdjustment);
}

});
