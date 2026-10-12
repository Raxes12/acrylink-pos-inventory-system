<?php $__env->startSection('title', 'Dashboard'); ?>
<?php $__env->startSection('page-label', 'Dashboard'); ?>

<?php $__env->startSection('content'); ?>
<?php
    $canViewBusinessMetrics = !auth()->user()->isCashier();
    $salesTrendClass = $salesChange >= 0 ? 'text-green' : 'text-danger';
    $salesTrendLabel = ($salesChange >= 0 ? '+' : '') . number_format($salesChange, 1) . '% vs yesterday';
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

<?php if($canViewBusinessMetrics): ?>
<section class="db-command-grid" aria-label="Today's business summary">
    <a href="<?php echo e(route('pos.transactions', ['date_from' => today()->toDateString(), 'date_to' => today()->toDateString()])); ?>" class="db-command-card db-command-card--sales">
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
<?php endif; ?></span><span>Today's Sales</span><i class="bi bi-chevron-right" aria-hidden="true"></i></div>
        <strong data-count="<?php echo e(round($todaySales)); ?>" data-prefix="&#8369;">&#8369;<?php echo e(number_format($todaySales, 0)); ?></strong>
        <div class="db-command-trend <?php echo e($salesTrendClass); ?>"><i class="bi <?php echo e($salesChange >= 0 ? 'bi-arrow-up' : 'bi-arrow-down'); ?>" aria-hidden="true"></i> <?php echo e($salesTrendLabel); ?></div>
        <small><?php echo e(number_format($todayTxns)); ?> transaction<?php echo e($todayTxns === 1 ? '' : 's'); ?> &middot; &#8369;<?php echo e(number_format($avgSale, 0)); ?> average</small>
    </a>
    <a href="<?php echo e(route('reports.index', ['range' => 'today'])); ?>" class="db-command-card db-command-card--collections">
        <div class="db-command-top"><span class="db-command-icon"><i class="bi bi-wallet2" aria-hidden="true"></i></span><span>Collections</span><i class="bi bi-chevron-right" aria-hidden="true"></i></div>
        <strong data-count="<?php echo e(round($todayFinancials['today_collections'])); ?>" data-prefix="&#8369;">&#8369;<?php echo e(number_format($todayFinancials['today_collections'], 0)); ?></strong>
        <div class="db-command-trend"><?php echo e($todaySales > 0 ? number_format(($todayFinancials['today_collections'] / $todaySales) * 100, 1).'%' : '0%'); ?> of today's sales</div>
        <small>Payments received today</small>
    </a>
    <a href="<?php echo e(route('accounts-receivable.index')); ?>" class="db-command-card db-command-card--receivables">
        <div class="db-command-top"><span class="db-command-icon"><i class="bi bi-people" aria-hidden="true"></i></span><span>Open Receivables</span><i class="bi bi-chevron-right" aria-hidden="true"></i></div>
        <strong data-count="<?php echo e(round($receivableSummary['total_outstanding'])); ?>" data-prefix="&#8369;">&#8369;<?php echo e(number_format($receivableSummary['total_outstanding'], 0)); ?></strong>
        <div class="db-command-trend"><?php echo e(number_format($partialReceivables)); ?> partially paid &middot; <?php echo e(number_format($overdueReceivables)); ?> overdue</div>
        <small><?php echo e(number_format($receivableSummary['customers_with_open_credit'])); ?> customer account<?php echo e($receivableSummary['customers_with_open_credit'] === 1 ? '' : 's'); ?></small>
    </a>
    <a href="<?php echo e(route('inventory.stock-overview', ['stock_level' => 'attention'])); ?>" class="db-command-card db-command-card--inventory">
        <div class="db-command-top"><span class="db-command-icon"><?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
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
<?php endif; ?></span><span>Inventory Alerts</span><i class="bi bi-chevron-right" aria-hidden="true"></i></div>
        <strong data-count="<?php echo e($lowStock); ?>"><?php echo e(number_format($lowStock)); ?></strong>
        <div class="db-command-trend"><?php echo e(number_format(max(0, $lowStock - $outOfStock))); ?> low stock &middot; <?php echo e(number_format($outOfStock)); ?> out of stock</div>
        <small>Items requiring attention</small>
    </a>
</section>
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

<div class="db-section-heading <?php echo e($canViewBusinessMetrics ? 'db-section-heading--overview' : ''); ?>">
    <div>
        <span class="db-section-kicker">Performance</span>
        <h2>Business overview</h2>
    </div>
    <span class="db-section-note">Updated from current sales and inventory records</span>
</div>

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
        <h2>Sales and product movement</h2>
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
            <nav class="db-sales-periods" aria-label="Sales performance period">
                <?php $__currentLoopData = ['weekly' => 'Weekly', 'monthly' => 'Monthly', 'quarterly' => 'Quarterly']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $period => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <a href="<?php echo e(route('dashboard', ['sales_period' => $period])); ?>" class="db-sales-period <?php echo e($salesPeriod === $period ? 'is-active' : ''); ?>" data-sales-period="<?php echo e($period); ?>" <?php if($salesPeriod === $period): ?> aria-current="page" <?php endif; ?>><?php echo e($label); ?></a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </nav>
        </div>
        <p class="db-panel-caption">Net sales for <?php echo e(strtolower($salesPeriodLabel)); ?></p>
        <div class="card-body db-trend-chart" role="img" aria-label="Net sales for <?php echo e(strtolower($salesPeriodLabel)); ?>">
            <?php if($salesChart->isNotEmpty()): ?>
            <?php
                $chartMax = max(1, (float) $salesChart->max('total'));
                $chartCount = $salesChart->count();
                $chartPoints = $salesChart->values()->map(function ($day, $index) use ($chartCount, $chartMax) {
                    $x = $chartCount === 1 ? 340 : 35 + ($index * (610 / ($chartCount - 1)));
                    $y = 178 - (((float) $day['total'] / $chartMax) * 138);
                    return ['x' => round($x, 1), 'y' => round($y, 1), 'label' => $day['label'], 'total' => (float) $day['total']];
                });
                $chartLine = $chartPoints->map(fn ($point) => $point['x'].','.$point['y'])->implode(' ');
                $chartArea = '35,184 '.$chartLine.' 645,184';
            ?>
            <svg class="db-sales-line" viewBox="0 0 680 225" aria-hidden="true">
                <defs><linearGradient id="dashboardSalesArea" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="var(--accent)" stop-opacity=".24"/><stop offset="1" stop-color="var(--accent)" stop-opacity="0"/></linearGradient></defs>
                <g class="db-chart-grid"><line x1="35" y1="40" x2="645" y2="40"/><line x1="35" y1="86" x2="645" y2="86"/><line x1="35" y1="132" x2="645" y2="132"/><line x1="35" y1="184" x2="645" y2="184"/></g>
                <polygon class="db-chart-area" points="<?php echo e($chartArea); ?>"/><polyline class="db-chart-line" points="<?php echo e($chartLine); ?>"/>
                <?php $__currentLoopData = $chartPoints; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $point): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><circle class="db-chart-point" cx="<?php echo e($point['x']); ?>" cy="<?php echo e($point['y']); ?>" r="3.5"><title><?php echo e($point['label']); ?>: ₱<?php echo e(number_format($point['total'], 2)); ?></title></circle><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $labelInterval = max(1, (int) ceil($chartCount / 6));
                ?>
                <?php $__currentLoopData = $chartPoints; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $point): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?> <?php if($loop->first || $loop->last || ($loop->index % $labelInterval === 0)): ?><text class="db-chart-label" x="<?php echo e($point['x']); ?>" y="210" text-anchor="middle"><?php echo e($point['label']); ?></text><?php endif; ?> <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </svg>
            <?php else: ?><?php if (isset($component)) { $__componentOriginal074a021b9d42f490272b5eefda63257c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal074a021b9d42f490272b5eefda63257c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.empty-state','data' => ['title' => 'No sales recorded','message' => 'Sales activity for this period will appear here.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'No sales recorded','message' => 'Sales activity for this period will appear here.']); ?>
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
            <span class="db-period-label">This month &middot; units sold</span>
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
            <span class="db-panel-subtitle">Current stock quantity and value</span>
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
        <div class="db-inventory-stat"><span class="db-inventory-stat-icon"><i class="bi bi-cash-stack" aria-hidden="true"></i></span><div><strong>&#8369;<?php echo e(number_format($inventorySummary['value'], 0)); ?></strong><span>Inventory value</span></div></div>
    </div>
</section>
<?php endif; ?>

<?php if($canViewBusinessMetrics): ?>
<div class="grid-2 db-row db-insight-row">
    <section class="card db-panel" aria-labelledby="categoryPerformanceHeading">
        <div class="card-header"><span class="card-title" id="categoryPerformanceHeading"><?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
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
<?php endif; ?> Category Sales Distribution</span><span class="db-period-label">This month &middot; revenue</span></div>
        <div class="card-body dashboard-chart-body">
            <?php if($categoryPerformance->isNotEmpty()): ?>
            <div id="categoryChart" class="doughnut-chart-layout" aria-label="Sales revenue distribution by category"></div>
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

    <section class="card db-panel" aria-labelledby="cashierPerformanceHeading">
        <div class="card-header"><span class="card-title" id="cashierPerformanceHeading"><?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
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
<?php endif; ?> Cashier Sales Performance</span><span class="db-period-label">This month &middot; net sales</span></div>
        <div class="card-body dashboard-chart-body">
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
</div>
<?php endif; ?>

<?php if($canViewBusinessMetrics): ?>
<div class="db-section-heading db-section-heading--spaced">
    <div>
        <span class="db-section-kicker">Live operations</span>
        <h2>Recent activity and attention</h2>
    </div>
</div>

<div class="grid-3 db-row db-activity-grid">
    <section class="card db-panel" aria-labelledby="recentTransactionsHeading">
        <div class="card-header">
            <span class="card-title" id="recentTransactionsHeading"><?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
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
<?php endif; ?> Recent Transactions</span>
            <a href="<?php echo e(route('pos.transactions')); ?>" class="btn btn-secondary btn-sm">View all</a>
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

    <section class="card db-panel" aria-labelledby="stockAlertsHeading">
        <div class="card-header">
            <span class="card-title" id="stockAlertsHeading"><?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
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
<?php endif; ?> Stock Alerts</span>
            <a href="<?php echo e(route('inventory.stock-overview', ['stock_level' => 'attention'])); ?>" class="btn btn-secondary btn-sm">View all</a>
        </div>
        <div class="list-stack db-list">
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
            <div class="stock-item <?php echo e(($isOut || $isCrit) ? 'stock-critical' : ''); ?>">
                <div class="stock-top">
                    <span class="stock-name"><span class="stock-dot" aria-hidden="true"></span><?php echo e($product?->name ?? 'Deleted product'); ?></span>
                    <span class="status <?php echo e(($isOut || $isCrit) ? 'status-inactive' : 'status-low'); ?>"><?php echo e($statusLabel); ?></span>
                </div>
                <div class="stock-track" role="img" aria-label="<?php echo e($quantity); ?> of minimum <?php echo e($minimum); ?> <?php echo e($variant->unit?->code); ?> in stock">
                    <div class="stock-fill" style="width:<?php echo e(max(2, $pct)); ?>%;"></div>
                </div>
                <div class="stock-footer">
                    <span class="stock-meta" title="<?php echo e(collect([$variant->variant_code, $variant->part_number, $variant->brand?->name, $variant->size, $variant->color])->filter()->join(' / ')); ?>">
                        <?php echo e(collect([$variant->variant_code, $variant->part_number, $variant->brand?->name, $variant->size, $variant->color])->filter()->join(' / ') ?: 'Variant details unavailable'); ?>

                        &middot; <?php echo e($variant->supplier?->name ?? 'No supplier'); ?>

                    </span>
                    <span class="stock-qty"><?php echo e($quantity); ?> / <?php echo e($minimum); ?> <?php echo e($variant->unit?->code); ?></span>
                </div>
            </div>
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

    <section class="card db-panel" aria-labelledby="securityActivityHeading">
        <div class="card-header">
            <span class="card-title" id="securityActivityHeading"><?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
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
<?php endif; ?> Security Activity</span>
            <a href="<?php echo e(route('security.events')); ?>" class="btn btn-secondary btn-sm">View all</a>
        </div>
        <div class="db-list security-activity-list">
            <?php $__empty_1 = true; $__currentLoopData = $recentIntrusionAlerts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $alert): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <?php
                $severity = strtolower($alert->severity ?? 'medium');
                $alertStatus = strtolower($alert->alert_status ?? 'new');
                $statusClass = $alertStatus === 'resolved' ? 'status-active' : ($alertStatus === 'new' ? 'status-inactive' : 'status-low');
            ?>
            <a href="<?php echo e(route('security.events.alerts.show', $alert)); ?>" class="security-activity-item">
                <span class="security-activity-marker security-activity-marker--<?php echo e($severity); ?>" aria-hidden="true"></span>
                <span class="security-activity-main">
                    <strong title="<?php echo e($alert->alert_title); ?>"><?php echo e($alert->alert_title); ?></strong>
                    <small><?php echo e(ucfirst($severity)); ?> risk &middot; <?php echo e($alert->created_at?->diffForHumans() ?? 'Recently'); ?></small>
                </span>
                <span class="status <?php echo e($statusClass); ?>"><?php echo e(ucfirst($alertStatus)); ?></span>
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

<?php if($canViewBusinessMetrics): ?>
<?php
    $dashboardChartData = [
        'products' => $topProducts->map(function ($product) {
            return [
                'label' => $product->product_name,
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
        'cashiers' => $cashierPerformance->map(function ($cashier) {
            return [
                'label' => $cashier->cashier_name,
                'value' => (float) $cashier->revenue,
                'transactions' => (int) $cashier->transaction_count,
            ];
        })->values(),
    ];
?>
<script type="application/json" id="dashboardChartData"><?php echo json_encode($dashboardChartData, 15, 512) ?></script>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<?php echo app('Illuminate\Foundation\Vite')('resources/css/views/dashboard-index-2.css'); ?>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<?php echo app('Illuminate\Foundation\Vite')('resources/js/views/dashboard-index-1.js'); ?>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\acrylink_newbie\resources\views\dashboard\index.blade.php ENDPATH**/ ?>