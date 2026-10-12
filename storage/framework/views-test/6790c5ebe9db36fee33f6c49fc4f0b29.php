<?php ($isEdit = isset($user)); ?>
<?php $__env->startSection('title', $isEdit ? 'Edit User' : 'Add User'); ?>
<?php $__env->startSection('page-label', 'Users / ' . ($isEdit ? 'Edit User' : 'Add User')); ?>

<?php $__env->startSection('content'); ?>
<div class="editor-page">
    <a href="<?php echo e(route('users.index')); ?>" class="editor-breadcrumb">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5"/><path d="m12 19-7-7 7-7"/></svg>
        Users
    </a>
    <div class="page-toolbar">
        <div>
            <h1 class="page-heading"><?php echo e($isEdit ? 'Edit User' : 'Add User'); ?></h1>
            <p class="page-sub"><?php echo e($isEdit ? 'Update staff access and account details.' : 'Create a staff account and choose its access level.'); ?></p>
        </div>
    </div>

    <form method="POST" action="<?php echo e($isEdit ? route('users.update', $user) : route('users.store')); ?>" class="card editor-card">
        <?php echo csrf_field(); ?>
        <?php if($isEdit): ?> <?php echo method_field('PUT'); ?> <?php endif; ?>
        <div class="editor-card-head">
            <div>
                <div class="editor-card-title">Account information</div>
                <div class="editor-card-note">Set the staff identity, access level, and account status.</div>
            </div>
            <span class="editor-required">* Required</span>
        </div>
        <?php echo $__env->make('users._form', ['user' => $user ?? null, 'isAdd' => !$isEdit], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <div class="editor-actions">
            <a href="<?php echo e(route('users.index')); ?>" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary"><?php echo e($isEdit ? 'Save Changes' : 'Create User'); ?></button>
        </div>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\acrylink_newbie\resources\views\users\form.blade.php ENDPATH**/ ?>