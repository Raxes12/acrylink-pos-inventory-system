<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['user']));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['user']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>
<?php
    $initials = collect(preg_split('/\s+/', trim($user->name ?: $user->username)) ?: [])
        ->filter()->take(2)->map(fn ($part) => strtoupper(substr($part, 0, 1)))->implode('');
?>
<details class="topbar-profile-menu" data-topbar-menu>
    <summary aria-label="Open account menu for <?php echo e($user->name ?: $user->username); ?>">
        <span class="topbar-profile-avatar" aria-hidden="true"><?php echo e($initials ?: strtoupper(substr($user->username, 0, 2))); ?></span>
    </summary>
    <div class="topbar-profile-panel">
        <header><span class="topbar-profile-avatar" aria-hidden="true"><?php echo e($initials); ?></span><div><strong><?php echo e($user->name ?: $user->username); ?></strong><small><?php echo e($user->username); ?> &middot; <?php echo e($user->roleLabel()); ?></small></div></header>
        <?php if($user->isAdmin()): ?>
        <a href="<?php echo e(route('users.show', $user)); ?>"><i class="bi bi-person" aria-hidden="true"></i><span>My Profile</span></a>
        <?php endif; ?>
        <form method="POST" action="<?php echo e(route('logout')); ?>">
            <?php echo csrf_field(); ?>
            <button type="submit"><?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'logout']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'logout']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?><span>Sign Out</span></button>
        </form>
    </div>
</details>
<?php /**PATH C:\xampp\htdocs\acrylink_newbie\resources\views/components/topbar-profile.blade.php ENDPATH**/ ?>