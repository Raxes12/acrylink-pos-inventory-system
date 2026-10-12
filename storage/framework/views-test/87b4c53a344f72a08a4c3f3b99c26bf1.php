<?php $__env->startSection('title', 'Stock Movements'); ?>
<?php $__env->startSection('page-label', 'Inventory Management'); ?>

<?php
    $types = [
        '' => 'All Types',
        'stock_in' => 'Stock In',
        'stock_out' => 'Stock Out',
        'sale' => 'Sale',
        'adjustment' => 'Adjustment',
        'sale_return' => 'Return In',
    ];
?>

<?php $__env->startSection('content'); ?>
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
<div class="page-toolbar stock-toolbar">
    <div>
        <h1 class="page-heading">Stock Movements</h1>
        <p class="page-sub">Complete history of all inventory changes.</p>
    </div>
    <a href="<?php echo e(route('inventory.stock-overview')); ?>" class="btn btn-secondary">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5"/><path d="m12 19-7-7 7-7"/></svg>
        Overview
    </a>
</div>

<form method="GET" action="<?php echo e(route('inventory.movements')); ?>" class="filter-bar stock-filter movement-filter" data-live-filter>
    <label>
        <span>Movement type</span>
        <select name="type">
            <?php $__currentLoopData = $types; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($value); ?>" <?php echo e(request('type')===$value ? 'selected' : ''); ?>><?php echo e($label); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </label>
    <label>
        <span>From date</span>
        <input type="date" name="date_from" value="<?php echo e(request('date_from')); ?>">
    </label>
    <label>
        <span>To date</span>
        <input type="date" name="date_to" value="<?php echo e(request('date_to')); ?>">
    </label>
    <button type="submit" class="btn btn-primary">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 3H2l8 9.46V19l4 2v-8.54L22 3z"/></svg>
        Filter
    </button>
    <?php if(request()->filled('type') || request()->filled('date_from') || request()->filled('date_to')): ?>
    <a href="<?php echo e(route('inventory.movements')); ?>" class="btn btn-secondary">Clear</a>
    <?php endif; ?>
</form>

<div class="card stock-card" data-live-results>
    <div class="table-wrap">
        <table class="stock-table movement-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Product / Variant</th>
                    <th>Movement Type</th>
                    <th>Qty Change</th>
                    <th>Before</th>
                    <th>After</th>
                    <th>User</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $movements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $movement): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <?php
                $typeLabel = $types[$movement->movement_type] ?? str($movement->movement_type)->replace('_', ' ')->title();
                $qtyClass = $movement->quantity_change < 0 ? 'movement-negative' : 'movement-positive';
                $variant = $movement->variant;
                $product = $variant?->product;
                $variantDetails = collect([
                    $variant?->part_number,
                    $variant?->brand?->name,
                    $variant?->size,
                    $variant?->color,
                ])->filter()->join(' / ');
            ?>
            <tr>
                <td class="stock-muted"><?php echo e($movement->created_at?->format('M j, Y h:i A')); ?></td>
                <td>
                    <?php if($variant): ?>
                    <a class="stock-product-name stock-product-link" href="<?php echo e(route('inventory.show', $variant)); ?>"><?php echo e($product?->name ?? 'Deleted product'); ?></a>
                    <?php else: ?>
                    <div class="stock-product-name">Deleted product</div>
                    <?php endif; ?>
                    <div class="stock-product-sub"><?php echo e($variant?->variant_code ?? '-'); ?><?php echo e($variantDetails ? ' - '.$variantDetails : ''); ?></div>
                </td>
                <td><span class="movement-type"><?php echo e($typeLabel); ?></span></td>
                <td class="<?php echo e($qtyClass); ?>"><?php echo e($movement->quantity_change > 0 ? '+' : ''); ?><?php echo e($movement->quantity_change); ?></td>
                <td class="stock-muted"><?php echo e($movement->quantity_before); ?></td>
                <td class="stock-onhand"><?php echo e($movement->quantity_after); ?></td>
                <td><?php echo e($movement->user?->name ?? 'System Administrator'); ?></td>
                <td class="stock-muted movement-remarks"><?php echo e($movement->remarks ?: '-'); ?></td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <?php if (isset($component)) { $__componentOriginal074a021b9d42f490272b5eefda63257c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal074a021b9d42f490272b5eefda63257c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.empty-state','data' => ['colspan' => '8','title' => 'No stock movements found','message' => 'Try clearing the date or movement filters.','icon' => 'transactions']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['colspan' => '8','title' => 'No stock movements found','message' => 'Try clearing the date or movement filters.','icon' => 'transactions']); ?>
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
        <nav class="rules-pagination" aria-label="Stock variant pagination">
            <?php if($movements->onFirstPage()): ?>
                <span class="rules-page is-disabled" aria-disabled="true">Previous</span>
            <?php else: ?>
                <a class="rules-page" href="<?php echo e($movements->previousPageUrl()); ?>" rel="prev">Previous</a>
            <?php endif; ?>

            <?php $__currentLoopData = $pages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php if($previousPage && $page > $previousPage + 1): ?>
                    <span class="rules-page rules-page--gap" aria-hidden="true">...</span>
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
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<?php echo app('Illuminate\Foundation\Vite')('resources/css/views/inventory-index-1.css'); ?>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\acrylink_newbie\resources\views\inventory\movements.blade.php ENDPATH**/ ?>