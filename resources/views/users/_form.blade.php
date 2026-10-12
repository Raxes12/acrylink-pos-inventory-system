<div class="editor-body">
    <section class="editor-section">
        <div class="editor-section-head">
            <div class="editor-section-title">Staff Details</div>
            <div class="editor-section-copy">Use the staff member's working identity and email.</div>
        </div>
        <div class="grid-2">
            <div class="form-group">
                <label>Full Name *</label>
                <input type="text" name="name" value="{{ old('name', $user->name ?? '') }}" placeholder="Juan Dela Cruz" autocomplete="name" required autofocus>
            </div>
            <div class="form-group">
                <label>Username *</label>
                <input type="text" name="username" value="{{ old('username', $user->username ?? '') }}" placeholder="juandc" autocomplete="username" required>
            </div>
        </div>
        <div class="form-group">
            <label>Email *</label>
            <input type="email" name="email" value="{{ old('email', $user->email ?? '') }}" placeholder="juan@vmcauto.ph" autocomplete="email" required>
        </div>
    </section>

    <section class="editor-section">
        <div class="editor-section-head">
            <div class="editor-section-title">Access</div>
            <div class="editor-section-copy">Control permissions, status, and sign-in credentials.</div>
        </div>
        <div class="grid-2">
            <div class="form-group">
                <label>Role *</label>
                <select name="role_id" required>
                    @foreach($roles as $role)
                    <option value="{{ $role->role_id }}" {{ (int) old('role_id', $user->role_id ?? 0) === $role->role_id ? 'selected' : '' }}>
                        {{ $role->role_name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Status *</label>
                <select name="status" required>
                    <option value="active" {{ old('status', $user->status ?? 'active') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ old('status', $user->status ?? 'active') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
        </div>
        <div class="form-group">
            <input type="hidden" name="mfa_enabled" value="0">
            <label class="editor-check">
                <input type="checkbox" name="mfa_enabled" value="1" {{ old('mfa_enabled', $user->mfa_enabled ?? false) ? 'checked' : '' }}>
                <span><strong>Require email OTP on login</strong><small>Add an extra verification step for this account.</small></span>
            </label>
        </div>
        <div class="grid-2 user-password-grid">
            <div class="form-group">
                <label for="userPassword">{{ $isAdd ? 'Password' : 'New Password' }} {{ $isAdd ? '*' : '' }}</label>
                <div class="editor-password-wrap">
                    <input id="userPassword" type="password" name="password" placeholder="{{ $isAdd ? 'Minimum 6 characters' : 'Leave blank to keep current password' }}" autocomplete="new-password" {{ $isAdd ? 'required' : '' }}>
                    <button type="button" class="editor-password-toggle" data-editor-password-toggle aria-controls="userPassword" aria-label="Show new password" aria-pressed="false">
                        <svg class="password-eye" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z"/><circle cx="12" cy="12" r="3"/></svg>
                        <svg class="password-eye-off" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path d="M3 3l18 18"/><path d="M10.6 10.6a2 2 0 0 0 2.8 2.8"/><path d="M9.9 4.2A9.5 9.5 0 0 1 12 4c6.5 0 10 8 10 8a18.5 18.5 0 0 1-2.8 4.1"/><path d="M6.6 6.6C3.7 8.6 2 12 2 12s3.5 8 10 8a9.8 9.8 0 0 0 4.6-1.1"/></svg>
                    </button>
                </div>
            </div>
            <div class="form-group">
                <label for="userPasswordConfirmation">{{ $isAdd ? 'Retype Password' : 'Retype New Password' }} {{ $isAdd ? '*' : '' }}</label>
                <div class="editor-password-wrap">
                    <input id="userPasswordConfirmation" type="password" name="password_confirmation" placeholder="Type the same password again" autocomplete="new-password" {{ $isAdd ? 'required' : '' }}>
                    <button type="button" class="editor-password-toggle" data-editor-password-toggle aria-controls="userPasswordConfirmation" aria-label="Show password confirmation" aria-pressed="false">
                        <svg class="password-eye" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z"/><circle cx="12" cy="12" r="3"/></svg>
                        <svg class="password-eye-off" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path d="M3 3l18 18"/><path d="M10.6 10.6a2 2 0 0 0 2.8 2.8"/><path d="M9.9 4.2A9.5 9.5 0 0 1 12 4c6.5 0 10 8 10 8a18.5 18.5 0 0 1-2.8 4.1"/><path d="M6.6 6.6C3.7 8.6 2 12 2 12s3.5 8 10 8a9.8 9.8 0 0 0 4.6-1.1"/></svg>
                    </button>
                </div>
            </div>
        </div>
        <div class="form-help user-password-help">{{ $isAdd ? 'Use at least 6 characters and enter the same password in both fields.' : 'Leave both password fields blank to keep the current password.' }}</div>
    </section>
</div>
