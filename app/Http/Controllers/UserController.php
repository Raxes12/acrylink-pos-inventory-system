<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Role;
use App\Models\User;
use App\Services\ReferenceDataService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('role')->orderBy('name')->paginate(20);
        $userSummary = [
            'total' => User::count(),
            'active' => User::where('status', 'active')->count(),
            'mfa' => User::where('mfa_enabled', true)->count(),
        ];

        return view('users.index', [
            'users' => $users,
            'userSummary' => $userSummary,
            'roles' => app(ReferenceDataService::class)->roles(),
        ]);
    }

    public function create()
    {
        return view('users.form', [
            'roles' => app(ReferenceDataService::class)->roles(),
        ]);
    }

    public function show(User $user)
    {
        $user->load('role');

        $loginAttempts = $user->loginAttempts()
            ->latest('attempted_at')
            ->take(10)
            ->get();

        return view('users.show', [
            'user' => $user,
            'loginAttempts' => $loginAttempts,
        ]);
    }

    public function edit(User $user)
    {
        $this->ensureUserIsEditable($user);

        return view('users.form', [
            'user' => $user->load('role'),
            'roles' => app(ReferenceDataService::class)->roles(),
        ]);
    }

    public function store(Request $request)
    {
        $this->mergeRoleIdFromLegacyInput($request);

        $data = $request->validate([
            'username' => 'required|string|max:50|unique:users,username',
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|max:150|unique:users,email',
            'password' => 'required|string|min:6|max:255|confirmed',
            'role_id'  => ['required', Rule::exists('roles', 'role_id')],
            'status'   => 'required|in:active,inactive',
            'mfa_enabled' => 'nullable|boolean',
        ]);

        $data['password'] = Hash::make($data['password']);
        $data['mfa_enabled'] = $request->boolean('mfa_enabled');
        $this->syncLegacyRole($data);
        $user = User::create($data);
        AuditLog::record('USER', "Created user: {$user->username} ({$user->roleLabel()})", null, [
            'affected_table' => 'users',
            'affected_record_id' => $user->id,
            'new_values' => $user->only(['username', 'name', 'email', 'role', 'role_id', 'status', 'mfa_enabled']),
        ]);
        return redirect()->route('users.index')->with('success', "User \"{$user->username}\" created.");
    }

    public function update(Request $request, User $user)
    {
        $this->ensureUserIsEditable($user);
        $this->mergeRoleIdFromLegacyInput($request);

        $data = $request->validate([
            'username' => "required|string|max:50|unique:users,username,{$user->id}",
            'name'     => 'required|string|max:100',
            'email'    => "required|email|max:150|unique:users,email,{$user->id}",
            'role_id'  => ['required', Rule::exists('roles', 'role_id')],
            'status'   => 'required|in:active,inactive',
            'mfa_enabled' => 'nullable|boolean',
            'password' => 'nullable|string|min:6|max:255|confirmed',
        ]);

        $oldValues = $user->only(['username', 'name', 'email', 'role', 'role_id', 'status', 'mfa_enabled']);
        $oldRole = $user->roleLabel();

        if (empty($data['password'])) unset($data['password']);
        else $data['password'] = Hash::make($data['password']);

        $data['mfa_enabled'] = $request->has('mfa_enabled')
            ? $request->boolean('mfa_enabled')
            : $user->mfa_enabled;
        $this->syncLegacyRole($data);
        $user->update($data);
        $user->refresh();
        $newRole = $user->roleLabel();
        $detail = $oldRole !== $newRole
            ? "Updated user: {$user->username}. Role changed from {$oldRole} to {$newRole}."
            : "Updated user: {$user->username}";

        AuditLog::record('USER', $detail, null, [
            'affected_table' => 'users',
            'affected_record_id' => $user->id,
            'old_values' => $oldValues,
            'new_values' => $user->only(['username', 'name', 'email', 'role', 'role_id', 'status', 'mfa_enabled']),
        ]);
        return redirect()->route('users.index')->with('success', "User \"{$user->username}\" updated.");
    }

    public function toggleStatus(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->withErrors(['error' => 'You cannot deactivate your own account.']);
        }
        $newStatus = $user->status === 'active' ? 'inactive' : 'active';
        $oldValues = $user->only(['status']);
        $user->update(['status' => $newStatus]);
        $verb = $newStatus === 'active' ? 'Activated' : 'Deactivated';
        AuditLog::record('USER', "{$verb} user: {$user->username}", null, [
            'affected_table' => 'users',
            'affected_record_id' => $user->id,
            'old_values' => $oldValues,
            'new_values' => $user->only(['status']),
        ]);
        return back()->with('success', "User \"{$user->username}\" {$newStatus}.");
    }

    private function syncLegacyRole(array &$data): void
    {
        $roleName = Role::whereKey($data['role_id'])->value('role_name');
        $data['role'] = Role::legacySlugForName($roleName);
    }

    private function mergeRoleIdFromLegacyInput(Request $request): void
    {
        if ($request->filled('role_id') || !$request->filled('role')) {
            return;
        }

        $roleName = match ($request->input('role')) {
            User::ROLE_ADMIN => Role::ADMIN,
            User::ROLE_INVENTORY => Role::INVENTORY_STAFF,
            default => Role::CASHIER,
        };

        $request->merge([
            'role_id' => Role::where('role_name', $roleName)->value('role_id'),
        ]);
    }

    private function ensureUserIsEditable(User $user): void
    {
        abort_if(
            $user->is(auth()->user()),
            403,
            'You cannot edit your own administrator account.'
        );
    }
}
