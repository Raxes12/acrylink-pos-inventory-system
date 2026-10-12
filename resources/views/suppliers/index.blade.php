@extends('layouts.app')
@section('title', 'Suppliers')
@section('page-label', 'Suppliers')

@section('content')
<div class="suppliers-page">
<x-page-header title="Suppliers" subtitle="Manage vendor contacts, payment preferences, and product coverage.">
    <x-slot:actions><a href="{{ route('suppliers.create') }}" class="btn btn-primary" data-open-supplier-create><x-icon name="plus" /> Add Supplier</a></x-slot:actions>
</x-page-header>

<form method="GET" action="{{ route('suppliers.index') }}" class="filter-bar suppliers-filter" data-live-filter>
    <div class="supplier-search-wrap"><i class="bi bi-search" aria-hidden="true"></i><input type="search" name="search" value="{{ request('search') }}" autocomplete="off" maxlength="150" placeholder="Search company, contact, phone, or email..." aria-label="Search suppliers"></div>
    <select name="status" aria-label="Supplier status"><option value="">All statuses</option><option value="active" @selected(request('status') === 'active')>Active</option><option value="inactive" @selected(request('status') === 'inactive')>Inactive</option></select>
    <select name="payment_method" aria-label="Preferred payment method"><option value="">All payment methods</option><option value="cash" @selected(request('payment_method') === 'cash')>Cash</option><option value="check" @selected(request('payment_method') === 'check')>Check</option><option value="credit_account" @selected(request('payment_method') === 'credit_account')>Credit Sale</option><option value="gcash" @selected(request('payment_method') === 'gcash')>GCash</option></select>
    <span class="live-filter-status" data-live-filter-status role="status" aria-live="polite"></span>
</form>

<div class="card supplier-table-card" data-live-results>
    <div class="card-header supplier-card-header"><div><span class="card-title">Supplier Directory</span><span class="supplier-card-caption">Vendor contacts and purchasing preferences.</span></div><span class="tag tag--accent">{{ $suppliers->total() }} supplier{{ $suppliers->total() === 1 ? '' : 's' }}</span></div>
    <div class="table-wrap" tabindex="0" role="region" aria-label="Suppliers table">
        <table class="supplier-table">
            <thead><tr><th>Supplier</th><th>Primary Contact</th><th>Payment</th><th>Products</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
            @forelse($suppliers as $supplier)
                @php
                    $paymentMethod = $supplier->payment_method ?? 'cash';
                @endphp
                <tr>
                    <td data-label="Supplier"><div class="supplier-identity"><span class="supplier-avatar" aria-hidden="true">{{ strtoupper(substr($supplier->name, 0, 1)) }}</span><div class="td-stack"><strong>{{ $supplier->name }}</strong><span><i class="bi bi-geo-alt" aria-hidden="true"></i>{{ $supplier->address ?: 'No address provided' }}</span></div></div></td>
                    <td data-label="Primary contact"><div class="td-stack supplier-contact"><strong>{{ $supplier->contact ?: 'No contact person' }}</strong><span><i class="bi bi-telephone" aria-hidden="true"></i>{{ $supplier->phone ?: 'No phone' }}</span><span><i class="bi bi-envelope" aria-hidden="true"></i>{{ $supplier->email ?: 'No email' }}</span></div></td>
                    <td data-label="Payment"><span class="supplier-payment"><i class="bi bi-wallet2" aria-hidden="true"></i>{{ \App\Models\PaymentMethod::labelFor($paymentMethod) }}</span></td>
                    <td data-label="Products"><strong class="supplier-product-count">{{ $supplier->products_count }}</strong><span class="supplier-product-label"> linked</span></td>
                    <td data-label="Status"><span class="status status-{{ $supplier->status }}">{{ ucfirst($supplier->status) }}</span></td>
                    <td data-label="Actions"><div class="table-actions supplier-actions">
                        <a href="{{ route('suppliers.show', $supplier) }}" class="btn btn-secondary btn-sm" aria-label="View {{ $supplier->name }}" title="View supplier"><i class="bi bi-eye" aria-hidden="true"></i></a>
                        <a href="{{ route('suppliers.edit', $supplier) }}" class="btn btn-secondary btn-sm" aria-label="Edit {{ $supplier->name }}" title="Edit supplier"><i class="bi bi-pencil" aria-hidden="true"></i></a>
                        @if($supplier->products_count > 0)
                        <button type="button" class="btn btn-danger btn-sm" disabled title="Supplier has linked products and cannot be deleted" aria-label="Cannot delete {{ $supplier->name }}"><i class="bi bi-trash3" aria-hidden="true"></i></button>
                        @else
                        <form method="POST" action="{{ route('suppliers.destroy', $supplier) }}" data-confirm data-confirm-title="Delete supplier?" data-confirm-message="Delete {{ $supplier->name }}? This cannot be undone." data-confirm-button="Delete supplier">@csrf @method('DELETE')<button type="button" class="btn btn-danger btn-sm" title="Delete supplier" aria-label="Delete {{ $supplier->name }}" data-confirm-trigger><i class="bi bi-trash3" aria-hidden="true"></i></button></form>
                        @endif
                    </div></td>
                </tr>
            @empty
                <x-empty-state colspan="6" title="No suppliers found" message="Adjust the search or filters, or add your first supplier." />
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="supplier-table-footer">
        <span>Showing {{ $suppliers->firstItem() ?? 0 }}-{{ $suppliers->lastItem() ?? 0 }} of {{ $suppliers->total() }} suppliers</span>
        @if($suppliers->hasPages())
        @php
            $currentPage = $suppliers->currentPage();
            $lastPage = $suppliers->lastPage();
            $supplierPages = collect([1, $currentPage - 1, $currentPage, $currentPage + 1, $lastPage])->filter(fn ($page) => $page >= 1 && $page <= $lastPage)->unique()->sort()->values();
            $previousSupplierPage = null;
        @endphp
        <nav class="pagination" aria-label="Supplier pagination">
            @if($suppliers->onFirstPage())<span class="disabled" aria-disabled="true">Previous</span>@else<a href="{{ $suppliers->previousPageUrl() }}" rel="prev">Previous</a>@endif
            @foreach($supplierPages as $page)
                @if($previousSupplierPage && $page > $previousSupplierPage + 1)<span class="disabled" aria-hidden="true">...</span>@endif
                @if($page === $currentPage)<span class="active" aria-current="page">{{ $page }}</span>@else<a href="{{ $suppliers->url($page) }}">{{ $page }}</a>@endif
                @php
                    $previousSupplierPage = $page;
                @endphp
            @endforeach
            @if($suppliers->hasMorePages())<a href="{{ $suppliers->nextPageUrl() }}" rel="next">Next</a>@else<span class="disabled" aria-disabled="true">Next</span>@endif
        </nav>
        @endif
    </div>
</div>

<div id="createSupplierModal" class="modal-backdrop supplier-modal-backdrop" style="display:none" role="presentation" aria-hidden="true" data-open-on-error="{{ old('_supplier_create') === '1' && $errors->any() ? 'true' : 'false' }}">
    <div class="modal supplier-create-modal" role="dialog" aria-modal="true" aria-labelledby="createSupplierTitle" aria-describedby="createSupplierDescription">
        <div class="modal-header supplier-modal-header"><div><span class="modal-title" id="createSupplierTitle">Add Supplier</span><span id="createSupplierDescription">Create a vendor profile for purchasing and inventory records.</span></div><button type="button" class="modal-close-btn" data-close-supplier-create aria-label="Close supplier form">&times;</button></div>
        <form method="POST" action="{{ route('suppliers.store') }}">@csrf<input type="hidden" name="_supplier_create" value="1"><div class="modal-body supplier-modal-body">@include('suppliers._form', ['s' => null])</div><div class="modal-footer supplier-modal-footer"><button type="button" class="btn btn-secondary" data-close-supplier-create>Cancel</button><button type="submit" class="btn btn-primary"><i class="bi bi-building-add" aria-hidden="true"></i> Create Supplier</button></div></form>
    </div>
</div>
</div>
@endsection

@push('styles')
@vite('resources/css/views/suppliers-index-1.css')
@endpush

@push('scripts')
@vite('resources/js/views/suppliers-index-1.js')
@endpush
