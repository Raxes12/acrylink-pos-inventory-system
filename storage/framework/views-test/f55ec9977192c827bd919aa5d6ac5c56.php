<?php $__env->startSection('title', 'Receipt - ' . $transaction->receipt_no); ?>
<?php $__env->startSection('page-label', 'Receipt'); ?>

<?php $__env->startSection('content'); ?>
<?php
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
?>
<a href="<?php echo e(route('pos.transactions')); ?>" class="editor-breadcrumb">
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
            <p><?php echo e($transaction->receipt_no); ?> &middot; <?php echo e($transactionDate->format('M d, Y h:i A')); ?></p>
        </div>
        <div class="receipt-actions">
            <?php if($canRefund): ?>
            <a href="<?php echo e(route('pos.refund.form', $transaction)); ?>" class="btn btn-secondary">
                <?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'transactions']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'transactions']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?> Return Items
            </a>
            <?php endif; ?>
            <a href="<?php echo e(route('pos.index')); ?>" class="btn btn-secondary"><?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'pos']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'pos']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?> New Sale</a>
            <button type="button" onclick="window.print()" class="btn btn-primary"><?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
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
<?php endif; ?> Print Receipt</button>
        </div>
    </div>

    <div id="receipt" class="receipt-paper">
        <?php if($isRefunded || $isPartial): ?>
        <div class="receipt-status <?php echo e($isRefunded ? 'receipt-status-danger' : 'receipt-status-warning'); ?>">
            <?php echo e($isRefunded ? 'Refunded' : 'Partial Refund'); ?>

        </div>
        <?php endif; ?>

        <header class="receipt-header">
            <img src="<?php echo e(asset('logo/vmc-logo.ico')); ?>" class="receipt-logo" width="64" height="64" alt="<?php echo e($receiptSettings['business.name']); ?> logo">
            <div class="receipt-company"><?php echo e($receiptSettings['business.name']); ?></div>
            <?php if($receiptSettings['business.address']): ?><div><?php echo e($receiptSettings['business.address']); ?></div><?php endif; ?>
            <?php if($receiptSettings['business.email']): ?><div><?php echo e($receiptSettings['business.email']); ?></div><?php endif; ?>
            <?php if($receiptSettings['business.contact_number']): ?><div><?php echo e($receiptSettings['business.contact_number']); ?></div><?php endif; ?>
        </header>

        <?php if($receiptSettings['receipt.show_cashier']): ?><div class="receipt-served-by">Served by <?php echo e($transaction->cashier?->name ?? 'Cashier'); ?></div><?php endif; ?>

        <table class="receipt-table">
            <thead class="receipt-column-head">
                <tr><th scope="col">Item</th><th scope="col" class="money">Amount</th></tr>
            </thead>
            <tbody>
            <?php $__currentLoopData = $transaction->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
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
                <tr>
                    <td>
                        <div class="item-name"><?php echo e($productName); ?></div>
                        <?php if($variantDetails): ?><div class="item-code"><?php echo e($variantDetails); ?></div><?php endif; ?>
                        <div class="item-calculation"><?php echo e($item->quantity); ?> &times; <?php echo e(number_format($item->unit_price, 2)); ?></div>
                        <?php if((float) $item->discount > 0): ?><div class="item-code">Item discount: -<?php echo e(number_format($item->discount, 2)); ?></div><?php endif; ?>
                    </td>
                    <td class="money"><?php echo e(number_format($item->netLineTotal(), 2)); ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>

        <div class="receipt-item-count"><?php echo e(number_format($itemCount)); ?> item<?php echo e($itemCount === 1 ? '' : 's'); ?></div>

        <section class="receipt-summary">
            <?php if($transaction->discount > 0): ?>
            <div class="summary-row"><span>Subtotal</span><strong><?php echo e(number_format($transaction->subtotal, 2)); ?></strong></div>
            <div class="summary-row discount"><span>Discount</span><strong>-<?php echo e(number_format($transaction->discount, 2)); ?></strong></div>
            <?php endif; ?>
            <div class="summary-total"><span>TOTAL</span><strong>&#8369; <?php echo e(number_format($transaction->total, 2)); ?></strong></div>
            <?php if($receiptSettings['receipt.show_payment_method']): ?><div class="summary-payment"><span><?php echo e($paymentMethod === 'cash' ? 'Cash' : $paymentLabel); ?></span><strong><?php echo e(number_format($transaction->payment, 2)); ?></strong></div><?php endif; ?>
            <?php if($paymentMethod === 'cash'): ?>
            <div class="summary-change"><span>CHANGE</span><strong>&#8369; <?php echo e(number_format($transaction->change_due, 2)); ?></strong></div>
            <?php endif; ?>
            <?php if($totalRefunded > 0): ?>
            <div class="summary-row refund-value"><span>Refunded</span><strong>-<?php echo e(number_format($totalRefunded, 2)); ?></strong></div>
            <div class="summary-row net-value"><span>Net Sale</span><strong>&#8369;<?php echo e(number_format($transaction->netTotal(), 2)); ?></strong></div>
            <?php endif; ?>
        </section>

        <?php if($receiptSettings['receipt.show_payment_method'] && $paymentMethod === 'gcash'): ?>
        <section class="receipt-payment-note">
            <strong>GCash Reference:</strong> <?php echo e($transaction->payment_reference ?? '-'); ?>

        </section>
        <?php endif; ?>

        <?php if($receiptSettings['receipt.show_payment_method'] && $paymentMethod === 'credit'): ?>
        <section class="receipt-payment-note">
            <div><strong>Card Reference:</strong> <?php echo e($transaction->payment_reference ?? '-'); ?></div>
            <div><strong>Card Type:</strong> <?php echo e($transaction->payment_card_type ? ucfirst($transaction->payment_card_type) : '-'); ?></div>
            <div><strong>Card Number:</strong> <?php echo e($transaction->payment_card_last4 ? '**** '.$transaction->payment_card_last4 : '-'); ?></div>
            <?php if($transaction->payment_payer_name): ?><div><strong>Cardholder:</strong> <?php echo e($transaction->payment_payer_name); ?></div><?php endif; ?>
        </section>
        <?php endif; ?>

        <?php if($isCreditSale): ?>
        <section class="receipt-payment-note">
            <div class="receipt-note-title">Credit Sale</div>
            <?php if($receiptSettings['receipt.show_customer']): ?><div><strong>Customer:</strong> <?php echo e($transaction->customer?->customer_code); ?> - <?php echo e($transaction->customer?->customer_name); ?></div><?php endif; ?>
            <?php if($receiptSettings['receipt.show_payment_method']): ?><div><strong>Payment Method:</strong> Credit Sale</div><?php endif; ?>
            <div><strong>Sale Total:</strong> &#8369; <?php echo e(number_format((float) $transaction->total, 2)); ?></div>
            <div><strong>Initial Paid Amount:</strong> &#8369; <?php echo e(number_format($initialPaidAmount, 2)); ?></div>
            <div><strong>Current Total Paid:</strong> &#8369; <?php echo e(number_format($currentTotalPaid, 2)); ?></div>
            <div><strong>Outstanding Balance:</strong> &#8369; <?php echo e(number_format($currentOutstanding, 2)); ?></div>
            <div><strong>Credit Term:</strong> <?php echo e($transaction->creditSale->credit_term_days); ?> days</div>
            <div><strong>Due Date:</strong> <?php echo e($transaction->creditSale->due_date->format('M d, Y')); ?></div>
            <div><strong>Payment Status:</strong> <?php echo e(ucfirst(str_replace('_', ' ', $currentPaymentStatus))); ?></div>
            <?php if($receiptSettings['receipt.show_payment_method'] && $initialPaidAmount > 0 && $transaction->payments->isNotEmpty()): ?>
            <div><strong>Initial Payment Method:</strong> <?php echo e($transaction->payments->first()->paymentMethod?->name ?? 'Payment'); ?></div>
            <?php if($transaction->payments->first()->payment_reference): ?><div><strong>Initial Payment Reference:</strong> <?php echo e($transaction->payments->first()->payment_reference); ?></div><?php endif; ?>
            <?php endif; ?>
        </section>
        <?php endif; ?>

        <?php if($receiptSettings['receipt.show_payment_method'] && $paymentMethod === 'check'): ?>
        <section class="receipt-payment-note">
            <div class="receipt-note-title">Check Details</div>
            <div><strong>Check No.:</strong> <?php echo e($transaction->check_number ?? '-'); ?></div>
            <div><strong>Bank:</strong> <?php echo e($transaction->check_bank ?? '-'); ?></div>
            <div><strong>Check Date:</strong> <?php echo e($transaction->check_date?->format('M d, Y') ?? '-'); ?></div>
        </section>
        <?php endif; ?>

        <?php if($transaction->refunds->isNotEmpty()): ?>
        <section class="receipt-refunds">
            <div class="receipt-section-title">REFUND HISTORY</div>
            <?php $__currentLoopData = $transaction->refunds; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $refund): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="receipt-refund-entry">
                <div>
                    <strong><?php echo e($refund->refund_no); ?></strong>
                    <span><?php echo e($refund->created_at->format('M d, Y h:i A')); ?></span>
                    <span><?php echo e($refund->reason); ?></span>
                    <?php if($transaction->creditSale && (float) $refund->receivable_adjustment_amount > 0): ?>
                    <span>Receivable reduction: &#8369;<?php echo e(number_format((float) $refund->receivable_adjustment_amount, 2)); ?></span>
                    <?php endif; ?>
                    <?php if($transaction->creditSale && (float) $refund->reimbursement_amount > 0): ?>
                    <span>Customer reimbursement: &#8369;<?php echo e(number_format((float) $refund->reimbursement_amount, 2)); ?> via <?php echo e(\App\Models\PaymentMethod::labelFor($refund->refund_method)); ?></span>
                    <?php endif; ?>
                </div>
                <strong>-<?php echo e(number_format($refund->refund_amount, 2)); ?></strong>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </section>
        <?php endif; ?>

        <footer class="receipt-footer">
            <strong>THANK YOU FOR YOUR PURCHASE</strong>
            <span><?php echo e($receiptSettings['receipt.footer_message']); ?></span>
            <span class="receipt-order">Order <?php echo e($transaction->receipt_no); ?></span>
            <span><?php echo e($transactionDate->format('M d, Y h:i A')); ?></span>
        </footer>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<?php echo app('Illuminate\Foundation\Vite')('resources/css/views/pos-receipt-1.css'); ?>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<?php echo app('Illuminate\Foundation\Vite')('resources/js/views/pos-receipt-1.js'); ?>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\acrylink_newbie\resources\views\pos\receipt.blade.php ENDPATH**/ ?>