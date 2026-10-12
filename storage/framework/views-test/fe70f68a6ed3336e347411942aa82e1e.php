<?php $__env->startSection('title', 'Products'); ?>
<?php $__env->startSection('page-label', 'Products'); ?>

<?php $__env->startSection('content'); ?>
<div class="products-page">
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => 'Products','subtitle' => 'Manage product variants, pricing, suppliers, and availability.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Products','subtitle' => 'Manage product variants, pricing, suppliers, and availability.']); ?>
         <?php $__env->slot('actions', null, []); ?> 
            <?php if(auth()->user()->canEdit()): ?>
            <a href="<?php echo e(route('inventory.create')); ?>" class="btn btn-primary">
                <?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'plus']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'plus']); ?>
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
                Add Variant
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

    <form method="GET" action="<?php echo e(route('inventory.index')); ?>" class="filter-bar products-filter" id="inventoryLiveFilter" data-live-filter data-no-loading="true" role="search">
        <label class="products-search">
            <span class="visually-hidden">Search products</span>
            <?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'search']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'search']); ?>
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
            <input type="search" name="search" value="<?php echo e(request('search')); ?>" placeholder="Search product, code, barcode, part number, or brand..." autocomplete="off">
        </label>
        <select name="category" id="inventoryCategory" aria-label="Filter by category">
            <option value="">All Categories</option>
            <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($cat); ?>" <?php echo e(request('category') === $cat ? 'selected' : ''); ?>><?php echo e($cat); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
        <select name="status" id="inventoryStatus" aria-label="Filter by product status">
            <option value="">All Statuses</option>
            <option value="active" <?php echo e(request('status') === 'active' ? 'selected' : ''); ?>>Active</option>
            <option value="inactive" <?php echo e(request('status') === 'inactive' ? 'selected' : ''); ?>>Inactive</option>
        </select>
        <span class="live-filter-status products-filter-status" data-live-filter-status role="status" aria-live="polite"></span>
    </form>
    
    <section class="card products-card" aria-label="Product variants" data-live-results>
        <div class="table-wrap">
            <table id="inventoryTable" class="inventory-products-table">
                <thead>
                    <tr>
                        <th><button type="button" class="sort-btn" data-sort="name">Product / Variant</button></th>
                        <th><button type="button" class="sort-btn" data-sort="part">Part / Brand</button></th>
                        <th><button type="button" class="sort-btn" data-sort="size">Details</button></th>
                        <th><button type="button" class="sort-btn" data-sort="supplier">Supplier</button></th>
                        <th class="text-right"><button type="button" class="sort-btn sort-btn--right" data-sort="price">Selling</button></th>
                        <th class="text-right"><button type="button" class="sort-btn sort-btn--right" data-sort="cost">Cost</button></th>
                        <th><button type="button" class="sort-btn" data-sort="stock">Stock</button></th>
                        <th class="text-center inventory-actions-col">Actions</th>
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
                    $stockState = $quantity <= 0
                        ? ['class' => 'out', 'label' => 'Out of stock']
                        : ($quantity <= $critical
                            ? ['class' => 'critical', 'label' => 'Critical']
                            : ($quantity <= $minimum
                                ? ['class' => 'low', 'label' => 'Low stock']
                                : ['class' => 'in', 'label' => 'In stock']));
                    $detailParts = collect([$variant->size, $variant->color, $variant->unit?->code])->filter();
                ?>
                <tr data-live-row
                    data-name="<?php echo e($product?->name); ?> <?php echo e($variant->variant_code); ?>"
                    data-category="<?php echo e($product?->category); ?>"
                    data-part="<?php echo e($variant->part_number); ?>"
                    data-brand="<?php echo e($variant->brand?->name); ?>"
                    data-size="<?php echo e($detailParts->join(' ')); ?>"
                    data-price="<?php echo e($variant->selling_price); ?>"
                    data-cost="<?php echo e($variant->cost_price); ?>"
                    data-stock="<?php echo e($quantity); ?>"
                    data-supplier="<?php echo e($variant->supplier?->name ?? ''); ?>"
                    data-status="<?php echo e($variant->status); ?>">
                    <td class="product-cell product-cell--<?php echo e($variant->status); ?>">
                        <?php if($variant->image_path): ?>
                        <img
                            src="<?php echo e(asset('storage/'.$variant->image_path)); ?>"
                            class="inventory-thumb"
                            width="46"
                            height="46"
                            loading="lazy"
                            alt="<?php echo e($product?->name ?? 'Product'); ?> <?php echo e($variant->variant_code); ?>">
                        <?php else: ?>
                        <div class="inventory-thumb inventory-thumb--empty" aria-hidden="true">
                            <?php echo e(strtoupper(substr($product?->name ?? 'PR', 0, 2))); ?>

                        </div>
                        <?php endif; ?>
                        <div class="product-info">
                            <a href="<?php echo e(route('inventory.show', $variant)); ?>" class="stock-product-name"><?php echo e($product?->name ?? 'Deleted product'); ?></a>
                            <div class="product-variant-meta">
                                <span class="stock-code"><?php echo e($variant->variant_code); ?></span>
                                <span class="product-availability product-availability--<?php echo e($variant->status); ?>" title="Variant is <?php echo e($variant->status); ?>">
                                    <span class="product-availability-dot" aria-hidden="true"></span>
                                    <?php echo e(ucfirst($variant->status)); ?>

                                </span>
                                <?php if($product?->category): ?><span><?php echo e($product->category); ?></span><?php endif; ?>
                            </div>
                        </div>
                    </td>
                    <td>
                        <strong class="product-part-number"><?php echo e($variant->part_number ?: 'No part number'); ?></strong>
                        <span class="product-secondary"><?php echo e($variant->brand?->name ?? 'No brand'); ?></span>
                    </td>
                    <td>
                        <?php if($detailParts->isNotEmpty()): ?>
                        <div class="product-attributes">
                            <?php $__currentLoopData = $detailParts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $detail): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><span><?php echo e($detail); ?></span><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                        <?php else: ?>
                        <span class="product-secondary">Not specified</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if($variant->supplier): ?>
                        <a href="<?php echo e(route('suppliers.show', $variant->supplier)); ?>" class="product-supplier"><?php echo e($variant->supplier->name); ?></a>
                        <?php else: ?>
                        <span class="product-secondary">No supplier</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-right product-selling-price">&#8369;<?php echo e(number_format((float) $variant->selling_price, 2)); ?></td>
                    <td class="text-right product-cost-price">&#8369;<?php echo e(number_format((float) $variant->cost_price, 2)); ?></td>
                    <td>
                        <div class="product-stock product-stock--<?php echo e($stockState['class']); ?>">
                            <strong><?php echo e(number_format($quantity)); ?></strong>
                            <span><?php echo e($stockState['label']); ?></span>
                        </div>
                    </td>
                    <td>
                        <div class="table-actions inventory-row-actions">
                            <a href="<?php echo e(route('inventory.show', $variant)); ?>" class="btn btn-secondary btn-sm product-action-btn" title="View variant" aria-label="View <?php echo e($variant->variant_code); ?>">
                                <i class="bi bi-eye" aria-hidden="true"></i>
                            </a>
                            <?php if(auth()->user()->canEdit()): ?>
                            <a href="<?php echo e(route('inventory.edit', $variant)); ?>" class="btn btn-secondary btn-sm product-action-btn" title="Edit variant" aria-label="Edit <?php echo e($variant->variant_code); ?>">
                                <i class="bi bi-pencil" aria-hidden="true"></i>
                            </a>
                            <?php if($variant->status === 'active'): ?>
                            <button type="button" class="btn btn-danger btn-sm product-action-btn" disabled title="Set this variant to inactive before deleting it" aria-label="Delete disabled for active variant <?php echo e($variant->variant_code); ?>">
                                <i class="bi bi-trash3" aria-hidden="true"></i>
                            </button>
                            <?php else: ?>
                            <form method="POST" action="<?php echo e(route('inventory.destroy', $variant)); ?>"
                                data-confirm
                                data-confirm-title="Delete variant?"
                                data-confirm-message="Delete <?php echo e($product?->name); ?> (<?php echo e($variant->variant_code); ?>)? This cannot be undone."
                                data-confirm-button="Delete">
                                <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                <button type="button" class="btn btn-danger btn-sm product-action-btn" title="Delete variant" aria-label="Delete <?php echo e($variant->variant_code); ?>" data-confirm-trigger>
                                    <i class="bi bi-trash3" aria-hidden="true"></i>
                                </button>
                            </form>
                            <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <?php if (isset($component)) { $__componentOriginal074a021b9d42f490272b5eefda63257c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal074a021b9d42f490272b5eefda63257c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.empty-state','data' => ['colspan' => '8','title' => 'No product variants found','message' => 'Try clearing the current filters or add a new product variant.','icon' => 'products']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['colspan' => '8','title' => 'No product variants found','message' => 'Try clearing the current filters or add a new product variant.','icon' => 'products']); ?>
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
    </section>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<?php echo app('Illuminate\Foundation\Vite')('resources/css/views/inventory-index-1.css'); ?>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<?php echo app('Illuminate\Foundation\Vite')('resources/js/views/inventory-index-1.js'); ?>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\acrylink_newbie\resources\views\inventory\index.blade.php ENDPATH**/ ?>