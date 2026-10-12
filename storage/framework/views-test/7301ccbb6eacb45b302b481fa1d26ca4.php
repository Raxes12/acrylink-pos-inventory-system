<?php $__env->startSection('title', 'Point of Sale'); ?>
<?php $__env->startSection('page-label', 'Point of Sale'); ?>

<?php $__env->startSection('content'); ?>
<div class="pos-layout">

    
    <div class="pos-left">
        <form method="GET" action="<?php echo e(route('pos.index')); ?>" data-products-url="<?php echo e(route('pos.products')); ?>" class="pos-search-bar" id="posFilterForm" data-no-loading="true">
            <div class="pos-search-field">
                <svg class="pos-search-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                    <circle cx="11" cy="11" r="8"></circle><path d="m21 21-4.35-4.35"></path>
                </svg>
                <label class="visually-hidden" for="posSearch">Search POS products</label>
                <input type="search" id="posSearch" name="search" value="<?php echo e($filters['search'] ?? ''); ?>" placeholder="Scan barcode or search product, code, part number, or brand..." autocomplete="off" enterkeyhint="search" aria-describedby="posSearchHint posSearchFeedback" autofocus>
                <button type="button" id="posSearchClear" class="pos-search-clear" title="Clear search" aria-label="Clear product search" <?php echo e(empty($filters['search']) ? 'hidden' : ''); ?>>
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"></path></svg>
                </button>
            </div>
            <div id="posSearchHint" class="visually-hidden">Press Enter after scanning a barcode to add an exact match directly to the cart.</div>
            <button type="button" class="pos-mobile-cart-link" data-pos-scroll-cart aria-controls="cartItems">
                <?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'cart']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'cart']); ?>
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
                View cart
            </button>
            <div id="posSearchFeedback" class="pos-search-feedback" role="status" aria-live="polite"></div>
        </form>
        <div id="productGrid" aria-live="polite">
            <?php if($initialProductError): ?>
            <div class="pos-empty pos-state--error" role="alert">
                <strong>Products unavailable</strong>
                <span><?php echo e($initialProductError); ?></span>
                <button type="button" class="pos-state-retry" data-pos-grid-retry>Retry</button>
            </div>
            <?php else: ?>
            <?php $__empty_1 = true; $__currentLoopData = $posVariants; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $variant): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <?php
                $stock = (int) $variant['stock'];
                $stockStatus = $variant['stock_status'];
                $stockLabel = match ($stockStatus) {
                    'out' => 'Out of Stock',
                    'low' => 'Low Stock',
                    default => 'In Stock',
                };
                $initials = collect(preg_split('/\s+/', trim($variant['product_name'] ?? 'Product')))
                    ->filter()
                    ->take(2)
                    ->map(fn ($word) => strtoupper(substr($word, 0, 1)))
                    ->join('');
            ?>
            <article class="product-card product-card--<?php echo e($stockStatus); ?>" data-variant-id="<?php echo e($variant['id']); ?>">
                <div class="product-card-image <?php echo e($variant['image_url'] ? 'is-loading' : 'is-missing'); ?>">
                    <span class="product-card-image-placeholder" aria-hidden="true"><?php echo e($initials ?: 'P'); ?></span>
                    <?php if($variant['image_url']): ?>
                    <img src="<?php echo e($variant['image_url']); ?>"
                         alt="<?php echo e($variant['product_name']); ?> <?php echo e($variant['variant_code']); ?>"
                         width="320" height="240"
                         loading="<?php echo e($loop->index < 4 ? 'eager' : 'lazy'); ?>"
                         decoding="async">
                    <?php endif; ?>
                </div>
                <div class="product-stock product-stock--<?php echo e($stockStatus); ?>" aria-label="<?php echo e($stockLabel); ?>, <?php echo e($stock); ?> <?php echo e($variant['unit'] ?? 'pcs'); ?> available">
                    <span class="product-stock-label"><?php echo e($stockLabel); ?></span>
                    <span aria-hidden="true">&middot;</span>
                    <span><?php echo e($stock); ?> <?php echo e($variant['unit'] ?? 'pcs'); ?></span>
                </div>
                <button type="button"
                        class="product-info-btn"
                        data-info-variant="<?php echo e($variant['id']); ?>"
                        data-info-url="<?php echo e($variant['details_url']); ?>"
                        title="Product information"
                        aria-label="View details for <?php echo e($variant['product_name']); ?> <?php echo e($variant['variant_code']); ?>">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="16" x2="12" y2="12"></line>
                        <line x1="12" y1="8" x2="12.01" y2="8"></line>
                    </svg>
                </button>
                <div class="product-card-content">
                    <div class="product-name" title="<?php echo e($variant['product_name']); ?>"><?php echo e($variant['product_name']); ?></div>
                    <div class="product-variant-details" aria-label="Variant identifying details">
                        <?php if($variant['part_number']): ?><span title="Part number <?php echo e($variant['part_number']); ?>"><small>Part No.</small><strong><?php echo e($variant['part_number']); ?></strong></span><?php endif; ?>
                        <?php if($variant['brand']): ?><span title="Brand <?php echo e($variant['brand']); ?>"><small>Brand</small><strong><?php echo e($variant['brand']); ?></strong></span><?php endif; ?>
                        <?php if($variant['size']): ?><span title="Size <?php echo e($variant['size']); ?>"><small>Size</small><strong><?php echo e($variant['size']); ?></strong></span><?php endif; ?>
                        <?php if($variant['color']): ?><span title="Color <?php echo e($variant['color']); ?>"><small>Color</small><strong><?php echo e($variant['color']); ?></strong></span><?php endif; ?>
                        <?php if(!$variant['part_number'] && !$variant['brand'] && !$variant['size'] && !$variant['color']): ?><span class="product-variant-standard"><small>Details</small><strong>Standard variant</strong></span><?php endif; ?>
                    </div>
                    <div class="product-price">&#8369;<?php echo e(number_format((float) $variant['price'], 2)); ?></div>
                </div>
                <div class="product-card-actions">
                    <button type="button" class="product-add-btn" data-add-variant="<?php echo e($variant['id']); ?>"
                            aria-label="Add <?php echo e($variant['product_name']); ?> <?php echo e($variant['variant_code']); ?> to cart"
                            <?php echo e($stockStatus === 'out' ? 'disabled' : ''); ?>>
                        <span aria-hidden="true">+</span>
                        <span><?php echo e($stockStatus === 'out' ? 'Unavailable' : 'Add to Cart'); ?></span>
                    </button>
                </div>
            </article>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div id="noProductsMsg" class="pos-empty">
                <?php if(!empty($filters['search'])): ?>
                    <strong>No search results</strong>
                    <span>No products match "<?php echo e($filters['search']); ?>"<?php echo e(!empty($filters['category']) ? ' in '.$filters['category'] : ''); ?>.</span>
                <?php else: ?>
                    <strong>Search for a product</strong>
                    <span>Enter a product name, code, barcode, part number, or brand to view matching products.</span>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            <?php endif; ?>
            <div id="posLoadSentinel" class="pos-load-sentinel" aria-live="polite">
                <span data-pos-load-message></span>
                <button type="button" class="pos-load-retry" data-pos-load-retry hidden>Retry</button>
            </div>
        </div>
    </div>

    
    <div class="pos-cart card">
        <div class="card-header">
            <span class="card-title">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                Cart
                <span id="cartCount" class="cart-count" aria-live="polite">0 items</span>
            </span>
            <div class="pos-cart-header-actions">
                <button type="button" class="pos-mobile-products-link" data-pos-scroll-products>
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
                    Products
                </button>
                <button id="clearCartBtn" onclick="openClearCartConfirmation()" class="btn btn-danger btn-sm" aria-label="Clear cart" disabled>Clear Cart</button>
            </div>
        </div>

        <div id="cartItems" class="cart-items">
            <div id="emptyCart" class="cart-empty">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="opacity:.3;"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                <strong>Your cart is empty.</strong>
                <span>Select a product to begin a sale. You can also scan an exact barcode.</span>
            </div>
        </div>

        <div class="checkout-panel">
            <div class="checkout-summary" aria-label="Order summary">
                <div class="checkout-row">
                    <span>Subtotal</span>
                    <strong id="subtotal">&#8369;0.00</strong>
                </div>
                <div class="checkout-row checkout-discount-total">
                    <span>Item discounts</span>
                    <strong id="discountTotal">&#8369;0.00</strong>
                </div>
                <div class="checkout-total-row">
                    <span>Total</span>
                    <strong id="total" class="checkout-total-val">&#8369;0.00</strong>
                </div>
            </div>
            <button type="button" id="proceedPaymentBtn" onclick="openPaymentModal()" class="btn btn-success w-full checkout-proceed" disabled aria-describedby="checkoutActionHint">
                Proceed to Payment
                <i class="bi bi-arrow-right" aria-hidden="true"></i>
            </button>
            <p id="checkoutActionHint" class="checkout-action-hint">Review item quantities and discounts before collecting payment.</p>
        </div>
    </div>
</div>

<div id="paymentModal" class="payment-modal-overlay" hidden role="presentation">
    <section class="payment-modal" role="dialog" aria-modal="true" aria-labelledby="paymentModalTitle">
        <header class="payment-modal-header">
            <div><span>Complete sale</span><h2 id="paymentModalTitle">Payment</h2></div>
            <button type="button" class="payment-modal-close" data-close-payment-modal aria-label="Close payment">&times;</button>
        </header>
        <div class="payment-modal-body">
            <aside class="payment-modal-total" aria-label="Payment amount due">
                <div class="payment-order-heading"><span class="payment-order-icon"><i class="bi bi-bag-check" aria-hidden="true"></i></span><div><strong>Order Summary</strong><small><span id="paymentItemCount">0 items</span> in this sale</small></div></div>
                <dl class="payment-order-breakdown">
                    <div><dt>Subtotal</dt><dd id="paymentModalSubtotal">&#8369;0.00</dd></div>
                    <div><dt>Item Discounts</dt><dd id="paymentModalDiscount">&#8369;0.00</dd></div>
                </dl>
                <div class="payment-amount-due"><span>Amount Due</span><strong id="amountDue">&#8369;0.00</strong></div>
                <div class="payment-order-note"><i class="bi bi-shield-check" aria-hidden="true"></i><span>Review the amount and payment details before processing.</span></div>
            </aside>
            <div class="payment-modal-fields">
                <div class="payment-fields-heading"><div><h3>Select payment method</h3><p id="paymentMethodHelp">Enter the cash received from the customer.</p></div><span class="payment-step">Step 1 of 2</span></div>
                <div class="method-grid">
                    <button type="button" class="method-btn active" data-method="cash" aria-pressed="true" onclick="selectMethod('cash')"><i class="bi bi-cash-stack" aria-hidden="true"></i><span>Cash<small>Cash received</small></span></button>
                    <button type="button" class="method-btn" data-method="gcash" aria-pressed="false" onclick="selectMethod('gcash')"><i class="bi bi-phone" aria-hidden="true"></i><span>GCash<small>Reference required</small></span></button>
                    <button type="button" class="method-btn" data-method="check" aria-pressed="false" onclick="selectMethod('check')"><i class="bi bi-bank" aria-hidden="true"></i><span>Check<small>Bank details</small></span></button>
                    <button type="button" class="method-btn" data-method="credit_account" aria-pressed="false" onclick="selectMethod('credit_account')"><i class="bi bi-journal-text" aria-hidden="true"></i><span>Credit Sale<small>Customer account</small></span></button>
                </div>

                <div id="gcashFields" class="payment-detail-panel" style="display:none;">
                    <div class="payment-section-title">GCash details</div>
                    <label class="payment-field"><span>GCash Reference Number</span><input type="text" id="gcashReference" placeholder="Enter transaction reference" oninput="recalculate()"></label>
                </div>

                <div id="checkFields" class="payment-detail-panel" style="display:none;">
                    <div class="payment-section-title">Check details</div>
                    <div class="payment-field-grid">
                        <label class="payment-field"><span>Check Number</span><input type="text" id="checkNumber" placeholder="Enter check number" oninput="recalculate()"></label>
                        <label class="payment-field"><span>Check Date</span><input type="date" id="checkDate" onchange="recalculate()"></label>
                    </div>
                    <label class="payment-field"><span>Bank Name</span><input type="text" id="checkBank" placeholder="Enter issuing bank" oninput="recalculate()"></label>
                </div>

                <div id="creditAccountFields" class="payment-detail-panel credit-account-panel" style="display:none;">
                    <div class="payment-section-title">Customer and credit details</div>
                    <div class="pos-customer-picker" data-customer-search-url="<?php echo e(route('pos.customers')); ?>">
                        <label class="payment-field" for="customerSearch"><span>Customer <b id="customerRequirement">Required</b></span></label>
                        <div class="pos-customer-search-row">
                            <input type="search" id="customerSearch" placeholder="Search name, code, phone, or email" autocomplete="off" aria-controls="customerSearchResults" aria-describedby="customerSearchStatus">
                            <button type="button" id="customerClear" class="btn btn-secondary btn-sm" hidden>Clear</button>
                        </div>
                        <div id="customerSearchStatus" class="pos-customer-search-status" role="status" aria-live="polite">Search for an active customer with credit enabled.</div>
                        <div id="customerSearchResults" class="pos-customer-results" hidden></div>
                    </div>
                    <dl class="credit-account-summary">
                        <div><dt>Customer</dt><dd id="creditCustomerName">Not selected</dd></div>
                        <div><dt>Credit Limit</dt><dd id="creditLimit">-</dd></div>
                        <div><dt>Current Outstanding</dt><dd id="creditOutstanding">-</dd></div>
                        <div><dt>Available Credit</dt><dd id="creditAvailable">-</dd></div>
                        <div><dt>Requested Credit</dt><dd id="creditRequested">-</dd></div>
                        <div><dt>Projected Outstanding</dt><dd id="creditProjected">-</dd></div>
                        <div><dt>Default Credit Term</dt><dd id="creditDefaultTerm">-</dd></div>
                    </dl>
                    <label class="payment-field" for="creditTermDays"><span>Credit Term (days)</span><input type="number" id="creditTermDays" min="1" max="65535" step="1" oninput="updateCreditDueDate(); recalculate()" aria-describedby="creditDueDate"></label>
                    <div class="credit-due-date">Due date: <strong id="creditDueDate">-</strong></div>
                    <label class="payment-field" for="creditInitialPaymentMethod"><span>Initial Payment Method</span><select id="creditInitialPaymentMethod" onchange="updateCreditInitialPaymentFields(); recalculate()">
                        <option value="">No initial payment</option><option value="cash">Cash</option><option value="gcash">GCash</option><option value="check">Check</option>
                    </select></label>
                    <div id="creditInitialReferenceFields" hidden><input type="text" id="creditInitialReference" placeholder="Payment reference no." oninput="recalculate()"></div>
                    <div id="creditInitialCheckFields" hidden><input type="text" id="creditInitialCheckNumber" placeholder="Check number" oninput="recalculate()"><input type="text" id="creditInitialCheckBank" placeholder="Bank name" oninput="recalculate()"><input type="date" id="creditInitialCheckDate" onchange="recalculate()"></div>
                </div>

                <div class="checkout-payment-summary">
                    <div class="payment-section-title"><span>Payment details</span><span class="payment-step">Step 2 of 2</span></div>
                    <label class="checkout-row checkout-tender-row" for="payment"><span id="paymentLabel">Cash Received</span><span class="checkout-money-input"><span aria-hidden="true">&#8369;</span><input type="number" id="payment" min="0" max="99999999.99" step="0.01" placeholder="0.00" oninput="recalculate()"></span></label>
                    <div class="checkout-row checkout-change-row"><span id="changeLabel">Change</span><strong id="change">&#8369;0.00</strong></div>
                </div>
                <div id="checkoutFeedback" class="checkout-feedback <?php echo e($errors->any() ? 'is-error' : ''); ?>" role="alert" aria-live="polite"><?php echo e($errors->any() ? $errors->first() : 'Enter the payment details to continue.'); ?></div>
            </div>
        </div>
        <footer class="payment-modal-footer">
            <span class="payment-footer-hint"><i class="bi bi-info-circle" aria-hidden="true"></i> The sale is recorded only after processing.</span>
            <div><button type="button" class="btn btn-secondary" data-close-payment-modal>Back to Cart</button><button type="button" id="checkoutBtn" onclick="submitCheckout()" class="btn btn-success checkout-submit" disabled><span class="checkout-submit-icon"><i class="bi bi-check2-circle" aria-hidden="true"></i></span><span class="checkout-spinner" aria-hidden="true"></span><span class="checkout-submit-text">Process Sale</span></button></div>
        </footer>
    </section>
</div>

<div id="clearCartModal" class="payment-modal-overlay clear-cart-overlay" hidden role="presentation">
    <section class="clear-cart-dialog" role="alertdialog" aria-modal="true" aria-labelledby="clearCartTitle" aria-describedby="clearCartMessage">
        <span class="clear-cart-icon"><i class="bi bi-trash3" aria-hidden="true"></i></span>
        <h2 id="clearCartTitle">Clear this cart?</h2>
        <p id="clearCartMessage">All products, quantities, and discounts in the current cart will be removed.</p>
        <div><button type="button" class="btn btn-secondary" data-cancel-clear-cart>Keep Cart</button><button type="button" class="btn btn-danger" data-confirm-clear-cart>Clear Cart</button></div>
    </section>
</div>

<div id="saleProcessingOverlay" class="sale-processing-overlay" aria-live="polite" aria-hidden="true">
    <div class="sale-processing-box" role="status">
        <span class="sale-processing-spinner" aria-hidden="true"></span>
        <div class="sale-processing-title">Processing Sale</div>
        <div class="sale-processing-sub">Please wait while the receipt is being prepared.</div>
    </div>
</div>


<form id="checkoutForm" method="POST" action="<?php echo e(route('pos.checkout')); ?>" style="display:none;">
    <?php echo csrf_field(); ?>
    <input type="hidden" name="checkout_token" value="<?php echo e((string) \Illuminate\Support\Str::uuid()); ?>">
    <input type="hidden" name="discount" id="formDiscount">
    <input type="hidden" name="payment_method" id="formPaymentMethod">
    <input type="hidden" name="payment_reference" id="formPaymentReference">
    <input type="hidden" name="payment_card_type" id="formPaymentCardType">
    <input type="hidden" name="payment_card_last4" id="formPaymentCardLast4">
    <input type="hidden" name="payment_payer_name" id="formPaymentPayerName">
    <input type="hidden" name="check_number" id="formCheckNumber">
    <input type="hidden" name="check_bank" id="formCheckBank">
    <input type="hidden" name="check_date" id="formCheckDate">
    <input type="hidden" name="payment" id="formPayment">
    <input type="hidden" name="customer_id" id="formCustomerId">
    <input type="hidden" name="credit_term_days" id="formCreditTermDays">
    <input type="hidden" name="initial_payment_method" id="formInitialPaymentMethod">
    <input type="hidden" name="initial_payment_reference" id="formInitialPaymentReference">
    <input type="hidden" name="initial_payment_card_type" id="formInitialPaymentCardType">
    <input type="hidden" name="initial_payment_card_last4" id="formInitialPaymentCardLast4">
    <input type="hidden" name="initial_payment_payer_name" id="formInitialPaymentPayerName">
    <input type="hidden" name="initial_check_number" id="formInitialCheckNumber">
    <input type="hidden" name="initial_check_bank" id="formInitialCheckBank">
    <input type="hidden" name="initial_check_date" id="formInitialCheckDate">
    <div id="formItems"></div>
</form>

<?php
    $posProductBatch = [
        'has_more' => $posVariants->hasMorePages(),
        'next_page' => $posVariants->hasMorePages() ? $posVariants->currentPage() + 1 : null,
        'batch_size' => $posVariants->perPage(),
    ];
    $posCheckoutState = [
        'has_errors' => $errors->any(),
        'error' => $errors->first(),
    ];
?>
<script type="application/json" id="posProducts"><?php echo json_encode($posVariants->items(), 15, 512) ?></script>
<script type="application/json" id="posProductBatch"><?php echo json_encode($posProductBatch, 15, 512) ?></script>
<script type="application/json" id="posCheckoutState"><?php echo json_encode($posCheckoutState, 15, 512) ?></script>


<div id="variantInfoModal" class="variant-info-overlay" hidden role="presentation">
    <div class="variant-info-modal" role="dialog" aria-modal="true" aria-labelledby="variantInfoTitle">
        <div class="variant-info-header">
            <div>
                <div id="variantInfoCode" class="variant-info-code"></div>
                <div id="variantInfoTitle" class="variant-info-title"></div>
            </div>
            <button type="button" class="variant-info-close" data-close-variant-info aria-label="Close variant details">&times;</button>
        </div>
        <div class="variant-info-body">
            <div id="variantInfoImageWrap" class="variant-info-image-wrap is-loading" role="status">
                <span class="variant-info-image-placeholder" data-variant-image-placeholder>Loading image...</span>
                <img id="variantInfoImage" alt="" width="800" height="450" loading="eager" decoding="async" hidden>
            </div>
            <p id="variantInfoDescription" class="variant-info-description"></p>
            <dl class="variant-info-grid">
                <div><dt>Barcode</dt><dd id="variantInfoBarcode"></dd></div>
                <div><dt>Part Number</dt><dd id="variantInfoPartNumber"></dd></div>
                <div><dt>Brand</dt><dd id="variantInfoBrand"></dd></div>
                <div><dt>Size</dt><dd id="variantInfoSize"></dd></div>
                <div><dt>Color</dt><dd id="variantInfoColor"></dd></div>
                <div><dt>Unit</dt><dd id="variantInfoUnit"></dd></div>
                <div><dt>Supplier</dt><dd id="variantInfoSupplier"></dd></div>
                <div><dt>Price</dt><dd id="variantInfoPrice"></dd></div>
                <div><dt>Stock</dt><dd id="variantInfoStock"></dd></div>
            </dl>
        </div>
        <div class="variant-info-footer">
            <button type="button" class="btn btn-secondary" id="variantInfoRetryBtn" hidden>Retry</button>
            <button type="button" class="btn btn-secondary" data-close-variant-info>Close</button>
            <button type="button" class="btn btn-success" id="variantInfoAddBtn">Add to Cart</button>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<?php echo app('Illuminate\Foundation\Vite')('resources/css/views/pos-index-1.css'); ?>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<?php echo app('Illuminate\Foundation\Vite')('resources/js/views/pos-index-1.js'); ?>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\acrylink_newbie\resources\views/pos/index.blade.php ENDPATH**/ ?>