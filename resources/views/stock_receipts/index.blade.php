@extends('layouts.app')
@section('title', 'Stock Receipts')
@section('page-label', 'Inventory / Receipts')

@section('content')
<x-inventory-nav />
<div class="page-toolbar stock-toolbar">
    <div>
        <h1 class="page-heading">Stock Receipts</h1>
        <p class="page-sub">Receive purchased stock into variant-level inventory.</p>
    </div>
    <div class="toolbar-actions">
        <a href="{{ route('stock-receipts.create') }}" class="btn btn-primary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
            New Receipt
        </a>
        <a href="{{ route('inventory.stock-overview') }}" class="btn btn-secondary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5"/><path d="m12 19-7-7 7-7"/></svg>
            Stock Overview
        </a>
    </div>
</div>

<div class="card stock-card">
    <div class="table-wrap">
        <table class="stock-table">
            <thead>
                <tr>
                    <th>Receipt No.</th>
                    <th>Date Received</th>
                    <th>Supplier</th>
                    <th>Items</th>
                    <th class="text-right">Total Cost</th>
                    <th>Received By</th>
                    <th>Created</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse($receipts as $receipt)
            <tr>
                <td data-label="Receipt no."><a href="{{ route('stock-receipts.show', $receipt) }}" class="stock-code">{{ $receipt->receipt_no }}</a></td>
                <td data-label="Date received">{{ $receipt->date_received?->format('M j, Y') }}</td>
                <td data-label="Supplier" class="stock-muted">{{ $receipt->supplier?->name ?? 'No supplier' }}</td>
                <td data-label="Items">{{ $receipt->items_count }}</td>
                <td data-label="Total cost" class="text-right fw-800" style="color:var(--accent2);">&#8369;{{ number_format((float) ($receipt->items_sum_total_cost ?? 0), 2) }}</td>
                <td data-label="Received by">{{ $receipt->receivedBy?->name ?? 'System Administrator' }}</td>
                <td data-label="Created" class="stock-muted">{{ $receipt->created_at?->format('M j, Y H:i') }}</td>
                <td data-label="Actions">
                    <div class="table-actions" style="justify-content:center;">
                        <a href="{{ route('stock-receipts.show', $receipt) }}" class="btn btn-secondary btn-sm" title="View receipt" aria-label="View receipt {{ $receipt->receipt_no }}">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z"/><circle cx="12" cy="12" r="3"/></svg>
                        </a>
                    </div>
                </td>
            </tr>
            @empty
            <x-empty-state colspan="8" title="No stock receipts yet" message="Record a receipt when purchased inventory arrives." icon="receipts" />
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="stock-pagination">{{ $receipts->links() }}</div>
</div>
@endsection

@push('styles')
@vite('resources/css/views/inventory-index-1.css')
@endpush
