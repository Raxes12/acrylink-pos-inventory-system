<?php $__env->startSection('title', 'Receivables Report'); ?>
<?php $__env->startSection('page-label', 'Reports / Receivables'); ?>

<?php $__env->startSection('content'); ?>
<div class="reports-page ar-report-page">
<?php if (isset($component)) { $__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => 'Receivables Report','subtitle' => 'Review outstanding customer credit, due dates, payment progress, and account aging.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Receivables Report','subtitle' => 'Review outstanding customer credit, due dates, payment progress, and account aging.']); ?>
     <?php $__env->slot('actions', null, []); ?> 
        <a class="btn btn-secondary" href="<?php echo e(route('accounts-receivable.index')); ?>"><i class="bi bi-wallet2" aria-hidden="true"></i> Manage Accounts</a>
        <a href="<?php echo e(route('reports.accounts-receivable.export', request()->query())); ?>" class="btn report-export report-export--csv"><i class="bi bi-file-earmark-spreadsheet" aria-hidden="true"></i> Export CSV</a>
        <a href="<?php echo e(route('reports.accounts-receivable.export.pdf', request()->query())); ?>" class="btn report-export report-export--pdf"><i class="bi bi-file-earmark-pdf" aria-hidden="true"></i> Export PDF</a>
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

<?php if($errors->has('export')): ?><div class="alert alert-danger ar-report-alert"><?php echo e($errors->first('export')); ?></div><?php endif; ?>

<section class="card ar-collections-panel" aria-labelledby="arCollectionsHeading">
    <div class="ar-collections-summary">
        <span class="ar-collections-icon"><i class="bi bi-wallet2" aria-hidden="true"></i></span>
        <div>
            <span class="ar-collections-label" id="arCollectionsHeading">Total Customer Collections</span>
            <strong>&#8369;<?php echo e(number_format($totalCollections, 2)); ?></strong>
            <small>Credit account payments received during <?php echo e(strtolower($collectionPeriodLabel)); ?></small>
        </div>
    </div>
    <div class="ar-collections-period">
        <span>Reporting period</span>
        <nav aria-label="Customer collections reporting period">
            <?php $__currentLoopData = ['today' => 'Today', 'week' => 'This Week', 'month' => 'This Month', 'all' => 'All Time']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $period => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $periodQuery = array_filter(array_merge(request()->except(['page', 'collection_period']), ['collection_period' => $period]));
                ?>
                <a href="<?php echo e(route('reports.accounts-receivable', $periodQuery)); ?>" class="report-period-chip <?php echo e($collectionPeriod === $period ? 'is-active' : ''); ?>" <?php if($collectionPeriod === $period): ?> aria-current="page" <?php endif; ?>><?php echo e($label); ?></a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </nav>
    </div>
</section>

<form method="GET" action="<?php echo e(route('reports.accounts-receivable')); ?>" class="card ar-report-filter" data-live-filter>
    <input type="hidden" name="collection_period" value="<?php echo e($collectionPeriod); ?>">
    <div class="ar-filter-heading"><div><strong>Find receivables</strong><span>Search and dropdown filters update automatically. Apply date ranges when ready.</span></div><span class="live-filter-status" data-live-filter-status role="status" aria-live="polite"></span></div>
    <div class="ar-filter-primary">
        <label class="ar-filter-search"><span>Customer</span><div><i class="bi bi-search" aria-hidden="true"></i><input type="search" name="customer" value="<?php echo e($filters['customer'] ?? ''); ?>" placeholder="Name or customer code" autocomplete="off"></div></label>
        <label class="ar-filter-search ar-filter-receipt"><span>Receipt</span><div><i class="bi bi-receipt" aria-hidden="true"></i><input type="search" name="receipt" value="<?php echo e($filters['receipt'] ?? ''); ?>" placeholder="Receipt number" autocomplete="off"></div></label>
        <label><span>Payment status</span><select name="payment_status"><option value="">All open statuses</option><option value="unpaid" <?php if(($filters['payment_status'] ?? '') === 'unpaid'): echo 'selected'; endif; ?>>Unpaid</option><option value="partially_paid" <?php if(($filters['payment_status'] ?? '') === 'partially_paid'): echo 'selected'; endif; ?>>Partially Paid</option><option value="overdue" <?php if(($filters['payment_status'] ?? '') === 'overdue'): echo 'selected'; endif; ?>>Overdue</option></select></label>
        <label><span>Aging</span><select name="aging"><option value="">All aging periods</option><option value="not_due" <?php if(($filters['aging'] ?? '') === 'not_due'): echo 'selected'; endif; ?>>Not Yet Due</option><option value="1_30" <?php if(($filters['aging'] ?? '') === '1_30'): echo 'selected'; endif; ?>>1-30 Days Overdue</option><option value="31_60" <?php if(($filters['aging'] ?? '') === '31_60'): echo 'selected'; endif; ?>>31-60 Days Overdue</option><option value="61_90" <?php if(($filters['aging'] ?? '') === '61_90'): echo 'selected'; endif; ?>>61-90 Days Overdue</option><option value="90_plus" <?php if(($filters['aging'] ?? '') === '90_plus'): echo 'selected'; endif; ?>>90+ Days Overdue</option></select></label>
    </div>
    <details class="ar-advanced-filters" <?php if(collect($filters)->only(['due_from','due_to','transaction_from','transaction_to'])->filter()->isNotEmpty()): ?> open <?php endif; ?>>
        <summary><i class="bi bi-calendar3" aria-hidden="true"></i> Date filters <span>Optional</span></summary>
        <div class="ar-date-filters">
            <label><span>Due from</span><input type="date" name="due_from" value="<?php echo e($filters['due_from'] ?? ''); ?>" data-live-manual></label>
            <label><span>Due to</span><input type="date" name="due_to" value="<?php echo e($filters['due_to'] ?? ''); ?>" data-live-manual></label>
            <label><span>Sale from</span><input type="date" name="transaction_from" value="<?php echo e($filters['transaction_from'] ?? ''); ?>" data-live-manual></label>
            <label><span>Sale to</span><input type="date" name="transaction_to" value="<?php echo e($filters['transaction_to'] ?? ''); ?>" data-live-manual></label>
        </div>
        <?php $withoutDates = request()->except(['page', 'due_from', 'due_to', 'transaction_from', 'transaction_to']); ?>
        <div class="ar-filter-actions">
            <button class="btn btn-primary btn-sm" type="submit"><i class="bi bi-calendar-check" aria-hidden="true"></i> Apply Dates</button>
            <a class="btn btn-secondary btn-sm" href="<?php echo e(route('reports.accounts-receivable', $withoutDates)); ?>" data-live-filter-clear-fields="due_from,due_to,transaction_from,transaction_to">Clear Dates</a>
        </div>
    </details>
</form>

<?php
    $totalOutstanding = collect($aging)->sum();
    $agingOptions = [
        'not_due' => ['label' => 'Not Yet Due', 'filter' => 'not_due', 'icon' => 'bi-calendar-check'],
        'days_1_30' => ['label' => '1-30 Days', 'filter' => '1_30', 'icon' => 'bi-clock'],
        'days_31_60' => ['label' => '31-60 Days', 'filter' => '31_60', 'icon' => 'bi-exclamation-circle'],
        'days_61_90' => ['label' => '61-90 Days', 'filter' => '61_90', 'icon' => 'bi-exclamation-triangle'],
        'days_90_plus' => ['label' => '90+ Days', 'filter' => '90_plus', 'icon' => 'bi-shield-exclamation'],
    ];
?>
<section class="ar-aging-workspace section-spaced" data-live-sync="ar-aging" aria-label="Receivables aging summary">
    <article class="card ar-outstanding-hero"><div class="ar-aging-top"><span class="ar-aging-icon"><i class="bi bi-cash-stack" aria-hidden="true"></i></span><span>Total Outstanding</span></div><strong>&#8369;<?php echo e(number_format($totalOutstanding, 2)); ?></strong><small>Across <?php echo e(number_format($transactions->total())); ?> open receivable records</small></article>
    <?php $__currentLoopData = $agingOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php $query = array_filter(array_merge(request()->except('page'), ['aging' => $option['filter']])); ?>
        <a class="card ar-aging-card ar-aging-card--<?php echo e($key); ?> <?php echo e(($filters['aging'] ?? '') === $option['filter'] ? 'is-active' : ''); ?>" href="<?php echo e(route('reports.accounts-receivable', $query)); ?>">
            <div class="ar-aging-top"><span class="ar-aging-icon"><i class="bi <?php echo e($option['icon']); ?>" aria-hidden="true"></i></span><span><?php echo e($option['label']); ?></span></div>
            <strong>&#8369;<?php echo e(number_format($aging[$key] ?? 0, 2)); ?></strong>
            <small><?php echo e($totalOutstanding > 0 ? number_format((($aging[$key] ?? 0) / $totalOutstanding) * 100, 1) : '0.0'); ?>% of outstanding</small>
        </a>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</section>

<section class="card ar-report-table" data-live-results>
    <div class="card-header"><div><span class="card-title"><i class="bi bi-list-check" aria-hidden="true"></i> Open Receivables</span><span class="ar-report-caption">Customer balances ordered by the nearest due date.</span></div><span class="tag tag--warning"><?php echo e($transactions->total()); ?> records</span></div>
    <div class="table-wrap" tabindex="0" role="region" aria-label="Open receivables report"><table><thead><tr><th>Customer</th><th>Receipt</th><th>Sale Date</th><th>Due Date</th><th class="text-right">Net Sale</th><th>Payment Progress</th><th class="text-right">Outstanding</th><th>Status</th></tr></thead><tbody>
    <?php $__empty_1 = true; $__currentLoopData = $transactions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $transaction): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <?php
            $status = $report->status($transaction);
            $dueDate = $transaction->creditSale->due_date;
            $daysDue = today()->diffInDays($dueDate, false);
            $paidPercent = (float) $transaction->ar_net_amount > 0 ? min(100, ((float) $transaction->ar_paid_amount / (float) $transaction->ar_net_amount) * 100) : 0;
            $initials = collect(explode(' ', $transaction->customer?->customer_name ?? '?'))->filter()->take(2)->map(fn ($part) => strtoupper(substr($part, 0, 1)))->implode('');
        ?>
        <tr class="<?php echo e($status === 'overdue' ? 'ar-report-row--overdue' : ''); ?>">
            <td><div class="ar-customer-cell"><span class="ar-customer-avatar" aria-hidden="true"><?php echo e($initials); ?></span><div><strong><?php echo e($transaction->customer?->customer_name ?? 'Unknown customer'); ?></strong><small><?php echo e($transaction->customer?->customer_code ?? 'No customer code'); ?></small></div></div></td>
            <td><a href="<?php echo e(route('pos.receipt', $transaction)); ?>" class="report-receipt"><?php echo e($transaction->receipt_no); ?></a></td>
            <td><div class="ar-date-cell"><strong><?php echo e($transaction->date->format('M d, Y')); ?></strong><small>Credit sale</small></div></td>
            <td><div class="ar-date-cell"><strong><?php echo e($dueDate->format('M d, Y')); ?></strong><small class="<?php echo e($daysDue < 0 ? 'ar-due-danger' : ''); ?>"><?php if($daysDue < 0): ?><?php echo e(abs($daysDue)); ?> day<?php echo e(abs($daysDue) === 1 ? '' : 's'); ?> overdue <?php elseif($daysDue === 0): ?>Due today <?php else: ?> Due in <?php echo e($daysDue); ?> days <?php endif; ?></small></div></td>
            <td class="text-right ar-net-sale">&#8369;<?php echo e(number_format((float) $transaction->ar_net_amount, 2)); ?></td>
            <td><div class="ar-payment-progress"><div><span>&#8369;<?php echo e(number_format((float) $transaction->ar_paid_amount, 2)); ?> paid</span><strong><?php echo e(number_format($paidPercent, 0)); ?>%</strong></div><span class="ar-progress-track"><i style="width:<?php echo e($paidPercent); ?>%"></i></span></div></td>
            <td class="text-right ar-report-balance">&#8369;<?php echo e(number_format((float) $transaction->ar_balance_amount, 2)); ?></td>
            <td><span class="status <?php echo e($status === 'overdue' ? 'status-danger' : 'status-low'); ?>"><?php echo e(ucfirst(str_replace('_', ' ', $status))); ?></span></td>
        </tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <?php if (isset($component)) { $__componentOriginal074a021b9d42f490272b5eefda63257c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal074a021b9d42f490272b5eefda63257c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.empty-state','data' => ['colspan' => '8','title' => 'No open receivables','message' => 'No customer accounts match the selected filters.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['colspan' => '8','title' => 'No open receivables','message' => 'No customer accounts match the selected filters.']); ?>
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
    </tbody></table></div>
    <div class="view-blades-footer"><div class="pagination-summary">Showing <?php echo e($transactions->firstItem() ?? 0); ?>-<?php echo e($transactions->lastItem() ?? 0); ?> of <?php echo e($transactions->total()); ?> receivables</div><?php echo e($transactions->links()); ?></div>
</section>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<?php echo app('Illuminate\Foundation\Vite')('resources/css/views/reports-index-1.css'); ?>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\acrylink_newbie\resources\views/reports/accounts-receivable.blade.php ENDPATH**/ ?>