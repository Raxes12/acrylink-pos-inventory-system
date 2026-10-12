
<?php ($isEdit = isset($customer)); ?>
<?php $__env->startSection('title', $isEdit ? 'Edit Customer' : 'Add Customer'); ?>
<?php $__env->startSection('page-label', 'Customers / ' . ($isEdit ? 'Edit' : 'Add')); ?>

<?php $__env->startSection('content'); ?>
<div class="editor-page customer-form-page">
    <a href="<?php echo e(route('customers.index')); ?>" class="editor-breadcrumb">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
            stroke-linecap="round" stroke-linejoin="round">
            <path d="M19 12H5"/><path d="m12 19-7-7 7-7"/>
        </svg>
        Back to Customers
    </a>
    <?php if (isset($component)) { $__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => $isEdit ? 'Edit Customer' : 'Add Customer','subtitle' => $isEdit ? $customer->customer_code : 'Create a customer and set their account preferences.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($isEdit ? 'Edit Customer' : 'Add Customer'),'subtitle' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($isEdit ? $customer->customer_code : 'Create a customer and set their account preferences.')]); ?>
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
    <form method="POST" action="<?php echo e($isEdit ? route('customers.update', $customer) : route('customers.store')); ?>" class="card editor-card">
        <?php echo csrf_field(); ?>
        <?php if($isEdit): ?> <?php echo method_field('PUT'); ?> <?php endif; ?>
        <div class="editor-card-head"><div class="editor-card-title">Customer information</div><span class="editor-required">* Required</span></div>
        <div class="editor-body">
            <section class="editor-section">
                <div class="editor-section-head"><div class="editor-section-title">Contact details</div></div>
                <div class="form-group">
                    <label for="customer_name">Customer name *</label>
                    <input id="customer_name" name="customer_name" value="<?php echo e(old('customer_name', $customer->customer_name ?? '')); ?>" maxlength="150" required autofocus>
                </div>
                <div class="grid-2">
                    <div class="form-group"><label for="phone">Phone</label><input id="phone" type="tel" name="phone" maxlength="30" autocomplete="tel" value="<?php echo e(old('phone', $customer->phone ?? '')); ?>"></div>
                    <div class="form-group"><label for="email">Email</label><input id="email" type="email" name="email" maxlength="255" autocomplete="email" value="<?php echo e(old('email', $customer->email ?? '')); ?>"></div>
                </div>
                <div class="form-group"><label for="address">Address</label><textarea id="address" name="address" rows="3" maxlength="2000"><?php echo e(old('address', $customer->address ?? '')); ?></textarea></div>
            </section>
            <section class="editor-section">
                <div class="editor-section-head"><div class="editor-section-title">Account and credit settings</div><p class="editor-section-copy">Credit changes are recorded in the audit log.</p></div>
                <div class="grid-2">
                    <div class="form-group">
                        <label for="status">Status *</label>
                        <select id="status" name="status" required><option value="active" <?php if(old('status', $customer->status ?? 'active') === 'active'): echo 'selected'; endif; ?>>Active</option><option value="inactive" <?php if(old('status', $customer->status ?? 'active') === 'inactive'): echo 'selected'; endif; ?>>Inactive</option></select>
                    </div>
                    <div class="form-group">
                        <label for="credit_enabled">Customer credit *</label>
                        <select id="credit_enabled" name="credit_enabled" required><option value="0" <?php if(!old('credit_enabled', $customer->credit_enabled ?? false)): echo 'selected'; endif; ?>>Disabled</option><option value="1" <?php if(old('credit_enabled', $customer->credit_enabled ?? false)): echo 'selected'; endif; ?>>Enabled</option></select>
                    </div>
                    <div class="form-group"><label for="credit_limit">Credit limit (PHP) *</label><input id="credit_limit" type="number" name="credit_limit" min="0" max="9999999999.99" step="0.01" value="<?php echo e(old('credit_limit', $customer->credit_limit ?? '0.00')); ?>" required><p class="form-help">Maximum unpaid balance allowed for this customer.</p></div>
                    <div class="form-group"><label for="default_credit_term_days">Default credit term (days) *</label><input id="default_credit_term_days" type="number" name="default_credit_term_days" min="1" max="65535" step="1" value="<?php echo e(old('default_credit_term_days', $customer->default_credit_term_days ?? 30)); ?>" required><p class="form-help">Used to calculate the due date for new credit sales.</p></div>
                </div>
            </section>
        </div>
        <div class="editor-actions"><a href="<?php echo e(route('customers.index')); ?>" class="btn btn-secondary">Cancel</a><button type="submit" class="btn btn-primary"><?php echo e($isEdit ? 'Save Changes' : 'Create Customer'); ?></button></div>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<?php echo app('Illuminate\Foundation\Vite')('resources/css/views/customers-1.css'); ?>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\acrylink_newbie\resources\views\customers\form.blade.php ENDPATH**/ ?>