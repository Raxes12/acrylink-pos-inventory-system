@extends('layouts.app')
@php($isEdit = isset($user))
@section('title', $isEdit ? 'Edit User' : 'Add User')
@section('page-label', 'Users / ' . ($isEdit ? 'Edit User' : 'Add User'))

@section('content')
<div class="editor-page">
    <a href="{{ route('users.index') }}" class="editor-breadcrumb">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5"/><path d="m12 19-7-7 7-7"/></svg>
        Users
    </a>
    <div class="page-toolbar">
        <div>
            <h1 class="page-heading">{{ $isEdit ? 'Edit User' : 'Add User' }}</h1>
            <p class="page-sub">{{ $isEdit ? 'Update staff access and account details.' : 'Create a staff account and choose its access level.' }}</p>
        </div>
    </div>

    <form method="POST" action="{{ $isEdit ? route('users.update', $user) : route('users.store') }}" class="card editor-card">
        @csrf
        @if($isEdit) @method('PUT') @endif
        <div class="editor-card-head">
            <div>
                <div class="editor-card-title">Account information</div>
                <div class="editor-card-note">Set the staff identity, access level, and account status.</div>
            </div>
            <span class="editor-required">* Required</span>
        </div>
        @include('users._form', ['user' => $user ?? null, 'isAdd' => !$isEdit])
        <div class="editor-actions">
            <a href="{{ route('users.index') }}" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">{{ $isEdit ? 'Save Changes' : 'Create User' }}</button>
        </div>
    </form>
</div>
@endsection
