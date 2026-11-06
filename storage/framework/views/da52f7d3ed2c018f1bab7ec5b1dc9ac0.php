<?php $__env->startSection('title', 'Categories'); ?>
<?php $__env->startSection('page-label', 'Categories'); ?>

<?php $__env->startSection('content'); ?>
<?php
    $totalProducts = $categories->sum('products_count');
    $totalVariants = $categories->sum('variants_count');
    $lowStockTotal = $categories->sum('low_stock_count');
    $emptyCategories = $categories->where('products_count', 0)->count();
?>

<div class="categories-page">
<?php if (isset($component)) { $__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => ''.e($selectedCategory ? $selectedCategory->name : 'Product Categories').'','subtitle' => ''.e($selectedCategory ? 'Review the products, variants, stock health, and suppliers assigned to this category.' : 'Organize products into clear groups and quickly spot categories that need attention.').'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => ''.e($selectedCategory ? $selectedCategory->name : 'Product Categories').'','subtitle' => ''.e($selectedCategory ? 'Review the products, variants, stock health, and suppliers assigned to this category.' : 'Organize products into clear groups and quickly spot categories that need attention.').'']); ?>
     <?php $__env->slot('actions', null, []); ?> 
        <?php if($selectedCategory): ?>
        <a href="<?php echo e(route('categories.index')); ?>" class="btn btn-secondary"><i class="bi bi-arrow-left" aria-hidden="true"></i> All Categories</a>
        <a href="<?php echo e(route('inventory.index', ['category' => $selectedCategory->name])); ?>" class="btn btn-primary"><?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
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
<?php endif; ?> Open in Inventory</a>
        <?php else: ?>
        <a href="<?php echo e(route('categories.create')); ?>" class="btn btn-primary"><i class="bi bi-plus-lg" aria-hidden="true"></i> Add Category</a>
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

<?php if(!$selectedCategory): ?>
<div class="category-kpis" aria-label="Category summary">
    <article class="card category-kpi"><span class="category-kpi-icon"><?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
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
<?php endif; ?></span><div><span>Categories</span><strong><?php echo e(number_format($categories->count())); ?></strong><small><?php echo e($emptyCategories); ?> currently empty</small></div></article>
    <article class="card category-kpi"><span class="category-kpi-icon"><?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
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
<?php endif; ?></span><div><span>Products</span><strong><?php echo e(number_format($totalProducts)); ?></strong><small><?php echo e(number_format($totalVariants)); ?> variants tracked</small></div></article>
    <article class="card category-kpi category-kpi--warning"><span class="category-kpi-icon"><?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
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
<?php endif; ?></span><div><span>Low-stock variants</span><strong><?php echo e(number_format($lowStockTotal)); ?></strong><small><?php echo e($lowStockTotal > 0 ? 'Review replenishment needs' : 'Stock levels look healthy'); ?></small></div></article>
    <article class="card category-kpi category-kpi--muted"><span class="category-kpi-icon"><i class="bi bi-folder2-open" aria-hidden="true"></i></span><div><span>Empty categories</span><strong><?php echo e(number_format($emptyCategories)); ?></strong><small>Safe to remove if unused</small></div></article>
</div>

<section class="category-panel" aria-labelledby="categoryDirectoryHeading">
    <div class="card-header category-panel-header">
        <div><span class="card-title" id="categoryDirectoryHeading"><i class="bi bi-grid" aria-hidden="true"></i> Category Directory</span><span class="category-panel-note"><span data-category-visible-count><?php echo e($categories->count()); ?></span> of <?php echo e($categories->count()); ?> categories shown</span></div>
        <label class="category-directory-search"><span class="visually-hidden">Search categories</span><i class="bi bi-search" aria-hidden="true"></i><input type="search" placeholder="Find a category..." autocomplete="off" data-category-search><button type="button" aria-label="Clear category search" data-category-search-clear hidden><i class="bi bi-x-lg" aria-hidden="true"></i></button></label>
    </div>

    <div class="category-card-grid" data-category-grid>
        <?php $__empty_1 = true; $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <article class="category-card" data-category-card data-category-name="<?php echo e(str($category->name)->lower()); ?>">
            <a href="<?php echo e(route('categories.index', ['category' => $category->id])); ?>" class="category-card-main">
                <span class="category-card-icon"><?php echo e(strtoupper(substr($category->name, 0, 2))); ?></span>
                <span class="category-card-copy">
                    <span class="category-name"><?php echo e($category->name); ?></span>
                    <span class="category-meta"><?php echo e($category->products_count); ?> product<?php echo e($category->products_count === 1 ? '' : 's'); ?></span>
                </span>
                <i class="bi bi-chevron-right category-card-chevron" aria-hidden="true"></i>
            </a>
            <a href="<?php echo e(route('categories.index', ['category' => $category->id])); ?>" class="category-card-stats">
                <span>
                    <strong><?php echo e(number_format($category->variants_count)); ?></strong>
                    <small>Variants</small>
                </span>
                <span class="<?php echo e($category->low_stock_count > 0 ? 'has-attention' : ''); ?>">
                    <strong><?php echo e(number_format($category->low_stock_count)); ?></strong>
                    <small>Low stock</small>
                </span>
                <span>
                    <strong><?php echo e(number_format($category->stock_units ?? 0)); ?></strong>
                    <small>Total units</small>
                </span>
            </a>
            <div class="category-actions">
                <a href="<?php echo e(route('categories.index', ['category' => $category->id])); ?>" class="btn btn-secondary btn-sm category-view-action"><i class="bi bi-eye" aria-hidden="true"></i> View products</a>
                <a href="<?php echo e(route('categories.edit', $category)); ?>" class="btn btn-secondary btn-sm" aria-label="Rename <?php echo e($category->name); ?>"><i class="bi bi-pencil" aria-hidden="true"></i><span class="category-action-label">Rename</span></a>
                <form method="POST" action="<?php echo e(route('categories.destroy', $category)); ?>"
                    data-confirm
                    data-confirm-title="Delete <?php echo e($category->name); ?>?"
                    data-confirm-message="This category will be permanently deleted. This action cannot be undone."
                    data-confirm-button="Delete category">
                    <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                    <button type="submit" class="btn btn-danger btn-sm" <?php if($category->products_count > 0): echo 'disabled'; endif; ?> aria-label="Delete <?php echo e($category->name); ?>" title="<?php echo e($category->products_count > 0 ? 'Move or remove its products before deleting this category.' : 'Delete category'); ?>"><i class="bi bi-trash" aria-hidden="true"></i><span class="category-action-label">Delete</span></button>
                </form>
            </div>
        </article>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <?php if (isset($component)) { $__componentOriginal074a021b9d42f490272b5eefda63257c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal074a021b9d42f490272b5eefda63257c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.empty-state','data' => ['title' => 'No categories yet','message' => 'Create the first category to start organizing inventory products.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'No categories yet','message' => 'Create the first category to start organizing inventory products.']); ?>
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
        <div class="category-search-empty" data-category-search-empty hidden><span class="category-search-empty-icon"><i class="bi bi-search" aria-hidden="true"></i></span><strong>No matching categories</strong><span>Try another name or clear the search.</span></div>
    </div>
</section>
<?php else: ?>
<section class="card category-products">
    <div class="card-header category-products-header">
        <div>
            <span class="card-title"><?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
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
<?php endif; ?> Products in <?php echo e($selectedCategory->name); ?></span>
            <div class="category-panel-note">Search by product name, variant code, barcode, or brand.</div>
        </div>
        <span class="category-result-count" data-live-sync="category-product-count"><?php echo e(number_format($products->total())); ?> product<?php echo e($products->total() === 1 ? '' : 's'); ?></span>
    </div>

    <form method="GET" action="<?php echo e(route('categories.index')); ?>" class="category-filter" data-live-filter>
        <input type="hidden" name="category" value="<?php echo e($selectedCategory->id); ?>">
        <label class="category-product-search"><span class="visually-hidden">Search products in <?php echo e($selectedCategory->name); ?></span><i class="bi bi-search" aria-hidden="true"></i><input type="search" name="search" value="<?php echo e($search); ?>" placeholder="Search products, variants, barcodes, or brands..." autocomplete="off"><a href="<?php echo e(route('categories.index', ['category' => $selectedCategory->id])); ?>" data-live-filter-clear aria-label="Clear product search"><i class="bi bi-x-lg" aria-hidden="true"></i></a></label>
        <span class="live-filter-status" data-live-filter-status role="status" aria-live="polite"></span>
        <noscript><button type="submit" class="btn btn-primary">Search</button></noscript>
    </form>

    <div data-live-results>
    <div class="category-table-wrap" tabindex="0" role="region" aria-label="Products in <?php echo e($selectedCategory->name); ?>">
    <table class="category-products-table">
        <thead>
            <tr>
                <th>Product / Variants</th>
                <th class="text-right">Price Range</th>
                <th class="text-right">Total Stock</th>
                <th>Suppliers</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <?php
                $variants = $product->variants;
                $variantPreview = $variants->take(3);
                $minPrice = $product->min_selling_price;
                $maxPrice = $product->max_selling_price;
                $lowCount = (int) $product->low_stock_variants_count;
                $outCount = (int) $product->out_of_stock_variants_count;
                $supplierNames = $product->supplier_names;
            ?>
            <tr>
                <td data-label="Product / Variants">
                    <div class="category-product-name"><?php echo e($product->name); ?></div>
                    <div class="category-product-count"><?php echo e($product->variants_count); ?> variant<?php echo e($product->variants_count === 1 ? '' : 's'); ?></div>
                    <div class="category-variant-preview">
                    <?php $__currentLoopData = $variantPreview; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $variant): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <span><?php echo e(collect([$variant->variant_code, $variant->part_number, $variant->brand?->name, $variant->size, $variant->color, $variant->unit?->code])->filter()->join(' / ')); ?></span>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php if($variants->count() > $variantPreview->count()): ?>
                    <span class="category-variant-more">+ <?php echo e($variants->count() - $variantPreview->count()); ?> more</span>
                    <?php endif; ?>
                    </div>
                </td>
                <td class="text-right category-price" data-label="Price Range">
                    <?php if($minPrice === null): ?>
                    -
                    <?php elseif((float) $minPrice === (float) $maxPrice): ?>
                    &#8369;<?php echo e(number_format((float) $minPrice, 2)); ?>

                    <?php else: ?>
                    &#8369;<?php echo e(number_format((float) $minPrice, 2)); ?> - &#8369;<?php echo e(number_format((float) $maxPrice, 2)); ?>

                    <?php endif; ?>
                </td>
                <td class="text-right category-stock <?php echo e($lowCount > 0 ? 'has-attention' : ''); ?>" data-label="Total Stock">
                    <strong><?php echo e(number_format($product->total_stock_units)); ?></strong><small> units</small>
                </td>
                <td class="category-suppliers" data-label="Suppliers"><?php echo e($supplierNames->isNotEmpty() ? $supplierNames->join(', ') : 'No supplier assigned'); ?></td>
                <td data-label="Status">
                    <?php if($outCount > 0): ?>
                    <span class="status status-inactive"><?php echo e($outCount); ?> Out</span>
                    <?php elseif($lowCount > 0): ?>
                    <span class="status status-low"><?php echo e($lowCount); ?> Low</span>
                    <?php else: ?>
                    <span class="status status-active">OK</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr><td colspan="5"><?php if (isset($component)) { $__componentOriginal074a021b9d42f490272b5eefda63257c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal074a021b9d42f490272b5eefda63257c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.empty-state','data' => ['title' => 'No matching products','message' => 'Try another search, or open Inventory to add products to this category.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'No matching products','message' => 'Try another search, or open Inventory to add products to this category.']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal074a021b9d42f490272b5eefda63257c)): ?>
<?php $attributes = $__attributesOriginal074a021b9d42f490272b5eefda63257c; ?>
<?php unset($__attributesOriginal074a021b9d42f490272b5eefda63257c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal074a021b9d42f490272b5eefda63257c)): ?>
<?php $component = $__componentOriginal074a021b9d42f490272b5eefda63257c; ?>
<?php unset($__componentOriginal074a021b9d42f490272b5eefda63257c); ?>
<?php endif; ?></td></tr>
            <?php endif; ?>
        </tbody>
    </table>
    </div>

    <div class="category-pagination"><?php echo e($products->links()); ?></div>
    </div>
</section>
<?php endif; ?>

</div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<?php echo app('Illuminate\Foundation\Vite')('resources/css/views/categories-index-1.css'); ?>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<?php echo app('Illuminate\Foundation\Vite')('resources/js/views/categories-index-1.js'); ?>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\acrylink_newbie1\resources\views\categories\index.blade.php ENDPATH**/ ?>