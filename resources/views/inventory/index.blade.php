@extends('layouts.app')
@section('title', 'Products')
@section('page-label', 'Products')

@section('content')
<div class="products-page">
    <x-inventory-nav />
    <x-page-header title="Products" subtitle="Manage product variants, pricing, suppliers, and availability.">
        <x-slot:actions>
            @if(auth()->user()->canEdit())
            <a href="{{ route('inventory.create') }}" class="btn btn-primary">
                <x-icon name="plus" />
                Add Variant
            </a>
            @endif
        </x-slot:actions>
    </x-page-header>

    <form method="GET" action="{{ route('inventory.index') }}" class="filter-bar products-filter" id="inventoryLiveFilter" data-live-filter data-no-loading="true" role="search">
        <label class="products-search">
            <span class="visually-hidden">Search products</span>
            <x-icon name="search" />
            <input type="search" name="search" value="{{ request('search') }}" placeholder="Search product, code, barcode, part number, or brand..." autocomplete="off">
        </label>
        <select name="category" id="inventoryCategory" aria-label="Filter by category">
            <option value="">All Categories</option>
            @foreach($categories as $cat)
            <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
            @endforeach
        </select>
        <select name="status" id="inventoryStatus" aria-label="Filter by product status">
            <option value="">All Statuses</option>
            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
        </select>
        <span class="live-filter-status products-filter-status" data-live-filter-status role="status" aria-live="polite"></span>
    </form>
    
    <section class="card products-card" aria-label="Product variants" data-live-results>
        <div class="table-wrap">
            <table id="inventoryTable" class="inventory-products-table">
                <thead>
                    <tr>
                        <th><button type="button" class="sort-btn" data-sort="name">Product / Variant</button></th>
                        <th><button type="button" class="sort-btn" data-sort="part">Part / Brand</button></th>
                        <th><button type="button" class="sort-btn" data-sort="size">Details</button></th>
                        <th><button type="button" class="sort-btn" data-sort="supplier">Supplier</button></th>
                        <th class="text-right"><button type="button" class="sort-btn sort-btn--right" data-sort="price">Selling</button></th>
                        <th class="text-right"><button type="button" class="sort-btn sort-btn--right" data-sort="cost">Cost</button></th>
                        <th><button type="button" class="sort-btn" data-sort="stock">Stock</button></th>
                        <th class="text-center inventory-actions-col">Actions</th>
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
                    $stockState = $quantity <= 0
                        ? ['class' => 'out', 'label' => 'Out of stock']
                        : ($quantity <= $critical
                            ? ['class' => 'critical', 'label' => 'Critical']
                            : ($quantity <= $minimum
                                ? ['class' => 'low', 'label' => 'Low stock']
                                : ['class' => 'in', 'label' => 'In stock']));
                    $detailParts = collect([$variant->size, $variant->color, $variant->unit?->code])->filter();
                @endphp
                <tr data-live-row
                    data-name="{{ $product?->name }} {{ $variant->variant_code }}"
                    data-category="{{ $product?->category }}"
                    data-part="{{ $variant->part_number }}"
                    data-brand="{{ $variant->brand?->name }}"
                    data-size="{{ $detailParts->join(' ') }}"
                    data-price="{{ $variant->selling_price }}"
                    data-cost="{{ $variant->cost_price }}"
                    data-stock="{{ $quantity }}"
                    data-supplier="{{ $variant->supplier?->name ?? '' }}"
                    data-status="{{ $variant->status }}">
                    <td class="product-cell product-cell--{{ $variant->status }}">
                        @if($variant->image_path)
                        <img
                            src="{{ asset('storage/'.$variant->image_path) }}"
                            class="inventory-thumb"
                            width="46"
                            height="46"
                            loading="lazy"
                            alt="{{ $product?->name ?? 'Product' }} {{ $variant->variant_code }}">
                        @else
                        <div class="inventory-thumb inventory-thumb--empty" aria-hidden="true">
                            {{ strtoupper(substr($product?->name ?? 'PR', 0, 2)) }}
                        </div>
                        @endif
                        <div class="product-info">
                            <a href="{{ route('inventory.show', $variant) }}" class="stock-product-name">{{ $product?->name ?? 'Deleted product' }}</a>
                            <div class="product-variant-meta">
                                <span class="stock-code">{{ $variant->variant_code }}</span>
                                <span class="product-availability product-availability--{{ $variant->status }}" title="Variant is {{ $variant->status }}">
                                    <span class="product-availability-dot" aria-hidden="true"></span>
                                    {{ ucfirst($variant->status) }}
                                </span>
                                @if($product?->category)<span>{{ $product->category }}</span>@endif
                            </div>
                        </div>
                    </td>
                    <td>
                        <strong class="product-part-number">{{ $variant->part_number ?: 'No part number' }}</strong>
                        <span class="product-secondary">{{ $variant->brand?->name ?? 'No brand' }}</span>
                    </td>
                    <td>
                        @if($detailParts->isNotEmpty())
                        <div class="product-attributes">
                            @foreach($detailParts as $detail)<span>{{ $detail }}</span>@endforeach
                        </div>
                        @else
                        <span class="product-secondary">Not specified</span>
                        @endif
                    </td>
                    <td>
                        @if($variant->supplier)
                        <a href="{{ route('suppliers.show', $variant->supplier) }}" class="product-supplier">{{ $variant->supplier->name }}</a>
                        @else
                        <span class="product-secondary">No supplier</span>
                        @endif
                    </td>
                    <td class="text-right product-selling-price">&#8369;{{ number_format((float) $variant->selling_price, 2) }}</td>
                    <td class="text-right product-cost-price">&#8369;{{ number_format((float) $variant->cost_price, 2) }}</td>
                    <td>
                        <div class="product-stock product-stock--{{ $stockState['class'] }}">
                            <strong>{{ number_format($quantity) }}</strong>
                            <span>{{ $stockState['label'] }}</span>
                        </div>
                    </td>
                    <td>
                        <div class="table-actions inventory-row-actions">
                            <a href="{{ route('inventory.show', $variant) }}" class="btn btn-secondary btn-sm product-action-btn" title="View variant" aria-label="View {{ $variant->variant_code }}">
                                <i class="bi bi-eye" aria-hidden="true"></i>
                            </a>
                            @if(auth()->user()->canEdit())
                            <a href="{{ route('inventory.edit', $variant) }}" class="btn btn-secondary btn-sm product-action-btn" title="Edit variant" aria-label="Edit {{ $variant->variant_code }}">
                                <i class="bi bi-pencil" aria-hidden="true"></i>
                            </a>
                            @if($variant->status === 'active')
                            <button type="button" class="btn btn-danger btn-sm product-action-btn" disabled title="Set this variant to inactive before deleting it" aria-label="Delete disabled for active variant {{ $variant->variant_code }}">
                                <i class="bi bi-trash3" aria-hidden="true"></i>
                            </button>
                            @else
                            <form method="POST" action="{{ route('inventory.destroy', $variant) }}"
                                data-confirm
                                data-confirm-title="Delete variant?"
                                data-confirm-message="Delete {{ $product?->name }} ({{ $variant->variant_code }})? This cannot be undone."
                                data-confirm-button="Delete">
                                @csrf @method('DELETE')
                                <button type="button" class="btn btn-danger btn-sm product-action-btn" title="Delete variant" aria-label="Delete {{ $variant->variant_code }}" data-confirm-trigger>
                                    <i class="bi bi-trash3" aria-hidden="true"></i>
                                </button>
                            </form>
                            @endif
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <x-empty-state
                    colspan="8"
                    title="No product variants found"
                    message="Try clearing the current filters or add a new product variant."
                    icon="products"
                />
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
    </section>
</div>
@endsection

@push('styles')
@vite('resources/css/views/inventory-index-1.css')
@endpush

@push('scripts')
@vite('resources/js/views/inventory-index-1.js')
@endpush
