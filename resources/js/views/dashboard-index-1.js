import { registerPage } from '../page-lifecycle.js';

registerPage(import.meta.url, scope => {
const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
let salesPeriodRequest = null;
let salesPeriodVersion = 0;

async function updateSalesPerformance(url) {
    const current = document.querySelector('[data-sales-performance]');
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
        const incoming = page.querySelector('[data-sales-performance]');
        if (!incoming || version !== salesPeriodVersion) return;

        current.replaceWith(incoming);
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

document.querySelectorAll('[data-count]').forEach(el => {
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

const chartDataElement = document.getElementById('dashboardChartData');

if (chartDataElement) {
    try {
        const chartData = JSON.parse(chartDataElement.textContent || '{}');
        const peso = new Intl.NumberFormat('en-PH', {
            style: 'currency',
            currency: 'PHP',
            maximumFractionDigits: 0,
        });

        const renderHorizontalChart = (containerId, rows, options) => {
            const container = document.getElementById(containerId);
            if (!container || !Array.isArray(rows) || rows.length === 0) return;

            const maximum = Math.max(...rows.map(row => Number(row.value) || 0), 1);
            rows.forEach(row => {
                const value = Number(row.value) || 0;
                const item = document.createElement('div');
                const heading = document.createElement('div');
                const label = document.createElement('strong');
                const amount = document.createElement('span');
                const track = document.createElement('div');
                const bar = document.createElement('span');

                item.className = 'horizontal-chart-row';
                item.tabIndex = 0;
                item.setAttribute('aria-label', options.aria(row));
                item.title = options.tooltip(row);
                heading.className = 'horizontal-chart-heading';
                label.textContent = row.label;
                amount.textContent = options.value(row);
                track.className = 'horizontal-chart-track';
                bar.className = 'horizontal-chart-bar';
                bar.style.setProperty('--chart-width', `${Math.max(2, value / maximum * 100)}%`);

                heading.append(label, amount);
                track.append(bar);
                item.append(heading, track);
                container.append(item);
            });
        };

        renderHorizontalChart('topProductsChart', chartData.products, {
            value: row => `${Number(row.value).toLocaleString('en-PH')} sold`,
            aria: row => `${row.label}: ${row.value} units sold, ${peso.format(row.revenue)}`,
            tooltip: row => `${row.label}\nUnits sold: ${Number(row.value).toLocaleString('en-PH')}\nRevenue: ${peso.format(row.revenue)}`,
        });

        renderHorizontalChart('cashierChart', chartData.cashiers, {
            value: row => peso.format(row.value),
            aria: row => `${row.label}: ${peso.format(row.value)}, ${row.transactions} transactions`,
            tooltip: row => `${row.label}\nSales processed: ${peso.format(row.value)}\nTransactions: ${Number(row.transactions).toLocaleString('en-PH')}`,
        });

        const categoryContainer = document.getElementById('categoryChart');
        if (categoryContainer && Array.isArray(chartData.categories) && chartData.categories.length > 0) {
            const colors = ['#1769aa', '#168c82', '#d58b18', '#c44c58', '#667085', '#8b6bb1'];
            const total = chartData.categories.reduce((sum, row) => sum + (Number(row.value) || 0), 0);
            let position = 0;
            const segments = chartData.categories.map((row, index) => {
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
            chart.style.background = `conic-gradient(${segments.join(', ')})`;
            center.className = 'doughnut-center';
            centerLabel.textContent = 'Total sales';
            centerValue.textContent = peso.format(total);
            center.append(centerLabel, centerValue);
            legend.className = 'doughnut-legend';

            chartData.categories.forEach((row, index) => {
                const share = total > 0 ? Number(row.value) / total * 100 : 0;
                const item = document.createElement('div');
                const key = document.createElement('span');
                const swatch = document.createElement('i');
                const label = document.createElement('strong');
                const value = document.createElement('span');

                item.className = 'doughnut-legend-item';
                item.tabIndex = 0;
                item.title = `${row.label}\nSales: ${peso.format(row.value)}\nShare: ${share.toFixed(1)}%\nUnits: ${Number(row.units).toLocaleString('en-PH')}`;
                item.setAttribute('aria-label', item.title.replaceAll('\n', ', '));
                key.className = 'doughnut-legend-key';
                swatch.style.backgroundColor = colors[index % colors.length];
                label.textContent = row.label;
                value.textContent = `${share.toFixed(1)}%`;
                key.append(swatch, label);
                item.append(key, value);
                legend.append(item);
            });

            chart.append(center);
            categoryContainer.append(chart, legend);
        }

        scope.requestAnimationFrame(() => document.documentElement.classList.add('charts-ready'));
    } catch (error) {
        console.error('Dashboard chart data could not be rendered.', error);
    }
}

scope.dispose(() => {
    salesPeriodRequest?.abort();
    document.documentElement.classList.remove('charts-ready');
});

});
