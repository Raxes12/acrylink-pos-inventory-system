<div class="card audit-results" id="auditResults" aria-live="polite">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Timestamp</th>
                    <th>User</th>
                    <th>Role</th>
                    <th>Action</th>
                    <th>Module / Record</th>
                    <th>Detail</th>
                    <th>IP / MAC</th>
                </tr>
            </thead>
            <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <?php
                $badgeClass = 'action-badge--' . strtolower($log->action);
                if (!in_array($badgeClass, [
                    'action-badge--login', 'action-badge--login_failed', 'action-badge--logout',
                    'action-badge--sale', 'action-badge--product', 'action-badge--supplier',
                    'action-badge--user', 'action-badge--category', 'action-badge--security',
                    'action-badge--ids', 'action-badge--refund', 'action-badge--report',
                ], true)) {
                    $badgeClass = 'action-badge--default';
                }
            ?>
            <tr>
                <td class="td-muted td-nowrap"><?php echo e(\Carbon\Carbon::parse($log->timestamp)->format('M d, Y h:i:s A')); ?></td>
                <td class="fw-bold"><?php echo e($log->username); ?></td>
                <td class="td-muted td-muted--sm"><?php echo e(ucfirst($log->role)); ?></td>
                <td><span class="action-badge <?php echo e($badgeClass); ?>"><?php echo e($log->action); ?></span></td>
                <td class="td-muted">
                    <div class="td-stack">
                        <span><?php echo e($log->affected_table ? str_replace('_', ' ', ucfirst($log->affected_table)) : '-'); ?></span>
                        <?php if($log->affected_record_id): ?><span class="td-mono">#<?php echo e($log->affected_record_id); ?></span><?php endif; ?>
                    </div>
                </td>
                <td class="td-muted td-truncate"><div><?php echo e($log->detail); ?></div></td>
                <td class="td-mono">
                    <div class="td-stack">
                        <span><?php echo e($log->ip_address); ?></span>
                        <?php if($log->mac_address): ?><span><?php echo e($log->mac_address); ?></span><?php endif; ?>
                    </div>
                </td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <?php if (isset($component)) { $__componentOriginal074a021b9d42f490272b5eefda63257c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal074a021b9d42f490272b5eefda63257c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.empty-state','data' => ['colspan' => '7','title' => 'No audit entries found','message' => 'Activity from sign-ins, sales, and inventory changes will appear here.','icon' => 'audit']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['colspan' => '7','title' => 'No audit entries found','message' => 'Activity from sign-ins, sales, and inventory changes will appear here.','icon' => 'audit']); ?>
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
    <div class="view-blades-footer" data-audit-pagination>
        <div class="pagination-summary">
            <?php if($logs->total() > 0): ?>
                Showing <?php echo e($logs->firstItem()); ?>-<?php echo e($logs->lastItem()); ?> of <?php echo e($logs->total()); ?> audit entries
            <?php else: ?>
                No audit entries found
            <?php endif; ?>
        </div>

        <?php if($logs->hasPages()): ?>
            <?php
                $currentPage = $logs->currentPage();
                $lastPage = $logs->lastPage();
                $startPage = max(1, $currentPage - 2);
                $endPage = min($lastPage, $currentPage + 2);
            ?>

            <nav class="rules-pagination" aria-label="Audit log pagination">
                <?php if($logs->onFirstPage()): ?>
                    <span class="rules-page is-disabled" aria-disabled="true">Previous</span>
                <?php else: ?>
                    <a class="rules-page" href="<?php echo e($logs->previousPageUrl()); ?>" rel="prev">Previous</a>
                <?php endif; ?>

                <?php if($startPage > 1): ?>
                    <a class="rules-page" href="<?php echo e($logs->url(1)); ?>">1</a>
                    <?php if($startPage > 2): ?><span class="rules-page rules-page--gap" aria-hidden="true">...</span><?php endif; ?>
                <?php endif; ?>

                <?php for($page = $startPage; $page <= $endPage; $page++): ?>
                    <?php if($page === $currentPage): ?>
                        <span class="rules-page is-active" aria-current="page"><?php echo e($page); ?></span>
                    <?php else: ?>
                        <a class="rules-page" href="<?php echo e($logs->url($page)); ?>"><?php echo e($page); ?></a>
                    <?php endif; ?>
                <?php endfor; ?>

                <?php if($endPage < $lastPage): ?>
                    <?php if($endPage < $lastPage - 1): ?><span class="rules-page rules-page--gap" aria-hidden="true">...</span><?php endif; ?>
                    <a class="rules-page" href="<?php echo e($logs->url($lastPage)); ?>"><?php echo e($lastPage); ?></a>
                <?php endif; ?>

                <?php if($logs->hasMorePages()): ?>
                    <a class="rules-page" href="<?php echo e($logs->nextPageUrl()); ?>" rel="next">Next</a>
                <?php else: ?>
                    <span class="rules-page is-disabled" aria-disabled="true">Next</span>
                <?php endif; ?>
            </nav>
        <?php endif; ?>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\acrylink_newbie\resources\views\audit\_results.blade.php ENDPATH**/ ?>