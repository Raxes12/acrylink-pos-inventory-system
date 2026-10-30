@extends('layouts.app')
@section('title', 'Receivables Report')
@section('page-label', 'Reports / Receivables')

@section('content')
<div class="reports-page ar-report-page">
<x-page-header title="Receivables Report" subtitle="Review outstanding customer credit, due dates, payment progress, and account aging.">
    <x-slot:actions>
        <a class="btn btn-secondary" href="{{ route('accounts-receivable.index') }}"><i class="bi bi-wallet2" aria-hidden="true"></i> Manage Accounts</a>
        <a href="{{ route('reports.accounts-receivable.export', request()->query()) }}" class="btn report-export report-export--csv"><i class="bi bi-file-earmark-spreadsheet" aria-hidden="true"></i> Export CSV</a>
        <a href="{{ route('reports.accounts-receivable.export.xlsx', request()->query()) }}" class="btn report-export report-export--excel"><i class="bi bi-file-earmark-excel" aria-hidden="true"></i> Export Excel</a>
        <a href="{{ route('reports.accounts-receivable.export.pdf', request()->query()) }}" class="btn report-export report-export--pdf"><i class="bi bi-file-earmark-pdf" aria-hidden="true"></i> Export PDF</a>
    </x-slot:actions>
</x-page-header>

@if($errors->has('export'))<div class="alert alert-danger ar-report-alert">{{ $errors->first('export') }}</div>@endif

<section class="card ar-collections-panel" aria-labelledby="arCollectionsHeading">
    <div class="ar-collections-summary">
        <span class="ar-collections-icon"><i class="bi bi-wallet2" aria-hidden="true"></i></span>
        <div>
            <span class="ar-collections-label" id="arCollectionsHeading">Total Customer Collections</span>
            <strong>&#8369;{{ number_format($totalCollections, 2) }}</strong>
            <small>Credit account payments received during {{ strtolower($collectionPeriodLabel) }}</small>
        </div>
    </div>
    <div class="ar-collections-period">
        <span>Reporting period</span>
        <nav aria-label="Customer collections reporting period">
            @foreach(['today' => 'Today', 'week' => 'This Week', 'month' => 'This Month', 'all' => 'All Time'] as $period => $label)
                @php
                    $periodQuery = array_filter(array_merge(request()->except(['page', 'collection_period']), ['collection_period' => $period]));
                @endphp
                <a href="{{ route('reports.accounts-receivable', $periodQuery) }}" class="report-period-chip {{ $collectionPeriod === $period ? 'is-active' : '' }}" @if($collectionPeriod === $period) aria-current="page" @endif>{{ $label }}</a>
            @endforeach
        </nav>
    </div>
</section>

<form method="GET" action="{{ route('reports.accounts-receivable') }}" class="card ar-report-filter" data-live-filter>
    <input type="hidden" name="collection_period" value="{{ $collectionPeriod }}">
    <div class="ar-filter-heading"><div><strong>Find receivables</strong><span>Search and dropdown filters update automatically. Apply date ranges when ready.</span></div><span class="live-filter-status" data-live-filter-status role="status" aria-live="polite"></span></div>
    <div class="ar-filter-primary">
        <label class="ar-filter-search"><span>Customer</span><div><i class="bi bi-search" aria-hidden="true"></i><input type="search" name="customer" value="{{ $filters['customer'] ?? '' }}" placeholder="Name or customer code" autocomplete="off"></div></label>
        <label class="ar-filter-search ar-filter-receipt"><span>Receipt</span><div><i class="bi bi-receipt" aria-hidden="true"></i><input type="search" name="receipt" value="{{ $filters['receipt'] ?? '' }}" placeholder="Receipt number" autocomplete="off"></div></label>
        <label><span>Payment status</span><select name="payment_status"><option value="">All open statuses</option><option value="unpaid" @selected(($filters['payment_status'] ?? '') === 'unpaid')>Unpaid</option><option value="partially_paid" @selected(($filters['payment_status'] ?? '') === 'partially_paid')>Partially Paid</option><option value="overdue" @selected(($filters['payment_status'] ?? '') === 'overdue')>Overdue</option></select></label>
        <label><span>Aging</span><select name="aging"><option value="">All aging periods</option><option value="not_due" @selected(($filters['aging'] ?? '') === 'not_due')>Not Yet Due</option><option value="1_30" @selected(($filters['aging'] ?? '') === '1_30')>1-30 Days Overdue</option><option value="31_60" @selected(($filters['aging'] ?? '') === '31_60')>31-60 Days Overdue</option><option value="61_90" @selected(($filters['aging'] ?? '') === '61_90')>61-90 Days Overdue</option><option value="90_plus" @selected(($filters['aging'] ?? '') === '90_plus')>90+ Days Overdue</option></select></label>
    </div>
    <details class="ar-advanced-filters" @if(collect($filters)->only(['due_from','due_to','transaction_from','transaction_to'])->filter()->isNotEmpty()) open @endif>
        <summary><i class="bi bi-calendar3" aria-hidden="true"></i> Date filters <span>Optional</span></summary>
        <div class="ar-date-filters">
            <label><span>Due from</span><input type="date" name="due_from" value="{{ $filters['due_from'] ?? '' }}" data-live-manual></label>
            <label><span>Due to</span><input type="date" name="due_to" value="{{ $filters['due_to'] ?? '' }}" data-live-manual></label>
            <label><span>Sale from</span><input type="date" name="transaction_from" value="{{ $filters['transaction_from'] ?? '' }}" data-live-manual></label>
            <label><span>Sale to</span><input type="date" name="transaction_to" value="{{ $filters['transaction_to'] ?? '' }}" data-live-manual></label>
        </div>
        @php $withoutDates = request()->except(['page', 'due_from', 'due_to', 'transaction_from', 'transaction_to']); @endphp
        <div class="ar-filter-actions">
            <button class="btn btn-primary btn-sm" type="submit"><i class="bi bi-calendar-check" aria-hidden="true"></i> Apply Dates</button>
            <a class="btn btn-secondary btn-sm" href="{{ route('reports.accounts-receivable', $withoutDates) }}" data-live-filter-clear-fields="due_from,due_to,transaction_from,transaction_to">Clear Dates</a>
        </div>
    </details>
</form>

@php
    $totalOutstanding = collect($aging)->sum();
    $agingOptions = [
        'not_due' => ['label' => 'Not Yet Due', 'filter' => 'not_due', 'icon' => 'bi-calendar-check'],
        'days_1_30' => ['label' => '1-30 Days', 'filter' => '1_30', 'icon' => 'bi-clock'],
        'days_31_60' => ['label' => '31-60 Days', 'filter' => '31_60', 'icon' => 'bi-exclamation-circle'],
        'days_61_90' => ['label' => '61-90 Days', 'filter' => '61_90', 'icon' => 'bi-exclamation-triangle'],
        'days_90_plus' => ['label' => '90+ Days', 'filter' => '90_plus', 'icon' => 'bi-shield-exclamation'],
    ];
@endphp
<section class="ar-aging-workspace section-spaced" data-live-sync="ar-aging" aria-label="Receivables aging summary">
    <article class="card ar-outstanding-hero"><div class="ar-aging-top"><span class="ar-aging-icon"><i class="bi bi-cash-stack" aria-hidden="true"></i></span><span>Total Outstanding</span></div><strong>&#8369;{{ number_format($totalOutstanding, 2) }}</strong><small>Across {{ number_format($transactions->total()) }} open receivable records</small></article>
    @foreach($agingOptions as $key => $option)
        @php $query = array_filter(array_merge(request()->except('page'), ['aging' => $option['filter']])); @endphp
        <a class="card ar-aging-card ar-aging-card--{{ $key }} {{ ($filters['aging'] ?? '') === $option['filter'] ? 'is-active' : '' }}" href="{{ route('reports.accounts-receivable', $query) }}">
            <div class="ar-aging-top"><span class="ar-aging-icon"><i class="bi {{ $option['icon'] }}" aria-hidden="true"></i></span><span>{{ $option['label'] }}</span></div>
            <strong>&#8369;{{ number_format($aging[$key] ?? 0, 2) }}</strong>
            <small>{{ $totalOutstanding > 0 ? number_format((($aging[$key] ?? 0) / $totalOutstanding) * 100, 1) : '0.0' }}% of outstanding</small>
        </a>
    @endforeach
</section>

<section class="card ar-report-table" data-live-results>
    <div class="card-header"><div><span class="card-title"><i class="bi bi-list-check" aria-hidden="true"></i> Open Receivables</span><span class="ar-report-caption">Customer balances ordered by the nearest due date.</span></div><span class="tag tag--warning">{{ $transactions->total() }} records</span></div>
    <div class="table-wrap" tabindex="0" role="region" aria-label="Open receivables report"><table><thead><tr><th>Customer</th><th>Receipt</th><th>Sale Date</th><th>Due Date</th><th class="text-right">Net Sale</th><th>Payment Progress</th><th class="text-right">Outstanding</th><th>Status</th></tr></thead><tbody>
    @forelse($transactions as $transaction)
        @php
            $status = $report->status($transaction);
            $dueDate = $transaction->creditSale->due_date;
            $daysDue = today()->diffInDays($dueDate, false);
            $paidPercent = (float) $transaction->ar_net_amount > 0 ? min(100, ((float) $transaction->ar_paid_amount / (float) $transaction->ar_net_amount) * 100) : 0;
            $initials = collect(explode(' ', $transaction->customer?->customer_name ?? '?'))->filter()->take(2)->map(fn ($part) => strtoupper(substr($part, 0, 1)))->implode('');
        @endphp
        <tr class="{{ $status === 'overdue' ? 'ar-report-row--overdue' : '' }}">
            <td><div class="ar-customer-cell"><span class="ar-customer-avatar" aria-hidden="true">{{ $initials }}</span><div><strong>{{ $transaction->customer?->customer_name ?? 'Unknown customer' }}</strong><small>{{ $transaction->customer?->customer_code ?? 'No customer code' }}</small></div></div></td>
            <td><a href="{{ route('pos.receipt', $transaction) }}" class="report-receipt">{{ $transaction->receipt_no }}</a></td>
            <td><div class="ar-date-cell"><strong>{{ $transaction->date->format('M d, Y') }}</strong><small>Credit sale</small></div></td>
            <td><div class="ar-date-cell"><strong>{{ $dueDate->format('M d, Y') }}</strong><small class="{{ $daysDue < 0 ? 'ar-due-danger' : '' }}">@if($daysDue < 0){{ abs($daysDue) }} day{{ abs($daysDue) === 1 ? '' : 's' }} overdue @elseif($daysDue === 0)Due today @else Due in {{ $daysDue }} days @endif</small></div></td>
            <td class="text-right ar-net-sale">&#8369;{{ number_format((float) $transaction->ar_net_amount, 2) }}</td>
            <td><div class="ar-payment-progress"><div><span>&#8369;{{ number_format((float) $transaction->ar_paid_amount, 2) }} paid</span><strong>{{ number_format($paidPercent, 0) }}%</strong></div><span class="ar-progress-track"><i style="width:{{ $paidPercent }}%"></i></span></div></td>
            <td class="text-right ar-report-balance">&#8369;{{ number_format((float) $transaction->ar_balance_amount, 2) }}</td>
            <td><span class="status {{ $status === 'overdue' ? 'status-danger' : 'status-low' }}">{{ ucfirst(str_replace('_', ' ', $status)) }}</span></td>
        </tr>
    @empty
        <x-empty-state colspan="8" title="No open receivables" message="No customer accounts match the selected filters." />
    @endforelse
    </tbody></table></div>
    <div class="view-blades-footer"><div class="pagination-summary">Showing {{ $transactions->firstItem() ?? 0 }}-{{ $transactions->lastItem() ?? 0 }} of {{ $transactions->total() }} receivables</div>{{ $transactions->links() }}</div>
</section>
</div>
@endsection

@push('styles')
@vite('resources/css/views/reports-index-1.css')
@endpush
