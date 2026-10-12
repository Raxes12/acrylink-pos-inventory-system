<?php $__env->startSection('title', 'Audit Log'); ?>
<?php $__env->startSection('page-label', 'Audit Log'); ?>

<?php $__env->startSection('content'); ?>
<?php if (isset($component)) { $__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => 'Audit Log','subtitle' => 'Investigate system activity, trace changes, and identify who performed each action.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Audit Log','subtitle' => 'Investigate system activity, trace changes, and identify who performed each action.']); ?>
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

<section class="audit-summary" aria-label="Audit activity overview">
    <article><span class="audit-summary__icon"><i class="bi bi-journal-text" aria-hidden="true"></i></span><span><small>All activity</small><strong><?php echo e(number_format($auditSummary['total'])); ?></strong><em>Complete recorded history</em></span></article>
    <article><span class="audit-summary__icon is-today"><i class="bi bi-calendar-check" aria-hidden="true"></i></span><span><small>Today</small><strong><?php echo e(number_format($auditSummary['today'])); ?></strong><em>Events recorded today</em></span></article>
    <article><span class="audit-summary__icon is-security"><i class="bi bi-shield-check" aria-hidden="true"></i></span><span><small>Security activity</small><strong><?php echo e(number_format($auditSummary['security'])); ?></strong><em>IDS and access events</em></span></article>
    <article><span class="audit-summary__icon is-users"><i class="bi bi-people" aria-hidden="true"></i></span><span><small>Accountable users</small><strong><?php echo e(number_format($auditSummary['users'])); ?></strong><em>Users represented in logs</em></span></article>
</section>

<form
    method="GET"
    action="<?php echo e(route('audit.index')); ?>"
    data-results-url="<?php echo e(route('audit.results', [], false)); ?>"
    class="audit-filter"
    id="auditLiveFilter"
    role="search"
>
    <div class="audit-filter__primary">
        <label class="audit-search" for="auditSearch">
            <i class="bi bi-search" aria-hidden="true"></i>
            <span class="visually-hidden">Search audit activity</span>
            <input id="auditSearch" type="search" name="search" value="<?php echo e(request('search')); ?>" placeholder="Search activity, user, action, IP, MAC, module, or record ID..." autocomplete="off">
        </label>
        <button type="button" class="audit-clear" data-audit-clear <?php echo e(collect(request()->except('page'))->filter()->isNotEmpty() ? '' : 'hidden'); ?>><i class="bi bi-arrow-counterclockwise" aria-hidden="true"></i> Reset filters</button>
    </div>
    <div class="audit-filter__controls">
        <label class="audit-select"><span>Action</span><select name="action"><option value="">All actions</option><?php $__currentLoopData = $actions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($a); ?>" <?php if(request('action') == $a): echo 'selected'; endif; ?>><?php echo e(ucwords(str_replace('_', ' ', $a))); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></label>
        <label class="audit-select"><span>Module</span><select name="module"><option value="">All modules</option><?php $__currentLoopData = $modules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $module): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($module); ?>" <?php if(request('module') === $module): echo 'selected'; endif; ?>><?php echo e(ucwords(str_replace('_', ' ', $module))); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></label>
        <label class="audit-select"><span>Performed by</span><select name="user_id"><option value="">All users</option><?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($user->id); ?>" <?php if((string) request('user_id') === (string) $user->id): echo 'selected'; endif; ?>><?php echo e($user->name ?: $user->username); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></label>
        <label class="audit-date"><span>From date</span><input type="date" name="date_from" value="<?php echo e(request('date_from')); ?>" max="<?php echo e(now()->toDateString()); ?>"></label>
        <label class="audit-date"><span>To date</span><input type="date" name="date_to" value="<?php echo e(request('date_to')); ?>" max="<?php echo e(now()->toDateString()); ?>"></label>
    </div>
    <div class="audit-filter__feedback"><span class="audit-filter-count" data-audit-active-count></span><span class="audit-live-status" data-audit-status role="status" aria-live="polite"></span></div>
</form>

<?php echo $__env->make('audit._results', ['logs' => $logs], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<?php echo app('Illuminate\Foundation\Vite')('resources/js/views/audit-index-1.js'); ?>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('styles'); ?>
<?php echo app('Illuminate\Foundation\Vite')('resources/css/views/audit-index-1.css'); ?>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\acrylink_newbie\resources\views/audit/index.blade.php ENDPATH**/ ?>