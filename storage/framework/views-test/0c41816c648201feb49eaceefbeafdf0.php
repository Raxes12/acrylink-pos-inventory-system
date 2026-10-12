<?php ($isEdit = isset($variant)); ?>
<?php $__env->startSection('title', $isEdit ? 'Edit Variant' : 'Add Variant'); ?>
<?php $__env->startSection('page-label', 'Inventory / ' . ($isEdit ? 'Edit Variant' : 'Add Variant')); ?>

<?php $__env->startSection('content'); ?>
<div class="editor-page inventory-form-page">
    <a href="<?php echo e(route('inventory.index')); ?>" class="editor-breadcrumb">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5"/><path d="m12 19-7-7 7-7"/></svg>
        Products
    </a>
    <div class="page-toolbar">
        <div>
            <h1 class="page-heading"><?php echo e($isEdit ? 'Edit Variant' : 'Add Variant'); ?></h1>
            <p class="page-sub"><?php echo e($isEdit ? 'Update product identity, variant details, and stock thresholds.' : 'Create a product variant with its own pricing and stock.'); ?></p>
        </div>
    </div>

    <form method="POST" action="<?php echo e($isEdit ? route('inventory.update', $variant) : route('inventory.store')); ?>" class="card editor-card inventory-form-card" enctype="multipart/form-data">
        <?php echo csrf_field(); ?>
        <?php if($isEdit): ?> <?php echo method_field('PUT'); ?> <?php endif; ?>
        <div class="editor-card-head">
            <div>
                <div class="editor-card-title">Variant information</div>
                <div class="editor-card-note">Complete the three sections below. New variants are active and ready for inventory and POS use.</div>
            </div>
            <div class="inventory-form-head-meta"><span class="inventory-active-default"><i class="bi bi-check-circle" aria-hidden="true"></i><?php echo e($isEdit ? 'Existing variant' : 'Active by default'); ?></span><span class="editor-required">* Required</span></div>
        </div>
        <?php echo $__env->make('inventory._form', ['variant' => $variant ?? null], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <div class="editor-actions inventory-form-actions">
            <span><i class="bi bi-shield-check" aria-hidden="true"></i>Changes are validated before saving.</span>
            <a href="<?php echo e(route('inventory.index')); ?>" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary"><i class="bi bi-check2" aria-hidden="true"></i><?php echo e($isEdit ? 'Update Variant' : 'Save Variant'); ?></button>
        </div>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<?php echo app('Illuminate\Foundation\Vite')('resources/css/views/inventory-index-1.css'); ?>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<?php echo app('Illuminate\Foundation\Vite')('resources/js/views/inventory-form-1.js'); ?>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\acrylink_newbie\resources\views\inventory\form.blade.php ENDPATH**/ ?>