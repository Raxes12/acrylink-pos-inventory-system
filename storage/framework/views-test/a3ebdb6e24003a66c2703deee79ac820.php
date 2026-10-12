<?php $__env->startSection('title', 'Audit Log'); ?>
<?php $__env->startSection('page-label', 'Audit Log'); ?>

<?php $__env->startSection('content'); ?>
<?php if (isset($component)) { $__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => 'Audit Log','subtitle' => 'Trace sign-ins, sales, inventory changes, security activity, and account accountability.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Audit Log','subtitle' => 'Trace sign-ins, sales, inventory changes, security activity, and account accountability.']); ?>
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

<form
    method="GET"
    action="<?php echo e(route('audit.index')); ?>"
    data-results-url="<?php echo e(route('audit.results', [], false)); ?>"
    class="filter-bar audit-filter"
    id="auditLiveFilter"
>
    <label class="audit-search">
        <input type="search" name="search" class="filter-field--grow" value="<?php echo e(request('search')); ?>" placeholder="Search user, activity, IP, or record..." autocomplete="off">
    </label>
    <select name="action" class="filter-field--sm" aria-label="Filter by action">
        <option value="">All Actions</option>
        <?php $__currentLoopData = $actions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <option value="<?php echo e($a); ?>" <?php echo e(request('action') == $a ? 'selected' : ''); ?>><?php echo e(ucwords(str_replace('_', ' ', $a))); ?></option>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </select>
    <select name="module" class="filter-field--md" aria-label="Filter by module">
        <option value="">All Modules</option>
        <?php $__currentLoopData = $modules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $module): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <option value="<?php echo e($module); ?>" <?php echo e(request('module') === $module ? 'selected' : ''); ?>><?php echo e(ucwords(str_replace('_', ' ', $module))); ?></option>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </select>
    <select name="user_id" class="filter-field--md" aria-label="Filter by user">
        <option value="">All Users</option>
        <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <option value="<?php echo e($user->id); ?>" <?php echo e((string) request('user_id') === (string) $user->id ? 'selected' : ''); ?>><?php echo e($user->name ?: $user->username); ?></option>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </select>
    <label class="audit-date"><span>From</span><input type="date" name="date_from" value="<?php echo e(request('date_from')); ?>"></label>
    <label class="audit-date"><span>To</span><input type="date" name="date_to" value="<?php echo e(request('date_to')); ?>"></label>
    <button type="submit" class="btn btn-primary btn-sm">Apply Dates</button>
    <button type="button" class="audit-clear" data-audit-clear <?php echo e(collect(request()->except('page'))->filter()->isNotEmpty() ? '' : 'hidden'); ?>>Reset</button>
    <span class="audit-live-status" data-audit-status role="status" aria-live="polite"></span>
</form>

<?php echo $__env->make('audit._results', ['logs' => $logs], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<?php echo app('Illuminate\Foundation\Vite')('resources/js/views/audit-index-1.js'); ?>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('styles'); ?>
<?php echo app('Illuminate\Foundation\Vite')('resources/css/views/audit-index-1.css'); ?>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\acrylink_newbie\resources\views\audit\index.blade.php ENDPATH**/ ?>