
<?php $__env->startSection('title', 'Anomaly Alerts'); ?>
<?php $__env->startSection('page-label', 'Anomaly Alerts'); ?>

<?php $__env->startSection('content'); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => 'Anomaly Alerts','subtitle' => 'Review unusual transactions and activities, prioritize risk, and track response status.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Anomaly Alerts','subtitle' => 'Review unusual transactions and activities, prioritize risk, and track response status.']); ?>
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

<div class="security-metrics" aria-label="Anomaly alert summary">
    <div class="security-metric security-metric--danger"><span class="security-metric__icon"><?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
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
<?php endif; ?></span><span class="security-metric__body"><span class="security-metric__label">Open</span><strong><?php echo e(number_format($eventCounts['open'])); ?></strong><small>Needs attention</small></span></div>
    <div class="security-metric security-metric--warning"><span class="security-metric__icon"><?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
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
<?php endif; ?></span><span class="security-metric__body"><span class="security-metric__label">High risk active</span><strong><?php echo e(number_format($eventCounts['high_risk'])); ?></strong><small>Prioritize review</small></span></div>
    <div class="security-metric security-metric--success"><span class="security-metric__icon"><?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
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
<?php endif; ?></span><span class="security-metric__body"><span class="security-metric__label">Resolved</span><strong><?php echo e(number_format($eventCounts['resolved'])); ?></strong><small>Response completed</small></span></div>
    <div class="security-metric"><span class="security-metric__icon"><?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'transactions']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'transactions']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?></span><span class="security-metric__body"><span class="security-metric__label">Total detections</span><strong><?php echo e(number_format($eventCounts['total'])); ?></strong><small>Recorded events</small></span></div>
</div>

<form method="GET" action="<?php echo e(route('security.events')); ?>" class="security-alert-filter" role="search" data-live-filter>
    <div class="security-alert-filter__controls">
        <label class="security-search-control security-alert-search" for="securityEventSearch">
            <?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'search']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'search']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
            <input id="securityEventSearch" type="search" name="search" value="<?php echo e($filters['search'] ?? ''); ?>" placeholder="Search alert, user, IP, MAC, or source..." autocomplete="off">
        </label>
        <label class="security-alert-select">
            <span>Risk level</span>
            <select name="severity"><option value="">All severities</option><?php $__currentLoopData = ['critical', 'high', 'medium', 'low']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $severity): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($severity); ?>" <?php if(($filters['severity'] ?? '') === $severity): echo 'selected'; endif; ?>><?php echo e(ucfirst($severity)); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select>
        </label>
        <label class="security-alert-select">
            <span>Response status</span>
            <select name="status"><option value="">All statuses</option><?php $__currentLoopData = ['open', 'reviewed', 'resolved']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($status); ?>" <?php if(($filters['status'] ?? '') === $status): echo 'selected'; endif; ?>><?php echo e(ucfirst($status)); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select>
        </label>
        <span class="live-filter-status security-alert-filter__status" data-live-filter-status role="status" aria-live="polite"></span>
    </div>
</form>

<div class="card card--spaced" data-live-results>
    <div class="card-header">
        <span class="card-title">
            <?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
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
<?php endif; ?>
            Manage Anomaly Detections
        </span>
        <?php if($eventCounts['open'] > 0): ?>
        <span class="status status-inactive"><?php echo e($eventCounts['open']); ?> open</span>
        <?php else: ?>
        <span class="status status-active">All reviewed</span>
        <?php endif; ?>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Detection</th>
                    <th>Risk</th>
                    <th>Origin</th>
                    <th>Response</th>
                    <th>Detected</th>
                    <th class="td-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $events; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <?php
                $alert = $event->latestAlert;
                $severity = $alert?->severity ?? $event->severity;
                $status = $alert?->alert_status ?? $event->event_status;
                $latestEmail = $alert?->latestNotificationLog;
                $emailNeeded = $alert && in_array($alert->alert_channel, ['email', 'both'], true);
                $emailStatus = $emailNeeded ? ($latestEmail?->send_status ?? 'pending') : 'No email needed';
                $severityClass = in_array($severity, ['high', 'critical'], true) ? 'status-inactive' : 'status-low';
                $statusClass = in_array($status, ['resolved'], true) ? 'status-active' : (in_array($status, ['new', 'open'], true) ? 'status-inactive' : 'status-low');
            ?>
            <tr class="security-alert-row security-alert-row--<?php echo e($severity); ?>">
                <td>
                    <a href="<?php echo e($alert ? route('security.events.alerts.show', $alert) : route('security.events.show', $event)); ?>" class="security-alert-title"><?php echo e($alert?->alert_title ?? str_replace('_', ' ', ucfirst($event->event_type))); ?></a>
                    <div class="security-alert-summary"><?php echo e(\Illuminate\Support\Str::limit($alert?->alert_message ?? $event->description, 105)); ?></div>
                    <div class="security-alert-meta"><span><?php echo e(str_replace('_', ' ', ucfirst($event->event_type))); ?></span><?php if($event->user): ?><span>User: <?php echo e($event->user->username); ?></span><?php endif; ?></div>
                </td>
                <td><span class="status <?php echo e($severityClass); ?>"><?php echo e(strtoupper($severity)); ?></span></td>
                <td class="table-meta security-origin-cell">
                    <strong><?php echo e($event->source_module ? str_replace('_', ' ', ucwords($event->source_module, '_')) : 'Unknown source'); ?></strong>
                    <?php if($event->request_path): ?>
                    <div class="security-endpoint"><?php echo e($event->request_method); ?> <?php echo e(\Illuminate\Support\Str::limit($event->request_path, 42)); ?></div>
                    <?php endif; ?>
                    <div class="security-address">IP: <?php echo e($event->ip_address ?: 'Not captured'); ?></div>
                </td>
                <td class="security-response-cell">
                    <span class="status <?php echo e($statusClass); ?>"><?php echo e(ucfirst($status)); ?></span>
                    <?php if($alert?->acknowledgedBy && $status === 'acknowledged'): ?>
                    <div class="text-muted td-muted--sm mt-1">By <?php echo e($alert->acknowledgedBy->username); ?></div>
                    <?php endif; ?>
                    <?php if($alert?->resolvedBy && $status === 'resolved'): ?>
                    <div class="text-muted td-muted--sm mt-1">By <?php echo e($alert->resolvedBy->username); ?></div>
                    <?php endif; ?>
                    <div class="security-email-state">Email: <span><?php echo e(ucfirst($emailStatus)); ?></span></div>
                </td>
                <td class="td-muted td-nowrap"><strong><?php echo e($event->detected_at?->diffForHumans() ?? '-'); ?></strong><div class="security-exact-time"><?php echo e($event->detected_at?->format('M d, Y h:i A') ?? ''); ?></div></td>
                <td>
                    <div class="security-row-actions security-alert-actions" role="group" aria-label="Actions for <?php echo e($alert?->alert_title ?? str_replace('_', ' ', ucfirst($event->event_type))); ?>">
                        <a href="<?php echo e($alert ? route('security.events.alerts.show', $alert) : route('security.events.show', $event)); ?>" class="btn btn-secondary btn-sm security-view-action" aria-label="View details for <?php echo e($alert?->alert_title ?? str_replace('_', ' ', ucfirst($event->event_type))); ?>">
                            <?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'eye']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'eye']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?> View details
                        </a>

                        <?php if(($alert && $status === 'new') || (!$alert && $status === 'open')): ?>
                        <form method="POST" action="<?php echo e($alert ? route('security.events.alerts.acknowledge', $alert) : route('security.events.update', $event)); ?>" class="form-stack">
                            <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                            <?php if (! ($alert)): ?>
                            <input type="hidden" name="event_status" value="reviewed">
                            <?php endif; ?>
                            <button class="btn btn-secondary btn-sm w-full" type="submit"><?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
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
<?php endif; ?> Acknowledge</button>
                        </form>
                        <?php endif; ?>

                        <?php if(($alert && $status === 'acknowledged') || (!$alert && $status === 'reviewed')): ?>
                        <form method="POST" action="<?php echo e($alert ? route('security.events.alerts.resolve', $alert) : route('security.events.update', $event)); ?>" class="form-stack">
                            <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                            <?php if (! ($alert)): ?>
                            <input type="hidden" name="event_status" value="resolved">
                            <?php endif; ?>
                            <button class="btn btn-success btn-sm w-full" type="submit"><?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'rules']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'rules']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?> Resolve</button>
                        </form>
                        <?php endif; ?>

                        <?php if(($event->ip_address || $event->mac_address) && $status !== 'resolved'): ?>
                        <form method="POST" action="<?php echo e(route('security.events.block-device', $event)); ?>" class="form-stack security-alert-actions__danger"
                            data-confirm
                            data-confirm-title="Block Device"
                            data-confirm-message="Block this IP/MAC address? Future access from this entity may be denied."
                            data-confirm-button="Block Device">
                            <?php echo csrf_field(); ?>
                            <button class="btn btn-danger btn-sm w-full" type="submit"><?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'blocklist']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'blocklist']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?> Block device</button>
                        </form>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <?php if (isset($component)) { $__componentOriginal074a021b9d42f490272b5eefda63257c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal074a021b9d42f490272b5eefda63257c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.empty-state','data' => ['colspan' => '6','title' => 'No anomalies detected','message' => 'Unusual transactions and activities will appear here when detected.','icon' => 'alerts']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['colspan' => '6','title' => 'No anomalies detected','message' => 'Unusual transactions and activities will appear here when detected.','icon' => 'alerts']); ?>
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
    <div class="view-blades-footer">
        <div class="pagination-summary">
            Showing <?php echo e($events->firstItem() ?? 0); ?>-<?php echo e($events->lastItem() ?? 0); ?> of <?php echo e($events->total()); ?> alerts or events
        </div>

        <?php if($events->hasPages()): ?>
        <?php
            $currentPage = $events->currentPage();
            $lastPage = $events->lastPage();
            $pages = collect([1, $currentPage - 1, $currentPage, $currentPage + 1, $lastPage])
                ->filter(fn ($page) => $page >= 1 && $page <= $lastPage)
                ->unique()
                ->sort()
                ->values();
            $previousPage = null;
        ?>
        <nav class="rules-pagination" aria-label="Anomaly alerts pagination">
            <?php if($events->onFirstPage()): ?>
                <span class="rules-page is-disabled" aria-disabled="true">Previous</span>
            <?php else: ?>
                <a class="rules-page" href="<?php echo e($events->previousPageUrl()); ?>" rel="prev">Previous</a>
            <?php endif; ?>

            <?php $__currentLoopData = $pages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php if($previousPage && $page > $previousPage + 1): ?>
                    <span class="rules-page rules-page--gap" aria-hidden="true">...</span>
                <?php endif; ?>

                <?php if($page === $currentPage): ?>
                    <span class="rules-page is-active" aria-current="page"><?php echo e($page); ?></span>
                <?php else: ?>
                    <a class="rules-page" href="<?php echo e($events->url($page)); ?>"><?php echo e($page); ?></a>
                <?php endif; ?>

                <?php $previousPage = $page; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

            <?php if($events->hasMorePages()): ?>
                <a class="rules-page" href="<?php echo e($events->nextPageUrl()); ?>" rel="next">Next</a>
            <?php else: ?>
                <span class="rules-page is-disabled" aria-disabled="true">Next</span>
            <?php endif; ?>
        </nav>
        <?php endif; ?>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<?php echo app('Illuminate\Foundation\Vite')('resources/css/views/security-pages-1.css'); ?>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\acrylink_newbie\resources\views/security/events.blade.php ENDPATH**/ ?>