
<?php $__env->startSection('title', 'Payment Acknowledgment'); ?>
<?php $__env->startSection('page-label', 'Payment Acknowledgment'); ?>

<?php $__env->startSection('content'); ?>
<?php
    $transaction = $transactionPayment->transaction;
    $paymentStatus = app(\App\Services\TransactionSettlementService::class)->paymentStatus($transaction);
    $remainingBalance = $transaction->outstandingBalance();
    $totalPaid = $transaction->totalPaid();
    $balanceBeforePayment = $remainingBalance + (float) $transactionPayment->amount;
    $acknowledgmentNumber = 'CP-'.str_pad((string) $transactionPayment->id, 6, '0', STR_PAD_LEFT);
    $receiptSettings = app(\App\Services\SystemSettingsService::class)->all();
?>

<div class="credit-payment-shell">
    <a href="<?php echo e(route('pos.credit-payments.create', $transaction)); ?>" class="editor-breadcrumb credit-payment-back">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
            stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M19 12H5"/><path d="m12 19-7-7 7-7"/>
        </svg>
        Back to Credit Sale
    </a>
    <div class="receipt-screen-header">
        <div>
            <h1>Payment Acknowledgment</h1>
            <p><?php echo e($acknowledgmentNumber); ?> &middot; Original receipt <?php echo e($transaction->receipt_no); ?></p>
        </div>
        <div class="receipt-actions">
            <?php if($remainingBalance > 0): ?>
            <a href="<?php echo e(route('pos.credit-payments.create', $transaction)); ?>" class="btn btn-secondary">Record Another Payment</a>
            <?php endif; ?>
            <button type="button" class="btn btn-primary" onclick="window.print()">
                <?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
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
<?php endif; ?> Print Acknowledgment
            </button>
        </div>
    </div>

    <article class="credit-payment-ack" id="paymentAcknowledgment" aria-label="Credit payment acknowledgment">
        <header class="ack-header">
            <img src="<?php echo e(asset('logo/Acry-Link_Logo.ico')); ?>" class="ack-logo" width="58" height="58" alt="<?php echo e($receiptSettings['business.name']); ?> logo">
            <div>
                <strong><?php echo e($receiptSettings['business.name']); ?></strong>
                <span><?php echo e($receiptSettings['business.address']); ?></span>
            </div>
        </header>

        <div class="ack-title-block">
            <span>Credit Payment</span>
            <h2>ACKNOWLEDGMENT RECEIPT</h2>
            <div class="ack-number">No. <?php echo e($acknowledgmentNumber); ?></div>
        </div>

        <div class="ack-status-row">
            <div><span>Date received</span><strong><?php echo e($transactionPayment->paid_at->format('M d, Y')); ?></strong></div>
            <div><span>Time</span><strong><?php echo e($transactionPayment->paid_at->format('h:i A')); ?></strong></div>
            <span class="ack-status ack-status-<?php echo e(str_replace('_', '-', $paymentStatus)); ?>"><?php echo e(ucfirst(str_replace('_', ' ', $paymentStatus))); ?></span>
        </div>

        <section class="ack-received" aria-label="Payment amount and customer">
            <span>Received from</span>
            <strong><?php echo e($transaction->customer?->customer_name ?? 'Walk-in Customer'); ?></strong>
            <?php if($transaction->customer?->customer_code): ?><small>Customer <?php echo e($transaction->customer->customer_code); ?></small><?php endif; ?>
            <div class="ack-amount">
                <span>Amount received</span>
                <strong>&#8369;<?php echo e(number_format((float) $transactionPayment->amount, 2)); ?></strong>
            </div>
        </section>

        <section class="ack-section" aria-labelledby="ack-payment-details">
            <h3 id="ack-payment-details">Payment Details</h3>
            <dl>
                <div><dt>Payment ID</dt><dd>#<?php echo e($transactionPayment->id); ?></dd></div>
                <div><dt>Paid At</dt><dd><?php echo e($transactionPayment->paid_at->format('M d, Y h:i A')); ?></dd></div>
                <div><dt>Payment Method</dt><dd><?php echo e($transactionPayment->paymentMethod?->name ?? 'Unknown'); ?></dd></div>
                <?php if($transactionPayment->payment_reference): ?>
                <div><dt>Payment Reference</dt><dd><?php echo e($transactionPayment->payment_reference); ?></dd></div>
                <?php endif; ?>
            <?php if($transactionPayment->paymentMethod?->code === 'check'): ?>
                <div><dt>Check Number</dt><dd><?php echo e($transactionPayment->check_number ?: '-'); ?></dd></div>
                <div><dt>Bank</dt><dd><?php echo e($transactionPayment->check_bank ?: '-'); ?></dd></div>
                <div><dt>Check Date</dt><dd><?php echo e($transactionPayment->check_date?->format('M d, Y') ?: '-'); ?></dd></div>
            <?php endif; ?>
                <div><dt>Received By</dt><dd><?php echo e($transactionPayment->receivedBy?->name ?? 'Unknown'); ?></dd></div>
                <?php if($transactionPayment->notes): ?><div><dt>Notes</dt><dd><?php echo e($transactionPayment->notes); ?></dd></div><?php endif; ?>
            </dl>
        </section>

        <section class="ack-section" aria-labelledby="ack-account-summary">
            <h3 id="ack-account-summary">Credit Account Summary</h3>
            <dl>
                <div><dt>Original Receipt Number</dt><dd><?php echo e($transaction->receipt_no); ?></dd></div>
                <div><dt>Sale Date</dt><dd><?php echo e($transaction->date->format('M d, Y')); ?></dd></div>
                <div><dt>Due Date</dt><dd><?php echo e($transaction->creditSale->due_date->format('M d, Y')); ?></dd></div>
                <div><dt>Balance Before Payment</dt><dd>&#8369;<?php echo e(number_format($balanceBeforePayment, 2)); ?></dd></div>
                <div><dt>Less: This Payment</dt><dd class="ack-paid">- &#8369;<?php echo e(number_format((float) $transactionPayment->amount, 2)); ?></dd></div>
                <div><dt>Total Paid to Date</dt><dd>&#8369;<?php echo e(number_format($totalPaid, 2)); ?></dd></div>
                <div class="ack-balance"><dt>Remaining Balance</dt><dd>&#8369;<?php echo e(number_format($remainingBalance, 2)); ?></dd></div>
            </dl>
        </section>

        <div class="ack-signatures">
            <div><span><?php echo e($transactionPayment->receivedBy?->name ?? 'Authorized Representative'); ?></span><strong>Received by</strong></div>
            <div><span>&nbsp;</span><strong>Customer signature</strong></div>
        </div>

        <footer>
            <strong>Thank you for your payment.</strong>
            <span>This document acknowledges payment toward the credit sale shown above.</span>
            <span>System-generated on <?php echo e($transactionPayment->paid_at->format('M d, Y h:i A')); ?>.</span>
        </footer>
    </article>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<?php echo app('Illuminate\Foundation\Vite')('resources/css/views/credit-payments-1.css'); ?>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\acrylink_newbie\resources\views\pos\credit-payments\show.blade.php ENDPATH**/ ?>