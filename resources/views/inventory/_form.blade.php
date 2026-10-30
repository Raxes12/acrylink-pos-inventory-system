@php
    $currentProduct = $variant?->product;
    $currentStock = $variant?->inventoryStock;
    $selectedProductId = old('product_id', $currentProduct?->id);
    $selectedProduct = $selectedProduct ?? $currentProduct;
    $selectedSupplierId = old('supplier_id', $variant?->supplier_id);
    $selectedSupplier = $selectedSupplier ?? $variant?->supplier;
@endphp

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
                <div class="inventory-lookup {{ $selectedProduct ? 'has-selection' : '' }}"
                     data-inventory-lookup data-lookup-type="product"
                     data-search-url="{{ route('inventory.search.products') }}">
                    <input type="hidden" name="product_id" value="{{ $selectedProductId }}" data-lookup-value>
                    <div class="inventory-lookup-input-wrap">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
                        <input type="text" data-lookup-input autocomplete="off"
                               placeholder="Search existing product name..."
                               value="{{ $selectedProduct?->name ?? '' }}">
                        <button type="button" data-lookup-clear aria-label="Clear existing product selection" {{ $selectedProduct ? '' : 'hidden' }}>&times;</button>
                    </div>
                    <div class="inventory-lookup-results" data-lookup-results hidden>
                        <div class="inventory-lookup-status">Type to search or choose from active products.</div>
                    </div>
                </div>
                <div class="inventory-product-mode {{ $selectedProduct ? 'is-linked' : '' }}" data-product-mode><i class="bi {{ $selectedProduct ? 'bi-link-45deg' : 'bi-plus-circle' }}" aria-hidden="true"></i><span>{{ $selectedProduct ? 'Adding a variant to '.$selectedProduct->name : 'Creating a new product and its first variant' }}</span></div>
            </div>
            <div class="form-group">
                <label>Product Name *</label>
                <input type="text" name="name" value="{{ old('name', $currentProduct->name ?? '') }}" placeholder="Full product name">
                <div class="form-help">Use the name customers and staff recognize.</div>
            </div>
            <div class="form-group">
                <label>Category *</label>
                <select name="category">
                    <option value="">&mdash; Select Category &mdash;</option>
                    @foreach($categories as $cat)
                    <option value="{{ $cat }}" {{ old('category', $currentProduct->category ?? '') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group inventory-span-2">
                <label>Description</label>
                <textarea name="description" rows="2" placeholder="Optional product notes">{{ old('description', $currentProduct->description ?? '') }}</textarea>
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
                @if(!$variant)
                    <input type="hidden" name="suggested_code" value="{{ $suggestedVariantCode }}">
                @endif
                <input type="text" name="code" value="{{ old('code', $variant->variant_code ?? $suggestedVariantCode ?? '') }}" placeholder="e.g. PRDV-056" autocomplete="off" required autofocus>
                <div class="form-help">
                    @if($variant)
                        Must be unique across all variants.
                    @else
                        The next available code is filled automatically. You may replace it when a custom code is required.
                    @endif
                </div>
            </div>
            <div class="form-group">
                <label>Barcode</label>
                <input type="text" name="barcode" value="{{ old('barcode', $variant->barcode ?? '') }}" placeholder="Optional barcode">
            </div>
            <div class="form-group">
                <label>Part Number</label>
                <input type="text" name="part_number" value="{{ old('part_number', $variant->part_number ?? '') }}" placeholder="Manufacturer part number">
            </div>
            <div class="form-group">
                <label>Brand</label>
                <input type="text" name="brand" value="{{ old('brand', $variant->brand?->name ?? '') }}" placeholder="Brand name">
            </div>
            <div class="form-group">
                <label>Unit *</label>
                <select name="unit" required>
                    @foreach($units as $u)
                    <option value="{{ $u }}" {{ old('unit', $variant->unit?->code ?? 'PCS') == $u ? 'selected' : '' }}>{{ $u }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Size</label>
                <input type="text" name="size" value="{{ old('size', $variant->size ?? '') }}" placeholder="Size or variant label">
            </div>
            <div class="form-group">
                <label>Color</label>
                <input type="text" name="color" value="{{ old('color', $variant->color ?? '') }}" placeholder="Color">
            </div>
            <div class="form-group">
                <label>Supplier</label>
                <div class="inventory-lookup {{ $selectedSupplier ? 'has-selection' : '' }}"
                     data-inventory-lookup data-lookup-type="supplier"
                     data-search-url="{{ route('inventory.search.suppliers') }}">
                    <input type="hidden" name="supplier_id" value="{{ $selectedSupplierId }}" data-lookup-value>
                    <div class="inventory-lookup-input-wrap">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
                        <input type="text" data-lookup-input autocomplete="off"
                               placeholder="Search supplier name, contact, or phone..."
                               value="{{ $selectedSupplier?->name ?? '' }}">
                        <button type="button" data-lookup-clear aria-label="Clear supplier selection" {{ $selectedSupplier ? '' : 'hidden' }}>&times;</button>
                    </div>
                    <div class="inventory-lookup-results" data-lookup-results hidden>
                        <div class="inventory-lookup-status">Type to search or choose from active suppliers.</div>
                    </div>
                </div>
            </div>
            @if($variant)
            <div class="form-group">
                <label>Status *</label>
                <select name="status" required>
                    <option value="active" {{ old('status', $variant->status) === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ old('status', $variant->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
                <div class="form-help">Inactive variants are hidden from the POS and active inventory searches.</div>
            </div>
            @endif
            <div class="form-group inventory-span-3">
                <label>Product Image</label>
                <div class="inventory-image-input inventory-image-input--wide">
                    @if($variant?->image_path)
                    <img src="{{ asset('storage/'.$variant->image_path) }}" alt="{{ $variant->variant_code }} image preview" data-image-preview>
                    @else
                    <div class="inventory-image-placeholder" data-image-preview><i class="bi bi-image" aria-hidden="true"></i><span>No image selected</span></div>
                    @endif
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
                <input type="number" name="stock" value="{{ old('stock', $currentStock->quantity_on_hand ?? '') }}" min="0" max="2147483647" step="1" placeholder="0" required>
            </div>
            <div class="form-group">
                <label>Cost Price (&#8369;) *</label>
                <input type="number" name="cost" value="{{ old('cost', $variant->cost_price ?? '') }}" step="0.01" min="0" max="99999999.99" placeholder="0.00" required>
            </div>  
            <div class="form-group">
                <label>Selling Price (&#8369;) *</label>
                <input type="number" name="price" value="{{ old('price', $variant->selling_price ?? '') }}" step="0.01" min="0" max="99999999.99" placeholder="0.00" required>
            </div>
            <div class="form-group">
                <label>Re-order Level *</label>
                <input type="number" name="min_stock" value="{{ old('min_stock', $currentStock->minimum_stock ?? '') }}" min="0" max="2147483647" step="1" placeholder="0" required>
            </div>
            <div class="form-group">
                <label>Critical Level *</label>
                <input type="number" name="critical_stock" value="{{ old('critical_stock', $currentStock->critical_stock ?? '') }}" min="0" max="2147483647" step="1" placeholder="0" required>
            </div>
            <div class="inventory-pricing-preview inventory-span-3" aria-live="polite">
                <div><span>Estimated profit per unit</span><strong data-profit-preview>&#8369;0.00</strong></div>
                <div><span>Gross margin</span><strong data-margin-preview>0.0%</strong></div>
                <p><i class="bi bi-info-circle" aria-hidden="true"></i> Critical level must be equal to or lower than the re-order level.</p>
            </div>
        </div>
    </section>
</div>
