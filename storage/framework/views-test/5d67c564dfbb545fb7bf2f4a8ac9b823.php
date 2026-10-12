<?php $__env->startSection('title', 'Security Rules'); ?>
<?php $__env->startSection('page-label', 'Security Rules'); ?>

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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => 'Security Rules','subtitle' => 'Control when suspicious activity is detected and how the system responds.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Security Rules','subtitle' => 'Control when suspicious activity is detected and how the system responds.']); ?>
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

<div class="security-rule-guide" aria-label="How security rules work">
    <div><span class="security-rule-guide__icon"><i class="bi bi-sliders" aria-hidden="true"></i></span><span><strong>Detection</strong><small>Threshold is the whole-number trigger. Window is how many minutes the system watches.</small></span></div>
    <div><span class="security-rule-guide__icon"><i class="bi bi-exclamation-diamond" aria-hidden="true"></i></span><span><strong>Response</strong><small>Severity sets priority. Action decides whether to log, alert, notify, or block.</small></span></div>
    <div><span class="security-rule-guide__icon"><i class="bi bi-toggle-on" aria-hidden="true"></i></span><span><strong>Status</strong><small>Disabled rules stop future detection immediately after you save the row.</small></span></div>
</div>

<div class="card security-rules-card">
    <div class="card-header">
        <div><span class="card-title"><?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
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
<?php endif; ?> IDS Rule Configuration</span><span class="security-rules-caption">Each row saves independently. Changes affect future activity only.</span></div>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Rule</th>
                    <th>Type</th>
                    <th>Threshold</th>
                    <th>Window</th>
                    <th>Severity</th>
                    <th>Action</th>
                    <th>Status</th>
                    <th class="td-actions">Save</th>
                </tr>
            </thead>
            <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $rules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rule): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <?php
                $formId = 'security-rule-'.$rule->security_rule_id;
                $hasRowErrors = (string) old('_rule_id') === (string) $rule->security_rule_id && $errors->any();
                $thresholdValue = $hasRowErrors ? old('threshold_value') : ($rule->threshold_value !== null ? (int) $rule->threshold_value : null);
                $windowValue = $hasRowErrors ? old('time_window_minutes') : $rule->time_window_minutes;
                $severityValue = $hasRowErrors ? old('severity') : $rule->severity;
                $actionValue = $hasRowErrors ? old('action') : $rule->action;
                $activeValue = $hasRowErrors ? old('is_active') : $rule->is_active;
                $thresholdUnit = match ($rule->rule_name) {
                    'large_transaction' => 'amount (₱)',
                    'excessive_discount' => 'percent (%)',
                    'bulk_inventory_change' => 'items',
                    'large_report_date_range' => 'days',
                    default => 'occurrences',
                };
            ?>
            <tr class="security-rule-row <?php echo e($hasRowErrors ? 'has-errors' : ''); ?>">
                <td>
                    <div class="fw-bold"><?php echo e(str_replace('_', ' ', ucwords($rule->rule_name, '_'))); ?></div>
                    <div class="security-rule-meta"><?php echo e($rule->rule_name); ?></div>
                    <?php if($rule->rule_name === 'rogue_device'): ?>
                    <div class="security-rule-scope"><i class="bi bi-info-circle" aria-hidden="true"></i><span>Controls detection and auto-blocking. Device whitelist access remains enforced separately.</span></div>
                    <?php elseif($rule->rule_name === 'failed_login_limit'): ?>
                    <div class="security-rule-scope"><i class="bi bi-info-circle" aria-hidden="true"></i><span>Threshold controls login attempts; Window controls lockout duration and detection history.</span></div>
                    <?php endif; ?>
                </td>
                <td>
                    <span class="status status-low security-rule-type"><?php echo e(str_replace('_', ' ', $rule->rule_type)); ?></span>
                </td>
                <td>
                    <?php if($rule->usesThreshold()): ?>
                    <label class="security-rule-control"><span class="visually-hidden">Threshold for <?php echo e($rule->rule_name); ?></span><input form="<?php echo e($formId); ?>" class="security-rule-input" type="number" name="threshold_value" value="<?php echo e($thresholdValue); ?>" min="<?php echo e($rule->minimumThreshold()); ?>" max="9999999999" step="1" inputmode="numeric" required><small><?php echo e($thresholdUnit); ?></small></label><?php else: ?><span class="security-rule-na" title="This rule triggers without a numeric threshold">Not used</span><?php endif; ?></td>
                <td>
                    <?php if($rule->usesWindow()): ?>
                    <label class="security-rule-control"><span class="visually-hidden">Time window for <?php echo e($rule->rule_name); ?></span><input form="<?php echo e($formId); ?>" class="security-rule-input" type="number" name="time_window_minutes" value="<?php echo e($windowValue); ?>" min="1" max="10080" step="1" inputmode="numeric" required><small>minutes</small></label><?php else: ?><span class="security-rule-na" title="This rule evaluates each activity immediately">Immediate</span><?php endif; ?></td>
                <td>
                    <select form="<?php echo e($formId); ?>" name="severity" class="security-rule-select-md" aria-label="Severity for <?php echo e($rule->rule_name); ?>">
                        <?php $__currentLoopData = $severityOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $severity): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($severity); ?>" <?php echo e($severityValue === $severity ? 'selected' : ''); ?>><?php echo e(ucfirst($severity)); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </td>
                <td>
                    <select form="<?php echo e($formId); ?>" name="action" class="security-rule-select-sm" aria-label="Action for <?php echo e($rule->rule_name); ?>">
                        <?php $__currentLoopData = $rule->supportedActions(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $action): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($action); ?>" <?php echo e($actionValue === $action ? 'selected' : ''); ?>><?php echo e(ucfirst($action)); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </td>
                <td>
                    <input form="<?php echo e($formId); ?>" type="hidden" name="is_active" value="0">
                    <label class="security-rule-toggle">
                        <input form="<?php echo e($formId); ?>" type="checkbox" name="is_active" value="1" <?php echo e($activeValue ? 'checked' : ''); ?>>
                        <span class="security-rule-toggle__track" aria-hidden="true"><span></span></span>
                        <span class="security-rule-toggle__state"></span>
                    </label>
                </td>
                <td>
                    <form id="<?php echo e($formId); ?>" method="POST" action="<?php echo e(route('security.rules.update', $rule)); ?>">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('PUT'); ?>
                        <input type="hidden" name="_rule_id" value="<?php echo e($rule->security_rule_id); ?>">
                        <button class="btn btn-primary btn-sm security-save-rule" type="submit"><i class="bi bi-check-lg" aria-hidden="true"></i> Save</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <?php if (isset($component)) { $__componentOriginal074a021b9d42f490272b5eefda63257c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal074a021b9d42f490272b5eefda63257c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.empty-state','data' => ['colspan' => '8','title' => 'No security rules configured','message' => 'Add IDS rules to control anomaly thresholds and responses.','icon' => 'rules']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['colspan' => '8','title' => 'No security rules configured','message' => 'Add IDS rules to control anomaly thresholds and responses.','icon' => 'rules']); ?>
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
            Showing <?php echo e($rules->firstItem() ?? 0); ?>-<?php echo e($rules->lastItem() ?? 0); ?> of <?php echo e($rules->total()); ?> rules
        </div>

        <?php if($rules->hasPages()): ?>
        <?php
            $currentPage = $rules->currentPage();
            $lastPage = $rules->lastPage();
            $pages = collect([1, $currentPage - 1, $currentPage, $currentPage + 1, $lastPage])
                ->filter(fn ($page) => $page >= 1 && $page <= $lastPage)
                ->unique()
                ->sort()
                ->values();
            $previousPage = null;
        ?>
        <nav class="rules-pagination" aria-label="Security rules pagination">
            <?php if($rules->onFirstPage()): ?>
                <span class="rules-page is-disabled" aria-disabled="true">Previous</span>
            <?php else: ?>
                <a class="rules-page" href="<?php echo e($rules->previousPageUrl()); ?>" rel="prev">Previous</a>
            <?php endif; ?>

            <?php $__currentLoopData = $pages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php if($previousPage && $page > $previousPage + 1): ?>
                    <span class="rules-page rules-page--gap" aria-hidden="true">...</span>
                <?php endif; ?>

                <?php if($page === $currentPage): ?>
                    <span class="rules-page is-active" aria-current="page"><?php echo e($page); ?></span>
                <?php else: ?>
                    <a class="rules-page" href="<?php echo e($rules->url($page)); ?>"><?php echo e($page); ?></a>
                <?php endif; ?>

                <?php $previousPage = $page; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

            <?php if($rules->hasMorePages()): ?>
                <a class="rules-page" href="<?php echo e($rules->nextPageUrl()); ?>" rel="next">Next</a>
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

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\acrylink_newbie\resources\views/security/rules.blade.php ENDPATH**/ ?>