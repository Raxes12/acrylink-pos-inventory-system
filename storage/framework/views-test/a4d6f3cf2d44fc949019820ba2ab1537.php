<?php $__env->startSection('title', 'System Settings'); ?>
<?php $__env->startSection('page-label', 'System Settings'); ?>

<?php $__env->startSection('content'); ?>
<?php if (isset($component)) { $__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => 'System Settings','subtitle' => 'Manage the client business details and shared Acry-Link preferences.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'System Settings','subtitle' => 'Manage the client business details and shared Acry-Link preferences.']); ?>
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

<?php
    $sections = [
        'general' => ['General', 'dashboard'],
        'inventory' => ['Inventory', 'stock'],
        'sales' => ['Sales & POS', 'pos'],
        'receipt' => ['Receipt', 'receipts'],
        'security' => ['Security', 'shield'],
        'maintenance' => ['Backup & Maintenance', 'audit'],
    ];
?>
<div class="settings-layout">
    <nav class="settings-nav" aria-label="Settings sections">
        <?php $__currentLoopData = $sections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => [$label, $icon]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <a href="<?php echo e(route('settings.index', ['section' => $key])); ?>" class="settings-nav-link <?php echo e($section === $key ? 'is-active' : ''); ?>" <?php if($section === $key): ?> aria-current="page" <?php endif; ?>><?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => $icon]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($icon)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?><span><?php echo e($label); ?></span></a>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </nav>

    <div class="settings-content">
        <?php if($section === 'general'): ?>
        <form method="POST" action="<?php echo e(route('settings.general.update')); ?>" class="card settings-card"><?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
            <div class="card-header"><div><strong>General Settings</strong><div class="text-muted text-sm">Business identity shared by the application and printed documents.</div></div></div>
            <div class="settings-card-body">
                <div class="settings-heading"><strong>Client Business Information</strong><span>Used on receipts and exported reports. This does not rename the Acry-Link system.</span></div>
                <div class="settings-form-grid">
                    <div class="form-group settings-wide"><label for="business_name">Business Name</label><input id="business_name" name="business_name" maxlength="120" required autocomplete="organization" value="<?php echo e(old('business_name', $settings['business.name'])); ?>" aria-describedby="business_name_help"><span id="business_name_help" class="form-help">Shown as the business heading on customer-facing documents.</span><?php $__errorArgs = ['business_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="field-error"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?></div>
                    <div class="form-group settings-wide"><label for="business_address">Address</label><input id="business_address" name="business_address" maxlength="255" autocomplete="street-address" value="<?php echo e(old('business_address', $settings['business.address'])); ?>"><?php $__errorArgs = ['business_address'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="field-error"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?></div>
                    <div class="form-group"><label for="business_contact_number">Contact Number</label><input id="business_contact_number" name="business_contact_number" maxlength="40" autocomplete="tel" value="<?php echo e(old('business_contact_number', $settings['business.contact_number'])); ?>"><?php $__errorArgs = ['business_contact_number'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="field-error"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?></div>
                    <div class="form-group"><label for="business_email">Email</label><input id="business_email" name="business_email" type="email" maxlength="120" autocomplete="email" value="<?php echo e(old('business_email', $settings['business.email'])); ?>"><?php $__errorArgs = ['business_email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="field-error"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?></div>
                </div>
                <div class="settings-heading"><strong>Regional Settings</strong><span>Current system-wide values are fixed to avoid inconsistent transaction formatting.</span></div>
                <dl class="settings-readonly"><div><dt>Currency</dt><dd>Philippine Peso (&#8369;)</dd></div><div><dt>Timezone</dt><dd>Asia/Manila</dd></div><div><dt>Date format</dt><dd>MMM DD, YYYY</dd></div></dl>
            </div>
            <div class="editor-actions"><button class="btn btn-primary" type="submit">Save Changes</button></div>
        </form>
        <?php elseif($section === 'inventory'): ?>
        <section class="card settings-card">
            <div class="card-header"><div><strong>Inventory Settings</strong><div class="text-muted text-sm">Manage shared product reference data.</div></div></div>
            <div class="settings-card-body">
                <a class="settings-resource" href="<?php echo e(route('categories.index')); ?>"><span class="settings-resource-icon"><?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'categories']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'categories']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?></span><span><strong>Product Categories</strong><small><?php echo e(number_format($categoryCount)); ?> categories &middot; open the existing category workspace</small></span><i class="bi bi-chevron-right" aria-hidden="true"></i></a>
                <div class="settings-heading"><strong>Units of Measurement</strong><span>Codes are used by products and variants. Units in use cannot be deleted.</span></div>
                <form method="POST" action="<?php echo e(route('settings.units.store')); ?>" class="settings-unit-add"><?php echo csrf_field(); ?><input name="code" maxlength="20" required placeholder="Code (e.g. PCS)" aria-label="Unit code"><input name="name" maxlength="50" required placeholder="Display name" aria-label="Unit name"><button class="btn btn-primary" type="submit">Add Unit</button></form>
                <div class="settings-unit-list">
                    <?php $__currentLoopData = $units; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $unit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="settings-unit-row">
                        <form method="POST" action="<?php echo e(route('settings.units.update', $unit)); ?>"><?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?><input name="code" maxlength="20" required value="<?php echo e($unit->code); ?>" aria-label="Unit code"><input name="name" maxlength="50" required value="<?php echo e($unit->name); ?>" aria-label="Unit name"><button class="btn btn-secondary btn-sm" type="submit">Save</button></form>
                        <form method="POST" action="<?php echo e(route('settings.units.destroy', $unit)); ?>" data-confirm data-confirm-title="Delete unit?" data-confirm-message="Delete <?php echo e($unit->code); ?> if it is not used by any product?"><?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?><button class="btn btn-danger btn-sm" type="submit">Delete</button></form>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        </section>
        <?php elseif($section === 'receipt'): ?>
        <form method="POST" action="<?php echo e(route('settings.receipt.update')); ?>" class="card settings-card"><?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
            <div class="card-header"><div><strong>Receipt Settings</strong><div class="text-muted text-sm">Control presentation fields used by new and existing receipt views.</div></div></div>
            <div class="settings-card-body">
                <div class="form-group"><label for="footer_message">Receipt Footer Message</label><textarea id="footer_message" name="footer_message" maxlength="240" required><?php echo e(old('footer_message', $settings['receipt.footer_message'])); ?></textarea><?php $__errorArgs = ['footer_message'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="field-error"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?></div>
                <?php $__currentLoopData = ['show_cashier' => ['Show cashier name', 'Identify the staff member who processed the sale.'], 'show_customer' => ['Show customer name', 'Show the customer on receipts when a customer is attached.'], 'show_payment_method' => ['Show payment method', 'Include cash, GCash, check, credit, or card information.']]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field => [$label, $help]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <label class="editor-check"><input type="checkbox" name="<?php echo e($field); ?>" value="1" <?php if(old($field, $settings['receipt.'.$field])): echo 'checked'; endif; ?>><span><strong><?php echo e($label); ?></strong><small><?php echo e($help); ?></small></span></label>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
            <div class="editor-actions"><button class="btn btn-primary" type="submit">Save Receipt Settings</button></div>
        </form>
        <?php elseif($section === 'security'): ?>
        <section class="card settings-card"><div class="card-header"><div><strong>Security Configuration</strong><div class="text-muted text-sm">Operational controls are managed by the existing security engine.</div></div></div><div class="settings-card-body"><div class="settings-notice"><?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'shield']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'shield']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?><div><strong>No duplicated security switches</strong><p>Login detection thresholds and response actions are already enforced by Security Rules. Manage those live controls in the Security Center.</p><a class="btn btn-secondary btn-sm" href="<?php echo e(route('security.rules')); ?>">Manage Security Rules</a></div></div></div></section>
        <?php elseif($section === 'sales'): ?>
        <section class="card settings-card"><div class="card-header"><div><strong>Sales &amp; POS</strong><div class="text-muted text-sm">Current transaction behavior remains protected by the established POS workflow.</div></div></div><div class="settings-card-body"><div class="settings-notice"><?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'pos']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'pos']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?><div><strong>No safe global overrides are needed</strong><p>Payment availability, credit validation, receipt sequences, refunds, and checkout rules are already enforced by database-backed services. They have not been duplicated here.</p></div></div></div></section>
        <?php else: ?>
        <section class="card settings-card"><div class="card-header"><div><strong>Backup &amp; Maintenance</strong><div class="text-muted text-sm">Read-only environment information without exposing credentials or destructive actions.</div></div></div><div class="settings-card-body"><dl class="settings-readonly"><div><dt>Application</dt><dd><?php echo e(config('app.name', 'Acry-Link')); ?></dd></div><div><dt>Laravel</dt><dd><?php echo e(app()->version()); ?></dd></div><div><dt>PHP</dt><dd><?php echo e(PHP_VERSION); ?></dd></div><div><dt>Database driver</dt><dd><?php echo e(strtoupper($databaseDriver)); ?></dd></div><div><dt>Environment</dt><dd><?php echo e(app()->environment()); ?></dd></div></dl><div class="settings-notice"><?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'audit']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'audit']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?><div><strong>Backup actions are not exposed</strong><p>Automated database backups require deployment-specific storage, retention, and encryption. Adding a generic download button could expose customer and security data.</p></div></div></div></section>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<?php echo app('Illuminate\Foundation\Vite')('resources/css/views/settings-1.css'); ?>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\acrylink_newbie\resources\views\settings\index.blade.php ENDPATH**/ ?>