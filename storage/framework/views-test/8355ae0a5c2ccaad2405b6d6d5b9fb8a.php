<?php $__env->startSection('title', 'Stock Overview'); ?>
<?php $__env->startSection('page-label', 'Inventory Management'); ?>

<?php
    $stockAlertCounts = $stockAlertCounts ?? \App\Models\Product::stockAlertBreakdown();
    $stockLevels = [
        '' => 'All Stock Levels',
        'attention' => 'Needs Attention ('.$stockAlertCounts['total'].')',
        'normal' => 'Normal',
        'critical' => 'Critical ('.$stockAlertCounts['critical'].')',
        'low' => 'Low Stock ('.$stockAlertCounts['low'].')',
        'out' => 'Out of Stock ('.$stockAlertCounts['out'].')',
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
        <h1 class="page-heading">Stock Overview</h1>
        <p class="page-sub">Monitor variant quantities and stock levels in real time.</p>
    </div>
    <div class="toolbar-actions">
        <?php if(auth()->user()->canEdit()): ?>
        <a href="<?php echo e(route('stock-receipts.create')); ?>" class="btn btn-primary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
            Receive Stock
        </a>
        <?php endif; ?>
        <a href="<?php echo e(route('inventory.movements')); ?>" class="btn btn-secondary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M7 7h15"/><path d="m18 3 4 4-4 4"/><path d="M17 17H2"/><path d="m6 13-4 4 4 4"/></svg>
            Movements
        </a>
    </div>
</div>

<form method="GET" action="<?php echo e(route('inventory.stock-overview')); ?>" class="stock-filter" id="stockOverviewLiveFilter" data-live-filter data-no-loading="true" role="search">
    <div class="stock-filter-main">
        <label class="stock-search-field">
            <i class="bi bi-search" aria-hidden="true"></i>
            <input type="search" name="search" value="<?php echo e(request('search')); ?>" placeholder="Search product, variant, part number, or brand..." autocomplete="off">
        </label>
        <label class="stock-category-filter">
            <select name="category" aria-label="Filter stock by category">
                <option value="">All Categories</option>
                <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($cat); ?>" <?php echo e(request('category')==$cat ? 'selected' : ''); ?>><?php echo e($cat); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </label>
    </div>
    <div class="stock-filter-footer">
        <input type="hidden" name="stock_level" value="<?php echo e(request('stock_level')); ?>">
        <div class="stock-filter-chips" aria-label="Filter by stock level">
            <?php $__currentLoopData = $stockLevels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <button type="button" class="stock-filter-chip <?php echo e(request('stock_level', '') === $value ? 'is-active' : ''); ?>"
                data-live-filter-choice="stock_level" data-live-filter-value="<?php echo e($value); ?>"
                aria-pressed="<?php echo e(request('stock_level', '') === $value ? 'true' : 'false'); ?>"><?php echo e($label); ?></button>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
        <span class="live-filter-status stock-filter-status" data-live-filter-status role="status" aria-live="polite"></span>
    </div>
</form>

<div data-live-results>
<div class="card stock-card">
    <div class="table-wrap">
        <table id="inventoryTable" class="stock-table">
            <thead>
                <tr>
                    <th><button type="button" class="sort-btn" data-sort="name">Product</button></th>
                    <th><button type="button" class="sort-btn" data-sort="code">Variant</button></th>
                    <th><button type="button" class="sort-btn" data-sort="brand">Brand</button></th>
                    <th>Supplier</th>
                    <th><button type="button" class="sort-btn" data-sort="unit">Unit</button></th>
                    <th><button type="button" class="sort-btn" data-sort="stock">On Hand</button></th>
                    <th><button type="button" class="sort-btn" data-sort="reorder">Minimum</button></th>
                    <th><button type="button" class="sort-btn" data-sort="critical">Critical</button></th>
                    <th><button type="button" class="sort-btn" data-sort="status">Status</button></th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $variants; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $variant): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <?php
                $product = $variant->product;
                $stock = $variant->inventoryStock;
                $quantity = (int) ($stock?->quantity_on_hand ?? 0);
                $minimum = (int) ($stock?->minimum_stock ?? 0);
                $critical = (int) ($stock?->critical_stock ?? 0);
                $status = $quantity <= 0 ? 'Out of Stock' : ($quantity <= $critical ? 'Critical' : ($quantity <= $minimum ? 'Low Stock' : 'Normal'));
                $statusClass = $status === 'Normal' ? 'status-active' : ($status === 'Low Stock' ? 'status-low' : 'status-danger');
                $stockLevel = $quantity <= 0 ? 'out' : ($quantity <= $critical ? 'critical' : ($quantity <= $minimum ? 'low' : 'normal'));
                $variantLabel = trim(($product?->name ?? 'Product').' - '.$variant->variant_code);
            ?>
            <tr data-live-row
                data-code="<?php echo e($variant->variant_code); ?>"
                data-name="<?php echo e($product?->name); ?>"
                data-category="<?php echo e($product?->category); ?>"
                data-brand="<?php echo e($variant->brand?->name); ?>"
                data-unit="<?php echo e($variant->unit?->code); ?>"
                data-stock="<?php echo e($quantity); ?>"
                data-reorder="<?php echo e($minimum); ?>"
                data-critical="<?php echo e($critical); ?>"
                data-status="<?php echo e($status); ?>"
                data-stock-level="<?php echo e($stockLevel); ?>">
                <td>
                    <div class="stock-product-name"><?php echo e($product?->name ?? 'Deleted product'); ?></div>
                    <div class="stock-product-sub"><?php echo e($product?->category ?? 'Uncategorized'); ?></div>
                </td>
                <td>
                    <div class="stock-code"><?php echo e($variant->variant_code); ?></div>
                    <div class="stock-product-sub">
                        <?php echo e(collect([$variant->part_number, $variant->size, $variant->color])->filter()->join(' / ') ?: 'No extra details'); ?>

                    </div>
                </td>
                <td class="stock-muted"><?php echo e($variant->brand?->name ?? '-'); ?></td>
                <td class="stock-muted"><?php echo e($variant->supplier?->name ?? 'Not assigned'); ?></td>
                <td><?php echo e($variant->unit?->code ?? '-'); ?></td>
                <td class="stock-onhand <?php echo e($status !== 'Normal' ? 'stock-onhand--warn' : ''); ?>"><?php echo e($quantity); ?></td>
                <td class="stock-muted"><?php echo e($minimum); ?></td>
                <td class="stock-muted"><?php echo e($critical); ?></td>
                <td><span class="status <?php echo e($statusClass); ?>"><?php echo e($status); ?></span></td>
                <td>
                    <div class="table-actions">
                        <?php if(auth()->user()->canEdit()): ?>
                        <button type="button" class="btn btn-secondary btn-sm stock-adjust-btn" title="Adjust stock for <?php echo e($variantLabel); ?>" aria-label="Adjust stock for <?php echo e($variantLabel); ?>"
                            data-product="<?php echo e($variantLabel); ?>"
                            data-current="<?php echo e($quantity); ?>"
                            data-action="<?php echo e(route('inventory.adjust-stock', $variant)); ?>">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 21v-7"/><path d="M4 10V3"/><path d="M12 21v-9"/><path d="M12 8V3"/><path d="M20 21v-5"/><path d="M20 12V3"/><path d="M2 14h4"/><path d="M10 8h4"/><path d="M18 16h4"/></svg>
                            <span>Adjust</span>
                        </button>
                        <?php else: ?>
                        <span class="stock-muted">-</span>
                        <?php endif; ?>
                        <a href="<?php echo e(route('inventory.show', $variant)); ?>" class="btn btn-secondary btn-sm icon-btn" title="View variant" aria-label="View <?php echo e($variantLabel); ?>">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z"/><circle cx="12" cy="12" r="3"/></svg>
                        </a>
                    </div>
                </td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr>
                <td colspan="10" class="empty-state">No variants found. Try adjusting your filters.</td>
            </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
    <div class="view-blades-footer">
        <div class="pagination-summary">
            Showing <?php echo e($variants->firstItem() ?? 0); ?>-<?php echo e($variants->lastItem() ?? 0); ?> of <?php echo e($variants->total()); ?> variants
        </div>

        <?php if($variants->hasPages()): ?>
        <?php
            $currentPage = $variants->currentPage();
            $lastPage = $variants->lastPage();
            $pages = collect([1, $currentPage - 1, $currentPage, $currentPage + 1, $lastPage])
                ->filter(fn ($page) => $page >= 1 && $page <= $lastPage)
                ->unique()
                ->sort()
                ->values();
            $previousPage = null;
        ?>
        <nav class="rules-pagination" aria-label="Stock variant pagination">
            <?php if($variants->onFirstPage()): ?>
                <span class="rules-page is-disabled" aria-disabled="true">Previous</span>
            <?php else: ?>
                <a class="rules-page" href="<?php echo e($variants->previousPageUrl()); ?>" rel="prev">Previous</a>
            <?php endif; ?>

            <?php $__currentLoopData = $pages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php if($previousPage && $page > $previousPage + 1): ?>
                    <span class="rules-page rules-page--gap" aria-hidden="true">...</span>
                <?php endif; ?>

                <?php if($page === $currentPage): ?>
                    <span class="rules-page is-active" aria-current="page"><?php echo e($page); ?></span>
                <?php else: ?>
                    <a class="rules-page" href="<?php echo e($variants->url($page)); ?>"><?php echo e($page); ?></a>
                <?php endif; ?>

                <?php $previousPage = $page; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

            <?php if($variants->hasMorePages()): ?>
                <a class="rules-page" href="<?php echo e($variants->nextPageUrl()); ?>" rel="next">Next</a>
            <?php else: ?>
                <span class="rules-page is-disabled" aria-disabled="true">Next</span>
            <?php endif; ?>
        </nav>
        <?php endif; ?>
    </div>
</div>
</div>

<?php if(auth()->user()->canEdit()): ?>
<div class="stock-modal" id="stockAdjustModal" hidden role="dialog" aria-modal="true" aria-labelledby="stockAdjustTitle">
    <div class="stock-modal-backdrop" data-close-adjust></div>
    <form method="POST" class="stock-modal-panel" id="stockAdjustForm">
        <?php echo csrf_field(); ?>
        <div class="stock-modal-head">
            <h2 id="stockAdjustTitle">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 21v-7"/><path d="M4 10V3"/><path d="M12 21v-9"/><path d="M12 8V3"/><path d="M20 21v-5"/><path d="M20 12V3"/><path d="M2 14h4"/><path d="M10 8h4"/><path d="M18 16h4"/></svg>
                Adjust Stock
            </h2>
            <button type="button" class="modal-close-btn" data-close-adjust aria-label="Close stock adjustment">&times;</button>
        </div>
        <div class="stock-modal-body">
            <div class="stock-modal-product" id="stockAdjustProduct"></div>
            <div class="stock-modal-current" id="stockAdjustCurrent"></div>
            <label class="form-group">
                <span>Type</span>
                <select name="type" required>
                    <option value="stock_in">Stock In (Increase)</option>
                    <option value="stock_out">Stock Out (Decrease)</option>
                    <option value="adjustment">Other Increase</option>
                </select>
            </label>
            <label class="form-group">
                <span>Quantity</span>
                <input type="number" name="quantity" min="1" max="2147483647" step="1" required aria-describedby="stockAdjustPreview">
            </label>
            <div class="stock-adjust-preview" id="stockAdjustPreview" role="status" aria-live="polite">
                Enter a quantity to preview the resulting stock.
            </div>
            <label class="form-group">
                <span>Remarks</span>
                <textarea name="remarks" placeholder="Explain why this stock is changing..." required minlength="1"></textarea>
            </label>
        </div>
        <div class="stock-modal-actions">
            <button type="button" class="btn btn-secondary" data-close-adjust>Cancel</button>
            <button type="submit" class="btn btn-primary">Apply Stock Change</button>
        </div>
    </form>
</div>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<?php echo app('Illuminate\Foundation\Vite')('resources/css/views/inventory-index-1.css'); ?>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<?php echo app('Illuminate\Foundation\Vite')('resources/js/views/inventory-index-1.js'); ?>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\acrylink_newbie\resources\views\inventory\stock-overview.blade.php ENDPATH**/ ?>