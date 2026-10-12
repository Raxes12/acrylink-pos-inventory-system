@extends('layouts.app')
@section('title', 'Stock Overview')
@section('page-label', 'Inventory Management')

@php
    $stockAlertCounts = $stockAlertCounts ?? \App\Models\Product::stockAlertBreakdown();
    $stockLevels = [
        '' => 'All Stock Levels',
        'attention' => 'Needs Attention ('.$stockAlertCounts['total'].')',
        'normal' => 'Normal',
        'critical' => 'Critical ('.$stockAlertCounts['critical'].')',
        'low' => 'Low Stock ('.$stockAlertCounts['low'].')',
        'out' => 'Out of Stock ('.$stockAlertCounts['out'].')',
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
                $status = $quantity <= 0 ? 'Out of Stock' : ($quantity <= $critical ? 'Critical' : ($quantity <= $minimum ? 'Low Stock' : 'Normal'));
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
                <td>
                    <div class="stock-product-name">{{ $product?->name ?? 'Deleted product' }}</div>
                    <div class="stock-product-sub">{{ $product?->category ?? 'Uncategorized' }}</div>
                </td>
                <td>
                    <div class="stock-code">{{ $variant->variant_code }}</div>
                    <div class="stock-product-sub">
                        {{ collect([$variant->part_number, $variant->size, $variant->color])->filter()->join(' / ') ?: 'No extra details' }}
                    </div>
                </td>
                <td class="stock-muted">{{ $variant->brand?->name ?? '-' }}</td>
                <td class="stock-muted">{{ $variant->supplier?->name ?? 'Not assigned' }}</td>
                <td>{{ $variant->unit?->code ?? '-' }}</td>
                <td class="stock-onhand {{ $status !== 'Normal' ? 'stock-onhand--warn' : '' }}">{{ $quantity }}</td>
                <td class="stock-muted">{{ $minimum }}</td>
                <td class="stock-muted">{{ $critical }}</td>
                <td><span class="status {{ $statusClass }}">{{ $status }}</span></td>
                <td>
                    <div class="table-actions">
                        @if(auth()->user()->canEdit())
                        <button type="button" class="btn btn-secondary btn-sm stock-adjust-btn" title="Adjust stock for {{ $variantLabel }}" aria-label="Adjust stock for {{ $variantLabel }}"
                            data-product="{{ $variantLabel }}"
                            data-current="{{ $quantity }}"
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
<div class="stock-modal" id="stockAdjustModal" hidden role="dialog" aria-modal="true" aria-labelledby="stockAdjustTitle">
    <div class="stock-modal-backdrop" data-close-adjust></div>
    <form method="POST" class="stock-modal-panel" id="stockAdjustForm">
        @csrf
        <div class="stock-modal-head">
            <h2 id="stockAdjustTitle">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 21v-7"/><path d="M4 10V3"/><path d="M12 21v-9"/><path d="M12 8V3"/><path d="M20 21v-5"/><path d="M20 12V3"/><path d="M2 14h4"/><path d="M10 8h4"/><path d="M18 16h4"/></svg>
                Adjust Stock
            </h2>
            <button type="button" class="modal-close-btn" data-close-adjust aria-label="Close stock adjustment">&times;</button>
        </div>
        <div class="stock-modal-body">
            <div class="stock-modal-product" id="stockAdjustProduct"></div>
            <div class="stock-modal-current" id="stockAdjustCurrent"></div>
            <label class="form-group">
                <span>Type</span>
                <select name="type" required>
                    <option value="stock_in">Stock In (Increase)</option>
                    <option value="stock_out">Stock Out (Decrease)</option>
                    <option value="adjustment">Other Increase</option>
                </select>
            </label>
            <label class="form-group">
                <span>Quantity</span>
                <input type="number" name="quantity" min="1" max="2147483647" step="1" required aria-describedby="stockAdjustPreview">
            </label>
            <div class="stock-adjust-preview" id="stockAdjustPreview" role="status" aria-live="polite">
                Enter a quantity to preview the resulting stock.
            </div>
            <label class="form-group">
                <span>Remarks</span>
                <textarea name="remarks" placeholder="Explain why this stock is changing..." required minlength="1"></textarea>
            </label>
        </div>
        <div class="stock-modal-actions">
            <button type="button" class="btn btn-secondary" data-close-adjust>Cancel</button>
            <button type="submit" class="btn btn-primary">Apply Stock Change</button>
        </div>
    </form>
</div>
@endif
@endsection

@push('styles')
@vite('resources/css/views/inventory-index-1.css')
@endpush

@push('scripts')
@vite('resources/js/views/inventory-index-1.js')
@endpush
