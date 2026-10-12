@extends('layouts.app')
@section('title', 'Transactions')
@section('page-label', 'Transactions')

@section('content')
<div class="transactions-page">
<x-page-header title="Transactions" subtitle="Search, filter, view receipts, and process returns or voids.">
    <x-slot:actions>
        <a href="{{ route('pos.index') }}" class="btn btn-primary">
            <x-icon name="pos" />
            POS Terminal
        </a>
    </x-slot:actions>
</x-page-header>

<div class="grid-3 section-spaced transaction-metrics">
    <div class="card metric-card transaction-metric transaction-metric--sales">
        <div class="metric-topline"><span class="metric-label">Today Sales</span><span class="metric-icon icon-sales">PHP</span></div>
        <div class="metric-value transaction-sales-value">&#8369;{{ number_format($todaySales, 2) }}</div>
        <div class="metric-note">Net sales after refunds and voids</div>
    </div>
    <div class="card metric-card transaction-metric transaction-metric--today">
        <div class="metric-topline"><span class="metric-label">Today Receipts</span><span class="metric-icon icon-txn">POS</span></div>
        <div class="metric-value">{{ number_format($todayTxns) }}</div>
        <div class="metric-note">Receipts processed today</div>
    </div>
    <div class="card metric-card transaction-metric transaction-metric--all">
        <div class="metric-topline"><span class="metric-label">All Receipts</span><span class="metric-icon icon-products">TXN</span></div>
        <div class="metric-value">{{ number_format($totalReceipts) }}</div>
        <div class="metric-note">{{ auth()->user()?->isCashier() ? 'Under your cashier account' : 'Across all cashiers' }}</div>
    </div>
</div>

<form method="GET" action="{{ route('pos.transactions') }}" class="filter-bar transaction-filter" data-live-filter>
    <div class="transaction-search"><i class="bi bi-search" aria-hidden="true"></i><input type="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search receipt, customer, cashier, or payment..." autocomplete="off" aria-label="Search transactions"></div>
    <label class="transaction-filter-field"><span>Status</span><select name="status" class="filter-field--md">
            <option value="">All Statuses</option>
            @foreach($statuses as $value => $label)
            <option value="{{ $value }}" {{ ($filters['status'] ?? '') === $value ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select></label>
    <label class="transaction-filter-field"><span>Payment</span><select name="payment_method" class="filter-field--md">
            <option value="">All Payments</option>
            @foreach($paymentMethods as $method)
            <option value="{{ $method->code }}" {{ ($filters['payment_method'] ?? '') === $method->code ? 'selected' : '' }}>{{ $method->name }}</option>
            @endforeach
        </select></label>
    <label class="transaction-date"><span>From</span><input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}"></label>
    <label class="transaction-date"><span>To</span><input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}"></label>
    <span class="live-filter-status" data-live-filter-status role="status" aria-live="polite"></span>
</form>

<div class="card transaction-history-card" data-live-results>
    <div class="card-header">
        <div class="transaction-history-heading"><span class="transaction-history-icon"><i class="bi bi-receipt" aria-hidden="true"></i></span><div><span class="card-title">Transaction History</span><span>Receipts, payment progress, and available actions</span></div></div>
        <span class="transaction-count">{{ $transactions->total() }} total</span>
    </div>
    <div class="table-wrap" tabindex="0" role="region" aria-label="Transaction history">
        <table>
            <thead>
                <tr>
                    <th>Sale</th>
                    <th>Customer</th>
                    <th>Payment</th>
                    <th class="text-right">Amounts</th>
                    <th>Credit Sale</th>
                    <th>Cashier</th>
                    <th>Transaction Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse($transactions as $transaction)
            @php
                $dateTime = \Carbon\Carbon::parse($transaction->date->format('Y-m-d').' '.$transaction->time);
                $refunded = $transaction->totalRefunded();
                $status = $transaction->status ?? 'completed';
                $statusClass = match ($status) {
                    'completed' => 'status-active',
                    'partial_refund' => 'status-low',
                    default => 'status-danger',
                };
                $isCreditSale = (bool) $transaction->creditSale;
                $paymentStatus = app(\App\Services\TransactionSettlementService::class)->paymentStatus($transaction);
                $paymentStatusClass = match ($paymentStatus) {
                    'paid' => 'status-active',
                    'overdue' => 'status-danger',
                    default => 'status-low',
                };
                $outstanding = $isCreditSale ? $transaction->outstandingBalance() : null;
                $canRefund = auth()->user()?->can('refund', $transaction) && in_array($status, ['completed', 'partial_refund'], true);
                $canRecordCreditPayment = auth()->user()?->canAccessSales()
                    && $isCreditSale
                    && $status !== 'voided'
                    && $outstanding > 0;
                $customerInitials = $transaction->customer
                    ? collect(explode(' ', $transaction->customer->customer_name))->filter()->take(2)->map(fn ($part) => strtoupper(substr($part, 0, 1)))->implode('')
                    : 'WI';
                $cashierInitials = collect(explode(' ', $transaction->cashier?->name ?? 'Unknown'))->filter()->take(2)->map(fn ($part) => strtoupper(substr($part, 0, 1)))->implode('');
                $daysUntilDue = $isCreditSale ? today()->diffInDays($transaction->creditSale->due_date, false) : null;
            @endphp
            <tr class="transaction-row transaction-row--{{ str_replace('_', '-', $status) }}">
                <td class="transaction-sale-cell" data-label="Receipt / Date">
                    <div class="transaction-sale-content"><a class="receipt-code" href="{{ route('pos.receipt', $transaction) }}">{{ $transaction->receipt_no }}</a><span class="transaction-date-cell"><strong>{{ $dateTime->format('M d, Y') }}</strong><small>{{ $dateTime->format('h:i A') }}</small></span></div>
                </td>
                <td class="transaction-customer-cell" data-label="Customer">
                    <div class="transaction-customer-content"><span class="transaction-avatar {{ $transaction->customer ? '' : 'transaction-avatar--walkin' }}" aria-hidden="true">{{ $customerInitials }}</span><div><strong>{{ $transaction->customer?->customer_name ?? 'Walk-in' }}</strong><span>{{ $transaction->customer?->customer_code ?? 'Guest customer' }}</span></div></div>
                </td>
                <td data-label="Payment"><span class="transaction-payment"><i class="bi {{ $isCreditSale ? 'bi-journal-text' : 'bi-wallet2' }}" aria-hidden="true"></i>{{ \App\Models\Transaction::paymentMethodLabel($transaction->payment_method) }}</span></td>
                <td class="text-right transaction-amounts-cell" data-label="Amounts">
                    <span class="transaction-amount-line"><small>Net total</small><strong class="transaction-net-value">&#8369;{{ number_format($transaction->netTotal(), 2) }}</strong></span>
                    @if($refunded > 0)<span class="transaction-amount-line"><small>Refunded</small><b class="transaction-refund-value">&minus;&#8369;{{ number_format($refunded, 2) }}</b></span>@endif
                    <span class="transaction-amount-line"><small>Balance:</small>@if($isCreditSale)<b class="{{ $outstanding > 0 ? 'transaction-balance-open' : '' }}">&#8369;{{ number_format($outstanding, 2) }}</b>@else<b class="transaction-zero-value">&mdash;</b>@endif</span>
                </td>
                <td class="transaction-credit-cell" data-label="Credit">
                    <span><small>Due Date</small><strong>{{ $isCreditSale ? $transaction->creditSale->due_date->format('M d, Y') : '—' }}</strong></span>
                    @if($isCreditSale && $outstanding > 0)
                        <small class="transaction-due-hint {{ $daysUntilDue < 0 ? 'is-overdue' : '' }}">@if($daysUntilDue < 0){{ abs($daysUntilDue) }} day{{ abs($daysUntilDue) === 1 ? '' : 's' }} overdue @elseif($daysUntilDue === 0)Due today @else Due in {{ $daysUntilDue }} days @endif</small>
                    @endif
                    <span class="status {{ $paymentStatusClass }}">{{ ucfirst(str_replace('_', ' ', $paymentStatus)) }}</span>
                </td>
                <td data-label="Cashier"><div class="transaction-cashier"><span class="transaction-cashier-avatar" aria-hidden="true">{{ $cashierInitials }}</span><strong>{{ $transaction->cashier?->username ?? 'Unknown' }}</strong></div></td>
                <td data-label="Transaction Status"><span class="status {{ $statusClass }}">{{ ucfirst(str_replace('_', ' ', $status)) }}</span></td>
                <td data-label="Actions">
                    <div class="table-actions transaction-actions">
                        <a href="{{ route('pos.receipt', $transaction) }}" class="btn btn-secondary btn-sm transaction-action" aria-label="View receipt {{ $transaction->receipt_no }}">
                            <x-icon name="receipts" />
                            View Receipt
                        </a>
                        @if($canRecordCreditPayment)
                        <a href="{{ route('pos.credit-payments.create', $transaction) }}" class="btn btn-primary btn-sm transaction-action" aria-label="Record payment for {{ $transaction->receipt_no }}">
                            Record Payment
                        </a>
                        @endif
                        @if($canRefund)
                        <a href="{{ route('pos.refund.form', $transaction) }}" class="btn btn-danger btn-sm transaction-action transaction-action--refund" aria-label="Return items from {{ $transaction->receipt_no }}">
                            <x-icon name="transactions" />
                            Return Items
                        </a>
                        @endif
                        @if($status === 'voided')
                        <span class="transaction-locked-state">No actions</span>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="empty-state">No transactions found.</td>
            </tr>
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
@vite('resources/css/views/dashboard-my-transactions-1.css')
@endpush
