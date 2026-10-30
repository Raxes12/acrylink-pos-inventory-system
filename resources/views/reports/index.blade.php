@extends('layouts.app')
@section('title', 'Sales Reports')
@section('page-label', 'Sales Reports')

@section('content')
<div class="reports-page">
<x-page-header title="Sales Reports" subtitle="Track revenue, collections, cashier performance, and transaction activity.">
    <x-slot:actions>
        <a href="{{ $exportUrl }}" class="btn report-export report-export--csv"><i class="bi bi-file-earmark-spreadsheet" aria-hidden="true"></i> Export CSV</a>
        <a href="{{ $exportExcelUrl }}" class="btn report-export report-export--excel"><i class="bi bi-file-earmark-excel" aria-hidden="true"></i> Export Excel</a>
        <a href="{{ $exportPdfUrl }}" class="btn report-export report-export--pdf"><i class="bi bi-file-earmark-pdf" aria-hidden="true"></i> Export PDF</a>
    </x-slot:actions>
</x-page-header>

<section class="card report-period-panel" aria-labelledby="report-period-heading">
    <div class="report-period-top">
        <div class="report-period-copy">
            <span class="report-period-eyebrow" id="report-period-heading">Reporting period</span>
            <span class="report-period-value"><strong>{{ $reportLabel }}</strong>@if($range === 'custom')<span class="report-period-state">Custom</span>@endif</span>
        </div>
        <nav class="report-period-presets" aria-label="Quick reporting periods">
            @foreach(['today' => 'Today', 'week' => 'This Week', 'month' => 'This Month', 'year' => 'This Year', 'all' => 'All Time'] as $key => $label)
                <a href="{{ route('reports.index', ['range' => $key]) }}" class="report-period-chip {{ $range === $key ? 'is-active' : '' }}" @if($range === $key) aria-current="page" @endif>{{ $label }}</a>
            @endforeach
        </nav>
    </div>
    <form id="salesReportDateFilter" method="GET" action="{{ route('reports.index') }}" class="report-date-filter">
        <input type="hidden" name="range" value="custom">
        <div class="report-date-filter-intro">
            <span class="report-date-filter-icon" aria-hidden="true"><i class="bi bi-calendar-range"></i></span>
            <span><strong>Custom date range</strong><small>Select one date or an exact start and end date.</small></span>
        </div>
        <div class="report-date-fields">
            <label><span>From</span><input type="date" name="date_from" value="{{ $dateFrom }}" @if($dateTo) max="{{ $dateTo }}" @endif aria-label="Report start date"></label>
            <label><span>To</span><input type="date" name="date_to" value="{{ $dateTo }}" @if($dateFrom) min="{{ $dateFrom }}" @endif aria-label="Report end date"></label>
        </div>
        <div class="report-date-actions">
            <button class="btn btn-primary btn-sm" type="submit"><i class="bi bi-funnel" aria-hidden="true"></i> Apply dates</button>
            @if($range === 'custom')<a href="{{ route('reports.index') }}" class="btn btn-secondary btn-sm">Reset</a>@endif
        </div>
    </form>
</section>

@if($errors->has('export'))<div class="alert alert-danger report-alert">{{ $errors->first('export') }}</div>@endif

<section class="sales-kpi-grid" aria-label="Sales summary for {{ $reportLabel }}">
    <article class="card sales-kpi sales-kpi--hero"><div class="sales-kpi-top"><span class="sales-kpi-label">Net Sales</span><span class="sales-kpi-icon"><x-icon name="money" /></span></div><div class="sales-kpi-value">&#8369;{{ number_format($totalSales, 2) }}</div><div class="sales-kpi-note">After discounts and &#8369;{{ number_format($totalRefunds, 2) }} in refunds</div><svg class="sales-kpi-spark" viewBox="0 0 92 46" aria-hidden="true"><polyline points="2,40 16,29 29,34 43,17 57,24 72,9 90,16" /></svg></article>
    <article class="card sales-kpi sales-kpi--revenue"><div class="sales-kpi-top"><span class="sales-kpi-label">Gross Revenue</span><span class="sales-kpi-icon"><x-icon name="sales-report" /></span></div><div class="sales-kpi-value">&#8369;{{ number_format($grossRevenue, 2) }}</div><div class="sales-kpi-note">Before discounts, after returned items</div></article>
    <article class="card sales-kpi sales-kpi--collections">
        <div class="sales-kpi-top"><span class="sales-kpi-label">Collections</span><span class="sales-kpi-icon"><i class="bi bi-wallet2" aria-hidden="true"></i></span></div>
        <div class="sales-kpi-value">&#8369;{{ number_format($totalCollections, 2) }}</div>
        <div class="sales-kpi-note">Actual payments received during {{ strtolower($reportLabel) }}</div>
        <div class="collections-breakdown" aria-label="Collections breakdown">
            <div><span><i class="collections-dot collections-dot--sales"></i>From selected-period sales</span><strong>&#8369;{{ number_format($salesPeriodCollections, 2) }}</strong></div>
            <div><span><i class="collections-dot collections-dot--credit"></i>From earlier credit sales</span><strong>&#8369;{{ number_format($priorCreditCollections, 2) }}</strong></div>
        </div>
        <details class="collections-explanation">
            <summary><i class="bi bi-info-circle" aria-hidden="true"></i> Why can this be higher than Net Sales?</summary>
            <p>Net Sales follows the sale date. Collections follows the payment date, so it also includes customer payments received now for credit sales recorded in an earlier period.</p>
        </details>
    </article>
    <article class="card sales-kpi sales-kpi--credit"><div class="sales-kpi-top"><span class="sales-kpi-label">Credit Sales</span><span class="sales-kpi-icon"><i class="bi bi-receipt" aria-hidden="true"></i></span></div><div class="sales-kpi-value">&#8369;{{ number_format($totalCreditSales, 2) }}</div><div class="sales-kpi-note">Net sales charged to customer accounts</div></article>
    <article class="card sales-kpi sales-kpi--discount"><div class="sales-kpi-top"><span class="sales-kpi-label">Total Discounts</span><span class="sales-kpi-icon"><x-icon name="percent" /></span></div><div class="sales-kpi-value">&#8369;{{ number_format($totalDiscount, 2) }}</div><div class="sales-kpi-note">Discounts remaining after refunds</div></article>
    <article class="card sales-kpi sales-kpi--average"><div class="sales-kpi-top"><span class="sales-kpi-label">Average Transaction</span><span class="sales-kpi-icon"><x-icon name="average" /></span></div><div class="sales-kpi-value">&#8369;{{ number_format($avgSale, 2) }}</div><div class="sales-kpi-note">Average net value per transaction</div></article>
</section>

<section class="report-analytics-grid">
    <article class="card report-analytics-card">
        <div class="card-header"><div><span class="card-title"><i class="bi bi-graph-up-arrow" aria-hidden="true"></i> Sales Trend</span><span class="report-card-caption">Daily sales for the selected period</span></div><span class="report-legend"><i></i> Net Sales</span></div>
        <div class="card-body sales-chart-wrap">
            @if($byDate->isNotEmpty())
                @php
                    $trendEntries = $byDate->map(fn ($amount, $date) => ['date' => $date, 'amount' => (float) $amount])->values();
                    $trendMax = max(1, (float) $trendEntries->max('amount'));
                    $trendCount = $trendEntries->count();
                    $trendPoints = $trendEntries->map(function ($entry, $index) use ($trendCount, $trendMax) {
                        $x = $trendCount === 1 ? 310 : 28 + ($index * (564 / ($trendCount - 1)));
                        $y = 170 - (($entry['amount'] / $trendMax) * 132);
                        return ['x' => round($x, 1), 'y' => round($y, 1), 'date' => $entry['date'], 'amount' => $entry['amount']];
                    });
                    $polyline = $trendPoints->map(fn ($point) => $point['x'].','.$point['y'])->implode(' ');
                    $area = '28,176 '.$polyline.' 592,176';
                @endphp
                <svg class="sales-line-chart" viewBox="0 0 620 220" role="img" aria-label="Net sales trend for {{ $reportLabel }}">
                    <defs><linearGradient id="salesTrendArea" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="var(--accent)" stop-opacity=".22"/><stop offset="1" stop-color="var(--accent)" stop-opacity="0"/></linearGradient></defs>
                    <g class="chart-grid"><line x1="28" y1="38" x2="592" y2="38"/><line x1="28" y1="82" x2="592" y2="82"/><line x1="28" y1="126" x2="592" y2="126"/><line x1="28" y1="176" x2="592" y2="176"/></g>
                    <polygon class="chart-area" points="{{ $area }}"/><polyline class="chart-line" points="{{ $polyline }}"/>
                    @foreach($trendPoints as $point)<circle class="chart-point" cx="{{ $point['x'] }}" cy="{{ $point['y'] }}" r="4"><title>{{ \Carbon\Carbon::parse($point['date'])->format('M d, Y') }}: ₱{{ number_format($point['amount'], 2) }}</title></circle>@endforeach
                    @foreach($trendPoints as $point) @if($loop->first || $loop->last || $loop->index % max(1, (int) ceil($trendCount / 5)) === 0)<text class="chart-label" x="{{ $point['x'] }}" y="205" text-anchor="middle">{{ \Carbon\Carbon::parse($point['date'])->format('M d') }}</text>@endif @endforeach
                </svg>
            @else<div class="report-empty"><i class="bi bi-graph-up" aria-hidden="true"></i><strong>No sales data</strong><span>No completed sales were found for this period.</span></div>@endif
        </div>
    </article>
    <article class="card report-analytics-card">
        <div class="card-header"><div><span class="card-title">Sales by Cashier</span><span class="report-card-caption">Net sales contribution by team member</span></div><span class="report-count">{{ $byCashier->count() }} cashiers</span></div>
        <div class="card-body report-bars">@php $maxCash = $byCashier->max() ?: 1; @endphp
            @forelse($byCashier as $name => $amt)<div class="cashier-row"><span class="cashier-avatar" aria-hidden="true">{{ strtoupper(substr($name, 0, 1)) }}</span><div class="cashier-performance"><div class="cashier-heading"><strong title="{{ $name }}">{{ $name }}</strong><span>&#8369;{{ number_format($amt, 2) }}</span></div><div class="sales-track"><div class="sales-fill sales-fill--cashier" style="width:{{ max(2, ($amt / $maxCash) * 100) }}%"></div></div></div></div>
            @empty<div class="report-empty"><i class="bi bi-people" aria-hidden="true"></i><strong>No cashier activity</strong><span>No cashier sales were found for this period.</span></div>@endforelse
        </div>
    </article>
    <article class="card report-analytics-card">
        <div class="card-header"><div><span class="card-title">Sales by Associate</span><span class="report-card-caption">Net sales attributed to the staff who assisted each customer</span></div><span class="report-count">{{ $bySalesAssociate->count() }} associates</span></div>
        <div class="card-body report-bars">@php $maxAssociate = $bySalesAssociate->max() ?: 1; @endphp
            @forelse($bySalesAssociate as $name => $amt)<div class="cashier-row"><span class="cashier-avatar" aria-hidden="true">{{ strtoupper(substr($name, 0, 1)) }}</span><div class="cashier-performance"><div class="cashier-heading"><strong title="{{ $name }}">{{ $name }}</strong><span>&#8369;{{ number_format($amt, 2) }}</span></div><div class="sales-track"><div class="sales-fill sales-fill--cashier" style="width:{{ max(2, ($amt / $maxAssociate) * 100) }}%"></div></div></div></div>
            @empty<div class="report-empty"><i class="bi bi-person-badge" aria-hidden="true"></i><strong>No sales associate activity</strong><span>Associate performance appears after a cashier records who assisted a sale.</span></div>@endforelse
        </div>
    </article>
</section>

<div class="report-lower-grid">
<section class="card report-table-card report-top-items">
    <div class="card-header"><div><span class="card-title"><i class="bi bi-trophy" aria-hidden="true"></i> Top Selling Items</span><span class="report-card-caption">Best-performing products during {{ strtolower($reportLabel) }}, ranked by units sold</span></div><span class="tag tag--accent">Top {{ $topProducts->count() }}</span></div>
    @php $topRevenue = max(1, (float) $topProducts->sum('revenue')); @endphp
    <div class="table-wrap" tabindex="0" role="region" aria-label="Top selling items table"><table><thead><tr><th>#</th><th>Item</th><th class="text-right">Units Sold</th><th class="text-right">Revenue</th><th class="text-right">Share</th></tr></thead><tbody>
        @forelse($topProducts as $product)<tr><td><span class="report-rank {{ $loop->iteration <= 3 ? 'report-rank--top' : '' }}">{{ $loop->iteration }}</span></td><td><div class="report-item-name"><strong>{{ $product->product_name }}</strong><span>{{ number_format(($product->revenue / $topRevenue) * 100, 1) }}% of top-item revenue</span></div></td><td class="text-right"><span class="report-units">{{ number_format($product->units) }}</span></td><td class="text-right report-money">&#8369;{{ number_format($product->revenue, 2) }}</td><td class="text-right"><div class="report-share-cell"><strong>{{ number_format(($product->revenue / $topRevenue) * 100, 1) }}%</strong><span><i style="width:{{ ($product->revenue / $topRevenue) * 100 }}%"></i></span></div></td></tr>
        @empty<tr><td colspan="5"><div class="report-empty report-empty--table"><strong>No sold items</strong><span>Items sold during this period will appear here.</span></div></td></tr>@endforelse
    </tbody></table></div>
</section>

<section class="card report-table-card" data-live-results>
    <div class="card-header"><div><span class="card-title"><i class="bi bi-receipt-cutoff" aria-hidden="true"></i> Transaction History</span><span class="report-card-caption">Receipt activity included in {{ $reportLabel }}</span></div><span class="tag tag--accent">{{ $transactions->total() }} transactions</span></div>
    <div class="table-wrap" tabindex="0" role="region" aria-label="Sales transaction history"><table><thead><tr><th>Receipt</th><th>Date &amp; Time</th><th>Staff</th><th>Payment</th><th class="text-right">Discount</th><th class="text-right">Refunded</th><th class="text-right">Net Total</th><th>Action</th></tr></thead><tbody>
        @forelse($transactions as $t)
            @php $dt = \Carbon\Carbon::parse($t->date->format('Y-m-d').' '.$t->time); $refundedAmount = $t->totalRefunded(); @endphp
            <tr><td><a class="report-receipt" href="{{ route('pos.receipt', $t) }}">{{ $t->receipt_no }}</a></td><td><div class="report-date"><strong>{{ $dt->format('M d, Y') }}</strong><span>{{ $dt->format('h:i A') }}</span></div></td><td><div class="report-staff"><strong>{{ $t->cashier?->name ?? 'Unknown' }}</strong>@if($t->sales_associate)<small>Associate: {{ $t->sales_associate }}</small>@endif</div></td><td><span class="status status-active">{{ \App\Models\Transaction::paymentMethodLabel($t->payment_method) }}</span></td><td class="text-right report-discount">@if($t->netDiscount() > 0)&#8369;{{ number_format($t->netDiscount(), 2) }}@else&mdash;@endif</td><td class="text-right {{ $refundedAmount > 0 ? 'report-refund' : 'text-muted' }}">@if($refundedAmount > 0)&minus; &#8369;{{ number_format($refundedAmount, 2) }}@else&mdash;@endif</td><td class="text-right report-money">&#8369;{{ number_format($t->netTotal(), 2) }}</td><td><a href="{{ route('pos.receipt', $t) }}" class="btn btn-secondary btn-sm report-view"><i class="bi bi-eye" aria-hidden="true"></i> View</a></td></tr>
        @empty<tr><td colspan="8"><div class="report-empty report-empty--table"><strong>No transactions found</strong><span>Choose another reporting period to view sales activity.</span></div></td></tr>@endforelse
    </tbody></table></div>
    <div class="view-blades-footer"><div class="pagination-summary">Showing {{ $transactions->firstItem() ?? 0 }}-{{ $transactions->lastItem() ?? 0 }} of {{ $transactions->total() }} transactions</div>
        @if($transactions->hasPages())
            @php $currentPage = $transactions->currentPage(); $lastPage = $transactions->lastPage(); $pages = collect([1, $currentPage - 1, $currentPage, $currentPage + 1, $lastPage])->filter(fn ($page) => $page >= 1 && $page <= $lastPage)->unique()->sort()->values(); $previousPage = null; @endphp
            <nav class="rules-pagination" aria-label="Transaction pagination">
                @if($transactions->onFirstPage())<span class="rules-page is-disabled" aria-disabled="true">Previous</span>@else<a class="rules-page" href="{{ $transactions->previousPageUrl() }}" rel="prev">Previous</a>@endif
                @foreach($pages as $page)@if($previousPage && $page > $previousPage + 1)<span class="rules-page rules-page--gap" aria-hidden="true">...</span>@endif @if($page === $currentPage)<span class="rules-page is-active" aria-current="page">{{ $page }}</span>@else<a class="rules-page" href="{{ $transactions->url($page) }}">{{ $page }}</a>@endif @php $previousPage = $page; @endphp @endforeach
                @if($transactions->hasMorePages())<a class="rules-page" href="{{ $transactions->nextPageUrl() }}" rel="next">Next</a>@else<span class="rules-page is-disabled" aria-disabled="true">Next</span>@endif
            </nav>
        @endif
    </div>
</section>
</div>
</div>
@endsection

@push('styles')
@vite('resources/css/views/reports-index-1.css')
@endpush
