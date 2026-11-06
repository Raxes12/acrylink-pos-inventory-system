<?php $__env->startSection('title', 'Stock Movements'); ?>
<?php $__env->startSection('page-label', 'Inventory Management'); ?>

<?php
    $selectedType = $filters['type'] ?? '';
    $dateFrom = $filters['date_from'] ?? '';
    $dateTo = $filters['date_to'] ?? '';
    $hasFilters = filled($selectedType) || filled($dateFrom) || filled($dateTo);

    if ($dateFrom && $dateTo) {
        $periodLabel = $dateFrom === $dateTo
            ? \Carbon\Carbon::parse($dateFrom)->format('M j, Y')
            : \Carbon\Carbon::parse($dateFrom)->format('M j').' – '.\Carbon\Carbon::parse($dateTo)->format('M j, Y');
    } elseif ($dateFrom) {
        $periodLabel = 'From '.\Carbon\Carbon::parse($dateFrom)->format('M j, Y');
    } elseif ($dateTo) {
        $periodLabel = 'Through '.\Carbon\Carbon::parse($dateTo)->format('M j, Y');
    } else {
        $periodLabel = 'All recorded dates';
    }
?>

<?php $__env->startSection('content'); ?>
<div class="movements-page">
    <?php if (isset($component)) { $__componentOriginalbf42c70af0df326d1656b16ed5563ab8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalbf42c70af0df326d1656b16ed5563ab8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.inventory-nav','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('inventory-nav'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalbf42c70af0df326d1656b16ed5563ab8)): ?>
<?php $attributes = $__attributesOriginalbf42c70af0df326d1656b16ed5563ab8; ?>
<?php unset($__attributesOriginalbf42c70af0df326d1656b16ed5563ab8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalbf42c70af0df326d1656b16ed5563ab8)): ?>
<?php $component = $__componentOriginalbf42c70af0df326d1656b16ed5563ab8; ?>
<?php unset($__componentOriginalbf42c70af0df326d1656b16ed5563ab8); ?>
<?php endif; ?>

    <?php if (isset($component)) { $__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => 'Stock Movements','subtitle' => 'Review every stock increase, sale, return, and adjustment in one audit-friendly history.','class' => 'movement-page-header']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Stock Movements','subtitle' => 'Review every stock increase, sale, return, and adjustment in one audit-friendly history.','class' => 'movement-page-header']); ?>
         <?php $__env->slot('actions', null, []); ?> 
            <a href="<?php echo e(route('inventory.stock-overview')); ?>" class="btn btn-secondary">
                <svg class="app-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M3 3v18h18"/><path d="m7 16 4-4 3 3 5-7"/></svg>
                Stock overview
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

    <section class="movement-filter-panel" aria-labelledby="movement-filter-title">
        <div class="movement-filter-heading">
            <div>
                <span class="movement-filter-kicker">History filters</span>
                <h2 id="movement-filter-title">Find a stock activity</h2>
                <p>Results refresh automatically when a filter changes.</p>
            </div>
            <div class="movement-filter-context">
                <span><?php echo e($movementTypes[$selectedType] ?? 'All movements'); ?></span>
                <strong><?php echo e($periodLabel); ?></strong>
            </div>
        </div>

        <form method="GET" action="<?php echo e(route('inventory.movements')); ?>" class="movement-filter" data-live-filter>
            <label class="movement-filter-field movement-filter-field--type">
                <span>Movement type</span>
                <div class="movement-control">
                    <svg class="app-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M8 7h11l-3-3"/><path d="m19 7-3 3"/><path d="M16 17H5l3 3"/><path d="m5 17 3-3"/></svg>
                    <select name="type" aria-label="Filter by movement type">
                        <?php $__currentLoopData = $movementTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($value); ?>" <?php if($selectedType === $value): echo 'selected'; endif; ?>><?php echo e($label); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
            </label>
            <label class="movement-filter-field">
                <span>From date</span>
                <div class="movement-control">
                    <svg class="app-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 11h18"/></svg>
                    <input type="date" name="date_from" value="<?php echo e($dateFrom); ?>" aria-label="Show movements from date">
                </div>
            </label>
            <label class="movement-filter-field">
                <span>To date</span>
                <div class="movement-control">
                    <svg class="app-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 11h18"/></svg>
                    <input type="date" name="date_to" value="<?php echo e($dateTo); ?>" min="<?php echo e($dateFrom); ?>" aria-label="Show movements through date">
                </div>
            </label>
            <?php if($hasFilters): ?>
                <a href="<?php echo e(route('inventory.movements')); ?>" class="movement-reset" data-live-filter-clear>
                    <svg class="app-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M3 12a9 9 0 1 0 3-6.7L3 8"/><path d="M3 3v5h5"/></svg>
                    Reset filters
                </a>
            <?php endif; ?>
            <span class="movement-filter-status" data-live-filter-status role="status" aria-live="polite"></span>
            <noscript><button type="submit" class="btn btn-primary">Apply filters</button></noscript>
        </form>
        <?php $__errorArgs = ['date_to'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="movement-filter-error" role="alert">The end date must be the same as or later than the start date.</p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </section>

    <div data-live-results class="movement-live-results">
        <section class="movement-summary-grid" aria-label="Filtered movement summary">
            <article class="movement-summary-card movement-summary-card--total">
                <span class="movement-summary-icon" aria-hidden="true"><svg class="app-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3v18h18"/><path d="M7 16h2v2H7zM11 11h2v7h-2zM15 6h2v12h-2z"/></svg></span>
                <div><span>Total activities</span><strong><?php echo e(number_format($movementSummary['movements'])); ?></strong><small>in this view</small></div>
            </article>
            <article class="movement-summary-card movement-summary-card--added">
                <span class="movement-summary-icon" aria-hidden="true"><svg class="app-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 19V5M5 12l7-7 7 7"/></svg></span>
                <div><span>Units added</span><strong>+<?php echo e(number_format($movementSummary['units_added'])); ?></strong><small>received or returned</small></div>
            </article>
            <article class="movement-summary-card movement-summary-card--removed">
                <span class="movement-summary-icon" aria-hidden="true"><svg class="app-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M19 12l-7 7-7-7"/></svg></span>
                <div><span>Units removed</span><strong>−<?php echo e(number_format($movementSummary['units_removed'])); ?></strong><small>sold or deducted</small></div>
            </article>
            <article class="movement-summary-card movement-summary-card--adjusted">
                <span class="movement-summary-icon" aria-hidden="true"><svg class="app-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 21v-7M4 10V3M12 21v-9M12 8V3M20 21v-5M20 12V3M1 14h6M9 8h6M17 16h6"/></svg></span>
                <div><span>Manual adjustments</span><strong><?php echo e(number_format($movementSummary['adjustments'])); ?></strong><small>staff-entered changes</small></div>
            </article>
        </section>

        <section class="card movement-results-card" aria-labelledby="movement-history-title">
            <header class="movement-results-header">
                <div>
                    <span class="movement-results-kicker">Activity log</span>
                    <h2 id="movement-history-title">Movement history</h2>
                    <p><?php echo e($periodLabel); ?> · <?php echo e($movementTypes[$selectedType] ?? 'All movements'); ?></p>
                </div>
                <span class="movement-result-count"><?php echo e(number_format($movements->total())); ?> <?php echo e(\Illuminate\Support\Str::plural('record', $movements->total())); ?></span>
            </header>
            <div class="table-wrap movement-table-wrap" tabindex="0" aria-label="Stock movement history table">
                <table class="stock-table movement-table">
                    <thead>
                        <tr>
                            <th>Date &amp; time</th>
                            <th>Product / variant</th>
                            <th>Movement</th>
                            <th>Change</th>
                            <th>Stock level</th>
                            <th>Recorded by</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $movements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $movement): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $typeLabel = $movement->reference_type === 'manual_adjustment'
                                ? \App\Support\StockAdjustmentOptions::typeLabel($movement->movement_type)
                                : ($movementTypes[$movement->movement_type] ?? str($movement->movement_type)->replace('_', ' ')->title());
                            $reasonLabel = \App\Support\StockAdjustmentOptions::reasonLabel($movement->movement_type, $movement->adjustment_reason);
                            $variant = $movement->variant;
                            $product = $variant?->product;
                            $unitCode = $variant?->unit?->code;
                            $variantDetails = collect([$variant?->part_number, $variant?->brand?->name, $variant?->size, $variant?->color])->filter()->join(' / ');
                            $typeClass = in_array($movement->movement_type, ['stock_in', 'sale_return'], true)
                                ? 'is-increase'
                                : (in_array($movement->movement_type, ['stock_out', 'sale'], true) ? 'is-decrease' : 'is-adjustment');
                            $referenceLabel = $movement->reference_type && $movement->reference_type !== 'manual_adjustment'
                                ? str($movement->reference_type)->replace('_', ' ')->title()
                                : null;
                        ?>
                        <tr class="movement-row <?php echo e($typeClass); ?>">
                            <td data-label="Date & time">
                                <time class="movement-date" datetime="<?php echo e($movement->created_at?->toIso8601String()); ?>">
                                    <strong><?php echo e($movement->created_at?->format('M j, Y') ?? 'Date unavailable'); ?></strong>
                                    <span><?php echo e($movement->created_at?->format('h:i A')); ?></span>
                                </time>
                            </td>
                            <td data-label="Product / variant">
                                <div class="movement-product">
                                    <?php if($variant): ?>
                                        <a class="stock-product-name stock-product-link" href="<?php echo e(route('inventory.show', $variant)); ?>"><?php echo e($product?->name ?? 'Deleted product'); ?></a>
                                    <?php else: ?>
                                        <strong class="stock-product-name">Deleted product</strong>
                                    <?php endif; ?>
                                    <div class="movement-variant-line">
                                        <span class="movement-code"><?php echo e($variant?->variant_code ?? 'Unavailable'); ?></span>
                                        <?php if($variantDetails): ?><span><?php echo e($variantDetails); ?></span><?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td data-label="Movement">
                                <span class="movement-type movement-type--<?php echo e($typeClass); ?>"><span aria-hidden="true"></span><?php echo e($typeLabel); ?></span>
                            </td>
                            <td data-label="Change">
                                <span class="movement-change <?php echo e($movement->quantity_change < 0 ? 'movement-negative' : 'movement-positive'); ?>">
                                    <?php echo e($movement->quantity_change > 0 ? '+' : ''); ?><?php echo e(number_format($movement->quantity_change)); ?>

                                    <?php if($unitCode): ?><small><?php echo e($unitCode); ?></small><?php endif; ?>
                                </span>
                            </td>
                            <td data-label="Stock level">
                                <div class="movement-stock-flow" aria-label="Stock changed from <?php echo e($movement->quantity_before); ?> to <?php echo e($movement->quantity_after); ?>">
                                    <span><?php echo e(number_format($movement->quantity_before)); ?></span>
                                    <svg class="app-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                                    <strong><?php echo e(number_format($movement->quantity_after)); ?></strong>
                                </div>
                            </td>
                            <td data-label="Recorded by">
                                <div class="movement-user">
                                    <span aria-hidden="true"><?php echo e(str($movement->user?->name ?? 'System')->substr(0, 1)->upper()); ?></span>
                                    <strong><?php echo e($movement->user?->name ?? 'System'); ?></strong>
                                </div>
                            </td>
                            <td data-label="Details">
                                <div class="movement-context">
                                    <?php if($reasonLabel): ?><span class="movement-reason"><?php echo e($reasonLabel); ?></span><?php endif; ?>
                                    <?php if($referenceLabel): ?><span class="movement-reference"><?php echo e($referenceLabel); ?></span><?php endif; ?>
                                    <p class="movement-remarks"><?php echo e($movement->remarks ?: 'No additional notes'); ?></p>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <?php if (isset($component)) { $__componentOriginal074a021b9d42f490272b5eefda63257c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal074a021b9d42f490272b5eefda63257c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.empty-state','data' => ['colspan' => '7','title' => 'No stock movements match these filters','message' => 'Try another movement type or widen the selected date range.','icon' => 'transactions']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['colspan' => '7','title' => 'No stock movements match these filters','message' => 'Try another movement type or widen the selected date range.','icon' => 'transactions']); ?>
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
            <footer class="view-blades-footer movement-results-footer">
        <div class="pagination-summary">
            Showing <?php echo e($movements->firstItem() ?? 0); ?>-<?php echo e($movements->lastItem() ?? 0); ?> of <?php echo e($movements->total()); ?> movements
        </div>
        <?php if($movements->hasPages()): ?>
        <?php
            $currentPage = $movements->currentPage();
            $lastPage = $movements->lastPage();
            $pages = collect([1, $currentPage - 1, $currentPage, $currentPage + 1, $lastPage])
                ->filter(fn ($page) => $page >= 1 && $page <= $lastPage)
                ->unique()
                ->sort()
                ->values();
            $previousPage = null;
        ?>
        <nav class="rules-pagination" aria-label="Stock movement pagination">
            <?php if($movements->onFirstPage()): ?>
                <span class="rules-page is-disabled" aria-disabled="true">Previous</span>
            <?php else: ?>
                <a class="rules-page" href="<?php echo e($movements->previousPageUrl()); ?>" rel="prev">Previous</a>
            <?php endif; ?>

            <?php $__currentLoopData = $pages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php if($previousPage && $page > $previousPage + 1): ?>
                    <span class="rules-page rules-page--gap" aria-hidden="true">&hellip;</span>
                <?php endif; ?>

                <?php if($page === $currentPage): ?>
                    <span class="rules-page is-active" aria-current="page"><?php echo e($page); ?></span>
                <?php else: ?>
                    <a class="rules-page" href="<?php echo e($movements->url($page)); ?>"><?php echo e($page); ?></a>
                <?php endif; ?>

                <?php $previousPage = $page; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

            <?php if($movements->hasMorePages()): ?>
                <a class="rules-page" href="<?php echo e($movements->nextPageUrl()); ?>" rel="next">Next</a>
            <?php else: ?>
                <span class="rules-page is-disabled" aria-disabled="true">Next</span>
            <?php endif; ?>
        </nav>
        <?php endif; ?>
            </footer>
        </section>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<?php echo app('Illuminate\Foundation\Vite')('resources/css/views/inventory-index-1.css'); ?>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\acrylink_newbie1\resources\views/inventory/movements.blade.php ENDPATH**/ ?>