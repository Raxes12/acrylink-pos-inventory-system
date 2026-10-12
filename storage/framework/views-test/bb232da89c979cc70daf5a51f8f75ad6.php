
<?php $__env->startSection('title', 'Customers'); ?>
<?php $__env->startSection('page-label', 'Customers'); ?>

<?php $__env->startSection('content'); ?>
<div class="customers-page">
<?php if (isset($component)) { $__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => 'Customers','subtitle' => 'Manage customer profiles, account access, and credit terms from one place.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Customers','subtitle' => 'Manage customer profiles, account access, and credit terms from one place.']); ?>
     <?php $__env->slot('actions', null, []); ?> <a href="<?php echo e(route('accounts-receivable.index')); ?>" class="btn btn-secondary">Accounts Receivable</a>
        <a href="<?php echo e(route('customers.create')); ?>" class="btn btn-primary" data-open-customer-create>
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
            Add Customer
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

<form method="GET" action="<?php echo e(route('customers.index')); ?>" class="filter-bar customer-filter" data-live-filter>
    <div class="customer-search-wrap"><i class="bi bi-search" aria-hidden="true"></i><input type="search" name="search" maxlength="150" autocomplete="off" value="<?php echo e($filters['search'] ?? ''); ?>" placeholder="Search name, code, phone, or email..." aria-label="Search customers"></div>
    <select name="status" aria-label="Customer status">
        <option value="">All statuses</option>
        <option value="active" <?php if(($filters['status'] ?? '') === 'active'): echo 'selected'; endif; ?>>Active</option>
        <option value="inactive" <?php if(($filters['status'] ?? '') === 'inactive'): echo 'selected'; endif; ?>>Inactive</option>
    </select>
    <select name="credit_enabled" aria-label="Credit eligibility">
        <option value="">All credit settings</option>
        <option value="1" <?php if(($filters['credit_enabled'] ?? '') === '1'): echo 'selected'; endif; ?>>Credit enabled</option>
        <option value="0" <?php if(($filters['credit_enabled'] ?? '') === '0'): echo 'selected'; endif; ?>>Credit disabled</option>
    </select>
    <?php if(filled($filters['search'] ?? null) || filled($filters['status'] ?? null) || isset($filters['credit_enabled'])): ?>
    <a class="btn btn-secondary customer-filter-clear" href="<?php echo e(route('customers.index')); ?>" data-live-filter-clear><i class="bi bi-x-lg" aria-hidden="true"></i> Clear filters</a>
    <?php endif; ?>
    <span class="live-filter-status" data-live-filter-status role="status" aria-live="polite"></span>
</form>

<div class="card customer-table-card" data-live-results>
    <div class="card-header"><div><span class="card-title">Customer Directory</span><span class="customer-card-caption">Contact information and credit availability.</span></div><span class="tag tag--accent"><?php echo e($customers->total()); ?> customers</span></div>
    <div class="table-wrap" tabindex="0" role="region" aria-label="Customers table">
        <table>
            <thead><tr><th>Code / Name</th><th>Contact</th><th>Status</th><th>Credit</th><th class="text-right">Credit limit</th><th>Term</th><th>Actions</th></tr></thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $customers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $customer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td data-label="Customer"><div class="customer-directory-identity"><span class="customer-directory-avatar" aria-hidden="true"><?php echo e(strtoupper(substr($customer->customer_name, 0, 1))); ?></span><div class="td-stack"><strong><?php echo e($customer->customer_name); ?></strong><span class="td-mono"><?php echo e($customer->customer_code); ?></span></div></div></td>
                    <td data-label="Contact"><div class="td-stack customer-contact"><span><i class="bi bi-telephone" aria-hidden="true"></i><?php echo e($customer->phone ?: '—'); ?></span><span><i class="bi bi-envelope" aria-hidden="true"></i><?php echo e($customer->email ?: '—'); ?></span></div></td>
                    <td data-label="Status"><?php if (isset($component)) { $__componentOriginal9ccaa90195d4e06c31c1de306aacdf44 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9ccaa90195d4e06c31c1de306aacdf44 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.tag','data' => ['variant' => $customer->status === 'active' ? 'success' : 'muted']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('tag'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($customer->status === 'active' ? 'success' : 'muted')]); ?><?php echo e(ucfirst($customer->status)); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9ccaa90195d4e06c31c1de306aacdf44)): ?>
<?php $attributes = $__attributesOriginal9ccaa90195d4e06c31c1de306aacdf44; ?>
<?php unset($__attributesOriginal9ccaa90195d4e06c31c1de306aacdf44); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9ccaa90195d4e06c31c1de306aacdf44)): ?>
<?php $component = $__componentOriginal9ccaa90195d4e06c31c1de306aacdf44; ?>
<?php unset($__componentOriginal9ccaa90195d4e06c31c1de306aacdf44); ?>
<?php endif; ?></td>
                    <td data-label="Credit"><span class="tag <?php echo e($customer->credit_enabled ? 'tag--success' : 'tag--muted'); ?>"><?php echo e($customer->credit_enabled ? 'Enabled' : 'Disabled'); ?></span></td>
                    <td data-label="Credit limit" class="text-right customer-credit-limit">&#8369;<?php echo e(number_format((float) $customer->credit_limit, 2)); ?></td>
                    <td data-label="Term"><?php echo e($customer->default_credit_term_days); ?> days</td>
                    <td data-label="Actions">
                        <div class="table-actions customer-actions">
                            <a class="btn btn-secondary btn-sm user-action-btn" aria-label="View <?php echo e($customer->customer_name); ?>" title="View customer" href="<?php echo e(route('customers.show', $customer)); ?>">
                                <i class="bi bi-eye" aria-hidden="true"></i>
                            </a>
                            <a class="btn btn-secondary btn-sm user-action-btn" aria-label="Edit <?php echo e($customer->customer_name); ?>" title="Edit customer" href="<?php echo e(route('customers.edit', $customer)); ?>">
                                <i class="bi bi-pencil" aria-hidden="true"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <?php if (isset($component)) { $__componentOriginal074a021b9d42f490272b5eefda63257c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal074a021b9d42f490272b5eefda63257c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.empty-state','data' => ['colspan' => '7','title' => 'No customers found','message' => 'Add a customer or adjust your search filters.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['colspan' => '7','title' => 'No customers found','message' => 'Add a customer or adjust your search filters.']); ?>
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
            Showing <?php echo e($customers->firstItem() ?? 0); ?>-<?php echo e($customers->lastItem() ?? 0); ?> of <?php echo e($customers->total()); ?> customers
        </div>
        <?php if($customers->hasPages()): ?>
        <?php
            $currentPage = $customers->currentPage();
            $lastPage = $customers->lastPage();
            $pages = collect([1, $currentPage - 1, $currentPage, $currentPage + 1, $lastPage])
                ->filter(fn ($page) => $page >= 1 && $page <= $lastPage)
                ->unique()
                ->sort()
                ->values();
            $previousPage = null;
        ?>
        <nav class="rules-pagination" aria-label="Stock variant pagination">
            <?php if($customers->onFirstPage()): ?>
                <span class="rules-page is-disabled" aria-disabled="true">Previous</span>
            <?php else: ?>
                <a class="rules-page" href="<?php echo e($customers->previousPageUrl()); ?>" rel="prev">Previous</a>
            <?php endif; ?>

            <?php $__currentLoopData = $pages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php if($previousPage && $page > $previousPage + 1): ?>
                    <span class="rules-page rules-page--gap" aria-hidden="true">...</span>
                <?php endif; ?>

                <?php if($page === $currentPage): ?>
                    <span class="rules-page is-active" aria-current="page"><?php echo e($page); ?></span>
                <?php else: ?>
                    <a class="rules-page" href="<?php echo e($customers->url($page)); ?>"><?php echo e($page); ?></a>
                <?php endif; ?>

                <?php $previousPage = $page; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

            <?php if($customers->hasMorePages()): ?>
                <a class="rules-page" href="<?php echo e($customers->nextPageUrl()); ?>" rel="next">Next</a>
            <?php else: ?>
                <span class="rules-page is-disabled" aria-disabled="true">Next</span>
            <?php endif; ?>
        </nav>
        <?php endif; ?>
    </div>
</div>

<div id="createCustomerModal" class="modal-backdrop customer-modal-backdrop" style="display:none;" role="presentation" aria-hidden="true" data-open-on-error="<?php echo e($errors->any() ? 'true' : 'false'); ?>">
    <div class="modal customer-create-modal" role="dialog" aria-modal="true" aria-labelledby="createCustomerTitle" aria-describedby="createCustomerDescription">
        <div class="modal-header customer-modal-header">
            <div><span class="modal-title" id="createCustomerTitle">Add Customer</span><span class="customer-modal-caption" id="createCustomerDescription">Create a customer profile and configure credit access.</span></div>
            <button type="button" class="modal-close-btn" data-close-customer-create aria-label="Close customer form">&times;</button>
        </div>
        <form method="POST" action="<?php echo e(route('customers.store')); ?>" class="customer-create-form">
            <?php echo csrf_field(); ?>
            <div class="modal-body customer-modal-body">
                <section class="customer-modal-section">
                    <div class="customer-modal-section-head"><span class="customer-modal-step">1</span><div><strong>Customer details</strong><span>Basic contact information used on sales and account records.</span></div></div>
                    <div class="customer-modal-fields">
                        <div class="form-group customer-field-wide"><label for="modal_customer_name">Customer Name <span aria-hidden="true">*</span></label><input id="modal_customer_name" name="customer_name" value="<?php echo e(old('customer_name')); ?>" maxlength="150" required autocomplete="name" placeholder="Full customer or business name"><div class="form-help">A customer code is generated automatically after saving.</div></div>
                        <div class="form-group"><label for="modal_phone">Phone</label><input id="modal_phone" type="tel" name="phone" maxlength="30" autocomplete="tel" value="<?php echo e(old('phone')); ?>" placeholder="09XX XXX XXXX"></div>
                        <div class="form-group"><label for="modal_email">Email</label><input id="modal_email" type="email" name="email" maxlength="255" autocomplete="email" value="<?php echo e(old('email')); ?>" placeholder="customer@example.com"></div>
                        <div class="form-group customer-field-wide"><label for="modal_address">Address</label><textarea id="modal_address" name="address" rows="2" maxlength="2000" placeholder="Street, barangay, city, and province"><?php echo e(old('address')); ?></textarea></div>
                    </div>
                </section>
                <section class="customer-modal-section">
                    <div class="customer-modal-section-head"><span class="customer-modal-step">2</span><div><strong>Account and credit</strong><span>Set purchasing access and default payment terms.</span></div></div>
                    <div class="customer-modal-fields">
                        <div class="form-group"><label for="modal_status">Account Status <span aria-hidden="true">*</span></label><select id="modal_status" name="status" required><option value="active" <?php if(old('status', 'active') === 'active'): echo 'selected'; endif; ?>>Active</option><option value="inactive" <?php if(old('status') === 'inactive'): echo 'selected'; endif; ?>>Inactive</option></select></div>
                        <div class="form-group"><label for="modal_credit_enabled">Customer Credit <span aria-hidden="true">*</span></label><select id="modal_credit_enabled" name="credit_enabled" required><option value="0" <?php if(old('credit_enabled', '0') == '0'): echo 'selected'; endif; ?>>Disabled</option><option value="1" <?php if(old('credit_enabled') == '1'): echo 'selected'; endif; ?>>Enabled</option></select></div>
                        <div class="form-group"><label for="modal_credit_limit">Credit Limit (PHP) <span aria-hidden="true">*</span></label><input id="modal_credit_limit" type="number" name="credit_limit" min="0" max="9999999999.99" step="0.01" value="<?php echo e(old('credit_limit', '0.00')); ?>" required><div class="form-help">Maximum unpaid balance.</div></div>
                        <div class="form-group"><label for="modal_credit_term">Default Term <span aria-hidden="true">*</span></label><div class="customer-term-input"><input id="modal_credit_term" type="number" name="default_credit_term_days" min="1" max="65535" step="1" value="<?php echo e(old('default_credit_term_days', 30)); ?>" required><span>days</span></div><div class="form-help">Used for new credit-sale due dates.</div></div>
                    </div>
                    <div class="customer-credit-note"><i class="bi bi-info-circle" aria-hidden="true"></i><span>Credit can be disabled now and enabled later from the customer edit page.</span></div>
                </section>
            </div>
            <div class="modal-footer customer-modal-footer"><button type="button" class="btn btn-secondary" data-close-customer-create>Cancel</button><button type="submit" class="btn btn-primary"><i class="bi bi-person-plus" aria-hidden="true"></i> Create Customer</button></div>
        </form>
    </div>
</div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<?php echo app('Illuminate\Foundation\Vite')('resources/css/views/customers-1.css'); ?>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<?php echo app('Illuminate\Foundation\Vite')('resources/js/views/customers-index-1.js'); ?>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\acrylink_newbie\resources\views\customers\index.blade.php ENDPATH**/ ?>