<?php $__env->startSection('title', 'Accounts Receivable'); ?>
<?php $__env->startSection('page-label', 'Accounts Receivable'); ?>

<?php $__env->startSection('content'); ?>
<div class="ar-page">
<?php if (isset($component)) { $__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => 'Accounts Receivable','subtitle' => 'Prioritize overdue accounts, monitor upcoming dues, and record customer collections.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Accounts Receivable','subtitle' => 'Prioritize overdue accounts, monitor upcoming dues, and record customer collections.']); ?>
     <?php $__env->slot('actions', null, []); ?> 
        <?php if(auth()->user()?->isAdmin()): ?>
        <a class="btn btn-secondary" href="<?php echo e(route('customers.index')); ?>">
            <i class="bi bi-people me-1" aria-hidden="true"></i>
            Customers
        </a>
        <a class="btn btn-primary" href="<?php echo e(route('reports.accounts-receivable')); ?>">
            <i class="bi bi-file-earmark-bar-graph" aria-hidden="true"></i>
            Receivables Report
        </a>
        <?php endif; ?>
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

<div class="grid-4 section-spaced ar-metrics" data-live-sync="ar-summary">
    <div class="card metric-card"><div class="metric-topline"><span class="metric-label">Total Outstanding</span><span class="metric-icon"><i class="bi bi-wallet2" aria-hidden="true"></i></span></div><div class="metric-value">&#8369;<?php echo e(number_format($summary['total_outstanding'], 2)); ?></div><div class="metric-note">Balance still collectible across open credit sales</div></div>
    <div class="card metric-card"><div class="metric-topline"><span class="metric-label">Due Soon</span><span class="metric-icon"><i class="bi bi-calendar2-week" aria-hidden="true"></i></span></div><div class="metric-value ar-due-soon">&#8369;<?php echo e(number_format($summary['due_soon'], 2)); ?></div><div class="metric-note">Due today through the next <?php echo e(\App\Services\AccountsReceivableService::DUE_SOON_DAYS); ?> days</div></div>
    <div class="card metric-card"><div class="metric-topline"><span class="metric-label">Overdue</span><span class="metric-icon"><i class="bi bi-exclamation-triangle" aria-hidden="true"></i></span></div><div class="metric-value ar-overdue">&#8369;<?php echo e(number_format($summary['overdue'], 2)); ?></div><div class="metric-note">Balance requiring immediate collection follow-up</div></div>
    <div class="card metric-card"><div class="metric-topline"><span class="metric-label">Open Credit Customers</span><span class="metric-icon"><i class="bi bi-people" aria-hidden="true"></i></span></div><div class="metric-value"><?php echo e(number_format($summary['customers_with_open_credit'])); ?></div><div class="metric-note">Customers who currently have an open balance</div></div>
</div>

<form method="GET" action="<?php echo e(route('accounts-receivable.index')); ?>" class="filter-bar ar-filter" data-live-filter>
    <div class="ar-filter-main">
        <div class="ar-search-wrap">
            <i class="bi bi-search" aria-hidden="true"></i>
            <input id="arSearch" type="search" name="search" maxlength="150" autocomplete="off" value="<?php echo e($filters['search'] ?? ''); ?>" placeholder="Search customer name, code, or receipt number...">
        </div>
            <select name="payment_status" aria-label="Account status">
                <option value="">All statuses</option>
                <option value="open" <?php if(($filters['payment_status'] ?? '') === 'open'): echo 'selected'; endif; ?>>Open</option>
                <option value="due_soon" <?php if(($filters['payment_status'] ?? '') === 'due_soon'): echo 'selected'; endif; ?>>Due Soon</option>
                <option value="overdue" <?php if(($filters['payment_status'] ?? '') === 'overdue'): echo 'selected'; endif; ?>>Overdue</option>
                <option value="partially_paid" <?php if(($filters['payment_status'] ?? '') === 'partially_paid'): echo 'selected'; endif; ?>>Partially Paid</option>
                <option value="paid" <?php if(($filters['payment_status'] ?? '') === 'paid'): echo 'selected'; endif; ?>>Paid</option>
            </select>
    </div>
    <div class="ar-quick-filters" role="group" aria-label="Quick account status filters">
        <?php $__currentLoopData = ['' => 'All', 'open' => 'Open', 'due_soon' => 'Due Soon', 'overdue' => 'Overdue', 'partially_paid' => 'Partially Paid', 'paid' => 'Paid']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <button type="button" class="ar-filter-chip <?php echo e(($filters['payment_status'] ?? '') === $value ? 'is-active' : ''); ?>" data-live-filter-choice="payment_status" data-live-filter-value="<?php echo e($value); ?>" aria-pressed="<?php echo e(($filters['payment_status'] ?? '') === $value ? 'true' : 'false'); ?>"><?php echo e($label); ?></button>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <span class="live-filter-status" data-live-filter-status role="status" aria-live="polite"></span>
    </div>
</form>

<div class="card ar-table-card" data-live-results>
    <div class="card-header"><div><span class="card-title">Credit Sales</span><span class="ar-card-caption">Review balances, due dates, and payment progress.</span></div><span class="tag tag--warning"><?php echo e($transactions->total()); ?> records</span></div>
    <div class="table-wrap ar-table-wrap" tabindex="0" role="region" aria-label="Accounts receivable credit sales">
        <table class="ar-table">
            <thead>
                <tr>
                    <th>Customer</th>
                    <th>Credit Sale</th>
                    <th class="text-right">Original Amount</th>
                    <th>Payment Progress</th>
                    <th>Due Date</th>
                    <th>Payment Status</th>
                    <th>Cashier</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $transactions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $transaction): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php
                    $displayStatus = $receivables->statusForRow($transaction);
                    $netAmount = (float) $transaction->ar_net_amount;
                    $paidAmount = (float) $transaction->ar_paid_amount;
                    $balanceAmount = (float) $transaction->ar_balance_amount;
                    $paidPercent = $netAmount > 0 ? min(100, round(($paidAmount / $netAmount) * 100)) : 100;
                    $dueDate = $transaction->creditSale->due_date;
                    $daysUntilDue = (int) today()->diffInDays($dueDate, false);
                    $customerName = $transaction->customer?->customer_name ?? 'Unknown customer';
                    $initials = collect(preg_split('/\s+/', trim($customerName)))->filter()->take(2)->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))->join('');
                ?>
                <tr class="ar-row ar-row--<?php echo e(str_replace('_', '-', $displayStatus)); ?>">
                    <td data-label="Customer">
                        <div class="ar-customer-cell">
                            <span class="ar-customer-avatar" aria-hidden="true">
                                <?php echo e($initials ?: '?'); ?>

                            </span>
                            <div>
                                <a class="ar-customer" href="<?php echo e(route('accounts-receivable.customers.show', $transaction->customer)); ?>">
                                    <?php echo e($customerName); ?>

                                </a>
                                <small>
                                    <?php echo e($transaction->customer?->customer_code); ?>

                                </small>
                            </div>
                        </div>
                    </td>
                    <td data-label="Credit Sale" class="ar-sale-cell">
                        <a class="receipt-code ar-receipt-link" href="<?php echo e(route('pos.receipt', $transaction)); ?>"><?php echo e($transaction->receipt_no); ?></a>
                        <span><?php echo e($transaction->date->format('M d, Y')); ?></span>
                        <small><?php echo e($transaction->date->diffForHumans()); ?></small>
                    </td>
                    <td data-label="Original Amount" class="text-right ar-money ar-original-amount">
                        &#8369;<?php echo e(number_format($netAmount, 2)); ?>

                    </td>
                    <td data-label="Payment Progress" class="ar-payment-cell">
                        <div class="ar-payment-totals"><span><small>Paid</small><strong>&#8369;<?php echo e(number_format($paidAmount, 2)); ?></strong></span><span><small>Balance</small><strong class="ar-balance <?php echo e($balanceAmount <= 0 ? 'is-settled' : ''); ?>">&#8369;<?php echo e(number_format($balanceAmount, 2)); ?></strong></span></div>
                        <div class="ar-progress" role="progressbar" aria-label="<?php echo e($paidPercent); ?>% paid" aria-valuenow="<?php echo e($paidPercent); ?>" aria-valuemin="0" aria-valuemax="100">
                            <span style="width:<?php echo e($paidPercent); ?>%"></span>
                        </div>
                        <small class="ar-progress-label"><?php echo e($paidPercent); ?>% collected</small>
                    </td>
                    <td data-label="Due Date" class="ar-date-cell">
                        <strong><?php echo e($dueDate->format('M d, Y')); ?></strong>
                        <small class="<?php echo e($displayStatus === 'overdue' ? 'ar-due-danger' : ''); ?>">
                            <?php if($displayStatus === 'paid'): ?>
                                Settled
                            <?php elseif($daysUntilDue < 0): ?>
                                <?php echo e(abs($daysUntilDue)); ?> day<?php echo e(abs($daysUntilDue) === 1 ? '' : 's'); ?> overdue
                            <?php elseif($daysUntilDue === 0): ?>
                                Due today
                            <?php else: ?>
                                Due in <?php echo e($daysUntilDue); ?> days
                            <?php endif; ?>
                        </small>
                    </td>
                    <td data-label="Status">
                        <span class="status <?php echo e($displayStatus === 'overdue' ? 'status-danger' : ($displayStatus === 'paid' ? 'status-active' : 'status-low')); ?>">
                            <?php echo e(ucfirst(str_replace('_', ' ', $displayStatus))); ?>

                        </span>
                    </td>
                    <td data-label="Cashier" class="ar-cashier">
                        <?php echo e($transaction->cashier?->name ?? 'Unknown'); ?>

                    </td>
                    <td data-label="Actions">
                        <div class="table-actions ar-actions">
                            <?php if($balanceAmount > 0): ?>
                            <a class="btn btn-primary btn-sm ar-pay-action" href="<?php echo e(route('pos.credit-payments.create', $transaction)); ?>">
                                <i class="bi bi-plus-circle" aria-hidden="true"></i><span>Record Payment</span>
                            </a>
                            <?php endif; ?>
                            <a class="btn btn-secondary btn-sm" href="<?php echo e(route('accounts-receivable.customers.show', $transaction->customer)); ?>" title="View customer account">
                                <i class="bi bi-person" aria-hidden="true"></i><span>Account</span>
                            </a>
                            <a class="btn btn-secondary btn-sm" href="<?php echo e(route('pos.receipt', $transaction)); ?>" title="View sale receipt">
                                <i class="bi bi-receipt" aria-hidden="true"></i><span>Receipt</span>
                            </a>
                        </div>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <?php if (isset($component)) { $__componentOriginal074a021b9d42f490272b5eefda63257c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal074a021b9d42f490272b5eefda63257c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.empty-state','data' => ['colspan' => '8','title' => 'No matching receivables','message' => 'Try a different customer, code, receipt number, or account status.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['colspan' => '8','title' => 'No matching receivables','message' => 'Try a different customer, code, receipt number, or account status.']); ?>
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
            <?php endif; ?>
            </tbody>
        </table>
    </div>
    <div class="view-blades-footer">
        <div class="pagination-summary">
            Showing <?php echo e($transactions->firstItem() ?? 0); ?>-<?php echo e($transactions->lastItem() ?? 0); ?> of <?php echo e($transactions->total()); ?> transactions
        </div>

        <?php if($transactions->hasPages()): ?>
        <?php
            $currentPage = $transactions->currentPage();
            $lastPage = $transactions->lastPage();
            $pages = collect([1, $currentPage - 1, $currentPage, $currentPage + 1, $lastPage])
                ->filter(fn ($page) => $page >= 1 && $page <= $lastPage)
                ->unique()
                ->sort()
                ->values();
            $previousPage = null;
        ?>
        <nav class="rules-pagination" aria-label="Security rules pagination">
            <?php if($transactions->onFirstPage()): ?>
                <span class="rules-page is-disabled" aria-disabled="true">Previous</span>
            <?php else: ?>
                <a class="rules-page" href="<?php echo e($transactions->previousPageUrl()); ?>" rel="prev">Previous</a>
            <?php endif; ?>

            <?php $__currentLoopData = $pages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php if($previousPage && $page > $previousPage + 1): ?>
                    <span class="rules-page rules-page--gap" aria-hidden="true">...</span>
                <?php endif; ?>

                <?php if($page === $currentPage): ?>
                    <span class="rules-page is-active" aria-current="page"><?php echo e($page); ?></span>
                <?php else: ?>
                    <a class="rules-page" href="<?php echo e($transactions->url($page)); ?>"><?php echo e($page); ?></a>
                <?php endif; ?>

                <?php $previousPage = $page; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

            <?php if($transactions->hasMorePages()): ?>
                <a class="rules-page" href="<?php echo e($transactions->nextPageUrl()); ?>" rel="next">Next</a>
            <?php else: ?>
                <span class="rules-page is-disabled" aria-disabled="true">Next</span>
            <?php endif; ?>
        </nav>
        <?php endif; ?>
    </div>
</div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<?php echo app('Illuminate\Foundation\Vite')('resources/css/views/accounts-receivable-1.css'); ?>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\acrylink_newbie\resources\views\accounts-receivable\index.blade.php ENDPATH**/ ?>