<?php
    $selectedVariantId = old("items.{$index}.variant_id", $item['variant_id'] ?? '');
    $quantityReceived = old("items.{$index}.quantity_received", $item['quantity_received'] ?? 1);
    $unitCost = old("items.{$index}.unit_cost", $item['unit_cost'] ?? '');
    $selectedLabel = $selectedVariant
        ? ($selectedVariant->product?->name ?? 'Deleted product').' - '.$selectedVariant->variant_code
        : '';
?>

<div class="receipt-item-row" data-receipt-item-row>
    <div class="grid-2">
        <div class="form-group">
            <label>Product Variant *</label>
            <div class="variant-search <?php echo e($selectedVariant ? 'has-selection' : ''); ?>"
                 data-variant-search
                 data-search-url="<?php echo e(route('stock-receipts.search.variants')); ?>">
                <input type="hidden" name="items[<?php echo e($index); ?>][variant_id]"
                       value="<?php echo e($selectedVariantId); ?>" data-variant-select required>
                <div class="variant-search-input-wrap">
                    <svg class="variant-search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <circle cx="11" cy="11" r="7"></circle><path d="m20 20-3.5-3.5"></path>
                    </svg>
                    <input type="text" class="variant-search-input" data-variant-search-input
                           placeholder="Search product, SKU, barcode, or part number..."
                           autocomplete="off" value="<?php echo e($selectedLabel); ?>">
                    <button type="button" class="variant-search-clear" data-variant-search-clear
                            aria-label="Clear selected variant" <?php echo e($selectedVariant ? '' : 'hidden'); ?>>&times;</button>
                </div>
                <div class="variant-search-results" data-variant-search-results hidden>
                    <div class="variant-search-empty" data-search-status>Type to search or choose from available variants.</div>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label>Quantity Received *</label>
            <input type="number" name="items[<?php echo e($index); ?>][quantity_received]"
                   value="<?php echo e($quantityReceived); ?>" min="1" max="2147483647" step="1" required>
        </div>
    </div>

    <div class="grid-2">
        <div class="form-group">
            <label>Unit Cost (&#8369;) *</label>
            <input type="number" name="items[<?php echo e($index); ?>][unit_cost]" value="<?php echo e($unitCost); ?>"
                   min="0" max="99999999.99" step="0.01" placeholder="0.00" data-unit-cost required>
        </div>
        <div class="form-group receipt-item-remove">
            <button type="button" class="btn btn-danger btn-sm" data-remove-receipt-item>Remove Item</button>
        </div>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\acrylink_newbie\resources\views\stock_receipts\partials\item-row.blade.php ENDPATH**/ ?>