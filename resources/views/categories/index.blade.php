@extends('layouts.app')
@section('title', 'Categories')
@section('page-label', 'Categories')

@section('content')
@php
    $totalProducts = $categories->sum('products_count');
    $totalVariants = $categories->sum('variants_count');
    $lowStockTotal = $categories->sum('low_stock_count');
    $emptyCategories = $categories->where('products_count', 0)->count();
@endphp

<div class="categories-page">
<x-page-header
    title="{{ $selectedCategory ? $selectedCategory->name : 'Product Categories' }}"
    subtitle="{{ $selectedCategory ? 'Review the products, variants, stock health, and suppliers assigned to this category.' : 'Organize products into clear groups and quickly spot categories that need attention.' }}"
>
    <x-slot:actions>
        @if($selectedCategory)
        <a href="{{ route('categories.index') }}" class="btn btn-secondary"><i class="bi bi-arrow-left" aria-hidden="true"></i> All Categories</a>
        <a href="{{ route('inventory.index', ['category' => $selectedCategory->name]) }}" class="btn btn-primary"><x-icon name="products" /> Open in Inventory</a>
        @else
        <a href="{{ route('categories.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg" aria-hidden="true"></i> Add Category</a>
        @endif
    </x-slot:actions>
</x-page-header>

@if(!$selectedCategory)
<div class="category-kpis" aria-label="Category summary">
    <article class="card category-kpi"><span class="category-kpi-icon"><x-icon name="categories" /></span><div><span>Categories</span><strong>{{ number_format($categories->count()) }}</strong><small>{{ $emptyCategories }} currently empty</small></div></article>
    <article class="card category-kpi"><span class="category-kpi-icon"><x-icon name="products" /></span><div><span>Products</span><strong>{{ number_format($totalProducts) }}</strong><small>{{ number_format($totalVariants) }} variants tracked</small></div></article>
    <article class="card category-kpi category-kpi--warning"><span class="category-kpi-icon"><x-icon name="alerts" /></span><div><span>Low-stock variants</span><strong>{{ number_format($lowStockTotal) }}</strong><small>{{ $lowStockTotal > 0 ? 'Review replenishment needs' : 'Stock levels look healthy' }}</small></div></article>
    <article class="card category-kpi category-kpi--muted"><span class="category-kpi-icon"><i class="bi bi-folder2-open" aria-hidden="true"></i></span><div><span>Empty categories</span><strong>{{ number_format($emptyCategories) }}</strong><small>Safe to remove if unused</small></div></article>
</div>

<section class="category-panel" aria-labelledby="categoryDirectoryHeading">
    <div class="card-header category-panel-header">
        <div><span class="card-title" id="categoryDirectoryHeading"><i class="bi bi-grid" aria-hidden="true"></i> Category Directory</span><span class="category-panel-note"><span data-category-visible-count>{{ $categories->count() }}</span> of {{ $categories->count() }} categories shown</span></div>
        <label class="category-directory-search"><span class="visually-hidden">Search categories</span><i class="bi bi-search" aria-hidden="true"></i><input type="search" placeholder="Find a category..." autocomplete="off" data-category-search><button type="button" aria-label="Clear category search" data-category-search-clear hidden><i class="bi bi-x-lg" aria-hidden="true"></i></button></label>
    </div>

    <div class="category-card-grid" data-category-grid>
        @forelse($categories as $category)
        <article class="category-card" data-category-card data-category-name="{{ str($category->name)->lower() }}">
            <a href="{{ route('categories.index', ['category' => $category->id]) }}" class="category-card-main">
                <span class="category-card-icon">{{ strtoupper(substr($category->name, 0, 2)) }}</span>
                <span class="category-card-copy">
                    <span class="category-name">{{ $category->name }}</span>
                    <span class="category-meta">{{ $category->products_count }} product{{ $category->products_count === 1 ? '' : 's' }}</span>
                </span>
                <i class="bi bi-chevron-right category-card-chevron" aria-hidden="true"></i>
            </a>
            <a href="{{ route('categories.index', ['category' => $category->id]) }}" class="category-card-stats">
                <span>
                    <strong>{{ number_format($category->variants_count) }}</strong>
                    <small>Variants</small>
                </span>
                <span class="{{ $category->low_stock_count > 0 ? 'has-attention' : '' }}">
                    <strong>{{ number_format($category->low_stock_count) }}</strong>
                    <small>Low stock</small>
                </span>
                <span>
                    <strong>{{ number_format($category->stock_units ?? 0) }}</strong>
                    <small>Total units</small>
                </span>
            </a>
            <div class="category-actions">
                <a href="{{ route('categories.index', ['category' => $category->id]) }}" class="btn btn-secondary btn-sm category-view-action"><i class="bi bi-eye" aria-hidden="true"></i> View products</a>
                <a href="{{ route('categories.edit', $category) }}" class="btn btn-secondary btn-sm" aria-label="Rename {{ $category->name }}"><i class="bi bi-pencil" aria-hidden="true"></i><span class="category-action-label">Rename</span></a>
                <form method="POST" action="{{ route('categories.destroy', $category) }}"
                    data-confirm
                    data-confirm-title="Delete {{ $category->name }}?"
                    data-confirm-message="This category will be permanently deleted. This action cannot be undone."
                    data-confirm-button="Delete category">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm" @disabled($category->products_count > 0) aria-label="Delete {{ $category->name }}" title="{{ $category->products_count > 0 ? 'Move or remove its products before deleting this category.' : 'Delete category' }}"><i class="bi bi-trash" aria-hidden="true"></i><span class="category-action-label">Delete</span></button>
                </form>
            </div>
        </article>
        @empty
        <x-empty-state title="No categories yet" message="Create the first category to start organizing inventory products." />
        @endforelse
        <div class="category-search-empty" data-category-search-empty hidden><span class="category-search-empty-icon"><i class="bi bi-search" aria-hidden="true"></i></span><strong>No matching categories</strong><span>Try another name or clear the search.</span></div>
    </div>
</section>
@else
<section class="card category-products">
    <div class="card-header category-products-header">
        <div>
            <span class="card-title"><x-icon name="products" /> Products in {{ $selectedCategory->name }}</span>
            <div class="category-panel-note">Search by product name, variant code, barcode, or brand.</div>
        </div>
        <span class="category-result-count" data-live-sync="category-product-count">{{ number_format($products->total()) }} product{{ $products->total() === 1 ? '' : 's' }}</span>
    </div>

    <form method="GET" action="{{ route('categories.index') }}" class="category-filter" data-live-filter>
        <input type="hidden" name="category" value="{{ $selectedCategory->id }}">
        <label class="category-product-search"><span class="visually-hidden">Search products in {{ $selectedCategory->name }}</span><i class="bi bi-search" aria-hidden="true"></i><input type="search" name="search" value="{{ $search }}" placeholder="Search products, variants, barcodes, or brands..." autocomplete="off"><a href="{{ route('categories.index', ['category' => $selectedCategory->id]) }}" data-live-filter-clear aria-label="Clear product search"><i class="bi bi-x-lg" aria-hidden="true"></i></a></label>
        <span class="live-filter-status" data-live-filter-status role="status" aria-live="polite"></span>
        <noscript><button type="submit" class="btn btn-primary">Search</button></noscript>
    </form>

    <div data-live-results>
    <div class="category-table-wrap" tabindex="0" role="region" aria-label="Products in {{ $selectedCategory->name }}">
    <table class="category-products-table">
        <thead>
            <tr>
                <th>Product / Variants</th>
                <th class="text-right">Price Range</th>
                <th class="text-right">Total Stock</th>
                <th>Suppliers</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($products as $product)
            @php
                $variants = $product->variants;
                $variantPreview = $variants->take(3);
                $minPrice = $product->min_selling_price;
                $maxPrice = $product->max_selling_price;
                $lowCount = (int) $product->low_stock_variants_count;
                $outCount = (int) $product->out_of_stock_variants_count;
                $supplierNames = $product->supplier_names;
            @endphp
            <tr>
                <td data-label="Product / Variants">
                    <div class="category-product-name">{{ $product->name }}</div>
                    <div class="category-product-count">{{ $product->variants_count }} variant{{ $product->variants_count === 1 ? '' : 's' }}</div>
                    <div class="category-variant-preview">
                    @foreach($variantPreview as $variant)
                    <span>{{ collect([$variant->variant_code, $variant->part_number, $variant->brand?->name, $variant->size, $variant->color, $variant->unit?->code])->filter()->join(' / ') }}</span>
                    @endforeach
                    @if($variants->count() > $variantPreview->count())
                    <span class="category-variant-more">+ {{ $variants->count() - $variantPreview->count() }} more</span>
                    @endif
                    </div>
                </td>
                <td class="text-right category-price" data-label="Price Range">
                    @if($minPrice === null)
                    -
                    @elseif((float) $minPrice === (float) $maxPrice)
                    &#8369;{{ number_format((float) $minPrice, 2) }}
                    @else
                    &#8369;{{ number_format((float) $minPrice, 2) }} - &#8369;{{ number_format((float) $maxPrice, 2) }}
                    @endif
                </td>
                <td class="text-right category-stock {{ $lowCount > 0 ? 'has-attention' : '' }}" data-label="Total Stock">
                    <strong>{{ number_format($product->total_stock_units) }}</strong><small> units</small>
                </td>
                <td class="category-suppliers" data-label="Suppliers">{{ $supplierNames->isNotEmpty() ? $supplierNames->join(', ') : 'No supplier assigned' }}</td>
                <td data-label="Status">
                    @if($outCount > 0)
                    <span class="status status-inactive">{{ $outCount }} Out</span>
                    @elseif($lowCount > 0)
                    <span class="status status-low">{{ $lowCount }} Low</span>
                    @else
                    <span class="status status-active">OK</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="5"><x-empty-state title="No matching products" message="Try another search, or open Inventory to add products to this category." /></td></tr>
            @endforelse
        </tbody>
    </table>
    </div>

    <div class="category-pagination">{{ $products->links() }}</div>
    </div>
</section>
@endif

</div>

@endsection

@push('styles')
@vite('resources/css/views/categories-index-1.css')
@endpush

@push('scripts')
@vite('resources/js/views/categories-index-1.js')
@endpush
