<?php ($isEdit = isset($supplier)); ?>
<?php $__env->startSection('title', $isEdit ? 'Edit Supplier' : 'Add Supplier'); ?>
<?php $__env->startSection('page-label', 'Suppliers / ' . ($isEdit ? 'Edit Supplier' : 'Add Supplier')); ?>

<?php $__env->startSection('content'); ?>
<div class="editor-page">
    <a href="<?php echo e(route('suppliers.index')); ?>" class="editor-breadcrumb">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5"/><path d="m12 19-7-7 7-7"/></svg>
        Suppliers
    </a>
    <div class="page-toolbar">
        <div>
            <h1 class="page-heading"><?php echo e($isEdit ? 'Edit Supplier' : 'Add Supplier'); ?></h1>
            <p class="page-sub"><?php echo e($isEdit ? 'Update vendor contact and payment details.' : 'Create a new supplier record.'); ?></p>
        </div>
    </div>

    <form method="POST" action="<?php echo e($isEdit ? route('suppliers.update', $supplier) : route('suppliers.store')); ?>" class="card editor-card">
        <?php echo csrf_field(); ?>
        <?php if($isEdit): ?> <?php echo method_field('PUT'); ?> <?php endif; ?>
        <div class="editor-card-head">
            <div>
                <div class="editor-card-title">Supplier information</div>
                <div class="editor-card-note">Store the primary contact and preferred payment method.</div>
            </div>
            <span class="editor-required">* Required</span>
        </div>
        <?php echo $__env->make('suppliers._form', ['s' => $supplier ?? null], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <div class="editor-actions">
            <a href="<?php echo e(route('suppliers.index')); ?>" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary"><?php echo e($isEdit ? 'Update Supplier' : 'Save Supplier'); ?></button>
        </div>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\acrylink_newbie\resources\views\suppliers\form.blade.php ENDPATH**/ ?>