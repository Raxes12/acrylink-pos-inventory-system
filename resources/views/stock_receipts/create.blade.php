@extends('layouts.app')
@section('title', 'Receive Stock')
@section('page-label', 'Inventory / Receive Stock')

@php
    $oldItems = old('items', [
        ['variant_id' => '', 'quantity_received' => 1, 'unit_cost' => ''],
    ]);
    $selectedSupplierId = old('supplier_id', '');
@endphp

@section('content')
<div class="editor-page">
    <a href="{{ route('stock-receipts.index') }}" class="editor-breadcrumb">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5"/><path d="m12 19-7-7 7-7"/></svg>
        Stock Receipts
    </a>

    <div class="page-toolbar">
        <div>
            <h1 class="page-heading">Receive Stock</h1>
            <p class="page-sub">Record incoming stock for one or more product variants.</p>
        </div>
    </div>

    <form method="POST" action="{{ route('stock-receipts.store') }}" class="card editor-card" id="stockReceiptForm">
        @csrf
        <div class="editor-card-head">
            <div>
                <div class="editor-card-title">Receipt information</div>
                <div class="editor-card-note">Stocks are increased per selected variant and logged as stock-in movements.</div>
            </div>
            <span class="editor-required">* Required</span>
        </div>

        <div class="editor-body">
            <section class="editor-section">
                <div class="editor-section-head">
                    <div class="editor-section-title">Header</div>
                    <div class="editor-section-copy">Supplier and received date for this batch.</div>
                </div>
                <div class="grid-2">
                    <div class="form-group">
                        <label>Supplier</label>
                        <div class="supplier-search {{ $selectedSupplier ? 'has-selection' : '' }}" data-supplier-search data-search-url="{{ route('stock-receipts.search.suppliers') }}">
                            <input type="hidden" name="supplier_id" value="{{ $selectedSupplierId }}" data-supplier-select>
                            <div class="supplier-search-input-wrap">
                                <svg class="supplier-search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <circle cx="11" cy="11" r="7"></circle>
                                    <path d="m20 20-3.5-3.5"></path>
                                </svg>
                                <input
                                    type="text"
                                    class="supplier-search-input"
                                    data-supplier-search-input
                                    placeholder="Search supplier name, contact, phone..."
                                    autocomplete="off"
                                    value="{{ $selectedSupplier?->name ?? '' }}"
                                >
                                <button
                                    type="button"
                                    class="supplier-search-clear"
                                    data-supplier-search-clear
                                    aria-label="Clear selected supplier"
                                    {{ $selectedSupplier ? '' : 'hidden' }}
                                >
                                    &times;
                                </button>
                            </div>

                            <div class="supplier-search-results" data-supplier-search-results hidden>
                                <button
                                    type="button"
                                    class="supplier-search-option"
                                    data-supplier-option
                                    data-id=""
                                    data-label=""
                                    data-search="no supplier none"
                                >
                                    <span class="supplier-option-main">
                                        <strong>&mdash; No supplier &mdash;</strong>
                                    </span>
                                    <span class="supplier-option-meta">Leave this receipt without a supplier</span>
                                </button>

                                <div class="supplier-search-empty" data-search-status>
                                    Type to search or choose from recent suppliers.
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Date Received *</label>
                        <input type="date" name="date_received" value="{{ old('date_received', now()->toDateString()) }}" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Remarks</label>
                    <textarea name="remarks" rows="2" placeholder="Optional receipt notes">{{ old('remarks') }}</textarea>
                </div>
            </section>

            <section class="editor-section">
                <div class="editor-section-head">
                    <div>
                        <div class="editor-section-title">Received Items</div>
                        <div class="editor-section-copy">Add each variant, received quantity, and latest unit cost.</div>
                    </div>
                    <button type="button" class="btn btn-secondary btn-sm" id="addReceiptItem">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
                        Add Item
                    </button>
                </div>

                <div class="receipt-items" id="receiptItems">
                    @foreach($oldItems as $index => $item)
                    @include('stock_receipts.partials.item-row', ['index' => $index, 'item' => $item, 'selectedVariant' => $selectedVariants->get((int) ($item['variant_id'] ?? 0))])
                    @endforeach
                </div>
            </section>
        </div>

        <div class="editor-actions">
            <a href="{{ route('stock-receipts.index') }}" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">Save Receipt</button>
        </div>
    </form>

    <template id="receiptItemTemplate">
        @include('stock_receipts.partials.item-row', ['index' => '__INDEX__', 'item' => ['variant_id' => '', 'quantity_received' => 1, 'unit_cost' => ''], 'selectedVariant' => null])
    </template>
</div>
@endsection

@push('styles')
@vite('resources/css/views/inventory-index-1.css')
@endpush

@push('scripts')
@vite('resources/js/views/stock-receipts-create-1.js')
@endpush
