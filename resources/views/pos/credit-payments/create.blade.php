@extends('layouts.app')
@section('title', 'Record Credit Payment')
@section('page-label', 'Record Credit Payment')

@section('content')
@php
    $outstanding = $transaction->outstandingBalance();
@endphp
<div class="credit-payment-shell">
    <x-page-header title="Record Payment" subtitle="Apply a collection to an existing Credit Sale without creating another sale.">
        <x-slot:actions>
            <a href="{{ route('pos.receipt', $transaction) }}" class="btn btn-secondary">View Receipt</a>
            <a href="{{ route('pos.transactions') }}" class="btn btn-secondary">Transactions</a>
        </x-slot:actions>
    </x-page-header>

    <div class="credit-payment-grid">
        <section class="card credit-payment-summary-card">
            <div class="card-header"><span class="card-title"><i class="bi bi-receipt" aria-hidden="true"></i> Credit Sale Details</span></div>
            <div class="credit-payment-balance">
                <span>Outstanding balance</span>
                <strong>&#8369;{{ number_format($outstanding, 2) }}</strong>
                <small>Receipt {{ $transaction->receipt_no }}</small>
            </div>
            <dl class="credit-payment-details">
                <div><dt>Customer</dt><dd>{{ $transaction->customer?->customer_code }} - {{ $transaction->customer?->customer_name }}</dd></div>
                <div><dt>Receipt</dt><dd>{{ $transaction->receipt_no }}</dd></div>
                <div><dt>Sale Date</dt><dd>{{ $transaction->date->format('M d, Y') }}</dd></div>
                <div><dt>Due Date</dt><dd>{{ $transaction->creditSale->due_date->format('M d, Y') }}</dd></div>
                <div><dt>Net Total</dt><dd>&#8369;{{ number_format($transaction->netTotal(), 2) }}</dd></div>
                <div><dt>Total Paid</dt><dd>&#8369;{{ number_format($transaction->totalPaid(), 2) }}</dd></div>
                @php($paymentStatus = app(\App\Services\TransactionSettlementService::class)->paymentStatus($transaction))
                <div><dt>Payment Status</dt><dd><span class="status status-{{ $paymentStatus === 'overdue' ? 'danger' : 'low' }}">{{ ucfirst(str_replace('_', ' ', $paymentStatus)) }}</span></dd></div>
            </dl>
        </section>

        <section class="card credit-payment-form-card">
            <div class="card-header"><div><span class="card-title"><i class="bi bi-wallet2" aria-hidden="true"></i> New Payment</span><span class="credit-payment-caption">Enter the amount received and how the customer paid.</span></div></div>
            <form method="POST" action="{{ route('pos.credit-payments.store', $transaction) }}" class="credit-payment-form">
                @csrf
                <div class="form-group">
                    <div class="credit-payment-label-row"><label for="amount">Payment Amount *</label><button type="button" class="credit-payment-full" id="payFullBalance" data-amount="{{ number_format($outstanding, 2, '.', '') }}">Pay full balance</button></div>
                    <span class="credit-payment-money"><span aria-hidden="true">&#8369;</span><input id="amount" name="amount" type="number" min="0.01" max="{{ number_format($outstanding, 2, '.', '') }}" step="0.01" value="{{ old('amount') }}" placeholder="0.00" required></span>
                    <small class="credit-payment-help">Maximum: &#8369;{{ number_format($outstanding, 2) }}</small>
                    @error('amount')<span class="field-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label for="payment_method">Settlement Payment Method *</label>
                    <select id="payment_method" name="payment_method" required>
                        <option value="">Select payment method</option>
                        @foreach($paymentMethods as $method)
                        <option value="{{ $method->code }}" @selected(old('payment_method') === $method->code)>{{ $method->name }}</option>
                        @endforeach
                    </select>
                    <small class="credit-payment-help" id="creditPaymentMethodHelp">Choose how the payment was received.</small>
                    @error('payment_method')<span class="field-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group credit-payment-detail-panel" id="creditPaymentReferenceGroup" hidden>
                    <div class="credit-payment-detail-title"><i class="bi bi-phone" aria-hidden="true"></i><span><strong>GCash Details</strong><small>Use the reference from the customer’s successful transfer.</small></span></div>
                    <label for="payment_reference" id="creditPaymentReferenceLabel">GCash Reference Number *</label>
                    <input id="payment_reference" name="payment_reference" type="text" maxlength="100" value="{{ old('payment_reference') }}" placeholder="e.g. 1234567890123">
                    @error('payment_reference')<span class="field-error">{{ $message }}</span>@enderror
                </div>
                <fieldset class="credit-payment-detail-panel" id="creditPaymentCheckGroup" @if(old('payment_method') !== 'check') hidden @endif>
                    <legend class="credit-payment-detail-title"><i class="bi bi-bank" aria-hidden="true"></i><span><strong>Check Details</strong><small>Record the check information before accepting payment.</small></span></legend>
                    <div class="credit-payment-check-grid">
                        <div class="form-group"><label for="check_number">Check Number *</label><input id="check_number" name="check_number" type="text" maxlength="100" value="{{ old('check_number') }}" placeholder="Enter check number">@error('check_number')<span class="field-error">{{ $message }}</span>@enderror</div>
                        <div class="form-group"><label for="check_bank">Bank *</label><input id="check_bank" name="check_bank" type="text" maxlength="100" value="{{ old('check_bank') }}" placeholder="Enter issuing bank">@error('check_bank')<span class="field-error">{{ $message }}</span>@enderror</div>
                        <div class="form-group credit-payment-check-date"><label for="check_date">Check Date *</label><input id="check_date" name="check_date" type="date" value="{{ old('check_date') }}">@error('check_date')<span class="field-error">{{ $message }}</span>@enderror</div>
                    </div>
                </fieldset>
                <div class="form-group">
                    <label for="notes">Notes</label>
                    <textarea id="notes" name="notes" maxlength="1000" rows="4" placeholder="Optional collection notes">{{ old('notes') }}</textarea>
                    @error('notes')<span class="field-error">{{ $message }}</span>@enderror
                </div>
                @error('transaction')<div class="checkout-feedback is-error">{{ $message }}</div>@enderror
                <label class="credit-payment-confirm"><input type="checkbox" id="creditPaymentConfirm" required><span>I confirm the amount and payment details are correct.</span></label>
                <button type="submit" class="btn btn-success w-full credit-payment-submit"><i class="bi bi-check-circle" aria-hidden="true"></i> Record Payment</button>
            </form>
        </section>
    </div>

    <section class="card credit-payment-history-card">
        <div class="card-header"><span class="card-title">Payment History</span></div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Date</th><th>Method</th><th>Payment details</th><th>Received By</th><th>Notes</th><th class="text-right">Amount</th></tr></thead>
                <tbody>
                @forelse($transaction->payments->sortByDesc('paid_at') as $payment)
                    <tr>
                        <td>{{ $payment->paid_at->format('M d, Y h:i A') }}</td>
                        <td>{{ $payment->paymentMethod?->name ?? 'Unknown' }}</td>
                        <td>@if($payment->paymentMethod?->code === 'check')<div class="credit-payment-table-detail"><strong>{{ $payment->check_number ?: '-' }}</strong><span>{{ $payment->check_bank ?: 'Bank not recorded' }} @if($payment->check_date)&middot; {{ $payment->check_date->format('M d, Y') }}@endif</span></div>@else{{ $payment->payment_reference ?: '-' }}@endif</td>
                        <td>{{ $payment->receivedBy?->name ?? 'Unknown' }}</td>
                        <td>{{ $payment->notes ?: '-' }}</td>
                        <td class="text-right fw-bold">&#8369;{{ number_format((float) $payment->amount, 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="empty-state">No payments have been recorded.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection

@push('styles')
@vite('resources/css/views/credit-payments-1.css')
@endpush

@push('scripts')
@vite('resources/js/views/credit-payments-1.js')
@endpush
