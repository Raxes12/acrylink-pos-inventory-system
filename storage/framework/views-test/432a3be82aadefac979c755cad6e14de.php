<?php ($isEdit = isset($category)); ?>
<?php $__env->startSection('title', $isEdit ? 'Edit Category' : 'Add Category'); ?>
<?php $__env->startSection('page-label', 'Categories / ' . ($isEdit ? 'Edit Category' : 'Add Category')); ?>

<?php $__env->startSection('content'); ?>
<div class="editor-page editor-page--compact">
    <a href="<?php echo e(route('categories.index')); ?>" class="editor-breadcrumb">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5"/><path d="m12 19-7-7 7-7"/></svg>
        Categories
    </a>
    <div class="page-toolbar">
        <div>
            <h1 class="page-heading"><?php echo e($isEdit ? 'Rename Category' : 'Add Category'); ?></h1>
            <p class="page-sub"><?php echo e($isEdit ? 'Update the category name.' : 'Create a category for grouping products.'); ?></p>
        </div>
    </div>

    <form method="POST" action="<?php echo e($isEdit ? route('categories.update', $category) : route('categories.store')); ?>" class="card editor-card">
        <?php echo csrf_field(); ?>
        <?php if($isEdit): ?> <?php echo method_field('PUT'); ?> <?php endif; ?>
        <div class="editor-card-head">
            <div>
                <div class="editor-card-title">Category information</div>
                <div class="editor-card-note">Use a short, recognizable label for product grouping.</div>
            </div>
            <span class="editor-required">* Required</span>
        </div>
        <div class="editor-section">
            <div class="form-group">
                <label>Category Name *</label>
                <input type="text" name="name" value="<?php echo e(old('name', $category->name ?? '')); ?>" placeholder="e.g. Transmission" maxlength="50" required autofocus>
            </div>
        </div>
        <div class="editor-actions">
            <a href="<?php echo e(route('categories.index')); ?>" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary"><?php echo e($isEdit ? 'Update Category' : 'Save Category'); ?></button>
        </div>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\acrylink_newbie\resources\views\categories\form.blade.php ENDPATH**/ ?>