@extends('layouts.app')
@php($isEdit = isset($supplier))
@section('title', $isEdit ? 'Edit Supplier' : 'Add Supplier')
@section('page-label', 'Suppliers / ' . ($isEdit ? 'Edit Supplier' : 'Add Supplier'))

@section('content')
<div class="editor-page">
    <a href="{{ route('suppliers.index') }}" class="editor-breadcrumb">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5"/><path d="m12 19-7-7 7-7"/></svg>
        Suppliers
    </a>
    <div class="page-toolbar">
        <div>
            <h1 class="page-heading">{{ $isEdit ? 'Edit Supplier' : 'Add Supplier' }}</h1>
            <p class="page-sub">{{ $isEdit ? 'Update vendor contact and payment details.' : 'Create a new supplier record.' }}</p>
        </div>
    </div>

    <form method="POST" action="{{ $isEdit ? route('suppliers.update', $supplier) : route('suppliers.store') }}" class="card editor-card">
        @csrf
        @if($isEdit) @method('PUT') @endif
        <div class="editor-card-head">
            <div>
                <div class="editor-card-title">Supplier information</div>
                <div class="editor-card-note">Store the primary contact and preferred payment method.</div>
            </div>
            <span class="editor-required">* Required</span>
        </div>
        @include('suppliers._form', ['s' => $supplier ?? null])
        <div class="editor-actions">
            <a href="{{ route('suppliers.index') }}" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">{{ $isEdit ? 'Update Supplier' : 'Save Supplier' }}</button>
        </div>
    </form>
</div>
@endsection
