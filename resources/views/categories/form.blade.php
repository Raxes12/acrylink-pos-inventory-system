@extends('layouts.app')
@php($isEdit = isset($category))
@section('title', $isEdit ? 'Edit Category' : 'Add Category')
@section('page-label', 'Categories / ' . ($isEdit ? 'Edit Category' : 'Add Category'))

@section('content')
<div class="editor-page editor-page--compact">
    <a href="{{ route('categories.index') }}" class="editor-breadcrumb">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5"/><path d="m12 19-7-7 7-7"/></svg>
        Categories
    </a>
    <div class="page-toolbar">
        <div>
            <h1 class="page-heading">{{ $isEdit ? 'Rename Category' : 'Add Category' }}</h1>
            <p class="page-sub">{{ $isEdit ? 'Update the category name.' : 'Create a category for grouping products.' }}</p>
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
            <div class="form-group">
                <label>Category Name *</label>
                <input type="text" name="name" value="{{ old('name', $category->name ?? '') }}" placeholder="e.g. Transmission" maxlength="50" required autofocus>
            </div>
        </div>
        <div class="editor-actions">
            <a href="{{ route('categories.index') }}" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">{{ $isEdit ? 'Update Category' : 'Save Category' }}</button>
        </div>
    </form>
</div>
@endsection
