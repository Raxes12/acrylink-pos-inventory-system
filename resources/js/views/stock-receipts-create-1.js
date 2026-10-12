import { registerPage } from '../page-lifecycle.js';

registerPage(import.meta.url, scope => {
(() => {
    const list = document.getElementById('receiptItems');
    const addButton = document.getElementById('addReceiptItem');
    const template = document.getElementById('receiptItemTemplate');

    if (!list || !template) return;

    const escapeHtml = (value) => String(value ?? '').replace(/[&<>'"]/g, (character) => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#039;', '"': '&quot;',
    })[character]);

    const debounce = (callback, delay = 250) => {
        let timer;
        return (...args) => {
            window.clearTimeout(timer);
            timer = scope.setTimeout(() => callback(...args), delay);
        };
    };

    const refreshRemoveButtons = () => {
        const rows = list.querySelectorAll('[data-receipt-item-row]');
        rows.forEach((row) => {
            const button = row.querySelector('[data-remove-receipt-item]');
            if (button) button.disabled = rows.length <= 1;
        });
    };

    const closeResults = (box) => {
        const results = box?.querySelector('[data-supplier-search-results], [data-variant-search-results]');
        if (results) results.hidden = true;
    };

    const setupRemoteSearch = (box, type) => {
        if (!box || box.dataset.bound === 'true') return;
        box.dataset.bound = 'true';

        const input = box.querySelector(type === 'supplier' ? '[data-supplier-search-input]' : '[data-variant-search-input]');
        const hidden = box.querySelector(type === 'supplier' ? '[data-supplier-select]' : '[data-variant-select]');
        const clear = box.querySelector(type === 'supplier' ? '[data-supplier-search-clear]' : '[data-variant-search-clear]');
        const results = box.querySelector(type === 'supplier' ? '[data-supplier-search-results]' : '[data-variant-search-results]');
        let controller = null;
        let requestNumber = 0;

        const status = (message) => {
            results.innerHTML = `<div class="${type}-search-empty" data-search-status>${escapeHtml(message)}</div>`;
            results.hidden = false;
        };

        const render = (records) => {
            const noSupplier = type === 'supplier' ? `
                <button type="button" class="supplier-search-option" data-search-option data-id="" data-label="">
                    <span class="supplier-option-main"><strong>&mdash; No supplier &mdash;</strong></span>
                    <span class="supplier-option-meta">Leave this receipt without a supplier</span>
                </button>` : '';

            const options = records.map((record) => {
                if (type === 'supplier') {
                    const contacts = [record.phone, record.email].filter(Boolean).join(' / ') || 'No contact details';
                    return `<button type="button" class="supplier-search-option" data-search-option data-id="${record.id}" data-label="${escapeHtml(record.name)}">
                        <span class="supplier-option-main"><strong>${escapeHtml(record.name)}</strong>${record.contact ? `<span>${escapeHtml(record.contact)}</span>` : ''}</span>
                        <span class="supplier-option-meta">${escapeHtml(contacts)}</span>
                    </button>`;
                }

                const details = (record.details || []).join(' / ');
                const label = `${record.product_name} - ${record.variant_code}${details ? ` / ${details}` : ''}`;
                return `<button type="button" class="variant-search-option" data-search-option data-id="${record.id}" data-label="${escapeHtml(label)}" data-cost="${record.cost}">
                    <span class="variant-option-main"><strong>${escapeHtml(record.product_name)}</strong><span class="variant-option-code">${escapeHtml(record.variant_code)}</span></span>
                    ${details ? `<span class="variant-option-details">${escapeHtml(details)}</span>` : ''}
                    <span class="variant-option-meta"><span>Stock: ${record.stock}</span><span>&#8369;${Number(record.cost).toFixed(2)}</span></span>
                </button>`;
            }).join('');

            results.innerHTML = noSupplier + options + (records.length ? '' : `<div class="${type}-search-empty">No matching ${type} found.</div>`);
            results.hidden = false;
        };

        const load = async () => {
            controller?.abort();
            controller = new AbortController();
            const currentRequest = ++requestNumber;
            status(`Searching ${type === 'supplier' ? 'suppliers' : 'product variants'}...`);

            try {
                const url = new URL(box.dataset.searchUrl, window.location.origin);
                const query = input.value.trim();
                if (query) url.searchParams.set('q', query);
                const response = await scope.fetch(url, { headers: { Accept: 'application/json' }, signal: controller.signal });
                if (!response.ok) throw new Error('Search request failed');
                const payload = await response.json();
                if (currentRequest === requestNumber) render(payload.data || []);
            } catch (error) {
                if (error.name !== 'AbortError' && currentRequest === requestNumber) {
                    status(`Could not load ${type === 'supplier' ? 'suppliers' : 'product variants'}. Click the field to retry.`);
                }
            }
        };
        const debouncedLoad = debounce(load);

        const select = (option) => {
            hidden.value = option.dataset.id || '';
            input.value = option.dataset.label || '';
            box.classList.toggle('has-selection', hidden.value !== '');
            clear.hidden = hidden.value === '';
            closeResults(box);

            if (type === 'variant') {
                const costInput = box.closest('[data-receipt-item-row]')?.querySelector('[data-unit-cost]');
                if (costInput && (!costInput.value || Number(costInput.value) === 0) && option.dataset.cost !== '') {
                    costInput.value = Number(option.dataset.cost).toFixed(2);
                }
            }
        };

        input.addEventListener('input', () => {
            hidden.value = '';
            clear.hidden = true;
            box.classList.remove('has-selection');
            debouncedLoad();
        });
        input.addEventListener('focus', load);
        input.addEventListener('click', () => { if (results.hidden) load(); });
        input.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') closeResults(box);
            if (event.key === 'Enter') {
                const first = results.querySelector('[data-search-option]');
                if (first) { event.preventDefault(); select(first); }
            }
        });
        results.addEventListener('click', (event) => {
            const option = event.target.closest('[data-search-option]');
            if (option) select(option);
        });
        clear.addEventListener('click', (event) => {
            event.preventDefault();
            hidden.value = '';
            input.value = '';
            clear.hidden = true;
            box.classList.remove('has-selection');
            input.focus();
            load();
        });
        box.addEventListener('click', (event) => event.stopPropagation());
    };

    const bindRow = (row) => {
        setupRemoteSearch(row.querySelector('[data-variant-search]'), 'variant');
        row.querySelector('[data-remove-receipt-item]')?.addEventListener('click', () => {
            row.remove();
            refreshRemoveButtons();
        });
    };

    setupRemoteSearch(document.querySelector('[data-supplier-search]'), 'supplier');
    list.querySelectorAll('[data-receipt-item-row]').forEach(bindRow);
    addButton?.addEventListener('click', () => {
        const wrapper = document.createElement('div');
        wrapper.innerHTML = template.innerHTML.replaceAll('__INDEX__', Date.now().toString());
        const row = wrapper.firstElementChild;
        if (!row) return;
        list.appendChild(row);
        bindRow(row);
        refreshRemoveButtons();
        row.querySelector('[data-variant-search-input]')?.focus();
    });
    scope.listen(document, 'click', () => document.querySelectorAll('[data-supplier-search-results], [data-variant-search-results]').forEach((results) => { results.hidden = true; }));
    refreshRemoveButtons();
})();

});
