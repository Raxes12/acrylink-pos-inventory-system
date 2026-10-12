
<?php $__env->startSection('title', 'Customer Credit Detail'); ?>
<?php $__env->startSection('page-label', 'Accounts Receivable / Customer'); ?>

<?php $__env->startSection('content'); ?>
<a href="<?php echo e(route('accounts-receivable.index')); ?>" class="editor-breadcrumb">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
        stroke-linecap="round" stroke-linejoin="round">
        <path d="M19 12H5"/><path d="m12 19-7-7 7-7"/>
    </svg>
    Back to Recieivables
</a>
<div class="ar-page ar-customer-page">
<?php if (isset($component)) { $__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => $customer->customer_name,'subtitle' => $customer->customer_code.' · Customer credit account']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($customer->customer_name),'subtitle' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($customer->customer_code.' · Customer credit account')]); ?>
     <?php $__env->slot('actions', null, []); ?> <a class="btn btn-secondary" href="<?php echo e(route('customers.show', $customer)); ?>">Customer Profile</a><a class="btn btn-primary" href="<?php echo e(route('accounts-receivable.index')); ?>">All Receivables</a> <?php $__env->endSlot(); ?>
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

<div class="grid-4 section-spaced ar-metrics">
    <div class="card metric-card">
        <div class="metric-label">Credit Limit</div>
        <div class="metric-value">&#8369;<?php echo e(number_format($customerSummary['credit_limit'], 2)); ?></div>
    </div>
    <div class="card metric-card">
        <div class="metric-label">Current Outstanding</div>
        <div class="metric-value ar-overdue">&#8369;<?php echo e(number_format($customerSummary['current_outstanding'], 2)); ?></div>
    </div>
    <div class="card metric-card">
        <div class="metric-label">Available Credit</div>
        <div class="metric-value ar-available">&#8369;<?php echo e(number_format($customerSummary['available_credit'], 2)); ?></div>
    </div>
    <div class="card metric-card">
        <div class="metric-label">Default Credit Term</div>
        <div class="metric-value"><?php echo e($customer->default_credit_term_days); ?> days</div>
        <div class="metric-note">
            <span class="status <?php echo e($customer->credit_enabled && $customer->status === 'active' ? 'status-active' : 'status-danger'); ?>">
                Credit <?php echo e($customer->credit_enabled ? 'enabled' : 'disabled'); ?>

            </span>
        </div>
    </div>
</div>

<section class="card ar-table-card">
    <div class="card-header"><span class="card-title">Open Credit Transactions</span><span><?php echo e($transactions->total()); ?> open</span></div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Receipt</th>
                    <th>Sale Date</th>
                    <th>Due Date</th>
                    <th>Status</th>
                    <th class="text-right">Net</th>
                    <th class="text-right">Paid</th>
                    <th class="text-right">Balance</th>
                    <th>Actions</th>
                </tr>
            </thead>
            
            <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $transactions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $transaction): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php ($displayStatus = $receivables->statusForRow($transaction)); ?>
                <tr>
                    <td><span class="receipt-code"><?php echo e($transaction->receipt_no); ?></span></td>
                    <td><?php echo e($transaction->date->format('M d, Y')); ?></td>
                    <td><?php echo e($transaction->creditSale->due_date->format('M d, Y')); ?></td>
                    <td>
                        <span class="status <?php echo e($displayStatus === 'overdue' ? 'status-danger' : 'status-low'); ?>">
                            <?php echo e(ucfirst(str_replace('_', ' ', $displayStatus))); ?>

                        </span>
                    </td>
                    <td class="text-right">&#8369;<?php echo e(number_format((float) $transaction->ar_net_amount, 2)); ?></td>
                    <td class="text-right">&#8369;<?php echo e(number_format((float) $transaction->ar_paid_amount, 2)); ?></td>
                    <td class="text-right fw-800 ar-balance">&#8369;<?php echo e(number_format((float) $transaction->ar_balance_amount, 2)); ?></td>
                    <td>
                        <div class="table-actions">
                            <a class="btn btn-primary btn-sm" href="<?php echo e(route('pos.credit-payments.create', $transaction)); ?>">
                                <i class="bi bi-plus-circle" aria-hidden="true"></i><span>Record Payment</span>
                            </a>
                            <a class="btn btn-secondary btn-sm" href="<?php echo e(route('pos.receipt', $transaction)); ?>">Receipt</a>
                        </div>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <?php if (isset($component)) { $__componentOriginal074a021b9d42f490272b5eefda63257c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal074a021b9d42f490272b5eefda63257c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.empty-state','data' => ['colspan' => '8','title' => 'No open credit transactions','message' => 'This customer has no outstanding Credit Sales.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['colspan' => '8','title' => 'No open credit transactions','message' => 'This customer has no outstanding Credit Sales.']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal074a021b9d42f490272b5eefda63257c)): ?>
<?php $attributes = $__attributesOriginal074a021b9d42f490272b5eefda63257c; ?>
<?php unset($__attributesOriginal074a021b9d42f490272b5eefda63257c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal074a021b9d42f490272b5eefda63257c)): ?>
<?php $component = $__componentOriginal074a021b9d42f490272b5eefda63257c; ?>
<?php unset($__componentOriginal074a021b9d42f490272b5eefda63257c); ?>
<?php endif; ?>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
    <div class="view-blades-footer"><?php echo e($transactions->links()); ?></div>
</section>

<section class="card ar-table-card section-spaced">
    <div class="card-header"><span class="card-title">Payment History</span><span><?php echo e($payments->total()); ?> payments</span></div>
    <div class="table-wrap"><table><thead><tr><th>Date</th><th>Receipt</th><th>Method</th><th>Reference</th><th>Received By</th><th>Notes</th><th class="text-right">Amount</th><th>Details</th></tr></thead><tbody>
    <?php $__empty_1 = true; $__currentLoopData = $payments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <tr><td><?php echo e($payment->paid_at->format('M d, Y h:i A')); ?></td><td><?php echo e($payment->transaction?->receipt_no); ?></td><td><?php echo e($payment->paymentMethod?->name ?? 'Unknown'); ?></td><td><?php echo e($payment->payment_reference ?: '-'); ?></td><td><?php echo e($payment->receivedBy?->name ?? 'Unknown'); ?></td><td><?php echo e($payment->notes ?: '-'); ?></td><td class="text-right fw-800">&#8369;<?php echo e(number_format((float) $payment->amount, 2)); ?></td><td><a class="btn btn-secondary btn-sm" href="<?php echo e(route('pos.credit-payments.show', $payment)); ?>">View</a></td></tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><tr><?php if (isset($component)) { $__componentOriginal074a021b9d42f490272b5eefda63257c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal074a021b9d42f490272b5eefda63257c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.empty-state','data' => ['colspan' => '8','title' => 'No payment history','message' => 'Payments recorded for this customer\'s Credit Sales will appear here.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['colspan' => '8','title' => 'No payment history','message' => 'Payments recorded for this customer\'s Credit Sales will appear here.']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal074a021b9d42f490272b5eefda63257c)): ?>
<?php $attributes = $__attributesOriginal074a021b9d42f490272b5eefda63257c; ?>
<?php unset($__attributesOriginal074a021b9d42f490272b5eefda63257c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal074a021b9d42f490272b5eefda63257c)): ?>
<?php $component = $__componentOriginal074a021b9d42f490272b5eefda63257c; ?>
<?php unset($__componentOriginal074a021b9d42f490272b5eefda63257c); ?>
<?php endif; ?></tr><?php endif; ?>
    </tbody></table></div><div class="view-blades-footer"><?php echo e($payments->links()); ?></div>
</section>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<?php echo app('Illuminate\Foundation\Vite')('resources/css/views/accounts-receivable-1.css'); ?>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\acrylink_newbie\resources\views\accounts-receivable\show.blade.php ENDPATH**/ ?>