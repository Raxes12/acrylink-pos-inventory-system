<?php $__env->startSection('title', 'Suppliers'); ?>
<?php $__env->startSection('page-label', 'Suppliers'); ?>

<?php $__env->startSection('content'); ?>
<div class="suppliers-page">
<?php if (isset($component)) { $__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => 'Suppliers','subtitle' => 'Manage vendor contacts, payment preferences, and product coverage.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Suppliers','subtitle' => 'Manage vendor contacts, payment preferences, and product coverage.']); ?>
     <?php $__env->slot('actions', null, []); ?> <a href="<?php echo e(route('suppliers.create')); ?>" class="btn btn-primary" data-open-supplier-create><?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
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
<?php endif; ?> Add Supplier</a> <?php $__env->endSlot(); ?>
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

<form method="GET" action="<?php echo e(route('suppliers.index')); ?>" class="filter-bar suppliers-filter" data-live-filter>
    <div class="supplier-search-wrap"><i class="bi bi-search" aria-hidden="true"></i><input type="search" name="search" value="<?php echo e(request('search')); ?>" autocomplete="off" maxlength="150" placeholder="Search company, contact, phone, or email..." aria-label="Search suppliers"></div>
    <select name="status" aria-label="Supplier status"><option value="">All statuses</option><option value="active" <?php if(request('status') === 'active'): echo 'selected'; endif; ?>>Active</option><option value="inactive" <?php if(request('status') === 'inactive'): echo 'selected'; endif; ?>>Inactive</option></select>
    <select name="payment_method" aria-label="Preferred payment method"><option value="">All payment methods</option><option value="cash" <?php if(request('payment_method') === 'cash'): echo 'selected'; endif; ?>>Cash</option><option value="check" <?php if(request('payment_method') === 'check'): echo 'selected'; endif; ?>>Check</option><option value="credit_account" <?php if(request('payment_method') === 'credit_account'): echo 'selected'; endif; ?>>Credit Sale</option><option value="gcash" <?php if(request('payment_method') === 'gcash'): echo 'selected'; endif; ?>>GCash</option></select>
    <span class="live-filter-status" data-live-filter-status role="status" aria-live="polite"></span>
</form>

<div class="card supplier-table-card" data-live-results>
    <div class="card-header supplier-card-header"><div><span class="card-title">Supplier Directory</span><span class="supplier-card-caption">Vendor contacts and purchasing preferences.</span></div><span class="tag tag--accent"><?php echo e($suppliers->total()); ?> supplier<?php echo e($suppliers->total() === 1 ? '' : 's'); ?></span></div>
    <div class="table-wrap" tabindex="0" role="region" aria-label="Suppliers table">
        <table class="supplier-table">
            <thead><tr><th>Supplier</th><th>Primary Contact</th><th>Payment</th><th>Products</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $suppliers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $supplier): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php
                    $paymentMethod = $supplier->payment_method ?? 'cash';
                ?>
                <tr>
                    <td data-label="Supplier"><div class="supplier-identity"><span class="supplier-avatar" aria-hidden="true"><?php echo e(strtoupper(substr($supplier->name, 0, 1))); ?></span><div class="td-stack"><strong><?php echo e($supplier->name); ?></strong><span><i class="bi bi-geo-alt" aria-hidden="true"></i><?php echo e($supplier->address ?: 'No address provided'); ?></span></div></div></td>
                    <td data-label="Primary contact"><div class="td-stack supplier-contact"><strong><?php echo e($supplier->contact ?: 'No contact person'); ?></strong><span><i class="bi bi-telephone" aria-hidden="true"></i><?php echo e($supplier->phone ?: 'No phone'); ?></span><span><i class="bi bi-envelope" aria-hidden="true"></i><?php echo e($supplier->email ?: 'No email'); ?></span></div></td>
                    <td data-label="Payment"><span class="supplier-payment"><i class="bi bi-wallet2" aria-hidden="true"></i><?php echo e(\App\Models\PaymentMethod::labelFor($paymentMethod)); ?></span></td>
                    <td data-label="Products"><strong class="supplier-product-count"><?php echo e($supplier->products_count); ?></strong><span class="supplier-product-label"> linked</span></td>
                    <td data-label="Status"><span class="status status-<?php echo e($supplier->status); ?>"><?php echo e(ucfirst($supplier->status)); ?></span></td>
                    <td data-label="Actions"><div class="table-actions supplier-actions">
                        <a href="<?php echo e(route('suppliers.show', $supplier)); ?>" class="btn btn-secondary btn-sm" aria-label="View <?php echo e($supplier->name); ?>" title="View supplier"><i class="bi bi-eye" aria-hidden="true"></i></a>
                        <a href="<?php echo e(route('suppliers.edit', $supplier)); ?>" class="btn btn-secondary btn-sm" aria-label="Edit <?php echo e($supplier->name); ?>" title="Edit supplier"><i class="bi bi-pencil" aria-hidden="true"></i></a>
                        <?php if($supplier->products_count > 0): ?>
                        <button type="button" class="btn btn-danger btn-sm" disabled title="Supplier has linked products and cannot be deleted" aria-label="Cannot delete <?php echo e($supplier->name); ?>"><i class="bi bi-trash3" aria-hidden="true"></i></button>
                        <?php else: ?>
                        <form method="POST" action="<?php echo e(route('suppliers.destroy', $supplier)); ?>" data-confirm data-confirm-title="Delete supplier?" data-confirm-message="Delete <?php echo e($supplier->name); ?>? This cannot be undone." data-confirm-button="Delete supplier"><?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?><button type="button" class="btn btn-danger btn-sm" title="Delete supplier" aria-label="Delete <?php echo e($supplier->name); ?>" data-confirm-trigger><i class="bi bi-trash3" aria-hidden="true"></i></button></form>
                        <?php endif; ?>
                    </div></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <?php if (isset($component)) { $__componentOriginal074a021b9d42f490272b5eefda63257c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal074a021b9d42f490272b5eefda63257c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.empty-state','data' => ['colspan' => '6','title' => 'No suppliers found','message' => 'Adjust the search or filters, or add your first supplier.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['colspan' => '6','title' => 'No suppliers found','message' => 'Adjust the search or filters, or add your first supplier.']); ?>
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
    <div class="supplier-table-footer">
        <span>Showing <?php echo e($suppliers->firstItem() ?? 0); ?>-<?php echo e($suppliers->lastItem() ?? 0); ?> of <?php echo e($suppliers->total()); ?> suppliers</span>
        <?php if($suppliers->hasPages()): ?>
        <?php
            $currentPage = $suppliers->currentPage();
            $lastPage = $suppliers->lastPage();
            $supplierPages = collect([1, $currentPage - 1, $currentPage, $currentPage + 1, $lastPage])->filter(fn ($page) => $page >= 1 && $page <= $lastPage)->unique()->sort()->values();
            $previousSupplierPage = null;
        ?>
        <nav class="pagination" aria-label="Supplier pagination">
            <?php if($suppliers->onFirstPage()): ?><span class="disabled" aria-disabled="true">Previous</span><?php else: ?><a href="<?php echo e($suppliers->previousPageUrl()); ?>" rel="prev">Previous</a><?php endif; ?>
            <?php $__currentLoopData = $supplierPages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php if($previousSupplierPage && $page > $previousSupplierPage + 1): ?><span class="disabled" aria-hidden="true">...</span><?php endif; ?>
                <?php if($page === $currentPage): ?><span class="active" aria-current="page"><?php echo e($page); ?></span><?php else: ?><a href="<?php echo e($suppliers->url($page)); ?>"><?php echo e($page); ?></a><?php endif; ?>
                <?php
                    $previousSupplierPage = $page;
                ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php if($suppliers->hasMorePages()): ?><a href="<?php echo e($suppliers->nextPageUrl()); ?>" rel="next">Next</a><?php else: ?><span class="disabled" aria-disabled="true">Next</span><?php endif; ?>
        </nav>
        <?php endif; ?>
    </div>
</div>

<div id="createSupplierModal" class="modal-backdrop supplier-modal-backdrop" style="display:none" role="presentation" aria-hidden="true" data-open-on-error="<?php echo e(old('_supplier_create') === '1' && $errors->any() ? 'true' : 'false'); ?>">
    <div class="modal supplier-create-modal" role="dialog" aria-modal="true" aria-labelledby="createSupplierTitle" aria-describedby="createSupplierDescription">
        <div class="modal-header supplier-modal-header"><div><span class="modal-title" id="createSupplierTitle">Add Supplier</span><span id="createSupplierDescription">Create a vendor profile for purchasing and inventory records.</span></div><button type="button" class="modal-close-btn" data-close-supplier-create aria-label="Close supplier form">&times;</button></div>
        <form method="POST" action="<?php echo e(route('suppliers.store')); ?>"><?php echo csrf_field(); ?><input type="hidden" name="_supplier_create" value="1"><div class="modal-body supplier-modal-body"><?php echo $__env->make('suppliers._form', ['s' => null], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></div><div class="modal-footer supplier-modal-footer"><button type="button" class="btn btn-secondary" data-close-supplier-create>Cancel</button><button type="submit" class="btn btn-primary"><i class="bi bi-building-add" aria-hidden="true"></i> Create Supplier</button></div></form>
    </div>
</div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<?php echo app('Illuminate\Foundation\Vite')('resources/css/views/suppliers-index-1.css'); ?>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<?php echo app('Illuminate\Foundation\Vite')('resources/js/views/suppliers-index-1.js'); ?>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\acrylink_newbie\resources\views/suppliers/index.blade.php ENDPATH**/ ?>