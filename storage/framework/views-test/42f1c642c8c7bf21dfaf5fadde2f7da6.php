<?php $__env->startSection('title', 'Blocked Entities'); ?>
<?php $__env->startSection('page-label', 'Blocked Entities'); ?>

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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => 'Blocklist','subtitle' => 'Review access restrictions, understand why they were created, and safely release active blocks.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Blocklist','subtitle' => 'Review access restrictions, understand why they were created, and safely release active blocks.']); ?>
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

<?php if($blocklistUnavailable ?? false): ?>
<div class="alert alert-error">Blocklist table is not available yet. Run database migrations to enable blocked entity enforcement.</div>
<?php endif; ?>

<?php if (! ($blocklistUnavailable ?? false)): ?>
<div class="security-metrics security-metrics--three blocklist-metrics" aria-label="Blocklist summary">
    <div class="security-metric security-metric--danger"><span class="security-metric__icon"><?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
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
<?php endif; ?></span><span class="security-metric__body"><span class="security-metric__label">Active blocks</span><strong><?php echo e(number_format($activeBlocks)); ?></strong><small>Currently enforced</small></span></div>
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
<?php endif; ?></span><span class="security-metric__body"><span class="security-metric__label">Permanent</span><strong><?php echo e(number_format($permanentBlocks)); ?></strong><small>No automatic expiry</small></span></div>
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
<?php endif; ?></span><span class="security-metric__body"><span class="security-metric__label">All entries</span><strong><?php echo e(number_format($totalBlocks)); ?></strong><small>Complete block history</small></span></div>
</div>
<form method="GET" action="<?php echo e(route('security.blocked-entities')); ?>" class="blocklist-filter" role="search" data-live-filter>
    <div class="blocklist-filter__top">
        <label class="security-search-control blocklist-search" for="blockedEntitySearch">
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
            <span class="visually-hidden">Search blocklist</span>
            <input id="blockedEntitySearch" type="search" name="search" value="<?php echo e($filters['search'] ?? ''); ?>" placeholder="Search IP, MAC, user, reason, or administrator..." autocomplete="off">
        </label>
        <label class="blocklist-select"><span>Entity type</span><select name="type"><option value="">All types</option><?php $__currentLoopData = ['user' => 'User', 'ip_address' => 'IP Address', 'mac_address' => 'MAC Address', 'device' => 'Device']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($value); ?>" <?php if(($filters['type'] ?? '') === $value): echo 'selected'; endif; ?>><?php echo e($label); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></label>
        <label class="blocklist-select"><span>Block status</span><select name="status"><option value="">All statuses</option><?php $__currentLoopData = ['active' => 'Active', 'expired' => 'Expired', 'removed' => 'Removed']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($value); ?>" <?php if(($filters['status'] ?? '') === $value): echo 'selected'; endif; ?>><?php echo e($label); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></label>
        <span class="live-filter-status blocklist-filter-status" data-live-filter-status role="status" aria-live="polite"></span>
    </div>
</form>
<?php endif; ?>

<div class="card blocklist-card" data-live-results>
    <div class="card-header">
        <div><span class="card-title"><?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
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
<?php endif; ?> Blocked entities</span><span class="blocklist-result-copy"><?php echo e(number_format($blockedEntities->total())); ?> <?php echo e(Str::plural('entry', $blockedEntities->total())); ?> found</span></div>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Entity</th>
                    <th>Reason</th>
                    <th>Status</th>
                    <th>Blocked</th>
                    <th>Expires</th>
                    <th>Blocked By</th>
                    <th class="td-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $blockedEntities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $entity): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <?php
                $statusClass = $entity->status === 'active' ? 'status-inactive' : ($entity->status === 'expired' ? 'status-low' : 'status-active');
            ?>
            <tr>
                <td class="blocklist-entity-cell">
                    <div class="blocklist-entity-type"><span class="blocklist-entity-icon" aria-hidden="true"><?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => $entity->entity_type === 'user' ? 'users' : ($entity->entity_type === 'device' ? 'devices' : 'shield')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($entity->entity_type === 'user' ? 'users' : ($entity->entity_type === 'device' ? 'devices' : 'shield'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?></span><span><?php echo e(str_replace('_', ' ', ucfirst($entity->entity_type))); ?></span></div>
                    <div class="code-badge security-code"><?php echo e($entity->entity_value); ?></div>
                </td>
                <td class="table-meta blocklist-reason" title="<?php echo e($entity->reason); ?>"><?php echo e($entity->reason); ?></td>
                <td><span class="status <?php echo e($statusClass); ?>"><?php echo e(ucfirst($entity->status)); ?></span></td>
                <td class="td-muted td-nowrap"><?php echo e($entity->blocked_at?->format('M d, Y h:i A') ?? '-'); ?></td>
                <td class="td-muted td-nowrap"><?php echo e($entity->expires_at?->format('M d, Y h:i A') ?? 'Permanent'); ?></td>
                <td class="td-muted"><?php echo e($entity->blocker?->username ?? 'System'); ?></td>
                <td>
                    <div class="security-row-actions">
                        <?php if($entity->status === 'active'): ?>
                        <form method="POST" action="<?php echo e(route('security.blocked-entities.expire', $entity)); ?>" class="security-form-inline">
                            <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                            <button class="btn btn-secondary btn-sm w-full" type="submit">Expire</button>
                        </form>
                        <form method="POST" action="<?php echo e(route('security.blocked-entities.remove', $entity)); ?>" class="security-form-inline"
                            data-confirm
                            data-confirm-title="Remove Block"
                            data-confirm-message="Remove this blocklist entry? The entity may be able to access the system again."
                            data-confirm-button="Remove">
                            <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                            <button class="btn btn-danger btn-sm w-full" type="submit">Remove</button>
                        </form>
                        <?php else: ?>
                        <span class="security-note">No action needed</span>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <?php if (isset($component)) { $__componentOriginal074a021b9d42f490272b5eefda63257c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal074a021b9d42f490272b5eefda63257c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.empty-state','data' => ['colspan' => '7','title' => collect($filters)->filter()->isNotEmpty() ? 'No matching blocklist entries' : 'No blocked entities','message' => collect($filters)->filter()->isNotEmpty() ? 'Try a different search term or clear the active filters.' : 'Blocked IP addresses, devices, MAC addresses, and users will appear here when generated by security rules.','icon' => 'blocklist']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['colspan' => '7','title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(collect($filters)->filter()->isNotEmpty() ? 'No matching blocklist entries' : 'No blocked entities'),'message' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(collect($filters)->filter()->isNotEmpty() ? 'Try a different search term or clear the active filters.' : 'Blocked IP addresses, devices, MAC addresses, and users will appear here when generated by security rules.'),'icon' => 'blocklist']); ?>
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
            Showing <?php echo e($blockedEntities->firstItem() ?? 0); ?>-<?php echo e($blockedEntities->lastItem() ?? 0); ?> of <?php echo e($blockedEntities->total()); ?> entities
        </div>

        <?php if($blockedEntities->hasPages()): ?>
        <?php
            $currentPage = $blockedEntities->currentPage();
            $lastPage = $blockedEntities->lastPage();
            $pages = collect([1, $currentPage - 1, $currentPage, $currentPage + 1, $lastPage])
                ->filter(fn ($page) => $page >= 1 && $page <= $lastPage)
                ->unique()
                ->sort()
                ->values();
            $previousPage = null;
        ?>
        <nav class="rules-pagination" aria-label="Blocklist pagination">
            <?php if($blockedEntities->onFirstPage()): ?>
                <span class="rules-page is-disabled" aria-disabled="true">Previous</span>
            <?php else: ?>
                <a class="rules-page" href="<?php echo e($blockedEntities->previousPageUrl()); ?>" rel="prev">Previous</a>
            <?php endif; ?>

            <?php $__currentLoopData = $pages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php if($previousPage && $page > $previousPage + 1): ?>
                    <span class="rules-page rules-page--gap" aria-hidden="true">...</span>
                <?php endif; ?>

                <?php if($page === $currentPage): ?>
                    <span class="rules-page is-active" aria-current="page"><?php echo e($page); ?></span>
                <?php else: ?>
                    <a class="rules-page" href="<?php echo e($blockedEntities->url($page)); ?>"><?php echo e($page); ?></a>
                <?php endif; ?>

                <?php $previousPage = $page; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

            <?php if($blockedEntities->hasMorePages()): ?>
                <a class="rules-page" href="<?php echo e($blockedEntities->nextPageUrl()); ?>" rel="next">Next</a>
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

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\acrylink_newbie\resources\views\security\blocked-entities.blade.php ENDPATH**/ ?>