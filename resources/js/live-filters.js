// Enhance server-rendered GET filters without changing their no-JavaScript behavior.
let activeRequest = null;
let debounceTimer = null;
let requestVersion = 0;

function currentForm() {
    return document.querySelector('#main-content form[data-live-filter]');
}

function setStatus(form, message, error = false) {
    const status = form?.querySelector('[data-live-filter-status]');
    if (!status) return;
    status.textContent = message;
    status.classList.toggle('is-error', error);
}

function stop() {
    clearTimeout(debounceTimer);
    activeRequest?.abort();
    activeRequest = null;
    requestVersion++;
}

async function load(form, url, push = false) {
    const results = document.querySelector('#main-content [data-live-results]');
    if (!results) return;
    activeRequest?.abort();
    const controller = new AbortController();
    activeRequest = controller;
    const version = ++requestVersion;
    results.setAttribute('aria-busy', 'true');
    setStatus(form, 'Updating results…');

    try {
        const response = await fetch(url, {
            credentials: 'same-origin',
            headers: { Accept: 'text/html' },
            signal: controller.signal,
        });
        if (controller.signal.aborted || version !== requestVersion) return;
        if (response.status === 401 || response.status === 403) {
            location.assign(response.url);
            return;
        }
        if (!response.ok || !response.headers.get('content-type')?.includes('text/html')) {
            throw new Error('Results unavailable');
        }
        const page = new DOMParser().parseFromString(await response.text(), 'text/html');
        const next = page.querySelector('#main-content [data-live-results]');
        if (version !== requestVersion || !form.isConnected) return;
        if (!next) {
            location.assign(response.url);
            return;
        }
        for (const current of document.querySelectorAll('#main-content [data-live-sync]')) {
            const incoming = [...page.querySelectorAll('#main-content [data-live-sync]')]
                .find(element => element.dataset.liveSync === current.dataset.liveSync);
            if (incoming) current.replaceWith(incoming.cloneNode(true));
        }
        results.replaceWith(next);
        const destination = new URL(response.url);
        const address = `${destination.pathname}${destination.search}`;
        if (push) history.pushState({}, '', address);
        else history.replaceState({}, '', address);
        setStatus(form, 'Results updated');
        document.dispatchEvent(new Event('app:filtered'));
        if (push) next.scrollIntoView({ block: 'start' });
        setTimeout(() => {
            if (version === requestVersion && form.isConnected) setStatus(form, '');
        }, 1200);
    } catch (error) {
        if (error.name !== 'AbortError' && version === requestVersion) {
            setStatus(form, 'Could not update results. Submit the filter to retry.', true);
        }
    } finally {
        if (version === requestVersion) {
            document.querySelector('#main-content [data-live-results]')?.removeAttribute('aria-busy');
            activeRequest = null;
        }
    }
}

function formUrl(form) {
    const url = new URL(form.action, location.href);
    url.search = new URLSearchParams(new FormData(form)).toString();
    url.searchParams.delete('page');
    return url.href;
}

function syncChoices(form, name, value) {
    form.querySelectorAll(`[data-live-filter-choice="${name}"]`).forEach(choice => {
        const active = choice.dataset.liveFilterValue === value;
        choice.classList.toggle('is-active', active);
        choice.setAttribute('aria-pressed', String(active));
    });
}

document.addEventListener('submit', event => {
    const form = event.target.closest?.('form[data-live-filter]');
    if (!form) return;
    event.preventDefault();
    clearTimeout(debounceTimer);
    load(form, formUrl(form));
});

document.addEventListener('input', event => {
    const form = event.target.closest?.('form[data-live-filter]');
    if (!form || form.hasAttribute('data-live-submit-only') || !event.target.matches('input[type="search"], input[type="text"]')) return;
    clearTimeout(debounceTimer);
    activeRequest?.abort();
    requestVersion++;
    debounceTimer = setTimeout(() => load(form, formUrl(form)), 300);
});

document.addEventListener('change', event => {
    const form = event.target.closest?.('form[data-live-filter]');
    if (!form || form.hasAttribute('data-live-submit-only') || event.target.hasAttribute('data-live-manual') || !event.target.matches('select, input[type="date"]')) return;
    clearTimeout(debounceTimer);
    if (event.target.name) syncChoices(form, event.target.name, event.target.value);
    load(form, formUrl(form));
});

document.addEventListener('click', event => {
    const form = currentForm();
    if (!form || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
    const link = event.target.closest('a[href]');
    const choice = event.target.closest('[data-live-filter-choice]');
    const clearFields = event.target.closest('[data-live-filter-clear-fields]');
    if (clearFields && form.contains(clearFields)) {
        event.preventDefault();
        stop();
        for (const name of (clearFields.dataset.liveFilterClearFields || '').split(',').map(value => value.trim()).filter(Boolean)) {
            const control = form.elements.namedItem(name);
            if (control) control.value = '';
        }
        load(form, formUrl(form));
        return;
    }
    if (choice && form.contains(choice)) {
        event.preventDefault();
        const name = choice.dataset.liveFilterChoice;
        const control = form.elements.namedItem(name);
        if (!name || !control) return;
        control.value = choice.dataset.liveFilterValue || '';
        syncChoices(form, name, control.value);
        clearTimeout(debounceTimer);
        load(form, formUrl(form));
        return;
    }
    if (!link || !link.closest('#main-content')) return;
    if (link.matches('[data-live-filter-clear]') && form.contains(link)) {
        event.preventDefault();
        stop();
        form.reset();
        form.querySelectorAll('[data-live-filter-choice]').forEach(choice => {
            const control = form.elements.namedItem(choice.dataset.liveFilterChoice);
            if (control) syncChoices(form, choice.dataset.liveFilterChoice, control.value);
        });
        const clearedUrl = new URL(link.href, location.href);
        for (const control of form.elements) {
            if (control.name && !['_token', '_method'].includes(control.name)) {
                if (control.type === 'hidden' && !clearedUrl.searchParams.has(control.name)) continue;
                control.value = clearedUrl.searchParams.get(control.name) ?? '';
            }
        }
        load(form, link.href);
        return;
    }
    if (!link.closest('[data-live-results] .rules-pagination, [data-live-results] .pagination, [data-live-results] .category-pagination')) return;
    const url = new URL(link.href, location.href);
    if (url.origin !== location.origin || url.pathname !== new URL(form.action).pathname) return;
    event.preventDefault();
    load(form, url.href, true);
}, true);

document.addEventListener('app:before-navigate', stop);
