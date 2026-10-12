<?php $__env->startSection('title', 'Stock Receipt '.$receipt->receipt_no); ?>
<?php $__env->startSection('page-label', 'Inventory / Receipt Details'); ?>

<?php
    $totalCost = $receipt->items->sum(fn ($item) => (float) $item->total_cost);
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
        <a href="<?php echo e(route('stock-receipts.index')); ?>" class="editor-breadcrumb" style="margin-bottom:10px;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5"/><path d="m12 19-7-7 7-7"/></svg>
            Stock Receipts
        </a>
        <h1 class="page-heading"><?php echo e($receipt->receipt_no); ?></h1>
        <p class="page-sub">Received <?php echo e($receipt->date_received?->format('M j, Y')); ?> by <?php echo e($receipt->receivedBy?->name ?? 'System Administrator'); ?>.</p>
    </div>
    <div class="toolbar-actions">
        <a href="<?php echo e(route('stock-receipts.create')); ?>" class="btn btn-primary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
            New Receipt
        </a>
        <a href="<?php echo e(route('inventory.movements', ['type' => 'stock_in'])); ?>" class="btn btn-secondary">View Movements</a>
    </div>
</div>

<section class="card receipt-summary-card">
    <div class="receipt-summary-grid">
        <div>
            <div class="stock-product-sub">Supplier</div>
            <div class="stock-product-name"><?php echo e($receipt->supplier?->name ?? 'No supplier'); ?></div>
        </div>
        <div>
            <div class="stock-product-sub">Total Cost</div>
            <div class="stock-onhand">&#8369;<?php echo e(number_format($totalCost, 2)); ?></div>
        </div>
    </div>
    <?php if($receipt->remarks): ?>
    <div class="receipt-summary-remarks">
        <div class="stock-product-sub">Remarks</div>
        <div><?php echo e($receipt->remarks); ?></div>
    </div>
    <?php endif; ?>
</section>

<div class="card stock-card">
    <div class="table-wrap">
        <table class="stock-table">
            <thead>
                <tr>
                    <th>Product / Variant</th>
                    <th>Part No.</th>
                    <th>Brand</th>
                    <th>Size / Color</th>
                    <th>Unit</th>
                    <th class="text-right">Qty Received</th>
                    <th class="text-right">Unit Cost</th>
                    <th class="text-right">Total</th>
                    <th class="text-right">Current Stock</th>
                </tr>
            </thead>
            <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $receipt->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <?php
                $variant = $item->productVariant;
                $product = $variant?->product;
                $currentStock = (int) ($variant?->inventoryStock?->quantity_on_hand ?? 0);
            ?>
            <tr>
                <td>
                    <?php if($variant): ?>
                    <a href="<?php echo e(route('inventory.show', $variant)); ?>" class="stock-product-name"><?php echo e($product?->name ?? 'Deleted product'); ?></a>
                    <?php else: ?>
                    <div class="stock-product-name">Deleted product</div>
                    <?php endif; ?>
                    <div class="stock-product-sub"><?php echo e($variant?->variant_code ?? 'Deleted variant'); ?></div>
                </td>
                <td class="stock-muted"><?php echo e($variant?->part_number ?: '-'); ?></td>
                <td><?php echo e($variant?->brand?->name ?? '-'); ?></td>
                <td class="stock-muted"><?php echo e(collect([$variant?->size, $variant?->color])->filter()->join(' / ') ?: '-'); ?></td>
                <td><?php echo e($variant?->unit?->code ?? '-'); ?></td>
                <td class="text-right stock-onhand"><?php echo e($item->quantity_received); ?></td>
                <td class="text-right">&#8369;<?php echo e(number_format((float) $item->unit_cost, 2)); ?></td>
                <td class="text-right fw-800">&#8369;<?php echo e(number_format((float) $item->total_cost, 2)); ?></td>
                <td class="text-right stock-muted"><?php echo e($currentStock); ?></td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <?php if (isset($component)) { $__componentOriginal074a021b9d42f490272b5eefda63257c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal074a021b9d42f490272b5eefda63257c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.empty-state','data' => ['colspan' => '9','title' => 'No receipt items','message' => 'This stock receipt does not contain any product variants.','icon' => 'inbox']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['colspan' => '9','title' => 'No receipt items','message' => 'This stock receipt does not contain any product variants.','icon' => 'inbox']); ?>
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
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<?php echo app('Illuminate\Foundation\Vite')('resources/css/views/inventory-index-1.css'); ?>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\acrylink_newbie\resources\views/stock_receipts/show.blade.php ENDPATH**/ ?>