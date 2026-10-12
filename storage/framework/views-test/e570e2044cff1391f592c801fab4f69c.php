<?php $__env->startSection('title', 'Inventory Reports'); ?>
<?php $__env->startSection('page-label', 'Inventory Reports'); ?>

<?php $__env->startSection('content'); ?>
<div class="reports-page">
    <div class="page-toolbar">
        <div>
            <h1 class="page-heading">Inventory Reports</h1>
            <p class="page-sub">Variant-level stock, value, supplier, and reorder monitoring.</p>
        </div>
        <div class="page-actions" data-live-sync="report-actions">
            <a href="<?php echo e(route('reports.index')); ?>" class="btn btn-secondary">Sales Reports</a>
            <a href="<?php echo e($exportUrl); ?>" class="btn report-export report-export--csv"><i class="bi bi-file-earmark-spreadsheet" aria-hidden="true"></i> Export CSV</a>
            <a href="<?php echo e($exportPdfUrl); ?>" class="btn report-export report-export--pdf"><i class="bi bi-file-earmark-pdf" aria-hidden="true"></i> Export PDF</a>
        </div>
        
    </div>

    <form method="GET" action="<?php echo e(route('reports.inventory')); ?>" class="card inventory-report-filter" data-live-filter role="search">
        <label class="inventory-filter-field inventory-filter-search"><span>Search inventory</span><div><i class="bi bi-search" aria-hidden="true"></i><input type="search" name="search" value="<?php echo e($search); ?>" placeholder="Product, variant, part number, or brand" autocomplete="off"></div></label>
        <label class="inventory-filter-field"><span>Category</span><select name="category">
            <option value="">All Categories</option>
            <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($cat); ?>" <?php echo e($category === $cat ? 'selected' : ''); ?>><?php echo e($cat); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select></label>
        <label class="inventory-filter-field"><span>Stock status</span><select name="stock_level">
            <option value="">All Stock Levels</option>
            <option value="attention" <?php echo e($stockLevel === 'attention' ? 'selected' : ''); ?>>Needs Attention</option>
            <option value="normal" <?php echo e($stockLevel === 'normal' ? 'selected' : ''); ?>>Normal</option>
            <option value="low" <?php echo e($stockLevel === 'low' ? 'selected' : ''); ?>>Low Stock</option>
            <option value="critical" <?php echo e($stockLevel === 'critical' ? 'selected' : ''); ?>>Critical</option>
            <option value="out" <?php echo e($stockLevel === 'out' ? 'selected' : ''); ?>>Out of Stock</option>
        </select></label>
        <span class="live-filter-status" data-live-filter-status role="status" aria-live="polite"></span>
    </form>

    <div data-live-results>
    <?php if($errors->has('export')): ?>
    <div class="alert alert-danger" style="margin-bottom:16px;"><?php echo e($errors->first('export')); ?></div>
    <?php endif; ?>

    <div class="grid-4 report-metric-grid">
        <div class="card metric-card report-metric report-metric--variants">
            <div class="metric-topline"><span class="metric-label">Variants</span><span class="metric-icon icon-products"><?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
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
<?php endif; ?></span></div>
            <div class="metric-value"><?php echo e(number_format($totalVariants)); ?></div>
            <div class="metric-note">Product variants in catalogue</div>
        </div>
        <div class="card metric-card report-metric report-metric--stock">
            <div class="metric-topline"><span class="metric-label">On Hand</span><span class="metric-icon icon-txn"><?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'stock']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'stock']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?></span></div>
            <div class="metric-value"><?php echo e(number_format($totalStock)); ?></div>
            <div class="metric-note">Total units across variants</div>
        </div>
        <div class="card metric-card report-metric report-metric--value">
            <div class="metric-topline"><span class="metric-label">Inventory Value</span><span class="metric-icon icon-sales"><?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
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
<?php endif; ?></span></div>
            <div class="metric-value report-value-emphasis">&#8369;<?php echo e(number_format($inventoryValue, 2)); ?></div>
            <div class="metric-note">Quantity on hand times cost price</div>
        </div>
        <div class="card metric-card report-metric report-metric--attention">
            <div class="metric-topline"><span class="metric-label">Needs Attention</span><span class="metric-icon icon-low"><?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
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
<?php endif; ?></span></div>
            <div class="metric-value report-value-attention"><?php echo e(number_format($lowStock + $criticalStock + $outOfStock)); ?></div>
            <div class="metric-note"><?php echo e($lowStock); ?> low, <?php echo e($criticalStock); ?> critical, <?php echo e($outOfStock); ?> out</div>
        </div>
    </div>

    <div class="card stock-card">
        <div class="card-header">
            <div><span class="card-title"><i class="bi bi-box-seam" aria-hidden="true"></i> Inventory Detail</span><span class="report-card-caption">Variant stock levels, pricing, and reorder thresholds</span></div>
            <span class="tag tag--accent"><?php echo e($variants->total()); ?> variants</span>
        </div>
        <div class="table-wrap">
            <table class="stock-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Variant</th>
                        <th>Brand</th>
                        <th>Supplier</th>
                        <th class="text-right">Cost</th>
                        <th class="text-right">Total Cost</th>
                        <th class="text-right">Selling</th>
                        <th class="text-right">On Hand</th>
                        <th class="text-right">Minimum</th>
                        <th class="text-right">Critical</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $variants; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $variant): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php
                    $stock = $variant->inventoryStock;
                    $quantity = (int) ($stock?->quantity_on_hand ?? 0);
                    $minimum = (int) ($stock?->minimum_stock ?? 0);
                    $critical = (int) ($stock?->critical_stock ?? 0);
                    $status = $quantity <= 0 ? 'Out of Stock' : ($quantity <= $critical ? 'Critical' : ($quantity <= $minimum ? 'Low Stock' : 'Normal'));
                    $statusClass = $status === 'Normal' ? 'status-active' : ($status === 'Low Stock' ? 'status-low' : 'status-danger');
                ?>
                <tr>
                    <td>
                        <div class="stock-product-name"><?php echo e($variant->product?->name ?? 'Deleted product'); ?></div>
                        <div class="stock-product-sub"><?php echo e($variant->product?->category ?? 'Uncategorized'); ?></div>
                    </td>
                    <td>
                        <div class="stock-code"><?php echo e($variant->variant_code); ?></div>
                        <div class="stock-product-sub"><?php echo e(collect([$variant->part_number, $variant->size, $variant->color])->filter()->join(' / ') ?: 'No extra details'); ?></div>
                    </td>
                    <td><?php echo e($variant->brand?->name ?? '-'); ?></td>
                    <td class="stock-muted"><?php echo e($variant->supplier?->name ?? 'None'); ?></td>
                    <td class="text-right stock-muted">&#8369;<?php echo e(number_format((float) $variant->cost_price, 2)); ?></td>
                    <td class="text-right stock-muted">&#8369;<?php echo e(number_format((float) ($variant->cost_price * $quantity), 2)); ?></td>
                    <td class="text-right fw-800" style="color:var(--accent2);">&#8369;<?php echo e(number_format((float) $variant->selling_price, 2)); ?></td>
                    <td class="text-right stock-onhand <?php echo e($status !== 'Normal' ? 'stock-onhand--warn' : ''); ?>"><?php echo e($quantity); ?></td>
                    <td class="text-right stock-muted"><?php echo e($minimum); ?></td>
                    <td class="text-right stock-muted"><?php echo e($critical); ?></td>
                    <td><span class="status <?php echo e($statusClass); ?>"><?php echo e($status); ?></span></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="10" class="empty-state">No inventory records match the selected filters.</td>
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
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<?php echo app('Illuminate\Foundation\Vite')('resources/css/views/reports-index-1.css'); ?>
<?php echo app('Illuminate\Foundation\Vite')('resources/css/views/inventory-index-1.css'); ?>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\acrylink_newbie\resources\views\reports\inventory.blade.php ENDPATH**/ ?>