<?php $__env->startSection('title', 'View Variant'); ?>
<?php $__env->startSection('page-label', 'Inventory / View Variant'); ?>

<?php
    $product = $variant->product;
    $stock = $variant->inventoryStock;
    $quantity = (int) ($stock?->quantity_on_hand ?? 0);
    $minimum = (int) ($stock?->minimum_stock ?? 0);
    $critical = (int) ($stock?->critical_stock ?? 0);

    $stockStatus = $quantity <= 0
        ? 'Out of Stock'
        : ($quantity <= $critical ? 'Critical' : ($quantity <= $minimum ? 'Low Stock' : 'Normal'));

    $stockStatusClass = match ($stockStatus) {
        'Normal' => 'status-active',
        'Low Stock' => 'status-low',
        default => 'status-danger',
    };

    $productName = $product?->name ?? 'Deleted product';
?>

<?php $__env->startSection('content'); ?>
<div class="editor-page inventory-view-page variant-show-page">

    <a href="<?php echo e(route('inventory.index')); ?>" class="editor-breadcrumb">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
             stroke-linecap="round" stroke-linejoin="round">
            <path d="M19 12H5"/><path d="m12 19-7-7 7-7"/>
        </svg>
        Products
    </a>

    <div class="page-toolbar variant-toolbar">
        <div>
            <h1 class="page-heading"><?php echo e($productName); ?></h1>
            <p class="page-sub">Read-only product variant details.</p>
        </div>
    </div>

    <div class="variant-main-grid">

        <div class="variant-left-column">
            <section class="card variant-product-card">
                <div class="variant-image-area">
                    <?php if($variant->image_path): ?>
                        <img src="<?php echo e(asset('storage/'.$variant->image_path)); ?>"
                             alt="<?php echo e($productName); ?> <?php echo e($variant->variant_code); ?>"
                             class="variant-product-image">
                    <?php else: ?>
                        <div class="variant-image-placeholder">
                            <svg viewBox="0 0 48 48" fill="none">
                                <rect x="7" y="8" width="34" height="32" rx="4"
                                      stroke="currentColor" stroke-width="3"/>
                                <circle cx="18" cy="19" r="4" fill="currentColor"/>
                                <path d="M10 35l10-10 7 7 5-5 6 8"
                                      stroke="currentColor" stroke-width="3"
                                      stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="variant-summary-content">
                    <h2><?php echo e($productName); ?></h2>
                    <div class="variant-code-caption"><?php echo e($variant->variant_code); ?></div>
                    <div class="variant-price">
                        ₱<?php echo e(number_format((float) $variant->selling_price, 2)); ?>

                    </div>
                    <span class="status <?php echo e($variant->status === 'active' ? 'status-active' : 'status-danger'); ?>">
                        <?php echo e(ucfirst($variant->status ?? 'active')); ?>

                    </span>
                </div>

                <a href="<?php echo e(route('inventory.edit', $variant)); ?>"
                   class="btn-edit">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M4 20h4L19 9l-4-4L4 16v4zM13.5 6.5l4 4"
                              fill="none" stroke="currentColor"
                              stroke-width="2" stroke-linecap="round"
                              stroke-linejoin="round"/>
                    </svg>
                    Edit Product
                </a>
            </section>

            <section class="card variant-stock-card">
                <h2 class="variant-section-title">STOCK STATUS</h2>

                <div class="variant-stock-main">
                    <div class="variant-stock-number"><?php echo e(number_format($quantity)); ?></div>
                    <div class="variant-stock-label">
                        <?php echo e($variant->unit?->code ?? 'Units'); ?> on hand
                    </div>
                </div>

                <div class="variant-stock-details">
                    <div class="variant-detail-row">
                        <span>Reorder Level</span>
                        <strong><?php echo e(number_format($minimum)); ?></strong>
                    </div>
                    <div class="variant-detail-row">
                        <span>Critical Level</span>
                        <strong><?php echo e(number_format($critical)); ?></strong>
                    </div>
                    <div class="variant-detail-row">
                        <span>Cost Price</span>
                        <strong>₱<?php echo e(number_format((float) $variant->cost_price, 2)); ?></strong>
                    </div>
                    <div class="variant-detail-row">
                        <span>Stock Status</span>
                        <strong>
                            <span class="status <?php echo e($stockStatusClass); ?>"><?php echo e($stockStatus); ?></span>
                        </strong>
                    </div>
                </div>
            </section>
        </div>

        <div class="variant-right-column">
            <section class="card variant-details-card">
                <div class="variant-card-header">
                    <h2 class="variant-section-title">VARIANT DETAILS</h2>
                    <span class="status <?php echo e($variant->status === 'active' ? 'status-active' : 'status-danger'); ?>">
                        <?php echo e(ucfirst($variant->status ?? 'active')); ?>

                    </span>
                </div>

                <div class="variant-details-grid">
                    <div class="variant-info-box">
                        <span>VARIANT CODE</span>
                        <strong class="variant-code-badge"><?php echo e($variant->variant_code); ?></strong>
                    </div>
                    <div class="variant-info-box">
                        <span>PART NUMBER</span>
                        <strong><?php echo e($variant->part_number ?: '-'); ?></strong>
                    </div>
                    <div class="variant-info-box">
                        <span>CATEGORY</span>
                        <strong><?php echo e($product?->category ?? 'Uncategorized'); ?></strong>
                    </div>
                    <div class="variant-info-box">
                        <span>BRAND</span>
                        <strong><?php echo e($variant->brand?->name ?? '-'); ?></strong>
                    </div>
                    <div class="variant-info-box">
                        <span>SIZE</span>
                        <strong><?php echo e($variant->size ?: '-'); ?></strong>
                    </div>
                    <div class="variant-info-box">
                        <span>COLOR</span>
                        <strong><?php echo e($variant->color ?: '-'); ?></strong>
                    </div>
                    <div class="variant-info-box">
                        <span>UNIT</span>
                        <strong><?php echo e($variant->unit?->code ?? '-'); ?></strong>
                    </div>
                    <div class="variant-info-box">
                        <span>BARCODE</span>
                        <strong class="variant-mono"><?php echo e($variant->barcode ?: '-'); ?></strong>
                    </div>
                </div>
            </section>

            <section class="card variant-supplier-card">
                <h2 class="variant-section-title">SUPPLIER</h2>
                <div class="variant-supplier-row">
                    <div>
                        <div class="variant-supplier-name">
                            <?php echo e($variant->supplier?->name ?? 'None'); ?>

                        </div>
                        <?php if($variant->supplier?->phone): ?>
                            <div class="variant-supplier-phone"><?php echo e($variant->supplier->phone); ?></div>
                        <?php endif; ?>
                    </div>
                    <?php if($variant->supplier): ?>
                    <a href="<?php echo e(route('suppliers.show', $variant->supplier)); ?>" class="btn btn-secondary btn-sm">View Supplier</a>
                    <?php endif; ?>
                </div>
            </section>

            <section class="card variant-pricing-card">
                <h2 class="variant-section-title">PRICING</h2>
                <div class="variant-pricing-grid">
                    <div class="variant-price-box">
                        <span>SELLING PRICE</span>
                        <strong class="variant-selling-price">
                            ₱<?php echo e(number_format((float) $variant->selling_price, 2)); ?>

                        </strong>
                        <small>Customer sale price</small>
                    </div>
                    <div class="variant-price-box">
                        <span>COST PRICE</span>
                        <strong>₱<?php echo e(number_format((float) $variant->cost_price, 2)); ?></strong>
                        <small>Inventory valuation cost</small>
                    </div>
                </div>
            </section>

            <section class="card variant-threshold-card">
                <h2 class="variant-section-title">STOCK THRESHOLDS</h2>
                <div class="variant-threshold-grid">
                    <div>
                        <span>QUANTITY ON HAND</span>
                        <strong><?php echo e(number_format($quantity)); ?></strong>
                    </div>
                    <div>
                        <span>REORDER LEVEL</span>
                        <strong><?php echo e(number_format($minimum)); ?></strong>
                    </div>
                    <div>
                        <span>CRITICAL LEVEL</span>
                        <strong><?php echo e(number_format($critical)); ?></strong>
                    </div>
                    <div>
                        <span>BARCODE</span>
                        <strong class="variant-mono"><?php echo e($variant->barcode ?: '-'); ?></strong>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<?php echo app('Illuminate\Foundation\Vite')('resources/css/views/inventory-index-1.css'); ?>
<?php echo app('Illuminate\Foundation\Vite')('resources/css/views/inventory-show.css'); ?>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\acrylink_newbie\resources\views\inventory\show.blade.php ENDPATH**/ ?>