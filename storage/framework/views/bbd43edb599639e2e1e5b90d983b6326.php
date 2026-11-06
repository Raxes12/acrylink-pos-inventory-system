<?php $__env->startSection('title', 'Customer Details'); ?>
<?php $__env->startSection('page-label', 'Customers / Details'); ?>

<?php $__env->startSection('content'); ?>
<?php
    $limit = (float) $creditSummary['credit_limit'];
    $outstanding = (float) $creditSummary['current_outstanding'];
    $available = (float) $creditSummary['available_credit'];
    $usage = $limit > 0 ? min(100, round(($outstanding / $limit) * 100)) : 0;
    $initials = collect(preg_split('/\s+/', trim($customer->customer_name)))->filter()->take(2)->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))->join('');
?>
<div class="customer-profile-page">
    <a href="<?php echo e(route('customers.index')); ?>" class="editor-breadcrumb">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
            stroke-linecap="round" stroke-linejoin="round">
            <path d="M19 12H5"/><path d="m12 19-7-7 7-7"/>
        </svg>
        Back to Customers
    </a>
    <div class="customer-profile-hero">
        <div class="customer-profile-identity">
            <span class="customer-profile-avatar" aria-hidden="true"><?php echo e($initials ?: '?'); ?></span>
            <div class="customer-profile-copy">
                <div class="customer-profile-eyebrow"><?php echo e($customer->customer_code); ?></div>
                <h1><?php echo e($customer->customer_name); ?></h1>
                <div class="customer-profile-badges">
                    <span class="tag <?php echo e($customer->status === 'active' ? 'tag--success' : 'tag--muted'); ?>"><?php echo e(ucfirst($customer->status)); ?></span>
                    <span class="tag <?php echo e($customer->credit_enabled ? 'tag--accent' : 'tag--muted'); ?>">Credit <?php echo e($customer->credit_enabled ? 'enabled' : 'disabled'); ?></span>
                </div>
                <div class="customer-profile-meta" aria-label="Customer contact summary">
                    <?php if($customer->phone): ?><span><i class="bi bi-telephone" aria-hidden="true"></i><?php echo e($customer->phone); ?></span><?php endif; ?>
                    <?php if($customer->email): ?><span><i class="bi bi-envelope" aria-hidden="true"></i><?php echo e($customer->email); ?></span><?php endif; ?>
                </div>
            </div>
        </div>
        <div class="page-actions">
            <a class="btn btn-secondary" href="<?php echo e(route('accounts-receivable.customers.show', $customer)); ?>"><i class="bi bi-journal-text" aria-hidden="true"></i>Receivable account</a>
            <a class="btn btn-primary" href="<?php echo e(route('customers.edit', $customer)); ?>"><i class="bi bi-pencil" aria-hidden="true"></i>Edit customer</a>
        </div>
    </div>

    <section class="customer-credit-overview section-spaced" aria-labelledby="customerCreditOverviewTitle">
        <div class="customer-section-heading">
            <div><h2 id="customerCreditOverviewTitle">Credit overview</h2><p>Current credit position and purchasing capacity.</p></div>
            <span class="customer-credit-state"><i class="bi <?php echo e($customer->credit_enabled ? 'bi-check-circle' : 'bi-dash-circle'); ?>" aria-hidden="true"></i>Credit <?php echo e($customer->credit_enabled ? 'enabled' : 'disabled'); ?></span>
        </div>
        <div class="grid-4 customer-credit-metrics">
            <article class="card metric-card customer-credit-metric">
                <div class="customer-metric-head"><span class="customer-metric-icon"><i class="bi bi-wallet2" aria-hidden="true"></i></span><span class="metric-label">Credit Limit</span></div>
                <div class="metric-value">&#8369;<?php echo e(number_format($limit, 2)); ?></div><div class="metric-note">Maximum approved credit</div>
            </article>
            <article class="card metric-card customer-credit-metric">
                <div class="customer-metric-head"><span class="customer-metric-icon customer-metric-icon--attention"><i class="bi bi-receipt" aria-hidden="true"></i></span><span class="metric-label">Outstanding Balance</span></div>
                <div class="metric-value">&#8369;<?php echo e(number_format($outstanding, 2)); ?></div><div class="metric-note">Still due from this customer</div>
            </article>
            <article class="card metric-card customer-credit-metric">
                <div class="customer-metric-head"><span class="customer-metric-icon customer-metric-icon--available"><i class="bi bi-credit-card" aria-hidden="true"></i></span><span class="metric-label">Available Credit</span></div>
                <div class="metric-value">&#8369;<?php echo e(number_format($available, 2)); ?></div><div class="metric-note">Available for new credit sales</div>
            </article>
            <article class="card metric-card customer-credit-metric customer-credit-metric--usage">
                <div class="customer-metric-head"><span class="customer-metric-icon"><i class="bi bi-pie-chart" aria-hidden="true"></i></span><span class="metric-label">Credit Usage</span></div>
                <div class="customer-usage-line"><div class="metric-value"><?php echo e($usage); ?>%</div><span><?php echo e($usage >= 80 ? 'Near limit' : ($usage > 0 ? 'In use' : 'Unused')); ?></span></div>
                <div class="customer-credit-progress" role="progressbar" aria-label="Credit usage" aria-valuenow="<?php echo e($usage); ?>" aria-valuemin="0" aria-valuemax="100"><span style="width:<?php echo e($usage); ?>%"></span></div>
                <div class="metric-note">&#8369;<?php echo e(number_format($outstanding, 2)); ?> used of &#8369;<?php echo e(number_format($limit, 2)); ?></div>
            </article>
        </div>
    </section>

    <div class="customer-overview-grid section-spaced">
        <section class="card customer-info-card">
            <div class="card-header">
                <span class="card-title">Contact Information</span>
            </div><dl class="customer-detail-list">
                <dt>Phone</dt><dd><?php echo e($customer->phone ?: '—'); ?></dd>
                <dt>Email</dt><dd><?php echo e($customer->email ?: '—'); ?></dd>
                <dt>Address</dt><dd><?php echo e($customer->address ?: '—'); ?></dd>
            </dl>
        </section>
        <section class="card customer-info-card">
            <div class="card-header">
                <span class="card-title">Account Settings</span>
            </div>
            <dl class="customer-detail-list">
                <dt>Status</dt>
                <dd>
                    <span class="tag <?php echo e($customer->status === 'active' ? 'tag--success' : 'tag--muted'); ?>">
                        <?php echo e(ucfirst($customer->status)); ?>

                    </span>
                </dd>
                <dt>Credit Access</dt>
                <dd>
                    <span class="tag <?php echo e($customer->credit_enabled ? 'tag--success' : 'tag--muted'); ?>">
                        <?php echo e($customer->credit_enabled ? 'Enabled' : 'Disabled'); ?>

                    </span>
                </dd>
                <dt>Default Term</dt>
                <dd><?php echo e($customer->default_credit_term_days); ?> days</dd>
            </dl>
            <div class="customer-account-action">
                <form method="POST" action="<?php echo e(route('customers.status', $customer)); ?>" data-confirm data-confirm-title="Change customer status" data-confirm-message="<?php echo e($customer->status === 'active' ? 'Deactivate' : 'Activate'); ?> <?php echo e($customer->customer_name); ?>?" data-confirm-button="Confirm">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('PATCH'); ?>
                    <input type="hidden" name="status" value="<?php echo e($customer->status === 'active' ? 'inactive' : 'active'); ?>">
                    <button type="submit" class="btn btn-secondary btn-sm">
                        <?php echo e($customer->status === 'active' ? 'Deactivate Customer' : 'Activate Customer'); ?>

                    </button>
                </form>
            </div>
        </section>
    </div>

    <section class="customer-activity section-spaced" data-customer-history aria-labelledby="customerActivityTitle">
        <header class="customer-activity-heading">
            <div>
                <span class="customer-activity-kicker">Account activity</span>
                <h2 id="customerActivityTitle">Credit and payment history</h2>
                <p>Switch between credit sales and collections without scrolling through both lists.</p>
            </div>
            <span class="customer-activity-total"><?php echo e(number_format($creditTransactions->total() + $payments->total())); ?> total records</span>
        </header>

        <nav class="customer-history-tabs" role="tablist" aria-label="Customer account history">
            <a
                href="<?php echo e(route('customers.show', ['customer' => $customer, 'tab' => 'credit'])); ?>"
                id="customerCreditTab"
                class="customer-history-tab <?php echo e($activeHistoryTab === 'credit' ? 'is-active' : ''); ?>"
                role="tab"
                aria-selected="<?php echo e($activeHistoryTab === 'credit' ? 'true' : 'false'); ?>"
                aria-controls="customerCreditPanel"
                tabindex="<?php echo e($activeHistoryTab === 'credit' ? '0' : '-1'); ?>"
                data-customer-history-tab="credit"
            >
                <span class="customer-tab-icon"><i class="bi bi-receipt" aria-hidden="true"></i></span>
                <span class="customer-tab-copy"><strong>Credit Transactions</strong><small>Sales, balances, and due dates</small></span>
                <span class="customer-tab-count"><?php echo e(number_format($creditTransactions->total())); ?></span>
            </a>
            <a
                href="<?php echo e(route('customers.show', ['customer' => $customer, 'tab' => 'payments'])); ?>"
                id="customerPaymentsTab"
                class="customer-history-tab <?php echo e($activeHistoryTab === 'payments' ? 'is-active' : ''); ?>"
                role="tab"
                aria-selected="<?php echo e($activeHistoryTab === 'payments' ? 'true' : 'false'); ?>"
                aria-controls="customerPaymentsPanel"
                tabindex="<?php echo e($activeHistoryTab === 'payments' ? '0' : '-1'); ?>"
                data-customer-history-tab="payments"
            >
                <span class="customer-tab-icon"><i class="bi bi-wallet2" aria-hidden="true"></i></span>
                <span class="customer-tab-copy"><strong>Payment History</strong><small>Collections and payment details</small></span>
                <span class="customer-tab-count customer-tab-count--success"><?php echo e(number_format($payments->total())); ?></span>
            </a>
        </nav>

        <div
            id="customerCreditPanel"
            class="customer-history-panel"
            role="tabpanel"
            aria-labelledby="customerCreditTab"
            data-customer-history-panel="credit"
            <?php if($activeHistoryTab !== 'credit'): ?> hidden <?php endif; ?>
        >
    <section class="card customer-history-card">
        <div class="card-header"><div><span class="card-title">Credit Transactions</span><span class="customer-card-caption">Open and settled credit sales for this customer.</span></div><span class="tag tag--accent"><?php echo e($creditTransactions->total()); ?> transactions</span></div>
        <div class="table-wrap" tabindex="0" role="region" aria-label="Customer credit transactions"><table><thead><tr><th>Receipt</th><th>Sale Date</th><th>Due Date</th><th>Status</th><th class="text-right">Net</th><th class="text-right">Paid</th><th class="text-right">Balance</th><th>Actions</th></tr></thead><tbody>
        <?php $__empty_1 = true; $__currentLoopData = $creditTransactions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $transaction): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <?php
                $displayStatus = $receivables->statusForRow($transaction);
            ?>
            <tr>
                <td data-label="Receipt"><a class="receipt-code" href="<?php echo e(route('pos.receipt', $transaction)); ?>"><?php echo e($transaction->receipt_no); ?></a></td>
                <td data-label="Sale date"><?php echo e($transaction->date->format('M d, Y')); ?></td>
                <td data-label="Due date"><?php echo e($transaction->creditSale->due_date->format('M d, Y')); ?></td>
                <td data-label="Status"><span class="status <?php echo e($displayStatus === 'overdue' ? 'status-danger' : ($displayStatus === 'paid' ? 'status-active' : 'status-low')); ?>"><?php echo e(ucfirst(str_replace('_', ' ', $displayStatus))); ?></span></td>
                <td data-label="Net" class="text-right">&#8369;<?php echo e(number_format((float) $transaction->ar_net_amount, 2)); ?></td>
                <td data-label="Paid" class="text-right">&#8369;<?php echo e(number_format((float) $transaction->ar_paid_amount, 2)); ?></td>
                <td data-label="Balance" class="text-right customer-history-balance">&#8369;<?php echo e(number_format((float) $transaction->ar_balance_amount, 2)); ?></td>
                <td data-label="Actions"><div class="table-actions"><?php if((float) $transaction->ar_balance_amount > 0): ?><a class="btn btn-primary btn-sm" href="<?php echo e(route('pos.credit-payments.create', $transaction)); ?>">Record payment</a><?php endif; ?><a class="btn btn-secondary btn-sm" href="<?php echo e(route('pos.receipt', $transaction)); ?>">View receipt</a></div></td>
            </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><tr><?php if (isset($component)) { $__componentOriginal074a021b9d42f490272b5eefda63257c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal074a021b9d42f490272b5eefda63257c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.empty-state','data' => ['colspan' => '8','title' => 'No credit transactions','message' => 'Credit sales for this customer will appear here.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['colspan' => '8','title' => 'No credit transactions','message' => 'Credit sales for this customer will appear here.']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal074a021b9d42f490272b5eefda63257c)): ?>
<?php $attributes = $__attributesOriginal074a021b9d42f490272b5eefda63257c; ?>
<?php unset($__attributesOriginal074a021b9d42f490272b5eefda63257c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal074a021b9d42f490272b5eefda63257c)): ?>
<?php $component = $__componentOriginal074a021b9d42f490272b5eefda63257c; ?>
<?php unset($__componentOriginal074a021b9d42f490272b5eefda63257c); ?>
<?php endif; ?></tr><?php endif; ?>
        </tbody></table></div><div class="view-blades-footer customer-history-footer"><span>Showing <?php echo e($creditTransactions->firstItem() ?? 0); ?>–<?php echo e($creditTransactions->lastItem() ?? 0); ?> of <?php echo e(number_format($creditTransactions->total())); ?> transactions</span><?php echo e($creditTransactions->links()); ?></div>
    </section>
        </div>

        <div
            id="customerPaymentsPanel"
            class="customer-history-panel"
            role="tabpanel"
            aria-labelledby="customerPaymentsTab"
            data-customer-history-panel="payments"
            <?php if($activeHistoryTab !== 'payments'): ?> hidden <?php endif; ?>
        >
    <section class="card customer-history-card">
        <div class="card-header">
            <div>
                <span class="card-title">Payment History</span>
                <span class="customer-card-caption">Collections received for this customer's credit sales.</span>
            </div>
            <span class="tag tag--success"><?php echo e($payments->total()); ?> payments</span>
        </div>
        <div class="table-wrap" tabindex="0" role="region" aria-label="Customer payment history">
            <table>
                <thead>
                    <tr>
                        <th>Payment Date</th>
                        <th>Receipt</th>
                        <th>Method</th>
                        <th>Reference</th>
                        <th>Received By</th>
                        <th>Notes</th>
                        <th class="text-right">Amount</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
        <?php $__empty_1 = true; $__currentLoopData = $payments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr>
                <td data-label="Payment date"><time datetime="<?php echo e($payment->paid_at->toIso8601String()); ?>"><?php echo e($payment->paid_at->format('M d, Y')); ?><span class="customer-payment-time"><?php echo e($payment->paid_at->format('h:i A')); ?></span></time></td>
                <td data-label="Receipt"><?php if($payment->transaction): ?><a class="receipt-code" href="<?php echo e(route('pos.receipt', $payment->transaction)); ?>"><?php echo e($payment->transaction->receipt_no); ?></a><?php else: ?><span>&mdash;</span><?php endif; ?></td>
                <td data-label="Method"><span class="tag tag--muted"><?php echo e($payment->paymentMethod?->name ?? 'Unknown'); ?></span></td>
                <td data-label="Reference"><?php echo e($payment->payment_reference ?: '—'); ?></td>
                <td data-label="Received by"><?php echo e($payment->receivedBy?->name ?? 'Unknown'); ?></td>
                <td data-label="Notes" class="customer-payment-notes"><?php echo e($payment->notes ?: '—'); ?></td>
                <td data-label="Amount" class="text-right customer-payment-amount">&#8369;<?php echo e(number_format((float) $payment->amount, 2)); ?></td>
                <td data-label="Details"><a class="btn btn-secondary btn-sm customer-payment-view" aria-label="View payment details for <?php echo e($payment->transaction?->receipt_no ?? 'payment'); ?>" title="View payment details" href="<?php echo e(route('pos.credit-payments.show', $payment)); ?>"><i class="bi bi-eye" aria-hidden="true"></i><span>View details</span></a></td>
            </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><tr><?php if (isset($component)) { $__componentOriginal074a021b9d42f490272b5eefda63257c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal074a021b9d42f490272b5eefda63257c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.empty-state','data' => ['colspan' => '8','title' => 'No payment history','message' => 'Recorded credit payments will appear here.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['colspan' => '8','title' => 'No payment history','message' => 'Recorded credit payments will appear here.']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal074a021b9d42f490272b5eefda63257c)): ?>
<?php $attributes = $__attributesOriginal074a021b9d42f490272b5eefda63257c; ?>
<?php unset($__attributesOriginal074a021b9d42f490272b5eefda63257c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal074a021b9d42f490272b5eefda63257c)): ?>
<?php $component = $__componentOriginal074a021b9d42f490272b5eefda63257c; ?>
<?php unset($__componentOriginal074a021b9d42f490272b5eefda63257c); ?>
<?php endif; ?></tr><?php endif; ?>
        </tbody></table></div>
        <div class="view-blades-footer customer-history-footer customer-payment-pagination">
            <span>Showing <?php echo e($payments->firstItem() ?? 0); ?>&ndash;<?php echo e($payments->lastItem() ?? 0); ?> of <?php echo e(number_format($payments->total())); ?> payments</span>
            <?php if($payments->hasPages()): ?>
                <?php
                    $currentPaymentPage = $payments->currentPage();
                    $lastPaymentPage = $payments->lastPage();
                    $paymentPages = collect([1, $currentPaymentPage - 1, $currentPaymentPage, $currentPaymentPage + 1, $lastPaymentPage])
                        ->filter(fn ($page) => $page >= 1 && $page <= $lastPaymentPage)
                        ->unique()
                        ->sort()
                        ->values();
                    $previousPaymentPage = null;
                ?>
                <nav class="pagination" aria-label="Payment history pagination">
                    <?php if($payments->onFirstPage()): ?>
                        <span class="disabled" aria-disabled="true">Previous</span>
                    <?php else: ?>
                        <a href="<?php echo e($payments->previousPageUrl()); ?>" rel="prev">Previous</a>
                    <?php endif; ?>

                    <?php $__currentLoopData = $paymentPages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php if($previousPaymentPage && $page > $previousPaymentPage + 1): ?>
                            <span class="disabled customer-pagination-gap" aria-hidden="true">&hellip;</span>
                        <?php endif; ?>
                        <?php if($page === $currentPaymentPage): ?>
                            <span class="active" aria-current="page"><?php echo e($page); ?></span>
                        <?php else: ?>
                            <a href="<?php echo e($payments->url($page)); ?>" aria-label="Go to payment history page <?php echo e($page); ?>"><?php echo e($page); ?></a>
                        <?php endif; ?>
                        <?php
                            $previousPaymentPage = $page;
                        ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                    <?php if($payments->hasMorePages()): ?>
                        <a href="<?php echo e($payments->nextPageUrl()); ?>" rel="next">Next</a>
                    <?php else: ?>
                        <span class="disabled" aria-disabled="true">Next</span>
                    <?php endif; ?>
                </nav>
            <?php endif; ?>
        </div>
    </section>
        </div>
    </section>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<?php echo app('Illuminate\Foundation\Vite')('resources/css/views/customers-1.css'); ?>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<?php echo app('Illuminate\Foundation\Vite')('resources/js/views/customers-show-1.js'); ?>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\acrylink_newbie1\resources\views\customers\show.blade.php ENDPATH**/ ?>