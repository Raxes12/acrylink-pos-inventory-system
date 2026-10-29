@extends('layouts.app')
@section('title', 'Dashboard')
@section('page-label', 'Dashboard')

@section('content')
@php
    $canViewBusinessMetrics = !auth()->user()->isCashier();
    $salesTrendClass = $salesChange >= 0 ? 'text-green' : 'text-danger';
    $salesTrendLabel = ($salesChange >= 0 ? '+' : '') . number_format($salesChange, 1) . '% vs yesterday';
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

@if($canViewBusinessMetrics)
<section class="db-command-grid" aria-label="Today's business summary">
    <a href="{{ route('pos.transactions', ['date_from' => today()->toDateString(), 'date_to' => today()->toDateString()]) }}" class="db-command-card db-command-card--sales">
        <div class="db-command-top"><span class="db-command-icon"><x-icon name="money" /></span><span>Today's Sales</span><i class="bi bi-chevron-right" aria-hidden="true"></i></div>
        <strong data-count="{{ round($todaySales) }}" data-prefix="&#8369;">&#8369;{{ number_format($todaySales, 0) }}</strong>
        <div class="db-command-trend {{ $salesTrendClass }}"><i class="bi {{ $salesChange >= 0 ? 'bi-arrow-up' : 'bi-arrow-down' }}" aria-hidden="true"></i> {{ $salesTrendLabel }}</div>
        <small>{{ number_format($todayTxns) }} transaction{{ $todayTxns === 1 ? '' : 's' }} &middot; &#8369;{{ number_format($avgSale, 0) }} average</small>
    </a>
    <a href="{{ route('reports.index', ['range' => 'today']) }}" class="db-command-card db-command-card--collections">
        <div class="db-command-top"><span class="db-command-icon"><i class="bi bi-wallet2" aria-hidden="true"></i></span><span>Collections</span><i class="bi bi-chevron-right" aria-hidden="true"></i></div>
        <strong data-count="{{ round($todayFinancials['today_collections']) }}" data-prefix="&#8369;">&#8369;{{ number_format($todayFinancials['today_collections'], 0) }}</strong>
        <div class="db-command-trend">{{ $todaySales > 0 ? number_format(($todayFinancials['today_collections'] / $todaySales) * 100, 1).'%' : '0%' }} of today's sales</div>
        <small>Payments received today</small>
    </a>
    <a href="{{ route('accounts-receivable.index') }}" class="db-command-card db-command-card--receivables">
        <div class="db-command-top"><span class="db-command-icon"><i class="bi bi-people" aria-hidden="true"></i></span><span>Open Receivables</span><i class="bi bi-chevron-right" aria-hidden="true"></i></div>
        <strong data-count="{{ round($receivableSummary['total_outstanding']) }}" data-prefix="&#8369;">&#8369;{{ number_format($receivableSummary['total_outstanding'], 0) }}</strong>
        <div class="db-command-trend">{{ number_format($partialReceivables) }} partially paid &middot; {{ number_format($overdueReceivables) }} overdue</div>
        <small>{{ number_format($receivableSummary['customers_with_open_credit']) }} customer account{{ $receivableSummary['customers_with_open_credit'] === 1 ? '' : 's' }}</small>
    </a>
    <a href="{{ route('inventory.stock-overview', ['stock_level' => 'attention']) }}" class="db-command-card db-command-card--inventory">
        <div class="db-command-top"><span class="db-command-icon"><x-icon name="alerts" /></span><span>Inventory Alerts</span><i class="bi bi-chevron-right" aria-hidden="true"></i></div>
        <strong data-count="{{ $lowStock }}">{{ number_format($lowStock) }}</strong>
        <div class="db-command-trend">{{ number_format(max(0, $lowStock - $outOfStock)) }} low stock &middot; {{ number_format($outOfStock) }} out of stock</div>
        <small>Items requiring attention</small>
    </a>
</section>
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

<div class="db-section-heading {{ $canViewBusinessMetrics ? 'db-section-heading--overview' : '' }}">
    <div>
        <span class="db-section-kicker">Performance</span>
        <h2>Business overview</h2>
    </div>
    <span class="db-section-note">Updated from current sales and inventory records</span>
</div>

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
        <h2>Sales and product movement</h2>
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
            <nav class="db-sales-periods" aria-label="Sales performance period">
                @foreach(['weekly' => 'Weekly', 'monthly' => 'Monthly', 'quarterly' => 'Quarterly'] as $period => $label)
                <a href="{{ route('dashboard', ['sales_period' => $period]) }}" class="db-sales-period {{ $salesPeriod === $period ? 'is-active' : '' }}" data-sales-period="{{ $period }}" @if($salesPeriod === $period) aria-current="page" @endif>{{ $label }}</a>
                @endforeach
            </nav>
        </div>
        <p class="db-panel-caption">Net sales for {{ strtolower($salesPeriodLabel) }}</p>
        <div class="card-body db-trend-chart" role="img" aria-label="Net sales for {{ strtolower($salesPeriodLabel) }}">
            @if($salesChart->isNotEmpty())
            @php
                $chartMax = max(1, (float) $salesChart->max('total'));
                $chartCount = $salesChart->count();
                $chartPoints = $salesChart->values()->map(function ($day, $index) use ($chartCount, $chartMax) {
                    $x = $chartCount === 1 ? 340 : 35 + ($index * (610 / ($chartCount - 1)));
                    $y = 178 - (((float) $day['total'] / $chartMax) * 138);
                    return ['x' => round($x, 1), 'y' => round($y, 1), 'label' => $day['label'], 'total' => (float) $day['total']];
                });
                $chartLine = $chartPoints->map(fn ($point) => $point['x'].','.$point['y'])->implode(' ');
                $chartArea = '35,184 '.$chartLine.' 645,184';
            @endphp
            <svg class="db-sales-line" viewBox="0 0 680 225" aria-hidden="true">
                <defs><linearGradient id="dashboardSalesArea" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="var(--accent)" stop-opacity=".24"/><stop offset="1" stop-color="var(--accent)" stop-opacity="0"/></linearGradient></defs>
                <g class="db-chart-grid"><line x1="35" y1="40" x2="645" y2="40"/><line x1="35" y1="86" x2="645" y2="86"/><line x1="35" y1="132" x2="645" y2="132"/><line x1="35" y1="184" x2="645" y2="184"/></g>
                <polygon class="db-chart-area" points="{{ $chartArea }}"/><polyline class="db-chart-line" points="{{ $chartLine }}"/>
                @foreach($chartPoints as $point)<circle class="db-chart-point" cx="{{ $point['x'] }}" cy="{{ $point['y'] }}" r="3.5"><title>{{ $point['label'] }}: ₱{{ number_format($point['total'], 2) }}</title></circle>@endforeach
                @php
                    $labelInterval = max(1, (int) ceil($chartCount / 6));
                @endphp
                @foreach($chartPoints as $point) @if($loop->first || $loop->last || ($loop->index % $labelInterval === 0))<text class="db-chart-label" x="{{ $point['x'] }}" y="210" text-anchor="middle">{{ $point['label'] }}</text>@endif @endforeach
            </svg>
            @else<x-empty-state title="No sales recorded" message="Sales activity for this period will appear here." />@endif
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
            <span class="db-period-label">This month &middot; units sold</span>
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
<section class="card db-panel db-inventory-snapshot" aria-labelledby="inventorySnapshotHeading">
    <div class="card-header">
        <div>
            <span class="card-title" id="inventorySnapshotHeading"><x-icon name="products" /> Inventory Snapshot</span>
            <span class="db-panel-subtitle">Current stock quantity and value</span>
        </div>
        <a href="{{ route('inventory.stock-overview') }}" class="btn btn-secondary btn-sm">View inventory</a>
    </div>
    <div class="db-inventory-snapshot-grid">
        <div class="db-inventory-stat"><span class="db-inventory-stat-icon"><x-icon name="products" /></span><div><strong>{{ number_format($inventorySummary['products']) }}</strong><span>Products</span></div></div>
        <div class="db-inventory-stat"><span class="db-inventory-stat-icon"><i class="bi bi-sliders" aria-hidden="true"></i></span><div><strong>{{ number_format($inventorySummary['variants']) }}</strong><span>Variants</span></div></div>
        <div class="db-inventory-stat"><span class="db-inventory-stat-icon"><i class="bi bi-bar-chart" aria-hidden="true"></i></span><div><strong>{{ number_format($inventorySummary['quantity']) }}</strong><span>Units on hand</span></div></div>
        <div class="db-inventory-stat"><span class="db-inventory-stat-icon"><i class="bi bi-cash-stack" aria-hidden="true"></i></span><div><strong>&#8369;{{ number_format($inventorySummary['value'], 0) }}</strong><span>Inventory value</span></div></div>
    </div>
</section>
@endif

@if($canViewBusinessMetrics)
<div class="grid-3 db-row db-insight-row">
    <section class="card db-panel" aria-labelledby="categoryPerformanceHeading">
        <div class="card-header"><span class="card-title" id="categoryPerformanceHeading"><x-icon name="categories" /> Category Sales Distribution</span><span class="db-period-label">This month &middot; revenue</span></div>
        <div class="card-body dashboard-chart-body">
            @if($categoryPerformance->isNotEmpty())
            <div id="categoryChart" class="doughnut-chart-layout" aria-label="Sales revenue distribution by category"></div>
            @else
            <x-empty-state title="No category sales yet" message="Category performance will appear after sales are processed." />
            @endif
        </div>
    </section>

    <section class="card db-panel" aria-labelledby="cashierPerformanceHeading">
        <div class="card-header"><span class="card-title" id="cashierPerformanceHeading"><x-icon name="users" /> Cashier Sales Performance</span><span class="db-period-label">This month &middot; net sales</span></div>
        <div class="card-body dashboard-chart-body">
            @if($cashierPerformance->isNotEmpty())
            <div id="cashierChart" class="horizontal-chart" aria-label="Cashier performance based on net sales processed"></div>
            @else
            <x-empty-state title="No cashier sales yet" message="Cashier performance will appear after sales are processed." />
            @endif
        </div>
    </section>

    <section class="card db-panel" aria-labelledby="salesAssociatePerformanceHeading">
        <div class="card-header"><span class="card-title" id="salesAssociatePerformanceHeading"><x-icon name="users" /> Sales Associate Performance</span><span class="db-period-label">This month &middot; net sales</span></div>
        <div class="card-body dashboard-chart-body">
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
<div class="db-section-heading db-section-heading--spaced">
    <div>
        <span class="db-section-kicker">Live operations</span>
        <h2>Recent activity and attention</h2>
    </div>
</div>

<div class="grid-3 db-row db-activity-grid">
    <section class="card db-panel" aria-labelledby="recentTransactionsHeading">
        <div class="card-header">
            <span class="card-title" id="recentTransactionsHeading"><x-icon name="transactions" /> Recent Transactions</span>
            <a href="{{ route('pos.transactions') }}" class="btn btn-secondary btn-sm">View all</a>
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
            <x-empty-state title="No transactions today" message="Completed sales will appear here." />
            @endforelse
        </div>
    </section>

    <section class="card db-panel" aria-labelledby="stockAlertsHeading">
        <div class="card-header">
            <span class="card-title" id="stockAlertsHeading"><x-icon name="alerts" /> Stock Alerts</span>
            <a href="{{ route('inventory.stock-overview', ['stock_level' => 'attention']) }}" class="btn btn-secondary btn-sm">View all</a>
        </div>
        <div class="list-stack db-list">
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
            <div class="stock-item {{ ($isOut || $isCrit) ? 'stock-critical' : '' }}">
                <div class="stock-top">
                    <span class="stock-name"><span class="stock-dot" aria-hidden="true"></span>{{ $product?->name ?? 'Deleted product' }}</span>
                    <span class="status {{ ($isOut || $isCrit) ? 'status-inactive' : 'status-low' }}">{{ $statusLabel }}</span>
                </div>
                <div class="stock-track" role="img" aria-label="{{ $quantity }} of minimum {{ $minimum }} {{ $variant->unit?->code }} in stock">
                    <div class="stock-fill" style="width:{{ max(2, $pct) }}%;"></div>
                </div>
                <div class="stock-footer">
                    <span class="stock-meta" title="{{ collect([$variant->variant_code, $variant->part_number, $variant->brand?->name, $variant->size, $variant->color])->filter()->join(' / ') }}">
                        {{ collect([$variant->variant_code, $variant->part_number, $variant->brand?->name, $variant->size, $variant->color])->filter()->join(' / ') ?: 'Variant details unavailable' }}
                        &middot; {{ $variant->supplier?->name ?? 'No supplier' }}
                    </span>
                    <span class="stock-qty">{{ $quantity }} / {{ $minimum }} {{ $variant->unit?->code }}</span>
                </div>
            </div>
            @empty
            <x-empty-state title="Stock levels are healthy" message="No variants are currently at or below minimum stock." />
            @endforelse
        </div>
    </section>

    <section class="card db-panel" aria-labelledby="securityActivityHeading">
        <div class="card-header">
            <span class="card-title" id="securityActivityHeading"><x-icon name="shield" /> Security Activity</span>
            <a href="{{ route('security.events') }}" class="btn btn-secondary btn-sm">View all</a>
        </div>
        <div class="db-list security-activity-list">
            @forelse($recentIntrusionAlerts as $alert)
            @php
                $severity = strtolower($alert->severity ?? 'medium');
                $alertStatus = strtolower($alert->alert_status ?? 'new');
                $statusClass = $alertStatus === 'resolved' ? 'status-active' : ($alertStatus === 'new' ? 'status-inactive' : 'status-low');
            @endphp
            <a href="{{ route('security.events.alerts.show', $alert) }}" class="security-activity-item">
                <span class="security-activity-marker security-activity-marker--{{ $severity }}" aria-hidden="true"></span>
                <span class="security-activity-main">
                    <strong title="{{ $alert->alert_title }}">{{ $alert->alert_title }}</strong>
                    <small>{{ ucfirst($severity) }} risk &middot; {{ $alert->created_at?->diffForHumans() ?? 'Recently' }}</small>
                </span>
                <span class="status {{ $statusClass }}">{{ ucfirst($alertStatus) }}</span>
            </a>
            @empty
            <x-empty-state title="No recent security alerts" message="New anomaly alerts will appear here." icon="shield" />
            @endforelse
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
@endsection

@push('styles')
@vite('resources/css/views/dashboard-index-2.css')
@endpush

@push('scripts')
@vite('resources/js/views/dashboard-index-1.js')
@endpush
