<?php $__env->startSection('title', $user->name); ?>
<?php $__env->startSection('page-label', 'User Management / View User'); ?>

<?php
    $roleColor = [
        'admin' => 'accent',
        'inventory_staff' => 'teal',
        'cashier' => 'purple',
    ][$user->roleColorKey()] ?? 'purple';

    $employeeId = $user->employee_id ?? '-';
    $phone = $user->phone ?? '-';
    $lastLoginIp = $user->last_login_ip ?? '-';
    $failedLogins = $user->failed_login_attempts ?? 0;

    // Pass $loginAttempts from the controller if you have a login-attempts table.
    $loginAttempts = $loginAttempts ?? collect();
?>

<?php $__env->startSection('content'); ?>
<div class="user-show-page">
    <a href="<?php echo e(route('users.index')); ?>" class="editor-breadcrumb">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
            stroke-linecap="round" stroke-linejoin="round">
            <path d="M19 12H5"/><path d="m12 19-7-7 7-7"/>
        </svg>
        Back to Users
    </a>

    <div class="user-show-grid">

        <section class="card user-profile-card">
            <div class="user-profile-avatar-large" aria-hidden="true">
                <?php echo e(strtoupper(substr($user->name, 0, 1))); ?>

            </div>

            <h1><?php echo e($user->name); ?></h1>

            <p class="user-profile-email"><?php echo e($user->email); ?></p>

            <?php if (isset($component)) { $__componentOriginal9ccaa90195d4e06c31c1de306aacdf44 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9ccaa90195d4e06c31c1de306aacdf44 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.tag','data' => ['variant' => $roleColor]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('tag'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($roleColor)]); ?>
                <?php echo e($user->roleLabel()); ?>

             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9ccaa90195d4e06c31c1de306aacdf44)): ?>
<?php $attributes = $__attributesOriginal9ccaa90195d4e06c31c1de306aacdf44; ?>
<?php unset($__attributesOriginal9ccaa90195d4e06c31c1de306aacdf44); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9ccaa90195d4e06c31c1de306aacdf44)): ?>
<?php $component = $__componentOriginal9ccaa90195d4e06c31c1de306aacdf44; ?>
<?php unset($__componentOriginal9ccaa90195d4e06c31c1de306aacdf44); ?>
<?php endif; ?>

            <div class="user-profile-status">
                <span class="status status-<?php echo e($user->status); ?>">
                    <?php echo e(ucfirst($user->status)); ?>

                </span>
            </div>

            <div class="user-profile-actions">
                <?php if($user->is(auth()->user())): ?>
                    <p class="form-help">Signed-in administrator protected: you cannot edit this account while using it.</p>
                    <button type="button" class="btn btn-secondary" disabled
                            title="You cannot edit your signed-in administrator account">
                        <i class="bi bi-pencil" aria-hidden="true"></i>
                        Edit disabled
                    </button>
                <?php else: ?>
                    <?php if(auth()->user()->canEdit()): ?>
                    <a href="<?php echo e(route('users.edit', $user)); ?>" class="btn btn-primary">
                        <i class="bi bi-pencil" aria-hidden="true"></i>
                        Edit
                    </a>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </section>

        <div class="user-show-right">

            <section class="card user-account-card">
                <div class="card-header">
                    <span class="card-title">Account Details</span>
                </div>

                <div class="card-body user-account-details">
                    <div class="user-detail-row">
                        <span>Username</span>
                        <strong class="td-mono"><?php echo e($user->username); ?></strong>
                    </div>

                    <div class="user-detail-row">
                        <span>Employee ID</span>
                        <strong><?php echo e($employeeId); ?></strong>
                    </div>

                    <div class="user-detail-row">
                        <span>Phone</span>
                        <strong><?php echo e($phone); ?></strong>
                    </div>

                    <div class="user-detail-row">
                        <span>Status</span>
                        <strong><?php echo e(ucfirst($user->status)); ?></strong>
                    </div>

                    <div class="user-detail-row">
                        <span>MFA</span>
                        <strong><?php echo e($user->mfa_enabled ? 'Enabled' : 'Disabled'); ?></strong>
                    </div>

                    <div class="user-detail-row">
                        <span>Last Login</span>
                        <strong><?php echo e($user->last_login?->format('M d, Y h:i A') ?? 'Never'); ?></strong>
                    </div>

                    <div class="user-detail-row">
                        <span>Last Login IP</span>
                        <strong><?php echo e($lastLoginIp); ?></strong>
                    </div>

                    <div class="user-detail-row">
                        <span>Failed Logins</span>
                        <strong><?php echo e(number_format((int) $failedLogins)); ?></strong>
                    </div>
                </div>
            </section>

            <section class="card user-login-card">
                <div class="card-header">
                    <span class="card-title">Recent Login Attempts</span>
                </div>

                <div class="card-body user-login-attempts">
                    <?php if($loginAttempts->isEmpty()): ?>
                        <div class="user-login-empty">
                            <i class="bi bi-shield-check" aria-hidden="true"></i>
                            <span>No recent login attempts</span>
                        </div>
                    <?php else: ?>
                        <?php $__currentLoopData = $loginAttempts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attempt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="user-login-row">
                            <div class="user-login-result">
                                <div class="user-login-result">
                                    <span class="login-result <?php echo e($attempt->attempt_status === 'success' ? 'is-success' : 'is-failed'); ?>">
                                        <?php echo e(ucfirst($attempt->attempt_status)); ?>

                                    </span>
                                    <span>from <?php echo e($attempt->ip_address ?? '-'); ?></span>
                                </div>
                            </div>

                            <span class="user-login-time">
                                <?php echo e($attempt->attempted_at?->diffForHumans() ?? '-'); ?>

                            </span>
                        </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php endif; ?>
                </div>
            </section>

        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<?php echo app('Illuminate\Foundation\Vite')('resources/css/views/users-show-2.css'); ?>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\acrylink_newbie\resources\views\users\show.blade.php ENDPATH**/ ?>