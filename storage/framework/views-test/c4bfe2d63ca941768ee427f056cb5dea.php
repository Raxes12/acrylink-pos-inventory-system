
<?php $__env->startSection('title', 'Record Credit Payment'); ?>
<?php $__env->startSection('page-label', 'Record Credit Payment'); ?>

<?php $__env->startSection('content'); ?>
<?php
    $outstanding = $transaction->outstandingBalance();
?>
<div class="credit-payment-shell">
    <?php if (isset($component)) { $__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => 'Record Payment','subtitle' => 'Apply a collection to an existing Credit Sale without creating another sale.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Record Payment','subtitle' => 'Apply a collection to an existing Credit Sale without creating another sale.']); ?>
         <?php $__env->slot('actions', null, []); ?> 
            <a href="<?php echo e(route('pos.receipt', $transaction)); ?>" class="btn btn-secondary">View Receipt</a>
            <a href="<?php echo e(route('pos.transactions')); ?>" class="btn btn-secondary">Transactions</a>
         <?php $__env->endSlot(); ?>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e)): ?>
<?php $attributes = $__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e; ?>
<?php unset($__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e)): ?>
<?php $component = $__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e; ?>
<?php unset($__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e); ?>
<?php endif; ?>

    <div class="credit-payment-grid">
        <section class="card credit-payment-summary-card">
            <div class="card-header"><span class="card-title"><i class="bi bi-receipt" aria-hidden="true"></i> Credit Sale Details</span></div>
            <div class="credit-payment-balance">
                <span>Outstanding balance</span>
                <strong>&#8369;<?php echo e(number_format($outstanding, 2)); ?></strong>
                <small>Receipt <?php echo e($transaction->receipt_no); ?></small>
            </div>
            <dl class="credit-payment-details">
                <div><dt>Customer</dt><dd><?php echo e($transaction->customer?->customer_code); ?> - <?php echo e($transaction->customer?->customer_name); ?></dd></div>
                <div><dt>Receipt</dt><dd><?php echo e($transaction->receipt_no); ?></dd></div>
                <div><dt>Sale Date</dt><dd><?php echo e($transaction->date->format('M d, Y')); ?></dd></div>
                <div><dt>Due Date</dt><dd><?php echo e($transaction->creditSale->due_date->format('M d, Y')); ?></dd></div>
                <div><dt>Net Total</dt><dd>&#8369;<?php echo e(number_format($transaction->netTotal(), 2)); ?></dd></div>
                <div><dt>Total Paid</dt><dd>&#8369;<?php echo e(number_format($transaction->totalPaid(), 2)); ?></dd></div>
                <?php ($paymentStatus = app(\App\Services\TransactionSettlementService::class)->paymentStatus($transaction)); ?>
                <div><dt>Payment Status</dt><dd><span class="status status-<?php echo e($paymentStatus === 'overdue' ? 'danger' : 'low'); ?>"><?php echo e(ucfirst(str_replace('_', ' ', $paymentStatus))); ?></span></dd></div>
            </dl>
        </section>

        <section class="card credit-payment-form-card">
            <div class="card-header"><div><span class="card-title"><i class="bi bi-wallet2" aria-hidden="true"></i> New Payment</span><span class="credit-payment-caption">Enter the amount received and how the customer paid.</span></div></div>
            <form method="POST" action="<?php echo e(route('pos.credit-payments.store', $transaction)); ?>" class="credit-payment-form">
                <?php echo csrf_field(); ?>
                <div class="form-group">
                    <div class="credit-payment-label-row"><label for="amount">Payment Amount *</label><button type="button" class="credit-payment-full" id="payFullBalance" data-amount="<?php echo e(number_format($outstanding, 2, '.', '')); ?>">Pay full balance</button></div>
                    <span class="credit-payment-money"><span aria-hidden="true">&#8369;</span><input id="amount" name="amount" type="number" min="0.01" max="<?php echo e(number_format($outstanding, 2, '.', '')); ?>" step="0.01" value="<?php echo e(old('amount')); ?>" placeholder="0.00" required></span>
                    <small class="credit-payment-help">Maximum: &#8369;<?php echo e(number_format($outstanding, 2)); ?></small>
                    <?php $__errorArgs = ['amount'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="field-error"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div class="form-group">
                    <label for="payment_method">Settlement Payment Method *</label>
                    <select id="payment_method" name="payment_method" required>
                        <option value="">Select payment method</option>
                        <?php $__currentLoopData = $paymentMethods; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $method): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($method->code); ?>" <?php if(old('payment_method') === $method->code): echo 'selected'; endif; ?>><?php echo e($method->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <small class="credit-payment-help" id="creditPaymentMethodHelp">Choose how the payment was received.</small>
                    <?php $__errorArgs = ['payment_method'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="field-error"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div class="form-group credit-payment-detail-panel" id="creditPaymentReferenceGroup" hidden>
                    <div class="credit-payment-detail-title"><i class="bi bi-phone" aria-hidden="true"></i><span><strong>GCash Details</strong><small>Use the reference from the customer’s successful transfer.</small></span></div>
                    <label for="payment_reference" id="creditPaymentReferenceLabel">GCash Reference Number *</label>
                    <input id="payment_reference" name="payment_reference" type="text" maxlength="100" value="<?php echo e(old('payment_reference')); ?>" placeholder="e.g. 1234567890123">
                    <?php $__errorArgs = ['payment_reference'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="field-error"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <fieldset class="credit-payment-detail-panel" id="creditPaymentCheckGroup" <?php if(old('payment_method') !== 'check'): ?> hidden <?php endif; ?>>
                    <legend class="credit-payment-detail-title"><i class="bi bi-bank" aria-hidden="true"></i><span><strong>Check Details</strong><small>Record the check information before accepting payment.</small></span></legend>
                    <div class="credit-payment-check-grid">
                        <div class="form-group"><label for="check_number">Check Number *</label><input id="check_number" name="check_number" type="text" maxlength="100" value="<?php echo e(old('check_number')); ?>" placeholder="Enter check number"><?php $__errorArgs = ['check_number'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="field-error"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?></div>
                        <div class="form-group"><label for="check_bank">Bank *</label><input id="check_bank" name="check_bank" type="text" maxlength="100" value="<?php echo e(old('check_bank')); ?>" placeholder="Enter issuing bank"><?php $__errorArgs = ['check_bank'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="field-error"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?></div>
                        <div class="form-group credit-payment-check-date"><label for="check_date">Check Date *</label><input id="check_date" name="check_date" type="date" value="<?php echo e(old('check_date')); ?>"><?php $__errorArgs = ['check_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="field-error"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?></div>
                    </div>
                </fieldset>
                <div class="form-group">
                    <label for="notes">Notes</label>
                    <textarea id="notes" name="notes" maxlength="1000" rows="4" placeholder="Optional collection notes"><?php echo e(old('notes')); ?></textarea>
                    <?php $__errorArgs = ['notes'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="field-error"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <?php $__errorArgs = ['transaction'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="checkout-feedback is-error"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
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
                <?php $__empty_1 = true; $__currentLoopData = $transaction->payments->sortByDesc('paid_at'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td><?php echo e($payment->paid_at->format('M d, Y h:i A')); ?></td>
                        <td><?php echo e($payment->paymentMethod?->name ?? 'Unknown'); ?></td>
                        <td><?php if($payment->paymentMethod?->code === 'check'): ?><div class="credit-payment-table-detail"><strong><?php echo e($payment->check_number ?: '-'); ?></strong><span><?php echo e($payment->check_bank ?: 'Bank not recorded'); ?> <?php if($payment->check_date): ?>&middot; <?php echo e($payment->check_date->format('M d, Y')); ?><?php endif; ?></span></div><?php else: ?><?php echo e($payment->payment_reference ?: '-'); ?><?php endif; ?></td>
                        <td><?php echo e($payment->receivedBy?->name ?? 'Unknown'); ?></td>
                        <td><?php echo e($payment->notes ?: '-'); ?></td>
                        <td class="text-right fw-bold">&#8369;<?php echo e(number_format((float) $payment->amount, 2)); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="6" class="empty-state">No payments have been recorded.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<?php echo app('Illuminate\Foundation\Vite')('resources/css/views/credit-payments-1.css'); ?>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<?php echo app('Illuminate\Foundation\Vite')('resources/js/views/credit-payments-1.js'); ?>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\acrylink_newbie\resources\views\pos\credit-payments\create.blade.php ENDPATH**/ ?>