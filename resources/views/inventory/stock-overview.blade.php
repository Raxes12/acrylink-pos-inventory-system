@extends('layouts.app')
@section('title', 'Stock Overview')
@section('page-label', 'Inventory Management')

@php
    use App\Support\StockAdjustmentOptions;

    $stockAlertCounts = $stockAlertCounts ?? \App\Models\Product::stockAlertBreakdown();
    $stockLevels = [
        '' => 'All Stock Levels',
        'attention' => 'Needs Attention ('.$stockAlertCounts['total'].')',
        'normal' => 'Normal',
        'critical' => 'Critical ('.$stockAlertCounts['critical'].')',
        'low' => 'Low Stock ('.$stockAlertCounts['low'].')',
        'out' => 'Out of Stock ('.$stockAlertCounts['out'].')',
    ];
    $oldStockAdjustment = [
        'variant_id' => old('variant_id'),
        'type' => old('type'),
        'reason' => old('reason'),
        'quantity' => old('quantity'),
        'remarks' => old('remarks'),
        'adjustment_token' => old('adjustment_token'),
    ];
@endphp

@section('content')
<x-inventory-nav />
<div class="page-toolbar stock-toolbar">
    <div>
        <h1 class="page-heading">Stock Overview</h1>
        <p class="page-sub">Monitor variant quantities and stock levels in real time.</p>
    </div>
    <div class="toolbar-actions">
        @if(auth()->user()->canEdit())
        <a href="{{ route('stock-receipts.create') }}" class="btn btn-primary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
            Receive Stock
        </a>
        @endif
        <a href="{{ route('inventory.movements') }}" class="btn btn-secondary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M7 7h15"/><path d="m18 3 4 4-4 4"/><path d="M17 17H2"/><path d="m6 13-4 4 4 4"/></svg>
            Movements
        </a>
    </div>
</div>

<form method="GET" action="{{ route('inventory.stock-overview') }}" class="stock-filter" id="stockOverviewLiveFilter" data-live-filter data-no-loading="true" role="search">
    <div class="stock-filter-main">
        <label class="stock-search-field">
            <i class="bi bi-search" aria-hidden="true"></i>
            <input type="search" name="search" value="{{ request('search') }}" placeholder="Search product, variant, part number, or brand..." autocomplete="off">
        </label>
        <label class="stock-category-filter">
            <select name="category" aria-label="Filter stock by category">
                <option value="">All Categories</option>
                @foreach($categories as $cat)
                <option value="{{ $cat }}" {{ request('category')==$cat ? 'selected' : '' }}>{{ $cat }}</option>
                @endforeach
            </select>
        </label>
    </div>
    <div class="stock-filter-footer">
        <input type="hidden" name="stock_level" value="{{ request('stock_level') }}">
        <div class="stock-filter-chips" aria-label="Filter by stock level">
            @foreach($stockLevels as $value => $label)
            <button type="button" class="stock-filter-chip {{ request('stock_level', '') === $value ? 'is-active' : '' }}"
                data-live-filter-choice="stock_level" data-live-filter-value="{{ $value }}"
                aria-pressed="{{ request('stock_level', '') === $value ? 'true' : 'false' }}">{{ $label }}</button>
            @endforeach
        </div>
        <span class="live-filter-status stock-filter-status" data-live-filter-status role="status" aria-live="polite"></span>
    </div>
</form>

<div data-live-results>
<div class="card stock-card">
    <div class="table-wrap">
        <table id="inventoryTable" class="stock-table">
            <thead>
                <tr>
                    <th><button type="button" class="sort-btn" data-sort="name">Product</button></th>
                    <th><button type="button" class="sort-btn" data-sort="code">Variant</button></th>
                    <th><button type="button" class="sort-btn" data-sort="brand">Brand</button></th>
                    <th>Supplier</th>
                    <th><button type="button" class="sort-btn" data-sort="unit">Unit</button></th>
                    <th><button type="button" class="sort-btn" data-sort="stock">On Hand</button></th>
                    <th><button type="button" class="sort-btn" data-sort="reorder">Minimum</button></th>
                    <th><button type="button" class="sort-btn" data-sort="critical">Critical</button></th>
                    <th><button type="button" class="sort-btn" data-sort="status">Status</button></th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse($variants as $variant)
            @php
                $product = $variant->product;
                $stock = $variant->inventoryStock;
                $quantity = (int) ($stock?->quantity_on_hand ?? 0);
                $minimum = (int) ($stock?->minimum_stock ?? 0);
                $critical = (int) ($stock?->critical_stock ?? 0);
                $status = StockAdjustmentOptions::stockStatus($quantity, $minimum, $critical);
                $statusClass = $status === 'Normal' ? 'status-active' : ($status === 'Low Stock' ? 'status-low' : 'status-danger');
                $stockLevel = $quantity <= 0 ? 'out' : ($quantity <= $critical ? 'critical' : ($quantity <= $minimum ? 'low' : 'normal'));
                $variantLabel = trim(($product?->name ?? 'Product').' - '.$variant->variant_code);
            @endphp
            <tr data-live-row
                data-code="{{ $variant->variant_code }}"
                data-name="{{ $product?->name }}"
                data-category="{{ $product?->category }}"
                data-brand="{{ $variant->brand?->name }}"
                data-unit="{{ $variant->unit?->code }}"
                data-stock="{{ $quantity }}"
                data-reorder="{{ $minimum }}"
                data-critical="{{ $critical }}"
                data-status="{{ $status }}"
                data-stock-level="{{ $stockLevel }}">
                <td data-label="Product">
                    <div class="stock-product-name">{{ $product?->name ?? 'Deleted product' }}</div>
                    <div class="stock-product-sub">{{ $product?->category ?? 'Uncategorized' }}</div>
                </td>
                <td data-label="Variant">
                    <div class="stock-code">{{ $variant->variant_code }}</div>
                    <div class="stock-product-sub">
                        {{ collect([$variant->part_number, $variant->size, $variant->color])->filter()->join(' / ') ?: 'No extra details' }}
                    </div>
                </td>
                <td data-label="Brand" class="stock-muted">{{ $variant->brand?->name ?? '-' }}</td>
                <td data-label="Supplier" class="stock-muted">{{ $variant->supplier?->name ?? 'Not assigned' }}</td>
                <td data-label="Unit">{{ $variant->unit?->code ?? '-' }}</td>
                <td data-label="On hand" class="stock-onhand {{ $status !== 'Normal' ? 'stock-onhand--warn' : '' }}">{{ $quantity }}</td>
                <td data-label="Minimum" class="stock-muted">{{ $minimum }}</td>
                <td data-label="Critical" class="stock-muted">{{ $critical }}</td>
                <td data-label="Status"><span class="status {{ $statusClass }}">{{ $status }}</span></td>
                <td data-label="Actions">
                    <div class="table-actions">
                        @if(auth()->user()->canEdit())
                        <button type="button" class="btn btn-secondary btn-sm stock-adjust-btn" title="Adjust stock for {{ $variantLabel }}" aria-label="Adjust stock for {{ $variantLabel }}"
                            data-variant-id="{{ $variant->id }}"
                            data-product="{{ $product?->name ?? 'Deleted product' }}"
                            data-variant="{{ $variant->variant_code }}"
                            data-part-number="{{ $variant->part_number ?: 'Not specified' }}"
                            data-current="{{ $quantity }}"
                            data-minimum="{{ $minimum }}"
                            data-critical="{{ $critical }}"
                            data-unit="{{ $variant->unit?->code ?: 'pcs' }}"
                            data-action="{{ route('inventory.adjust-stock', $variant) }}">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 21v-7"/><path d="M4 10V3"/><path d="M12 21v-9"/><path d="M12 8V3"/><path d="M20 21v-5"/><path d="M20 12V3"/><path d="M2 14h4"/><path d="M10 8h4"/><path d="M18 16h4"/></svg>
                            <span>Adjust</span>
                        </button>
                        @else
                        <span class="stock-muted">-</span>
                        @endif
                        <a href="{{ route('inventory.show', $variant) }}" class="btn btn-secondary btn-sm icon-btn" title="View variant" aria-label="View {{ $variantLabel }}">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z"/><circle cx="12" cy="12" r="3"/></svg>
                        </a>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="10" class="empty-state">No variants found. Try adjusting your filters.</td>
            </tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="view-blades-footer">
        <div class="pagination-summary">
            Showing {{ $variants->firstItem() ?? 0 }}-{{ $variants->lastItem() ?? 0 }} of {{ $variants->total() }} variants
        </div>

        @if($variants->hasPages())
        @php
            $currentPage = $variants->currentPage();
            $lastPage = $variants->lastPage();
            $pages = collect([1, $currentPage - 1, $currentPage, $currentPage + 1, $lastPage])
                ->filter(fn ($page) => $page >= 1 && $page <= $lastPage)
                ->unique()
                ->sort()
                ->values();
            $previousPage = null;
        @endphp
        <nav class="rules-pagination" aria-label="Stock variant pagination">
            @if($variants->onFirstPage())
                <span class="rules-page is-disabled" aria-disabled="true">Previous</span>
            @else
                <a class="rules-page" href="{{ $variants->previousPageUrl() }}" rel="prev">Previous</a>
            @endif

            @foreach($pages as $page)
                @if($previousPage && $page > $previousPage + 1)
                    <span class="rules-page rules-page--gap" aria-hidden="true">...</span>
                @endif

                @if($page === $currentPage)
                    <span class="rules-page is-active" aria-current="page">{{ $page }}</span>
                @else
                    <a class="rules-page" href="{{ $variants->url($page) }}">{{ $page }}</a>
                @endif

                @php $previousPage = $page; @endphp
            @endforeach

            @if($variants->hasMorePages())
                <a class="rules-page" href="{{ $variants->nextPageUrl() }}" rel="next">Next</a>
            @else
                <span class="rules-page is-disabled" aria-disabled="true">Next</span>
            @endif
        </nav>
        @endif
    </div>
</div>
</div>

@if(auth()->user()->canEdit())
<div class="stock-modal" id="stockAdjustModal" hidden role="dialog" aria-modal="true" aria-labelledby="stockAdjustTitle"
     data-reasons='@json(StockAdjustmentOptions::REASONS)'
     data-type-labels='@json(StockAdjustmentOptions::TYPES)'
     data-old-adjustment='@json($oldStockAdjustment)'
     data-errors='@json($errors->all())'
     data-performed-by="{{ auth()->user()->name }}">
    <div class="stock-modal-backdrop" data-close-adjust></div>
    <form method="POST" class="stock-modal-panel" id="stockAdjustForm">
        @csrf
        <input type="hidden" name="variant_id" value="">
        <input type="hidden" name="adjustment_token" value="">
        <div class="stock-modal-head">
            <h2 id="stockAdjustTitle">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 21v-7"/><path d="M4 10V3"/><path d="M12 21v-9"/><path d="M12 8V3"/><path d="M20 21v-5"/><path d="M20 12V3"/><path d="M2 14h4"/><path d="M10 8h4"/><path d="M18 16h4"/></svg>
                Manual Stock Adjustment
            </h2>
            <button type="button" class="modal-close-btn" data-close-adjust aria-label="Close stock adjustment">&times;</button>
        </div>
        <div class="stock-modal-body">
            <div class="stock-adjust-errors" id="stockAdjustErrors" role="alert" hidden></div>
            <section class="stock-adjust-product" aria-label="Selected product information">
                <div><span>Product</span><strong id="stockAdjustProduct">&mdash;</strong></div>
                <div><span>Variant / Part Number</span><strong id="stockAdjustVariant">&mdash;</strong></div>
                <div><span>Current Stock</span><strong id="stockAdjustCurrent">&mdash;</strong></div>
            </section>
            <label class="form-group">
                <span>Adjustment Type *</span>
                <select name="type" required>
                    @foreach(StockAdjustmentOptions::TYPES as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <label class="form-group">
                <span>Reason for Adjustment *</span>
                <select name="reason" required>
                    <option value="">&mdash; Select a reason &mdash;</option>
                </select>
            </label>
            <label class="form-group">
                <span>Quantity *</span>
                <input type="number" name="quantity" min="1" max="2147483647" step="1" inputmode="numeric" required aria-describedby="stockAdjustPreview stockAdjustQuantityError">
                <small class="field-error" id="stockAdjustQuantityError" hidden></small>
            </label>
            <div class="stock-adjust-preview" id="stockAdjustPreview" role="status" aria-live="polite">
                <div class="stock-preview-title">Stock Adjustment Preview</div>
                <dl>
                    <div><dt>Current Stock</dt><dd data-preview-current>&mdash;</dd></div>
                    <div><dt>Adjustment</dt><dd data-preview-change>&mdash;</dd></div>
                    <div class="stock-preview-result"><dt>Resulting Stock</dt><dd data-preview-result>&mdash;</dd></div>
                    <div><dt>Stock Status</dt><dd><span class="stock-preview-status" data-preview-status>Waiting for quantity</span></dd></div>
                </dl>
                <p class="stock-preview-warning" data-preview-warning hidden></p>
            </div>
            <label class="form-group">
                <span>Remarks <em data-remarks-requirement>(Optional)</em></span>
                <textarea name="remarks" maxlength="1000" rows="3" placeholder="Provide additional details about this stock adjustment..."></textarea>
                <small class="form-help">Required for Other and Lost / Missing adjustments.</small>
            </label>
        </div>
        <div class="stock-modal-actions">
            <button type="button" class="btn btn-secondary" data-close-adjust>Cancel</button>
            <button type="button" class="btn btn-primary" data-review-adjustment disabled>Review Adjustment</button>
        </div>
    </form>
</div>

<div class="stock-modal stock-confirm-modal" id="stockConfirmModal" hidden role="dialog" aria-modal="true" aria-labelledby="stockConfirmTitle">
    <div class="stock-modal-backdrop" data-close-confirm></div>
    <div class="stock-modal-panel stock-confirm-panel">
        <div class="stock-modal-head">
            <h2 id="stockConfirmTitle"><i class="bi bi-shield-check" aria-hidden="true"></i> Confirm Stock Adjustment</h2>
            <button type="button" class="modal-close-btn" data-close-confirm aria-label="Close confirmation">&times;</button>
        </div>
        <div class="stock-modal-body">
            <p class="stock-confirm-question">Are you sure you want to apply this stock adjustment?</p>
            <dl class="stock-confirm-summary">
                <div><dt>Product</dt><dd data-confirm-product>&mdash;</dd></div>
                <div><dt>Variant</dt><dd data-confirm-variant>&mdash;</dd></div>
                <div><dt>Adjustment Type</dt><dd data-confirm-type>&mdash;</dd></div>
                <div><dt>Reason</dt><dd data-confirm-reason>&mdash;</dd></div>
                <div><dt>Quantity</dt><dd data-confirm-quantity>&mdash;</dd></div>
                <div><dt>Current Stock</dt><dd data-confirm-current>&mdash;</dd></div>
                <div><dt>Resulting Stock</dt><dd data-confirm-result>&mdash;</dd></div>
                <div><dt>Performed By</dt><dd>{{ auth()->user()->name }}</dd></div>
            </dl>
        </div>
        <div class="stock-modal-actions">
            <button type="button" class="btn btn-secondary" data-close-confirm>Cancel</button>
            <button type="button" class="btn btn-primary" data-confirm-adjustment><span data-confirm-label>Confirm Adjustment</span></button>
        </div>
    </div>
</div>
@endif
@endsection

@push('styles')
@vite('resources/css/views/inventory-index-1.css')
@endpush

@push('scripts')
@vite('resources/js/views/inventory-index-1.js')
@endpush
