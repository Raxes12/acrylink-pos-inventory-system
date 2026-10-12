<?php $__env->startSection('title', 'IDS Event Details'); ?>
<?php $__env->startSection('page-label', 'Anomaly Alerts'); ?>

<?php $__env->startSection('content'); ?>
<?php
    $statusClass = $event->event_status === 'resolved' ? 'status-active' : ($event->event_status === 'open' ? 'status-inactive' : 'status-low');
    $severityClass = in_array($event->severity, ['high', 'critical'], true) ? 'status-inactive' : 'status-low';
?>

<?php if (isset($component)) { $__componentOriginald76c17eed9b4929ac10f16f4b54f43db = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald76c17eed9b4929ac10f16f4b54f43db = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.security-nav','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('security-nav'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald76c17eed9b4929ac10f16f4b54f43db)): ?>
<?php $attributes = $__attributesOriginald76c17eed9b4929ac10f16f4b54f43db; ?>
<?php unset($__attributesOriginald76c17eed9b4929ac10f16f4b54f43db); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald76c17eed9b4929ac10f16f4b54f43db)): ?>
<?php $component = $__componentOriginald76c17eed9b4929ac10f16f4b54f43db; ?>
<?php unset($__componentOriginald76c17eed9b4929ac10f16f4b54f43db); ?>
<?php endif; ?>
<?php if (isset($component)) { $__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => 'IDS Event Details','subtitle' => ''.e(str_replace('_', ' ', ucfirst($event->event_type))).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'IDS Event Details','subtitle' => ''.e(str_replace('_', ' ', ucfirst($event->event_type))).'']); ?>
     <?php $__env->slot('actions', null, []); ?> 
        <a href="<?php echo e(route('security.events')); ?>" class="btn btn-secondary">Back</a>
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

<div class="security-detail-banner <?php echo e(in_array($event->severity, ['high', 'critical'], true) ? 'security-detail-banner--danger' : ''); ?>">
    <span class="security-detail-banner__icon"><?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
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
<?php endif; ?></span>
    <div class="security-detail-banner__copy"><span class="security-detail-eyebrow"><?php echo e(strtoupper($event->severity)); ?> severity</span><strong><?php echo e(str_replace('_', ' ', ucfirst($event->event_type))); ?></strong></div>
    <span class="status <?php echo e($statusClass); ?>"><?php echo e(ucfirst($event->event_status)); ?></span>
</div>

<div class="grid-2 security-top-grid">
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
            <span class="status <?php echo e($statusClass); ?>"><?php echo e(ucfirst($event->event_status)); ?></span>
        </div>
        <div class="card-body security-card-body">
            <div class="detail-grid">
                <div class="detail-item">
                    <div class="detail-label">Event Type</div>
                    <div class="detail-value"><?php echo e(str_replace('_', ' ', ucfirst($event->event_type))); ?></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Severity</div>
                    <span class="status <?php echo e($severityClass); ?>"><?php echo e(strtoupper($event->severity)); ?></span>
                </div>
            </div>
            <div class="detail-grid">
                <div class="detail-item">
                    <div class="detail-label">Source Module</div>
                    <div class="detail-value"><?php echo e($event->source_module ? str_replace('_', ' ', ucwords($event->source_module, '_')) : '-'); ?></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Rule</div>
                    <div class="detail-value"><?php echo e($event->rule?->rule_name ?? '-'); ?></div>
                </div>
            </div>
            <?php if($event->request_path || $event->user_role || $event->required_roles): ?>
            <div class="detail-grid">
                <div class="detail-item">
                    <div class="detail-label">Attempted Endpoint</div>
                    <div class="detail-value detail-value--mono"><?php echo e(trim(($event->request_method ?? '').' '.($event->request_path ?? '')) ?: '-'); ?></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Role Check</div>
                    <div class="detail-value"><?php echo e($event->user_role ?? '-'); ?> / required: <?php echo e($event->required_roles ?? '-'); ?></div>
                </div>
            </div>
            <?php endif; ?>
            <div class="detail-grid">
                <div class="detail-item">
                    <div class="detail-label">IP Address</div>
                    <div class="detail-value detail-value--mono"><?php echo e($event->ip_address ?? '-'); ?></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">MAC Address</div>
                    <div class="detail-value detail-value--mono"><?php echo e($event->mac_address ?? '-'); ?></div>
                </div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Description</div>
                <div class="detail-value"><?php echo e($event->description); ?></div>
            </div>
            <div class="detail-item">
                <div class="detail-label">User Agent</div>
                <div class="detail-value detail-value--mono"><?php echo e($event->user_agent ?? '-'); ?></div>
            </div>
            <div class="detail-grid">
                <div class="detail-item">
                    <div class="detail-label">Detected</div>
                    <div class="detail-value"><?php echo e($event->detected_at?->format('M d, Y h:i A') ?? '-'); ?></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">User</div>
                    <div class="detail-value"><?php echo e($event->user?->username ?? '-'); ?></div>
                </div>
            </div>
        </div>
    </div>

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
<?php endif; ?> Linked Dashboard Alerts</span>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Alert</th>
                        <th>Status</th>
                        <th>Email</th>
                        <th>Created</th>
                    </tr>
                </thead>
                <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $event->alerts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $alert): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php
                    $latestEmail = $alert->notificationLogs->sortByDesc('created_at')->first();
                    $emailNeeded = in_array($alert->alert_channel, ['email', 'both'], true);
                    $emailStatus = $emailNeeded ? ($latestEmail?->send_status ?? 'pending') : 'No email needed';
                ?>
                <tr>
                    <td>
                        <div class="fw-bold"><?php echo e($alert->alert_title); ?></div>
                        <div class="security-note"><?php echo e($alert->alert_message); ?></div>
                    </td>
                    <td><span class="status <?php echo e($alert->alert_status === 'resolved' ? 'status-active' : ($alert->alert_status === 'new' ? 'status-inactive' : 'status-low')); ?>"><?php echo e(ucfirst($alert->alert_status)); ?></span></td>
                    <td><span class="status <?php echo e($emailStatus === 'sent' ? 'status-active' : ($emailStatus === 'failed' ? 'status-inactive' : 'status-low')); ?>"><?php echo e(ucfirst($emailStatus)); ?></span></td>
                    <td class="td-muted td-nowrap"><?php echo e($alert->created_at?->format('M d, Y h:i A') ?? '-'); ?></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <?php if (isset($component)) { $__componentOriginal074a021b9d42f490272b5eefda63257c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal074a021b9d42f490272b5eefda63257c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.empty-state','data' => ['colspan' => '4','title' => 'No linked dashboard alerts','message' => 'This IDS event has no linked alert record yet.','icon' => 'alerts']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['colspan' => '4','title' => 'No linked dashboard alerts','message' => 'This IDS event has no linked alert record yet.','icon' => 'alerts']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal074a021b9d42f490272b5eefda63257c)): ?>
<?php $attributes = $__attributesOriginal074a021b9d42f490272b5eefda63257c; ?>
<?php unset($__attributesOriginal074a021b9d42f490272b5eefda63257c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal074a021b9d42f490272b5eefda63257c)): ?>
<?php $component = $__componentOriginal074a021b9d42f490272b5eefda63257c; ?>
<?php unset($__componentOriginal074a021b9d42f490272b5eefda63257c); ?>
<?php endif; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<?php echo app('Illuminate\Foundation\Vite')('resources/css/views/security-pages-1.css'); ?>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\acrylink_newbie\resources\views/security/event-details.blade.php ENDPATH**/ ?>