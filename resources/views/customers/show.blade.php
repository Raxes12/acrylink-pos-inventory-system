@extends('layouts.app')
@section('title', 'Customer Details')
@section('page-label', 'Customers / Details')

@section('content')
@php
    $limit = (float) $creditSummary['credit_limit'];
    $outstanding = (float) $creditSummary['current_outstanding'];
    $available = (float) $creditSummary['available_credit'];
    $usage = $limit > 0 ? min(100, round(($outstanding / $limit) * 100)) : 0;
    $initials = collect(preg_split('/\s+/', trim($customer->customer_name)))->filter()->take(2)->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))->join('');
@endphp
<div class="customer-profile-page">
    <a href="{{ route('customers.index') }}" class="editor-breadcrumb">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
            stroke-linecap="round" stroke-linejoin="round">
            <path d="M19 12H5"/><path d="m12 19-7-7 7-7"/>
        </svg>
        Back to Customers
    </a>
    <div class="customer-profile-hero">
        <div class="customer-profile-identity"><span class="customer-profile-avatar" aria-hidden="true">{{ $initials ?: '?' }}</span><div><div class="customer-profile-eyebrow">{{ $customer->customer_code }}</div><h1>{{ $customer->customer_name }}</h1><div class="customer-profile-badges"><span class="tag {{ $customer->status === 'active' ? 'tag--success' : 'tag--muted' }}">{{ ucfirst($customer->status) }}</span><span class="tag {{ $customer->credit_enabled ? 'tag--accent' : 'tag--muted' }}">Credit {{ $customer->credit_enabled ? 'enabled' : 'disabled' }}</span></div></div></div>
        <div class="page-actions"><a class="btn btn-secondary" href="{{ route('accounts-receivable.customers.show', $customer) }}">Receivable Account</a><a class="btn btn-primary" href="{{ route('customers.edit', $customer) }}">Edit Customer</a></div>
    </div>

    <section class="customer-credit-overview section-spaced" aria-labelledby="customerCreditOverviewTitle">
        <div class="customer-section-heading">
            <div><h2 id="customerCreditOverviewTitle">Credit overview</h2><p>Current credit position and purchasing capacity.</p></div>
            <span class="customer-credit-state"><i class="bi {{ $customer->credit_enabled ? 'bi-check-circle' : 'bi-dash-circle' }}" aria-hidden="true"></i>Credit {{ $customer->credit_enabled ? 'enabled' : 'disabled' }}</span>
        </div>
        <div class="grid-4 customer-credit-metrics">
            <article class="card metric-card customer-credit-metric">
                <div class="customer-metric-head"><span class="customer-metric-icon"><i class="bi bi-wallet2" aria-hidden="true"></i></span><span class="metric-label">Credit Limit</span></div>
                <div class="metric-value">&#8369;{{ number_format($limit, 2) }}</div><div class="metric-note">Maximum approved credit</div>
            </article>
            <article class="card metric-card customer-credit-metric">
                <div class="customer-metric-head"><span class="customer-metric-icon customer-metric-icon--attention"><i class="bi bi-receipt" aria-hidden="true"></i></span><span class="metric-label">Outstanding Balance</span></div>
                <div class="metric-value">&#8369;{{ number_format($outstanding, 2) }}</div><div class="metric-note">Still due from this customer</div>
            </article>
            <article class="card metric-card customer-credit-metric">
                <div class="customer-metric-head"><span class="customer-metric-icon customer-metric-icon--available"><i class="bi bi-credit-card" aria-hidden="true"></i></span><span class="metric-label">Available Credit</span></div>
                <div class="metric-value">&#8369;{{ number_format($available, 2) }}</div><div class="metric-note">Available for new credit sales</div>
            </article>
            <article class="card metric-card customer-credit-metric customer-credit-metric--usage">
                <div class="customer-metric-head"><span class="customer-metric-icon"><i class="bi bi-pie-chart" aria-hidden="true"></i></span><span class="metric-label">Credit Usage</span></div>
                <div class="customer-usage-line"><div class="metric-value">{{ $usage }}%</div><span>{{ $usage >= 80 ? 'Near limit' : ($usage > 0 ? 'In use' : 'Unused') }}</span></div>
                <div class="customer-credit-progress" role="progressbar" aria-label="Credit usage" aria-valuenow="{{ $usage }}" aria-valuemin="0" aria-valuemax="100"><span style="width:{{ $usage }}%"></span></div>
                <div class="metric-note">&#8369;{{ number_format($outstanding, 2) }} used of &#8369;{{ number_format($limit, 2) }}</div>
            </article>
        </div>
    </section>

    <div class="customer-overview-grid section-spaced">
        <section class="card customer-info-card"><div class="card-header"><span class="card-title">Contact Information</span></div><dl class="customer-detail-list"><dt>Phone</dt><dd>{{ $customer->phone ?: '—' }}</dd><dt>Email</dt><dd>{{ $customer->email ?: '—' }}</dd><dt>Address</dt><dd>{{ $customer->address ?: '—' }}</dd></dl></section>
        <section class="card customer-info-card"><div class="card-header"><span class="card-title">Account Settings</span></div><dl class="customer-detail-list"><dt>Status</dt><dd><span class="tag {{ $customer->status === 'active' ? 'tag--success' : 'tag--muted' }}">{{ ucfirst($customer->status) }}</span></dd><dt>Credit Access</dt><dd><span class="tag {{ $customer->credit_enabled ? 'tag--success' : 'tag--muted' }}">{{ $customer->credit_enabled ? 'Enabled' : 'Disabled' }}</span></dd><dt>Default Term</dt><dd>{{ $customer->default_credit_term_days }} days</dd></dl><div class="customer-account-action"><form method="POST" action="{{ route('customers.status', $customer) }}" data-confirm data-confirm-title="Change customer status" data-confirm-message="{{ $customer->status === 'active' ? 'Deactivate' : 'Activate' }} {{ $customer->customer_name }}?" data-confirm-button="Confirm">@csrf @method('PATCH')<input type="hidden" name="status" value="{{ $customer->status === 'active' ? 'inactive' : 'active' }}"><button type="submit" class="btn btn-secondary btn-sm">{{ $customer->status === 'active' ? 'Deactivate Customer' : 'Activate Customer' }}</button></form></div></section>
    </div>

    <section class="card customer-history-card section-spaced">
        <div class="card-header"><div><span class="card-title">Credit Transactions</span><span class="customer-card-caption">Open and settled credit sales for this customer.</span></div><span class="tag tag--accent">{{ $creditTransactions->total() }} transactions</span></div>
        <div class="table-wrap" tabindex="0" role="region" aria-label="Customer credit transactions"><table><thead><tr><th>Receipt</th><th>Sale Date</th><th>Due Date</th><th>Status</th><th class="text-right">Net</th><th class="text-right">Paid</th><th class="text-right">Balance</th><th>Actions</th></tr></thead><tbody>
        @forelse($creditTransactions as $transaction)
            @php($displayStatus = $receivables->statusForRow($transaction))
            <tr><td><a class="receipt-code" href="{{ route('pos.receipt', $transaction) }}">{{ $transaction->receipt_no }}</a></td><td>{{ $transaction->date->format('M d, Y') }}</td><td>{{ $transaction->creditSale->due_date->format('M d, Y') }}</td><td><span class="status {{ $displayStatus === 'overdue' ? 'status-danger' : ($displayStatus === 'paid' ? 'status-active' : 'status-low') }}">{{ ucfirst(str_replace('_', ' ', $displayStatus)) }}</span></td><td class="text-right">&#8369;{{ number_format((float) $transaction->ar_net_amount, 2) }}</td><td class="text-right">&#8369;{{ number_format((float) $transaction->ar_paid_amount, 2) }}</td><td class="text-right customer-history-balance">&#8369;{{ number_format((float) $transaction->ar_balance_amount, 2) }}</td><td><div class="table-actions">@if((float) $transaction->ar_balance_amount > 0)<a class="btn btn-primary btn-sm" href="{{ route('pos.credit-payments.create', $transaction) }}">Record Payment</a>@endif<a class="btn btn-secondary btn-sm" href="{{ route('pos.receipt', $transaction) }}">Receipt</a></div></td></tr>
        @empty<tr><x-empty-state colspan="8" title="No credit transactions" message="Credit sales for this customer will appear here." /></tr>@endforelse
        </tbody></table></div><div class="view-blades-footer">{{ $creditTransactions->links() }}</div>
    </section>

    <section class="card customer-history-card">
        <div class="card-header"><div><span class="card-title">Payment History</span><span class="customer-card-caption">Collections received for this customer's credit sales.</span></div><span class="tag tag--success">{{ $payments->total() }} payments</span></div>
        <div class="table-wrap" tabindex="0" role="region" aria-label="Customer payment history"><table><thead><tr><th>Payment Date</th><th>Receipt</th><th>Method</th><th>Reference</th><th>Received By</th><th>Notes</th><th class="text-right">Amount</th><th>Details</th></tr></thead><tbody>
        @forelse($payments as $payment)
            <tr>
                <td>{{ $payment->paid_at->format('M d, Y h:i A') }}</td>
                <td><span class="receipt-code">{{ $payment->transaction?->receipt_no }}</span></td>
                <td><span class="tag tag--muted">{{ $payment->paymentMethod?->name ?? 'Unknown' }}</span></td>
                <td>{{ $payment->payment_reference ?: '—' }}</td>
                <td>{{ $payment->receivedBy?->name ?? 'Unknown' }}</td>
                <td class="customer-payment-notes">{{ $payment->notes ?: '—' }}</td>
                <td class="text-right customer-payment-amount">&#8369;{{ number_format((float) $payment->amount, 2) }}</td>
                <td><a class="btn btn-secondary btn-sm" title="View Receipt Details" href="{{ route('pos.credit-payments.show', $payment) }}"><i class="bi bi-eye" aria-hidden="true"></i></a></td>
            </tr>
        @empty<tr><x-empty-state colspan="8" title="No payment history" message="Recorded credit payments will appear here." /></tr>@endforelse
        </tbody></table></div><div class="view-blades-footer">{{ $payments->links() }}</div>
    </section>
</div>
@endsection

@push('styles')
@vite('resources/css/views/customers-1.css')
@endpush
