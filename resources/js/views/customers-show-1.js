import { registerPage } from '../page-lifecycle.js';

registerPage(import.meta.url, scope => {
function initializeCustomerHistoryTabs(root) {
    const tabs = [...root.querySelectorAll('[role="tab"][data-customer-history-tab]')];
    const panels = [...root.querySelectorAll('[role="tabpanel"][data-customer-history-panel]')];
    if (!tabs.length || !panels.length) return;

    const activate = (name, { focus = false, updateUrl = true } = {}) => {
        const selected = tabs.find(tab => tab.dataset.customerHistoryTab === name) || tabs[0];

        for (const tab of tabs) {
            const active = tab === selected;
            tab.setAttribute('aria-selected', String(active));
            tab.tabIndex = active ? 0 : -1;
            tab.classList.toggle('is-active', active);
        }

        for (const panel of panels) {
            panel.hidden = panel.dataset.customerHistoryPanel !== selected.dataset.customerHistoryTab;
        }

        if (focus) selected.focus();
        if (updateUrl) {
            const url = new URL(window.location.href);
            url.searchParams.set('tab', selected.dataset.customerHistoryTab);
            url.searchParams.delete('credit_page');
            url.searchParams.delete('payment_page');
            history.replaceState({ customerHistoryTab: selected.dataset.customerHistoryTab }, '', url);
        }
    };

    scope.listen(root, 'click', event => {
        const tab = event.target.closest('[data-customer-history-tab]');
        if (tab) {
            event.preventDefault();
            activate(tab.dataset.customerHistoryTab);
        }
    });

    scope.listen(root, 'keydown', event => {
        const current = event.target.closest('[data-customer-history-tab]');
        if (!current || !['ArrowLeft', 'ArrowRight', 'Home', 'End'].includes(event.key)) return;
        event.preventDefault();
        const currentIndex = tabs.indexOf(current);
        const targetIndex = event.key === 'Home'
            ? 0
            : event.key === 'End'
                ? tabs.length - 1
                : (currentIndex + (event.key === 'ArrowRight' ? 1 : -1) + tabs.length) % tabs.length;
        activate(tabs[targetIndex].dataset.customerHistoryTab, { focus: true });
    });

    scope.listen(window, 'popstate', () => {
        activate(new URL(window.location.href).searchParams.get('tab') || 'credit', { updateUrl: false });
    });
}

document.querySelectorAll('[data-customer-history]').forEach(initializeCustomerHistoryTabs);
});
