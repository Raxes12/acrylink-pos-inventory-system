<?php $__env->startSection('title', 'Categories'); ?>
<?php $__env->startSection('page-label', 'Categories'); ?>

<?php $__env->startSection('content'); ?>
<?php
    $totalProducts = $categories->sum('products_count');
    $totalVariants = $categories->sum('variants_count');
    $lowStockTotal = $categories->sum('low_stock_count');
    $emptyCategories = $categories->where('products_count', 0)->count();
?>

<div class="page-toolbar">
    <div>
        <div class="page-heading" style="margin:0;"><?php echo e($selectedCategory ? $selectedCategory->name : 'Product Categories'); ?></div>
        <div class="page-sub">
            <?php echo e($selectedCategory ? 'Products assigned to this category.' : 'Choose a category card to view its products.'); ?>

        </div>
    </div>

    <?php if($selectedCategory): ?>
    <div class="category-toolbar-actions">
        <a href="<?php echo e(route('categories.index')); ?>" class="btn btn-secondary">Back to Categories</a>
        <a href="<?php echo e(route('inventory.index', ['category' => $selectedCategory->name])); ?>" class="btn btn-primary">Open Inventory</a>
    </div>
    <?php else: ?>
    <a href="<?php echo e(route('categories.create')); ?>" class="btn btn-primary">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Add Category
    </a>
    <?php endif; ?>
</div>

<?php if(!$selectedCategory): ?>
<div class="grid-4 category-kpis">
    <div class="card metric-card">
        <div class="metric-label">Categories</div>
        <div class="metric-value text-accent"><?php echo e($categories->count()); ?></div>
        <div class="page-sub"><?php echo e($emptyCategories); ?> empty</div>
    </div>
    <div class="card metric-card">
        <div class="metric-label">Products</div>
        <div class="metric-value"><?php echo e($totalProducts); ?></div>
        <div class="page-sub"><?php echo e($totalVariants); ?> variants tracked</div>
    </div>
    <div class="card metric-card">
        <div class="metric-label">Low Stock</div>
        <div class="metric-value text-warning"><?php echo e($lowStockTotal); ?></div>
        <div class="page-sub">Needs replenishment</div>
    </div>
    <div class="card metric-card">
        <div class="metric-label">Empty</div>
        <div class="metric-value"><?php echo e($emptyCategories); ?></div>
        <div class="page-sub">Can be deleted</div>
    </div>
</div>

<section class="category-panel">
    <div class="card-header">
        <span class="card-title">Browse Categories</span>
    </div>

    <div class="category-card-grid">
        <?php $__empty_1 = true; $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <article class="category-card">
            <a href="<?php echo e(route('categories.index', ['category' => $category->id])); ?>" class="category-card-main">
                <span class="category-card-icon"><?php echo e(strtoupper(substr($category->name, 0, 2))); ?></span>
                <span class="category-card-copy">
                    <span class="category-name"><?php echo e($category->name); ?></span>
                    <span class="category-meta">Click to view products</span>
                </span>
            </a>
            <a href="<?php echo e(route('categories.index', ['category' => $category->id])); ?>" class="category-card-stats">
                <span>
                    <strong><?php echo e($category->variants_count); ?></strong>
                    <small>Variants</small>
                </span>
                <span>
                    <strong><?php echo e($category->low_stock_count); ?></strong>
                    <small>Low Variants</small>
                </span>
                <span>
                    <strong><?php echo e(number_format($category->stock_units ?? 0)); ?></strong>
                    <small>Total Units</small>
                </span>
            </a>
            <div class="category-actions">
                <a href="<?php echo e(route('categories.edit', $category)); ?>" class="btn btn-secondary btn-sm">Rename</a>
                <form method="POST" action="<?php echo e(route('categories.destroy', $category)); ?>"
                    data-confirm
                    data-confirm-title="Delete Category"
                    data-confirm-message="Delete category <?php echo e($category->name); ?>? This action cannot be undone."
                    data-confirm-button="Delete">
                    <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                    <button type="submit" class="btn btn-danger btn-sm" <?php if($category->products_count > 0): echo 'disabled'; endif; ?> title="<?php echo e($category->products_count > 0 ? 'Only empty categories can be deleted.' : 'Delete category'); ?>">Delete</button>
                </form>
            </div>
        </article>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <div class="empty-state">No categories found.</div>
        <?php endif; ?>
    </div>
</section>
<?php else: ?>
<section class="card category-products">
    <div class="card-header category-products-header">
        <div>
            <span class="card-title"><?php echo e($selectedCategory->name); ?> Products</span>
            <div class="page-sub"><?php echo e($products->total()); ?> product<?php echo e($products->total() === 1 ? '' : 's'); ?> found in this category</div>
        </div>
    </div>

    <form method="GET" action="<?php echo e(route('categories.index')); ?>" class="filter-bar category-filter" data-live-filter>
        <input type="hidden" name="category" value="<?php echo e($selectedCategory->id); ?>">
        <input type="text" name="search" value="<?php echo e($search); ?>" placeholder="Search products in <?php echo e($selectedCategory->name); ?>...">
        <button type="submit" class="btn btn-primary">Search</button>
        <a href="<?php echo e(route('categories.index', ['category' => $selectedCategory->id])); ?>" class="btn btn-secondary" data-live-filter-clear>Clear</a>
        <span class="live-filter-status" data-live-filter-status role="status" aria-live="polite"></span>
    </form>

    <div data-live-results>
    <table>
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
                <td>
                    <div style="font-weight:700;"><?php echo e($product->name); ?></div>
                    <div class="text-muted" style="font-size:11px;margin-top:2px;">
                        <?php echo e($product->variants_count); ?> variant<?php echo e($product->variants_count === 1 ? '' : 's'); ?>

                    </div>
                    <?php $__currentLoopData = $variantPreview; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $variant): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="text-muted" style="font-size:11px;margin-top:2px;">
                        <?php echo e(collect([$variant->variant_code, $variant->part_number, $variant->brand?->name, $variant->size, $variant->color, $variant->unit?->code])->filter()->join(' / ')); ?>

                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php if($variants->count() > $variantPreview->count()): ?>
                    <div class="text-muted" style="font-size:11px;margin-top:2px;">+ <?php echo e($variants->count() - $variantPreview->count()); ?> more</div>
                    <?php endif; ?>
                </td>
                <td class="text-right fw-bold">
                    <?php if($minPrice === null): ?>
                    -
                    <?php elseif((float) $minPrice === (float) $maxPrice): ?>
                    &#8369;<?php echo e(number_format((float) $minPrice, 2)); ?>

                    <?php else: ?>
                    &#8369;<?php echo e(number_format((float) $minPrice, 2)); ?> - &#8369;<?php echo e(number_format((float) $maxPrice, 2)); ?>

                    <?php endif; ?>
                </td>
                <td class="text-right">
                    <span style="color:<?php echo e($lowCount > 0 ? 'var(--warning)' : 'var(--accent2)'); ?>;font-weight:800;"><?php echo e(number_format($product->total_stock_units)); ?></span>
                    <span class="text-muted" style="font-size:11px;">units</span>
                </td>
                <td class="text-muted" style="font-size:12px;"><?php echo e($supplierNames->isNotEmpty() ? $supplierNames->join(', ') : 'None'); ?></td>
                <td>
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
            <tr><td colspan="5" class="empty-state">No products found for this category.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="category-pagination"><?php echo e($products->links()); ?></div>
    </div>
</section>
<?php endif; ?>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<?php echo app('Illuminate\Foundation\Vite')('resources/css/views/categories-index-1.css'); ?>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\acrylink_newbie\resources\views\categories\index.blade.php ENDPATH**/ ?>