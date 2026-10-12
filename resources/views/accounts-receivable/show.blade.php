@extends('layouts.app')
@section('title', 'Customer Credit Detail')
@section('page-label', 'Accounts Receivable / Customer')

@section('content')
<a href="{{ route('accounts-receivable.index') }}" class="editor-breadcrumb">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
        stroke-linecap="round" stroke-linejoin="round">
        <path d="M19 12H5"/><path d="m12 19-7-7 7-7"/>
    </svg>
    Back to Recieivables
</a>
<div class="ar-page ar-customer-page">
<x-page-header :title="$customer->customer_name" :subtitle="$customer->customer_code.' · Customer credit account'">
    <x-slot:actions><a class="btn btn-secondary" href="{{ route('customers.show', $customer) }}">Customer Profile</a><a class="btn btn-primary" href="{{ route('accounts-receivable.index') }}">All Receivables</a></x-slot:actions>
</x-page-header>

<div class="grid-4 section-spaced ar-metrics">
    <div class="card metric-card">
        <div class="metric-label">Credit Limit</div>
        <div class="metric-value">&#8369;{{ number_format($customerSummary['credit_limit'], 2) }}</div>
    </div>
    <div class="card metric-card">
        <div class="metric-label">Current Outstanding</div>
        <div class="metric-value ar-overdue">&#8369;{{ number_format($customerSummary['current_outstanding'], 2) }}</div>
    </div>
    <div class="card metric-card">
        <div class="metric-label">Available Credit</div>
        <div class="metric-value ar-available">&#8369;{{ number_format($customerSummary['available_credit'], 2) }}</div>
    </div>
    <div class="card metric-card">
        <div class="metric-label">Default Credit Term</div>
        <div class="metric-value">{{ $customer->default_credit_term_days }} days</div>
        <div class="metric-note">
            <span class="status {{ $customer->credit_enabled && $customer->status === 'active' ? 'status-active' : 'status-danger' }}">
                Credit {{ $customer->credit_enabled ? 'enabled' : 'disabled' }}
            </span>
        </div>
    </div>
</div>

<section class="card ar-table-card">
    <div class="card-header"><span class="card-title">Open Credit Transactions</span><span>{{ $transactions->total() }} open</span></div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Receipt</th>
                    <th>Sale Date</th>
                    <th>Due Date</th>
                    <th>Status</th>
                    <th class="text-right">Net</th>
                    <th class="text-right">Paid</th>
                    <th class="text-right">Balance</th>
                    <th>Actions</th>
                </tr>
            </thead>
            
            <tbody>
            @forelse($transactions as $transaction)
                @php($displayStatus = $receivables->statusForRow($transaction))
                <tr>
                    <td><span class="receipt-code">{{ $transaction->receipt_no }}</span></td>
                    <td>{{ $transaction->date->format('M d, Y') }}</td>
                    <td>{{ $transaction->creditSale->due_date->format('M d, Y') }}</td>
                    <td>
                        <span class="status {{ $displayStatus === 'overdue' ? 'status-danger' : 'status-low' }}">
                            {{ ucfirst(str_replace('_', ' ', $displayStatus)) }}
                        </span>
                    </td>
                    <td class="text-right">&#8369;{{ number_format((float) $transaction->ar_net_amount, 2) }}</td>
                    <td class="text-right">&#8369;{{ number_format((float) $transaction->ar_paid_amount, 2) }}</td>
                    <td class="text-right fw-800 ar-balance">&#8369;{{ number_format((float) $transaction->ar_balance_amount, 2) }}</td>
                    <td>
                        <div class="table-actions">
                            <a class="btn btn-primary btn-sm" href="{{ route('pos.credit-payments.create', $transaction) }}">
                                <i class="bi bi-plus-circle" aria-hidden="true"></i><span>Record Payment</span>
                            </a>
                            <a class="btn btn-secondary btn-sm" href="{{ route('pos.receipt', $transaction) }}">Receipt</a>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <x-empty-state colspan="8" title="No open credit transactions" message="This customer has no outstanding Credit Sales." />
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="view-blades-footer">{{ $transactions->links() }}</div>
</section>

<section class="card ar-table-card section-spaced">
    <div class="card-header"><span class="card-title">Payment History</span><span>{{ $payments->total() }} payments</span></div>
    <div class="table-wrap"><table><thead><tr><th>Date</th><th>Receipt</th><th>Method</th><th>Reference</th><th>Received By</th><th>Notes</th><th class="text-right">Amount</th><th>Details</th></tr></thead><tbody>
    @forelse($payments as $payment)
        <tr><td>{{ $payment->paid_at->format('M d, Y h:i A') }}</td><td>{{ $payment->transaction?->receipt_no }}</td><td>{{ $payment->paymentMethod?->name ?? 'Unknown' }}</td><td>{{ $payment->payment_reference ?: '-' }}</td><td>{{ $payment->receivedBy?->name ?? 'Unknown' }}</td><td>{{ $payment->notes ?: '-' }}</td><td class="text-right fw-800">&#8369;{{ number_format((float) $payment->amount, 2) }}</td><td><a class="btn btn-secondary btn-sm" href="{{ route('pos.credit-payments.show', $payment) }}">View</a></td></tr>
    @empty<tr><x-empty-state colspan="8" title="No payment history" message="Payments recorded for this customer's Credit Sales will appear here." /></tr>@endforelse
    </tbody></table></div><div class="view-blades-footer">{{ $payments->links() }}</div>
</section>
</div>
@endsection

@push('styles')
@vite('resources/css/views/accounts-receivable-1.css')
@endpush
