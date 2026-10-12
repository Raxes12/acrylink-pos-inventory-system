@extends('layouts.app')
@section('title', 'Accounts Receivable')
@section('page-label', 'Accounts Receivable')

@section('content')
<div class="ar-page">
<x-page-header title="Accounts Receivable" subtitle="Prioritize overdue accounts, monitor upcoming dues, and record customer collections.">
    <x-slot:actions>
        @if(auth()->user()?->isAdmin())
        <a class="btn btn-secondary" href="{{ route('customers.index') }}">
            <i class="bi bi-people me-1" aria-hidden="true"></i>
            Customers
        </a>
        <a class="btn btn-primary" href="{{ route('reports.accounts-receivable') }}">
            <i class="bi bi-file-earmark-bar-graph" aria-hidden="true"></i>
            Receivables Report
        </a>
        @endif
    </x-slot:actions>
</x-page-header>

<div class="grid-4 section-spaced ar-metrics" data-live-sync="ar-summary">
    <div class="card metric-card"><div class="metric-topline"><span class="metric-label">Total Outstanding</span><span class="metric-icon"><i class="bi bi-wallet2" aria-hidden="true"></i></span></div><div class="metric-value">&#8369;{{ number_format($summary['total_outstanding'], 2) }}</div><div class="metric-note">Balance still collectible across open credit sales</div></div>
    <div class="card metric-card"><div class="metric-topline"><span class="metric-label">Due Soon</span><span class="metric-icon"><i class="bi bi-calendar2-week" aria-hidden="true"></i></span></div><div class="metric-value ar-due-soon">&#8369;{{ number_format($summary['due_soon'], 2) }}</div><div class="metric-note">Due today through the next {{ \App\Services\AccountsReceivableService::DUE_SOON_DAYS }} days</div></div>
    <div class="card metric-card"><div class="metric-topline"><span class="metric-label">Overdue</span><span class="metric-icon"><i class="bi bi-exclamation-triangle" aria-hidden="true"></i></span></div><div class="metric-value ar-overdue">&#8369;{{ number_format($summary['overdue'], 2) }}</div><div class="metric-note">Balance requiring immediate collection follow-up</div></div>
    <div class="card metric-card"><div class="metric-topline"><span class="metric-label">Open Credit Customers</span><span class="metric-icon"><i class="bi bi-people" aria-hidden="true"></i></span></div><div class="metric-value">{{ number_format($summary['customers_with_open_credit']) }}</div><div class="metric-note">Customers who currently have an open balance</div></div>
</div>

<form method="GET" action="{{ route('accounts-receivable.index') }}" class="filter-bar ar-filter" data-live-filter>
    <div class="ar-filter-main">
        <div class="ar-search-wrap">
            <i class="bi bi-search" aria-hidden="true"></i>
            <input id="arSearch" type="search" name="search" maxlength="150" autocomplete="off" value="{{ $filters['search'] ?? '' }}" placeholder="Search customer name, code, or receipt number...">
        </div>
            <select name="payment_status" aria-label="Account status">
                <option value="">All statuses</option>
                <option value="open" @selected(($filters['payment_status'] ?? '') === 'open')>Open</option>
                <option value="due_soon" @selected(($filters['payment_status'] ?? '') === 'due_soon')>Due Soon</option>
                <option value="overdue" @selected(($filters['payment_status'] ?? '') === 'overdue')>Overdue</option>
                <option value="partially_paid" @selected(($filters['payment_status'] ?? '') === 'partially_paid')>Partially Paid</option>
                <option value="paid" @selected(($filters['payment_status'] ?? '') === 'paid')>Paid</option>
            </select>
    </div>
    <div class="ar-quick-filters" role="group" aria-label="Quick account status filters">
        @foreach(['' => 'All', 'open' => 'Open', 'due_soon' => 'Due Soon', 'overdue' => 'Overdue', 'partially_paid' => 'Partially Paid', 'paid' => 'Paid'] as $value => $label)
            <button type="button" class="ar-filter-chip {{ ($filters['payment_status'] ?? '') === $value ? 'is-active' : '' }}" data-live-filter-choice="payment_status" data-live-filter-value="{{ $value }}" aria-pressed="{{ ($filters['payment_status'] ?? '') === $value ? 'true' : 'false' }}">{{ $label }}</button>
        @endforeach
        <span class="live-filter-status" data-live-filter-status role="status" aria-live="polite"></span>
    </div>
</form>

<div class="card ar-table-card" data-live-results>
    <div class="card-header"><div><span class="card-title">Credit Sales</span><span class="ar-card-caption">Review balances, due dates, and payment progress.</span></div><span class="tag tag--warning">{{ $transactions->total() }} records</span></div>
    <div class="table-wrap ar-table-wrap" tabindex="0" role="region" aria-label="Accounts receivable credit sales">
        <table class="ar-table">
            <thead>
                <tr>
                    <th>Customer</th>
                    <th>Credit Sale</th>
                    <th class="text-right">Original Amount</th>
                    <th>Payment Progress</th>
                    <th>Due Date</th>
                    <th>Payment Status</th>
                    <th>Cashier</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse($transactions as $transaction)
                @php
                    $displayStatus = $receivables->statusForRow($transaction);
                    $netAmount = (float) $transaction->ar_net_amount;
                    $paidAmount = (float) $transaction->ar_paid_amount;
                    $balanceAmount = (float) $transaction->ar_balance_amount;
                    $paidPercent = $netAmount > 0 ? min(100, round(($paidAmount / $netAmount) * 100)) : 100;
                    $dueDate = $transaction->creditSale->due_date;
                    $daysUntilDue = (int) today()->diffInDays($dueDate, false);
                    $customerName = $transaction->customer?->customer_name ?? 'Unknown customer';
                    $initials = collect(preg_split('/\s+/', trim($customerName)))->filter()->take(2)->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))->join('');
                @endphp
                <tr class="ar-row ar-row--{{ str_replace('_', '-', $displayStatus) }}">
                    <td data-label="Customer">
                        <div class="ar-customer-cell">
                            <span class="ar-customer-avatar" aria-hidden="true">
                                {{ $initials ?: '?' }}
                            </span>
                            <div>
                                <a class="ar-customer" href="{{ route('accounts-receivable.customers.show', $transaction->customer) }}">
                                    {{ $customerName }}
                                </a>
                                <small>
                                    {{ $transaction->customer?->customer_code }}
                                </small>
                            </div>
                        </div>
                    </td>
                    <td data-label="Credit Sale" class="ar-sale-cell">
                        <a class="receipt-code ar-receipt-link" href="{{ route('pos.receipt', $transaction) }}">{{ $transaction->receipt_no }}</a>
                        <span>{{ $transaction->date->format('M d, Y') }}</span>
                        <small>{{ $transaction->date->diffForHumans() }}</small>
                    </td>
                    <td data-label="Original Amount" class="text-right ar-money ar-original-amount">
                        &#8369;{{ number_format($netAmount, 2) }}
                    </td>
                    <td data-label="Payment Progress" class="ar-payment-cell">
                        <div class="ar-payment-totals"><span><small>Paid</small><strong>&#8369;{{ number_format($paidAmount, 2) }}</strong></span><span><small>Balance</small><strong class="ar-balance {{ $balanceAmount <= 0 ? 'is-settled' : '' }}">&#8369;{{ number_format($balanceAmount, 2) }}</strong></span></div>
                        <div class="ar-progress" role="progressbar" aria-label="{{ $paidPercent }}% paid" aria-valuenow="{{ $paidPercent }}" aria-valuemin="0" aria-valuemax="100">
                            <span style="width:{{ $paidPercent }}%"></span>
                        </div>
                        <small class="ar-progress-label">{{ $paidPercent }}% collected</small>
                    </td>
                    <td data-label="Due Date" class="ar-date-cell">
                        <strong>{{ $dueDate->format('M d, Y') }}</strong>
                        <small class="{{ $displayStatus === 'overdue' ? 'ar-due-danger' : '' }}">
                            @if($displayStatus === 'paid')
                                Settled
                            @elseif($daysUntilDue < 0)
                                {{ abs($daysUntilDue) }} day{{ abs($daysUntilDue) === 1 ? '' : 's' }} overdue
                            @elseif($daysUntilDue === 0)
                                Due today
                            @else
                                Due in {{ $daysUntilDue }} days
                            @endif
                        </small>
                    </td>
                    <td data-label="Status">
                        <span class="status {{ $displayStatus === 'overdue' ? 'status-danger' : ($displayStatus === 'paid' ? 'status-active' : 'status-low') }}">
                            {{ ucfirst(str_replace('_', ' ', $displayStatus)) }}
                        </span>
                    </td>
                    <td data-label="Cashier" class="ar-cashier">
                        {{ $transaction->cashier?->name ?? 'Unknown' }}
                    </td>
                    <td data-label="Actions">
                        <div class="table-actions ar-actions">
                            @if($balanceAmount > 0)
                            <a class="btn btn-primary btn-sm ar-pay-action" href="{{ route('pos.credit-payments.create', $transaction) }}">
                                <i class="bi bi-plus-circle" aria-hidden="true"></i><span>Record Payment</span>
                            </a>
                            @endif
                            <a class="btn btn-secondary btn-sm" href="{{ route('accounts-receivable.customers.show', $transaction->customer) }}" title="View customer account">
                                <i class="bi bi-person" aria-hidden="true"></i><span>Account</span>
                            </a>
                            <a class="btn btn-secondary btn-sm" href="{{ route('pos.receipt', $transaction) }}" title="View sale receipt">
                                <i class="bi bi-receipt" aria-hidden="true"></i><span>Receipt</span>
                            </a>
                        </div>
                    </td>
                </tr>
            @empty
                <x-empty-state colspan="8" title="No matching receivables" message="Try a different customer, code, receipt number, or account status." />
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="view-blades-footer">
        <div class="pagination-summary">
            Showing {{ $transactions->firstItem() ?? 0 }}-{{ $transactions->lastItem() ?? 0 }} of {{ $transactions->total() }} transactions
        </div>

        @if($transactions->hasPages())
        @php
            $currentPage = $transactions->currentPage();
            $lastPage = $transactions->lastPage();
            $pages = collect([1, $currentPage - 1, $currentPage, $currentPage + 1, $lastPage])
                ->filter(fn ($page) => $page >= 1 && $page <= $lastPage)
                ->unique()
                ->sort()
                ->values();
            $previousPage = null;
        @endphp
        <nav class="rules-pagination" aria-label="Security rules pagination">
            @if($transactions->onFirstPage())
                <span class="rules-page is-disabled" aria-disabled="true">Previous</span>
            @else
                <a class="rules-page" href="{{ $transactions->previousPageUrl() }}" rel="prev">Previous</a>
            @endif

            @foreach($pages as $page)
                @if($previousPage && $page > $previousPage + 1)
                    <span class="rules-page rules-page--gap" aria-hidden="true">...</span>
                @endif

                @if($page === $currentPage)
                    <span class="rules-page is-active" aria-current="page">{{ $page }}</span>
                @else
                    <a class="rules-page" href="{{ $transactions->url($page) }}">{{ $page }}</a>
                @endif

                @php $previousPage = $page; @endphp
            @endforeach

            @if($transactions->hasMorePages())
                <a class="rules-page" href="{{ $transactions->nextPageUrl() }}" rel="next">Next</a>
            @else
                <span class="rules-page is-disabled" aria-disabled="true">Next</span>
            @endif
        </nav>
        @endif
    </div>
</div>
</div>
@endsection

@push('styles')
@vite('resources/css/views/accounts-receivable-1.css')
@endpush
