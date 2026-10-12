import { registerPage } from '../page-lifecycle.js';

registerPage(import.meta.url, scope => {
const REFUND_CONFIG = JSON.parse(document.getElementById('refundConfig')?.textContent || '{}');
const TXN_SUBTOTAL = Number(REFUND_CONFIG.subtotal || 0);
const TXN_DISCOUNT = Number(REFUND_CONFIG.discount || 0);
const TXN_REMAINING = Number(REFUND_CONFIG.remaining || 0);
const HAS_LINE_DISCOUNTS = Boolean(REFUND_CONFIG.has_line_discounts);

function recalcRefund() {
    let rawAmount = 0;
    let selectedLines = 0;
    let selectedUnits = 0;

    document.querySelectorAll('.refund-qty-input').forEach(input => {
        const row = input.closest('.refund-item-row');
        const price = Number.parseFloat(row.dataset.price) || 0;
        const lineDiscount = Number.parseFloat(row.dataset.discount) || 0;
        const originalQty = Number.parseInt(row.dataset.originalQty, 10) || 0;
        const max = Number.parseInt(row.dataset.max, 10) || 0;
        const qty = Math.max(0, Math.min(Number.parseInt(input.value, 10) || 0, max));

        if (Number(input.value) !== qty) input.value = qty;
        const discountPerUnit = originalQty > 0 ? lineDiscount / originalQty : 0;
        rawAmount += qty * Math.max(0, price - discountPerUnit);
        row.classList.toggle('is-selected', qty > 0);

        if (qty > 0) {
            selectedLines++;
            selectedUnits += qty;
        }
    });

    let refundAmount = rawAmount;
    if (!HAS_LINE_DISCOUNTS && TXN_SUBTOTAL > 0 && TXN_DISCOUNT > 0) {
        refundAmount = rawAmount * (1 - (TXN_DISCOUNT / TXN_SUBTOTAL));
    }
    refundAmount = Math.min(refundAmount, TXN_REMAINING);

    const amountDisplay = document.getElementById('refundAmountDisplay');
    if (amountDisplay) {
        amountDisplay.textContent = '\u20B1' + refundAmount.toLocaleString('en-PH', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        });
    }

    const selectionStatus = document.getElementById('refundSelectionStatus');
    if (selectionStatus) {
        selectionStatus.textContent = selectedLines > 0
            ? `${selectedUnits} unit${selectedUnits === 1 ? '' : 's'} from ${selectedLines} item${selectedLines === 1 ? '' : 's'} selected`
            : 'No items selected';
    }

    const button = document.getElementById('refundBtn');
    if (button && !button.classList.contains('is-processing')) {
        button.disabled = refundAmount <= 0;
    }
}

function adjustQty(button, delta) {
    const row = button.closest('.refund-item-row');
    const input = row.querySelector('.refund-qty-input');
    const max = Number.parseInt(row.dataset.max, 10) || 0;
    const value = (Number.parseInt(input.value, 10) || 0) + delta;
    input.value = Math.max(0, Math.min(value, max));
    recalcRefund();
}

function selectAll() {
    document.querySelectorAll('.refund-qty-input').forEach(input => {
        input.value = input.closest('.refund-item-row').dataset.max;
    });
    recalcRefund();
}

function selectNone() {
    document.querySelectorAll('.refund-qty-input').forEach(input => {
        input.value = 0;
    });
    recalcRefund();
}

scope.expose({ recalcRefund, adjustQty, selectAll, selectNone });

document.getElementById('refundForm')?.addEventListener('submit', event => {
    const button = document.getElementById('refundBtn');
    if (!button || button.disabled || button.classList.contains('is-processing')) {
        event.preventDefault();
        return;
    }

    button.classList.add('is-processing');
    button.disabled = true;
    button.setAttribute('aria-busy', 'true');
    const label = button.querySelector('[data-refund-button-text]');
    if (label) label.textContent = 'Processing Refund...';
});

recalcRefund();

});
