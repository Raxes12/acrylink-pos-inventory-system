@extends('layouts.app')
@php
    $isEdit = isset($variant);
    $wizardStepTwoFields = ['code', 'barcode', 'part_number', 'brand', 'unit', 'size', 'color', 'supplier_id', 'image'];
    $wizardStepThreeFields = ['stock', 'cost', 'price', 'min_stock', 'critical_stock'];
    $initialWizardStep = $errors->hasAny($wizardStepThreeFields) ? 3 : ($errors->hasAny($wizardStepTwoFields) ? 2 : 1);
@endphp
@section('title', $isEdit ? 'Edit Variant' : 'Add Variant')
@section('page-label', 'Inventory / ' . ($isEdit ? 'Edit Variant' : 'Add Variant'))

@section('content')
<div class="editor-page inventory-form-page">
    <a href="{{ route('inventory.index') }}" class="editor-breadcrumb">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5"/><path d="m12 19-7-7 7-7"/></svg>
        Products
    </a>
    <div class="page-toolbar">
        <div>
            <h1 class="page-heading">{{ $isEdit ? 'Edit Variant' : 'Add Variant' }}</h1>
            <p class="page-sub">{{ $isEdit ? 'Update product identity, variant details, and stock thresholds.' : 'Create a product variant with its own pricing and stock.' }}</p>
        </div>
    </div>

    <form method="POST" action="{{ $isEdit ? route('inventory.update', $variant) : route('inventory.store') }}" class="card editor-card inventory-form-card" enctype="multipart/form-data"
        @if(!$isEdit) data-inventory-variant-wizard data-initial-step="{{ $initialWizardStep }}" @endif>
        @csrf
        @if($isEdit) @method('PUT') @endif
        <div class="editor-card-head">
            <div>
                <div class="editor-card-title">Variant information</div>
                <div class="editor-card-note">Complete the three sections below. New variants are active and ready for inventory and POS use.</div>
            </div>
            <div class="inventory-form-head-meta"><span class="inventory-active-default"><i class="bi bi-check-circle" aria-hidden="true"></i>{{ $isEdit ? 'Existing variant' : 'Active by default' }}</span><span class="editor-required">* Required</span></div>
        </div>
        @if(!$isEdit)
        <ol class="inventory-wizard-steps" aria-label="Add variant progress" data-inventory-wizard-steps>
            @foreach([
                1 => ['Product identity', 'Choose or create the product'],
                2 => ['Variant details', 'Add identifying information'],
                3 => ['Pricing & stock', 'Set prices and stock levels'],
            ] as $step => [$label, $description])
            <li data-inventory-step-indicator="{{ $step }}" class="{{ $step === $initialWizardStep ? 'is-active' : ($step < $initialWizardStep ? 'is-complete' : '') }}">
                <button type="button" data-inventory-go-step="{{ $step }}" aria-current="{{ $step === $initialWizardStep ? 'step' : 'false' }}" @disabled($step > $initialWizardStep)>
                    <span>{{ $step }}</span>
                    <div><strong>{{ $label }}</strong><small>{{ $description }}</small></div>
                </button>
            </li>
            @endforeach
        </ol>
        @if($errors->any())
        <div class="alert alert-error inventory-wizard-errors" role="alert">
            <i class="bi bi-exclamation-circle" aria-hidden="true"></i>
            <div><strong>Please review this step.</strong><span>{{ $errors->first() }}</span></div>
        </div>
        @endif
        @endif
        @include('inventory._form', ['variant' => $variant ?? null])
        @if($isEdit)
        <div class="editor-actions inventory-form-actions">
            <span><i class="bi bi-shield-check" aria-hidden="true"></i>Changes are validated before saving.</span>
            <a href="{{ route('inventory.index') }}" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary"><i class="bi bi-check2" aria-hidden="true"></i>{{ $isEdit ? 'Update Variant' : 'Save Variant' }}</button>
        </div>
        @endif
    </form>
</div>
@endsection

@push('styles')
@vite('resources/css/views/inventory-index-1.css')
@endpush

@push('scripts')
@vite('resources/js/views/inventory-form-1.js')
@endpush
