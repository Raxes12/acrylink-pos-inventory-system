import { registerPage } from '../page-lifecycle.js';

registerPage(import.meta.url, scope => {
const REFUND_CONFIG = JSON.parse(document.getElementById('refundConfig')?.textContent || '{}');
const TXN_SUBTOTAL = Number(REFUND_CONFIG.subtotal || 0);
const TXN_DISCOUNT = Number(REFUND_CONFIG.discount || 0);
const TXN_REMAINING = Number(REFUND_CONFIG.remaining || 0);
const HAS_LINE_DISCOUNTS = Boolean(REFUND_CONFIG.has_line_discounts);
const IS_CREDIT_SALE = Boolean(REFUND_CONFIG.is_credit_sale);
const OUTSTANDING_BALANCE = Number(REFUND_CONFIG.outstanding || 0);
const refundForm = document.getElementById('refundForm');
let currentStep = Math.min(3, Math.max(1, Number(REFUND_CONFIG.initial_step || 1)));
let refundState = { amount: 0, selectedLines: 0, selectedUnits: 0 };

function formatCurrency(amount) {
    return '\u20B1' + amount.toLocaleString('en-PH', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
}

function hasReturnDetails() {
    return Boolean(refundForm?.elements.reason?.value && refundForm?.elements.refund_method?.value);
}

function updateReview() {
    const reason = refundForm?.elements.reason;
    const method = refundForm?.elements.refund_method;
    const items = document.getElementById('refundReviewItems');
    const reasonOutput = document.getElementById('refundReviewReason');
    const methodOutput = document.getElementById('refundReviewMethod');

    if (items) {
        items.textContent = refundState.selectedLines > 0
            ? `${refundState.selectedUnits} unit${refundState.selectedUnits === 1 ? '' : 's'} across ${refundState.selectedLines} item${refundState.selectedLines === 1 ? '' : 's'}`
            : '\u2014';
    }
    if (reasonOutput) reasonOutput.textContent = reason?.selectedOptions?.[0]?.textContent || '\u2014';
    if (methodOutput) methodOutput.textContent = method?.selectedOptions?.[0]?.textContent || '\u2014';
}

function updateWizardAvailability() {
    const hasSelection = refundState.amount > 0;
    const hasDetails = hasReturnDetails();

    document.querySelectorAll('[data-refund-next-step="2"]').forEach(button => { button.disabled = !hasSelection; });
    document.querySelectorAll('[data-refund-next-step="3"]').forEach(button => { button.disabled = !hasSelection || !hasDetails; });

    const stepTwoButton = document.querySelector('[data-refund-go-step="2"]');
    const stepThreeButton = document.querySelector('[data-refund-go-step="3"]');
    if (stepTwoButton) stepTwoButton.disabled = !hasSelection;
    if (stepThreeButton) stepThreeButton.disabled = !hasSelection || !hasDetails;

    const submitButton = document.getElementById('refundBtn');
    if (submitButton && !submitButton.classList.contains('is-processing')) {
        submitButton.disabled = currentStep !== 3 || !hasSelection || !hasDetails;
    }
}

function goToStep(requestedStep, focusPanel = true) {
    let nextStep = Math.min(3, Math.max(1, Number(requestedStep) || 1));
    if (nextStep > 1 && refundState.amount <= 0) nextStep = 1;
    if (nextStep > 2 && !hasReturnDetails()) nextStep = 2;
    currentStep = nextStep;

    if (refundForm) refundForm.dataset.currentStep = String(currentStep);
    document.querySelectorAll('[data-refund-step-panel]').forEach(panel => {
        panel.hidden = Number(panel.dataset.refundStepPanel) !== currentStep;
    });
    document.querySelectorAll('[data-refund-step-indicator]').forEach(indicator => {
        const step = Number(indicator.dataset.refundStepIndicator);
        indicator.classList.toggle('is-active', step === currentStep);
        indicator.classList.toggle('is-complete', step < currentStep);
        const button = indicator.querySelector('[data-refund-go-step]');
        if (button) button.setAttribute('aria-current', step === currentStep ? 'step' : 'false');
    });

    updateReview();
    updateWizardAvailability();

    if (focusPanel) {
        const panel = document.querySelector(`[data-refund-step-panel="${currentStep}"]`);
        panel?.setAttribute('tabindex', '-1');
        panel?.focus({ preventScroll: true });
        document.querySelector('[data-refund-steps]')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}

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
    refundState = { amount: refundAmount, selectedLines, selectedUnits };

    const amountDisplay = document.getElementById('refundAmountDisplay');
    if (amountDisplay) {
        amountDisplay.textContent = formatCurrency(refundAmount);
    }

    const selectionStatus = document.getElementById('refundSelectionStatus');
    if (selectionStatus) {
        selectionStatus.textContent = selectedLines > 0
            ? `${selectedUnits} unit${selectedUnits === 1 ? '' : 's'} from ${selectedLines} item${selectedLines === 1 ? '' : 's'} selected`
            : 'No items selected';
    }

    const summarySelection = document.getElementById('refundSummarySelection');
    if (summarySelection) {
        summarySelection.textContent = selectedLines > 0
            ? `${selectedUnits} unit${selectedUnits === 1 ? '' : 's'} across ${selectedLines} item${selectedLines === 1 ? '' : 's'}`
            : 'Select an item to calculate the refund.';
    }

    if (IS_CREDIT_SALE) {
        const receivableReduction = Math.min(refundAmount, OUTSTANDING_BALANCE);
        const reimbursement = Math.max(0, refundAmount - receivableReduction);
        const receivableDisplay = document.getElementById('refundReceivableDisplay');
        const reimbursementDisplay = document.getElementById('refundReimbursementDisplay');
        if (receivableDisplay) receivableDisplay.textContent = formatCurrency(receivableReduction);
        if (reimbursementDisplay) reimbursementDisplay.textContent = formatCurrency(reimbursement);
    }

    const hasReason = Boolean(refundForm?.elements.reason?.value);

    const submitHelp = document.getElementById('refundSubmitHelp');
    if (submitHelp) {
        submitHelp.textContent = refundAmount <= 0
            ? 'Select at least one item to continue.'
            : !hasReason
                ? 'Choose a return reason to continue.'
                : 'Ready to review and process this refund.';
        submitHelp.classList.toggle('is-ready', refundAmount > 0 && hasReason);
    }

    if (refundForm) {
        refundForm.dataset.confirmMessage = selectedUnits > 0
            ? `Return ${selectedUnits} unit${selectedUnits === 1 ? '' : 's'} and process a refund of ${formatCurrency(refundAmount)}?`
            : 'Review the selected items and refund amount before continuing.';
    }

    updateReview();
    updateWizardAvailability();
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

scope.listen(document, 'click', event => {
    const stepButton = event.target.closest('[data-refund-qty-delta]');
    if (stepButton) {
        adjustQty(stepButton, Number.parseInt(stepButton.dataset.refundQtyDelta, 10) || 0);
        return;
    }
    if (event.target.closest('[data-refund-select-all]')) selectAll();
    if (event.target.closest('[data-refund-select-none]')) selectNone();

    const nextButton = event.target.closest('[data-refund-next-step]');
    if (nextButton && !nextButton.disabled) goToStep(nextButton.dataset.refundNextStep);

    const backButton = event.target.closest('[data-refund-back-step]');
    if (backButton) goToStep(backButton.dataset.refundBackStep);

    const navigationButton = event.target.closest('[data-refund-go-step]');
    if (navigationButton && !navigationButton.disabled) goToStep(navigationButton.dataset.refundGoStep);
});

scope.listen(document, 'input', event => {
    if (event.target.matches('.refund-qty-input')) recalcRefund();
});

scope.listen(document, 'change', event => {
    if (event.target.matches('.refund-qty-input, [name="reason"], [name="refund_method"]')) recalcRefund();
});

if (refundForm) scope.listen(refundForm, 'submit', event => {
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
goToStep(currentStep, false);

});
