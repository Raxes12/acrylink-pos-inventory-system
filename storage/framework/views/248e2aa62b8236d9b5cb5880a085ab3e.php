<?php $__env->startSection('title', 'Dashboard'); ?>
<?php $__env->startSection('page-label', 'Dashboard'); ?>

<?php $__env->startSection('content'); ?>
<?php
    $canViewBusinessMetrics = !auth()->user()->isCashier();
    $salesTrendClass = is_null($periodChange) ? 'is-neutral' : ($periodChange >= 0 ? 'text-green' : 'text-danger');
    $salesTrendLabel = is_null($periodChange)
        ? 'No prior-period sales for comparison'
        : (($periodChange >= 0 ? '+' : '') . number_format($periodChange, 1) . '% ' . $periodComparisonLabel);
?>

<?php if (isset($component)) { $__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => 'Dashboard','subtitle' => ''.e($canViewBusinessMetrics ? 'Sales, stock health, and operational activity at a glance.' : 'Your sales workspace, recent receipts, and stock activity.').'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Dashboard','subtitle' => ''.e($canViewBusinessMetrics ? 'Sales, stock health, and operational activity at a glance.' : 'Your sales workspace, recent receipts, and stock activity.').'']); ?>
     <?php $__env->slot('actions', null, []); ?> 
        <?php if($canViewBusinessMetrics): ?>
        <a href="<?php echo e(route('reports.index')); ?>" class="btn btn-secondary">
            <?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
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
<?php endif; ?>
            Sales Report
        </a>
        <?php endif; ?>
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
            Start Sale
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

<div data-dashboard-performance-scope>
<?php if($canViewBusinessMetrics): ?>
<div class="db-section-heading db-section-heading--overview">
    <div>
        <span class="db-section-kicker">Performance</span>
        <h2>Business Overview</h2>
        <span class="db-section-note"><?php echo e($salesPeriodLabel); ?> &middot; <?php echo e($salesPeriodDateRange); ?></span>
    </div>
    <nav class="db-sales-periods db-dashboard-slicer" aria-label="Dashboard performance period">
        <?php $__currentLoopData = ['today' => 'Today', 'weekly' => 'Weekly', 'monthly' => 'Monthly', 'quarterly' => 'Quarterly']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $period => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <a href="<?php echo e(route('dashboard', ['sales_period' => $period])); ?>" class="db-sales-period <?php echo e($salesPeriod === $period ? 'is-active' : ''); ?>" data-sales-period="<?php echo e($period); ?>" <?php if($salesPeriod === $period): ?> aria-current="page" <?php endif; ?>><?php echo e($label); ?></a>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </nav>
</div>
<section class="db-command-grid" aria-label="<?php echo e($salesPeriodLabel); ?> business summary">
    <a href="<?php echo e(route('pos.transactions', ['date_from' => $salesPeriodStart->toDateString(), 'date_to' => today()->toDateString()])); ?>" class="db-command-card db-command-card--sales">
        <div class="db-command-top"><span class="db-command-icon"><?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
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
<?php endif; ?></span><span>Net Sales</span><i class="bi bi-chevron-right" aria-hidden="true"></i></div>
        <strong data-count="<?php echo e(round($periodSales)); ?>" data-prefix="&#8369;">&#8369;<?php echo e(number_format($periodSales, 0)); ?></strong>
        <div class="db-command-trend <?php echo e($salesTrendClass); ?>"><i class="bi <?php echo e(is_null($periodChange) ? 'bi-dash' : ($periodChange >= 0 ? 'bi-arrow-up' : 'bi-arrow-down')); ?>" aria-hidden="true"></i> <?php echo e($salesTrendLabel); ?></div>
        <small>Refund-adjusted sales for <?php echo e(strtolower($salesPeriodLabel)); ?></small>
    </a>
    <a href="<?php echo e(route('reports.index', ['date_from' => $salesPeriodStart->toDateString(), 'date_to' => today()->toDateString()])); ?>" class="db-command-card db-command-card--collections">
        <div class="db-command-top"><span class="db-command-icon"><i class="bi bi-wallet2" aria-hidden="true"></i></span><span>Collections</span><i class="bi bi-chevron-right" aria-hidden="true"></i></div>
        <strong data-count="<?php echo e(round($periodCollections)); ?>" data-prefix="&#8369;">&#8369;<?php echo e(number_format($periodCollections, 0)); ?></strong>
        <div class="db-command-trend">Payments received during this period</div>
        <small>Includes collections for earlier credit sales</small>
    </a>
    <a href="<?php echo e(route('pos.transactions', ['date_from' => $salesPeriodStart->toDateString(), 'date_to' => today()->toDateString()])); ?>" class="db-command-card db-command-card--receivables">
        <div class="db-command-top"><span class="db-command-icon"><?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
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
<?php endif; ?></span><span>Completed Sales</span><i class="bi bi-chevron-right" aria-hidden="true"></i></div>
        <strong data-count="<?php echo e($periodTransactions); ?>"><?php echo e(number_format($periodTransactions)); ?></strong>
        <div class="db-command-trend">Non-voided transactions</div>
        <small><?php echo e($salesPeriodDateRange); ?></small>
    </a>
    <a href="<?php echo e(route('pos.transactions', ['date_from' => $salesPeriodStart->toDateString(), 'date_to' => today()->toDateString()])); ?>" class="db-command-card db-command-card--inventory">
        <div class="db-command-top"><span class="db-command-icon"><i class="bi bi-receipt" aria-hidden="true"></i></span><span>Average Sale</span><i class="bi bi-chevron-right" aria-hidden="true"></i></div>
        <strong data-count="<?php echo e(round($periodAverageSale)); ?>" data-prefix="&#8369;">&#8369;<?php echo e(number_format($periodAverageSale, 0)); ?></strong>
        <div class="db-command-trend">Average per completed transaction</div>
        <small>Based on <?php echo e(number_format($periodTransactions)); ?> sale<?php echo e($periodTransactions === 1 ? '' : 's'); ?></small>
    </a>
</section>
<aside class="db-current-snapshot" aria-label="Current operational snapshot">
    <span class="db-current-snapshot-label">Current snapshot</span>
    <a href="<?php echo e(route('accounts-receivable.index')); ?>">
        <span>Open receivables</span>
        <strong>&#8369;<?php echo e(number_format($receivableSummary['total_outstanding'], 2)); ?></strong>
        <small><?php echo e(number_format($overdueReceivables)); ?> overdue</small>
    </a>
    <a href="<?php echo e(route('inventory.stock-overview', ['stock_level' => 'attention'])); ?>">
        <span>Inventory attention</span>
        <strong><?php echo e(number_format($lowStock)); ?> variants</strong>
        <small><?php echo e($stockAlertSummary['critical']); ?> critical &middot; <?php echo e($stockAlertSummary['out']); ?> out</small>
    </a>
</aside>
<?php else: ?>
<section class="db-hero db-hero--cashier" aria-labelledby="cashierWorkspaceLabel">
    <div class="db-hero-main">
        <div class="db-hero-label" id="cashierWorkspaceLabel">
            <span class="db-hero-icon"><?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
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
<?php endif; ?></span>
            Cashier workspace
        </div>
        <div class="db-hero-total">Ready for the next sale</div>
        <p class="db-hero-copy"><?php echo e($todayTxns); ?> receipt<?php echo e($todayTxns !== 1 ? 's' : ''); ?> processed today.</p>
    </div>
    <div class="db-hero-actions">
        <a href="<?php echo e(route('pos.index')); ?>" class="btn btn-primary">Open POS Terminal</a>
    </div>
</section>
<?php endif; ?>

<?php if($canViewBusinessMetrics): ?>
<?php else: ?>
<section class="db-kpis grid-2" aria-label="Cashier summary">
    <div class="card metric-card">
        <div class="metric-topline">
            <span class="metric-label">My Sales Today</span>
            <span class="metric-icon icon-txn"><?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
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
<?php endif; ?></span>
        </div>
        <div class="metric-value" data-count="<?php echo e(round($todaySales)); ?>" data-prefix="&#8369;">&#8369;<?php echo e(number_format($todaySales, 0)); ?></div>
        <div class="metric-note"><?php echo e($todayTxns); ?> receipt<?php echo e($todayTxns !== 1 ? 's' : ''); ?> processed</div>
    </div>
    <div class="card metric-card <?php echo e($lowStock > 0 ? 'metric-card--warn' : 'metric-card--ok'); ?>">
        <div class="metric-topline">
            <span class="metric-label">Stock Attention</span>
            <span class="metric-icon icon-low"><?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'alerts']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'alerts']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?></span>
        </div>
        <div class="metric-value <?php echo e($lowStock > 0 ? 'metric-value--warn' : 'metric-value--ok'); ?>" data-count="<?php echo e($lowStock); ?>"><?php echo e($lowStock); ?></div>
        <div class="metric-note">Variants at or below minimum stock</div>
    </div>
</section>
<?php endif; ?>

<div class="db-section-heading db-section-heading--spaced">
    <div>
        <span class="db-section-kicker">Trends</span>
        <h2>Sales and Product Performance</h2>
    </div>
</div>

<div class="grid-2 db-row">
    <?php if($canViewBusinessMetrics): ?>
    <section class="card db-panel" aria-labelledby="salesTrendHeading" data-sales-performance>
        <div class="card-header">
            <span class="card-title" id="salesTrendHeading">
                <?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
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
<?php endif; ?>
                Sales Performance
            </span>
            <span class="db-period-label"><?php echo e($salesPeriodLabel); ?></span>
        </div>
        <p class="db-panel-caption">Net sales for <?php echo e(strtolower($salesPeriodLabel)); ?> &middot; <?php echo e($salesPeriodDateRange); ?><?php if($salesPeriod === 'quarterly'): ?> &middot; seven-day buckets; the final bucket may be partial <?php endif; ?></p>
        <div class="card-body db-trend-chart">
            <?php if($salesChart->sum('total') > 0): ?>
            <?php
                $chartMax = max(1, (float) $salesChart->max('total'));
                $chartCount = $salesChart->count();
                $chartPoints = $salesChart->values()->map(function ($day, $index) use ($chartCount, $chartMax) {
                    $x = $chartCount === 1 ? 350 : 58 + ($index * (590 / ($chartCount - 1)));
                    $y = 178 - (((float) $day['total'] / $chartMax) * 138);
                    return ['x' => round($x, 1), 'y' => round($y, 1), 'label' => $day['label'], 'total' => (float) $day['total']];
                });
                $chartLine = $chartPoints->map(fn ($point) => $point['x'].','.$point['y'])->implode(' ');
                $chartArea = '58,184 '.$chartLine.' 648,184';
                $chartTicks = collect([1, .6667, .3333, 0])->map(fn ($ratio) => [
                    'y' => 178 - ($ratio * 138),
                    'value' => $chartMax * $ratio,
                ]);
            ?>
            <svg class="db-sales-line" viewBox="0 0 680 225" role="img" aria-labelledby="salesChartTitle salesChartDescription">
                <title id="salesChartTitle">Net sales for <?php echo e(strtolower($salesPeriodLabel)); ?></title>
                <desc id="salesChartDescription">Sales from <?php echo e($salesPeriodDateRange); ?>. Use Tab to inspect each plotted value.</desc>
                <defs><linearGradient id="dashboardSalesArea" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="var(--accent)" stop-opacity=".24"/><stop offset="1" stop-color="var(--accent)" stop-opacity="0"/></linearGradient></defs>
                <g class="db-chart-grid">
                    <?php $__currentLoopData = $chartTicks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tick): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <line x1="58" y1="<?php echo e($tick['y']); ?>" x2="648" y2="<?php echo e($tick['y']); ?>"/>
                    <text class="db-chart-axis-label" x="50" y="<?php echo e($tick['y'] + 3); ?>" text-anchor="end">&#8369;<?php echo e($tick['value'] >= 1000 ? number_format($tick['value'] / 1000, 1).'k' : number_format($tick['value'], 0)); ?></text>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </g>
                <polygon class="db-chart-area" points="<?php echo e($chartArea); ?>"/><polyline class="db-chart-line" points="<?php echo e($chartLine); ?>"/>
                <?php $__currentLoopData = $chartPoints; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $point): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><circle class="db-chart-point" cx="<?php echo e($point['x']); ?>" cy="<?php echo e($point['y']); ?>" r="4" tabindex="0" data-chart-label="<?php echo e($point['label']); ?>" data-chart-value="<?php echo e(number_format($point['total'], 2, '.', '')); ?>" aria-label="<?php echo e($point['label']); ?>: &#8369;<?php echo e(number_format($point['total'], 2)); ?>"><title><?php echo e($point['label']); ?>: &#8369;<?php echo e(number_format($point['total'], 2)); ?></title></circle><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $labelInterval = max(1, (int) ceil($chartCount / 6));
                ?>
                <?php $__currentLoopData = $chartPoints; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $point): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?> <?php if($loop->first || $loop->last || ($loop->index % $labelInterval === 0)): ?><text class="db-chart-label" x="<?php echo e($point['x']); ?>" y="210" text-anchor="middle"><?php echo e($point['label']); ?></text><?php endif; ?> <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </svg>
            <div class="db-chart-tooltip" data-sales-chart-tooltip role="status" aria-live="polite" hidden></div>
            <?php else: ?><?php if (isset($component)) { $__componentOriginal074a021b9d42f490272b5eefda63257c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal074a021b9d42f490272b5eefda63257c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.empty-state','data' => ['title' => 'No sales recorded for this period','message' => 'The selected dates contain no completed, non-voided net sales.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'No sales recorded for this period','message' => 'The selected dates contain no completed, non-voided net sales.']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal074a021b9d42f490272b5eefda63257c)): ?>
<?php $attributes = $__attributesOriginal074a021b9d42f490272b5eefda63257c; ?>
<?php unset($__attributesOriginal074a021b9d42f490272b5eefda63257c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal074a021b9d42f490272b5eefda63257c)): ?>
<?php $component = $__componentOriginal074a021b9d42f490272b5eefda63257c; ?>
<?php unset($__componentOriginal074a021b9d42f490272b5eefda63257c); ?>
<?php endif; ?><?php endif; ?>
        </div>
        <div class="db-sales-summary" aria-label="<?php echo e($salesPeriodLabel); ?> sales summary">
            <div>
                <span>Total sales</span>
                <strong>&#8369;<?php echo e(number_format($trendSummary->revenue, 2)); ?></strong>
                <small>Net sales in this period</small>
            </div>
            <div>
                <span>Average daily sales</span>
                <strong>&#8369;<?php echo e(number_format($trendSummary->revenue / $salesPeriodDays, 2)); ?></strong>
                <small>Across <?php echo e(strtolower($salesPeriodLabel)); ?></small>
            </div>
            <div>
                <span>Total transactions</span>
                <strong><?php echo e(number_format($trendSummary->transaction_count)); ?></strong>
                <small><?php echo e($trendSummary->transaction_count === 1 ? 'Completed sale' : 'Completed sales'); ?></small>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <section class="card db-panel" aria-labelledby="topItemsHeading">
        <div class="card-header">
            <span class="card-title" id="topItemsHeading">
                <?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
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
<?php endif; ?>
                Top Selling Products
            </span>
            <span class="db-period-label"><?php echo e($salesPeriodLabel); ?> &middot; net units by variant</span>
        </div>
        <div class="card-body dashboard-chart-body">
            <?php if($topProducts->isNotEmpty()): ?>
            <div id="topProductsChart" class="horizontal-chart" aria-label="Top selling products based on units sold"></div>
            <?php else: ?>
            <?php if (isset($component)) { $__componentOriginal074a021b9d42f490272b5eefda63257c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal074a021b9d42f490272b5eefda63257c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.empty-state','data' => ['title' => 'No sales data yet','message' => 'Best-selling items will appear after sales are processed.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'No sales data yet','message' => 'Best-selling items will appear after sales are processed.']); ?>
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
        </div>
    </section>

    <?php if (! ($canViewBusinessMetrics)): ?>
    <section class="card db-panel" aria-labelledby="cashierTransactionsHeading">
        <div class="card-header">
            <span class="card-title" id="cashierTransactionsHeading"><?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
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
<?php endif; ?> My Recent Transactions</span>
            <a href="<?php echo e(route('dashboard.my-transactions')); ?>" class="btn btn-secondary btn-sm">View all</a>
        </div>
        <div class="list-stack db-list">
            <?php $__empty_1 = true; $__currentLoopData = $recentTxns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <?php $dt = \Carbon\Carbon::parse($t->date->format('Y-m-d').' '.$t->time); ?>
            <div class="txn-item">
                <div class="txn-main">
                    <span class="receipt-code"><?php echo e($t->receipt_no); ?></span>
                    <div class="txn-meta"><span><?php echo e($t->cashier?->name ?? 'Unassigned'); ?></span><span><?php echo e($dt->format('M d h:i A')); ?></span></div>
                </div>
                <div class="txn-side">
                    <span class="txn-total">&#8369;<?php echo e(number_format($t->netTotal(), 2)); ?></span>
                    <a href="<?php echo e(route('pos.receipt', $t)); ?>" class="btn btn-secondary btn-sm"><?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'eye']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'eye']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?> View</a>
                </div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <?php if (isset($component)) { $__componentOriginal074a021b9d42f490272b5eefda63257c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal074a021b9d42f490272b5eefda63257c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.empty-state','data' => ['title' => 'No transactions today','message' => 'Your completed sales will appear here.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'No transactions today','message' => 'Your completed sales will appear here.']); ?>
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
        </div>
    </section>
    <?php endif; ?>
</div>

<?php if($canViewBusinessMetrics): ?>
<div class="db-distribution-grid" aria-label="Sales distribution charts">
    <section class="card db-panel db-distribution-panel" aria-labelledby="categoryPerformanceHeading">
        <div class="card-header">
            <div>
                <span class="card-title" id="categoryPerformanceHeading"><?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'categories']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'categories']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?> Category Sales Distribution</span>
                <span class="db-panel-subtitle">Allocated net sales for <?php echo e(strtolower($salesPeriodLabel)); ?></span>
            </div>
            <span class="db-period-label"><?php echo e($salesPeriodLabel); ?></span>
        </div>
        <div class="card-body dashboard-chart-body db-distribution-body">
            <?php if($categoryPerformance->isNotEmpty()): ?>
            <div id="categoryChart" class="doughnut-chart-layout" aria-label="Sales revenue distribution by current category"></div>
            <?php else: ?>
            <?php if (isset($component)) { $__componentOriginal074a021b9d42f490272b5eefda63257c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal074a021b9d42f490272b5eefda63257c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.empty-state','data' => ['title' => 'No category sales yet','message' => 'Category performance will appear after sales are processed.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'No category sales yet','message' => 'Category performance will appear after sales are processed.']); ?>
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
        </div>
    </section>

    <section class="card db-panel db-distribution-panel" aria-labelledby="dashboardPaymentMethodsHeading" data-sales-payment-methods>
        <div class="card-header">
            <div>
                <span class="card-title" id="dashboardPaymentMethodsHeading"><i class="bi bi-credit-card-2-front" aria-hidden="true"></i> Sales by Payment Method</span>
                <span class="db-panel-subtitle">Net sales collected for <?php echo e(strtolower($salesPeriodLabel)); ?></span>
            </div>
            <span class="db-period-label"><?php echo e($salesPeriodLabel); ?></span>
        </div>
        <div class="card-body dashboard-chart-body db-distribution-body">
            <?php if($paymentMethodPerformance->isNotEmpty()): ?>
            <div id="paymentMethodChart" class="doughnut-chart-layout" aria-label="Net sales distribution by payment method"></div>
            <?php else: ?>
            <?php if (isset($component)) { $__componentOriginal074a021b9d42f490272b5eefda63257c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal074a021b9d42f490272b5eefda63257c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.empty-state','data' => ['title' => 'No payment-method sales yet','message' => 'Payment distribution will appear after completed sales are processed.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'No payment-method sales yet','message' => 'Payment distribution will appear after completed sales are processed.']); ?>
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
        </div>
    </section>
</div>

<div class="db-section-heading db-section-heading--spaced">
    <div>
        <span class="db-section-kicker">Stock and people</span>
        <h2>Inventory and Team</h2>
    </div>
</div>
<section class="card db-panel db-inventory-snapshot" aria-labelledby="inventorySnapshotHeading">
    <div class="card-header">
        <div>
            <span class="card-title" id="inventorySnapshotHeading"><?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
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
<?php endif; ?> Inventory Snapshot</span>
            <span class="db-panel-subtitle">Current stock quantity and valuation based on recorded cost price</span>
        </div>
        <a href="<?php echo e(route('inventory.stock-overview')); ?>" class="btn btn-secondary btn-sm">View inventory</a>
    </div>
    <div class="db-inventory-snapshot-grid">
        <div class="db-inventory-stat"><span class="db-inventory-stat-icon"><?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
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
<?php endif; ?></span><div><strong><?php echo e(number_format($inventorySummary['products'])); ?></strong><span>Products</span></div></div>
        <div class="db-inventory-stat"><span class="db-inventory-stat-icon"><i class="bi bi-sliders" aria-hidden="true"></i></span><div><strong><?php echo e(number_format($inventorySummary['variants'])); ?></strong><span>Variants</span></div></div>
        <div class="db-inventory-stat"><span class="db-inventory-stat-icon"><i class="bi bi-bar-chart" aria-hidden="true"></i></span><div><strong><?php echo e(number_format($inventorySummary['quantity'])); ?></strong><span>Units on hand</span></div></div>
        <div class="db-inventory-stat"><span class="db-inventory-stat-icon"><i class="bi bi-cash-stack" aria-hidden="true"></i></span><div><strong>&#8369;<?php echo e(number_format($inventorySummary['value'], 0)); ?></strong><span>Value at cost</span></div></div>
    </div>
</section>
<?php endif; ?>

<?php if($canViewBusinessMetrics): ?>
<div class="grid-2 db-row db-insight-row">
    <section class="card db-panel db-team-performance" aria-labelledby="cashierPerformanceHeading">
        <div class="card-header db-team-performance-header">
            <div><span class="card-title" id="cashierPerformanceHeading"><?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'users']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'users']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?> Cashier Sales Performance</span><span class="db-panel-subtitle">Sales processed at checkout</span></div>
            <div class="db-team-performance-meta"><span class="db-period-label"><?php echo e($salesPeriodLabel); ?></span><strong><?php echo e($cashierPerformance->count()); ?> cashier<?php echo e($cashierPerformance->count() === 1 ? '' : 's'); ?></strong></div>
        </div>
        <div class="card-body dashboard-chart-body db-team-performance-body">
            <?php if($cashierPerformance->isNotEmpty()): ?>
            <div id="cashierChart" class="horizontal-chart" aria-label="Cashier performance based on net sales processed"></div>
            <?php else: ?>
            <?php if (isset($component)) { $__componentOriginal074a021b9d42f490272b5eefda63257c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal074a021b9d42f490272b5eefda63257c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.empty-state','data' => ['title' => 'No cashier sales yet','message' => 'Cashier performance will appear after sales are processed.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'No cashier sales yet','message' => 'Cashier performance will appear after sales are processed.']); ?>
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
        </div>
    </section>

    <section class="card db-panel db-team-performance" aria-labelledby="salesAssociatePerformanceHeading">
        <div class="card-header db-team-performance-header">
            <div><span class="card-title" id="salesAssociatePerformanceHeading"><?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'users']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'users']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?> Sales Associate Performance</span><span class="db-panel-subtitle">Sales credited to the assisting staff member</span></div>
            <div class="db-team-performance-meta"><span class="db-period-label"><?php echo e($salesPeriodLabel); ?></span><strong><?php echo e($salesAssociatePerformance->count()); ?> associate<?php echo e($salesAssociatePerformance->count() === 1 ? '' : 's'); ?></strong></div>
        </div>
        <div class="card-body dashboard-chart-body db-team-performance-body">
            <?php if($salesAssociatePerformance->isNotEmpty()): ?>
            <div id="salesAssociateChart" class="horizontal-chart" aria-label="Sales associate performance based on net sales assisted"></div>
            <?php else: ?>
            <?php if (isset($component)) { $__componentOriginal074a021b9d42f490272b5eefda63257c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal074a021b9d42f490272b5eefda63257c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.empty-state','data' => ['title' => 'No sales associate activity yet','message' => 'Performance appears after a cashier records a sales associate on a sale.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'No sales associate activity yet','message' => 'Performance appears after a cashier records a sales associate on a sale.']); ?>
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
        </div>
    </section>
</div>
<?php endif; ?>

<?php if($canViewBusinessMetrics): ?>
<?php
    $dashboardChartData = [
        'products' => $topProducts->map(function ($product) {
            return [
                'label' => $product->product_name,
                'identifier' => $product->identifier,
                'value' => (int) $product->units_sold,
                'revenue' => (float) $product->revenue,
            ];
        })->values(),
        'categories' => $categoryPerformance->map(function ($category) {
            return [
                'label' => $category->category_name,
                'value' => (float) $category->revenue,
                'units' => (int) $category->units_sold,
            ];
        })->values(),
        'paymentMethods' => $paymentMethodPerformance->map(function ($method) {
            return [
                'label' => $method->label,
                'value' => (float) $method->revenue,
                'transactions' => (int) $method->transaction_count,
                'isUnknown' => (bool) $method->is_unknown,
            ];
        })->values(),
        'cashiers' => $cashierPerformance->map(function ($cashier) {
            return [
                'label' => $cashier->cashier_name,
                'value' => (float) $cashier->revenue,
                'transactions' => (int) $cashier->transaction_count,
            ];
        })->values(),
        'salesAssociates' => $salesAssociatePerformance->map(function ($associate) {
            return [
                'label' => $associate->sales_associate_name,
                'value' => (float) $associate->revenue,
                'transactions' => (int) $associate->transaction_count,
            ];
        })->values(),
    ];
?>
<script type="application/json" id="dashboardChartData"><?php echo json_encode($dashboardChartData, 15, 512) ?></script>
<?php endif; ?>
</div>

<?php if($canViewBusinessMetrics): ?>
<div class="db-section-heading db-section-heading--spaced">
    <div>
        <span class="db-section-kicker">Live operations</span>
        <h2>Recent Operations</h2>
        <span class="db-section-note">Latest sales, inventory attention, and security events in one place.</span>
    </div>
</div>

<div class="grid-3 db-row db-activity-grid">
    <section class="card db-panel db-operations-panel" aria-labelledby="recentTransactionsHeading">
        <div class="card-header">
            <div><span class="card-title" id="recentTransactionsHeading"><?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
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
<?php endif; ?> Recent Transactions</span><span class="db-panel-subtitle">Latest completed receipts</span></div>
            <a href="<?php echo e(route('pos.transactions')); ?>" class="btn btn-secondary btn-sm">View all <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
        </div>
        <div class="list-stack db-list db-operations-list">
            <?php $__empty_1 = true; $__currentLoopData = $recentTxns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <?php $dt = \Carbon\Carbon::parse($t->date->format('Y-m-d').' '.$t->time); ?>
            <div class="txn-item">
                <div class="txn-main">
                    <span class="receipt-code"><?php echo e($t->receipt_no); ?></span>
                    <div class="txn-meta"><span><?php echo e($t->cashier?->name ?? 'Unassigned cashier'); ?></span><span><?php echo e($dt->format('M d, Y · h:i A')); ?></span><span class="status <?php echo e($t->status === 'completed' ? 'status-active' : 'status-low'); ?>"><?php echo e(str($t->status ?? 'unknown')->replace('_', ' ')->title()); ?></span></div>
                </div>
                <div class="txn-side">
                    <span class="txn-total">&#8369;<?php echo e(number_format($t->netTotal(), 2)); ?></span>
                    <a href="<?php echo e(route('pos.receipt', $t)); ?>" class="btn btn-secondary btn-sm"><?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'eye']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'eye']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?> View</a>
                </div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <?php if (isset($component)) { $__componentOriginal074a021b9d42f490272b5eefda63257c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal074a021b9d42f490272b5eefda63257c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.empty-state','data' => ['title' => 'No transactions today','message' => 'Completed sales will appear here.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'No transactions today','message' => 'Completed sales will appear here.']); ?>
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
        </div>
    </section>

    <section class="card db-panel db-operations-panel" aria-labelledby="stockAlertsHeading">
        <div class="card-header">
            <div><span class="card-title" id="stockAlertsHeading"><?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'alerts']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'alerts']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?> Stock Alerts</span><span class="db-panel-subtitle">Variants needing attention first</span></div>
            <a href="<?php echo e(route('inventory.stock-overview', ['stock_level' => 'attention'])); ?>" class="btn btn-secondary btn-sm">View all <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
        </div>
        <div class="list-stack db-list db-operations-list">
            <?php $__empty_1 = true; $__currentLoopData = $lowStockItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $variant): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <?php
                $stock = $variant->inventoryStock;
                $product = $variant->product;
                $quantity = (int) ($stock?->quantity_on_hand ?? 0);
                $minimum = (int) ($stock?->minimum_stock ?? 0);
                $critical = (int) ($stock?->critical_stock ?? 0);
                $isOut = $quantity <= 0;
                $isCrit = !$isOut && $quantity <= $critical;
                $statusLabel = $isOut ? 'Out of Stock' : ($isCrit ? 'Critical' : 'Low Stock');
                $pct = $minimum > 0 ? min(100, max(0, ($quantity / $minimum) * 100)) : 0;
            ?>
            <a href="<?php echo e(route('inventory.show', $variant)); ?>" class="stock-item stock-item-link db-stock-alert <?php echo e(($isOut || $isCrit) ? 'stock-critical' : ''); ?>">
                <div class="stock-top">
                    <span class="db-stock-identity"><strong><span class="stock-dot" aria-hidden="true"></span><?php echo e($product?->name ?? 'Deleted product'); ?></strong><small title="<?php echo e(collect([$variant->variant_code, $variant->part_number, $variant->brand?->name, $variant->size, $variant->color])->filter()->join(' / ')); ?>"><?php echo e(collect([$variant->variant_code, $variant->part_number, $variant->brand?->name, $variant->size, $variant->color])->filter()->join(' / ') ?: 'Variant details unavailable'); ?></small></span>
                    <span class="status <?php echo e(($isOut || $isCrit) ? 'status-inactive' : 'status-low'); ?>"><?php echo e($statusLabel); ?></span>
                </div>
                <div class="db-stock-levels"><span>On hand <strong><?php echo e($quantity); ?> <?php echo e($variant->unit?->code ?: 'units'); ?></strong></span><span>Reorder at <strong><?php echo e($minimum); ?></strong></span></div>
                <div class="stock-track" role="img" aria-label="<?php echo e($quantity); ?> of minimum <?php echo e($minimum); ?> <?php echo e($variant->unit?->code); ?> in stock">
                    <div class="stock-fill" style="width:<?php echo e($pct); ?>%;"></div>
                </div>
            </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <?php if (isset($component)) { $__componentOriginal074a021b9d42f490272b5eefda63257c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal074a021b9d42f490272b5eefda63257c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.empty-state','data' => ['title' => 'Stock levels are healthy','message' => 'No variants are currently at or below minimum stock.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Stock levels are healthy','message' => 'No variants are currently at or below minimum stock.']); ?>
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
        </div>
    </section>

    <section class="card db-panel db-operations-panel" aria-labelledby="securityActivityHeading">
        <div class="card-header">
            <div><span class="card-title" id="securityActivityHeading"><?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'shield']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'shield']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?> Security Activity</span><span class="db-panel-subtitle">Newest intrusion alerts</span></div>
            <a href="<?php echo e(route('security.events')); ?>" class="btn btn-secondary btn-sm">View all <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
        </div>
        <div class="db-list db-operations-list security-activity-list">
            <?php $__empty_1 = true; $__currentLoopData = $recentIntrusionAlerts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $alert): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <?php
                $severity = strtolower($alert->severity ?? 'medium');
                $alertStatus = strtolower($alert->alert_status ?? 'new');
                $statusClass = $alertStatus === 'resolved' ? 'status-active' : ($alertStatus === 'new' ? 'status-inactive' : 'status-low');
            ?>
            <a href="<?php echo e(route('security.events.alerts.show', $alert)); ?>" class="security-activity-item">
                <span class="security-activity-marker security-activity-marker--<?php echo e($severity); ?>" aria-hidden="true"></span>
                <span class="security-activity-main">
                    <strong title="<?php echo e($alert->alert_title); ?>"><?php echo e(str($alert->alert_title)->replace(['_', '-'], ' ')->title()); ?></strong>
                    <small><?php echo e(ucfirst($severity)); ?> risk &middot; <?php echo e($alert->created_at?->diffForHumans() ?? 'Recently'); ?></small>
                </span>
                <span class="status <?php echo e($statusClass); ?>"><?php echo e(ucfirst($alertStatus)); ?></span>
                <i class="bi bi-chevron-right security-activity-chevron" aria-hidden="true"></i>
            </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <?php if (isset($component)) { $__componentOriginal074a021b9d42f490272b5eefda63257c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal074a021b9d42f490272b5eefda63257c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.empty-state','data' => ['title' => 'No recent security alerts','message' => 'New anomaly alerts will appear here.','icon' => 'shield']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'No recent security alerts','message' => 'New anomaly alerts will appear here.','icon' => 'shield']); ?>
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
        </div>
    </section>
</div>
<?php endif; ?>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<?php echo app('Illuminate\Foundation\Vite')('resources/css/views/dashboard-index-2.css'); ?>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<?php echo app('Illuminate\Foundation\Vite')('resources/js/views/dashboard-index-1.js'); ?>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\acrylink_newbie1\resources\views\dashboard\index.blade.php ENDPATH**/ ?>