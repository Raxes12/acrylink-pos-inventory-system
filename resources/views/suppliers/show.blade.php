@extends('layouts.app')
@section('title', $supplier->name)
@section('page-label', 'Suppliers / '.$supplier->name)

@section('content')
<div class="supplier-show-page supplier-show-image2">
    <a href="{{ route('suppliers.index') }}" class="editor-breadcrumb">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
             stroke-linecap="round" stroke-linejoin="round">
            <path d="M19 12H5"/><path d="m12 19-7-7 7-7"/>
        </svg>
        Back to Suppliers
    </a>

    <div class="supplier-show-layout">
        <section class="card supplier-profile-card">
            <div class="supplier-profile-top">
                <h1>{{ $supplier->name }}</h1>
                <div class="supplier-code">{{ $supplier->code ?? 'SUP-'.str_pad($supplier->id, 3, '0', STR_PAD_LEFT) }}</div>
                <span class="status status-{{ $supplier->status }}">{{ ucfirst($supplier->status) }}</span>
            </div>

            <h2 class="supplier-details-title">Supplier details</h2>
            <dl class="supplier-profile-details">
                <div><dt>Contact</dt><dd>{{ $supplier->contact ?: 'Not provided' }}</dd></div>
                <div><dt>Email</dt><dd>@if($supplier->email)<a href="mailto:{{ $supplier->email }}">{{ $supplier->email }}</a>@else Not provided @endif</dd></div>
                <div><dt>Phone</dt><dd>@if($supplier->phone)<a href="tel:{{ $supplier->phone }}">{{ $supplier->phone }}</a>@else Not provided @endif</dd></div>
                <div><dt>Mobile</dt><dd>{{ $supplier->mobile ?: 'Not provided' }}</dd></div>
                <div><dt>City</dt><dd>{{ $supplier->city ?: 'Not provided' }}</dd></div>
                <div><dt>Province</dt><dd>{{ $supplier->province ?: 'Not provided' }}</dd></div>
                <div><dt>Credit Limit</dt><dd>₱{{ number_format((float)($supplier->credit_limit ?? 0), 2) }}</dd></div>
                <div><dt>Payment Terms</dt><dd>{{ $supplier->payment_terms !== null && $supplier->payment_terms !== '' ? $supplier->payment_terms.' days' : 'Not provided' }}</dd></div>
            </dl>

            @if(auth()->user()->canEdit())
            <div class="supplier-profile-actions">
                <a href="{{ route('suppliers.edit', $supplier) }}" class="btn btn-primary">
                    <i class="bi bi-pencil" aria-hidden="true"></i> Edit
                </a>
            </div>
            @endif
        </section>

        <section class="card supplier-products-card">
            <div class="supplier-products-header">
                <h2>Supplied products</h2>
            </div>

            @if($variants->isEmpty())
            <div class="supplier-products-empty">
                <x-icon name="inbox" />
                <strong>No linked products</strong>
                <span>Products assigned to this supplier will appear here.</span>
            </div>
            @else
            <div class="table-wrap supplier-products-table">
                <table>
                    <thead>
                        <tr>
                            <th>PRODUCT</th>
                            <th>CODE</th>
                            <th>BRAND</th>
                            <th>STOCK</th>
                            <th>SELLING PRICE</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($variants as $variant)
                        @php $quantity = (int)($variant->inventoryStock?->quantity_on_hand ?? 0); @endphp
                        <tr>
                            <td><a class="supplier-product-name" href="{{ route('inventory.show', $variant) }}">{{ $variant->product?->name ?? 'Deleted product' }}</a></td>
                            <td><a class="supplier-code-link" href="{{ route('inventory.show', $variant) }}">{{ $variant->variant_code }}</a></td>
                            <td>{{ $variant->brand?->name ?? '-' }}</td>
                            <td><span class="supplier-stock {{ $quantity <= 0 ? 'is-empty' : '' }}">{{ number_format($quantity) }}</span></td>
                            <td class="supplier-price">&#8369;{{ number_format((float)$variant->selling_price, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($variants->hasPages())
            <div class="supplier-show-pagination">
                <span>Showing {{ $variants->firstItem() }}-{{ $variants->lastItem() }} of {{ $variants->total() }}</span>
                {{ $variants->links() }}
            </div>
            @endif
            @endif
        </section>
    </div>
</div>
@endsection

@push('styles')
@vite('resources/css/views/suppliers-show-1.css')
@endpush
