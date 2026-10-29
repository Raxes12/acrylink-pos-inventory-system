@extends('layouts.app')
@section('title', 'Stock Receipt '.$receipt->receipt_no)
@section('page-label', 'Inventory / Receipt Details')

@php
    $totalCost = $receipt->items->sum(fn ($item) => (float) $item->total_cost);
@endphp

@section('content')
<x-inventory-nav />
<div class="page-toolbar stock-toolbar">
    <div>
        <a href="{{ route('stock-receipts.index') }}" class="editor-breadcrumb" style="margin-bottom:10px;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5"/><path d="m12 19-7-7 7-7"/></svg>
            Stock Receipts
        </a>
        <h1 class="page-heading">{{ $receipt->receipt_no }}</h1>
        <p class="page-sub">Received {{ $receipt->date_received?->format('M j, Y') }} by {{ $receipt->receivedBy?->name ?? 'System Administrator' }}.</p>
    </div>
    <div class="toolbar-actions">
        <a href="{{ route('stock-receipts.create') }}" class="btn btn-primary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
            New Receipt
        </a>
        <a href="{{ route('inventory.movements', ['type' => 'stock_in']) }}" class="btn btn-secondary">View Movements</a>
    </div>
</div>

<section class="card receipt-summary-card">
    <div class="receipt-summary-grid">
        <div>
            <div class="stock-product-sub">Supplier</div>
            <div class="stock-product-name">{{ $receipt->supplier?->name ?? 'No supplier' }}</div>
        </div>
        <div>
            <div class="stock-product-sub">Total Cost</div>
            <div class="stock-onhand">&#8369;{{ number_format($totalCost, 2) }}</div>
        </div>
    </div>
    @if($receipt->remarks)
    <div class="receipt-summary-remarks">
        <div class="stock-product-sub">Remarks</div>
        <div>{{ $receipt->remarks }}</div>
    </div>
    @endif
</section>

<div class="card stock-card">
    <div class="table-wrap">
        <table class="stock-table">
            <thead>
                <tr>
                    <th>Product / Variant</th>
                    <th>Part No.</th>
                    <th>Brand</th>
                    <th>Size / Color</th>
                    <th>Unit</th>
                    <th class="text-right">Qty Received</th>
                    <th class="text-right">Unit Cost</th>
                    <th class="text-right">Total</th>
                    <th class="text-right">Current Stock</th>
                </tr>
            </thead>
            <tbody>
            @forelse($receipt->items as $item)
            @php
                $variant = $item->productVariant;
                $product = $variant?->product;
                $currentStock = (int) ($variant?->inventoryStock?->quantity_on_hand ?? 0);
            @endphp
            <tr>
                <td data-label="Product / Variant">
                    @if($variant)
                    <a href="{{ route('inventory.show', $variant) }}" class="stock-product-name">{{ $product?->name ?? 'Deleted product' }}</a>
                    @else
                    <div class="stock-product-name">Deleted product</div>
                    @endif
                    <div class="stock-product-sub">{{ $variant?->variant_code ?? 'Deleted variant' }}</div>
                </td>
                <td data-label="Part no." class="stock-muted">{{ $variant?->part_number ?: '-' }}</td>
                <td data-label="Brand">{{ $variant?->brand?->name ?? '-' }}</td>
                <td data-label="Size / Color" class="stock-muted">{{ collect([$variant?->size, $variant?->color])->filter()->join(' / ') ?: '-' }}</td>
                <td data-label="Unit">{{ $variant?->unit?->code ?? '-' }}</td>
                <td data-label="Quantity received" class="text-right stock-onhand">{{ $item->quantity_received }}</td>
                <td data-label="Unit cost" class="text-right">&#8369;{{ number_format((float) $item->unit_cost, 2) }}</td>
                <td data-label="Total" class="text-right fw-800">&#8369;{{ number_format((float) $item->total_cost, 2) }}</td>
                <td data-label="Current stock" class="text-right stock-muted">{{ $currentStock }}</td>
            </tr>
            @empty
            <x-empty-state colspan="9" title="No receipt items" message="This stock receipt does not contain any product variants." icon="inbox" />
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('styles')
@vite('resources/css/views/inventory-index-1.css')
@endpush
