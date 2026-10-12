const variantPayload = JSON.parse(document.getElementById('posProducts')?.textContent || '[]');
const initialBatchPayload = JSON.parse(document.getElementById('posProductBatch')?.textContent || '{}');
const initialCheckoutState = JSON.parse(document.getElementById('posCheckoutState')?.textContent || '{}');
const VARIANTS = Object.fromEntries(variantPayload.map(variant => [String(variant.id), variant]));
const DETAIL_CACHE = {};
const DETAIL_REQUESTS = {};
const CHECKOUT_STORAGE_KEY = 'pos.pendingCheckout';

let cart = {};
let currentMethod = 'cash';
let infoVariantId = null;
let infoDetailUrl = null;
let isCheckingOut = false;
let lastTouchedCartKey = null;
let selectedCustomer = null;
let customerSearchTimer = null;
let customerSearchRequest = null;
let paymentModalTrigger = null;
let clearCartTrigger = null;

const PAYMENT_METHODS = ['cash', 'gcash', 'check', 'credit_account'];
const PAYMENT_LABELS = {
    cash: 'Cash',
    gcash: 'GCash',
    check: 'Check',
    credit_account: 'Credit Sale',
};
const PAYMENT_HELP = {
    cash: 'Enter the cash received to calculate the customer change.',
    gcash: 'Confirm the exact amount and enter the GCash reference number.',
    check: 'Record the check number, issuing bank, and check date.',
    credit_account: 'Select an eligible customer and confirm the credit terms.',
};
const fmt = n => '\u20b1' + parseFloat(n || 0).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
const cartKey = variantId => String(variantId);
const stockOf = variant => Math.max(0, parseInt(variant?.stock, 10) || 0);
const variantCartQty = variantId => Object.values(cart).reduce((sum, item) => sum + (String(item.variant_id) === String(variantId) ? item.qty : 0), 0);
const variantRemaining = variantId => stockOf(VARIANTS[variantId]) - variantCartQty(variantId);
const esc = value => String(value ?? '').replace(/[&<>"']/g, char => ({
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#39;',
}[char]));

function variantDetails(variant) {
    return [variant.part_number, variant.brand, variant.size, variant.color]
        .filter(Boolean)
        .join(' / ');
}

function variantTitle(variant) {
    const details = variantDetails(variant);
    return details ? `${variant.variant_code} - ${details}` : (variant.variant_code || variant.product_name);
}

function setText(id, value, fallback = '-') {
    const el = document.getElementById(id);
    if (el) el.textContent = value || fallback;
}

function renderVariantInfo(variant) {
    setText('variantInfoCode', variant.variant_code);
    setText('variantInfoTitle', variant.product_name);
    setText('variantInfoDescription', variant.description, 'No product description has been added yet.');
    setText('variantInfoBarcode', variant.barcode);
    setText('variantInfoPartNumber', variant.part_number);
    setText('variantInfoBrand', variant.brand);
    setText('variantInfoSize', variant.size);
    setText('variantInfoColor', variant.color);
    setText('variantInfoUnit', variant.unit || 'pcs');
    setText('variantInfoSupplier', variant.supplier);
    setText('variantInfoPrice', fmt(variant.price));
    setText('variantInfoStock', `${stockOf(variant)} ${variant.unit || 'pcs'} available`);

    const imageWrap = document.getElementById('variantInfoImageWrap');
    const image = document.getElementById('variantInfoImage');
    const imagePlaceholder = imageWrap?.querySelector('[data-variant-image-placeholder]');
    if (imageWrap && image && imagePlaceholder) {
        if (variant.image_url) {
            const imageVariantKey = String(variant.id);
            image.dataset.variantKey = imageVariantKey;
            image.hidden = true;
            imageWrap.classList.add('is-loading');
            imageWrap.classList.remove('is-loaded', 'is-missing');
            imagePlaceholder.textContent = 'Loading image...';
            image.onload = () => {
                if (image.dataset.variantKey !== imageVariantKey) return;
                image.hidden = false;
                imageWrap.classList.remove('is-loading', 'is-missing');
                imageWrap.classList.add('is-loaded');
            };
            image.onerror = () => {
                if (image.dataset.variantKey !== imageVariantKey) return;
                image.removeAttribute('src');
                image.alt = '';
                image.hidden = true;
                imageWrap.classList.remove('is-loading', 'is-loaded');
                imageWrap.classList.add('is-missing');
                imagePlaceholder.textContent = 'Image unavailable';
            };
            image.src = variant.image_url;
            image.alt = `${variant.product_name} ${variant.variant_code}`;
        } else if (variant.image_state === 'loading') {
            delete image.dataset.variantKey;
            image.onload = null;
            image.onerror = null;
            image.removeAttribute('src');
            image.alt = '';
            image.hidden = true;
            imageWrap.classList.add('is-loading');
            imageWrap.classList.remove('is-loaded', 'is-missing');
            imagePlaceholder.textContent = 'Loading image...';
        } else {
            delete image.dataset.variantKey;
            image.onload = null;
            image.onerror = null;
            image.removeAttribute('src');
            image.alt = '';
            image.hidden = true;
            imageWrap.classList.remove('is-loading', 'is-loaded');
            imageWrap.classList.add('is-missing');
            imagePlaceholder.textContent = 'No image available';
        }
    }
}

function setVariantInfoLoading(isLoading) {
    const modal = document.getElementById('variantInfoModal');
    const body = modal?.querySelector('.variant-info-body');
    if (!modal || !body) return;

    modal.setAttribute('aria-busy', String(isLoading));
    body.classList.toggle('is-loading', isLoading);
    const retryButton = document.getElementById('variantInfoRetryBtn');
    if (retryButton && isLoading) retryButton.hidden = true;
}

async function loadVariantDetails(variantId, detailUrl) {
    const key = String(variantId);
    if (DETAIL_CACHE[key]) return DETAIL_CACHE[key];
    if (DETAIL_REQUESTS[key]) return DETAIL_REQUESTS[key];
    if (!detailUrl) return VARIANTS[key];

    DETAIL_REQUESTS[key] = fetch(detailUrl, {
        headers: {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
    }).then(async response => {
        if (!response.ok) {
            throw new Error(`Variant details request failed with status ${response.status}.`);
        }

        const details = await response.json();
        VARIANTS[key] = { ...VARIANTS[key], ...details };
        DETAIL_CACHE[key] = VARIANTS[key];
        return DETAIL_CACHE[key];
    }).finally(() => {
        delete DETAIL_REQUESTS[key];
    });

    return DETAIL_REQUESTS[key];
}

async function openVariantInfo(variantId, detailUrl = null) {
    const key = String(variantId);
    const variant = VARIANTS[key];
    const modal = document.getElementById('variantInfoModal');
    if (!variant || !modal) {
        window.vmcToast?.('Variant details could not be loaded. Please refresh the POS page.', 'error');
        return;
    }

    infoVariantId = key;
    infoDetailUrl = detailUrl || variant.details_url || null;
    renderVariantInfo({
        ...variant,
        description: DETAIL_CACHE[key] ? variant.description : 'Loading variant details...',
        image_url: DETAIL_CACHE[key] ? variant.image_url : null,
        image_state: DETAIL_CACHE[key] ? null : 'loading',
    });
    modal.hidden = false;
    modal.classList.add('is-visible');
    setVariantInfoLoading(!DETAIL_CACHE[key]);

    try {
        const detailedVariant = await loadVariantDetails(key, detailUrl || variant.details_url);
        if (infoVariantId === key) {
            renderVariantInfo(detailedVariant);
            setVariantInfoLoading(false);
            const retryButton = document.getElementById('variantInfoRetryBtn');
            if (retryButton) retryButton.hidden = true;
        }
    } catch (error) {
        window.vmcToast?.('Variant details could not be loaded. Please try again.', 'error');
        if (infoVariantId === key) {
            renderVariantInfo({
                ...variant,
                description: 'Product information is temporarily unavailable.',
                image_url: null,
            });
            setVariantInfoLoading(false);
            const retryButton = document.getElementById('variantInfoRetryBtn');
            if (retryButton) retryButton.hidden = false;
        }
    }
}

function hideVariantInfo() {
    const modal = document.getElementById('variantInfoModal');
    if (!modal) return;
    modal.classList.remove('is-visible');
    modal.hidden = true;
    setVariantInfoLoading(false);
    infoVariantId = null;
    infoDetailUrl = null;
}

function selectMethod(m, announce = true) {
    const previousMethod = currentMethod;
    currentMethod = PAYMENT_METHODS.includes(m) ? m : 'cash';
    if (currentMethod !== 'credit_account' && selectedCustomer) {
        clearSelectedCustomer();
    }
    document.querySelectorAll('.method-btn').forEach(button => {
        const selected = button.dataset.method === currentMethod;
        button.classList.toggle('active', selected);
        button.setAttribute('aria-pressed', String(selected));
    });
    setText('paymentMethodHelp', PAYMENT_HELP[currentMethod]);
    ['gcashFields', 'cardFields', 'checkFields', 'creditAccountFields'].forEach(id => {
        const panel = document.getElementById(id);
        if (!panel) return;
        panel.style.display = (
            (id === 'gcashFields' && currentMethod === 'gcash') ||
            (id === 'cardFields' && currentMethod === 'credit') ||
            (id === 'checkFields' && currentMethod === 'check') ||
            (id === 'creditAccountFields' && currentMethod === 'credit_account')
        ) ? '' : 'none';
    });
    const payLbl = document.getElementById('paymentLabel');
    const chgLbl = document.getElementById('changeLabel');
    const payEl = document.getElementById('payment');
    if (currentMethod === 'credit_account' && previousMethod !== 'credit_account') payEl.value = '0.00';
    if (currentMethod === 'cash') {
        payLbl.textContent = 'Cash Received';
        chgLbl.textContent = 'Change';
        payEl.readOnly = false;
    } else if (currentMethod === 'credit_account') {
        payLbl.textContent = 'Initial Payment Applied';
        chgLbl.textContent = 'Outstanding Balance';
        payEl.readOnly = false;
    } else {
        payLbl.textContent = 'Amount Received';
        chgLbl.textContent = 'Change';
        payEl.readOnly = true;
    }
    document.getElementById('customerRequirement').textContent = 'Required';
    if (currentMethod === 'credit_account' && selectedCustomer && (!selectedCustomer.credit_enabled || selectedCustomer.available_credit == null)) clearSelectedCustomer(false);
    renderCreditCustomer();
    if (announce) window.vmcToast?.(`${PAYMENT_LABELS[currentMethod]} payment selected`, 'info');
    recalculate();
}

function addToCart(variantId, qty = 1) {
    const variant = VARIANTS[String(variantId)];
    if (!variant) {
        window.vmcToast?.('Variant data could not be loaded. Please refresh the POS page.', 'error');
        return false;
    }

    const stock = stockOf(variant);
    if (stock <= 0) {
        window.vmcToast?.(`${variantTitle(variant)} is out of stock.`, 'warning');
        return false;
    }

    const remaining = variantRemaining(variant.id);
    if (remaining <= 0) {
        window.vmcToast?.(`${variantTitle(variant)} has no remaining stock available for this cart.`, 'warning');
        return false;
    }

    qty = Math.min(Math.max(1, parseInt(qty, 10) || 1), remaining);
    const key = cartKey(variant.id);
    if (cart[key]) {
        cart[key].qty += qty;
    } else {
        cart[key] = {
            key,
            variant_id: variant.id,
            product_id: variant.product_id,
            name: variant.product_name,
            details: variantTitle(variant),
            price: parseFloat(variant.price),
            stock,
            unit: variant.unit || 'pcs',
            qty,
            discount: 0,
        };
    }

    lastTouchedCartKey = key;
    window.vmcToast?.(`${variant.product_name} - ${variant.variant_code} added to cart`, 'success');
    renderCart();
    return true;
}

function updateQty(key, qty) {
    qty = parseInt(qty, 10);
    if (!Number.isFinite(qty)) {
        window.vmcToast?.('Enter a valid quantity.', 'warning');
        renderCart();
        return;
    }
    if (qty <= 0) {
        delete cart[key];
        renderCart();
        return;
    }
    if (!cart[key]) return;

    const item = cart[key];
    const maxForItem = stockOf(VARIANTS[item.variant_id]);
    if (qty > maxForItem) {
        qty = maxForItem;
        window.vmcToast?.(`Only ${maxForItem} ${item.unit} available for ${item.details}.`, 'warning');
    }
    if (qty <= 0) {
        delete cart[key];
        renderCart();
        return;
    }

    cart[key].qty = qty;
    cart[key].discount = Math.min(Number(cart[key].discount || 0), cart[key].price * qty);
    renderCart();
}

function updateItemDiscount(key, value) {
    const item = cart[key];
    if (!item) return;

    const parsed = value === '' ? 0 : Number(value);
    item.discount = Number.isFinite(parsed) && parsed >= 0
        ? Math.min(parsed, item.price * item.qty)
        : 0;
    renderCart();
}

function removeItem(key) {
    const item = cart[key];
    delete cart[key];
    if (item) window.vmcToast?.(`${item.details} removed`, 'warning');
    renderCart();
}

function clearCart() {
    const hadItems = Object.keys(cart).length > 0;
    cart = {};
    if (hadItems) window.vmcToast?.('Cart cleared', 'warning');
    renderCart();
}

function openClearCartConfirmation() {
    if (!Object.keys(cart).length) return;
    const modal = document.getElementById('clearCartModal');
    clearCartTrigger = document.activeElement;
    modal.hidden = false;
    modal.classList.add('is-visible');
    document.body.classList.add('pos-modal-open');
    modal.querySelector('[data-cancel-clear-cart]')?.focus();
}

function closeClearCartConfirmation() {
    const modal = document.getElementById('clearCartModal');
    if (!modal || modal.hidden) return;
    modal.classList.remove('is-visible');
    modal.hidden = true;
    if (document.getElementById('paymentModal')?.hidden !== false) document.body.classList.remove('pos-modal-open');
    clearCartTrigger?.focus?.();
}

function openPaymentModal() {
    if (!Object.keys(cart).length) return;
    const modal = document.getElementById('paymentModal');
    paymentModalTrigger = document.activeElement;
    modal.hidden = false;
    modal.classList.add('is-visible');
    document.body.classList.add('pos-modal-open');
    recalculate();
    window.setTimeout(() => modal.querySelector('.method-btn.active')?.focus(), 0);
}

function closePaymentModal() {
    const modal = document.getElementById('paymentModal');
    if (!modal || modal.hidden || isCheckingOut) return;
    modal.classList.remove('is-visible');
    modal.hidden = true;
    document.body.classList.remove('pos-modal-open');
    paymentModalTrigger?.focus?.();
}

function renderCart() {
    const items = Object.values(cart);
    const container = document.getElementById('cartItems');
    const itemCount = items.reduce((sum, item) => sum + item.qty, 0);
    const cartCount = document.getElementById('cartCount');
    const paymentItemCount = document.getElementById('paymentItemCount');
    const clearCartButton = document.getElementById('clearCartBtn');
    if (cartCount) cartCount.textContent = `${itemCount} ${itemCount === 1 ? 'item' : 'items'}`;
    if (paymentItemCount) paymentItemCount.textContent = `${itemCount} ${itemCount === 1 ? 'item' : 'items'}`;
    if (clearCartButton) clearCartButton.disabled = items.length === 0;

    if (items.length === 0) {
        container.innerHTML = '<div class="cart-empty"><svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg><strong>Your cart is empty.</strong><span>Select a product to begin a sale. You can also scan an exact barcode.</span></div>';
        recalculate();
        return;
    }

    container.innerHTML = items.map(item => `
        <div class="cart-item ${item.key === lastTouchedCartKey ? 'is-fresh' : ''}" role="group" aria-label="${esc(item.name)} ${esc(item.details)}">
            <div class="cart-item-info">
                <div class="cart-item-name" title="${esc(item.name)}">${esc(item.name)}</div>
                <div class="cart-item-sub" title="${esc(item.details)}">${esc(item.details)}</div>
            </div>
            <button class="cart-remove" type="button" data-cart-action="remove" data-cart-key="${esc(item.key)}" aria-label="Remove ${esc(item.name)} from cart" title="Remove item">&times;</button>
            <div class="cart-item-controls">
                <div class="cart-price-block">
                    <span>Unit price</span>
                    <strong>${fmt(item.price)} / ${esc(item.unit)}</strong>
                    <small>${Math.max(0, item.stock - item.qty)} remaining after sale</small>
                </div>
                <div class="cart-qty-block">
                    <span>Quantity</span>
                    <div class="cart-qty-wrap">
                        <button class="cart-qty-btn" type="button" data-cart-action="decrease" data-cart-key="${esc(item.key)}" aria-label="Decrease ${esc(item.name)} quantity" ${item.qty <= 1 ? 'disabled' : ''}>-</button>
                        <input type="number" inputmode="numeric" class="cart-qty-input" value="${item.qty}" min="1" max="${item.stock}" data-cart-action="quantity" data-cart-key="${esc(item.key)}" aria-label="Quantity for ${esc(item.name)}">
                        <button class="cart-qty-btn" type="button" data-cart-action="increase" data-cart-key="${esc(item.key)}" aria-label="Increase ${esc(item.name)} quantity" ${item.qty >= item.stock ? 'disabled' : ''}>+</button>
                    </div>
                </div>
                <label class="cart-discount-block">
                    <span>Discount</span>
                    <span class="cart-discount-input"><span aria-hidden="true">₱</span><input type="number" inputmode="decimal" value="${Number(item.discount || 0).toFixed(2)}" min="0" max="${(item.price * item.qty).toFixed(2)}" step="0.01" data-cart-action="discount" data-cart-key="${esc(item.key)}" aria-label="Discount for ${esc(item.name)}"></span>
                </label>
                <div class="cart-total-block">
                    <span>${Number(item.discount || 0) > 0 ? 'After discount' : 'Line total'}</span>
                    <strong>${fmt(Math.max(0, item.price * item.qty - Number(item.discount || 0)))}</strong>
                </div>
            </div>
        </div>`).join('');
    lastTouchedCartKey = null;
    recalculate();
}

function paymentDetails() {
    return {
        gcashReference: document.getElementById('gcashReference')?.value.trim() || '',
        cardType: document.getElementById('cardType')?.value || '',
        cardLast4: document.getElementById('cardLast4')?.value.trim() || '',
        cardReference: document.getElementById('cardReference')?.value.trim() || '',
        cardPayerName: document.getElementById('cardPayerName')?.value.trim() || '',
        checkNumber: document.getElementById('checkNumber')?.value.trim() || '',
        checkBank: document.getElementById('checkBank')?.value.trim() || '',
        checkDate: document.getElementById('checkDate')?.value || '',
        initialPaymentMethod: document.getElementById('creditInitialPaymentMethod')?.value || '',
        initialPaymentReference: document.getElementById('creditInitialReference')?.value.trim() || '',
        initialCardType: document.getElementById('creditInitialCardType')?.value || '',
        initialCardLast4: document.getElementById('creditInitialCardLast4')?.value.trim() || '',
        initialPayerName: document.getElementById('creditInitialPayerName')?.value.trim() || '',
        initialCheckNumber: document.getElementById('creditInitialCheckNumber')?.value.trim() || '',
        initialCheckBank: document.getElementById('creditInitialCheckBank')?.value.trim() || '',
        initialCheckDate: document.getElementById('creditInitialCheckDate')?.value || '',
    };
}

function updateCreditInitialPaymentFields() {
    const method = document.getElementById('creditInitialPaymentMethod')?.value || '';
    document.getElementById('creditInitialReferenceFields').hidden = method !== 'gcash';
    
    document.getElementById('creditInitialCheckFields').hidden = method !== 'check';
}

function renderCreditCustomer() {
    setText('creditCustomerName', selectedCustomer?.customer_name, 'Not selected');
    setText('creditLimit', selectedCustomer ? fmt(selectedCustomer.credit_limit) : '', '-');
    setText('creditOutstanding', selectedCustomer ? fmt(selectedCustomer.current_outstanding) : '', '-');
    setText('creditAvailable', selectedCustomer ? fmt(selectedCustomer.available_credit) : '', '-');
    setText('creditRequested', selectedCustomer ? fmt(0) : '', '-');
    setText('creditProjected', selectedCustomer ? fmt(selectedCustomer.current_outstanding) : '', '-');
    setText('creditDefaultTerm', selectedCustomer ? `${selectedCustomer.default_credit_term_days} days` : '', '-');
    updateCreditDueDate();
}

function creditLimitMessage(requestedCredit) {
    return `Credit Limit: ${fmt(selectedCustomer.credit_limit)} Current Outstanding: ${fmt(selectedCustomer.current_outstanding)} Available Credit: ${fmt(selectedCustomer.available_credit)} Requested Credit: ${fmt(requestedCredit)} Credit limit exceeded.`;
}

function updateCreditDueDate() {
    const term = Number(document.getElementById('creditTermDays')?.value || 0);
    const output = document.getElementById('creditDueDate');
    if (!output) return;
    if (!Number.isInteger(term) || term < 1 || term > 65535) {
        output.textContent = '-';
        return;
    }
    const due = new Date();
    due.setHours(12, 0, 0, 0);
    due.setDate(due.getDate() + term);
    output.textContent = due.toLocaleDateString('en-PH', { year: 'numeric', month: 'short', day: 'numeric' });
}

function selectCustomer(customer) {
    selectedCustomer = customer;
    const search = document.getElementById('customerSearch');
    search.value = `${customer.customer_code} - ${customer.customer_name}`;
    document.getElementById('customerClear').hidden = false;
    document.getElementById('customerSearchResults').hidden = true;
    document.getElementById('customerSearchStatus').textContent = `${customer.customer_code} - ${customer.customer_name} selected.`;
    if (currentMethod === 'credit_account') document.getElementById('creditTermDays').value = customer.default_credit_term_days;
    renderCreditCustomer();
    recalculate();
}

function clearSelectedCustomer(clearSearch = true) {
    selectedCustomer = null;
    if (clearSearch) document.getElementById('customerSearch').value = '';
    document.getElementById('customerClear').hidden = true;
    document.getElementById('customerSearchResults').hidden = true;
    document.getElementById('customerSearchStatus').textContent = currentMethod === 'credit_account'
        ? 'Search for an active customer with credit enabled.'
        : 'No customer selected (Walk-in).';
    document.getElementById('creditTermDays').value = '';
    renderCreditCustomer();
    recalculate();
}

async function searchCustomers() {
    const picker = document.querySelector('.pos-customer-picker');
    const input = document.getElementById('customerSearch');
    const results = document.getElementById('customerSearchResults');
    const status = document.getElementById('customerSearchStatus');
    const search = input.value.trim();
    if (search.length < 2 || (selectedCustomer && search === `${selectedCustomer.customer_code} - ${selectedCustomer.customer_name}`)) {
        results.hidden = true;
        if (search.length > 0 && search.length < 2) status.textContent = 'Enter at least 2 characters.';
        return;
    }
    customerSearchRequest?.abort();
    customerSearchRequest = new AbortController();
    status.textContent = 'Searching customers...';
    try {
        const url = new URL(picker.dataset.customerSearchUrl, window.location.origin);
        url.searchParams.set('search', search);
        url.searchParams.set('credit_only', currentMethod === 'credit_account' ? '1' : '0');
        const response = await fetch(url, { headers: { Accept: 'application/json' }, signal: customerSearchRequest.signal });
        if (!response.ok) throw new Error('Customer search failed.');
        const payload = await response.json();
        results.innerHTML = payload.customers.length ? payload.customers.map(customer => `
            <button type="button" class="pos-customer-result" data-customer='${esc(JSON.stringify(customer))}'>
                <strong>${esc(customer.customer_code)} - ${esc(customer.customer_name)}</strong>
                <span>${esc(customer.phone || customer.email || 'No contact details')}</span>
            </button>`).join('') : '<div class="pos-customer-result-empty">No eligible customers found.</div>';
        results.hidden = false;
        status.textContent = `${payload.customers.length} customer${payload.customers.length === 1 ? '' : 's'} found.`;
    } catch (error) {
        if (error.name === 'AbortError') return;
        results.hidden = true;
        status.textContent = 'Customers could not be loaded. Try again.';
    }
}

function checkoutValidation(items, total, rawPayment, details) {
    if (!items.length) return 'Add at least one product to begin checkout.';
    if (items.some(item => !Number.isFinite(Number(item.discount || 0)) || Number(item.discount || 0) < 0 || Number(item.discount || 0) > item.price * item.qty)) return 'Enter a valid item discount.';
    if (!PAYMENT_METHODS.includes(currentMethod)) return 'Select a payment method.';
    if (total <= 0) return 'The total must be greater than zero.';
    if (rawPayment === '' || !Number.isFinite(Number(rawPayment)) || Number(rawPayment) < 0) return 'Enter a valid payment amount.';
    if (currentMethod === 'cash' && Number(rawPayment) < total) return 'Cash tendered is less than the amount due.';
    if (currentMethod === 'gcash' && !details.gcashReference) return 'Enter the GCash reference number.';
    if (currentMethod === 'credit' && (!details.cardType || !details.cardReference)) return 'Enter the card type and approval reference.';
    if (currentMethod === 'check' && (!details.checkNumber || !details.checkBank || !details.checkDate)) return 'Enter the check number, bank, and date.';
    if (currentMethod === 'credit_account' && !selectedCustomer) return 'Select an active customer with credit enabled.';
    if (currentMethod === 'credit_account' && (!Number.isInteger(Number(document.getElementById('creditTermDays').value)) || Number(document.getElementById('creditTermDays').value) < 1)) return 'Enter a valid credit term.';
    if (currentMethod === 'credit_account' && Number(rawPayment) > total) return 'The initial payment cannot exceed the sale total.';
    if (currentMethod === 'credit_account' && Number(rawPayment) > 0 && !details.initialPaymentMethod) return 'Select the actual initial payment method.';
    if (currentMethod === 'credit_account' && Number(rawPayment) > 0 && ['gcash', 'credit'].includes(details.initialPaymentMethod) && !details.initialPaymentReference) return 'Enter the initial payment reference number.';
    if (currentMethod === 'credit_account' && Number(rawPayment) > 0 && details.initialPaymentMethod === 'credit' && !details.initialCardType) return 'Select the initial payment card type.';
    if (currentMethod === 'credit_account' && Number(rawPayment) > 0 && details.initialPaymentMethod === 'check' && (!details.initialCheckNumber || !details.initialCheckBank || !details.initialCheckDate)) return 'Enter the initial payment check number, bank, and date.';
    if (currentMethod === 'credit_account' && Math.max(0, total - Number(rawPayment)) > Number(selectedCustomer.available_credit)) return creditLimitMessage(Math.max(0, total - Number(rawPayment)));
    return '';
}

function setCheckoutFeedback(message, type = 'error') {
    const feedback = document.getElementById('checkoutFeedback');
    if (!feedback) return;
    feedback.textContent = message;
    feedback.classList.toggle('is-error', type === 'error');
    feedback.classList.toggle('is-ready', type === 'ready');
}

function recalculate() {
    if (isCheckingOut) return;

    const items = Object.values(cart);
    const subtotal = items.reduce((s, i) => s + i.price * i.qty, 0);
    const discount = items.reduce((sum, item) => sum + Math.min(Number(item.discount || 0), item.price * item.qty), 0);
    const total = subtotal - discount;
    const payEl = document.getElementById('payment');
    const details = paymentDetails();
    let rawPayment = payEl.value.trim();
    let payment = Number(rawPayment);

    if (currentMethod !== 'cash' && currentMethod !== 'credit_account') {
        payment = total;
        payEl.value = total.toFixed(2);
        rawPayment = payEl.value;
    }
    const change = currentMethod === 'cash' ? payment - total : 0;
    const creditBalance = currentMethod === 'credit_account' && Number.isFinite(payment)
        ? Math.max(0, total - payment)
        : 0;
    if (currentMethod === 'credit_account' && selectedCustomer) {
        setText('creditRequested', fmt(creditBalance));
        setText('creditProjected', fmt(Number(selectedCustomer.current_outstanding) + creditBalance));
    }

    document.getElementById('subtotal').textContent = fmt(subtotal);
    document.getElementById('discountTotal').textContent = discount > 0 ? `-${fmt(discount)}` : fmt(0);
    document.getElementById('total').textContent = fmt(total);
    document.getElementById('paymentModalSubtotal').textContent = fmt(subtotal);
    document.getElementById('paymentModalDiscount').textContent = discount > 0 ? `-${fmt(discount)}` : fmt(0);
    document.getElementById('amountDue').textContent = fmt(total);

    const chgEl = document.getElementById('change');
    if (currentMethod === 'cash') {
        chgEl.textContent = change >= 0 ? fmt(change) : '-';
        chgEl.style.color = change >= 0 ? 'var(--accent2)' : 'var(--danger)';
    } else if (currentMethod === 'credit_account') {
        chgEl.textContent = fmt(creditBalance);
        chgEl.style.color = 'var(--text)';
    } else {
        chgEl.textContent = '-';
        chgEl.style.color = 'var(--muted)';
    }

    const validationMessage = checkoutValidation(items, total, rawPayment, details);
    const ok = validationMessage === '';
    const btn = document.getElementById('checkoutBtn');
    btn.disabled = !ok;
    const proceedButton = document.getElementById('proceedPaymentBtn');
    if (proceedButton) proceedButton.disabled = items.length === 0 || total <= 0;
    setCheckoutFeedback(validationMessage || 'Ready to process sale.', ok ? 'ready' : 'error');
}

function persistPendingCheckout() {
    try {
        window.sessionStorage.setItem(CHECKOUT_STORAGE_KEY, JSON.stringify({
            cart,
            currentMethod,
            payment: document.getElementById('payment')?.value || '',
            details: paymentDetails(),
            selectedCustomer,
            creditTermDays: document.getElementById('creditTermDays')?.value || '',
            savedAt: Date.now(),
        }));
    } catch (error) {
        // Checkout can proceed when browser storage is unavailable.
    }
}

function restorePendingCheckout() {
    let snapshot = null;
    try {
        snapshot = JSON.parse(window.sessionStorage.getItem(CHECKOUT_STORAGE_KEY) || 'null');
        if (!initialCheckoutState.has_errors) {
            window.sessionStorage.removeItem(CHECKOUT_STORAGE_KEY);
            return;
        }
    } catch (error) {
        return;
    }

    if (!snapshot?.cart || Date.now() - Number(snapshot.savedAt || 0) > 2 * 60 * 60 * 1000) return;

    cart = snapshot.cart;
    Object.values(cart).forEach(item => {
        item.discount = Number(item.discount || 0);
        VARIANTS[String(item.variant_id)] = {
            ...VARIANTS[String(item.variant_id)],
            id: item.variant_id,
            product_id: item.product_id,
            product_name: item.name,
            price: item.price,
            stock: item.stock,
            unit: item.unit,
        };
    });

    selectedCustomer = snapshot.selectedCustomer || null;
    selectMethod(snapshot.currentMethod, false);
    const fields = {
        payment: snapshot.payment,
        gcashReference: snapshot.details?.gcashReference,
        cardType: snapshot.details?.cardType,
        cardLast4: snapshot.details?.cardLast4,
        cardReference: snapshot.details?.cardReference,
        cardPayerName: snapshot.details?.cardPayerName,
        checkNumber: snapshot.details?.checkNumber,
        checkBank: snapshot.details?.checkBank,
        checkDate: snapshot.details?.checkDate,
        creditInitialPaymentMethod: snapshot.details?.initialPaymentMethod,
        creditInitialReference: snapshot.details?.initialPaymentReference,
        creditInitialCardType: snapshot.details?.initialCardType,
        creditInitialCardLast4: snapshot.details?.initialCardLast4,
        creditInitialPayerName: snapshot.details?.initialPayerName,
        creditInitialCheckNumber: snapshot.details?.initialCheckNumber,
        creditInitialCheckBank: snapshot.details?.initialCheckBank,
        creditInitialCheckDate: snapshot.details?.initialCheckDate,
    };
    Object.entries(fields).forEach(([id, value]) => {
        const field = document.getElementById(id);
        if (field && value != null) field.value = value;
    });
    if (selectedCustomer) {
        document.getElementById('customerSearch').value = `${selectedCustomer.customer_code} - ${selectedCustomer.customer_name}`;
        document.getElementById('customerClear').hidden = false;
        document.getElementById('customerSearchStatus').textContent = `${selectedCustomer.customer_code} - ${selectedCustomer.customer_name} selected.`;
    }
    document.getElementById('creditTermDays').value = snapshot.creditTermDays || selectedCustomer?.default_credit_term_days || '';
    updateCreditInitialPaymentFields();
    renderCreditCustomer();
    renderCart();
    setCheckoutFeedback(initialCheckoutState.error || 'The sale could not be completed. Please try again.', 'error');
}

function submitCheckout() {
    if (isCheckingOut) return;

    const items = Object.values(cart);
    if (!items.length) return;
    const subtotal = items.reduce((s, i) => s + i.price * i.qty, 0);
    const discount = items.reduce((sum, item) => sum + Math.min(Number(item.discount || 0), item.price * item.qty), 0);
    const total = subtotal - discount;
    const rawPayment = document.getElementById('payment').value.trim();
    const payment = Number(rawPayment);
    const details = paymentDetails();
    const validationMessage = checkoutValidation(items, total, rawPayment, details);
    if (validationMessage) {
        setCheckoutFeedback(validationMessage, 'error');
        return;
    }

    document.getElementById('formDiscount').value = discount;
    document.getElementById('formPaymentMethod').value = currentMethod;
    document.getElementById('formPaymentReference').value = currentMethod === 'gcash'
        ? details.gcashReference
        : (currentMethod === 'credit' ? details.cardReference : '');
    document.getElementById('formPaymentCardType').value = currentMethod === 'credit' ? details.cardType : '';
    document.getElementById('formPaymentCardLast4').value = currentMethod === 'credit' ? details.cardLast4 : '';
    document.getElementById('formPaymentPayerName').value = currentMethod === 'credit' ? details.cardPayerName : '';
    document.getElementById('formCheckNumber').value = currentMethod === 'check' ? details.checkNumber : '';
    document.getElementById('formCheckBank').value = currentMethod === 'check' ? details.checkBank : '';
    document.getElementById('formCheckDate').value = currentMethod === 'check' ? details.checkDate : '';
    document.getElementById('formPayment').value = ['cash', 'credit_account'].includes(currentMethod) ? payment : total;
    document.getElementById('formCustomerId').value = currentMethod === 'credit_account' ? (selectedCustomer?.id || '') : '';
    document.getElementById('formCreditTermDays').value = currentMethod === 'credit_account' ? document.getElementById('creditTermDays').value : '';
    document.getElementById('formInitialPaymentMethod').value = currentMethod === 'credit_account' ? details.initialPaymentMethod : '';
    document.getElementById('formInitialPaymentReference').value = currentMethod === 'credit_account' ? details.initialPaymentReference : '';
    document.getElementById('formInitialPaymentCardType').value = currentMethod === 'credit_account' && details.initialPaymentMethod === 'credit' ? details.initialCardType : '';
    document.getElementById('formInitialPaymentCardLast4').value = currentMethod === 'credit_account' && details.initialPaymentMethod === 'credit' ? details.initialCardLast4 : '';
    document.getElementById('formInitialPaymentPayerName').value = currentMethod === 'credit_account' && details.initialPaymentMethod === 'credit' ? details.initialPayerName : '';
    document.getElementById('formInitialCheckNumber').value = currentMethod === 'credit_account' && details.initialPaymentMethod === 'check' ? details.initialCheckNumber : '';
    document.getElementById('formInitialCheckBank').value = currentMethod === 'credit_account' && details.initialPaymentMethod === 'check' ? details.initialCheckBank : '';
    document.getElementById('formInitialCheckDate').value = currentMethod === 'credit_account' && details.initialPaymentMethod === 'check' ? details.initialCheckDate : '';

    const fi = document.getElementById('formItems');
    fi.innerHTML = '';
    items.forEach((item, i) => {
        fi.innerHTML += `<input type="hidden" name="items[${i}][variant_id]" value="${esc(item.variant_id)}">
                         <input type="hidden" name="items[${i}][quantity]" value="${esc(item.qty)}">
                         <input type="hidden" name="items[${i}][unit_price]" value="${esc(item.price)}">
                         <input type="hidden" name="items[${i}][discount]" value="${esc(Number(item.discount || 0).toFixed(2))}">`;
    });

    persistPendingCheckout();
    isCheckingOut = true;
    const btn = document.getElementById('checkoutBtn');
    btn.disabled = true;
    btn.classList.add('is-processing');
    btn.setAttribute('aria-busy', 'true');
    btn.querySelector('.checkout-submit-text').textContent = 'Processing Sale';
    const overlay = document.getElementById('saleProcessingOverlay');
    overlay.classList.add('is-visible');
    overlay.setAttribute('aria-hidden', 'false');

    document.getElementById('checkoutForm').submit();
}

const posFilterForm = document.getElementById('posFilterForm');
const posSearch = document.getElementById('posSearch');
const posSearchClear = document.getElementById('posSearchClear');
const posSearchFeedback = document.getElementById('posSearchFeedback');
const productGrid = document.getElementById('productGrid');
const posLoadSentinel = document.getElementById('posLoadSentinel');
const posLoadMessage = posLoadSentinel?.querySelector('[data-pos-load-message]');
const posLoadRetry = posLoadSentinel?.querySelector('[data-pos-load-retry]');
let productFilterTimer = null;
let productFeedbackTimer = null;
let productSkeletonTimer = null;
let productFetchController = null;
let productObserver = null;
let productRequestVersion = 0;
let productState = {
    nextPage: initialBatchPayload.next_page || null,
    hasMore: Boolean(initialBatchPayload.has_more),
    batchSize: initialBatchPayload.batch_size || 30,
    isLoading: false,
    error: false,
    requestKey: null,
    retryRequest: null,
    search: posSearch?.value.trim() || '',
    category: '',
};

function registerVariants(variants) {
    variants.forEach(variant => {
        const key = String(variant.id);
        VARIANTS[key] = { ...VARIANTS[key], ...variant };
    });
}

function productInitials(name) {
    const initials = String(name || 'Product')
        .trim()
        .split(/\s+/)
        .slice(0, 2)
        .map(word => word.charAt(0).toUpperCase())
        .join('');

    return initials || 'P';
}

function productStockState(variant) {
    if (variant.stock_status === 'low' || variant.stock_status === 'out') return variant.stock_status;
    return stockOf(variant) <= 0 ? 'out' : 'in';
}

function productCardHtml(variant) {
    const stock = stockOf(variant);
    const productName = variant.product_name || 'Deleted product';
    const stockState = productStockState(variant);
    const stockLabel = { in: 'In Stock', low: 'Low Stock', out: 'Out of Stock' }[stockState];
    const image = variant.image_url ? `
        <img src="${esc(variant.image_url)}"
             alt="${esc(`${productName} ${variant.variant_code}`)}"
             width="320" height="240" loading="lazy" decoding="async">` : '';

    return `
        <article class="product-card product-card--${stockState}" data-variant-id="${esc(variant.id)}">
            <div class="product-card-image ${variant.image_url ? 'is-loading' : 'is-missing'}">
                <span class="product-card-image-placeholder" aria-hidden="true">${esc(productInitials(productName))}</span>
                ${image}
            </div>
            <div class="product-stock product-stock--${stockState}" aria-label="${stockLabel}, ${stock} ${esc(variant.unit || 'pcs')} available">
                <span class="product-stock-label">${stockLabel}</span>
                <span aria-hidden="true">&middot;</span>
                <span>${stock} ${esc(variant.unit || 'pcs')}</span>
            </div>
            <button type="button"
                    class="product-info-btn"
                    data-info-variant="${esc(variant.id)}"
                    data-info-url="${esc(variant.details_url || '')}"
                    title="Product information"
                    aria-label="View details for ${esc(productName)} ${esc(variant.variant_code)}">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="16" x2="12" y2="12"></line>
                    <line x1="12" y1="8" x2="12.01" y2="8"></line>
                </svg>
            </button>
            <div class="product-card-content">
                <div class="product-name" title="${esc(productName)}">${esc(productName)}</div>
                <div class="product-variant-details" aria-label="Variant identifying details">
                    ${variant.part_number ? `<span title="Part number ${esc(variant.part_number)}"><small>Part No.</small><strong>${esc(variant.part_number)}</strong></span>` : ''}
                    ${variant.brand ? `<span title="Brand ${esc(variant.brand)}"><small>Brand</small><strong>${esc(variant.brand)}</strong></span>` : ''}
                    ${variant.size ? `<span title="Size ${esc(variant.size)}"><small>Size</small><strong>${esc(variant.size)}</strong></span>` : ''}
                    ${variant.color ? `<span title="Color ${esc(variant.color)}"><small>Color</small><strong>${esc(variant.color)}</strong></span>` : ''}
                    ${!variant.part_number && !variant.brand && !variant.size && !variant.color ? '<span class="product-variant-standard"><small>Details</small><strong>Standard variant</strong></span>' : ''}
                </div>
                <div class="product-price">${fmt(variant.price)}</div>
            </div>
            <div class="product-card-actions">
                <button type="button" class="product-add-btn" data-add-variant="${esc(variant.id)}"
                        aria-label="Add ${esc(productName)} ${esc(variant.variant_code)} to cart"
                        ${stockState === 'out' ? 'disabled' : ''}>
                    <span aria-hidden="true">+</span>
                    <span>${stockState === 'out' ? 'Unavailable' : 'Add to Cart'}</span>
                </button>
            </div>
        </article>`;
}

function settleProductImage(image, loaded = image.naturalWidth > 0) {
    const imageWrap = image.closest('.product-card-image');
    if (!imageWrap) return;

    imageWrap.classList.remove('is-loading');
    imageWrap.classList.toggle('is-loaded', loaded);
    imageWrap.classList.toggle('is-missing', !loaded);
    image.hidden = !loaded;
    if (!loaded) image.removeAttribute('src');
}

function attachLoadSentinel() {
    if (!productGrid) return;

    if (posLoadSentinel && posLoadSentinel.parentElement !== productGrid) {
        productGrid.appendChild(posLoadSentinel);
    }
}

function setLoadState(mode = 'idle') {
    if (!posLoadSentinel || !posLoadMessage) return;

    posLoadSentinel.classList.toggle('is-loading', mode === 'loading');
    posLoadSentinel.classList.toggle('is-error', mode === 'error');
    posLoadSentinel.classList.toggle('is-complete', mode === 'complete');

    posLoadMessage.textContent = {
        loading: 'Loading more products...',
        error: 'Products could not be loaded.',
        complete: productGrid?.querySelector('.product-card') ? 'All products loaded' : '',
        idle: '',
    }[mode] || '';

    if (posLoadRetry) {
        posLoadRetry.hidden = mode !== 'error';
    }
}

function updateSearchControls() {
    if (posSearchClear) posSearchClear.hidden = !posSearch?.value;
}

function setSearchFeedback(mode = 'idle') {
    if (!posFilterForm || !posSearchFeedback) return;

    posFilterForm.classList.toggle('is-searching', mode === 'loading');
    posFilterForm.setAttribute('aria-busy', String(mode === 'loading'));
    posSearchFeedback.textContent = {
        loading: 'Searching products...',
        empty: 'No matching products found.',
        error: 'Product search is temporarily unavailable.',
        idle: '',
    }[mode] || '';
}

function clearDeferredProductFeedback() {
    clearTimeout(productFeedbackTimer);
    clearTimeout(productSkeletonTimer);
    productFeedbackTimer = null;
    productSkeletonTimer = null;
}

function renderProductSkeletons() {
    if (!productGrid || productGrid.querySelector('.product-card')) return;
    productGrid.innerHTML = Array.from({ length: 6 }, () => `
        <div class="product-skeleton" aria-hidden="true">
            <span class="product-skeleton-image"></span>
            <span class="product-skeleton-line product-skeleton-line--title"></span>
            <span class="product-skeleton-line"></span>
            <span class="product-skeleton-line product-skeleton-line--short"></span>
        </div>`).join('');
    attachLoadSentinel();
}

function scheduleReplacementFeedback() {
    clearDeferredProductFeedback();
    productFeedbackTimer = window.setTimeout(() => setSearchFeedback('loading'), 120);
    productSkeletonTimer = window.setTimeout(renderProductSkeletons, 160);
}

function noProductsHtml(filters) {
    const title = filters.search
        ? 'No search results'
        : 'Search for a product';
    const context = filters.search
        ? `No products match "${esc(filters.search)}"${filters.category ? ` in ${esc(filters.category)}` : ''}.`
        : 'Enter a product name, code, barcode, part number, or brand to view matching products.';

    return `<div id="noProductsMsg" class="pos-empty">
        <strong>${title}</strong>
        <span>${context}</span>
    </div>`;
}

function productRequestErrorHtml() {
    return `<div class="pos-empty pos-state--error" role="alert">
        <strong>Products unavailable</strong>
        <span>Products could not be loaded. Check your connection and try again.</span>
        <button type="button" class="pos-state-retry" data-pos-grid-retry>Retry</button>
    </div>`;
}

function renderProductGrid(variants, mode = 'replace', filters = currentProductFilters()) {
    if (!productGrid) return;

    registerVariants(variants);
    const html = variants.map(productCardHtml).join('');

    if (mode === 'append') {
        attachLoadSentinel();
        if (posLoadSentinel?.parentElement === productGrid) {
            posLoadSentinel.insertAdjacentHTML('beforebegin', html);
        } else {
            productGrid.insertAdjacentHTML('beforeend', html);
        }
    } else {
        productGrid.innerHTML = variants.length
            ? html
            : noProductsHtml(filters);
    }

    attachLoadSentinel();
}

function currentProductFilters() {
    return {
        search: posSearch?.value.trim() || '',
        category: '',
    };
}

function filterSignature(filters = currentProductFilters()) {
    return `${filters.search}\n${filters.category}`;
}

function productPageUrl(page = 1, endpoint = posFilterForm?.dataset.productsUrl || posFilterForm?.action || window.location.href, filters = currentProductFilters(), includeBatchSize = true) {
    const url = new URL(endpoint, window.location.origin);
    const data = new FormData();

    Object.entries(filters).forEach(([key, value]) => data.set(key, value));

    data.forEach((value, key) => {
        const cleaned = String(value || '').trim();
        if (cleaned === '') {
            url.searchParams.delete(key);
        } else {
            url.searchParams.set(key, cleaned);
        }
    });

    url.searchParams.delete('page');
    if (page > 1) url.searchParams.set('page', page);
    url.searchParams.delete('per_page');
    if (includeBatchSize) url.searchParams.set('per_page', productState.batchSize);

    return url;
}

function posHistoryUrl(page = 1) {
    return productPageUrl(page, posFilterForm?.action || window.location.href, currentProductFilters(), false);
}

function updateProductState(data, page, filters) {
    productState.hasMore = Boolean(data.has_more);
    productState.nextPage = data.next_page || null;
    productState.batchSize = data.batch_size || productState.batchSize;
    productState.search = filters.search;
    productState.category = filters.category;
    productState.error = false;
    productState.requestKey = null;
    productState.retryRequest = null;
}

async function loadProductBatch(page = 1, { mode = 'replace', updateHistory = false, focusFirstAction = false, addExactBarcode = false } = {}) {
    if (!productGrid) return;

    const filters = currentProductFilters();

    if (!filters.search) {
        productFetchController?.abort();
        productRequestVersion += 1;
        clearDeferredProductFeedback();
        productState.isLoading = false;
        productState.hasMore = false;
        productState.nextPage = null;
        productState.error = false;
        productState.requestKey = null;
        productState.retryRequest = null;
        productGrid.removeAttribute('aria-busy');
        productGrid.innerHTML = noProductsHtml(filters);
        attachLoadSentinel();
        setLoadState('idle');
        setSearchFeedback('idle');
        if (updateHistory) window.history.replaceState({}, '', posHistoryUrl().toString());
        return;
    }

    const signature = filterSignature(filters);
    const requestKey = `${signature}\n${page}\n${mode}`;

    if (productState.isLoading && productState.requestKey === requestKey) return;
    if (productState.isLoading && mode === 'append') return;

    if (mode === 'append' && (!productState.hasMore || productState.error)) return;

    if (mode === 'replace') {
        productFetchController?.abort();
        productRequestVersion += 1;
        productState.hasMore = false;
        productState.nextPage = null;
        productState.error = false;
        productState.retryRequest = null;
        scheduleReplacementFeedback();
    }

    const requestVersion = productRequestVersion;
    const requestController = new AbortController();
    productFetchController = requestController;
    productState.isLoading = true;
    productState.requestKey = requestKey;
    productGrid.setAttribute('aria-busy', 'true');
    setLoadState(mode === 'append' ? 'loading' : 'idle');

    try {
        const url = productPageUrl(page, undefined, filters);
        const response = await fetch(url, {
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            signal: requestController.signal,
        });

        if (!response.ok) {
            throw new Error('Product page request failed.');
        }

        const data = await response.json();
        if (requestVersion !== productRequestVersion || filterSignature() !== signature) return;

        clearDeferredProductFeedback();
        const variants = data.data || data.items || [];
        renderProductGrid(variants, mode, filters);
        updateProductState(data, page, filters);
        setLoadState(productState.hasMore ? 'idle' : 'complete');
        if (mode === 'replace') {
            setSearchFeedback(variants.length ? 'idle' : 'empty');
            const normalizedSearch = filters.search.toLowerCase();
            const exactBarcodeMatches = variants.filter(variant => String(variant.barcode || '').toLowerCase() === normalizedSearch);
            if (addExactBarcode && exactBarcodeMatches.length === 1) {
                if (addToCart(exactBarcodeMatches[0].id, 1)) {
                    posSearch?.select();
                    setSearchFeedback('idle');
                }
                return;
            }
            if (focusFirstAction) {
                const focusVariant = exactBarcodeMatches.length === 1
                    ? exactBarcodeMatches[0]
                    : (variants.length === 1 ? variants[0] : null);
                if (focusVariant) {
                    window.requestAnimationFrame(() => {
                        productGrid.querySelector(`[data-add-variant="${focusVariant.id}"]:not(:disabled)`)?.focus();
                    });
                }
            }
        }

        if (updateHistory) {
            window.history.replaceState({}, '', posHistoryUrl().toString());
        }
    } catch (error) {
        if (error.name !== 'AbortError' && requestVersion === productRequestVersion) {
            clearDeferredProductFeedback();
            productState.error = true;
            productState.requestKey = null;
            productState.retryRequest = { page, mode, updateHistory, focusFirstAction };
            const hasExistingProducts = Boolean(productGrid.querySelector('.product-card'));
            if (mode === 'replace' && !hasExistingProducts) {
                productGrid.innerHTML = productRequestErrorHtml();
                attachLoadSentinel();
            }
            setLoadState(mode === 'replace' && !hasExistingProducts ? 'idle' : 'error');
            if (mode === 'replace') setSearchFeedback('error');
            window.vmcToast?.('Products could not be loaded. Please try again.', 'error');
        }
    } finally {
        if (requestVersion === productRequestVersion && productFetchController === requestController) {
            productState.isLoading = false;
            productState.requestKey = null;
            productFetchController = null;
            productGrid.removeAttribute('aria-busy');
        }
    }
}

function submitProductFilters(delay = 0, options = {}) {
    clearTimeout(productFilterTimer);
    productFilterTimer = window.setTimeout(() => {
        loadProductBatch(1, { mode: 'replace', updateHistory: true, ...options });
    }, delay);
}

function loadNextProductBatch() {
    if (!productState.hasMore || !productState.nextPage || productState.isLoading || productState.error) return;

    loadProductBatch(productState.nextPage, { mode: 'append' });
}

function observeProductGridEnd() {
    if (!posLoadSentinel || !('IntersectionObserver' in window)) return;

    productObserver?.disconnect();
    productObserver = new IntersectionObserver(entries => {
        if (entries.some(entry => entry.isIntersecting)) {
            loadNextProductBatch();
        }
    }, {
        root: productGrid && getComputedStyle(productGrid).overflowY !== 'visible' ? productGrid : null,
        rootMargin: '220px 0px',
        threshold: 0,
    });
    productObserver.observe(posLoadSentinel);
}

Object.assign(window, {
    openVariantInfo,
    hideVariantInfo,
    addToCart,
    updateQty,
    removeItem,
    clearCart,
    openClearCartConfirmation,
    closeClearCartConfirmation,
    openPaymentModal,
    closePaymentModal,
    recalculate,
    selectMethod,
    submitCheckout,
    updateCreditInitialPaymentFields,
});

posFilterForm?.addEventListener('submit', event => {
    event.preventDefault();
    loadProductBatch(1, { mode: 'replace', updateHistory: true, focusFirstAction: true, addExactBarcode: true });
});
posSearch?.addEventListener('input', () => {
    updateSearchControls();
    submitProductFilters(350);
});
posSearchClear?.addEventListener('click', () => {
    if (!posSearch) return;
    posSearch.value = '';
    updateSearchControls();
    submitProductFilters();
    posSearch.focus();
});
posLoadRetry?.addEventListener('click', () => {
    const retryRequest = productState.retryRequest;
    if (!retryRequest) return;
    productState.error = false;
    loadProductBatch(retryRequest.page, {
        mode: retryRequest.mode,
        updateHistory: retryRequest.updateHistory,
        focusFirstAction: retryRequest.focusFirstAction,
    });
});
document.addEventListener('keydown', event => {
    const target = event.target;
    const isTyping = target instanceof HTMLInputElement
        || target instanceof HTMLTextAreaElement
        || target instanceof HTMLSelectElement
        || target?.isContentEditable;
    if (event.key === '/' && !isTyping && !event.ctrlKey && !event.metaKey && !event.altKey) {
        event.preventDefault();
        posSearch?.focus();
        posSearch?.select();
        return;
    }

    if ((event.ctrlKey || event.metaKey) && !event.altKey && event.key.toLowerCase() === 'k') {
        event.preventDefault();
        posSearch?.focus();
        posSearch?.select();
        return;
    }

    if (event.key === 'Escape' && document.activeElement === posSearch && posSearch.value) {
        posSearchClear?.click();
        return;
    }

    if (event.key === 'Escape' && document.getElementById('clearCartModal')?.hidden === false) {
        closeClearCartConfirmation();
        return;
    }

    if (event.key === 'Escape' && document.getElementById('paymentModal')?.hidden === false) {
        closePaymentModal();
    }
});
productGrid?.addEventListener('click', event => {
    const gridRetry = event.target.closest('[data-pos-grid-retry]');
    if (gridRetry) {
        loadProductBatch(1, { mode: 'replace' });
        return;
    }

    const infoButton = event.target.closest('[data-info-variant]');
    if (infoButton) {
        event.preventDefault();
        event.stopPropagation();
        openVariantInfo(infoButton.dataset.infoVariant, infoButton.dataset.infoUrl);
        return;
    }

    const addButton = event.target.closest('[data-add-variant]');
    if (!addButton || addButton.disabled) return;
    addToCart(addButton.dataset.addVariant, 1);
});
productGrid?.addEventListener('load', event => {
    if (event.target.matches('.product-card-image img')) settleProductImage(event.target, true);
}, true);
productGrid?.addEventListener('error', event => {
    if (event.target.matches('.product-card-image img')) settleProductImage(event.target, false);
}, true);
window.addEventListener('popstate', () => loadProductBatch(1, { mode: 'replace' }));
document.getElementById('variantInfoModal')?.addEventListener('click', event => {
    if (event.target.id === 'variantInfoModal' || event.target.closest('[data-close-variant-info]')) {
        hideVariantInfo();
    }
});
document.getElementById('variantInfoAddBtn')?.addEventListener('click', () => {
    if (infoVariantId && addToCart(infoVariantId, 1)) hideVariantInfo();
});
document.getElementById('variantInfoRetryBtn')?.addEventListener('click', () => {
    if (infoVariantId) openVariantInfo(infoVariantId, infoDetailUrl);
});
document.getElementById('paymentModal')?.addEventListener('click', event => {
    if (event.target.id === 'paymentModal' || event.target.closest('[data-close-payment-modal]')) closePaymentModal();
});
document.getElementById('clearCartModal')?.addEventListener('click', event => {
    if (event.target.id === 'clearCartModal' || event.target.closest('[data-cancel-clear-cart]')) closeClearCartConfirmation();
    if (event.target.closest('[data-confirm-clear-cart]')) {
        clearCart();
        closeClearCartConfirmation();
    }
});
[
    'gcashReference',
    'cardType',
    'cardLast4',
    'cardReference',
    'cardPayerName',
    'checkNumber',
    'checkBank',
    'checkDate',
].forEach(id => {
    const eventName = id === 'cardType' || id === 'checkDate' ? 'change' : 'input';
    document.getElementById(id)?.addEventListener(eventName, recalculate);
});
document.getElementById('cartItems')?.addEventListener('click', event => {
    const control = event.target.closest('[data-cart-action]');
    if (!control) return;
    const key = control.dataset.cartKey;
    const item = cart[key];
    if (!item) return;

    if (control.dataset.cartAction === 'decrease') updateQty(key, item.qty - 1);
    if (control.dataset.cartAction === 'increase') updateQty(key, item.qty + 1);
    if (control.dataset.cartAction === 'remove') removeItem(key);
});
document.getElementById('cartItems')?.addEventListener('change', event => {
    const input = event.target.closest('[data-cart-action="quantity"], [data-cart-action="discount"]');
    if (!input) return;
    if (input.dataset.cartAction === 'quantity') updateQty(input.dataset.cartKey, input.value);
    if (input.dataset.cartAction === 'discount') updateItemDiscount(input.dataset.cartKey, input.value);
});
document.getElementById('customerSearch')?.addEventListener('input', () => {
    if (selectedCustomer) selectedCustomer = null;
    clearTimeout(customerSearchTimer);
    customerSearchTimer = window.setTimeout(searchCustomers, 300);
    recalculate();
});
document.getElementById('customerClear')?.addEventListener('click', () => clearSelectedCustomer());
document.getElementById('customerSearchResults')?.addEventListener('click', event => {
    const button = event.target.closest('[data-customer]');
    if (!button) return;
    try {
        selectCustomer(JSON.parse(button.dataset.customer));
    } catch (error) {
        setCheckoutFeedback('The selected customer could not be loaded.', 'error');
    }
});

document.querySelector('[data-pos-scroll-cart]')?.addEventListener('click', () => {
    const behavior = window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth';
    document.querySelector('.pos-cart')?.scrollIntoView({ behavior, block: 'start' });
});

document.querySelector('[data-pos-scroll-products]')?.addEventListener('click', () => {
    const behavior = window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth';
    document.getElementById('posFilterForm')?.scrollIntoView({ behavior, block: 'start' });
    window.setTimeout(() => posSearch?.focus({ preventScroll: true }), 280);
});

attachLoadSentinel();
updateSearchControls();
productGrid?.querySelectorAll('.product-card-image img').forEach(image => {
    if (image.complete) settleProductImage(image);
});
restorePendingCheckout();
if (initialCheckoutState.has_errors && Object.keys(cart).length) openPaymentModal();
setLoadState(productState.hasMore ? 'idle' : 'complete');
observeProductGridEnd();
