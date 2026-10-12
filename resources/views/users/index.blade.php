@extends('layouts.app')
@section('title', 'User Management')
@section('page-label', 'User Management')

@section('content')
@php
$roleVariants = ['admin' => 'accent', 'inventory_staff' => 'teal', 'cashier' => 'purple'];
$rolePerms  = [
    'Admin' => 'Full access: users, inventory, POS, security, audit logs, and device controls.',
    'Inventory Staff' => 'Inventory, suppliers, refunds, and stocks overview. Cannot manage users or security.',
    'Cashier' => 'POS terminal, owned transactions, receipts, and customer credit payments.',
];
$roleKeys = ['Admin' => 'admin', 'Inventory Staff' => 'inventory_staff', 'Cashier' => 'cashier'];
$editUsersData = $users->getCollection()
    ->reject(fn ($listedUser) => $listedUser->id === auth()->id())
    ->mapWithKeys(fn ($listedUser) => [(string) $listedUser->id => [
        'id' => $listedUser->id,
        'name' => $listedUser->name,
        'username' => $listedUser->username,
        'email' => $listedUser->email,
        'role_id' => $listedUser->role_id,
        'status' => $listedUser->status,
        'mfa_enabled' => (bool) $listedUser->mfa_enabled,
        'update_url' => route('users.update', $listedUser),
    ]]);
@endphp

<div class="users-page">
<x-page-header title="User Management" subtitle="Control staff access, roles, and account status.">
    <x-slot:actions>
        <a href="{{ route('users.create') }}" class="btn btn-primary" data-open-user-create>
            <x-icon name="plus" />
            Add User
        </a>
    </x-slot:actions>
</x-page-header>

<section class="users-summary" aria-label="User account overview">
    <article class="card users-summary-card"><span class="users-summary-icon"><i class="bi bi-people" aria-hidden="true"></i></span><div><span>Total Staff</span><strong>{{ number_format($userSummary['total']) }}</strong><small>Registered user accounts</small></div></article>
    <article class="card users-summary-card"><span class="users-summary-icon is-success"><i class="bi bi-person-check" aria-hidden="true"></i></span><div><span>Active Accounts</span><strong>{{ number_format($userSummary['active']) }}</strong><small>Can currently sign in</small></div></article>
    <article class="card users-summary-card"><span class="users-summary-icon is-secure"><i class="bi bi-shield-check" aria-hidden="true"></i></span><div><span>MFA Protected</span><strong>{{ number_format($userSummary['mfa']) }}</strong><small>Require an email OTP</small></div></article>
</section>

<div class="card users-directory-card">
    <div class="users-table-head">
        <div>
            <h2>Staff accounts</h2>
            <p>{{ $users->total() }} registered user{{ $users->total() === 1 ? '' : 's' }}</p>
        </div>
        <div class="users-status-key" aria-label="Account status legend">
            <span><i class="users-status-dot is-active"></i> Active</span>
            <span><i class="users-status-dot is-inactive"></i> Inactive</span>
        </div>
    </div>
    <div class="table-wrap users-table-wrap" tabindex="0" role="region" aria-label="Staff accounts table">
        <table class="users-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Last Login</th>
                    <th>Status</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse($users as $u)
            @php $isCurrentUser = $u->id === auth()->id(); @endphp
            <tr class="{{ $isCurrentUser ? 'is-current-user' : '' }}">
                <td data-label="Staff member">
                    <div class="user-identity-cell">
                        <span class="user-list-avatar" aria-hidden="true">{{ strtoupper(substr($u->name, 0, 2)) }}</span>
                        <span>
                            <strong>{{ $u->name }}</strong>
                            @if($isCurrentUser)<small>Signed-in account</small>@endif
                        </span>
                    </div>
                </td>
                <td data-label="Username" class="td-mono">{{ $u->username }}</td>
                <td data-label="Email"><a class="user-email" href="mailto:{{ $u->email }}">{{ $u->email }}</a></td>
                <td data-label="Role">
                    <x-tag :variant="$roleVariants[$u->roleColorKey()] ?? 'purple'">{{ $u->roleLabel() }}</x-tag>
                </td>
                <td data-label="Last login" class="td-muted td-nowrap">{{ $u->last_login ? $u->last_login->format('M d, Y h:i A') : 'Never signed in' }}</td>
                <td data-label="Status">
                    <span class="status status-{{ $u->status }}">{{ ucfirst($u->status) }}</span>
                    @if($u->mfa_enabled)<span class="user-mfa" title="Multi-factor authentication enabled">MFA</span>@endif
                </td>
                <td data-label="Actions">
                    <div class="table-actions table-actions--center">
                        <a href="{{ route('users.show', $u) }}" class="btn btn-secondary btn-sm user-action-btn" aria-label="View {{ $u->name }}" title="View user">
                            <i class="bi bi-eye" aria-hidden="true"></i>
                        </a>
                        @if($isCurrentUser)
                        <button type="button" class="btn btn-secondary btn-sm user-action-btn" disabled aria-label="Editing your signed-in administrator account is disabled" title="You cannot edit your signed-in administrator account">
                            <i class="bi bi-pencil" aria-hidden="true"></i>
                        </button>
                        @else
                        <a href="{{ route('users.edit', $u) }}" class="btn btn-secondary btn-sm user-action-btn" aria-label="Edit {{ $u->name }}" title="Edit user" data-open-user-edit data-user-id="{{ $u->id }}">
                            <i class="bi bi-pencil" aria-hidden="true"></i>
                        </a>
                        <form method="POST" action="{{ route('users.toggle', $u) }}">
                            @csrf @method('PATCH')
                            <button type="submit" class="btn btn-sm user-status-action {{ $u->status==='active' ? 'btn-danger' : 'btn-success' }}" aria-label="{{ $u->status==='active' ? 'Deactivate' : 'Activate' }} {{ $u->name }}">
                                <i class="bi {{ $u->status === 'active' ? 'bi-person-x' : 'bi-person-check' }}" aria-hidden="true"></i>
                                {{ $u->status === 'active' ? 'Deactivate' : 'Activate' }}
                            </button>
                        </form>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <x-empty-state colspan="7" title="No users found" message="Add staff accounts to control who can access POS, inventory, and admin tools." icon="users" />
            @endforelse
            </tbody>
        </table>
    </div>
    @if($users->hasPages())
    <div class="users-pagination">
        <span>Showing {{ $users->firstItem() }}-{{ $users->lastItem() }} of {{ $users->total() }}</span>
        {{ $users->links() }}
    </div>
    @endif
</div>

<div class="card users-roles-card">
    <div class="card-header"><span class="card-title">Role Permissions</span></div>
    <div class="card-body">
        <div class="grid-3">
            @foreach($rolePerms as $role => $desc)
            @php $key = $roleKeys[$role]; @endphp
            <div class="role-card role-card--{{ $key }}">
                <div class="role-card__title role-card__title--{{ $key }}">{{ $role }}</div>
                <div class="role-card__copy">{{ $desc }}</div>
            </div>
            @endforeach
        </div>
    </div>
</div>

<div id="createUserModal" class="modal-backdrop user-modal-backdrop" style="display:none" role="presentation" aria-hidden="true" data-open-on-error="{{ old('_user_create') === '1' && $errors->any() ? 'true' : 'false' }}">
    <div class="modal user-create-modal" role="dialog" aria-modal="true" aria-labelledby="createUserTitle" aria-describedby="createUserDescription">
        <div class="modal-header user-modal-header">
            <div><span class="modal-title" id="createUserTitle">Add Staff Account</span><span id="createUserDescription">Create sign-in credentials and assign the appropriate access level.</span></div>
            <button type="button" class="modal-close-btn" data-close-user-create aria-label="Close user form">&times;</button>
        </div>
        <form method="POST" action="{{ route('users.store') }}">
            @csrf
            <input type="hidden" name="_user_create" value="1">
            <div class="modal-body user-modal-body">
                @include('users._form', ['user' => null, 'isAdd' => true])
            </div>
            <div class="modal-footer user-modal-footer">
                <button type="button" class="btn btn-secondary" data-close-user-create>Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="bi bi-person-plus" aria-hidden="true"></i> Create User</button>
            </div>
        </form>
    </div>
</div>

<script type="application/json" id="userEditData">@json($editUsersData, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT)</script>
<div id="editUserModal" class="modal-backdrop user-modal-backdrop" style="display:none" role="presentation" aria-hidden="true" data-open-on-error="{{ old('_user_edit') === '1' && $errors->any() ? 'true' : 'false' }}" data-error-user-id="{{ old('_user_edit_id') }}">
    <div class="modal user-create-modal user-edit-modal" role="dialog" aria-modal="true" aria-labelledby="editUserTitle" aria-describedby="editUserDescription">
        <div class="modal-header user-modal-header">
            <div class="user-modal-heading">
                <span class="user-modal-avatar" data-edit-user-avatar aria-hidden="true">U</span>
                <span><span class="modal-title" id="editUserTitle">Edit Staff Account</span><span id="editUserDescription">Update account details, access level, and sign-in protection.</span></span>
            </div>
            <button type="button" class="modal-close-btn" data-close-user-edit aria-label="Close edit user form">&times;</button>
        </div>
        <form method="POST" action="" data-user-edit-form>
            @csrf @method('PUT')
            <input type="hidden" name="_user_edit" value="1">
            <input type="hidden" name="_user_edit_id" value="{{ old('_user_edit_id') }}">
            <div class="modal-body user-modal-body">
                <div class="editor-body">
                    <section class="editor-section">
                        <div class="editor-section-head"><div class="editor-section-title">Staff Details</div><div class="editor-section-copy">Keep the staff member's working identity and contact email accurate.</div></div>
                        <div class="grid-2">
                            <div class="form-group"><label for="editUserName">Full Name *</label><input id="editUserName" type="text" name="name" value="{{ old('_user_edit') === '1' ? old('name') : '' }}" autocomplete="name" required></div>
                            <div class="form-group"><label for="editUserUsername">Username *</label><input id="editUserUsername" type="text" name="username" value="{{ old('_user_edit') === '1' ? old('username') : '' }}" autocomplete="username" required></div>
                        </div>
                        <div class="form-group"><label for="editUserEmail">Email *</label><input id="editUserEmail" type="email" name="email" value="{{ old('_user_edit') === '1' ? old('email') : '' }}" autocomplete="email" required></div>
                    </section>
                    <section class="editor-section">
                        <div class="editor-section-head"><div class="editor-section-title">Access & Security</div><div class="editor-section-copy">Choose permissions, account availability, and optional login protection.</div></div>
                        <div class="grid-2">
                            <div class="form-group"><label for="editUserRole">Role *</label><select id="editUserRole" name="role_id" required>@foreach($roles as $role)<option value="{{ $role->role_id }}" @selected(old('_user_edit') === '1' && (int) old('role_id') === $role->role_id)>{{ $role->role_name }}</option>@endforeach</select></div>
                            <div class="form-group"><label for="editUserStatus">Status *</label><select id="editUserStatus" name="status" required><option value="active" @selected(old('_user_edit') === '1' && old('status') === 'active')>Active</option><option value="inactive" @selected(old('_user_edit') === '1' && old('status') === 'inactive')>Inactive</option></select></div>
                        </div>
                        <div class="form-group"><input type="hidden" name="mfa_enabled" value="0"><label class="editor-check"><input type="checkbox" name="mfa_enabled" value="1" @checked(old('_user_edit') === '1' && old('mfa_enabled'))><span><strong>Require email OTP on login</strong><small>Add an extra verification step for this account.</small></span></label></div>
                        <div class="grid-2 user-password-grid">
                            <div class="form-group"><label for="editUserPassword">New Password</label><div class="editor-password-wrap"><input id="editUserPassword" type="password" name="password" placeholder="Leave blank to keep current password" autocomplete="new-password"><button type="button" class="editor-password-toggle" data-editor-password-toggle aria-controls="editUserPassword" aria-label="Show new password" aria-pressed="false"><i class="bi bi-eye" aria-hidden="true"></i></button></div></div>
                            <div class="form-group"><label for="editUserPasswordConfirmation">Retype New Password</label><div class="editor-password-wrap"><input id="editUserPasswordConfirmation" type="password" name="password_confirmation" placeholder="Type the same password again" autocomplete="new-password"><button type="button" class="editor-password-toggle" data-editor-password-toggle aria-controls="editUserPasswordConfirmation" aria-label="Show password confirmation" aria-pressed="false"><i class="bi bi-eye" aria-hidden="true"></i></button></div></div>
                        </div>
                        <div class="form-help user-password-help"><i class="bi bi-info-circle" aria-hidden="true"></i> Password changes are optional. Leave both fields empty to keep the current password.</div>
                    </section>
                </div>
            </div>
            <div class="modal-footer user-modal-footer"><button type="button" class="btn btn-secondary" data-close-user-edit>Cancel</button><button type="submit" class="btn btn-primary"><i class="bi bi-check-lg" aria-hidden="true"></i> Save Changes</button></div>
        </form>
    </div>
</div>
</div>

@endsection

@push('styles')
@vite('resources/css/views/users-index-1.css')
@endpush

@push('scripts')
@vite('resources/js/views/users-index-1.js')
@endpush
