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

<div class="page-toolbar">
    <div>
        <div class="page-heading" style="margin:0;">{{ $selectedCategory ? $selectedCategory->name : 'Product Categories' }}</div>
        <div class="page-sub">
            {{ $selectedCategory ? 'Products assigned to this category.' : 'Choose a category card to view its products.' }}
        </div>
    </div>

    @if($selectedCategory)
    <div class="category-toolbar-actions">
        <a href="{{ route('categories.index') }}" class="btn btn-secondary">Back to Categories</a>
        <a href="{{ route('inventory.index', ['category' => $selectedCategory->name]) }}" class="btn btn-primary">Open Inventory</a>
    </div>
    @else
    <a href="{{ route('categories.create') }}" class="btn btn-primary">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Add Category
    </a>
    @endif
</div>

@if(!$selectedCategory)
<div class="grid-4 category-kpis">
    <div class="card metric-card">
        <div class="metric-label">Categories</div>
        <div class="metric-value text-accent">{{ $categories->count() }}</div>
        <div class="page-sub">{{ $emptyCategories }} empty</div>
    </div>
    <div class="card metric-card">
        <div class="metric-label">Products</div>
        <div class="metric-value">{{ $totalProducts }}</div>
        <div class="page-sub">{{ $totalVariants }} variants tracked</div>
    </div>
    <div class="card metric-card">
        <div class="metric-label">Low Stock</div>
        <div class="metric-value text-warning">{{ $lowStockTotal }}</div>
        <div class="page-sub">Needs replenishment</div>
    </div>
    <div class="card metric-card">
        <div class="metric-label">Empty</div>
        <div class="metric-value">{{ $emptyCategories }}</div>
        <div class="page-sub">Can be deleted</div>
    </div>
</div>

<section class="category-panel">
    <div class="card-header">
        <span class="card-title">Browse Categories</span>
    </div>

    <div class="category-card-grid">
        @forelse($categories as $category)
        <article class="category-card">
            <a href="{{ route('categories.index', ['category' => $category->id]) }}" class="category-card-main">
                <span class="category-card-icon">{{ strtoupper(substr($category->name, 0, 2)) }}</span>
                <span class="category-card-copy">
                    <span class="category-name">{{ $category->name }}</span>
                    <span class="category-meta">Click to view products</span>
                </span>
            </a>
            <a href="{{ route('categories.index', ['category' => $category->id]) }}" class="category-card-stats">
                <span>
                    <strong>{{ $category->variants_count }}</strong>
                    <small>Variants</small>
                </span>
                <span>
                    <strong>{{ $category->low_stock_count }}</strong>
                    <small>Low Variants</small>
                </span>
                <span>
                    <strong>{{ number_format($category->stock_units ?? 0) }}</strong>
                    <small>Total Units</small>
                </span>
            </a>
            <div class="category-actions">
                <a href="{{ route('categories.edit', $category) }}" class="btn btn-secondary btn-sm">Rename</a>
                <form method="POST" action="{{ route('categories.destroy', $category) }}"
                    data-confirm
                    data-confirm-title="Delete Category"
                    data-confirm-message="Delete category {{ $category->name }}? This action cannot be undone."
                    data-confirm-button="Delete">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm" @disabled($category->products_count > 0) title="{{ $category->products_count > 0 ? 'Only empty categories can be deleted.' : 'Delete category' }}">Delete</button>
                </form>
            </div>
        </article>
        @empty
        <div class="empty-state">No categories found.</div>
        @endforelse
    </div>
</section>
@else
<section class="card category-products">
    <div class="card-header category-products-header">
        <div>
            <span class="card-title">{{ $selectedCategory->name }} Products</span>
            <div class="page-sub">{{ $products->total() }} product{{ $products->total() === 1 ? '' : 's' }} found in this category</div>
        </div>
    </div>

    <form method="GET" action="{{ route('categories.index') }}" class="filter-bar category-filter" data-live-filter>
        <input type="hidden" name="category" value="{{ $selectedCategory->id }}">
        <input type="text" name="search" value="{{ $search }}" placeholder="Search products in {{ $selectedCategory->name }}...">
        <button type="submit" class="btn btn-primary">Search</button>
        <a href="{{ route('categories.index', ['category' => $selectedCategory->id]) }}" class="btn btn-secondary" data-live-filter-clear>Clear</a>
        <span class="live-filter-status" data-live-filter-status role="status" aria-live="polite"></span>
    </form>

    <div data-live-results>
    <table>
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
                <td>
                    <div style="font-weight:700;">{{ $product->name }}</div>
                    <div class="text-muted" style="font-size:11px;margin-top:2px;">
                        {{ $product->variants_count }} variant{{ $product->variants_count === 1 ? '' : 's' }}
                    </div>
                    @foreach($variantPreview as $variant)
                    <div class="text-muted" style="font-size:11px;margin-top:2px;">
                        {{ collect([$variant->variant_code, $variant->part_number, $variant->brand?->name, $variant->size, $variant->color, $variant->unit?->code])->filter()->join(' / ') }}
                    </div>
                    @endforeach
                    @if($variants->count() > $variantPreview->count())
                    <div class="text-muted" style="font-size:11px;margin-top:2px;">+ {{ $variants->count() - $variantPreview->count() }} more</div>
                    @endif
                </td>
                <td class="text-right fw-bold">
                    @if($minPrice === null)
                    -
                    @elseif((float) $minPrice === (float) $maxPrice)
                    &#8369;{{ number_format((float) $minPrice, 2) }}
                    @else
                    &#8369;{{ number_format((float) $minPrice, 2) }} - &#8369;{{ number_format((float) $maxPrice, 2) }}
                    @endif
                </td>
                <td class="text-right">
                    <span style="color:{{ $lowCount > 0 ? 'var(--warning)' : 'var(--accent2)' }};font-weight:800;">{{ number_format($product->total_stock_units) }}</span>
                    <span class="text-muted" style="font-size:11px;">units</span>
                </td>
                <td class="text-muted" style="font-size:12px;">{{ $supplierNames->isNotEmpty() ? $supplierNames->join(', ') : 'None' }}</td>
                <td>
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
            <tr><td colspan="5" class="empty-state">No products found for this category.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="category-pagination">{{ $products->links() }}</div>
    </div>
</section>
@endif

@endsection

@push('styles')
@vite('resources/css/views/categories-index-1.css')
@endpush
