<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['page' => 'Dashboard', 'title' => null]));

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

foreach (array_filter((['page' => 'Dashboard', 'title' => null]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>
<?php
    $routeName = request()->route()?->getName() ?? '';
    $section = match (true) {
        $routeName === 'dashboard' => 'Overview',
        str_starts_with($routeName, 'inventory.'), str_starts_with($routeName, 'stock-receipts.') => 'Inventory / Stock Control',
        str_starts_with($routeName, 'categories.'), str_starts_with($routeName, 'suppliers.') => 'Inventory',
        str_starts_with($routeName, 'pos.'), str_starts_with($routeName, 'dashboard.my-transactions') => 'Sales & Transactions',
        str_starts_with($routeName, 'accounts-receivable.'), str_starts_with($routeName, 'customers.') => 'Customers & Credit',
        str_starts_with($routeName, 'reports.') => 'Reports & Analytics',
        str_starts_with($routeName, 'security.') => 'Security & Monitoring',
        str_starts_with($routeName, 'users.'), str_starts_with($routeName, 'audit.') => 'Administration',
        default => auth()->user()?->roleLabel().' Workspace',
    };
    $segments = collect(preg_split('/\s*\/\s*/', trim($page)) ?: [])->filter()->values();
    $currentPage = trim($title ?: ($segments->last() ?: $page));
    $detailSegments = $segments->count() > 1
        ? $segments->reject(fn ($segment) => strcasecmp($segment, $currentPage) === 0)->values()
        : collect();
?>
<div class="topbar-context">
    <nav class="topbar-breadcrumb" aria-label="Current location">
        <span><?php echo e($section); ?></span>
        <?php $__currentLoopData = $detailSegments->take(2); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $segment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <i class="bi bi-chevron-right" aria-hidden="true"></i><span><?php echo e($segment); ?></span>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </nav>
    <span class="page-label"><?php echo e($currentPage); ?></span>
</div>
<?php /**PATH C:\xampp\htdocs\acrylink_newbie\resources\views/components/topbar-context.blade.php ENDPATH**/ ?>