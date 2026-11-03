import { registerPage } from '../page-lifecycle.js';

registerPage(import.meta.url, scope => {
const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
let salesPeriodRequest = null;
let salesPeriodVersion = 0;

async function updateSalesPerformance(url) {
    const current = document.querySelector('[data-dashboard-performance-scope]');
    if (!current) return;

    salesPeriodRequest?.abort();
    salesPeriodRequest = new AbortController();
    const version = ++salesPeriodVersion;
    current.setAttribute('aria-busy', 'true');
    current.classList.add('is-updating');

    try {
        const response = await scope.fetch(url, {
            credentials: 'same-origin',
            headers: { Accept: 'text/html' },
            signal: salesPeriodRequest.signal,
        });
        if (!response.ok || !response.headers.get('content-type')?.includes('text/html')) {
            throw new Error('Sales performance is unavailable.');
        }

        const page = new DOMParser().parseFromString(await response.text(), 'text/html');
        const incoming = page.querySelector('[data-dashboard-performance-scope]');
        if (!incoming || version !== salesPeriodVersion) return;

        current.replaceWith(incoming);
        animateCounters(incoming);
        renderDashboardCharts(incoming);
        const destination = new URL(response.url);
        history.replaceState(history.state, '', `${destination.pathname}${destination.search}`);
        incoming.querySelector('[aria-current="page"]')?.focus({ preventScroll: true });
    } catch (error) {
        if (error.name !== 'AbortError' && version === salesPeriodVersion) {
            current.removeAttribute('aria-busy');
            current.classList.remove('is-updating');
            window.vmcToast?.('Sales performance could not be updated. Please try again.', 'error');
        }
    } finally {
        if (version === salesPeriodVersion) salesPeriodRequest = null;
    }
}

scope.listen(document, 'click', event => {
    if (event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
    const link = event.target.closest('[data-sales-period]');
    if (!link) return;
    event.preventDefault();
    if (link.matches('[aria-current="page"]')) return;
    updateSalesPerformance(link.href);
});

const showSalesPointTooltip = point => {
    const chart = point.closest('[data-sales-performance]');
    const tooltip = chart?.querySelector('[data-sales-chart-tooltip]');
    if (!tooltip) return;
    const amount = new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' })
        .format(Number(point.dataset.chartValue) || 0);
    tooltip.textContent = `${point.dataset.chartLabel}: ${amount}`;
    tooltip.hidden = false;
};

const hideSalesPointTooltip = point => {
    const tooltip = point.closest('[data-sales-performance]')?.querySelector('[data-sales-chart-tooltip]');
    if (tooltip) tooltip.hidden = true;
};

scope.listen(document, 'focusin', event => {
    const point = event.target.closest?.('.db-chart-point');
    if (point) showSalesPointTooltip(point);
});
scope.listen(document, 'focusout', event => {
    const point = event.target.closest?.('.db-chart-point');
    if (point) hideSalesPointTooltip(point);
});
scope.listen(document, 'pointerover', event => {
    const point = event.target.closest?.('.db-chart-point');
    if (point) showSalesPointTooltip(point);
});
scope.listen(document, 'pointerout', event => {
    const point = event.target.closest?.('.db-chart-point');
    if (point) hideSalesPointTooltip(point);
});

function animateCounters(root = document) {
root.querySelectorAll('[data-count]').forEach(el => {
    const target = Number(el.dataset.count || 0);
    const prefix = el.dataset.prefix || '';
    const format = value => prefix + Math.round(value).toLocaleString('en-PH');

    if (reduceMotion) {
        el.textContent = format(target);
        return;
    }

    const duration = 720;
    const start = performance.now();
    const step = now => {
        const progress = Math.min(1, (now - start) / duration);
        const eased = 1 - Math.pow(1 - progress, 3);
        el.textContent = format(target * eased);
        if (progress < 1) scope.requestAnimationFrame(step);
    };
    scope.requestAnimationFrame(step);
    });
}

animateCounters();

scope.requestAnimationFrame(() => {
    document.querySelectorAll('.sales-fill[data-width]').forEach((bar, index) => {
        if (reduceMotion) {
            bar.style.width = `${bar.dataset.width}%`;
            return;
        }

        scope.setTimeout(() => {
            bar.style.width = `${bar.dataset.width}%`;
        }, index * 70);
    });

    document.querySelectorAll('.trend-bar[data-height]').forEach((bar, index) => {
        bar.parentElement?.style.setProperty('--trend-height', `${bar.dataset.height}%`);
        if (reduceMotion) {
            bar.style.height = `${bar.dataset.height}%`;
            return;
        }

        scope.setTimeout(() => {
            bar.style.height = `${bar.dataset.height}%`;
        }, index * 18);
    });
});

function renderDashboardCharts(root = document) {
    const chartDataElement = root.querySelector('#dashboardChartData');
    if (!chartDataElement) return;

    try {
        const chartData = JSON.parse(chartDataElement.textContent || '{}');
        const peso = new Intl.NumberFormat('en-PH', {
            style: 'currency',
            currency: 'PHP',
            maximumFractionDigits: 0,
        });

        const renderHorizontalChart = (containerId, rows, options) => {
            const container = root.querySelector(`#${containerId}`);
            if (!container || !Array.isArray(rows) || rows.length === 0) return;

            const maximum = Math.max(...rows.map(row => Number(row.value) || 0), 1);
            rows.forEach(row => {
                const value = Number(row.value) || 0;
                const item = document.createElement('div');
                const heading = document.createElement('div');
                const labelGroup = document.createElement('div');
                const label = document.createElement('strong');
                const meta = document.createElement('small');
                const amount = document.createElement('span');
                const track = document.createElement('div');
                const bar = document.createElement('span');

                item.className = 'horizontal-chart-row';
                item.tabIndex = 0;
                item.setAttribute('aria-label', options.aria(row));
                item.title = options.tooltip(row);
                heading.className = 'horizontal-chart-heading';
                labelGroup.className = 'horizontal-chart-label';
                label.textContent = row.label;
                if (options.meta) {
                    meta.textContent = options.meta(row);
                    labelGroup.append(label, meta);
                } else {
                    labelGroup.append(label);
                }
                amount.textContent = options.value(row);
                track.className = 'horizontal-chart-track';
                bar.className = 'horizontal-chart-bar';
                bar.style.setProperty('--chart-width', `${Math.max(2, value / maximum * 100)}%`);

                heading.append(labelGroup, amount);
                track.append(bar);
                item.append(heading, track);
                container.append(item);
            });
        };

        renderHorizontalChart('topProductsChart', chartData.products, {
            value: row => `${Number(row.value).toLocaleString('en-PH')} sold`,
            meta: row => row.identifier || 'Identifier unavailable',
            aria: row => `${row.label}, ${row.identifier || 'identifier unavailable'}: ${row.value} net units sold, ${peso.format(row.revenue)} allocated net sales`,
            tooltip: row => `${row.label}\n${row.identifier || 'Identifier unavailable'}\nNet units sold: ${Number(row.value).toLocaleString('en-PH')}\nAllocated net sales: ${peso.format(row.revenue)}`,
        });

        renderHorizontalChart('cashierChart', chartData.cashiers, {
            value: row => peso.format(row.value),
            meta: row => `${Number(row.transactions).toLocaleString('en-PH')} transaction${Number(row.transactions) === 1 ? '' : 's'} processed`,
            aria: row => `${row.label}: ${peso.format(row.value)}, ${row.transactions} transactions`,
            tooltip: row => `${row.label}\nSales processed: ${peso.format(row.value)}\nTransactions: ${Number(row.transactions).toLocaleString('en-PH')}`,
        });
        renderHorizontalChart('salesAssociateChart', chartData.salesAssociates, {
            value: row => peso.format(row.value),
            meta: row => `${Number(row.transactions).toLocaleString('en-PH')} assisted transaction${Number(row.transactions) === 1 ? '' : 's'}`,
            aria: row => `${row.label}: ${peso.format(row.value)} in assisted sales across ${row.transactions} transactions`,
            tooltip: row => `${row.label}\nSales assisted: ${peso.format(row.value)}\nTransactions: ${Number(row.transactions).toLocaleString('en-PH')}`,
        });

        const renderDoughnutChart = (containerId, rows, options) => {
            const container = root.querySelector(`#${containerId}`);
            if (!container || !Array.isArray(rows) || rows.length === 0) return;

            const colors = ['#1769aa', '#168c82', '#d58b18', '#c44c58', '#667085', '#8b6bb1', '#0e9f6e', '#7c3aed'];
            const total = rows.reduce((sum, row) => sum + (Number(row.value) || 0), 0);
            let position = 0;
            const segments = rows.map((row, index) => {
                const start = position;
                position += total > 0 ? Number(row.value) / total * 100 : 0;
                return `${colors[index % colors.length]} ${start}% ${position}%`;
            });
            const chart = document.createElement('div');
            const center = document.createElement('div');
            const centerLabel = document.createElement('span');
            const centerValue = document.createElement('strong');
            const legend = document.createElement('div');

            chart.className = 'doughnut-chart';
            chart.tabIndex = 0;
            chart.setAttribute('role', 'img');
            chart.setAttribute('aria-label', `${options.chartLabel}. Total ${peso.format(total)}.`);
            chart.style.background = total > 0
                ? `conic-gradient(${segments.join(', ')})`
                : 'var(--soft)';
            center.className = 'doughnut-center';
            centerLabel.textContent = options.centerLabel;
            centerValue.textContent = peso.format(total);
            center.append(centerLabel, centerValue);
            legend.className = 'doughnut-legend';

            rows.forEach((row, index) => {
                const share = total > 0 ? Number(row.value) / total * 100 : 0;
                const item = document.createElement('div');
                const key = document.createElement('span');
                const swatch = document.createElement('i');
                const copy = document.createElement('span');
                const label = document.createElement('strong');
                const meta = document.createElement('small');
                const summary = document.createElement('span');
                const percentage = document.createElement('strong');
                const amount = document.createElement('small');

                item.className = 'doughnut-legend-item';
                item.tabIndex = 0;
                item.title = options.tooltip(row, share);
                item.setAttribute('aria-label', item.title.replaceAll('\n', ', '));
                key.className = 'doughnut-legend-key';
                swatch.style.backgroundColor = colors[index % colors.length];
                label.textContent = row.label;
                meta.textContent = options.meta(row);
                copy.className = 'doughnut-legend-copy';
                copy.append(label, meta);
                percentage.textContent = `${share.toFixed(1)}%`;
                amount.textContent = peso.format(row.value);
                summary.className = 'doughnut-legend-summary';
                summary.append(percentage, amount);
                key.append(swatch, copy);
                item.append(key, summary);
                legend.append(item);
            });

            chart.append(center);
            container.append(chart, legend);
        };

        renderDoughnutChart('categoryChart', chartData.categories, {
            chartLabel: 'Category sales distribution',
            centerLabel: 'Total sales',
            meta: row => `${Number(row.units).toLocaleString('en-PH')} unit${Number(row.units) === 1 ? '' : 's'} sold`,
            tooltip: (row, share) => `${row.label}\nSales: ${peso.format(row.value)}\nShare: ${share.toFixed(1)}%\nUnits: ${Number(row.units).toLocaleString('en-PH')}`,
        });

        renderDoughnutChart('paymentMethodChart', chartData.paymentMethods, {
            chartLabel: 'Sales distribution by payment method',
            centerLabel: 'Net sales',
            meta: row => `${Number(row.transactions).toLocaleString('en-PH')} transaction${Number(row.transactions) === 1 ? '' : 's'}`,
            tooltip: (row, share) => `${row.label}\nNet sales: ${peso.format(row.value)}\nShare: ${share.toFixed(1)}%\nTransactions: ${Number(row.transactions).toLocaleString('en-PH')}${row.isUnknown ? '\nLegacy payment method needs review' : ''}`,
        });

        scope.requestAnimationFrame(() => document.documentElement.classList.add('charts-ready'));
    } catch (error) {
        console.error('Dashboard chart data could not be rendered.', error);
    }
}

renderDashboardCharts();

scope.dispose(() => {
    salesPeriodRequest?.abort();
    document.documentElement.classList.remove('charts-ready');
});

});
