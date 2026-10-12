@extends('layouts.app')
@section('title', 'Inventory Reports')
@section('page-label', 'Inventory Reports')

@section('content')
<div class="reports-page">
    <div class="page-toolbar">
        <div>
            <h1 class="page-heading">Inventory Reports</h1>
            <p class="page-sub">Variant-level stock, value, supplier, and reorder monitoring.</p>
        </div>
        <div class="page-actions" data-live-sync="report-actions">
            <a href="{{ route('reports.index') }}" class="btn btn-secondary">Sales Reports</a>
            <a href="{{ $exportUrl }}" class="btn report-export report-export--csv"><i class="bi bi-file-earmark-spreadsheet" aria-hidden="true"></i> Export CSV</a>
            <a href="{{ $exportPdfUrl }}" class="btn report-export report-export--pdf"><i class="bi bi-file-earmark-pdf" aria-hidden="true"></i> Export PDF</a>
        </div>
        
    </div>

    <form method="GET" action="{{ route('reports.inventory') }}" class="card inventory-report-filter" data-live-filter role="search">
        <label class="inventory-filter-field inventory-filter-search"><span>Search inventory</span><div><i class="bi bi-search" aria-hidden="true"></i><input type="search" name="search" value="{{ $search }}" placeholder="Product, variant, part number, or brand" autocomplete="off"></div></label>
        <label class="inventory-filter-field"><span>Category</span><select name="category">
            <option value="">All Categories</option>
            @foreach($categories as $cat)
            <option value="{{ $cat }}" {{ $category === $cat ? 'selected' : '' }}>{{ $cat }}</option>
            @endforeach
        </select></label>
        <label class="inventory-filter-field"><span>Stock status</span><select name="stock_level">
            <option value="">All Stock Levels</option>
            <option value="attention" {{ $stockLevel === 'attention' ? 'selected' : '' }}>Needs Attention</option>
            <option value="normal" {{ $stockLevel === 'normal' ? 'selected' : '' }}>Normal</option>
            <option value="low" {{ $stockLevel === 'low' ? 'selected' : '' }}>Low Stock</option>
            <option value="critical" {{ $stockLevel === 'critical' ? 'selected' : '' }}>Critical</option>
            <option value="out" {{ $stockLevel === 'out' ? 'selected' : '' }}>Out of Stock</option>
        </select></label>
        <span class="live-filter-status" data-live-filter-status role="status" aria-live="polite"></span>
    </form>

    <div data-live-results>
    @if($errors->has('export'))
    <div class="alert alert-danger" style="margin-bottom:16px;">{{ $errors->first('export') }}</div>
    @endif

    <div class="grid-4 report-metric-grid">
        <div class="card metric-card report-metric report-metric--variants">
            <div class="metric-topline"><span class="metric-label">Variants</span><span class="metric-icon icon-products"><x-icon name="products" /></span></div>
            <div class="metric-value">{{ number_format($totalVariants) }}</div>
            <div class="metric-note">Product variants in catalogue</div>
        </div>
        <div class="card metric-card report-metric report-metric--stock">
            <div class="metric-topline"><span class="metric-label">On Hand</span><span class="metric-icon icon-txn"><x-icon name="stock" /></span></div>
            <div class="metric-value">{{ number_format($totalStock) }}</div>
            <div class="metric-note">Total units across variants</div>
        </div>
        <div class="card metric-card report-metric report-metric--value">
            <div class="metric-topline"><span class="metric-label">Inventory Value</span><span class="metric-icon icon-sales"><x-icon name="money" /></span></div>
            <div class="metric-value report-value-emphasis">&#8369;{{ number_format($inventoryValue, 2) }}</div>
            <div class="metric-note">Quantity on hand times cost price</div>
        </div>
        <div class="card metric-card report-metric report-metric--attention">
            <div class="metric-topline"><span class="metric-label">Needs Attention</span><span class="metric-icon icon-low"><x-icon name="alerts" /></span></div>
            <div class="metric-value report-value-attention">{{ number_format($lowStock + $criticalStock + $outOfStock) }}</div>
            <div class="metric-note">{{ $lowStock }} low, {{ $criticalStock }} critical, {{ $outOfStock }} out</div>
        </div>
    </div>

    <div class="card stock-card">
        <div class="card-header">
            <div><span class="card-title"><i class="bi bi-box-seam" aria-hidden="true"></i> Inventory Detail</span><span class="report-card-caption">Variant stock levels, pricing, and reorder thresholds</span></div>
            <span class="tag tag--accent">{{ $variants->total() }} variants</span>
        </div>
        <div class="table-wrap">
            <table class="stock-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Variant</th>
                        <th>Brand</th>
                        <th>Supplier</th>
                        <th class="text-right">Cost</th>
                        <th class="text-right">Total Cost</th>
                        <th class="text-right">Selling</th>
                        <th class="text-right">On Hand</th>
                        <th class="text-right">Minimum</th>
                        <th class="text-right">Critical</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($variants as $variant)
                @php
                    $stock = $variant->inventoryStock;
                    $quantity = (int) ($stock?->quantity_on_hand ?? 0);
                    $minimum = (int) ($stock?->minimum_stock ?? 0);
                    $critical = (int) ($stock?->critical_stock ?? 0);
                    $status = $quantity <= 0 ? 'Out of Stock' : ($quantity <= $critical ? 'Critical' : ($quantity <= $minimum ? 'Low Stock' : 'Normal'));
                    $statusClass = $status === 'Normal' ? 'status-active' : ($status === 'Low Stock' ? 'status-low' : 'status-danger');
                @endphp
                <tr>
                    <td>
                        <div class="stock-product-name">{{ $variant->product?->name ?? 'Deleted product' }}</div>
                        <div class="stock-product-sub">{{ $variant->product?->category ?? 'Uncategorized' }}</div>
                    </td>
                    <td>
                        <div class="stock-code">{{ $variant->variant_code }}</div>
                        <div class="stock-product-sub">{{ collect([$variant->part_number, $variant->size, $variant->color])->filter()->join(' / ') ?: 'No extra details' }}</div>
                    </td>
                    <td>{{ $variant->brand?->name ?? '-' }}</td>
                    <td class="stock-muted">{{ $variant->supplier?->name ?? 'None' }}</td>
                    <td class="text-right stock-muted">&#8369;{{ number_format((float) $variant->cost_price, 2) }}</td>
                    <td class="text-right stock-muted">&#8369;{{ number_format((float) ($variant->cost_price * $quantity), 2) }}</td>
                    <td class="text-right fw-800" style="color:var(--accent2);">&#8369;{{ number_format((float) $variant->selling_price, 2) }}</td>
                    <td class="text-right stock-onhand {{ $status !== 'Normal' ? 'stock-onhand--warn' : '' }}">{{ $quantity }}</td>
                    <td class="text-right stock-muted">{{ $minimum }}</td>
                    <td class="text-right stock-muted">{{ $critical }}</td>
                    <td><span class="status {{ $statusClass }}">{{ $status }}</span></td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" class="empty-state">No inventory records match the selected filters.</td>
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
</div>
@endsection

@push('styles')
@vite('resources/css/views/reports-index-1.css')
@vite('resources/css/views/inventory-index-1.css')
@endpush
