@extends('layouts.app')
@php($isEdit = isset($customer))
@section('title', $isEdit ? 'Edit Customer' : 'Add Customer')
@section('page-label', 'Customers / ' . ($isEdit ? 'Edit' : 'Add'))

@section('content')
<div class="editor-page customer-form-page">
    <a href="{{ route('customers.index') }}" class="editor-breadcrumb">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
            stroke-linecap="round" stroke-linejoin="round">
            <path d="M19 12H5"/><path d="m12 19-7-7 7-7"/>
        </svg>
        Back to Customers
    </a>
    <x-page-header :title="$isEdit ? 'Edit Customer' : 'Add Customer'" :subtitle="$isEdit ? $customer->customer_code : 'Create a customer and set their account preferences.'" />
    <form method="POST" action="{{ $isEdit ? route('customers.update', $customer) : route('customers.store') }}" class="card editor-card">
        @csrf
        @if($isEdit) @method('PUT') @endif
        <div class="editor-card-head"><div class="editor-card-title">Customer information</div><span class="editor-required">* Required</span></div>
        <div class="editor-body">
            <section class="editor-section">
                <div class="editor-section-head"><div class="editor-section-title">Contact details</div></div>
                <div class="form-group">
                    <label for="customer_name">Customer name *</label>
                    <input id="customer_name" name="customer_name" value="{{ old('customer_name', $customer->customer_name ?? '') }}" maxlength="150" required autofocus>
                </div>
                <div class="grid-2">
                    <div class="form-group"><label for="phone">Phone</label><input id="phone" type="tel" name="phone" maxlength="30" autocomplete="tel" value="{{ old('phone', $customer->phone ?? '') }}"></div>
                    <div class="form-group"><label for="email">Email</label><input id="email" type="email" name="email" maxlength="255" autocomplete="email" value="{{ old('email', $customer->email ?? '') }}"></div>
                </div>
                <div class="form-group"><label for="address">Address</label><textarea id="address" name="address" rows="3" maxlength="2000">{{ old('address', $customer->address ?? '') }}</textarea></div>
            </section>
            <section class="editor-section">
                <div class="editor-section-head"><div class="editor-section-title">Account and credit settings</div><p class="editor-section-copy">Credit changes are recorded in the audit log.</p></div>
                <div class="grid-2">
                    <div class="form-group">
                        <label for="status">Status *</label>
                        <select id="status" name="status" required><option value="active" @selected(old('status', $customer->status ?? 'active') === 'active')>Active</option><option value="inactive" @selected(old('status', $customer->status ?? 'active') === 'inactive')>Inactive</option></select>
                    </div>
                    <div class="form-group">
                        <label for="credit_enabled">Customer credit *</label>
                        <select id="credit_enabled" name="credit_enabled" required><option value="0" @selected(!old('credit_enabled', $customer->credit_enabled ?? false))>Disabled</option><option value="1" @selected(old('credit_enabled', $customer->credit_enabled ?? false))>Enabled</option></select>
                    </div>
                    <div class="form-group"><label for="credit_limit">Credit limit (PHP) *</label><input id="credit_limit" type="number" name="credit_limit" min="0" max="9999999999.99" step="0.01" value="{{ old('credit_limit', $customer->credit_limit ?? '0.00') }}" required><p class="form-help">Maximum unpaid balance allowed for this customer.</p></div>
                    <div class="form-group"><label for="default_credit_term_days">Default credit term (days) *</label><input id="default_credit_term_days" type="number" name="default_credit_term_days" min="1" max="65535" step="1" value="{{ old('default_credit_term_days', $customer->default_credit_term_days ?? 30) }}" required><p class="form-help">Used to calculate the due date for new credit sales.</p></div>
                </div>
            </section>
        </div>
        <div class="editor-actions"><a href="{{ route('customers.index') }}" class="btn btn-secondary">Cancel</a><button type="submit" class="btn btn-primary">{{ $isEdit ? 'Save Changes' : 'Create Customer' }}</button></div>
    </form>
</div>
@endsection

@push('styles')
@vite('resources/css/views/customers-1.css')
@endpush
