import { registerPage } from '../page-lifecycle.js';

registerPage(import.meta.url, scope => {

(() => {
    const escapeHtml = (value) => String(value ?? '').replace(/[&<>'"]/g, (character) => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#039;', '"': '&quot;',
    })[character]);

    const productNameInput = document.querySelector('input[name="name"]');
    const categorySelect = document.querySelector('select[name="category"]');
    const productMode = document.querySelector('[data-product-mode]');

    const updateProductMode = (productName = '') => {
        if (!productMode) return;
        const linked = productName.trim() !== '';
        productMode.innerHTML = `<i class="bi ${linked ? 'bi-link-45deg' : 'bi-plus-circle'}" aria-hidden="true"></i><span>${linked ? `Adding a variant to ${escapeHtml(productName)}` : 'Creating a new product and its first variant'}</span>`;
        productMode.classList.toggle('is-linked', linked);
    };

    document.querySelectorAll('[data-inventory-lookup]').forEach((lookup) => {
        const input = lookup.querySelector('[data-lookup-input]');
        const value = lookup.querySelector('[data-lookup-value]');
        const clear = lookup.querySelector('[data-lookup-clear]');
        const results = lookup.querySelector('[data-lookup-results]');
        const type = lookup.dataset.lookupType;
        let debounceTimer;
        let controller;
        let requestVersion = 0;

        const close = () => { results.hidden = true; };
        const showStatus = (message) => {
            results.innerHTML = `<div class="inventory-lookup-status">${escapeHtml(message)}</div>`;
            results.hidden = false;
        };

        const render = (records) => {
            const emptyChoice = `<button type="button" class="inventory-lookup-option" data-lookup-option data-id="" data-label="">
                <strong>${type === 'product' ? 'Create New Product' : 'No Supplier'}</strong>
                <span>${type === 'product' ? 'Use the product name and category fields below' : 'Leave this variant without a supplier'}</span>
            </button>`;
            const options = records.map((record) => {
                const secondary = type === 'product'
                    ? (record.category || 'Uncategorized')
                    : ([record.contact, record.phone, record.email].filter(Boolean).join(' / ') || 'No contact details');
                return `<button type="button"
                    class="inventory-lookup-option"
                    data-lookup-option
                    data-id="${record.id}"
                    data-label="${escapeHtml(record.name)}"
                    data-category="${escapeHtml(record.category || '')}">
                    <strong>${escapeHtml(record.name)}</strong>
                    <span>${escapeHtml(secondary)}</span>
                </button>`;
            }).join('');

            results.innerHTML = emptyChoice + options + (records.length ? '' : `<div class="inventory-lookup-status">No matching ${type} found.</div>`);
            results.hidden = false;
        };

        const load = async () => {
            controller?.abort();
            controller = new AbortController();
            const version = ++requestVersion;
            showStatus(`Searching ${type === 'product' ? 'products' : 'suppliers'}...`);

            try {
                const url = new URL(lookup.dataset.searchUrl, window.location.origin);
                if (input.value.trim()) url.searchParams.set('q', input.value.trim());
                const response = await scope.fetch(url, { headers: { Accept: 'application/json' }, signal: controller.signal });
                if (!response.ok) throw new Error('Lookup failed');
                const payload = await response.json();
                if (version === requestVersion) render(payload.data || []);
            } catch (error) {
                if (error.name !== 'AbortError' && version === requestVersion) {
                    showStatus(`Could not load ${type === 'product' ? 'products' : 'suppliers'}. Click the field to retry.`);
                }
            }
        };

        const select = (option) => {
            value.value = option.dataset.id || '';
            input.value = option.dataset.label || '';

            if (type === 'product') {
                if (productNameInput) {
                    productNameInput.value = option.dataset.label || '';
                }

                if (categorySelect && option.dataset.category) {
                    categorySelect.value = option.dataset.category;
                }
                updateProductMode(option.dataset.label || '');
            }

            lookup.classList.toggle('has-selection', value.value !== '');
            clear.hidden = value.value === '';
            close();
        };

        input.addEventListener('input', () => {
            value.value = '';
            clear.hidden = true;
            lookup.classList.remove('has-selection');
            if (type === 'product') updateProductMode();
            window.clearTimeout(debounceTimer);
            debounceTimer = scope.setTimeout(load, 250);
        });
        input.addEventListener('focus', load);
        input.addEventListener('click', () => { if (results.hidden) load(); });
        input.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') close();
            if (event.key === 'Enter') {
                const first = results.querySelector('[data-lookup-option]');
                if (first) { event.preventDefault(); select(first); }
            }
        });
        results.addEventListener('click', (event) => {
            const option = event.target.closest('[data-lookup-option]');
            if (option) select(option);
        });
        clear.addEventListener('click', () => {
            value.value = '';
            input.value = '';
            clear.hidden = true;
            lookup.classList.remove('has-selection');

            if (type === 'product') {
                if (productNameInput) {
                    productNameInput.value = '';
                }

                if (categorySelect) {
                    categorySelect.value = '';
                }
                updateProductMode();
            }

            input.focus();
            load();
        });
        lookup.addEventListener('click', (event) => event.stopPropagation());
        scope.listen(document, 'click', close);
    });

    const costInput = document.querySelector('input[name="cost"]');
    const priceInput = document.querySelector('input[name="price"]');
    const profitPreview = document.querySelector('[data-profit-preview]');
    const marginPreview = document.querySelector('[data-margin-preview]');
    const money = value => `\u20B1${value.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
    const updatePricingPreview = () => {
        const cost = Number(costInput?.value || 0);
        const price = Number(priceInput?.value || 0);
        const profit = Number.isFinite(price - cost) ? price - cost : 0;
        const margin = price > 0 ? (profit / price) * 100 : 0;
        if (profitPreview) profitPreview.textContent = money(profit);
        if (marginPreview) marginPreview.textContent = `${margin.toFixed(1)}%`;
        profitPreview?.classList.toggle('is-negative', profit < 0);
        marginPreview?.classList.toggle('is-negative', margin < 0);
    };
    costInput?.addEventListener('input', updatePricingPreview);
    priceInput?.addEventListener('input', updatePricingPreview);
    updatePricingPreview();

    const imageInput = document.querySelector('[data-image-input]');
    const imageHelp = document.querySelector('[data-image-help]');
    let imageObjectUrl = null;
    imageInput?.addEventListener('change', () => {
        const file = imageInput.files?.[0];
        if (!file) return;
        if (imageObjectUrl) URL.revokeObjectURL(imageObjectUrl);
        imageObjectUrl = URL.createObjectURL(file);
        const currentPreview = document.querySelector('[data-image-preview]');
        const preview = document.createElement('img');
        preview.src = imageObjectUrl;
        preview.alt = `Preview of ${file.name}`;
        preview.dataset.imagePreview = '';
        currentPreview?.replaceWith(preview);
        if (imageHelp) imageHelp.textContent = `${file.name} selected. The image will be uploaded when you save.`;
    });
    scope.dispose(() => {
        if (imageObjectUrl) URL.revokeObjectURL(imageObjectUrl);
    });
})();

});
