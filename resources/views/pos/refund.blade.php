@extends('layouts.app')
@section('title', 'Return / Refund &mdash; ' . $transaction->receipt_no)
@section('page-label', 'Return & Refund')

@section('content')
@php
    $alreadyRefunded = $transaction->totalRefunded();
    $remaining       = $transaction->total - $alreadyRefunded;
    $isFullyRefunded = $transaction->status === 'refunded';
    $refundConfig    = [
        'subtotal' => $transaction->subtotal,
        'discount' => $transaction->discount,
        'remaining' => $remaining,
        'has_line_discounts' => $transaction->items->sum('discount') > 0,
    ];
    $transactionDate = \Carbon\Carbon::parse($transaction->date->format('Y-m-d').' '.$transaction->time);
    $isCreditSale = (bool) $transaction->creditSale;
    $totalPaid = $isCreditSale ? $transaction->totalPaid() : 0;
    $outstanding = $isCreditSale ? $transaction->outstandingBalance() : 0;
@endphp

<div class="refund-page">
    <div class="refund-head">
        <a href="{{ route('pos.transactions') }}" class="refund-back-link" aria-label="Back to transactions">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                stroke-linecap="round" stroke-linejoin="round">
                <path d="M19 12H5"/><path d="m12 19-7-7 7-7"/>
            </svg>
        </a>
        <div class="refund-head__copy">
            <div class="refund-head__title">Return / Refund</div>
            <div class="refund-head__meta">Receipt <strong>{{ $transaction->receipt_no }}</strong> &middot; {{ $transactionDate->format('M d, Y h:i A') }}</div>
        </div>
        <a href="{{ route('pos.receipt', $transaction) }}" class="btn btn-secondary btn-sm refund-receipt-link"><x-icon name="receipts" /> View Receipt</a>
        @if($isFullyRefunded)
        <span class="status status-inactive refund-status">Fully Refunded</span>
        @elseif($transaction->status === 'partial_refund')
        <span class="status status-low refund-status">Partial Refund</span>
        @endif
    </div>

    <div class="refund-context" aria-label="Transaction summary">
        <div><span>Cashier</span><strong>{{ $transaction->cashier?->name ?? 'Unknown' }}</strong></div>
        <div><span>Payment</span><strong>{{ \App\Models\Transaction::paymentMethodLabel($transaction->payment_method) }}</strong></div>
        <div><span>Original Total</span><strong>&#8369;{{ number_format($transaction->total, 2) }}</strong></div>
        <div><span>Refundable Balance</span><strong>&#8369;{{ number_format($remaining, 2) }}</strong></div>
        @if($isCreditSale)<div><span>Outstanding Balance</span><strong>&#8369;{{ number_format($outstanding, 2) }}</strong></div>@endif
    </div>

    @if($errors->any())
    <div class="alert alert-error">
        <strong>Errors:</strong>
        <ul class="refund-error-list">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </div>
    @endif

    @if($isFullyRefunded)
    <div class="alert alert-error refund-empty">
        <div class="fw-bold">This transaction has already been fully refunded.</div>
        <div class="security-note">Total refunded: &#8369;{{ number_format($alreadyRefunded, 2) }}</div>
    </div>
    @else
    <form method="POST" action="{{ route('pos.refund.process', $transaction) }}" id="refundForm">
        @csrf
        <div class="refund-grid">
            <div class="card">
                <div class="card-header">
                    <div>
                        <span class="card-title"><x-icon name="products" /> Items to Return</span>
                        <span class="refund-selection-status" id="refundSelectionStatus" aria-live="polite">No items selected</span>
                    </div>
                    <div class="flex gap-1">
                        <button type="button" onclick="selectAll()" class="btn btn-secondary btn-sm">All</button>
                        <button type="button" onclick="selectNone()" class="btn btn-secondary btn-sm">None</button>
                    </div>
                </div>
                <div class="refund-items-body">
                    @foreach($transaction->items as $index => $item)
                    @php
                        $refundedQty = $transaction->refunds->flatMap->items->where('transaction_item_id', $item->id)->sum('quantity');
                        $availableQty = max(0, $item->quantity - $refundedQty);
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
                    <div class="refund-item-row {{ $availableQty <= 0 ? 'is-unavailable' : '' }}" data-price="{{ $item->unit_price }}" data-discount="{{ $item->discount }}" data-original-qty="{{ $item->quantity }}" data-max="{{ $availableQty }}">
                        <div class="refund-item-layout">
                            <input type="hidden" name="items[{{ $index }}][transaction_item_id]" value="{{ $item->id }}">

                            <div class="refund-item-main">
                                <div class="refund-item-name">{{ $productName }}</div>
                                @if($variantDetails)
                                <div class="refund-item-meta">{{ $variantDetails }}</div>
                                @endif
                                <div class="refund-item-meta">
                                    &#8369;{{ number_format($item->unit_price, 2) }} &times; {{ $item->quantity }}
                                    = &#8369;{{ number_format($item->lineTotal(), 2) }}
                                    @if($refundedQty > 0)
                                    &nbsp;&bull;&nbsp;<span class="text-danger">{{ $refundedQty }} already refunded</span>
                                    @endif
                                </div>
                            </div>

                            @if($availableQty > 0)
                            <div class="refund-item-side">
                                <div class="refund-qty-row">
                                    <label class="refund-inline-note" for="refundQty{{ $item->id }}">Return qty</label>
                                    <div class="refund-stepper">
                                        <button type="button" onclick="adjustQty(this,-1)" class="refund-step-btn" aria-label="Decrease return quantity for {{ $productName }}">&minus;</button>
                                        <input type="number" id="refundQty{{ $item->id }}" name="items[{{ $index }}][quantity]" value="{{ old("items.$index.quantity", 0) }}"
                                               min="0" max="{{ $availableQty }}"
                                               class="refund-qty-input"
                                               onchange="recalcRefund()" oninput="recalcRefund()">
                                        <button type="button" onclick="adjustQty(this,1)" class="refund-step-btn" aria-label="Increase return quantity for {{ $productName }}">+</button>
                                    </div>
                                    <span class="refund-inline-note">/ {{ $availableQty }}</span>
                                </div>
                                <label class="refund-restock-option">
                                    <input type="hidden" name="items[{{ $index }}][restock]" value="0">
                                    <input type="checkbox" name="items[{{ $index }}][restock]" value="1" {{ old("items.$index.restock", '1') ? 'checked' : '' }}>
                                    <span><strong>Return to stock</strong><small>Item is suitable for resale</small></span>
                                </label>
                            </div>
                            @else
                            <div class="refund-returned-tag">Already returned</div>
                            <input type="hidden" name="items[{{ $index }}][quantity]" value="0">
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="refund-side">
                <div class="card refund-panel">
                    <div class="refund-kicker">Refund Summary</div>

                    <div class="refund-row">
                        <span>Original Total</span><span>&#8369;{{ number_format($transaction->total, 2) }}</span>
                    </div>
                    @if($alreadyRefunded > 0)
                    <div class="refund-row refund-row--danger">
                        <span>Already Refunded</span><span>&minus; &#8369;{{ number_format($alreadyRefunded, 2) }}</span>
                    </div>
                    <div class="refund-row refund-row--border">
                        <span>Remaining</span><span>&#8369;{{ number_format($remaining, 2) }}</span>
                    </div>
                    @endif

                    <div class="refund-total-box">
                        <div class="refund-total-label">Refund Amount</div>
                        <div id="refundAmountDisplay" class="refund-total-amount">&#8369;0.00</div>
                    </div>
                </div>

                <div class="card refund-panel">
                    <div class="refund-kicker">Refund Details</div>

                    @if($isCreditSale)
                    <div class="alert alert-info refund-credit-guidance">
                        Returns reduce the outstanding receivable first. Only the portion exceeding the remaining debt is reimbursed to the customer.
                        Current applied payments: <strong>&#8369;{{ number_format($totalPaid, 2) }}</strong>.
                    </div>
                    @endif

                    <div class="form-group">
                        <label>Reason *</label>
                        <select name="reason" required>
                            <option value="">&mdash; Select reason &mdash;</option>
                            @foreach(['Defective / Damaged item', 'Wrong item delivered', 'Item not as described', 'Customer changed mind', 'Duplicate purchase', 'Other'] as $reason)
                            <option value="{{ $reason }}" {{ old('reason') === $reason ? 'selected' : '' }}>{{ $reason === 'Other' ? 'Other (see notes)' : $reason }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>{{ $isCreditSale ? 'Reimburse Excess Via' : 'Refund Via' }}</label>
                        <select name="refund_method" @if(!$isCreditSale) required @endif>
                            @foreach(['cash' => 'Cash', 'gcash' => 'GCash', 'check' => 'Check'] as $value => $label)
                            <option value="{{ $value }}" {{ old('refund_method', 'cash') === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @if($isCreditSale)<small class="security-note">Used only when the return creates an amount owed back to the customer.</small>@endif
                    </div>

                    <div class="form-group mb-1">
                        <label>Notes (optional)</label>
                        <textarea name="notes" rows="3" maxlength="1000" placeholder="Additional remarks...">{{ old('notes') }}</textarea>
                    </div>
                </div>

                <button type="submit" id="refundBtn" class="btn btn-danger w-full refund-submit" disabled>
                    <span class="refund-submit-spinner" aria-hidden="true"></span>
                    <span data-refund-button-text>Process Refund</span>
                </button>
            </div>
        </div>
    </form>
    @endif
</div>

@push('styles')
@vite('resources/css/views/pos-refund-1.css')
@endpush

@push('scripts')
<script type="application/json" id="refundConfig">@json($refundConfig)</script>
@vite('resources/js/views/pos-refund-1.js')
@endpush
@endsection
