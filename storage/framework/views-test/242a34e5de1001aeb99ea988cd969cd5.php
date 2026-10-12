<?php $__env->startSection('title', 'Stock Receipts'); ?>
<?php $__env->startSection('page-label', 'Inventory / Receipts'); ?>

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
        <h1 class="page-heading">Stock Receipts</h1>
        <p class="page-sub">Receive purchased stock into variant-level inventory.</p>
    </div>
    <div class="toolbar-actions">
        <a href="<?php echo e(route('stock-receipts.create')); ?>" class="btn btn-primary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
            New Receipt
        </a>
        <a href="<?php echo e(route('inventory.stock-overview')); ?>" class="btn btn-secondary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5"/><path d="m12 19-7-7 7-7"/></svg>
            Stock Overview
        </a>
    </div>
</div>

<div class="card stock-card">
    <div class="table-wrap">
        <table class="stock-table">
            <thead>
                <tr>
                    <th>Receipt No.</th>
                    <th>Date Received</th>
                    <th>Supplier</th>
                    <th>Items</th>
                    <th class="text-right">Total Cost</th>
                    <th>Received By</th>
                    <th>Created</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $receipts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $receipt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr>
                <td><a href="<?php echo e(route('stock-receipts.show', $receipt)); ?>" class="stock-code"><?php echo e($receipt->receipt_no); ?></a></td>
                <td><?php echo e($receipt->date_received?->format('M j, Y')); ?></td>
                <td class="stock-muted"><?php echo e($receipt->supplier?->name ?? 'No supplier'); ?></td>
                <td><?php echo e($receipt->items_count); ?></td>
                <td class="text-right fw-800" style="color:var(--accent2);">&#8369;<?php echo e(number_format((float) ($receipt->items_sum_total_cost ?? 0), 2)); ?></td>
                <td><?php echo e($receipt->receivedBy?->name ?? 'System Administrator'); ?></td>
                <td class="stock-muted"><?php echo e($receipt->created_at?->format('M j, Y H:i')); ?></td>
                <td>
                    <div class="table-actions" style="justify-content:center;">
                        <a href="<?php echo e(route('stock-receipts.show', $receipt)); ?>" class="btn btn-secondary btn-sm" title="View receipt" aria-label="View receipt <?php echo e($receipt->receipt_no); ?>">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z"/><circle cx="12" cy="12" r="3"/></svg>
                        </a>
                    </div>
                </td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <?php if (isset($component)) { $__componentOriginal074a021b9d42f490272b5eefda63257c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal074a021b9d42f490272b5eefda63257c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.empty-state','data' => ['colspan' => '8','title' => 'No stock receipts yet','message' => 'Record a receipt when purchased inventory arrives.','icon' => 'receipts']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['colspan' => '8','title' => 'No stock receipts yet','message' => 'Record a receipt when purchased inventory arrives.','icon' => 'receipts']); ?>
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
    <div class="stock-pagination"><?php echo e($receipts->links()); ?></div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<?php echo app('Illuminate\Foundation\Vite')('resources/css/views/inventory-index-1.css'); ?>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\acrylink_newbie\resources\views/stock_receipts/index.blade.php ENDPATH**/ ?>