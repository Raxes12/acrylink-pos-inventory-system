@extends('layouts.app')
@section('title', 'Dashboard')
@section('page-label', 'Dashboard')

@section('content')
@php
    $canViewBusinessMetrics = !auth()->user()->isCashier();
    $salesTrendClass = is_null($periodChange) ? 'is-neutral' : ($periodChange >= 0 ? 'text-green' : 'text-danger');
    $salesTrendLabel = is_null($periodChange)
        ? 'No prior-period sales for comparison'
        : (($periodChange >= 0 ? '+' : '') . number_format($periodChange, 1) . '% ' . $periodComparisonLabel);
@endphp

<x-page-header
    title="Dashboard"
    subtitle="{{ $canViewBusinessMetrics ? 'Sales, stock health, and operational activity at a glance.' : 'Your sales workspace, recent receipts, and stock activity.' }}"
>
    <x-slot:actions>
        @if($canViewBusinessMetrics)
        <a href="{{ route('reports.index') }}" class="btn btn-secondary">
            <x-icon name="sales-report" />
            Sales Report
        </a>
        @endif
        <a href="{{ route('pos.index') }}" class="btn btn-primary">
            <x-icon name="pos" />
            Start Sale
        </a>
    </x-slot:actions>
</x-page-header>

<div data-dashboard-performance-scope>
@if($canViewBusinessMetrics)
<div class="db-section-heading db-section-heading--overview">
    <div>
        <span class="db-section-kicker">Performance</span>
        <h2>Business Overview</h2>
        <span class="db-section-note">{{ $salesPeriodLabel }} &middot; {{ $salesPeriodDateRange }}</span>
    </div>
    <nav class="db-sales-periods db-dashboard-slicer" aria-label="Dashboard performance period">
        @foreach(['today' => 'Today', 'weekly' => 'Weekly', 'monthly' => 'Monthly', 'quarterly' => 'Quarterly'] as $period => $label)
        <a href="{{ route('dashboard', ['sales_period' => $period]) }}" class="db-sales-period {{ $salesPeriod === $period ? 'is-active' : '' }}" data-sales-period="{{ $period }}" @if($salesPeriod === $period) aria-current="page" @endif>{{ $label }}</a>
        @endforeach
    </nav>
</div>
<section class="db-command-grid" aria-label="{{ $salesPeriodLabel }} business summary">
    <a href="{{ route('pos.transactions', ['date_from' => $salesPeriodStart->toDateString(), 'date_to' => today()->toDateString()]) }}" class="db-command-card db-command-card--sales">
        <div class="db-command-top"><span class="db-command-icon"><x-icon name="money" /></span><span>Net Sales</span><i class="bi bi-chevron-right" aria-hidden="true"></i></div>
        <strong data-count="{{ round($periodSales) }}" data-prefix="&#8369;">&#8369;{{ number_format($periodSales, 0) }}</strong>
        <div class="db-command-trend {{ $salesTrendClass }}"><i class="bi {{ is_null($periodChange) ? 'bi-dash' : ($periodChange >= 0 ? 'bi-arrow-up' : 'bi-arrow-down') }}" aria-hidden="true"></i> {{ $salesTrendLabel }}</div>
        <small>Refund-adjusted sales for {{ strtolower($salesPeriodLabel) }}</small>
    </a>
    <a href="{{ route('reports.index', ['date_from' => $salesPeriodStart->toDateString(), 'date_to' => today()->toDateString()]) }}" class="db-command-card db-command-card--collections">
        <div class="db-command-top"><span class="db-command-icon"><i class="bi bi-wallet2" aria-hidden="true"></i></span><span>Collections</span><i class="bi bi-chevron-right" aria-hidden="true"></i></div>
        <strong data-count="{{ round($periodCollections) }}" data-prefix="&#8369;">&#8369;{{ number_format($periodCollections, 0) }}</strong>
        <div class="db-command-trend">Payments received during this period</div>
        <small>Includes collections for earlier credit sales</small>
    </a>
    <a href="{{ route('pos.transactions', ['date_from' => $salesPeriodStart->toDateString(), 'date_to' => today()->toDateString()]) }}" class="db-command-card db-command-card--receivables">
        <div class="db-command-top"><span class="db-command-icon"><x-icon name="transactions" /></span><span>Completed Sales</span><i class="bi bi-chevron-right" aria-hidden="true"></i></div>
        <strong data-count="{{ $periodTransactions }}">{{ number_format($periodTransactions) }}</strong>
        <div class="db-command-trend">Non-voided transactions</div>
        <small>{{ $salesPeriodDateRange }}</small>
    </a>
    <a href="{{ route('pos.transactions', ['date_from' => $salesPeriodStart->toDateString(), 'date_to' => today()->toDateString()]) }}" class="db-command-card db-command-card--inventory">
        <div class="db-command-top"><span class="db-command-icon"><i class="bi bi-receipt" aria-hidden="true"></i></span><span>Average Sale</span><i class="bi bi-chevron-right" aria-hidden="true"></i></div>
        <strong data-count="{{ round($periodAverageSale) }}" data-prefix="&#8369;">&#8369;{{ number_format($periodAverageSale, 0) }}</strong>
        <div class="db-command-trend">Average per completed transaction</div>
        <small>Based on {{ number_format($periodTransactions) }} sale{{ $periodTransactions === 1 ? '' : 's' }}</small>
    </a>
</section>
<aside class="db-current-snapshot" aria-label="Current operational snapshot">
    <span class="db-current-snapshot-label">Current snapshot</span>
    <a href="{{ route('accounts-receivable.index') }}">
        <span>Open receivables</span>
        <strong>&#8369;{{ number_format($receivableSummary['total_outstanding'], 2) }}</strong>
        <small>{{ number_format($overdueReceivables) }} overdue</small>
    </a>
    <a href="{{ route('inventory.stock-overview', ['stock_level' => 'attention']) }}">
        <span>Inventory attention</span>
        <strong>{{ number_format($lowStock) }} variants</strong>
        <small>{{ $stockAlertSummary['critical'] }} critical &middot; {{ $stockAlertSummary['out'] }} out</small>
    </a>
</aside>
@else
<section class="db-hero db-hero--cashier" aria-labelledby="cashierWorkspaceLabel">
    <div class="db-hero-main">
        <div class="db-hero-label" id="cashierWorkspaceLabel">
            <span class="db-hero-icon"><x-icon name="pos" /></span>
            Cashier workspace
        </div>
        <div class="db-hero-total">Ready for the next sale</div>
        <p class="db-hero-copy">{{ $todayTxns }} receipt{{ $todayTxns !== 1 ? 's' : '' }} processed today.</p>
    </div>
    <div class="db-hero-actions">
        <a href="{{ route('pos.index') }}" class="btn btn-primary">Open POS Terminal</a>
    </div>
</section>
@endif

@if($canViewBusinessMetrics)
@else
<section class="db-kpis grid-2" aria-label="Cashier summary">
    <div class="card metric-card">
        <div class="metric-topline">
            <span class="metric-label">My Sales Today</span>
            <span class="metric-icon icon-txn"><x-icon name="money" /></span>
        </div>
        <div class="metric-value" data-count="{{ round($todaySales) }}" data-prefix="&#8369;">&#8369;{{ number_format($todaySales, 0) }}</div>
        <div class="metric-note">{{ $todayTxns }} receipt{{ $todayTxns !== 1 ? 's' : '' }} processed</div>
    </div>
    <div class="card metric-card {{ $lowStock > 0 ? 'metric-card--warn' : 'metric-card--ok' }}">
        <div class="metric-topline">
            <span class="metric-label">Stock Attention</span>
            <span class="metric-icon icon-low"><x-icon name="alerts" /></span>
        </div>
        <div class="metric-value {{ $lowStock > 0 ? 'metric-value--warn' : 'metric-value--ok' }}" data-count="{{ $lowStock }}">{{ $lowStock }}</div>
        <div class="metric-note">Variants at or below minimum stock</div>
    </div>
</section>
@endif

<div class="db-section-heading db-section-heading--spaced">
    <div>
        <span class="db-section-kicker">Trends</span>
        <h2>Sales and Product Performance</h2>
    </div>
</div>

<div class="grid-2 db-row">
    @if($canViewBusinessMetrics)
    <section class="card db-panel" aria-labelledby="salesTrendHeading" data-sales-performance>
        <div class="card-header">
            <span class="card-title" id="salesTrendHeading">
                <x-icon name="sales-report" />
                Sales Performance
            </span>
            <span class="db-period-label">{{ $salesPeriodLabel }}</span>
        </div>
        <p class="db-panel-caption">Net sales for {{ strtolower($salesPeriodLabel) }} &middot; {{ $salesPeriodDateRange }}@if($salesPeriod === 'quarterly') &middot; seven-day buckets; the final bucket may be partial @endif</p>
        <div class="card-body db-trend-chart">
            @if($salesChart->sum('total') > 0)
            @php
                $chartMax = max(1, (float) $salesChart->max('total'));
                $chartCount = $salesChart->count();
                $chartPoints = $salesChart->values()->map(function ($day, $index) use ($chartCount, $chartMax) {
                    $x = $chartCount === 1 ? 350 : 58 + ($index * (590 / ($chartCount - 1)));
                    $y = 178 - (((float) $day['total'] / $chartMax) * 138);
                    return ['x' => round($x, 1), 'y' => round($y, 1), 'label' => $day['label'], 'total' => (float) $day['total']];
                });
                $chartLine = $chartPoints->map(fn ($point) => $point['x'].','.$point['y'])->implode(' ');
                $chartArea = '58,184 '.$chartLine.' 648,184';
                $chartTicks = collect([1, .6667, .3333, 0])->map(fn ($ratio) => [
                    'y' => 178 - ($ratio * 138),
                    'value' => $chartMax * $ratio,
                ]);
            @endphp
            <svg class="db-sales-line" viewBox="0 0 680 225" role="img" aria-labelledby="salesChartTitle salesChartDescription">
                <title id="salesChartTitle">Net sales for {{ strtolower($salesPeriodLabel) }}</title>
                <desc id="salesChartDescription">Sales from {{ $salesPeriodDateRange }}. Use Tab to inspect each plotted value.</desc>
                <defs><linearGradient id="dashboardSalesArea" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="var(--accent)" stop-opacity=".24"/><stop offset="1" stop-color="var(--accent)" stop-opacity="0"/></linearGradient></defs>
                <g class="db-chart-grid">
                    @foreach($chartTicks as $tick)
                    <line x1="58" y1="{{ $tick['y'] }}" x2="648" y2="{{ $tick['y'] }}"/>
                    <text class="db-chart-axis-label" x="50" y="{{ $tick['y'] + 3 }}" text-anchor="end">&#8369;{{ $tick['value'] >= 1000 ? number_format($tick['value'] / 1000, 1).'k' : number_format($tick['value'], 0) }}</text>
                    @endforeach
                </g>
                <polygon class="db-chart-area" points="{{ $chartArea }}"/><polyline class="db-chart-line" points="{{ $chartLine }}"/>
                @foreach($chartPoints as $point)<circle class="db-chart-point" cx="{{ $point['x'] }}" cy="{{ $point['y'] }}" r="4" tabindex="0" data-chart-label="{{ $point['label'] }}" data-chart-value="{{ number_format($point['total'], 2, '.', '') }}" aria-label="{{ $point['label'] }}: &#8369;{{ number_format($point['total'], 2) }}"><title>{{ $point['label'] }}: &#8369;{{ number_format($point['total'], 2) }}</title></circle>@endforeach
                @php
                    $labelInterval = max(1, (int) ceil($chartCount / 6));
                @endphp
                @foreach($chartPoints as $point) @if($loop->first || $loop->last || ($loop->index % $labelInterval === 0))<text class="db-chart-label" x="{{ $point['x'] }}" y="210" text-anchor="middle">{{ $point['label'] }}</text>@endif @endforeach
            </svg>
            <div class="db-chart-tooltip" data-sales-chart-tooltip role="status" aria-live="polite" hidden></div>
            @else<x-empty-state title="No sales recorded for this period" message="The selected dates contain no completed, non-voided net sales." />@endif
        </div>
        <div class="db-sales-summary" aria-label="{{ $salesPeriodLabel }} sales summary">
            <div>
                <span>Total sales</span>
                <strong>&#8369;{{ number_format($trendSummary->revenue, 2) }}</strong>
                <small>Net sales in this period</small>
            </div>
            <div>
                <span>Average daily sales</span>
                <strong>&#8369;{{ number_format($trendSummary->revenue / $salesPeriodDays, 2) }}</strong>
                <small>Across {{ strtolower($salesPeriodLabel) }}</small>
            </div>
            <div>
                <span>Total transactions</span>
                <strong>{{ number_format($trendSummary->transaction_count) }}</strong>
                <small>{{ $trendSummary->transaction_count === 1 ? 'Completed sale' : 'Completed sales' }}</small>
            </div>
        </div>
    </section>
    @endif

    <section class="card db-panel" aria-labelledby="topItemsHeading">
        <div class="card-header">
            <span class="card-title" id="topItemsHeading">
                <x-icon name="products" />
                Top Selling Products
            </span>
            <span class="db-period-label">{{ $salesPeriodLabel }} &middot; net units by variant</span>
        </div>
        <div class="card-body dashboard-chart-body">
            @if($topProducts->isNotEmpty())
            <div id="topProductsChart" class="horizontal-chart" aria-label="Top selling products based on units sold"></div>
            @else
            <x-empty-state title="No sales data yet" message="Best-selling items will appear after sales are processed." />
            @endif
        </div>
    </section>

    @unless($canViewBusinessMetrics)
    <section class="card db-panel" aria-labelledby="cashierTransactionsHeading">
        <div class="card-header">
            <span class="card-title" id="cashierTransactionsHeading"><x-icon name="transactions" /> My Recent Transactions</span>
            <a href="{{ route('dashboard.my-transactions') }}" class="btn btn-secondary btn-sm">View all</a>
        </div>
        <div class="list-stack db-list">
            @forelse($recentTxns as $t)
            @php $dt = \Carbon\Carbon::parse($t->date->format('Y-m-d').' '.$t->time); @endphp
            <div class="txn-item">
                <div class="txn-main">
                    <span class="receipt-code">{{ $t->receipt_no }}</span>
                    <div class="txn-meta"><span>{{ $t->cashier?->name ?? 'Unassigned' }}</span><span>{{ $dt->format('M d h:i A') }}</span></div>
                </div>
                <div class="txn-side">
                    <span class="txn-total">&#8369;{{ number_format($t->netTotal(), 2) }}</span>
                    <a href="{{ route('pos.receipt', $t) }}" class="btn btn-secondary btn-sm"><x-icon name="eye" /> View</a>
                </div>
            </div>
            @empty
            <x-empty-state title="No transactions today" message="Your completed sales will appear here." />
            @endforelse
        </div>
    </section>
    @endunless
</div>

@if($canViewBusinessMetrics)
<div class="db-distribution-grid" aria-label="Sales distribution charts">
    <section class="card db-panel db-distribution-panel" aria-labelledby="categoryPerformanceHeading">
        <div class="card-header">
            <div>
                <span class="card-title" id="categoryPerformanceHeading"><x-icon name="categories" /> Category Sales Distribution</span>
                <span class="db-panel-subtitle">Allocated net sales for {{ strtolower($salesPeriodLabel) }}</span>
            </div>
            <span class="db-period-label">{{ $salesPeriodLabel }}</span>
        </div>
        <div class="card-body dashboard-chart-body db-distribution-body">
            @if($categoryPerformance->isNotEmpty())
            <div id="categoryChart" class="doughnut-chart-layout" aria-label="Sales revenue distribution by current category"></div>
            @else
            <x-empty-state title="No category sales yet" message="Category performance will appear after sales are processed." />
            @endif
        </div>
    </section>

    <section class="card db-panel db-distribution-panel" aria-labelledby="dashboardPaymentMethodsHeading" data-sales-payment-methods>
        <div class="card-header">
            <div>
                <span class="card-title" id="dashboardPaymentMethodsHeading"><i class="bi bi-credit-card-2-front" aria-hidden="true"></i> Sales by Payment Method</span>
                <span class="db-panel-subtitle">Net sales collected for {{ strtolower($salesPeriodLabel) }}</span>
            </div>
            <span class="db-period-label">{{ $salesPeriodLabel }}</span>
        </div>
        <div class="card-body dashboard-chart-body db-distribution-body">
            @if($paymentMethodPerformance->isNotEmpty())
            <div id="paymentMethodChart" class="doughnut-chart-layout" aria-label="Net sales distribution by payment method"></div>
            @else
            <x-empty-state title="No payment-method sales yet" message="Payment distribution will appear after completed sales are processed." />
            @endif
        </div>
    </section>
</div>

<div class="db-section-heading db-section-heading--spaced">
    <div>
        <span class="db-section-kicker">Stock and people</span>
        <h2>Inventory and Team</h2>
    </div>
</div>
<section class="card db-panel db-inventory-snapshot" aria-labelledby="inventorySnapshotHeading">
    <div class="card-header">
        <div>
            <span class="card-title" id="inventorySnapshotHeading"><x-icon name="products" /> Inventory Snapshot</span>
            <span class="db-panel-subtitle">Current stock quantity and valuation based on recorded cost price</span>
        </div>
        <a href="{{ route('inventory.stock-overview') }}" class="btn btn-secondary btn-sm">View inventory</a>
    </div>
    <div class="db-inventory-snapshot-grid">
        <div class="db-inventory-stat"><span class="db-inventory-stat-icon"><x-icon name="products" /></span><div><strong>{{ number_format($inventorySummary['products']) }}</strong><span>Products</span></div></div>
        <div class="db-inventory-stat"><span class="db-inventory-stat-icon"><i class="bi bi-sliders" aria-hidden="true"></i></span><div><strong>{{ number_format($inventorySummary['variants']) }}</strong><span>Variants</span></div></div>
        <div class="db-inventory-stat"><span class="db-inventory-stat-icon"><i class="bi bi-bar-chart" aria-hidden="true"></i></span><div><strong>{{ number_format($inventorySummary['quantity']) }}</strong><span>Units on hand</span></div></div>
        <div class="db-inventory-stat"><span class="db-inventory-stat-icon"><i class="bi bi-cash-stack" aria-hidden="true"></i></span><div><strong>&#8369;{{ number_format($inventorySummary['value'], 0) }}</strong><span>Value at cost</span></div></div>
    </div>
</section>
@endif

@if($canViewBusinessMetrics)
<div class="grid-2 db-row db-insight-row">
    <section class="card db-panel db-team-performance" aria-labelledby="cashierPerformanceHeading">
        <div class="card-header db-team-performance-header">
            <div><span class="card-title" id="cashierPerformanceHeading"><x-icon name="users" /> Cashier Sales Performance</span><span class="db-panel-subtitle">Sales processed at checkout</span></div>
            <div class="db-team-performance-meta"><span class="db-period-label">{{ $salesPeriodLabel }}</span><strong>{{ $cashierPerformance->count() }} cashier{{ $cashierPerformance->count() === 1 ? '' : 's' }}</strong></div>
        </div>
        <div class="card-body dashboard-chart-body db-team-performance-body">
            @if($cashierPerformance->isNotEmpty())
            <div id="cashierChart" class="horizontal-chart" aria-label="Cashier performance based on net sales processed"></div>
            @else
            <x-empty-state title="No cashier sales yet" message="Cashier performance will appear after sales are processed." />
            @endif
        </div>
    </section>

    <section class="card db-panel db-team-performance" aria-labelledby="salesAssociatePerformanceHeading">
        <div class="card-header db-team-performance-header">
            <div><span class="card-title" id="salesAssociatePerformanceHeading"><x-icon name="users" /> Sales Associate Performance</span><span class="db-panel-subtitle">Sales credited to the assisting staff member</span></div>
            <div class="db-team-performance-meta"><span class="db-period-label">{{ $salesPeriodLabel }}</span><strong>{{ $salesAssociatePerformance->count() }} associate{{ $salesAssociatePerformance->count() === 1 ? '' : 's' }}</strong></div>
        </div>
        <div class="card-body dashboard-chart-body db-team-performance-body">
            @if($salesAssociatePerformance->isNotEmpty())
            <div id="salesAssociateChart" class="horizontal-chart" aria-label="Sales associate performance based on net sales assisted"></div>
            @else
            <x-empty-state title="No sales associate activity yet" message="Performance appears after a cashier records a sales associate on a sale." />
            @endif
        </div>
    </section>
</div>
@endif

@if($canViewBusinessMetrics)
@php
    $dashboardChartData = [
        'products' => $topProducts->map(function ($product) {
            return [
                'label' => $product->product_name,
                'identifier' => $product->identifier,
                'value' => (int) $product->units_sold,
                'revenue' => (float) $product->revenue,
            ];
        })->values(),
        'categories' => $categoryPerformance->map(function ($category) {
            return [
                'label' => $category->category_name,
                'value' => (float) $category->revenue,
                'units' => (int) $category->units_sold,
            ];
        })->values(),
        'paymentMethods' => $paymentMethodPerformance->map(function ($method) {
            return [
                'label' => $method->label,
                'value' => (float) $method->revenue,
                'transactions' => (int) $method->transaction_count,
                'isUnknown' => (bool) $method->is_unknown,
            ];
        })->values(),
        'cashiers' => $cashierPerformance->map(function ($cashier) {
            return [
                'label' => $cashier->cashier_name,
                'value' => (float) $cashier->revenue,
                'transactions' => (int) $cashier->transaction_count,
            ];
        })->values(),
        'salesAssociates' => $salesAssociatePerformance->map(function ($associate) {
            return [
                'label' => $associate->sales_associate_name,
                'value' => (float) $associate->revenue,
                'transactions' => (int) $associate->transaction_count,
            ];
        })->values(),
    ];
@endphp
<script type="application/json" id="dashboardChartData">@json($dashboardChartData)</script>
@endif
</div>

@if($canViewBusinessMetrics)
<div class="db-section-heading db-section-heading--spaced">
    <div>
        <span class="db-section-kicker">Live operations</span>
        <h2>Recent Operations</h2>
        <span class="db-section-note">Latest sales, inventory attention, and security events in one place.</span>
    </div>
</div>

<div class="grid-3 db-row db-activity-grid">
    <section class="card db-panel db-operations-panel" aria-labelledby="recentTransactionsHeading">
        <div class="card-header">
            <div><span class="card-title" id="recentTransactionsHeading"><x-icon name="transactions" /> Recent Transactions</span><span class="db-panel-subtitle">Latest completed receipts</span></div>
            <a href="{{ route('pos.transactions') }}" class="btn btn-secondary btn-sm">View all <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
        </div>
        <div class="list-stack db-list db-operations-list">
            @forelse($recentTxns as $t)
            @php $dt = \Carbon\Carbon::parse($t->date->format('Y-m-d').' '.$t->time); @endphp
            <div class="txn-item">
                <div class="txn-main">
                    <span class="receipt-code">{{ $t->receipt_no }}</span>
                    <div class="txn-meta"><span>{{ $t->cashier?->name ?? 'Unassigned cashier' }}</span><span>{{ $dt->format('M d, Y · h:i A') }}</span><span class="status {{ $t->status === 'completed' ? 'status-active' : 'status-low' }}">{{ str($t->status ?? 'unknown')->replace('_', ' ')->title() }}</span></div>
                </div>
                <div class="txn-side">
                    <span class="txn-total">&#8369;{{ number_format($t->netTotal(), 2) }}</span>
                    <a href="{{ route('pos.receipt', $t) }}" class="btn btn-secondary btn-sm"><x-icon name="eye" /> View</a>
                </div>
            </div>
            @empty
            <x-empty-state title="No transactions today" message="Completed sales will appear here." />
            @endforelse
        </div>
    </section>

    <section class="card db-panel db-operations-panel" aria-labelledby="stockAlertsHeading">
        <div class="card-header">
            <div><span class="card-title" id="stockAlertsHeading"><x-icon name="alerts" /> Stock Alerts</span><span class="db-panel-subtitle">Variants needing attention first</span></div>
            <a href="{{ route('inventory.stock-overview', ['stock_level' => 'attention']) }}" class="btn btn-secondary btn-sm">View all <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
        </div>
        <div class="list-stack db-list db-operations-list">
            @forelse($lowStockItems as $variant)
            @php
                $stock = $variant->inventoryStock;
                $product = $variant->product;
                $quantity = (int) ($stock?->quantity_on_hand ?? 0);
                $minimum = (int) ($stock?->minimum_stock ?? 0);
                $critical = (int) ($stock?->critical_stock ?? 0);
                $isOut = $quantity <= 0;
                $isCrit = !$isOut && $quantity <= $critical;
                $statusLabel = $isOut ? 'Out of Stock' : ($isCrit ? 'Critical' : 'Low Stock');
                $pct = $minimum > 0 ? min(100, max(0, ($quantity / $minimum) * 100)) : 0;
            @endphp
            <a href="{{ route('inventory.show', $variant) }}" class="stock-item stock-item-link db-stock-alert {{ ($isOut || $isCrit) ? 'stock-critical' : '' }}">
                <div class="stock-top">
                    <span class="db-stock-identity"><strong><span class="stock-dot" aria-hidden="true"></span>{{ $product?->name ?? 'Deleted product' }}</strong><small title="{{ collect([$variant->variant_code, $variant->part_number, $variant->brand?->name, $variant->size, $variant->color])->filter()->join(' / ') }}">{{ collect([$variant->variant_code, $variant->part_number, $variant->brand?->name, $variant->size, $variant->color])->filter()->join(' / ') ?: 'Variant details unavailable' }}</small></span>
                    <span class="status {{ ($isOut || $isCrit) ? 'status-inactive' : 'status-low' }}">{{ $statusLabel }}</span>
                </div>
                <div class="db-stock-levels"><span>On hand <strong>{{ $quantity }} {{ $variant->unit?->code ?: 'units' }}</strong></span><span>Reorder at <strong>{{ $minimum }}</strong></span></div>
                <div class="stock-track" role="img" aria-label="{{ $quantity }} of minimum {{ $minimum }} {{ $variant->unit?->code }} in stock">
                    <div class="stock-fill" style="width:{{ $pct }}%;"></div>
                </div>
            </a>
            @empty
            <x-empty-state title="Stock levels are healthy" message="No variants are currently at or below minimum stock." />
            @endforelse
        </div>
    </section>

    <section class="card db-panel db-operations-panel" aria-labelledby="securityActivityHeading">
        <div class="card-header">
            <div><span class="card-title" id="securityActivityHeading"><x-icon name="shield" /> Security Activity</span><span class="db-panel-subtitle">Newest intrusion alerts</span></div>
            <a href="{{ route('security.events') }}" class="btn btn-secondary btn-sm">View all <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
        </div>
        <div class="db-list db-operations-list security-activity-list">
            @forelse($recentIntrusionAlerts as $alert)
            @php
                $severity = strtolower($alert->severity ?? 'medium');
                $alertStatus = strtolower($alert->alert_status ?? 'new');
                $statusClass = $alertStatus === 'resolved' ? 'status-active' : ($alertStatus === 'new' ? 'status-inactive' : 'status-low');
            @endphp
            <a href="{{ route('security.events.alerts.show', $alert) }}" class="security-activity-item">
                <span class="security-activity-marker security-activity-marker--{{ $severity }}" aria-hidden="true"></span>
                <span class="security-activity-main">
                    <strong title="{{ $alert->alert_title }}">{{ str($alert->alert_title)->replace(['_', '-'], ' ')->title() }}</strong>
                    <small>{{ ucfirst($severity) }} risk &middot; {{ $alert->created_at?->diffForHumans() ?? 'Recently' }}</small>
                </span>
                <span class="status {{ $statusClass }}">{{ ucfirst($alertStatus) }}</span>
                <i class="bi bi-chevron-right security-activity-chevron" aria-hidden="true"></i>
            </a>
            @empty
            <x-empty-state title="No recent security alerts" message="New anomaly alerts will appear here." icon="shield" />
            @endforelse
        </div>
    </section>
</div>
@endif

@endsection

@push('styles')
@vite('resources/css/views/dashboard-index-2.css')
@endpush

@push('scripts')
@vite('resources/js/views/dashboard-index-1.js')
@endpush
