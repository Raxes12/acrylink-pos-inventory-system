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
        'is_credit_sale' => (bool) $transaction->creditSale,
        'outstanding' => $transaction->creditSale ? $transaction->outstandingBalance() : 0,
    ];
    $transactionDate = \Carbon\Carbon::parse($transaction->date->format('Y-m-d').' '.$transaction->time);
    $isCreditSale = (bool) $transaction->creditSale;
    $totalPaid = $isCreditSale ? $transaction->totalPaid() : 0;
    $outstanding = $isCreditSale ? $transaction->outstandingBalance() : 0;
    $initialStep = $errors->has('reason') || $errors->has('refund_method') || $errors->has('notes') ? 2 : 1;
    $refundConfig['initial_step'] = $initialStep;
@endphp

<div class="refund-page">
    <header class="refund-head">
        <a href="{{ route('pos.transactions') }}" class="refund-back-link" aria-label="Back to transactions">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                stroke-linecap="round" stroke-linejoin="round">
                <path d="M19 12H5"/><path d="m12 19-7-7 7-7"/>
            </svg>
        </a>
        <div class="refund-head__copy">
            <span class="refund-eyebrow">Sales adjustment</span>
            <h1 class="refund-head__title">Return Items</h1>
            <div class="refund-head__meta"><span class="refund-receipt-code">{{ $transaction->receipt_no }}</span><span>{{ $transactionDate->format('M d, Y') }}</span><span>{{ $transactionDate->format('h:i A') }}</span></div>
        </div>
        <a href="{{ route('pos.receipt', $transaction) }}" class="btn btn-secondary btn-sm refund-receipt-link"><x-icon name="receipts" /> View Receipt</a>
        @if($isFullyRefunded)
        <span class="status status-inactive refund-status">Fully Refunded</span>
        @elseif($transaction->status === 'partial_refund')
        <span class="status status-low refund-status">Partial Refund</span>
        @endif
    </header>

    <div class="refund-context" aria-label="Transaction summary">
        <div><span class="refund-context-icon"><i class="bi bi-person" aria-hidden="true"></i></span><span>Cashier</span><strong>{{ $transaction->cashier?->name ?? 'Unknown' }}</strong></div>
        <div><span class="refund-context-icon"><i class="bi bi-wallet2" aria-hidden="true"></i></span><span>Payment</span><strong>{{ \App\Models\Transaction::paymentMethodLabel($transaction->payment_method) }}</strong></div>
        <div><span class="refund-context-icon"><i class="bi bi-receipt" aria-hidden="true"></i></span><span>Original Total</span><strong>&#8369;{{ number_format($transaction->total, 2) }}</strong></div>
        <div class="refund-context-highlight"><span class="refund-context-icon"><i class="bi bi-arrow-counterclockwise" aria-hidden="true"></i></span><span>Refundable Balance</span><strong>&#8369;{{ number_format($remaining, 2) }}</strong></div>
        @if($isCreditSale)<div><span class="refund-context-icon"><i class="bi bi-credit-card" aria-hidden="true"></i></span><span>Outstanding Balance</span><strong>&#8369;{{ number_format($outstanding, 2) }}</strong></div>@endif
    </div>

    <ol class="refund-steps" aria-label="Return process" data-refund-steps>
        <li class="{{ $initialStep === 1 ? 'is-active' : 'is-complete' }}" data-refund-step-indicator="1"><button type="button" data-refund-go-step="1" aria-current="{{ $initialStep === 1 ? 'step' : 'false' }}"><span>1</span><div><strong>Select items</strong><small>Choose quantities to return</small></div></button></li>
        <li class="{{ $initialStep === 2 ? 'is-active' : '' }}" data-refund-step-indicator="2"><button type="button" data-refund-go-step="2" aria-current="{{ $initialStep === 2 ? 'step' : 'false' }}" @disabled($initialStep < 2)><span>2</span><div><strong>Add details</strong><small>Reason and refund method</small></div></button></li>
        <li data-refund-step-indicator="3"><button type="button" data-refund-go-step="3" aria-current="false" disabled><span>3</span><div><strong>Review &amp; confirm</strong><small>Verify the calculated amount</small></div></button></li>
    </ol>

    @if($errors->any())
    <div class="alert alert-error">
        <strong>Errors:</strong>
        <ul class="refund-error-list">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </div>
    @endif

    @if($isFullyRefunded)
    <div class="card refund-empty">
        <span class="refund-empty-icon"><i class="bi bi-check2-circle" aria-hidden="true"></i></span>
        <div><strong>This transaction is fully refunded</strong><p>All eligible items and value have already been returned.</p><span>Total refunded: &#8369;{{ number_format($alreadyRefunded, 2) }}</span></div>
        <a href="{{ route('pos.receipt', $transaction) }}" class="btn btn-secondary">View receipt</a>
    </div>
    @else
    <form method="POST" action="{{ route('pos.refund.process', $transaction) }}" id="refundForm" data-current-step="{{ $initialStep }}" data-confirm data-confirm-title="Confirm return and refund" data-confirm-message="Review the selected items and refund amount before continuing." data-confirm-button="Process refund">
        @csrf
        <div class="refund-grid">
            <section class="card refund-items-card refund-step-panel" aria-labelledby="refundItemsTitle" data-refund-step-panel="1" @if($initialStep !== 1) hidden @endif>
                <div class="card-header">
                    <div>
                        <span class="card-title" id="refundItemsTitle"><span class="refund-section-icon"><x-icon name="products" /></span> Items to Return</span>
                        <span class="refund-selection-status" id="refundSelectionStatus" aria-live="polite">No items selected</span>
                    </div>
                    <div class="refund-selection-actions">
                        <button type="button" class="btn btn-secondary btn-sm" data-refund-select-all><i class="bi bi-check2-square" aria-hidden="true"></i>Select all</button>
                        <button type="button" class="btn btn-secondary btn-sm" data-refund-select-none><i class="bi bi-x-square" aria-hidden="true"></i>Clear</button>
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
                    <article class="refund-item-row {{ $availableQty <= 0 ? 'is-unavailable' : '' }}" data-refund-item data-price="{{ $item->unit_price }}" data-discount="{{ $item->discount }}" data-original-qty="{{ $item->quantity }}" data-max="{{ $availableQty }}">
                        <div class="refund-item-layout">
                            <input type="hidden" name="items[{{ $index }}][transaction_item_id]" value="{{ $item->id }}">

                            <div class="refund-item-main">
                                <div class="refund-item-title-line"><div class="refund-item-name">{{ $productName }}</div><span class="refund-item-availability">{{ $availableQty }} available</span></div>
                                @if($variantDetails)
                                <div class="refund-item-meta">{{ $variantDetails }}</div>
                                @endif
                                <div class="refund-item-pricing">
                                    <span><small>Unit price</small><strong>&#8369;{{ number_format($item->unit_price, 2) }}</strong></span>
                                    <span><small>Purchased</small><strong>{{ $item->quantity }}</strong></span>
                                    <span><small>Line total</small><strong>&#8369;{{ number_format($item->lineTotal(), 2) }}</strong></span>
                                    @if($refundedQty > 0)
                                    <span class="refund-prior-return"><small>Previously returned</small><strong>{{ $refundedQty }}</strong></span>
                                    @endif
                                </div>
                            </div>

                            @if($availableQty > 0)
                            <div class="refund-item-side">
                                <div class="refund-qty-row">
                                    <label class="refund-inline-note" for="refundQty{{ $item->id }}">Return qty</label>
                                    <div class="refund-stepper">
                                        <button type="button" class="refund-step-btn" data-refund-qty-delta="-1" aria-label="Decrease return quantity for {{ $productName }}">&minus;</button>
                                        <input type="number" id="refundQty{{ $item->id }}" name="items[{{ $index }}][quantity]" value="{{ old("items.$index.quantity", 0) }}"
                                               min="0" max="{{ $availableQty }}"
                                               class="refund-qty-input" inputmode="numeric">
                                        <button type="button" class="refund-step-btn" data-refund-qty-delta="1" aria-label="Increase return quantity for {{ $productName }}">+</button>
                                    </div>
                                    <span class="refund-inline-note">/ {{ $availableQty }}</span>
                                </div>
                                <label class="refund-restock-option">
                                    <input type="hidden" name="items[{{ $index }}][restock]" value="0">
                                    <input type="checkbox" name="items[{{ $index }}][restock]" value="1" {{ old("items.$index.restock", '1') ? 'checked' : '' }}>
                                    <span><strong>Return to inventory</strong><small>Enable only when the item can be resold</small></span>
                                </label>
                            </div>
                            @else
                            <div class="refund-returned-tag">Already returned</div>
                            <input type="hidden" name="items[{{ $index }}][quantity]" value="0">
                            @endif
                        </div>
                    </article>
                    @endforeach
                </div>
                <footer class="refund-step-actions refund-step-actions--end">
                    <span><i class="bi bi-info-circle" aria-hidden="true"></i>Select the exact quantity the customer is returning.</span>
                    <button type="button" class="btn btn-primary" data-refund-next-step="2" disabled>Continue to details <i class="bi bi-arrow-right" aria-hidden="true"></i></button>
                </footer>
            </section>

            <div class="refund-side">
                <section class="card refund-panel refund-summary-panel refund-step-panel" aria-labelledby="refundSummaryTitle" data-refund-step-panel="3" hidden>
                    <div class="refund-panel-heading">
                        <span class="refund-section-icon"><i class="bi bi-calculator" aria-hidden="true"></i></span>
                        <div><h2 id="refundSummaryTitle">Refund Summary</h2><p id="refundSummarySelection">Select an item to calculate the refund.</p></div>
                    </div>

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

                    @if($isCreditSale)
                    <div class="refund-impact" aria-label="Credit return impact">
                        <div><span>Receivable reduction</span><strong id="refundReceivableDisplay">&#8369;0.00</strong></div>
                        <div><span>Customer reimbursement</span><strong id="refundReimbursementDisplay">&#8369;0.00</strong></div>
                    </div>
                    @endif
                    <div class="refund-review-details">
                        <div><span>Selected items</span><strong id="refundReviewItems">&mdash;</strong></div>
                        <div><span>Return reason</span><strong id="refundReviewReason">&mdash;</strong></div>
                        <div><span>{{ $isCreditSale ? 'Excess reimbursement' : 'Refund method' }}</span><strong id="refundReviewMethod">&mdash;</strong></div>
                    </div>
                    <footer class="refund-step-actions">
                        <button type="button" class="btn btn-secondary" data-refund-back-step="2"><i class="bi bi-arrow-left" aria-hidden="true"></i>Back</button>
                        <button type="submit" id="refundBtn" class="btn btn-danger refund-submit" disabled>
                            <span class="refund-submit-spinner" aria-hidden="true"></span>
                            <i class="bi bi-arrow-counterclockwise refund-submit-icon" aria-hidden="true"></i>
                            <span data-refund-button-text>Process Refund</span>
                        </button>
                    </footer>
                    <p class="refund-submit-help" id="refundSubmitHelp" role="status" aria-live="polite">Review the return before processing.</p>
                </section>

                <section class="card refund-panel refund-details-panel refund-step-panel" aria-labelledby="refundDetailsTitle" data-refund-step-panel="2" @if($initialStep !== 2) hidden @endif>
                    <div class="refund-panel-heading">
                        <span class="refund-section-icon"><i class="bi bi-card-checklist" aria-hidden="true"></i></span>
                        <div><h2 id="refundDetailsTitle">Return Details</h2><p>Required information for the audit record.</p></div>
                    </div>

                    @if($isCreditSale)
                    <div class="alert alert-info refund-credit-guidance">
                        <i class="bi bi-info-circle" aria-hidden="true"></i>
                        <span>Returns reduce the outstanding receivable first. Any excess is reimbursed using the selected method. Applied payments: <strong>&#8369;{{ number_format($totalPaid, 2) }}</strong>.</span>
                    </div>
                    @endif

                    <div class="form-group">
                        <label for="refundReason">Return reason <span aria-hidden="true">*</span></label>
                        <select name="reason" id="refundReason" required aria-describedby="refundReasonHelp">
                            <option value="">&mdash; Select reason &mdash;</option>
                            @foreach(['Defective / Damaged item', 'Wrong item delivered', 'Item not as described', 'Customer changed mind', 'Duplicate purchase', 'Other'] as $reason)
                            <option value="{{ $reason }}" {{ old('reason') === $reason ? 'selected' : '' }}>{{ $reason === 'Other' ? 'Other (see notes)' : $reason }}</option>
                            @endforeach
                        </select>
                        <small id="refundReasonHelp" class="refund-field-help">Choose the reason that best describes this return.</small>
                    </div>

                    <div class="form-group">
                        <label for="refundMethod">{{ $isCreditSale ? 'Reimburse excess via' : 'Refund method' }}</label>
                        <select name="refund_method" id="refundMethod" @if(!$isCreditSale) required @endif>
                            @foreach(['cash' => 'Cash', 'gcash' => 'GCash', 'check' => 'Check'] as $value => $label)
                            <option value="{{ $value }}" {{ old('refund_method', 'cash') === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @if($isCreditSale)<small class="refund-field-help">Used only when the return creates an amount owed back to the customer.</small>@endif
                    </div>

                    <div class="form-group mb-1">
                        <label for="refundNotes">Notes <span class="refund-optional">Optional</span></label>
                        <textarea name="notes" id="refundNotes" rows="3" maxlength="1000" placeholder="Condition of the item or other helpful details">{{ old('notes') }}</textarea>
                    </div>
                    <footer class="refund-step-actions">
                        <button type="button" class="btn btn-secondary" data-refund-back-step="1"><i class="bi bi-arrow-left" aria-hidden="true"></i>Back</button>
                        <button type="button" class="btn btn-primary" data-refund-next-step="3" disabled>Review return <i class="bi bi-arrow-right" aria-hidden="true"></i></button>
                    </footer>
                </section>
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
