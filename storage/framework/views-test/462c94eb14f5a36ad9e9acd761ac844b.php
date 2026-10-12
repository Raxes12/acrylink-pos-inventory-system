<?php $__env->startSection('title', 'Return / Refund &mdash; ' . $transaction->receipt_no); ?>
<?php $__env->startSection('page-label', 'Return & Refund'); ?>

<?php $__env->startSection('content'); ?>
<?php
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
?>

<div class="refund-page">
    <div class="refund-head">
        <a href="<?php echo e(route('pos.transactions')); ?>" class="refund-back-link" aria-label="Back to transactions">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                stroke-linecap="round" stroke-linejoin="round">
                <path d="M19 12H5"/><path d="m12 19-7-7 7-7"/>
            </svg>
        </a>
        <div class="refund-head__copy">
            <div class="refund-head__title">Return / Refund</div>
            <div class="refund-head__meta">Receipt <strong><?php echo e($transaction->receipt_no); ?></strong> &middot; <?php echo e($transactionDate->format('M d, Y h:i A')); ?></div>
        </div>
        <a href="<?php echo e(route('pos.receipt', $transaction)); ?>" class="btn btn-secondary btn-sm refund-receipt-link"><?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'receipts']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'receipts']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?> View Receipt</a>
        <?php if($isFullyRefunded): ?>
        <span class="status status-inactive refund-status">Fully Refunded</span>
        <?php elseif($transaction->status === 'partial_refund'): ?>
        <span class="status status-low refund-status">Partial Refund</span>
        <?php endif; ?>
    </div>

    <div class="refund-context" aria-label="Transaction summary">
        <div><span>Cashier</span><strong><?php echo e($transaction->cashier?->name ?? 'Unknown'); ?></strong></div>
        <div><span>Payment</span><strong><?php echo e(\App\Models\Transaction::paymentMethodLabel($transaction->payment_method)); ?></strong></div>
        <div><span>Original Total</span><strong>&#8369;<?php echo e(number_format($transaction->total, 2)); ?></strong></div>
        <div><span>Refundable Balance</span><strong>&#8369;<?php echo e(number_format($remaining, 2)); ?></strong></div>
        <?php if($isCreditSale): ?><div><span>Outstanding Balance</span><strong>&#8369;<?php echo e(number_format($outstanding, 2)); ?></strong></div><?php endif; ?>
    </div>

    <?php if($errors->any()): ?>
    <div class="alert alert-error">
        <strong>Errors:</strong>
        <ul class="refund-error-list">
            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><?php echo e($e); ?></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
    </div>
    <?php endif; ?>

    <?php if($isFullyRefunded): ?>
    <div class="alert alert-error refund-empty">
        <div class="fw-bold">This transaction has already been fully refunded.</div>
        <div class="security-note">Total refunded: &#8369;<?php echo e(number_format($alreadyRefunded, 2)); ?></div>
    </div>
    <?php else: ?>
    <form method="POST" action="<?php echo e(route('pos.refund.process', $transaction)); ?>" id="refundForm">
        <?php echo csrf_field(); ?>
        <div class="refund-grid">
            <div class="card">
                <div class="card-header">
                    <div>
                        <span class="card-title"><?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'products']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'products']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?> Items to Return</span>
                        <span class="refund-selection-status" id="refundSelectionStatus" aria-live="polite">No items selected</span>
                    </div>
                    <div class="flex gap-1">
                        <button type="button" onclick="selectAll()" class="btn btn-secondary btn-sm">All</button>
                        <button type="button" onclick="selectNone()" class="btn btn-secondary btn-sm">None</button>
                    </div>
                </div>
                <div class="refund-items-body">
                    <?php $__currentLoopData = $transaction->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
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
                    ?>
                    <div class="refund-item-row <?php echo e($availableQty <= 0 ? 'is-unavailable' : ''); ?>" data-price="<?php echo e($item->unit_price); ?>" data-discount="<?php echo e($item->discount); ?>" data-original-qty="<?php echo e($item->quantity); ?>" data-max="<?php echo e($availableQty); ?>">
                        <div class="refund-item-layout">
                            <input type="hidden" name="items[<?php echo e($index); ?>][transaction_item_id]" value="<?php echo e($item->id); ?>">

                            <div class="refund-item-main">
                                <div class="refund-item-name"><?php echo e($productName); ?></div>
                                <?php if($variantDetails): ?>
                                <div class="refund-item-meta"><?php echo e($variantDetails); ?></div>
                                <?php endif; ?>
                                <div class="refund-item-meta">
                                    &#8369;<?php echo e(number_format($item->unit_price, 2)); ?> &times; <?php echo e($item->quantity); ?>

                                    = &#8369;<?php echo e(number_format($item->lineTotal(), 2)); ?>

                                    <?php if($refundedQty > 0): ?>
                                    &nbsp;&bull;&nbsp;<span class="text-danger"><?php echo e($refundedQty); ?> already refunded</span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <?php if($availableQty > 0): ?>
                            <div class="refund-item-side">
                                <div class="refund-qty-row">
                                    <label class="refund-inline-note" for="refundQty<?php echo e($item->id); ?>">Return qty</label>
                                    <div class="refund-stepper">
                                        <button type="button" onclick="adjustQty(this,-1)" class="refund-step-btn" aria-label="Decrease return quantity for <?php echo e($productName); ?>">&minus;</button>
                                        <input type="number" id="refundQty<?php echo e($item->id); ?>" name="items[<?php echo e($index); ?>][quantity]" value="<?php echo e(old("items.$index.quantity", 0)); ?>"
                                               min="0" max="<?php echo e($availableQty); ?>"
                                               class="refund-qty-input"
                                               onchange="recalcRefund()" oninput="recalcRefund()">
                                        <button type="button" onclick="adjustQty(this,1)" class="refund-step-btn" aria-label="Increase return quantity for <?php echo e($productName); ?>">+</button>
                                    </div>
                                    <span class="refund-inline-note">/ <?php echo e($availableQty); ?></span>
                                </div>
                                <label class="refund-restock-option">
                                    <input type="hidden" name="items[<?php echo e($index); ?>][restock]" value="0">
                                    <input type="checkbox" name="items[<?php echo e($index); ?>][restock]" value="1" <?php echo e(old("items.$index.restock", '1') ? 'checked' : ''); ?>>
                                    <span><strong>Return to stock</strong><small>Item is suitable for resale</small></span>
                                </label>
                            </div>
                            <?php else: ?>
                            <div class="refund-returned-tag">Already returned</div>
                            <input type="hidden" name="items[<?php echo e($index); ?>][quantity]" value="0">
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>

            <div class="refund-side">
                <div class="card refund-panel">
                    <div class="refund-kicker">Refund Summary</div>

                    <div class="refund-row">
                        <span>Original Total</span><span>&#8369;<?php echo e(number_format($transaction->total, 2)); ?></span>
                    </div>
                    <?php if($alreadyRefunded > 0): ?>
                    <div class="refund-row refund-row--danger">
                        <span>Already Refunded</span><span>&minus; &#8369;<?php echo e(number_format($alreadyRefunded, 2)); ?></span>
                    </div>
                    <div class="refund-row refund-row--border">
                        <span>Remaining</span><span>&#8369;<?php echo e(number_format($remaining, 2)); ?></span>
                    </div>
                    <?php endif; ?>

                    <div class="refund-total-box">
                        <div class="refund-total-label">Refund Amount</div>
                        <div id="refundAmountDisplay" class="refund-total-amount">&#8369;0.00</div>
                    </div>
                </div>

                <div class="card refund-panel">
                    <div class="refund-kicker">Refund Details</div>

                    <?php if($isCreditSale): ?>
                    <div class="alert alert-info refund-credit-guidance">
                        Returns reduce the outstanding receivable first. Only the portion exceeding the remaining debt is reimbursed to the customer.
                        Current applied payments: <strong>&#8369;<?php echo e(number_format($totalPaid, 2)); ?></strong>.
                    </div>
                    <?php endif; ?>

                    <div class="form-group">
                        <label>Reason *</label>
                        <select name="reason" required>
                            <option value="">&mdash; Select reason &mdash;</option>
                            <?php $__currentLoopData = ['Defective / Damaged item', 'Wrong item delivered', 'Item not as described', 'Customer changed mind', 'Duplicate purchase', 'Other']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $reason): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($reason); ?>" <?php echo e(old('reason') === $reason ? 'selected' : ''); ?>><?php echo e($reason === 'Other' ? 'Other (see notes)' : $reason); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label><?php echo e($isCreditSale ? 'Reimburse Excess Via' : 'Refund Via'); ?></label>
                        <select name="refund_method" <?php if(!$isCreditSale): ?> required <?php endif; ?>>
                            <?php $__currentLoopData = ['cash' => 'Cash', 'gcash' => 'GCash', 'check' => 'Check']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($value); ?>" <?php echo e(old('refund_method', 'cash') === $value ? 'selected' : ''); ?>><?php echo e($label); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <?php if($isCreditSale): ?><small class="security-note">Used only when the return creates an amount owed back to the customer.</small><?php endif; ?>
                    </div>

                    <div class="form-group mb-1">
                        <label>Notes (optional)</label>
                        <textarea name="notes" rows="3" maxlength="1000" placeholder="Additional remarks..."><?php echo e(old('notes')); ?></textarea>
                    </div>
                </div>

                <button type="submit" id="refundBtn" class="btn btn-danger w-full refund-submit" disabled>
                    <span class="refund-submit-spinner" aria-hidden="true"></span>
                    <span data-refund-button-text>Process Refund</span>
                </button>
            </div>
        </div>
    </form>
    <?php endif; ?>
</div>

<?php $__env->startPush('styles'); ?>
<?php echo app('Illuminate\Foundation\Vite')('resources/css/views/pos-refund-1.css'); ?>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<script type="application/json" id="refundConfig"><?php echo json_encode($refundConfig, 15, 512) ?></script>
<?php echo app('Illuminate\Foundation\Vite')('resources/js/views/pos-refund-1.js'); ?>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\acrylink_newbie\resources\views\pos\refund.blade.php ENDPATH**/ ?>