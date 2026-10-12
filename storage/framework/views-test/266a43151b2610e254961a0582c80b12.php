
<?php $__env->startSection('title', 'Authorized Devices'); ?>
<?php $__env->startSection('page-label', 'Authorized Devices'); ?>

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
<div id="devicePageState" hidden data-initial-edit-device="<?php echo e(old('_device_id', $editingDevice?->id)); ?>" data-open-register="<?php echo e($errors->any() && old('_device_form') === 'register' ? 'true' : 'false'); ?>"></div>
<script type="application/json" id="initialEditingDeviceData"><?php echo json_encode($editingDevice, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT, 512) ?></script>
<?php if (isset($component)) { $__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => 'Authorized Devices','subtitle' => 'Control which devices can access Acry-Link and review their assigned users.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Authorized Devices','subtitle' => 'Control which devices can access Acry-Link and review their assigned users.']); ?>
     <?php $__env->slot('actions', null, []); ?> 
        <button type="button" class="btn btn-primary" data-open-device-register><?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'plus']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'plus']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?> Register Device</button>
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

<div class="security-metrics device-metrics" aria-label="Authorized device summary">
    <button type="button" class="security-metric device-metric-filter <?php echo e(($filters['status'] ?? '') === 'authorized' ? 'is-active' : ''); ?>" data-device-status-filter="authorized" aria-pressed="<?php echo e(($filters['status'] ?? '') === 'authorized' ? 'true' : 'false'); ?>">
        <span class="device-metric-icon device-metric-icon--authorized"><i class="bi bi-shield-check" aria-hidden="true"></i></span>
        <span class="device-metric-content">
            <span class="device-metric-label">Authorized</span><strong><?php echo e(number_format($deviceCounts['authorized'])); ?></strong>
            <small>Allowed to connect</small>
            <span class="device-metric-action">View authorized <i class="bi bi-arrow-right" aria-hidden="true"></i></span>
        </span>
    </button>
    <button type="button" class="security-metric device-metric-filter <?php echo e(($filters['status'] ?? '') === 'blocked' ? 'is-active' : ''); ?>" data-device-status-filter="blocked" aria-pressed="<?php echo e(($filters['status'] ?? '') === 'blocked' ? 'true' : 'false'); ?>">
        <span class="device-metric-icon device-metric-icon--blocked"><i class="bi bi-slash-circle" aria-hidden="true"></i></span>
        <span class="device-metric-content">
            <span class="device-metric-label">Blocked</span><strong><?php echo e(number_format($deviceCounts['blocked'])); ?></strong>
            <small>Access denied</small>
            <span class="device-metric-action">View blocked <i class="bi bi-arrow-right" aria-hidden="true"></i></span>
        </span>
    </button>
    <button type="button" class="security-metric device-metric-filter <?php echo e(($filters['status'] ?? '') === 'inactive' ? 'is-active' : ''); ?>" data-device-status-filter="inactive" aria-pressed="<?php echo e(($filters['status'] ?? '') === 'inactive' ? 'true' : 'false'); ?>">
        <span class="device-metric-icon device-metric-icon--inactive"><i class="bi bi-pause-circle" aria-hidden="true"></i></span>
        <span class="device-metric-content">
            <span class="device-metric-label">Inactive</span><strong><?php echo e(number_format($deviceCounts['inactive'])); ?></strong>
            <small>Temporarily disabled</small>
            <span class="device-metric-action">View inactive <i class="bi bi-arrow-right" aria-hidden="true"></i></span>
        </span>
    </button>
    <button type="button" class="security-metric device-metric-filter <?php echo e(empty($filters['status']) ? 'is-active' : ''); ?>" data-device-status-filter="" aria-pressed="<?php echo e(empty($filters['status']) ? 'true' : 'false'); ?>">
        <span class="device-metric-icon"><i class="bi bi-pc-display" aria-hidden="true"></i></span>
        <span class="device-metric-content">
            <span class="device-metric-label">Total devices</span><strong><?php echo e(number_format($deviceCounts['total'])); ?></strong>
            <small>All registered records</small>
            <span class="device-metric-action">View all <i class="bi bi-arrow-right" aria-hidden="true"></i></span>
        </span>
    </button>
</div>

<form method="GET" action="<?php echo e(route('security.devices')); ?>" class="filter-bar security-filter-bar" role="search" data-live-filter>
    <div class="security-search-control"><?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
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
<?php endif; ?><input id="deviceSearch" type="search" name="search" value="<?php echo e($filters['search'] ?? ''); ?>" placeholder="Search device, IP, MAC, or assigned user..."></div>
    <select name="status" aria-label="Filter by device status">
        <option value="">All statuses</option>
        <?php $__currentLoopData = ['authorized' => 'Authorized', 'blocked' => 'Blocked', 'inactive' => 'Inactive']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <option value="<?php echo e($value); ?>" <?php if(($filters['status'] ?? '') === $value): echo 'selected'; endif; ?>><?php echo e($label); ?></option>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </select>
    <?php if(filled($filters['search'] ?? null) || filled($filters['status'] ?? null)): ?>
    <a href="<?php echo e(route('security.devices')); ?>" class="btn btn-secondary btn-sm" data-live-filter-clear><i class="bi bi-x-lg" aria-hidden="true"></i> Clear filters</a>
    <?php endif; ?>
    <span class="live-filter-status" data-live-filter-status role="status" aria-live="polite"></span>
</form>

<div class="card device-policy-card">
    <div class="card-header"><div><span class="card-title"><?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
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
<?php endif; ?> Device Access Policy</span><span class="device-card-caption">Identifiers detected for your current request.</span></div></div>
    <div class="card-body device-policy-body">
        <div class="current-device-panel">
            <div class="current-device-row"><span>Current IP</span><code><?php echo e($currentIp ?? 'Not detected'); ?></code></div>
            <div class="current-device-row"><span>Current MAC</span><code><?php echo e($currentMac ?? 'Not detected'); ?></code></div>
        </div>
        <p class="section-note"><i class="bi bi-info-circle" aria-hidden="true"></i> Only <strong>Authorized</strong> records can pass strict device checks. Assigning a user limits the rule to that account.</p>
    </div>
</div>

<div class="card" data-live-results>
    <div class="card-header"><span class="card-title"><?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'devices']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'devices']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?> Registered Devices</span><span class="security-note"><?php echo e($devices->total()); ?> matching</span></div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Device</th>
                    <th>IP</th>
                    <th>MAC</th>
                    <th>User</th>
                    <th>Status</th>
                    <th class="td-actions text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $devices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $device): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr class="device-table-row">
                <td><div class="device-table-name"><span class="device-table-icon"><i class="bi bi-pc-display"></i></span><div><strong><?php echo e($device->device_name); ?></strong><small>Updated <?php echo e($device->updated_at?->diffForHumans() ?? 'recently'); ?></small></div></div></td>
                <td><span class="code-badge"><?php echo e($device->ip_address ?? '-'); ?></span></td>
                <td><span class="code-badge"><?php echo e($device->mac_address ?? '-'); ?></span></td>
                <td><div class="device-user-name"><?php echo e($device->assignedUser?->name ?? 'Any active user'); ?></div><?php if($device->assignedUser): ?><div class="table-meta"><?php echo e($device->assignedUser->username); ?></div><?php endif; ?></td>
                <td>
                    <span class="status <?php echo e($device->status === 'authorized' ? 'status-active' : ($device->status === 'blocked' ? 'status-inactive' : 'status-low')); ?>">
                        <?php echo e(ucfirst($device->status)); ?>

                    </span>
                </td>
                <td>
                    <div class="table-actions table-actions--center device-actions">
                        <button type="button" class="btn btn-secondary btn-sm device-action-btn" data-view-device="<?php echo e($device->id); ?>" title="View Device" aria-label="View <?php echo e($device->device_name); ?>">
                            <i class="bi bi-eye"></i>
                        </button>
                        <a href="<?php echo e(route('security.devices', ['edit_device' => $device->id])); ?>" class="btn btn-secondary btn-sm device-action-btn" data-edit-device="<?php echo e($device->id); ?>" title="Edit Device" aria-label="Edit <?php echo e($device->device_name); ?>">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form method="POST" action="<?php echo e(route('security.devices.destroy', $device)); ?>" class="device-action-form"
                            data-confirm
                            data-confirm-title="Are you sure?"
                            data-confirm-message="Delete this device <?php echo e($device->device_name); ?>? This cannot be undone."
                            data-confirm-button="Confirm">
                            <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                            <button type="button" class="btn btn-danger btn-sm device-action-btn" title="Delete Device" aria-label="Delete <?php echo e($device->device_name); ?>" data-confirm-trigger><i class="bi bi-trash3"></i></button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr><td colspan="6" class="empty-state">No devices registered yet.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
    <div style="padding:14px;"><?php echo e($devices->links()); ?></div>
    <script type="application/json" id="authorizedDevicesData"><?php echo json_encode($devices->items(), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT, 512) ?></script>
</div>

<div id="registerDeviceModal" class="modal-backdrop" style="display:none;" role="presentation" aria-hidden="true">
    <div class="modal device-editor-modal" role="dialog" aria-modal="true" aria-labelledby="registerDeviceTitle" aria-describedby="registerDeviceDescription">
        <div class="modal-header">
            <div><span class="modal-title" id="registerDeviceTitle">Register Device</span><span class="device-modal-caption" id="registerDeviceDescription">Add a trusted device rule for POS access.</span></div>
            <button type="button" class="modal-close-btn" aria-label="Close device registration" data-close-device-register>&times;</button>
        </div>
        <form method="POST" action="<?php echo e(route('security.devices.store')); ?>" class="device-modal-form">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="_device_form" value="register">
            <div class="modal-body device-modal-body">
                <div class="form-group device-form-wide">
                    <label for="registerDeviceName">Device Name <span aria-hidden="true">*</span></label>
                    <input id="registerDeviceName" name="device_name" value="<?php echo e(old('device_name')); ?>" maxlength="100" required placeholder="e.g. Front Counter POS">
                    <div class="form-help">Use a name staff can quickly recognize.</div>
                </div>
                <div class="form-group">
                    <label for="registerDeviceIp">IP Address</label>
                    <input id="registerDeviceIp" name="ip_address" value="<?php echo e(old('ip_address')); ?>" placeholder="<?php echo e(request()->ip()); ?>" inputmode="decimal">
                    <div class="form-help">Current request: <?php echo e($currentIp ?? 'Not detected'); ?></div>
                </div>
                <div class="form-group">
                    <label for="registerDeviceMac">MAC Address</label>
                    <input id="registerDeviceMac" name="mac_address" value="<?php echo e(old('mac_address')); ?>" maxlength="17" placeholder="AA:BB:CC:DD:EE:FF" spellcheck="false">
                    <div class="form-help">Provide an IP address, MAC address, or both.</div>
                </div>
                <div class="form-group">
                    <label for="registerDeviceUser">Assigned User</label>
                    <select id="registerDeviceUser" name="assigned_user_id">
                        <option value="">Any active user</option>
                        <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($user->id); ?>" <?php if((string) old('assigned_user_id') === (string) $user->id): echo 'selected'; endif; ?>><?php echo e($user->name); ?> (<?php echo e($user->username); ?>)</option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="registerDeviceStatus">Initial Status</label>
                    <select id="registerDeviceStatus" name="status">
                        <option value="authorized" <?php if(old('status', 'authorized') === 'authorized'): echo 'selected'; endif; ?>>Authorized</option>
                        <option value="blocked" <?php if(old('status') === 'blocked'): echo 'selected'; endif; ?>>Blocked</option>
                        <option value="inactive" <?php if(old('status') === 'inactive'): echo 'selected'; endif; ?>>Inactive</option>
                    </select>
                </div>
                <div class="device-form-note"><i class="bi bi-shield-check" aria-hidden="true"></i><span><strong>Access reminder</strong> Authorized devices can access protected areas when the rest of the security checks pass.</span></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-close-device-register>Cancel</button>
                <button class="btn btn-primary" type="submit"><?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'devices']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'devices']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?> Register Device</button>
            </div>
        </form>
    </div>
</div>

<div id="viewDeviceModal" class="modal-backdrop" style="display:none;" role="presentation" aria-hidden="true">
    <div class="modal device-detail-modal" role="dialog" aria-modal="true" aria-labelledby="viewDeviceTitle">
        <div class="modal-header">
            <span class="modal-title" id="viewDeviceTitle">Device Details</span>
            <button type="button" class="modal-close-btn" aria-label="Close device details" data-close-device-view>&times;</button>
        </div>
        <div class="modal-body">
            <div class="device-detail-summary">
                <span class="device-detail-icon" aria-hidden="true"><i class="bi bi-pc-display"></i></span>
                <div>
                    <div id="viewDeviceName" class="device-detail-name">-</div>
                    <span id="viewDeviceStatus" class="status status-low">-</span>
                </div>
            </div>

            <div class="device-detail-grid">
                <div class="detail-item">
                    <span class="detail-label">IP Address</span>
                    <span id="viewDeviceIp" class="detail-value detail-value--mono">-</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">MAC Address</span>
                    <span id="viewDeviceMac" class="detail-value detail-value--mono">-</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Assigned User</span>
                    <span id="viewDeviceUser" class="detail-value">-</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Created</span>
                    <span id="viewDeviceCreated" class="detail-value">-</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Last Updated</span>
                    <span id="viewDeviceUpdated" class="detail-value">-</span>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-close-device-view>Close</button>
            <a id="viewDeviceEditLink" href="<?php echo e(route('security.devices')); ?>" class="btn btn-primary" data-edit-template="<?php echo e(route('security.devices', ['edit_device' => '__ID__'], false)); ?>">Edit Device</a>
        </div>
    </div>
</div>

<div id="editDeviceModal" class="modal-backdrop" style="display:none;" role="presentation" aria-hidden="true">
    <div class="modal device-editor-modal" role="dialog" aria-modal="true" aria-labelledby="editDeviceTitle">
        <div class="modal-header">
            <span class="modal-title" id="editDeviceTitle">Edit Device Rule</span>
            <button type="button" class="modal-close-btn" aria-label="Close device editor" data-close-device-edit>&times;</button>
        </div>
        <form id="editDeviceForm" method="POST" data-action-template="<?php echo e(route('security.devices.update', ['device' => '__ID__'], false)); ?>">
            <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
            <input type="hidden" name="_device_form" value="edit">
            <input type="hidden" name="_device_id" value="">
            <div class="modal-body device-modal-body">
                <div class="form-group" style="margin:0;"><label>Device Name</label><input name="device_name" required></div>
                <div class="grid-2" style="gap:10px;">
                    <div class="form-group" style="margin:0;"><label>IP Address</label><input name="ip_address"></div>
                    <div class="form-group" style="margin:0;"><label>MAC Address</label><input name="mac_address"></div>
                </div>
                <div class="grid-2" style="gap:10px;">
                    <div class="form-group" style="margin:0;">
                        <label>Assigned User</label>
                        <select name="assigned_user_id">
                            <option value="">Any active user</option>
                            <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($user->id); ?>"><?php echo e($user->name); ?> (<?php echo e($user->username); ?>)</option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="form-group" style="margin:0;">
                        <label>Status</label>
                        <select name="status">
                            <option value="authorized">Authorized</option>
                            <option value="blocked">Blocked</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-close-device-edit>Cancel</button>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<?php echo app('Illuminate\Foundation\Vite')('resources/css/views/security-pages-1.css'); ?>
<?php echo app('Illuminate\Foundation\Vite')('resources/css/views/security-devices-1.css'); ?>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<?php echo app('Illuminate\Foundation\Vite')('resources/js/views/security-devices-1.js'); ?>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\acrylink_newbie\resources\views\security\devices.blade.php ENDPATH**/ ?>