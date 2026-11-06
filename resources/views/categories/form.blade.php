@extends('layouts.app')
@php($isEdit = isset($category))
@section('title', $isEdit ? 'Edit Category' : 'Add Category')
@section('page-label', 'Categories / ' . ($isEdit ? 'Edit Category' : 'Add Category'))

@section('content')
<div class="editor-page editor-page--compact category-editor">
    <a href="{{ route('categories.index') }}" class="editor-breadcrumb">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5"/><path d="m12 19-7-7 7-7"/></svg>
        Categories
    </a>
    <div class="page-toolbar">
        <div>
            <h1 class="page-heading">{{ $isEdit ? 'Rename Category' : 'Add Category' }}</h1>
            <p class="page-sub">{{ $isEdit ? 'Choose a clear name that remains familiar to inventory and sales staff.' : 'Create a clear product group that staff can recognize quickly.' }}</p>
        </div>
    </div>

    <form method="POST" action="{{ $isEdit ? route('categories.update', $category) : route('categories.store') }}" class="card editor-card">
        @csrf
        @if($isEdit) @method('PUT') @endif
        <div class="editor-card-head">
            <div>
                <div class="editor-card-title">Category information</div>
                <div class="editor-card-note">Use a short, recognizable label for product grouping.</div>
            </div>
            <span class="editor-required">* Required</span>
        </div>
        <div class="editor-section">
            <div class="category-editor-layout">
            <div class="form-group category-name-field">
                <label for="categoryName">Category Name <span aria-hidden="true">*</span></label>
                <input id="categoryName" type="text" name="name" value="{{ old('name', $category->name ?? '') }}" placeholder="e.g. Transmission" maxlength="50" required autofocus aria-describedby="categoryNameHelp categoryNameCount" data-category-name-input>
                <div class="category-field-help"><span id="categoryNameHelp">Use a short, specific name. Product names and brands belong on the product or variant.</span><span id="categoryNameCount" data-category-name-count>0/50</span></div>
                @error('name')<span class="field-error" role="alert">{{ $message }}</span>@enderror
            </div>
            <aside class="category-name-preview" aria-label="Category card preview"><span>Preview</span><div><strong data-category-name-initials>CA</strong><p data-category-name-preview>Category name</p></div></aside>
            </div>
        </div>
        <div class="editor-actions">
            <a href="{{ route('categories.index') }}" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">{{ $isEdit ? 'Update Category' : 'Save Category' }}</button>
        </div>
    </form>
</div>
@endsection

@push('styles')
@vite('resources/css/views/categories-index-1.css')
@endpush

@push('scripts')
@vite('resources/js/views/categories-index-1.js')
@endpush
