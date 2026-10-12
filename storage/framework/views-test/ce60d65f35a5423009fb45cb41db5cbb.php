<?php $__env->startSection('title', 'User Management'); ?>
<?php $__env->startSection('page-label', 'User Management'); ?>

<?php $__env->startSection('content'); ?>
<?php
$roleVariants = ['admin' => 'accent', 'inventory_staff' => 'teal', 'cashier' => 'purple'];
$rolePerms  = [
    'Admin' => 'Full access: users, inventory, POS, security, audit logs, and device controls.',
    'Inventory Staff' => 'Inventory, suppliers, refunds, and stocks overview. Cannot manage users or security.',
    'Cashier' => 'POS terminal, owned transactions, receipts, and customer credit payments.',
];
$roleKeys = ['Admin' => 'admin', 'Inventory Staff' => 'inventory_staff', 'Cashier' => 'cashier'];
$editUsersData = $users->getCollection()
    ->reject(fn ($listedUser) => $listedUser->id === auth()->id())
    ->mapWithKeys(fn ($listedUser) => [(string) $listedUser->id => [
        'id' => $listedUser->id,
        'name' => $listedUser->name,
        'username' => $listedUser->username,
        'email' => $listedUser->email,
        'role_id' => $listedUser->role_id,
        'status' => $listedUser->status,
        'mfa_enabled' => (bool) $listedUser->mfa_enabled,
        'update_url' => route('users.update', $listedUser),
    ]]);
?>

<div class="users-page">
<?php if (isset($component)) { $__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => 'User Management','subtitle' => 'Control staff access, roles, and account status.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'User Management','subtitle' => 'Control staff access, roles, and account status.']); ?>
     <?php $__env->slot('actions', null, []); ?> 
        <a href="<?php echo e(route('users.create')); ?>" class="btn btn-primary" data-open-user-create>
            <?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
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
<?php endif; ?>
            Add User
        </a>
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

<section class="users-summary" aria-label="User account overview">
    <article class="card users-summary-card"><span class="users-summary-icon"><i class="bi bi-people" aria-hidden="true"></i></span><div><span>Total Staff</span><strong><?php echo e(number_format($userSummary['total'])); ?></strong><small>Registered user accounts</small></div></article>
    <article class="card users-summary-card"><span class="users-summary-icon is-success"><i class="bi bi-person-check" aria-hidden="true"></i></span><div><span>Active Accounts</span><strong><?php echo e(number_format($userSummary['active'])); ?></strong><small>Can currently sign in</small></div></article>
    <article class="card users-summary-card"><span class="users-summary-icon is-secure"><i class="bi bi-shield-check" aria-hidden="true"></i></span><div><span>MFA Protected</span><strong><?php echo e(number_format($userSummary['mfa'])); ?></strong><small>Require an email OTP</small></div></article>
</section>

<div class="card users-directory-card">
    <div class="users-table-head">
        <div>
            <h2>Staff accounts</h2>
            <p><?php echo e($users->total()); ?> registered user<?php echo e($users->total() === 1 ? '' : 's'); ?></p>
        </div>
        <div class="users-status-key" aria-label="Account status legend">
            <span><i class="users-status-dot is-active"></i> Active</span>
            <span><i class="users-status-dot is-inactive"></i> Inactive</span>
        </div>
    </div>
    <div class="table-wrap users-table-wrap" tabindex="0" role="region" aria-label="Staff accounts table">
        <table class="users-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Last Login</th>
                    <th>Status</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <?php $isCurrentUser = $u->id === auth()->id(); ?>
            <tr class="<?php echo e($isCurrentUser ? 'is-current-user' : ''); ?>">
                <td data-label="Staff member">
                    <div class="user-identity-cell">
                        <span class="user-list-avatar" aria-hidden="true"><?php echo e(strtoupper(substr($u->name, 0, 2))); ?></span>
                        <span>
                            <strong><?php echo e($u->name); ?></strong>
                            <?php if($isCurrentUser): ?><small>Signed-in account</small><?php endif; ?>
                        </span>
                    </div>
                </td>
                <td data-label="Username" class="td-mono"><?php echo e($u->username); ?></td>
                <td data-label="Email"><a class="user-email" href="mailto:<?php echo e($u->email); ?>"><?php echo e($u->email); ?></a></td>
                <td data-label="Role">
                    <?php if (isset($component)) { $__componentOriginal9ccaa90195d4e06c31c1de306aacdf44 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9ccaa90195d4e06c31c1de306aacdf44 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.tag','data' => ['variant' => $roleVariants[$u->roleColorKey()] ?? 'purple']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('tag'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($roleVariants[$u->roleColorKey()] ?? 'purple')]); ?><?php echo e($u->roleLabel()); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9ccaa90195d4e06c31c1de306aacdf44)): ?>
<?php $attributes = $__attributesOriginal9ccaa90195d4e06c31c1de306aacdf44; ?>
<?php unset($__attributesOriginal9ccaa90195d4e06c31c1de306aacdf44); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9ccaa90195d4e06c31c1de306aacdf44)): ?>
<?php $component = $__componentOriginal9ccaa90195d4e06c31c1de306aacdf44; ?>
<?php unset($__componentOriginal9ccaa90195d4e06c31c1de306aacdf44); ?>
<?php endif; ?>
                </td>
                <td data-label="Last login" class="td-muted td-nowrap"><?php echo e($u->last_login ? $u->last_login->format('M d, Y h:i A') : 'Never signed in'); ?></td>
                <td data-label="Status">
                    <span class="status status-<?php echo e($u->status); ?>"><?php echo e(ucfirst($u->status)); ?></span>
                    <?php if($u->mfa_enabled): ?><span class="user-mfa" title="Multi-factor authentication enabled">MFA</span><?php endif; ?>
                </td>
                <td data-label="Actions">
                    <div class="table-actions table-actions--center">
                        <a href="<?php echo e(route('users.show', $u)); ?>" class="btn btn-secondary btn-sm user-action-btn" aria-label="View <?php echo e($u->name); ?>" title="View user">
                            <i class="bi bi-eye" aria-hidden="true"></i>
                        </a>
                        <?php if($isCurrentUser): ?>
                        <button type="button" class="btn btn-secondary btn-sm user-action-btn" disabled aria-label="Editing your signed-in administrator account is disabled" title="You cannot edit your signed-in administrator account">
                            <i class="bi bi-pencil" aria-hidden="true"></i>
                        </button>
                        <?php else: ?>
                        <a href="<?php echo e(route('users.edit', $u)); ?>" class="btn btn-secondary btn-sm user-action-btn" aria-label="Edit <?php echo e($u->name); ?>" title="Edit user" data-open-user-edit data-user-id="<?php echo e($u->id); ?>">
                            <i class="bi bi-pencil" aria-hidden="true"></i>
                        </a>
                        <form method="POST" action="<?php echo e(route('users.toggle', $u)); ?>">
                            <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                            <button type="submit" class="btn btn-sm user-status-action <?php echo e($u->status==='active' ? 'btn-danger' : 'btn-success'); ?>" aria-label="<?php echo e($u->status==='active' ? 'Deactivate' : 'Activate'); ?> <?php echo e($u->name); ?>">
                                <i class="bi <?php echo e($u->status === 'active' ? 'bi-person-x' : 'bi-person-check'); ?>" aria-hidden="true"></i>
                                <?php echo e($u->status === 'active' ? 'Deactivate' : 'Activate'); ?>

                            </button>
                        </form>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <?php if (isset($component)) { $__componentOriginal074a021b9d42f490272b5eefda63257c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal074a021b9d42f490272b5eefda63257c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.empty-state','data' => ['colspan' => '7','title' => 'No users found','message' => 'Add staff accounts to control who can access POS, inventory, and admin tools.','icon' => 'users']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['colspan' => '7','title' => 'No users found','message' => 'Add staff accounts to control who can access POS, inventory, and admin tools.','icon' => 'users']); ?>
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
    <?php if($users->hasPages()): ?>
    <div class="users-pagination">
        <span>Showing <?php echo e($users->firstItem()); ?>-<?php echo e($users->lastItem()); ?> of <?php echo e($users->total()); ?></span>
        <?php echo e($users->links()); ?>

    </div>
    <?php endif; ?>
</div>

<div class="card users-roles-card">
    <div class="card-header"><span class="card-title">Role Permissions</span></div>
    <div class="card-body">
        <div class="grid-3">
            <?php $__currentLoopData = $rolePerms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role => $desc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php $key = $roleKeys[$role]; ?>
            <div class="role-card role-card--<?php echo e($key); ?>">
                <div class="role-card__title role-card__title--<?php echo e($key); ?>"><?php echo e($role); ?></div>
                <div class="role-card__copy"><?php echo e($desc); ?></div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
</div>

<div id="createUserModal" class="modal-backdrop user-modal-backdrop" style="display:none" role="presentation" aria-hidden="true" data-open-on-error="<?php echo e(old('_user_create') === '1' && $errors->any() ? 'true' : 'false'); ?>">
    <div class="modal user-create-modal" role="dialog" aria-modal="true" aria-labelledby="createUserTitle" aria-describedby="createUserDescription">
        <div class="modal-header user-modal-header">
            <div><span class="modal-title" id="createUserTitle">Add Staff Account</span><span id="createUserDescription">Create sign-in credentials and assign the appropriate access level.</span></div>
            <button type="button" class="modal-close-btn" data-close-user-create aria-label="Close user form">&times;</button>
        </div>
        <form method="POST" action="<?php echo e(route('users.store')); ?>">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="_user_create" value="1">
            <div class="modal-body user-modal-body">
                <?php echo $__env->make('users._form', ['user' => null, 'isAdd' => true], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>
            <div class="modal-footer user-modal-footer">
                <button type="button" class="btn btn-secondary" data-close-user-create>Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="bi bi-person-plus" aria-hidden="true"></i> Create User</button>
            </div>
        </form>
    </div>
</div>

<script type="application/json" id="userEditData"><?php echo json_encode($editUsersData, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT, 512) ?></script>
<div id="editUserModal" class="modal-backdrop user-modal-backdrop" style="display:none" role="presentation" aria-hidden="true" data-open-on-error="<?php echo e(old('_user_edit') === '1' && $errors->any() ? 'true' : 'false'); ?>" data-error-user-id="<?php echo e(old('_user_edit_id')); ?>">
    <div class="modal user-create-modal user-edit-modal" role="dialog" aria-modal="true" aria-labelledby="editUserTitle" aria-describedby="editUserDescription">
        <div class="modal-header user-modal-header">
            <div class="user-modal-heading">
                <span class="user-modal-avatar" data-edit-user-avatar aria-hidden="true">U</span>
                <span><span class="modal-title" id="editUserTitle">Edit Staff Account</span><span id="editUserDescription">Update account details, access level, and sign-in protection.</span></span>
            </div>
            <button type="button" class="modal-close-btn" data-close-user-edit aria-label="Close edit user form">&times;</button>
        </div>
        <form method="POST" action="" data-user-edit-form>
            <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
            <input type="hidden" name="_user_edit" value="1">
            <input type="hidden" name="_user_edit_id" value="<?php echo e(old('_user_edit_id')); ?>">
            <div class="modal-body user-modal-body">
                <div class="editor-body">
                    <section class="editor-section">
                        <div class="editor-section-head"><div class="editor-section-title">Staff Details</div><div class="editor-section-copy">Keep the staff member's working identity and contact email accurate.</div></div>
                        <div class="grid-2">
                            <div class="form-group"><label for="editUserName">Full Name *</label><input id="editUserName" type="text" name="name" value="<?php echo e(old('_user_edit') === '1' ? old('name') : ''); ?>" autocomplete="name" required></div>
                            <div class="form-group"><label for="editUserUsername">Username *</label><input id="editUserUsername" type="text" name="username" value="<?php echo e(old('_user_edit') === '1' ? old('username') : ''); ?>" autocomplete="username" required></div>
                        </div>
                        <div class="form-group"><label for="editUserEmail">Email *</label><input id="editUserEmail" type="email" name="email" value="<?php echo e(old('_user_edit') === '1' ? old('email') : ''); ?>" autocomplete="email" required></div>
                    </section>
                    <section class="editor-section">
                        <div class="editor-section-head"><div class="editor-section-title">Access & Security</div><div class="editor-section-copy">Choose permissions, account availability, and optional login protection.</div></div>
                        <div class="grid-2">
                            <div class="form-group"><label for="editUserRole">Role *</label><select id="editUserRole" name="role_id" required><?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($role->role_id); ?>" <?php if(old('_user_edit') === '1' && (int) old('role_id') === $role->role_id): echo 'selected'; endif; ?>><?php echo e($role->role_name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
                            <div class="form-group"><label for="editUserStatus">Status *</label><select id="editUserStatus" name="status" required><option value="active" <?php if(old('_user_edit') === '1' && old('status') === 'active'): echo 'selected'; endif; ?>>Active</option><option value="inactive" <?php if(old('_user_edit') === '1' && old('status') === 'inactive'): echo 'selected'; endif; ?>>Inactive</option></select></div>
                        </div>
                        <div class="form-group"><input type="hidden" name="mfa_enabled" value="0"><label class="editor-check"><input type="checkbox" name="mfa_enabled" value="1" <?php if(old('_user_edit') === '1' && old('mfa_enabled')): echo 'checked'; endif; ?>><span><strong>Require email OTP on login</strong><small>Add an extra verification step for this account.</small></span></label></div>
                        <div class="grid-2 user-password-grid">
                            <div class="form-group"><label for="editUserPassword">New Password</label><div class="editor-password-wrap"><input id="editUserPassword" type="password" name="password" placeholder="Leave blank to keep current password" autocomplete="new-password"><button type="button" class="editor-password-toggle" data-editor-password-toggle aria-controls="editUserPassword" aria-label="Show new password" aria-pressed="false"><i class="bi bi-eye" aria-hidden="true"></i></button></div></div>
                            <div class="form-group"><label for="editUserPasswordConfirmation">Retype New Password</label><div class="editor-password-wrap"><input id="editUserPasswordConfirmation" type="password" name="password_confirmation" placeholder="Type the same password again" autocomplete="new-password"><button type="button" class="editor-password-toggle" data-editor-password-toggle aria-controls="editUserPasswordConfirmation" aria-label="Show password confirmation" aria-pressed="false"><i class="bi bi-eye" aria-hidden="true"></i></button></div></div>
                        </div>
                        <div class="form-help user-password-help"><i class="bi bi-info-circle" aria-hidden="true"></i> Password changes are optional. Leave both fields empty to keep the current password.</div>
                    </section>
                </div>
            </div>
            <div class="modal-footer user-modal-footer"><button type="button" class="btn btn-secondary" data-close-user-edit>Cancel</button><button type="submit" class="btn btn-primary"><i class="bi bi-check-lg" aria-hidden="true"></i> Save Changes</button></div>
        </form>
    </div>
</div>
</div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<?php echo app('Illuminate\Foundation\Vite')('resources/css/views/users-index-1.css'); ?>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<?php echo app('Illuminate\Foundation\Vite')('resources/js/views/users-index-1.js'); ?>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\acrylink_newbie\resources\views\users\index.blade.php ENDPATH**/ ?>