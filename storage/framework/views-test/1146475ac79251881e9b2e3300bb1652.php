<?php $__env->startSection('title', 'Sales Reports'); ?>
<?php $__env->startSection('page-label', 'Sales Reports'); ?>

<?php $__env->startSection('content'); ?>
<div class="reports-page">
<?php if (isset($component)) { $__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => 'Sales Reports','subtitle' => 'Track revenue, collections, cashier performance, and transaction activity.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Sales Reports','subtitle' => 'Track revenue, collections, cashier performance, and transaction activity.']); ?>
     <?php $__env->slot('actions', null, []); ?> 
        <a href="<?php echo e($exportUrl); ?>" class="btn report-export report-export--csv"><i class="bi bi-file-earmark-spreadsheet" aria-hidden="true"></i> Export CSV</a>
        <a href="<?php echo e($exportPdfUrl); ?>" class="btn report-export report-export--pdf"><i class="bi bi-file-earmark-pdf" aria-hidden="true"></i> Export PDF</a>
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

<section class="card report-period-panel" aria-labelledby="report-period-heading">
    <div class="report-period-top">
        <div class="report-period-copy">
            <span class="report-period-eyebrow" id="report-period-heading">Reporting period</span>
            <span class="report-period-value"><strong><?php echo e($reportLabel); ?></strong><?php if($range === 'custom'): ?><span class="report-period-state">Custom</span><?php endif; ?></span>
        </div>
        <nav class="report-period-presets" aria-label="Quick reporting periods">
            <?php $__currentLoopData = ['today' => 'Today', 'week' => 'This Week', 'month' => 'This Month', 'year' => 'This Year', 'all' => 'All Time']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <a href="<?php echo e(route('reports.index', ['range' => $key])); ?>" class="report-period-chip <?php echo e($range === $key ? 'is-active' : ''); ?>" <?php if($range === $key): ?> aria-current="page" <?php endif; ?>><?php echo e($label); ?></a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </nav>
    </div>
    <form id="salesReportDateFilter" method="GET" action="<?php echo e(route('reports.index')); ?>" class="report-date-filter">
        <input type="hidden" name="range" value="custom">
        <div class="report-date-filter-intro">
            <span class="report-date-filter-icon" aria-hidden="true"><i class="bi bi-calendar-range"></i></span>
            <span><strong>Custom date range</strong><small>Select one date or an exact start and end date.</small></span>
        </div>
        <div class="report-date-fields">
            <label><span>From</span><input type="date" name="date_from" value="<?php echo e($dateFrom); ?>" <?php if($dateTo): ?> max="<?php echo e($dateTo); ?>" <?php endif; ?> aria-label="Report start date"></label>
            <label><span>To</span><input type="date" name="date_to" value="<?php echo e($dateTo); ?>" <?php if($dateFrom): ?> min="<?php echo e($dateFrom); ?>" <?php endif; ?> aria-label="Report end date"></label>
        </div>
        <div class="report-date-actions">
            <button class="btn btn-primary btn-sm" type="submit"><i class="bi bi-funnel" aria-hidden="true"></i> Apply dates</button>
            <?php if($range === 'custom'): ?><a href="<?php echo e(route('reports.index')); ?>" class="btn btn-secondary btn-sm">Reset</a><?php endif; ?>
        </div>
    </form>
</section>

<?php if($errors->has('export')): ?><div class="alert alert-danger report-alert"><?php echo e($errors->first('export')); ?></div><?php endif; ?>

<section class="sales-kpi-grid" aria-label="Sales summary for <?php echo e($reportLabel); ?>">
    <article class="card sales-kpi sales-kpi--hero"><div class="sales-kpi-top"><span class="sales-kpi-label">Net Sales</span><span class="sales-kpi-icon"><?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'money']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'money']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?></span></div><div class="sales-kpi-value">&#8369;<?php echo e(number_format($totalSales, 2)); ?></div><div class="sales-kpi-note">After discounts and &#8369;<?php echo e(number_format($totalRefunds, 2)); ?> in refunds</div><svg class="sales-kpi-spark" viewBox="0 0 92 46" aria-hidden="true"><polyline points="2,40 16,29 29,34 43,17 57,24 72,9 90,16" /></svg></article>
    <article class="card sales-kpi sales-kpi--revenue"><div class="sales-kpi-top"><span class="sales-kpi-label">Gross Revenue</span><span class="sales-kpi-icon"><?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'sales-report']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'sales-report']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?></span></div><div class="sales-kpi-value">&#8369;<?php echo e(number_format($grossRevenue, 2)); ?></div><div class="sales-kpi-note">Before discounts, after returned items</div></article>
    <article class="card sales-kpi sales-kpi--collections">
        <div class="sales-kpi-top"><span class="sales-kpi-label">Collections</span><span class="sales-kpi-icon"><i class="bi bi-wallet2" aria-hidden="true"></i></span></div>
        <div class="sales-kpi-value">&#8369;<?php echo e(number_format($totalCollections, 2)); ?></div>
        <div class="sales-kpi-note">Actual payments received during <?php echo e(strtolower($reportLabel)); ?></div>
        <div class="collections-breakdown" aria-label="Collections breakdown">
            <div><span><i class="collections-dot collections-dot--sales"></i>From selected-period sales</span><strong>&#8369;<?php echo e(number_format($salesPeriodCollections, 2)); ?></strong></div>
            <div><span><i class="collections-dot collections-dot--credit"></i>From earlier credit sales</span><strong>&#8369;<?php echo e(number_format($priorCreditCollections, 2)); ?></strong></div>
        </div>
        <details class="collections-explanation">
            <summary><i class="bi bi-info-circle" aria-hidden="true"></i> Why can this be higher than Net Sales?</summary>
            <p>Net Sales follows the sale date. Collections follows the payment date, so it also includes customer payments received now for credit sales recorded in an earlier period.</p>
        </details>
    </article>
    <article class="card sales-kpi sales-kpi--credit"><div class="sales-kpi-top"><span class="sales-kpi-label">Credit Sales</span><span class="sales-kpi-icon"><i class="bi bi-receipt" aria-hidden="true"></i></span></div><div class="sales-kpi-value">&#8369;<?php echo e(number_format($totalCreditSales, 2)); ?></div><div class="sales-kpi-note">Net sales charged to customer accounts</div></article>
    <article class="card sales-kpi sales-kpi--discount"><div class="sales-kpi-top"><span class="sales-kpi-label">Total Discounts</span><span class="sales-kpi-icon"><?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'percent']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'percent']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?></span></div><div class="sales-kpi-value">&#8369;<?php echo e(number_format($totalDiscount, 2)); ?></div><div class="sales-kpi-note">Discounts remaining after refunds</div></article>
    <article class="card sales-kpi sales-kpi--average"><div class="sales-kpi-top"><span class="sales-kpi-label">Average Transaction</span><span class="sales-kpi-icon"><?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'average']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'average']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?></span></div><div class="sales-kpi-value">&#8369;<?php echo e(number_format($avgSale, 2)); ?></div><div class="sales-kpi-note">Average net value per transaction</div></article>
</section>

<section class="report-analytics-grid">
    <article class="card report-analytics-card">
        <div class="card-header"><div><span class="card-title"><i class="bi bi-graph-up-arrow" aria-hidden="true"></i> Sales Trend</span><span class="report-card-caption">Daily sales for the selected period</span></div><span class="report-legend"><i></i> Net Sales</span></div>
        <div class="card-body sales-chart-wrap">
            <?php if($byDate->isNotEmpty()): ?>
                <?php
                    $trendEntries = $byDate->map(fn ($amount, $date) => ['date' => $date, 'amount' => (float) $amount])->values();
                    $trendMax = max(1, (float) $trendEntries->max('amount'));
                    $trendCount = $trendEntries->count();
                    $trendPoints = $trendEntries->map(function ($entry, $index) use ($trendCount, $trendMax) {
                        $x = $trendCount === 1 ? 310 : 28 + ($index * (564 / ($trendCount - 1)));
                        $y = 170 - (($entry['amount'] / $trendMax) * 132);
                        return ['x' => round($x, 1), 'y' => round($y, 1), 'date' => $entry['date'], 'amount' => $entry['amount']];
                    });
                    $polyline = $trendPoints->map(fn ($point) => $point['x'].','.$point['y'])->implode(' ');
                    $area = '28,176 '.$polyline.' 592,176';
                ?>
                <svg class="sales-line-chart" viewBox="0 0 620 220" role="img" aria-label="Net sales trend for <?php echo e($reportLabel); ?>">
                    <defs><linearGradient id="salesTrendArea" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="var(--accent)" stop-opacity=".22"/><stop offset="1" stop-color="var(--accent)" stop-opacity="0"/></linearGradient></defs>
                    <g class="chart-grid"><line x1="28" y1="38" x2="592" y2="38"/><line x1="28" y1="82" x2="592" y2="82"/><line x1="28" y1="126" x2="592" y2="126"/><line x1="28" y1="176" x2="592" y2="176"/></g>
                    <polygon class="chart-area" points="<?php echo e($area); ?>"/><polyline class="chart-line" points="<?php echo e($polyline); ?>"/>
                    <?php $__currentLoopData = $trendPoints; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $point): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><circle class="chart-point" cx="<?php echo e($point['x']); ?>" cy="<?php echo e($point['y']); ?>" r="4"><title><?php echo e(\Carbon\Carbon::parse($point['date'])->format('M d, Y')); ?>: ₱<?php echo e(number_format($point['amount'], 2)); ?></title></circle><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php $__currentLoopData = $trendPoints; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $point): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?> <?php if($loop->first || $loop->last || $loop->index % max(1, (int) ceil($trendCount / 5)) === 0): ?><text class="chart-label" x="<?php echo e($point['x']); ?>" y="205" text-anchor="middle"><?php echo e(\Carbon\Carbon::parse($point['date'])->format('M d')); ?></text><?php endif; ?> <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </svg>
            <?php else: ?><div class="report-empty"><i class="bi bi-graph-up" aria-hidden="true"></i><strong>No sales data</strong><span>No completed sales were found for this period.</span></div><?php endif; ?>
        </div>
    </article>
    <article class="card report-analytics-card">
        <div class="card-header"><div><span class="card-title">Sales by Cashier</span><span class="report-card-caption">Net sales contribution by team member</span></div><span class="report-count"><?php echo e($byCashier->count()); ?> cashiers</span></div>
        <div class="card-body report-bars"><?php $maxCash = $byCashier->max() ?: 1; ?>
            <?php $__empty_1 = true; $__currentLoopData = $byCashier; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $name => $amt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><div class="cashier-row"><span class="cashier-avatar" aria-hidden="true"><?php echo e(strtoupper(substr($name, 0, 1))); ?></span><div class="cashier-performance"><div class="cashier-heading"><strong title="<?php echo e($name); ?>"><?php echo e($name); ?></strong><span>&#8369;<?php echo e(number_format($amt, 2)); ?></span></div><div class="sales-track"><div class="sales-fill sales-fill--cashier" style="width:<?php echo e(max(2, ($amt / $maxCash) * 100)); ?>%"></div></div></div></div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><div class="report-empty"><i class="bi bi-people" aria-hidden="true"></i><strong>No cashier activity</strong><span>No cashier sales were found for this period.</span></div><?php endif; ?>
        </div>
    </article>
</section>

<div class="report-lower-grid">
<section class="card report-table-card report-top-items">
    <div class="card-header"><div><span class="card-title"><i class="bi bi-trophy" aria-hidden="true"></i> Top Selling Items</span><span class="report-card-caption">Best-performing products during <?php echo e(strtolower($reportLabel)); ?>, ranked by units sold</span></div><span class="tag tag--accent">Top <?php echo e($topProducts->count()); ?></span></div>
    <?php $topRevenue = max(1, (float) $topProducts->sum('revenue')); ?>
    <div class="table-wrap" tabindex="0" role="region" aria-label="Top selling items table"><table><thead><tr><th>#</th><th>Item</th><th class="text-right">Units Sold</th><th class="text-right">Revenue</th><th class="text-right">Share</th></tr></thead><tbody>
        <?php $__empty_1 = true; $__currentLoopData = $topProducts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><tr><td><span class="report-rank <?php echo e($loop->iteration <= 3 ? 'report-rank--top' : ''); ?>"><?php echo e($loop->iteration); ?></span></td><td><div class="report-item-name"><strong><?php echo e($product->product_name); ?></strong><span><?php echo e(number_format(($product->revenue / $topRevenue) * 100, 1)); ?>% of top-item revenue</span></div></td><td class="text-right"><span class="report-units"><?php echo e(number_format($product->units)); ?></span></td><td class="text-right report-money">&#8369;<?php echo e(number_format($product->revenue, 2)); ?></td><td class="text-right"><div class="report-share-cell"><strong><?php echo e(number_format(($product->revenue / $topRevenue) * 100, 1)); ?>%</strong><span><i style="width:<?php echo e(($product->revenue / $topRevenue) * 100); ?>%"></i></span></div></td></tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><tr><td colspan="5"><div class="report-empty report-empty--table"><strong>No sold items</strong><span>Items sold during this period will appear here.</span></div></td></tr><?php endif; ?>
    </tbody></table></div>
</section>

<section class="card report-table-card" data-live-results>
    <div class="card-header"><div><span class="card-title"><i class="bi bi-receipt-cutoff" aria-hidden="true"></i> Transaction History</span><span class="report-card-caption">Receipt activity included in <?php echo e($reportLabel); ?></span></div><span class="tag tag--accent"><?php echo e($transactions->total()); ?> transactions</span></div>
    <div class="table-wrap" tabindex="0" role="region" aria-label="Sales transaction history"><table><thead><tr><th>Receipt</th><th>Date &amp; Time</th><th>Cashier</th><th>Payment</th><th class="text-right">Discount</th><th class="text-right">Refunded</th><th class="text-right">Net Total</th><th>Action</th></tr></thead><tbody>
        <?php $__empty_1 = true; $__currentLoopData = $transactions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <?php $dt = \Carbon\Carbon::parse($t->date->format('Y-m-d').' '.$t->time); $refundedAmount = $t->totalRefunded(); ?>
            <tr><td><a class="report-receipt" href="<?php echo e(route('pos.receipt', $t)); ?>"><?php echo e($t->receipt_no); ?></a></td><td><div class="report-date"><strong><?php echo e($dt->format('M d, Y')); ?></strong><span><?php echo e($dt->format('h:i A')); ?></span></div></td><td><strong><?php echo e($t->cashier?->name ?? 'Unknown'); ?></strong></td><td><span class="status status-active"><?php echo e(\App\Models\Transaction::paymentMethodLabel($t->payment_method)); ?></span></td><td class="text-right report-discount"><?php if($t->netDiscount() > 0): ?>&#8369;<?php echo e(number_format($t->netDiscount(), 2)); ?><?php else: ?>&mdash;<?php endif; ?></td><td class="text-right <?php echo e($refundedAmount > 0 ? 'report-refund' : 'text-muted'); ?>"><?php if($refundedAmount > 0): ?>&minus; &#8369;<?php echo e(number_format($refundedAmount, 2)); ?><?php else: ?>&mdash;<?php endif; ?></td><td class="text-right report-money">&#8369;<?php echo e(number_format($t->netTotal(), 2)); ?></td><td><a href="<?php echo e(route('pos.receipt', $t)); ?>" class="btn btn-secondary btn-sm report-view"><i class="bi bi-eye" aria-hidden="true"></i> View</a></td></tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><tr><td colspan="8"><div class="report-empty report-empty--table"><strong>No transactions found</strong><span>Choose another reporting period to view sales activity.</span></div></td></tr><?php endif; ?>
    </tbody></table></div>
    <div class="view-blades-footer"><div class="pagination-summary">Showing <?php echo e($transactions->firstItem() ?? 0); ?>-<?php echo e($transactions->lastItem() ?? 0); ?> of <?php echo e($transactions->total()); ?> transactions</div>
        <?php if($transactions->hasPages()): ?>
            <?php $currentPage = $transactions->currentPage(); $lastPage = $transactions->lastPage(); $pages = collect([1, $currentPage - 1, $currentPage, $currentPage + 1, $lastPage])->filter(fn ($page) => $page >= 1 && $page <= $lastPage)->unique()->sort()->values(); $previousPage = null; ?>
            <nav class="rules-pagination" aria-label="Transaction pagination">
                <?php if($transactions->onFirstPage()): ?><span class="rules-page is-disabled" aria-disabled="true">Previous</span><?php else: ?><a class="rules-page" href="<?php echo e($transactions->previousPageUrl()); ?>" rel="prev">Previous</a><?php endif; ?>
                <?php $__currentLoopData = $pages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if($previousPage && $page > $previousPage + 1): ?><span class="rules-page rules-page--gap" aria-hidden="true">...</span><?php endif; ?> <?php if($page === $currentPage): ?><span class="rules-page is-active" aria-current="page"><?php echo e($page); ?></span><?php else: ?><a class="rules-page" href="<?php echo e($transactions->url($page)); ?>"><?php echo e($page); ?></a><?php endif; ?> <?php $previousPage = $page; ?> <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php if($transactions->hasMorePages()): ?><a class="rules-page" href="<?php echo e($transactions->nextPageUrl()); ?>" rel="next">Next</a><?php else: ?><span class="rules-page is-disabled" aria-disabled="true">Next</span><?php endif; ?>
            </nav>
        <?php endif; ?>
    </div>
</section>
</div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<?php echo app('Illuminate\Foundation\Vite')('resources/css/views/reports-index-1.css'); ?>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\acrylink_newbie\resources\views\reports\index.blade.php ENDPATH**/ ?>