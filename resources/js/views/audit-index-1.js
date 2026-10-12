import { registerPage } from '../page-lifecycle.js';

registerPage(import.meta.url, scope => {
const auditForm = document.getElementById('auditLiveFilter');
const auditSearch = auditForm?.querySelector('[name="search"]');
const auditAction = auditForm?.querySelector('[name="action"]');
const auditModule = auditForm?.querySelector('[name="module"]');
const auditUser = auditForm?.querySelector('[name="user_id"]');
const auditDateFrom = auditForm?.querySelector('[name="date_from"]');
const auditDateTo = auditForm?.querySelector('[name="date_to"]');
const auditClear = auditForm?.querySelector('[data-audit-clear]');
const auditStatus = auditForm?.querySelector('[data-audit-status]');
const auditActiveCount = auditForm?.querySelector('[data-audit-active-count]');
const auditTodayMax = auditDateFrom?.max || '';
let auditResults = document.getElementById('auditResults');
let debounceTimer;
let activeRequest;
let requestVersion = 0;

function updateControls() {
    const activeFilters = [
        auditSearch?.value.trim(), auditAction?.value, auditModule?.value,
        auditUser?.value, auditDateFrom?.value, auditDateTo?.value,
    ].filter(Boolean).length;
    const hasFilters = activeFilters > 0;
    if (auditClear) auditClear.hidden = !hasFilters;
    if (auditActiveCount) auditActiveCount.textContent = activeFilters ? `${activeFilters} ${activeFilters === 1 ? 'filter' : 'filters'}` : '';
    if (auditDateTo) auditDateTo.min = auditDateFrom?.value || '';
    if (auditDateFrom) auditDateFrom.max = auditDateTo?.value || auditTodayMax;
}

async function loadAuditResults(url = null) {
    if (!auditForm || !auditResults) return;
    activeRequest?.abort();
    activeRequest = new AbortController();
    const version = ++requestVersion;
    const query = url
        ? new URL(url, window.location.href).searchParams
        : new URLSearchParams(new FormData(auditForm));
    const targetUrl = new URL(auditForm.dataset.resultsUrl, window.location.origin);
    targetUrl.search = query.toString();

    auditStatus?.classList.remove('is-error');
    auditResults.classList.add('is-loading');
    auditResults.setAttribute('aria-busy', 'true');
    if (auditStatus) auditStatus.textContent = 'Updating...';

    try {
        const response = await scope.fetch(targetUrl.toString(), {
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            signal: activeRequest.signal,
        });
        if (!response.ok) throw new Error('Unable to load audit entries.');

        const payload = await response.json();
        const fragment = document.createElement('template');
        fragment.innerHTML = payload.html || '';
        const nextResults = fragment.content.querySelector('#auditResults');
        if (!nextResults || version !== requestVersion) return;

        auditResults.replaceWith(nextResults);
        auditResults = nextResults;
        const pageUrl = new URL(auditForm.action, window.location.href);
        window.history.replaceState({}, '', `${pageUrl.pathname}${targetUrl.search}`);
        if (auditStatus) auditStatus.textContent = 'Results updated';
    } catch (error) {
        if (error.name !== 'AbortError' && auditStatus) {
            auditStatus.textContent = 'Could not update results. Please try again.';
            auditStatus.classList.add('is-error');
        }
    } finally {
        if (version === requestVersion) {
            auditResults?.classList.remove('is-loading');
            auditResults?.removeAttribute('aria-busy');
            scope.setTimeout(() => {
                if (auditStatus?.textContent === 'Results updated') auditStatus.textContent = '';
            }, 1200);
        }
    }
}

auditForm?.addEventListener('submit', event => {
    event.preventDefault();
    loadAuditResults();
});

auditSearch?.addEventListener('input', () => {
    updateControls();
    window.clearTimeout(debounceTimer);
    debounceTimer = scope.setTimeout(() => loadAuditResults(), 300);
});

auditAction?.addEventListener('change', () => {
    updateControls();
    loadAuditResults();
});

[auditModule, auditUser].forEach(control => {
    control?.addEventListener('change', () => {
        updateControls();
        loadAuditResults();
    });
});

[auditDateFrom, auditDateTo].forEach(control => {
    control?.addEventListener('change', () => {
        updateControls();
        loadAuditResults();
    });
});

auditClear?.addEventListener('click', () => {
    auditSearch.value = '';
    auditAction.value = '';
    auditModule.value = '';
    auditUser.value = '';
    auditDateFrom.value = '';
    auditDateTo.value = '';
    updateControls();
    auditSearch.focus();
    loadAuditResults(auditForm.action);
});

scope.listen(document, 'click', event => {
    const link = event.target.closest('#auditResults .rules-pagination a');
    if (!link) return;
    event.preventDefault();
    loadAuditResults(link.href);
});

scope.listen(document, 'keydown', event => {
    if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k' && auditSearch) {
        event.preventDefault();
        auditSearch.focus();
        auditSearch.select();
    }
});

updateControls();
});
