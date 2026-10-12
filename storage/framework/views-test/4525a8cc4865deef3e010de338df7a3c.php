<?php $__env->startSection('title', 'My Transactions'); ?>
<?php $__env->startSection('page-label', 'My Transactions'); ?>

<?php $__env->startSection('content'); ?>
<?php if (isset($component)) { $__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => 'My Transactions','subtitle' => 'Receipts processed by your cashier account.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'My Transactions','subtitle' => 'Receipts processed by your cashier account.']); ?>
     <?php $__env->slot('actions', null, []); ?> <a href="<?php echo e(route('dashboard')); ?>" class="btn btn-secondary">Back to Dashboard</a> <?php $__env->endSlot(); ?>
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

<div class="grid-2 cashier-summary">
    <div class="card metric-card">
        <div class="metric-topline">
            <span class="metric-label">My Total Sales Today</span>
            <span class="metric-icon icon-sales">PHP</span>
        </div>
        <div class="metric-value" style="color:var(--accent2);">&#8369;<?php echo e(number_format($todaySales, 2)); ?></div>
        <div class="metric-note"><?php echo e($todayTxns); ?> receipt<?php echo e($todayTxns !== 1 ? 's' : ''); ?> processed today</div>
    </div>
    <div class="card metric-card">
        <div class="metric-topline">
            <span class="metric-label">All My Receipts</span>
            <span class="metric-icon icon-txn">POS</span>
        </div>
        <div class="metric-value"><?php echo e(number_format($transactions->total())); ?></div>
        <div class="metric-note">Total receipts under your account</div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <span class="card-title">Transaction History</span>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Receipt</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Payment</th>
                    <th class="text-right">Total</th>
                    <th>Status</th>
                    <th class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $transactions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td><span class="receipt-code"><?php echo e($t->receipt_no); ?></span></td>
                    <td><?php echo e($t->date->format('M d, Y')); ?></td>
                    <td><?php echo e(\Carbon\Carbon::parse($t->time)->format('h:i A')); ?></td>
                    <td><?php echo e(\App\Models\Transaction::paymentMethodLabel($t->payment_method)); ?></td>
                    <td class="text-right fw-800" style="color:var(--accent2);">&#8369;<?php echo e(number_format($t->netTotal(), 2)); ?></td>
                    <td><span class="status status-active"><?php echo e(ucfirst(str_replace('_', ' ', $t->status ?? 'completed'))); ?></span></td>
                    <td class="text-center">
                        <a href="<?php echo e(route('pos.receipt', $t)); ?>" class="btn btn-secondary btn-sm">Receipt</a>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="7" class="empty-state">No transactions found for your account.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <div class="transaction-pagination"><?php echo e($transactions->links()); ?></div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<?php echo app('Illuminate\Foundation\Vite')('resources/css/views/dashboard-my-transactions-1.css'); ?>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\acrylink_newbie\resources\views\dashboard\my-transactions.blade.php ENDPATH**/ ?>