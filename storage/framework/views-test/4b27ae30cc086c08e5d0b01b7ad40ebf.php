<?php $__env->startSection('title', 'Transactions'); ?>
<?php $__env->startSection('page-label', 'Transactions'); ?>

<?php $__env->startSection('content'); ?>
<div class="transactions-page">
<?php if (isset($component)) { $__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => 'Transactions','subtitle' => 'Search, filter, view receipts, and process returns or voids.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Transactions','subtitle' => 'Search, filter, view receipts, and process returns or voids.']); ?>
     <?php $__env->slot('actions', null, []); ?> 
        <a href="<?php echo e(route('pos.index')); ?>" class="btn btn-primary">
            <?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
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
<?php endif; ?>
            POS Terminal
        </a>
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

<div class="grid-3 section-spaced transaction-metrics">
    <div class="card metric-card transaction-metric transaction-metric--sales">
        <div class="metric-topline"><span class="metric-label">Today Sales</span><span class="metric-icon icon-sales">PHP</span></div>
        <div class="metric-value transaction-sales-value">&#8369;<?php echo e(number_format($todaySales, 2)); ?></div>
        <div class="metric-note">Net sales after refunds and voids</div>
    </div>
    <div class="card metric-card transaction-metric transaction-metric--today">
        <div class="metric-topline"><span class="metric-label">Today Receipts</span><span class="metric-icon icon-txn">POS</span></div>
        <div class="metric-value"><?php echo e(number_format($todayTxns)); ?></div>
        <div class="metric-note">Receipts processed today</div>
    </div>
    <div class="card metric-card transaction-metric transaction-metric--all">
        <div class="metric-topline"><span class="metric-label">All Receipts</span><span class="metric-icon icon-products">TXN</span></div>
        <div class="metric-value"><?php echo e(number_format($totalReceipts)); ?></div>
        <div class="metric-note"><?php echo e(auth()->user()?->isCashier() ? 'Under your cashier account' : 'Across all cashiers'); ?></div>
    </div>
</div>

<form method="GET" action="<?php echo e(route('pos.transactions')); ?>" class="filter-bar transaction-filter" data-live-filter>
    <div class="transaction-search"><i class="bi bi-search" aria-hidden="true"></i><input type="search" name="search" value="<?php echo e($filters['search'] ?? ''); ?>" placeholder="Search receipt, customer, cashier, or payment..." autocomplete="off" aria-label="Search transactions"></div>
    <label class="transaction-filter-field"><span>Status</span><select name="status" class="filter-field--md">
            <option value="">All Statuses</option>
            <?php $__currentLoopData = $statuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($value); ?>" <?php echo e(($filters['status'] ?? '') === $value ? 'selected' : ''); ?>><?php echo e($label); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select></label>
    <label class="transaction-filter-field"><span>Payment</span><select name="payment_method" class="filter-field--md">
            <option value="">All Payments</option>
            <?php $__currentLoopData = $paymentMethods; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $method): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($method->code); ?>" <?php echo e(($filters['payment_method'] ?? '') === $method->code ? 'selected' : ''); ?>><?php echo e($method->name); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select></label>
    <label class="transaction-date"><span>From</span><input type="date" name="date_from" value="<?php echo e($filters['date_from'] ?? ''); ?>"></label>
    <label class="transaction-date"><span>To</span><input type="date" name="date_to" value="<?php echo e($filters['date_to'] ?? ''); ?>"></label>
    <span class="live-filter-status" data-live-filter-status role="status" aria-live="polite"></span>
</form>

<div class="card transaction-history-card" data-live-results>
    <div class="card-header">
        <div class="transaction-history-heading"><span class="transaction-history-icon"><i class="bi bi-receipt" aria-hidden="true"></i></span><div><span class="card-title">Transaction History</span><span>Receipts, payment progress, and available actions</span></div></div>
        <span class="transaction-count"><?php echo e($transactions->total()); ?> total</span>
    </div>
    <div class="table-wrap" tabindex="0" role="region" aria-label="Transaction history">
        <table>
            <thead>
                <tr>
                    <th>Sale</th>
                    <th>Customer</th>
                    <th>Payment</th>
                    <th class="text-right">Amounts</th>
                    <th>Credit Sale</th>
                    <th>Cashier</th>
                    <th>Transaction Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $transactions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $transaction): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <?php
                $dateTime = \Carbon\Carbon::parse($transaction->date->format('Y-m-d').' '.$transaction->time);
                $refunded = $transaction->totalRefunded();
                $status = $transaction->status ?? 'completed';
                $statusClass = match ($status) {
                    'completed' => 'status-active',
                    'partial_refund' => 'status-low',
                    default => 'status-danger',
                };
                $isCreditSale = (bool) $transaction->creditSale;
                $paymentStatus = app(\App\Services\TransactionSettlementService::class)->paymentStatus($transaction);
                $paymentStatusClass = match ($paymentStatus) {
                    'paid' => 'status-active',
                    'overdue' => 'status-danger',
                    default => 'status-low',
                };
                $outstanding = $isCreditSale ? $transaction->outstandingBalance() : null;
                $canRefund = auth()->user()?->can('refund', $transaction) && in_array($status, ['completed', 'partial_refund'], true);
                $canRecordCreditPayment = auth()->user()?->canAccessSales()
                    && $isCreditSale
                    && $status !== 'voided'
                    && $outstanding > 0;
                $customerInitials = $transaction->customer
                    ? collect(explode(' ', $transaction->customer->customer_name))->filter()->take(2)->map(fn ($part) => strtoupper(substr($part, 0, 1)))->implode('')
                    : 'WI';
                $cashierInitials = collect(explode(' ', $transaction->cashier?->name ?? 'Unknown'))->filter()->take(2)->map(fn ($part) => strtoupper(substr($part, 0, 1)))->implode('');
                $daysUntilDue = $isCreditSale ? today()->diffInDays($transaction->creditSale->due_date, false) : null;
            ?>
            <tr class="transaction-row transaction-row--<?php echo e(str_replace('_', '-', $status)); ?>">
                <td class="transaction-sale-cell" data-label="Receipt / Date">
                    <div class="transaction-sale-content"><a class="receipt-code" href="<?php echo e(route('pos.receipt', $transaction)); ?>"><?php echo e($transaction->receipt_no); ?></a><span class="transaction-date-cell"><strong><?php echo e($dateTime->format('M d, Y')); ?></strong><small><?php echo e($dateTime->format('h:i A')); ?></small></span></div>
                </td>
                <td class="transaction-customer-cell" data-label="Customer">
                    <div class="transaction-customer-content"><span class="transaction-avatar <?php echo e($transaction->customer ? '' : 'transaction-avatar--walkin'); ?>" aria-hidden="true"><?php echo e($customerInitials); ?></span><div><strong><?php echo e($transaction->customer?->customer_name ?? 'Walk-in'); ?></strong><span><?php echo e($transaction->customer?->customer_code ?? 'Guest customer'); ?></span></div></div>
                </td>
                <td data-label="Payment"><span class="transaction-payment"><i class="bi <?php echo e($isCreditSale ? 'bi-journal-text' : 'bi-wallet2'); ?>" aria-hidden="true"></i><?php echo e(\App\Models\Transaction::paymentMethodLabel($transaction->payment_method)); ?></span></td>
                <td class="text-right transaction-amounts-cell" data-label="Amounts">
                    <span class="transaction-amount-line"><small>Net total</small><strong class="transaction-net-value">&#8369;<?php echo e(number_format($transaction->netTotal(), 2)); ?></strong></span>
                    <?php if($refunded > 0): ?><span class="transaction-amount-line"><small>Refunded</small><b class="transaction-refund-value">&minus;&#8369;<?php echo e(number_format($refunded, 2)); ?></b></span><?php endif; ?>
                    <span class="transaction-amount-line"><small>Balance:</small><?php if($isCreditSale): ?><b class="<?php echo e($outstanding > 0 ? 'transaction-balance-open' : ''); ?>">&#8369;<?php echo e(number_format($outstanding, 2)); ?></b><?php else: ?><b class="transaction-zero-value">&mdash;</b><?php endif; ?></span>
                </td>
                <td class="transaction-credit-cell" data-label="Credit">
                    <span><small>Due Date</small><strong><?php echo e($isCreditSale ? $transaction->creditSale->due_date->format('M d, Y') : '—'); ?></strong></span>
                    <?php if($isCreditSale && $outstanding > 0): ?>
                        <small class="transaction-due-hint <?php echo e($daysUntilDue < 0 ? 'is-overdue' : ''); ?>"><?php if($daysUntilDue < 0): ?><?php echo e(abs($daysUntilDue)); ?> day<?php echo e(abs($daysUntilDue) === 1 ? '' : 's'); ?> overdue <?php elseif($daysUntilDue === 0): ?>Due today <?php else: ?> Due in <?php echo e($daysUntilDue); ?> days <?php endif; ?></small>
                    <?php endif; ?>
                    <span class="status <?php echo e($paymentStatusClass); ?>"><?php echo e(ucfirst(str_replace('_', ' ', $paymentStatus))); ?></span>
                </td>
                <td data-label="Cashier"><div class="transaction-cashier"><span class="transaction-cashier-avatar" aria-hidden="true"><?php echo e($cashierInitials); ?></span><strong><?php echo e($transaction->cashier?->username ?? 'Unknown'); ?></strong></div></td>
                <td data-label="Transaction Status"><span class="status <?php echo e($statusClass); ?>"><?php echo e(ucfirst(str_replace('_', ' ', $status))); ?></span></td>
                <td data-label="Actions">
                    <div class="table-actions transaction-actions">
                        <a href="<?php echo e(route('pos.receipt', $transaction)); ?>" class="btn btn-secondary btn-sm transaction-action" aria-label="View receipt <?php echo e($transaction->receipt_no); ?>">
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
<?php endif; ?>
                            View Receipt
                        </a>
                        <?php if($canRecordCreditPayment): ?>
                        <a href="<?php echo e(route('pos.credit-payments.create', $transaction)); ?>" class="btn btn-primary btn-sm transaction-action" aria-label="Record payment for <?php echo e($transaction->receipt_no); ?>">
                            Record Payment
                        </a>
                        <?php endif; ?>
                        <?php if($canRefund): ?>
                        <a href="<?php echo e(route('pos.refund.form', $transaction)); ?>" class="btn btn-danger btn-sm transaction-action transaction-action--refund" aria-label="Return items from <?php echo e($transaction->receipt_no); ?>">
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
<?php endif; ?>
                            Return Items
                        </a>
                        <?php endif; ?>
                        <?php if($status === 'voided'): ?>
                        <span class="transaction-locked-state">No actions</span>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr>
                <td colspan="8" class="empty-state">No transactions found.</td>
            </tr>
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
<?php echo app('Illuminate\Foundation\Vite')('resources/css/views/dashboard-my-transactions-1.css'); ?>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\acrylink_newbie\resources\views/pos/transactions.blade.php ENDPATH**/ ?>