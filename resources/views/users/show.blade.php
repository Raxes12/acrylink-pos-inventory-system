@extends('layouts.app')
@section('title', $user->name)
@section('page-label', 'User Management / View User')

@php
    $roleColor = [
        'admin' => 'accent',
        'inventory_staff' => 'teal',
        'cashier' => 'purple',
    ][$user->roleColorKey()] ?? 'purple';

    $employeeId = $user->employee_id ?? '-';
    $phone = $user->phone ?? '-';
    $lastLoginIp = $user->last_login_ip ?? '-';
    $failedLogins = $user->failed_login_attempts ?? 0;

    // Pass $loginAttempts from the controller if you have a login-attempts table.
    $loginAttempts = $loginAttempts ?? collect();
@endphp

@section('content')
<div class="user-show-page">
    <a href="{{ route('users.index') }}" class="editor-breadcrumb">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
            stroke-linecap="round" stroke-linejoin="round">
            <path d="M19 12H5"/><path d="m12 19-7-7 7-7"/>
        </svg>
        Back to Users
    </a>

    <div class="user-show-grid">

        <section class="card user-profile-card">
            <div class="user-profile-avatar-large" aria-hidden="true">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>

            <h1>{{ $user->name }}</h1>

            <p class="user-profile-email">{{ $user->email }}</p>

            <x-tag :variant="$roleColor">
                {{ $user->roleLabel() }}
            </x-tag>

            <div class="user-profile-status">
                <span class="status status-{{ $user->status }}">
                    {{ ucfirst($user->status) }}
                </span>
            </div>

            <div class="user-profile-actions">
                @if($user->is(auth()->user()))
                    <p class="form-help">Signed-in administrator protected: you cannot edit this account while using it.</p>
                    <button type="button" class="btn btn-secondary" disabled
                            title="You cannot edit your signed-in administrator account">
                        <i class="bi bi-pencil" aria-hidden="true"></i>
                        Edit disabled
                    </button>
                @else
                    @if(auth()->user()->canEdit())
                    <a href="{{ route('users.edit', $user) }}" class="btn btn-primary">
                        <i class="bi bi-pencil" aria-hidden="true"></i>
                        Edit
                    </a>
                    @endif
                @endif
            </div>
        </section>

        <div class="user-show-right">

            <section class="card user-account-card">
                <div class="card-header">
                    <span class="card-title">Account Details</span>
                </div>

                <div class="card-body user-account-details">
                    <div class="user-detail-row">
                        <span>Username</span>
                        <strong class="td-mono">{{ $user->username }}</strong>
                    </div>

                    <div class="user-detail-row">
                        <span>Employee ID</span>
                        <strong>{{ $employeeId }}</strong>
                    </div>

                    <div class="user-detail-row">
                        <span>Phone</span>
                        <strong>{{ $phone }}</strong>
                    </div>

                    <div class="user-detail-row">
                        <span>Status</span>
                        <strong>{{ ucfirst($user->status) }}</strong>
                    </div>

                    <div class="user-detail-row">
                        <span>MFA</span>
                        <strong>{{ $user->mfa_enabled ? 'Enabled' : 'Disabled' }}</strong>
                    </div>

                    <div class="user-detail-row">
                        <span>Last Login</span>
                        <strong>{{ $user->last_login?->format('M d, Y h:i A') ?? 'Never' }}</strong>
                    </div>

                    <div class="user-detail-row">
                        <span>Last Login IP</span>
                        <strong>{{ $lastLoginIp }}</strong>
                    </div>

                    <div class="user-detail-row">
                        <span>Failed Logins</span>
                        <strong>{{ number_format((int) $failedLogins) }}</strong>
                    </div>
                </div>
            </section>

            <section class="card user-login-card">
                <div class="card-header">
                    <span class="card-title">Recent Login Attempts</span>
                </div>

                <div class="card-body user-login-attempts">
                    @if($loginAttempts->isEmpty())
                        <div class="user-login-empty">
                            <i class="bi bi-shield-check" aria-hidden="true"></i>
                            <span>No recent login attempts</span>
                        </div>
                    @else
                        @foreach($loginAttempts as $attempt)
                        <div class="user-login-row">
                            <div class="user-login-result">
                                <div class="user-login-result">
                                    <span class="login-result {{ $attempt->attempt_status === 'success' ? 'is-success' : 'is-failed' }}">
                                        {{ ucfirst($attempt->attempt_status) }}
                                    </span>
                                    <span>from {{ $attempt->ip_address ?? '-' }}</span>
                                </div>
                            </div>

                            <span class="user-login-time">
                                {{ $attempt->attempted_at?->diffForHumans() ?? '-' }}
                            </span>
                        </div>
                        @endforeach
                    @endif
                </div>
            </section>

        </div>
    </div>
</div>
@endsection

@push('styles')
@vite('resources/css/views/users-show-2.css')
@endpush
