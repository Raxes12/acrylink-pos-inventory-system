@extends('layouts.app')
@section('title', 'Receipt - ' . $transaction->receipt_no)
@section('page-label', 'Receipt')

@section('content')
@php
    $receiptSettings = app(\App\Services\SystemSettingsService::class)->all();
    $status = $transaction->status ?? 'completed';
    $isRefunded = $status === 'refunded';
    $isPartial = $status === 'partial_refund';
    $paymentMethod = $transaction->payment_method ?? 'cash';
    $paymentLabel = \App\Models\Transaction::paymentMethodLabel($paymentMethod);
    $transactionDate = \Carbon\Carbon::parse($transaction->date->format('Y-m-d').' '.$transaction->time);
    $itemCount = $transaction->items->sum('quantity');
    $totalRefunded = $transaction->totalRefunded();
    $canRefund = auth()->user()?->can('refund', $transaction) && in_array($status, ['completed', 'partial_refund'], true);
    $isCreditSale = $paymentMethod === 'credit_account' && $transaction->creditSale;
    $initialPaidAmount = $isCreditSale ? min((float) $transaction->payment, (float) $transaction->total) : 0;
    $currentTotalPaid = $isCreditSale ? $transaction->totalPaid() : 0;
    $currentOutstanding = $isCreditSale ? $transaction->outstandingBalance() : 0;
    $currentPaymentStatus = $isCreditSale
        ? app(\App\Services\TransactionSettlementService::class)->paymentStatus($transaction)
        : $transaction->payment_status;
@endphp
<a href="{{ route('pos.transactions') }}" class="editor-breadcrumb">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
        stroke-linecap="round" stroke-linejoin="round">
        <path d="M19 12H5"/><path d="m12 19-7-7 7-7"/>
    </svg>
    Back to Transactions
</a>
<div class="receipt-shell">
    <div class="receipt-screen-header">
        <div>
            <h1>Sales Receipt</h1>
            <p>{{ $transaction->receipt_no }} &middot; {{ $transactionDate->format('M d, Y h:i A') }}</p>
        </div>
        <div class="receipt-actions">
            @if($canRefund)
            <a href="{{ route('pos.refund.form', $transaction) }}" class="btn btn-secondary">
                <x-icon name="transactions" /> Return Items
            </a>
            @endif
            <a href="{{ route('pos.index') }}" class="btn btn-secondary"><x-icon name="pos" /> New Sale</a>
            <button type="button" onclick="window.print()" class="btn btn-primary"><x-icon name="receipts" /> Print Receipt</button>
        </div>
    </div>

    <div id="receipt" class="receipt-paper">
        @if($isRefunded || $isPartial)
        <div class="receipt-status {{ $isRefunded ? 'receipt-status-danger' : 'receipt-status-warning' }}">
            {{ $isRefunded ? 'Refunded' : 'Partial Refund' }}
        </div>
        @endif

        <header class="receipt-header">
            <img src="{{ app(\App\Services\SystemSettingsService::class)->businessLogoUrl() }}" class="receipt-logo" width="64" height="64" alt="{{ $receiptSettings['business.name'] }} logo">
            <div class="receipt-company">{{ $receiptSettings['business.name'] }}</div>
            @if($receiptSettings['business.address'])<div>{{ $receiptSettings['business.address'] }}</div>@endif
            @if($receiptSettings['business.email'])<div>{{ $receiptSettings['business.email'] }}</div>@endif
            @if($receiptSettings['business.contact_number'])<div>{{ $receiptSettings['business.contact_number'] }}</div>@endif
        </header>

        @if($receiptSettings['receipt.show_cashier'])<div class="receipt-served-by">Served by {{ $transaction->cashier?->name ?? 'Cashier' }}</div>@endif
        @if($transaction->sales_associate)<div class="receipt-served-by">Sales assisted by {{ $transaction->sales_associate }}</div>@endif

        <table class="receipt-table">
            <thead class="receipt-column-head">
                <tr><th scope="col">Item</th><th scope="col" class="money">Amount</th></tr>
            </thead>
            <tbody>
            @foreach($transaction->items as $item)
                @php
                    $productName = $item->product_name_snapshot ?: $item->product_name;
                    $variantDetails = collect([
                        $item->part_number_snapshot,
                        $item->brand_snapshot,
                        $item->model_snapshot,
                        $item->size_snapshot,
                        $item->color_snapshot,
                        $item->unit_snapshot ? 'Unit: '.$item->unit_snapshot : null,
                    ])->filter()->join(' / ');
                @endphp
                <tr>
                    <td>
                        <div class="item-name">{{ $productName }}</div>
                        @if($variantDetails)<div class="item-code">{{ $variantDetails }}</div>@endif
                        <div class="item-calculation">{{ $item->quantity }} &times; {{ number_format($item->unit_price, 2) }}</div>
                        @if((float) $item->discount > 0)<div class="item-code">Item discount: -{{ number_format($item->discount, 2) }}</div>@endif
                    </td>
                    <td class="money">{{ number_format($item->netLineTotal(), 2) }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>

        <div class="receipt-item-count">{{ number_format($itemCount) }} item{{ $itemCount === 1 ? '' : 's' }}</div>

        <section class="receipt-summary">
            @if($transaction->discount > 0)
            <div class="summary-row"><span>Subtotal</span><strong>{{ number_format($transaction->subtotal, 2) }}</strong></div>
            <div class="summary-row discount"><span>Discount</span><strong>-{{ number_format($transaction->discount, 2) }}</strong></div>
            @endif
            <div class="summary-total"><span>TOTAL</span><strong>&#8369; {{ number_format($transaction->total, 2) }}</strong></div>
            @if($receiptSettings['receipt.show_payment_method'])<div class="summary-payment"><span>{{ $paymentMethod === 'cash' ? 'Cash' : $paymentLabel }}</span><strong>{{ number_format($transaction->payment, 2) }}</strong></div>@endif
            @if($paymentMethod === 'cash')
            <div class="summary-change"><span>CHANGE</span><strong>&#8369; {{ number_format($transaction->change_due, 2) }}</strong></div>
            @endif
            @if($totalRefunded > 0)
            <div class="summary-row refund-value"><span>Refunded</span><strong>-{{ number_format($totalRefunded, 2) }}</strong></div>
            <div class="summary-row net-value"><span>Net Sale</span><strong>&#8369;{{ number_format($transaction->netTotal(), 2) }}</strong></div>
            @endif
        </section>

        @if($receiptSettings['receipt.show_payment_method'] && $paymentMethod === 'gcash')
        <section class="receipt-payment-note">
            <strong>GCash Reference:</strong> {{ $transaction->payment_reference ?? '-' }}
        </section>
        @endif

        @if($receiptSettings['receipt.show_payment_method'] && $paymentMethod === 'credit')
        <section class="receipt-payment-note">
            <div><strong>Card Reference:</strong> {{ $transaction->payment_reference ?? '-' }}</div>
            <div><strong>Card Type:</strong> {{ $transaction->payment_card_type ? ucfirst($transaction->payment_card_type) : '-' }}</div>
            <div><strong>Card Number:</strong> {{ $transaction->payment_card_last4 ? '**** '.$transaction->payment_card_last4 : '-' }}</div>
            @if($transaction->payment_payer_name)<div><strong>Cardholder:</strong> {{ $transaction->payment_payer_name }}</div>@endif
        </section>
        @endif

        @if($isCreditSale)
        <section class="receipt-payment-note">
            <div class="receipt-note-title">Credit Sale</div>
            @if($receiptSettings['receipt.show_customer'])<div><strong>Customer:</strong> {{ $transaction->customer?->customer_code }} - {{ $transaction->customer?->customer_name }}</div>@endif
            @if($receiptSettings['receipt.show_payment_method'])<div><strong>Payment Method:</strong> Credit Sale</div>@endif
            <div><strong>Sale Total:</strong> &#8369; {{ number_format((float) $transaction->total, 2) }}</div>
            <div><strong>Initial Paid Amount:</strong> &#8369; {{ number_format($initialPaidAmount, 2) }}</div>
            <div><strong>Current Total Paid:</strong> &#8369; {{ number_format($currentTotalPaid, 2) }}</div>
            <div><strong>Outstanding Balance:</strong> &#8369; {{ number_format($currentOutstanding, 2) }}</div>
            <div><strong>Credit Term:</strong> {{ $transaction->creditSale->credit_term_days }} days</div>
            <div><strong>Due Date:</strong> {{ $transaction->creditSale->due_date->format('M d, Y') }}</div>
            <div><strong>Payment Status:</strong> {{ ucfirst(str_replace('_', ' ', $currentPaymentStatus)) }}</div>
            @if($receiptSettings['receipt.show_payment_method'] && $initialPaidAmount > 0 && $transaction->payments->isNotEmpty())
            <div><strong>Initial Payment Method:</strong> {{ $transaction->payments->first()->paymentMethod?->name ?? 'Payment' }}</div>
            @if($transaction->payments->first()->payment_reference)<div><strong>Initial Payment Reference:</strong> {{ $transaction->payments->first()->payment_reference }}</div>@endif
            @endif
        </section>
        @endif

        @if($receiptSettings['receipt.show_payment_method'] && $paymentMethod === 'check')
        <section class="receipt-payment-note">
            <div class="receipt-note-title">Check Details</div>
            <div><strong>Check No.:</strong> {{ $transaction->check_number ?? '-' }}</div>
            <div><strong>Bank:</strong> {{ $transaction->check_bank ?? '-' }}</div>
            <div><strong>Check Date:</strong> {{ $transaction->check_date?->format('M d, Y') ?? '-' }}</div>
        </section>
        @endif

        @if($transaction->refunds->isNotEmpty())
        <section class="receipt-refunds">
            <div class="receipt-section-title">REFUND HISTORY</div>
            @foreach($transaction->refunds as $refund)
            <div class="receipt-refund-entry">
                <div>
                    <strong>{{ $refund->refund_no }}</strong>
                    <span>{{ $refund->created_at->format('M d, Y h:i A') }}</span>
                    <span>{{ $refund->reason }}</span>
                    @if($transaction->creditSale && (float) $refund->receivable_adjustment_amount > 0)
                    <span>Receivable reduction: &#8369;{{ number_format((float) $refund->receivable_adjustment_amount, 2) }}</span>
                    @endif
                    @if($transaction->creditSale && (float) $refund->reimbursement_amount > 0)
                    <span>Customer reimbursement: &#8369;{{ number_format((float) $refund->reimbursement_amount, 2) }} via {{ \App\Models\PaymentMethod::labelFor($refund->refund_method) }}</span>
                    @endif
                </div>
                <strong>-{{ number_format($refund->refund_amount, 2) }}</strong>
            </div>
            @endforeach
        </section>
        @endif

        <footer class="receipt-footer">
            <strong>THANK YOU FOR YOUR PURCHASE</strong>
            <span>{{ $receiptSettings['receipt.footer_message'] }}</span>
            <span class="receipt-order">Order {{ $transaction->receipt_no }}</span>
            <span>{{ $transactionDate->format('M d, Y h:i A') }}</span>
        </footer>
    </div>
</div>
@endsection

@push('styles')
@vite('resources/css/views/pos-receipt-1.css')
@endpush

@push('scripts')
@vite('resources/js/views/pos-receipt-1.js')
@endpush
