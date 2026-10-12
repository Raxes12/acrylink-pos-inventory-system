const method = document.getElementById('payment_method');
const reference = document.getElementById('payment_reference');
const referenceLabel = document.getElementById('creditPaymentReferenceLabel');
const referenceGroup = document.getElementById('creditPaymentReferenceGroup');
const checkGroup = document.getElementById('creditPaymentCheckGroup');
const checkNumber = document.getElementById('check_number');
const checkBank = document.getElementById('check_bank');
const checkDate = document.getElementById('check_date');
const methodHelp = document.getElementById('creditPaymentMethodHelp');

function updateReferenceField() {
    if (!method || !reference || !referenceLabel) return;
    const help = {
        cash: 'Cash received directly from the customer.',
        gcash: 'Confirm the transfer and record its GCash reference.',
        check: 'Record the check number, issuing bank, and check date.',
    };
    const isGcash = method.value === 'gcash';
    const isCheck = method.value === 'check';

    referenceGroup.hidden = !isGcash;
    checkGroup.hidden = !isCheck;
    reference.required = isGcash;
    checkNumber.required = isCheck;
    checkBank.required = isCheck;
    checkDate.required = isCheck;
    methodHelp.textContent = help[method.value] || 'Choose how the payment was received.';
}

method?.addEventListener('change', updateReferenceField);
document.getElementById('payFullBalance')?.addEventListener('click', event => {
    const amount = document.getElementById('amount');
    amount.value = event.currentTarget.dataset.amount || '';
    amount.focus();
});
updateReferenceField();
