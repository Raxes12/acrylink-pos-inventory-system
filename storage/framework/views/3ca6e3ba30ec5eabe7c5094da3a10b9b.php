<?php ($isEdit = isset($category)); ?>
<?php $__env->startSection('title', $isEdit ? 'Edit Category' : 'Add Category'); ?>
<?php $__env->startSection('page-label', 'Categories / ' . ($isEdit ? 'Edit Category' : 'Add Category')); ?>

<?php $__env->startSection('content'); ?>
<div class="editor-page editor-page--compact category-editor">
    <a href="<?php echo e(route('categories.index')); ?>" class="editor-breadcrumb">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5"/><path d="m12 19-7-7 7-7"/></svg>
        Categories
    </a>
    <div class="page-toolbar">
        <div>
            <h1 class="page-heading"><?php echo e($isEdit ? 'Rename Category' : 'Add Category'); ?></h1>
            <p class="page-sub"><?php echo e($isEdit ? 'Choose a clear name that remains familiar to inventory and sales staff.' : 'Create a clear product group that staff can recognize quickly.'); ?></p>
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
            <div class="category-editor-layout">
            <div class="form-group category-name-field">
                <label for="categoryName">Category Name <span aria-hidden="true">*</span></label>
                <input id="categoryName" type="text" name="name" value="<?php echo e(old('name', $category->name ?? '')); ?>" placeholder="e.g. Transmission" maxlength="50" required autofocus aria-describedby="categoryNameHelp categoryNameCount" data-category-name-input>
                <div class="category-field-help"><span id="categoryNameHelp">Use a short, specific name. Product names and brands belong on the product or variant.</span><span id="categoryNameCount" data-category-name-count>0/50</span></div>
                <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="field-error" role="alert"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
            <aside class="category-name-preview" aria-label="Category card preview"><span>Preview</span><div><strong data-category-name-initials>CA</strong><p data-category-name-preview>Category name</p></div></aside>
            </div>
        </div>
        <div class="editor-actions">
            <a href="<?php echo e(route('categories.index')); ?>" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary"><?php echo e($isEdit ? 'Update Category' : 'Save Category'); ?></button>
        </div>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<?php echo app('Illuminate\Foundation\Vite')('resources/css/views/categories-index-1.css'); ?>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<?php echo app('Illuminate\Foundation\Vite')('resources/js/views/categories-index-1.js'); ?>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\acrylink_newbie1\resources\views\categories\form.blade.php ENDPATH**/ ?>