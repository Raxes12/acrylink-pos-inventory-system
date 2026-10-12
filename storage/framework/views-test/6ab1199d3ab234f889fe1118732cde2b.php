<?php $__env->startSection('title', 'Anomaly Alert Details'); ?>
<?php $__env->startSection('page-label', 'Anomaly Alerts / View Details'); ?>

<?php $__env->startSection('content'); ?>
<?php
    $event = $alert->intrusionEvent;
    $statusClass = $alert->alert_status === 'resolved' ? 'status-active' : ($alert->alert_status === 'new' ? 'status-inactive' : 'status-low');
    $severityClass = in_array($alert->severity, ['high', 'critical'], true) ? 'status-inactive' : 'status-low';
?>

<a href="<?php echo e(route('security.events')); ?>" class="editor-breadcrumb">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
        stroke-linecap="round" stroke-linejoin="round">
        <path d="M19 12H5"/><path d="m12 19-7-7 7-7"/>
    </svg>
    Back to Alerts
</a>

<?php if (isset($component)) { $__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => '','subtitle' => '']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => '','subtitle' => '']); ?>
     <?php $__env->slot('actions', null, []); ?> 
        <?php if($alert->alert_status === 'new'): ?>
        <form method="POST" action="<?php echo e(route('security.events.alerts.acknowledge', $alert)); ?>"><?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?><button class="btn btn-secondary" type="submit">Acknowledge</button></form>
        <?php endif; ?>
        <?php if(in_array($alert->alert_status, ['new', 'acknowledged'], true)): ?>
        <form method="POST" action="<?php echo e(route('security.events.alerts.resolve', $alert)); ?>"><?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?><button class="btn btn-success" type="submit">Resolve</button></form>
        <?php endif; ?>
     <?php $__env->endSlot(); ?>
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

<div class="security-detail-banner <?php echo e(in_array($alert->severity, ['high', 'critical'], true) ? 'security-detail-banner--danger' : ''); ?>">
    <span class="security-detail-banner__icon"><?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'alerts']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'alerts']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?></span>
    <div class="security-detail-banner__copy"><span class="security-detail-eyebrow"><?php echo e(strtoupper($alert->severity)); ?> severity</span><strong><?php echo e($alert->alert_title); ?></strong></div>
    <span class="status <?php echo e($statusClass); ?>"><?php echo e(ucfirst($alert->alert_status)); ?></span>
</div>

<div class="grid-2 security-top-grid">
    <div class="card">
        <div class="card-header">
            <span class="card-title"><?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'alerts']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'alerts']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?> Alert Information</span>
            <span class="status <?php echo e($statusClass); ?>"><?php echo e(ucfirst($alert->alert_status)); ?></span>
        </div>
        <div class="card-body security-card-body">
            <div class="detail-item">
                <div class="detail-label">Title</div>
                <div class="detail-value fw-bold"><?php echo e($alert->alert_title); ?></div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Message</div>
                <div class="detail-value"><?php echo e($alert->alert_message); ?></div>
            </div>
            <div class="detail-grid">
                <div class="detail-item">
                    <div class="detail-label">Severity</div>
                    <span class="status <?php echo e($severityClass); ?>"><?php echo e(strtoupper($alert->severity)); ?></span>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Channel</div>
                    <span class="status status-low"><?php echo e(ucfirst($alert->alert_channel)); ?></span>
                </div>
            </div>
            <div class="detail-grid">
                <div class="detail-item">
                    <div class="detail-label">Acknowledged By</div>
                    <div class="detail-value"><?php echo e($alert->acknowledgedBy?->username ?? '-'); ?></div>
                    <div class="security-note"><?php echo e($alert->acknowledged_at?->format('M d, Y h:i A') ?? ''); ?></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Resolved By</div>
                    <div class="detail-value"><?php echo e($alert->resolvedBy?->username ?? '-'); ?></div>
                    <div class="security-note"><?php echo e($alert->resolved_at?->format('M d, Y h:i A') ?? ''); ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <span class="card-title"><?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
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
<?php endif; ?> Anomaly Event</span>
            <span class="status <?php echo e($event?->event_status === 'resolved' ? 'status-active' : ($event?->event_status === 'open' ? 'status-inactive' : 'status-low')); ?>"><?php echo e(ucfirst($event?->event_status ?? 'missing')); ?></span>
        </div>
        <div class="card-body security-card-body">
            <div class="detail-grid">
                <div class="detail-item">
                    <div class="detail-label">Event Type</div>
                    <div class="detail-value"><?php echo e($event?->event_type ? str_replace('_', ' ', ucfirst($event->event_type)) : '-'); ?></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Source Module</div>
                    <div class="detail-value"><?php echo e($event?->source_module ? str_replace('_', ' ', ucwords($event->source_module, '_')) : '-'); ?></div>
                </div>
            </div>
            <div class="detail-grid">
                <div class="detail-item">
                    <div class="detail-label">IP Address</div>
                    <div class="detail-value detail-value--mono"><?php echo e($event?->ip_address ?? '-'); ?></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">MAC Address</div>
                    <div class="detail-value detail-value--mono"><?php echo e($event?->mac_address ?? '-'); ?></div>
                </div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Description</div>
                <div class="detail-value"><?php echo e($event?->description ?? '-'); ?></div>
            </div>
            <div class="detail-item">
                <div class="detail-label">User Agent</div>
                <div class="detail-value detail-value--mono"><?php echo e($event?->user_agent ?? '-'); ?></div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Detected</div>
                <div class="detail-value"><?php echo e($event?->detected_at?->format('M d, Y h:i A') ?? '-'); ?></div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<?php echo app('Illuminate\Foundation\Vite')('resources/css/views/security-pages-1.css'); ?>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\acrylink_newbie\resources\views\security\alert-details.blade.php ENDPATH**/ ?>