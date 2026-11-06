@extends('layouts.app')
@section('title', 'Stock Movements')
@section('page-label', 'Inventory Management')

@php
    $selectedType = $filters['type'] ?? '';
    $dateFrom = $filters['date_from'] ?? '';
    $dateTo = $filters['date_to'] ?? '';
    $hasFilters = filled($selectedType) || filled($dateFrom) || filled($dateTo);

    if ($dateFrom && $dateTo) {
        $periodLabel = $dateFrom === $dateTo
            ? \Carbon\Carbon::parse($dateFrom)->format('M j, Y')
            : \Carbon\Carbon::parse($dateFrom)->format('M j').' – '.\Carbon\Carbon::parse($dateTo)->format('M j, Y');
    } elseif ($dateFrom) {
        $periodLabel = 'From '.\Carbon\Carbon::parse($dateFrom)->format('M j, Y');
    } elseif ($dateTo) {
        $periodLabel = 'Through '.\Carbon\Carbon::parse($dateTo)->format('M j, Y');
    } else {
        $periodLabel = 'All recorded dates';
    }
@endphp

@section('content')
<div class="movements-page">
    <x-inventory-nav />

    <x-page-header title="Stock Movements" subtitle="Review every stock increase, sale, return, and adjustment in one audit-friendly history." class="movement-page-header">
        <x-slot:actions>
            <a href="{{ route('inventory.stock-overview') }}" class="btn btn-secondary">
                <svg class="app-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M3 3v18h18"/><path d="m7 16 4-4 3 3 5-7"/></svg>
                Stock overview
            </a>
        </x-slot:actions>
    </x-page-header>

    <section class="movement-filter-panel" aria-labelledby="movement-filter-title">
        <div class="movement-filter-heading">
            <div>
                <span class="movement-filter-kicker">History filters</span>
                <h2 id="movement-filter-title">Find a stock activity</h2>
                <p>Results refresh automatically when a filter changes.</p>
            </div>
            <div class="movement-filter-context">
                <span>{{ $movementTypes[$selectedType] ?? 'All movements' }}</span>
                <strong>{{ $periodLabel }}</strong>
            </div>
        </div>

        <form method="GET" action="{{ route('inventory.movements') }}" class="movement-filter" data-live-filter>
            <label class="movement-filter-field movement-filter-field--type">
                <span>Movement type</span>
                <div class="movement-control">
                    <svg class="app-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M8 7h11l-3-3"/><path d="m19 7-3 3"/><path d="M16 17H5l3 3"/><path d="m5 17 3-3"/></svg>
                    <select name="type" aria-label="Filter by movement type">
                        @foreach($movementTypes as $value => $label)
                            <option value="{{ $value }}" @selected($selectedType === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </label>
            <label class="movement-filter-field">
                <span>From date</span>
                <div class="movement-control">
                    <svg class="app-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 11h18"/></svg>
                    <input type="date" name="date_from" value="{{ $dateFrom }}" aria-label="Show movements from date">
                </div>
            </label>
            <label class="movement-filter-field">
                <span>To date</span>
                <div class="movement-control">
                    <svg class="app-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 11h18"/></svg>
                    <input type="date" name="date_to" value="{{ $dateTo }}" min="{{ $dateFrom }}" aria-label="Show movements through date">
                </div>
            </label>
            @if($hasFilters)
                <a href="{{ route('inventory.movements') }}" class="movement-reset" data-live-filter-clear>
                    <svg class="app-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M3 12a9 9 0 1 0 3-6.7L3 8"/><path d="M3 3v5h5"/></svg>
                    Reset filters
                </a>
            @endif
            <span class="movement-filter-status" data-live-filter-status role="status" aria-live="polite"></span>
            <noscript><button type="submit" class="btn btn-primary">Apply filters</button></noscript>
        </form>
        @error('date_to')<p class="movement-filter-error" role="alert">The end date must be the same as or later than the start date.</p>@enderror
    </section>

    <div data-live-results class="movement-live-results">
        <section class="movement-summary-grid" aria-label="Filtered movement summary">
            <article class="movement-summary-card movement-summary-card--total">
                <span class="movement-summary-icon" aria-hidden="true"><svg class="app-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3v18h18"/><path d="M7 16h2v2H7zM11 11h2v7h-2zM15 6h2v12h-2z"/></svg></span>
                <div><span>Total activities</span><strong>{{ number_format($movementSummary['movements']) }}</strong><small>in this view</small></div>
            </article>
            <article class="movement-summary-card movement-summary-card--added">
                <span class="movement-summary-icon" aria-hidden="true"><svg class="app-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 19V5M5 12l7-7 7 7"/></svg></span>
                <div><span>Units added</span><strong>+{{ number_format($movementSummary['units_added']) }}</strong><small>received or returned</small></div>
            </article>
            <article class="movement-summary-card movement-summary-card--removed">
                <span class="movement-summary-icon" aria-hidden="true"><svg class="app-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M19 12l-7 7-7-7"/></svg></span>
                <div><span>Units removed</span><strong>−{{ number_format($movementSummary['units_removed']) }}</strong><small>sold or deducted</small></div>
            </article>
            <article class="movement-summary-card movement-summary-card--adjusted">
                <span class="movement-summary-icon" aria-hidden="true"><svg class="app-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 21v-7M4 10V3M12 21v-9M12 8V3M20 21v-5M20 12V3M1 14h6M9 8h6M17 16h6"/></svg></span>
                <div><span>Manual adjustments</span><strong>{{ number_format($movementSummary['adjustments']) }}</strong><small>staff-entered changes</small></div>
            </article>
        </section>

        <section class="card movement-results-card" aria-labelledby="movement-history-title">
            <header class="movement-results-header">
                <div>
                    <span class="movement-results-kicker">Activity log</span>
                    <h2 id="movement-history-title">Movement history</h2>
                    <p>{{ $periodLabel }} · {{ $movementTypes[$selectedType] ?? 'All movements' }}</p>
                </div>
                <span class="movement-result-count">{{ number_format($movements->total()) }} {{ \Illuminate\Support\Str::plural('record', $movements->total()) }}</span>
            </header>
            <div class="table-wrap movement-table-wrap" tabindex="0" aria-label="Stock movement history table">
                <table class="stock-table movement-table">
                    <thead>
                        <tr>
                            <th>Date &amp; time</th>
                            <th>Product / variant</th>
                            <th>Movement</th>
                            <th>Change</th>
                            <th>Stock level</th>
                            <th>Recorded by</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($movements as $movement)
                        @php
                            $typeLabel = $movement->reference_type === 'manual_adjustment'
                                ? \App\Support\StockAdjustmentOptions::typeLabel($movement->movement_type)
                                : ($movementTypes[$movement->movement_type] ?? str($movement->movement_type)->replace('_', ' ')->title());
                            $reasonLabel = \App\Support\StockAdjustmentOptions::reasonLabel($movement->movement_type, $movement->adjustment_reason);
                            $variant = $movement->variant;
                            $product = $variant?->product;
                            $unitCode = $variant?->unit?->code;
                            $variantDetails = collect([$variant?->part_number, $variant?->brand?->name, $variant?->size, $variant?->color])->filter()->join(' / ');
                            $typeClass = in_array($movement->movement_type, ['stock_in', 'sale_return'], true)
                                ? 'is-increase'
                                : (in_array($movement->movement_type, ['stock_out', 'sale'], true) ? 'is-decrease' : 'is-adjustment');
                            $referenceLabel = $movement->reference_type && $movement->reference_type !== 'manual_adjustment'
                                ? str($movement->reference_type)->replace('_', ' ')->title()
                                : null;
                        @endphp
                        <tr class="movement-row {{ $typeClass }}">
                            <td data-label="Date & time">
                                <time class="movement-date" datetime="{{ $movement->created_at?->toIso8601String() }}">
                                    <strong>{{ $movement->created_at?->format('M j, Y') ?? 'Date unavailable' }}</strong>
                                    <span>{{ $movement->created_at?->format('h:i A') }}</span>
                                </time>
                            </td>
                            <td data-label="Product / variant">
                                <div class="movement-product">
                                    @if($variant)
                                        <a class="stock-product-name stock-product-link" href="{{ route('inventory.show', $variant) }}">{{ $product?->name ?? 'Deleted product' }}</a>
                                    @else
                                        <strong class="stock-product-name">Deleted product</strong>
                                    @endif
                                    <div class="movement-variant-line">
                                        <span class="movement-code">{{ $variant?->variant_code ?? 'Unavailable' }}</span>
                                        @if($variantDetails)<span>{{ $variantDetails }}</span>@endif
                                    </div>
                                </div>
                            </td>
                            <td data-label="Movement">
                                <span class="movement-type movement-type--{{ $typeClass }}"><span aria-hidden="true"></span>{{ $typeLabel }}</span>
                            </td>
                            <td data-label="Change">
                                <span class="movement-change {{ $movement->quantity_change < 0 ? 'movement-negative' : 'movement-positive' }}">
                                    {{ $movement->quantity_change > 0 ? '+' : '' }}{{ number_format($movement->quantity_change) }}
                                    @if($unitCode)<small>{{ $unitCode }}</small>@endif
                                </span>
                            </td>
                            <td data-label="Stock level">
                                <div class="movement-stock-flow" aria-label="Stock changed from {{ $movement->quantity_before }} to {{ $movement->quantity_after }}">
                                    <span>{{ number_format($movement->quantity_before) }}</span>
                                    <svg class="app-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                                    <strong>{{ number_format($movement->quantity_after) }}</strong>
                                </div>
                            </td>
                            <td data-label="Recorded by">
                                <div class="movement-user">
                                    <span aria-hidden="true">{{ str($movement->user?->name ?? 'System')->substr(0, 1)->upper() }}</span>
                                    <strong>{{ $movement->user?->name ?? 'System' }}</strong>
                                </div>
                            </td>
                            <td data-label="Details">
                                <div class="movement-context">
                                    @if($reasonLabel)<span class="movement-reason">{{ $reasonLabel }}</span>@endif
                                    @if($referenceLabel)<span class="movement-reference">{{ $referenceLabel }}</span>@endif
                                    <p class="movement-remarks">{{ $movement->remarks ?: 'No additional notes' }}</p>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <x-empty-state colspan="7" title="No stock movements match these filters" message="Try another movement type or widen the selected date range." icon="transactions" />
                    @endforelse
                    </tbody>
                </table>
            </div>
            <footer class="view-blades-footer movement-results-footer">
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
        <nav class="rules-pagination" aria-label="Stock movement pagination">
            @if($movements->onFirstPage())
                <span class="rules-page is-disabled" aria-disabled="true">Previous</span>
            @else
                <a class="rules-page" href="{{ $movements->previousPageUrl() }}" rel="prev">Previous</a>
            @endif

            @foreach($pages as $page)
                @if($previousPage && $page > $previousPage + 1)
                    <span class="rules-page rules-page--gap" aria-hidden="true">&hellip;</span>
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
            </footer>
        </section>
    </div>
</div>
@endsection

@push('styles')
@vite('resources/css/views/inventory-index-1.css')
@endpush
