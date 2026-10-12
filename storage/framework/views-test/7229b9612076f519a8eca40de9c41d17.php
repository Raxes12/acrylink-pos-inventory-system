<?php $__env->startSection('title', $supplier->name); ?>
<?php $__env->startSection('page-label', 'Suppliers / '.$supplier->name); ?>

<?php $__env->startSection('content'); ?>
<div class="supplier-show-page supplier-show-image2">
    <a href="<?php echo e(route('suppliers.index')); ?>" class="editor-breadcrumb">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
             stroke-linecap="round" stroke-linejoin="round">
            <path d="M19 12H5"/><path d="m12 19-7-7 7-7"/>
        </svg>
        Back to Suppliers
    </a>

    <div class="supplier-show-layout">
        <section class="card supplier-profile-card">
            <div class="supplier-profile-top">
                <h1><?php echo e($supplier->name); ?></h1>
                <div class="supplier-code"><?php echo e($supplier->code ?? 'SUP-'.str_pad($supplier->id, 3, '0', STR_PAD_LEFT)); ?></div>
                <span class="status status-<?php echo e($supplier->status); ?>"><?php echo e(ucfirst($supplier->status)); ?></span>
            </div>

            <h2 class="supplier-details-title">Supplier details</h2>
            <dl class="supplier-profile-details">
                <div><dt>Contact</dt><dd><?php echo e($supplier->contact ?: 'Not provided'); ?></dd></div>
                <div><dt>Email</dt><dd><?php if($supplier->email): ?><a href="mailto:<?php echo e($supplier->email); ?>"><?php echo e($supplier->email); ?></a><?php else: ?> Not provided <?php endif; ?></dd></div>
                <div><dt>Phone</dt><dd><?php if($supplier->phone): ?><a href="tel:<?php echo e($supplier->phone); ?>"><?php echo e($supplier->phone); ?></a><?php else: ?> Not provided <?php endif; ?></dd></div>
                <div><dt>Mobile</dt><dd><?php echo e($supplier->mobile ?: 'Not provided'); ?></dd></div>
                <div><dt>City</dt><dd><?php echo e($supplier->city ?: 'Not provided'); ?></dd></div>
                <div><dt>Province</dt><dd><?php echo e($supplier->province ?: 'Not provided'); ?></dd></div>
                <div><dt>Credit Limit</dt><dd>₱<?php echo e(number_format((float)($supplier->credit_limit ?? 0), 2)); ?></dd></div>
                <div><dt>Payment Terms</dt><dd><?php echo e($supplier->payment_terms !== null && $supplier->payment_terms !== '' ? $supplier->payment_terms.' days' : 'Not provided'); ?></dd></div>
            </dl>

            <?php if(auth()->user()->canEdit()): ?>
            <div class="supplier-profile-actions">
                <a href="<?php echo e(route('suppliers.edit', $supplier)); ?>" class="btn btn-primary">
                    <i class="bi bi-pencil" aria-hidden="true"></i> Edit
                </a>
            </div>
            <?php endif; ?>
        </section>

        <section class="card supplier-products-card">
            <div class="supplier-products-header">
                <h2>Supplied products</h2>
            </div>

            <?php if($variants->isEmpty()): ?>
            <div class="supplier-products-empty">
                <?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'inbox']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'inbox']); ?>
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
                <strong>No linked products</strong>
                <span>Products assigned to this supplier will appear here.</span>
            </div>
            <?php else: ?>
            <div class="table-wrap supplier-products-table">
                <table>
                    <thead>
                        <tr>
                            <th>PRODUCT</th>
                            <th>CODE</th>
                            <th>BRAND</th>
                            <th>STOCK</th>
                            <th>SELLING PRICE</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $variants; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $variant): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php $quantity = (int)($variant->inventoryStock?->quantity_on_hand ?? 0); ?>
                        <tr>
                            <td><a class="supplier-product-name" href="<?php echo e(route('inventory.show', $variant)); ?>"><?php echo e($variant->product?->name ?? 'Deleted product'); ?></a></td>
                            <td><a class="supplier-code-link" href="<?php echo e(route('inventory.show', $variant)); ?>"><?php echo e($variant->variant_code); ?></a></td>
                            <td><?php echo e($variant->brand?->name ?? '-'); ?></td>
                            <td><span class="supplier-stock <?php echo e($quantity <= 0 ? 'is-empty' : ''); ?>"><?php echo e(number_format($quantity)); ?></span></td>
                            <td class="supplier-price">&#8369;<?php echo e(number_format((float)$variant->selling_price, 2)); ?></td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>

            <?php if($variants->hasPages()): ?>
            <div class="supplier-show-pagination">
                <span>Showing <?php echo e($variants->firstItem()); ?>-<?php echo e($variants->lastItem()); ?> of <?php echo e($variants->total()); ?></span>
                <?php echo e($variants->links()); ?>

            </div>
            <?php endif; ?>
            <?php endif; ?>
        </section>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<?php echo app('Illuminate\Foundation\Vite')('resources/css/views/suppliers-show-1.css'); ?>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\acrylink_newbie\resources\views\suppliers\show.blade.php ENDPATH**/ ?>