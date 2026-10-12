<?php
    $currentProduct = $variant?->product;
    $currentStock = $variant?->inventoryStock;
    $selectedProductId = old('product_id', $currentProduct?->id);
    $selectedProduct = $selectedProduct ?? $currentProduct;
    $selectedSupplierId = old('supplier_id', $variant?->supplier_id);
    $selectedSupplier = $selectedSupplier ?? $variant?->supplier;
?>

<div class="editor-body inventory-form-body">
    <section class="editor-section inventory-form-section">
        <div class="editor-section-head inventory-form-section-head">
            <span class="inventory-form-step">1</span>
            <div class="editor-section-title">Product Identity</div>
            <div class="editor-section-copy">Link this variant to an existing product, or create a new product identity.</div>
        </div>
        <div class="inventory-form-fields">
            <div class="form-group inventory-span-2">
                <label>Existing Product</label>
                <div class="inventory-lookup <?php echo e($selectedProduct ? 'has-selection' : ''); ?>"
                     data-inventory-lookup data-lookup-type="product"
                     data-search-url="<?php echo e(route('inventory.search.products')); ?>">
                    <input type="hidden" name="product_id" value="<?php echo e($selectedProductId); ?>" data-lookup-value>
                    <div class="inventory-lookup-input-wrap">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
                        <input type="text" data-lookup-input autocomplete="off"
                               placeholder="Search existing product name..."
                               value="<?php echo e($selectedProduct?->name ?? ''); ?>">
                        <button type="button" data-lookup-clear aria-label="Clear existing product selection" <?php echo e($selectedProduct ? '' : 'hidden'); ?>>&times;</button>
                    </div>
                    <div class="inventory-lookup-results" data-lookup-results hidden>
                        <div class="inventory-lookup-status">Type to search or choose from active products.</div>
                    </div>
                </div>
                <div class="inventory-product-mode <?php echo e($selectedProduct ? 'is-linked' : ''); ?>" data-product-mode><i class="bi <?php echo e($selectedProduct ? 'bi-link-45deg' : 'bi-plus-circle'); ?>" aria-hidden="true"></i><span><?php echo e($selectedProduct ? 'Adding a variant to '.$selectedProduct->name : 'Creating a new product and its first variant'); ?></span></div>
            </div>
            <div class="form-group">
                <label>Product Name *</label>
                <input type="text" name="name" value="<?php echo e(old('name', $currentProduct->name ?? '')); ?>" placeholder="Full product name">
                <div class="form-help">Use the name customers and staff recognize.</div>
            </div>
            <div class="form-group">
                <label>Category *</label>
                <select name="category">
                    <option value="">&mdash; Select Category &mdash;</option>
                    <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($cat); ?>" <?php echo e(old('category', $currentProduct->category ?? '') == $cat ? 'selected' : ''); ?>><?php echo e($cat); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="form-group inventory-span-2">
                <label>Description</label>
                <textarea name="description" rows="2" placeholder="Optional product notes"><?php echo e(old('description', $currentProduct->description ?? '')); ?></textarea>
            </div>
        </div>
    </section>

    <section class="editor-section inventory-form-section">
        <div class="editor-section-head inventory-form-section-head">
            <span class="inventory-form-step">2</span>
            <div class="editor-section-title">Variant Details</div>
            <div class="editor-section-copy">Add the identifiers staff use to find the exact auto part.</div>
        </div>
        <div class="inventory-form-fields inventory-form-fields--three">
            <div class="form-group">
                <label>Variant Code *</label>
                <input type="text" name="code" value="<?php echo e(old('code', $variant->variant_code ?? '')); ?>" placeholder="e.g. PRDV001-BLK" autocomplete="off" required autofocus>
                <div class="form-help">Must be unique across all variants.</div>
            </div>
            <div class="form-group">
                <label>Barcode</label>
                <input type="text" name="barcode" value="<?php echo e(old('barcode', $variant->barcode ?? '')); ?>" placeholder="Optional barcode">
            </div>
            <div class="form-group">
                <label>Part Number</label>
                <input type="text" name="part_number" value="<?php echo e(old('part_number', $variant->part_number ?? '')); ?>" placeholder="Manufacturer part number">
            </div>
            <div class="form-group">
                <label>Brand</label>
                <input type="text" name="brand" value="<?php echo e(old('brand', $variant->brand?->name ?? '')); ?>" placeholder="Brand name">
            </div>
            <div class="form-group">
                <label>Unit *</label>
                <select name="unit" required>
                    <?php $__currentLoopData = $units; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($u); ?>" <?php echo e(old('unit', $variant->unit?->code ?? 'pcs') == $u ? 'selected' : ''); ?>><?php echo e($u); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="form-group">
                <label>Size</label>
                <input type="text" name="size" value="<?php echo e(old('size', $variant->size ?? '')); ?>" placeholder="Size or variant label">
            </div>
            <div class="form-group">
                <label>Color</label>
                <input type="text" name="color" value="<?php echo e(old('color', $variant->color ?? '')); ?>" placeholder="Color">
            </div>
            <div class="form-group">
                <label>Supplier</label>
                <div class="inventory-lookup <?php echo e($selectedSupplier ? 'has-selection' : ''); ?>"
                     data-inventory-lookup data-lookup-type="supplier"
                     data-search-url="<?php echo e(route('inventory.search.suppliers')); ?>">
                    <input type="hidden" name="supplier_id" value="<?php echo e($selectedSupplierId); ?>" data-lookup-value>
                    <div class="inventory-lookup-input-wrap">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
                        <input type="text" data-lookup-input autocomplete="off"
                               placeholder="Search supplier name, contact, or phone..."
                               value="<?php echo e($selectedSupplier?->name ?? ''); ?>">
                        <button type="button" data-lookup-clear aria-label="Clear supplier selection" <?php echo e($selectedSupplier ? '' : 'hidden'); ?>>&times;</button>
                    </div>
                    <div class="inventory-lookup-results" data-lookup-results hidden>
                        <div class="inventory-lookup-status">Type to search or choose from active suppliers.</div>
                    </div>
                </div>
            </div>
            <?php if($variant): ?>
            <div class="form-group">
                <label>Status *</label>
                <select name="status" required>
                    <option value="active" <?php echo e(old('status', $variant->status) === 'active' ? 'selected' : ''); ?>>Active</option>
                    <option value="inactive" <?php echo e(old('status', $variant->status) === 'inactive' ? 'selected' : ''); ?>>Inactive</option>
                </select>
                <div class="form-help">Inactive variants are hidden from the POS and active inventory searches.</div>
            </div>
            <?php endif; ?>
            <div class="form-group inventory-span-3">
                <label>Product Image</label>
                <div class="inventory-image-input inventory-image-input--wide">
                    <?php if($variant?->image_path): ?>
                    <img src="<?php echo e(asset('storage/'.$variant->image_path)); ?>" alt="<?php echo e($variant->variant_code); ?> image preview" data-image-preview>
                    <?php else: ?>
                    <div class="inventory-image-placeholder" data-image-preview><i class="bi bi-image" aria-hidden="true"></i><span>No image selected</span></div>
                    <?php endif; ?>
                    <div>
                        <strong class="inventory-image-title">Add a clear product photo</strong>
                        <input type="file" name="image" accept="image/jpeg,image/png,image/webp" data-image-input>
                        <div class="form-help" data-image-help>JPG, PNG, or WEBP up to 2 MB. A square or landscape image works best.</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="editor-section inventory-form-section">
        <div class="editor-section-head inventory-form-section-head">
            <span class="inventory-form-step">3</span>
            <div class="editor-section-title">Pricing &amp; Stock</div>
            <div class="editor-section-copy">Set the selling price, opening quantity, and alert thresholds.</div>
        </div>
        <div class="inventory-form-fields inventory-form-fields--three">
            <div class="form-group">
                <label>Quantity *</label>
                <input type="number" name="stock" value="<?php echo e(old('stock', $currentStock->quantity_on_hand ?? '')); ?>" min="0" max="2147483647" step="1" placeholder="0" required>
            </div>
            <div class="form-group">
                <label>Cost Price (&#8369;) *</label>
                <input type="number" name="cost" value="<?php echo e(old('cost', $variant->cost_price ?? '')); ?>" step="0.01" min="0" max="99999999.99" placeholder="0.00" required>
            </div>  
            <div class="form-group">
                <label>Selling Price (&#8369;) *</label>
                <input type="number" name="price" value="<?php echo e(old('price', $variant->selling_price ?? '')); ?>" step="0.01" min="0" max="99999999.99" placeholder="0.00" required>
            </div>
            <div class="form-group">
                <label>Re-order Level *</label>
                <input type="number" name="min_stock" value="<?php echo e(old('min_stock', $currentStock->minimum_stock ?? '')); ?>" min="0" max="2147483647" step="1" placeholder="0" required>
            </div>
            <div class="form-group">
                <label>Critical Level *</label>
                <input type="number" name="critical_stock" value="<?php echo e(old('critical_stock', $currentStock->critical_stock ?? '')); ?>" min="0" max="2147483647" step="1" placeholder="0" required>
            </div>
            <div class="inventory-pricing-preview inventory-span-3" aria-live="polite">
                <div><span>Estimated profit per unit</span><strong data-profit-preview>&#8369;0.00</strong></div>
                <div><span>Gross margin</span><strong data-margin-preview>0.0%</strong></div>
                <p><i class="bi bi-info-circle" aria-hidden="true"></i> Critical level must be equal to or lower than the re-order level.</p>
            </div>
        </div>
    </section>
</div>
<?php /**PATH C:\xampp\htdocs\acrylink_newbie\resources\views\inventory\_form.blade.php ENDPATH**/ ?>