@extends('layouts.app')
@section('title', 'Stock Movements')
@section('page-label', 'Inventory Management')

@php
    $types = [
        '' => 'All Types',
        'stock_in' => 'Stock In',
        'stock_out' => 'Stock Out',
        'sale' => 'Sale',
        'adjustment' => 'Adjustment',
        'sale_return' => 'Return In',
    ];
@endphp

@section('content')
<x-inventory-nav />
<div class="page-toolbar stock-toolbar">
    <div>
        <h1 class="page-heading">Stock Movements</h1>
        <p class="page-sub">Complete history of all inventory changes.</p>
    </div>
    <a href="{{ route('inventory.stock-overview') }}" class="btn btn-secondary">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5"/><path d="m12 19-7-7 7-7"/></svg>
        Overview
    </a>
</div>

<form method="GET" action="{{ route('inventory.movements') }}" class="filter-bar stock-filter movement-filter" data-live-filter>
    <label>
        <span>Movement type</span>
        <select name="type">
            @foreach($types as $value => $label)
            <option value="{{ $value }}" {{ request('type')===$value ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
    </label>
    <label>
        <span>From date</span>
        <input type="date" name="date_from" value="{{ request('date_from') }}">
    </label>
    <label>
        <span>To date</span>
        <input type="date" name="date_to" value="{{ request('date_to') }}">
    </label>
    <button type="submit" class="btn btn-primary">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 3H2l8 9.46V19l4 2v-8.54L22 3z"/></svg>
        Filter
    </button>
    @if(request()->filled('type') || request()->filled('date_from') || request()->filled('date_to'))
    <a href="{{ route('inventory.movements') }}" class="btn btn-secondary">Clear</a>
    @endif
</form>

<div class="card stock-card" data-live-results>
    <div class="table-wrap">
        <table class="stock-table movement-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Product / Variant</th>
                    <th>Movement Type</th>
                    <th>Qty Change</th>
                    <th>Before</th>
                    <th>After</th>
                    <th>User</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody>
            @forelse($movements as $movement)
            @php
                $typeLabel = $types[$movement->movement_type] ?? str($movement->movement_type)->replace('_', ' ')->title();
                $qtyClass = $movement->quantity_change < 0 ? 'movement-negative' : 'movement-positive';
                $variant = $movement->variant;
                $product = $variant?->product;
                $variantDetails = collect([
                    $variant?->part_number,
                    $variant?->brand?->name,
                    $variant?->size,
                    $variant?->color,
                ])->filter()->join(' / ');
            @endphp
            <tr>
                <td class="stock-muted">{{ $movement->created_at?->format('M j, Y h:i A') }}</td>
                <td>
                    @if($variant)
                    <a class="stock-product-name stock-product-link" href="{{ route('inventory.show', $variant) }}">{{ $product?->name ?? 'Deleted product' }}</a>
                    @else
                    <div class="stock-product-name">Deleted product</div>
                    @endif
                    <div class="stock-product-sub">{{ $variant?->variant_code ?? '-' }}{{ $variantDetails ? ' - '.$variantDetails : '' }}</div>
                </td>
                <td><span class="movement-type">{{ $typeLabel }}</span></td>
                <td class="{{ $qtyClass }}">{{ $movement->quantity_change > 0 ? '+' : '' }}{{ $movement->quantity_change }}</td>
                <td class="stock-muted">{{ $movement->quantity_before }}</td>
                <td class="stock-onhand">{{ $movement->quantity_after }}</td>
                <td>{{ $movement->user?->name ?? 'System Administrator' }}</td>
                <td class="stock-muted movement-remarks">{{ $movement->remarks ?: '-' }}</td>
            </tr>
            @empty
            <x-empty-state colspan="8" title="No stock movements found" message="Try clearing the date or movement filters." icon="transactions" />
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="view-blades-footer">
        <div class="pagination-summary">
            Showing {{ $movements->firstItem() ?? 0 }}-{{ $movements->lastItem() ?? 0 }} of {{ $movements->total() }} movements
        </div>
        @if($movements->hasPages())
        @php
            $currentPage = $movements->currentPage();
            $lastPage = $movements->lastPage();
            $pages = collect([1, $currentPage - 1, $currentPage, $currentPage + 1, $lastPage])
                ->filter(fn ($page) => $page >= 1 && $page <= $lastPage)
                ->unique()
                ->sort()
                ->values();
            $previousPage = null;
        @endphp
        <nav class="rules-pagination" aria-label="Stock variant pagination">
            @if($movements->onFirstPage())
                <span class="rules-page is-disabled" aria-disabled="true">Previous</span>
            @else
                <a class="rules-page" href="{{ $movements->previousPageUrl() }}" rel="prev">Previous</a>
            @endif

            @foreach($pages as $page)
                @if($previousPage && $page > $previousPage + 1)
                    <span class="rules-page rules-page--gap" aria-hidden="true">...</span>
                @endif

                @if($page === $currentPage)
                    <span class="rules-page is-active" aria-current="page">{{ $page }}</span>
                @else
                    <a class="rules-page" href="{{ $movements->url($page) }}">{{ $page }}</a>
                @endif

                @php $previousPage = $page; @endphp
            @endforeach

            @if($movements->hasMorePages())
                <a class="rules-page" href="{{ $movements->nextPageUrl() }}" rel="next">Next</a>
            @else
                <span class="rules-page is-disabled" aria-disabled="true">Next</span>
            @endif
        </nav>
        @endif
    </div>
</div>
@endsection

@push('styles')
@vite('resources/css/views/inventory-index-1.css')
@endpush
