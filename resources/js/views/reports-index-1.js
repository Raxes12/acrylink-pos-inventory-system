import { registerPage } from '../page-lifecycle.js';

registerPage(import.meta.url, scope => {
    let request = null;
    let requestVersion = 0;

    const syncDateBounds = form => {
        if (!form) return;
        const from = form.querySelector('[name="date_from"]');
        const to = form.querySelector('[name="date_to"]');
        if (!from || !to) return;
        from.max = to.value || '';
        to.min = from.value || '';
    };

    const replacePeriodControls = page => {
        const replacements = [
            ['[data-report-period-copy]', '[data-report-period-copy]'],
            ['.report-period-presets', '.report-period-presets'],
            ['[data-report-filter-form]', '[data-report-filter-form]'],
        ];

        replacements.forEach(([currentSelector, incomingSelector]) => {
            const current = document.querySelector(currentSelector);
            const incoming = page.querySelector(incomingSelector);
            if (current && incoming) current.replaceWith(incoming);
        });

        ['csv', 'excel', 'pdf'].forEach(type => {
            const current = document.querySelector(`[data-report-export="${type}"]`);
            const incoming = page.querySelector(`[data-report-export="${type}"]`);
            if (current && incoming) current.href = incoming.href;
        });

        syncDateBounds(document.querySelector('[data-report-filter-form]'));
    };

    const updateReport = async (url, { historyMode = 'push', restoreFocus = true } = {}) => {
        const currentResults = document.querySelector('[data-report-results]');
        const periodPanel = document.querySelector('.report-period-panel');
        if (!currentResults || !periodPanel) return;

        request?.abort();
        request = new AbortController();
        const version = ++requestVersion;
        currentResults.setAttribute('aria-busy', 'true');
        periodPanel.classList.add('is-filtering');

        try {
            const response = await scope.fetch(url, {
                credentials: 'same-origin',
                headers: { Accept: 'text/html', 'X-Requested-With': 'XMLHttpRequest' },
                signal: request.signal,
            });
            if (!response.ok || !response.headers.get('content-type')?.includes('text/html')) {
                throw new Error('The sales report could not be updated.');
            }

            const page = new DOMParser().parseFromString(await response.text(), 'text/html');
            const incomingResults = page.querySelector('[data-report-results]');
            if (!incomingResults || version !== requestVersion) return;

            currentResults.replaceWith(incomingResults);
            replacePeriodControls(page);

            const destination = new URL(response.url);
            const relativeUrl = `${destination.pathname}${destination.search}`;
            if (historyMode === 'push') history.pushState({ salesReport: true }, '', relativeUrl);
            if (historyMode === 'replace') history.replaceState({ salesReport: true }, '', relativeUrl);

            if (restoreFocus) {
                document.querySelector('.report-period-chip[aria-current="page"]')?.focus({ preventScroll: true });
            }
        } catch (error) {
            if (error.name !== 'AbortError' && version === requestVersion) {
                currentResults.removeAttribute('aria-busy');
                window.vmcToast?.('The sales report could not be updated. Please try again.', 'error');
            }
        } finally {
            if (version === requestVersion) {
                document.querySelector('.report-period-panel')?.classList.remove('is-filtering');
                request = null;
            }
        }
    };

    scope.listen(document, 'click', event => {
        if (event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
        const link = event.target.closest('[data-report-period-link], [data-report-results] .rules-pagination a');
        if (!link) return;
        event.preventDefault();
        if (link.matches('[aria-current="page"]')) return;
        updateReport(link.href, { restoreFocus: link.matches('[data-report-period-link]') });
    });

    scope.listen(document, 'submit', event => {
        const form = event.target.closest('[data-report-filter-form]');
        if (!form) return;
        event.preventDefault();
        const url = new URL(form.action, window.location.href);
        new FormData(form).forEach((value, key) => {
            if (String(value).trim() !== '') url.searchParams.set(key, value);
        });
        updateReport(url.href);
    });

    scope.listen(document, 'input', event => {
        if (event.target.matches('[data-report-filter-form] input[type="date"]')) {
            syncDateBounds(event.target.form);
        }
    });

    scope.listen(window, 'popstate', () => updateReport(window.location.href, {
        historyMode: 'none',
        restoreFocus: false,
    }));

    syncDateBounds(document.querySelector('[data-report-filter-form]'));

    scope.dispose(() => request?.abort());
});
