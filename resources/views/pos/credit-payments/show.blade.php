@extends('layouts.app')
@section('title', 'Payment Acknowledgment')
@section('page-label', 'Payment Acknowledgment')

@section('content')
@php
    $transaction = $transactionPayment->transaction;
    $paymentStatus = app(\App\Services\TransactionSettlementService::class)->paymentStatus($transaction);
    $remainingBalance = $transaction->outstandingBalance();
    $totalPaid = $transaction->totalPaid();
    $balanceBeforePayment = $remainingBalance + (float) $transactionPayment->amount;
    $acknowledgmentNumber = 'CP-'.str_pad((string) $transactionPayment->id, 6, '0', STR_PAD_LEFT);
    $receiptSettings = app(\App\Services\SystemSettingsService::class)->all();
@endphp

<div class="credit-payment-shell">
    <a href="{{ route('pos.credit-payments.create', $transaction) }}" class="editor-breadcrumb credit-payment-back">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
            stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M19 12H5"/><path d="m12 19-7-7 7-7"/>
        </svg>
        Back to Credit Sale
    </a>
    <div class="receipt-screen-header">
        <div>
            <h1>Payment Acknowledgment</h1>
            <p>{{ $acknowledgmentNumber }} &middot; Original receipt {{ $transaction->receipt_no }}</p>
        </div>
        <div class="receipt-actions">
            @if($remainingBalance > 0)
            <a href="{{ route('pos.credit-payments.create', $transaction) }}" class="btn btn-secondary">Record Another Payment</a>
            @endif
            <button type="button" class="btn btn-primary" onclick="window.print()">
                <x-icon name="receipts" /> Print Acknowledgment
            </button>
        </div>
    </div>

    <article class="credit-payment-ack" id="paymentAcknowledgment" aria-label="Credit payment acknowledgment">
        <header class="ack-header">
            <img src="{{ asset('logo/Acry-Link_Logo.ico') }}" class="ack-logo" width="58" height="58" alt="{{ $receiptSettings['business.name'] }} logo">
            <div>
                <strong>{{ $receiptSettings['business.name'] }}</strong>
                <span>{{ $receiptSettings['business.address'] }}</span>
            </div>
        </header>

        <div class="ack-title-block">
            <span>Credit Payment</span>
            <h2>ACKNOWLEDGMENT RECEIPT</h2>
            <div class="ack-number">No. {{ $acknowledgmentNumber }}</div>
        </div>

        <div class="ack-status-row">
            <div><span>Date received</span><strong>{{ $transactionPayment->paid_at->format('M d, Y') }}</strong></div>
            <div><span>Time</span><strong>{{ $transactionPayment->paid_at->format('h:i A') }}</strong></div>
            <span class="ack-status ack-status-{{ str_replace('_', '-', $paymentStatus) }}">{{ ucfirst(str_replace('_', ' ', $paymentStatus)) }}</span>
        </div>

        <section class="ack-received" aria-label="Payment amount and customer">
            <span>Received from</span>
            <strong>{{ $transaction->customer?->customer_name ?? 'Walk-in Customer' }}</strong>
            @if($transaction->customer?->customer_code)<small>Customer {{ $transaction->customer->customer_code }}</small>@endif
            <div class="ack-amount">
                <span>Amount received</span>
                <strong>&#8369;{{ number_format((float) $transactionPayment->amount, 2) }}</strong>
            </div>
        </section>

        <section class="ack-section" aria-labelledby="ack-payment-details">
            <h3 id="ack-payment-details">Payment Details</h3>
            <dl>
                <div><dt>Payment ID</dt><dd>#{{ $transactionPayment->id }}</dd></div>
                <div><dt>Paid At</dt><dd>{{ $transactionPayment->paid_at->format('M d, Y h:i A') }}</dd></div>
                <div><dt>Payment Method</dt><dd>{{ $transactionPayment->paymentMethod?->name ?? 'Unknown' }}</dd></div>
                @if($transactionPayment->payment_reference)
                <div><dt>Payment Reference</dt><dd>{{ $transactionPayment->payment_reference }}</dd></div>
                @endif
            @if($transactionPayment->paymentMethod?->code === 'check')
                <div><dt>Check Number</dt><dd>{{ $transactionPayment->check_number ?: '-' }}</dd></div>
                <div><dt>Bank</dt><dd>{{ $transactionPayment->check_bank ?: '-' }}</dd></div>
                <div><dt>Check Date</dt><dd>{{ $transactionPayment->check_date?->format('M d, Y') ?: '-' }}</dd></div>
            @endif
                <div><dt>Received By</dt><dd>{{ $transactionPayment->receivedBy?->name ?? 'Unknown' }}</dd></div>
                @if($transactionPayment->notes)<div><dt>Notes</dt><dd>{{ $transactionPayment->notes }}</dd></div>@endif
            </dl>
        </section>

        <section class="ack-section" aria-labelledby="ack-account-summary">
            <h3 id="ack-account-summary">Credit Account Summary</h3>
            <dl>
                <div><dt>Original Receipt Number</dt><dd>{{ $transaction->receipt_no }}</dd></div>
                <div><dt>Sale Date</dt><dd>{{ $transaction->date->format('M d, Y') }}</dd></div>
                <div><dt>Due Date</dt><dd>{{ $transaction->creditSale->due_date->format('M d, Y') }}</dd></div>
                <div><dt>Balance Before Payment</dt><dd>&#8369;{{ number_format($balanceBeforePayment, 2) }}</dd></div>
                <div><dt>Less: This Payment</dt><dd class="ack-paid">- &#8369;{{ number_format((float) $transactionPayment->amount, 2) }}</dd></div>
                <div><dt>Total Paid to Date</dt><dd>&#8369;{{ number_format($totalPaid, 2) }}</dd></div>
                <div class="ack-balance"><dt>Remaining Balance</dt><dd>&#8369;{{ number_format($remainingBalance, 2) }}</dd></div>
            </dl>
        </section>

        <div class="ack-signatures">
            <div><span>{{ $transactionPayment->receivedBy?->name ?? 'Authorized Representative' }}</span><strong>Received by</strong></div>
            <div><span>&nbsp;</span><strong>Customer signature</strong></div>
        </div>

        <footer>
            <strong>Thank you for your payment.</strong>
            <span>This document acknowledges payment toward the credit sale shown above.</span>
            <span>System-generated on {{ $transactionPayment->paid_at->format('M d, Y h:i A') }}.</span>
        </footer>
    </article>
</div>
@endsection

@push('styles')
@vite('resources/css/views/credit-payments-1.css')
@endpush
