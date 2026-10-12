@extends('layouts.app')
@section('title', 'Authorized Devices')
@section('page-label', 'Authorized Devices')

@section('content')
<x-security-nav />
<div id="devicePageState" hidden data-initial-edit-device="{{ old('_device_id', $editingDevice?->id) }}" data-open-register="{{ $errors->any() && old('_device_form') === 'register' ? 'true' : 'false' }}"></div>
<script type="application/json" id="initialEditingDeviceData">@json($editingDevice, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT)</script>
<x-page-header title="Authorized Devices" subtitle="Control which devices can access Acry-Link and review their assigned users.">
    <x-slot:actions>
        <button type="button" class="btn btn-primary" data-open-device-register><x-icon name="plus" /> Register Device</button>
    </x-slot:actions>
</x-page-header>

<div class="security-metrics device-metrics" aria-label="Authorized device summary">
    <button type="button" class="security-metric device-metric-filter {{ ($filters['status'] ?? '') === 'authorized' ? 'is-active' : '' }}" data-device-status-filter="authorized" aria-pressed="{{ ($filters['status'] ?? '') === 'authorized' ? 'true' : 'false' }}">
        <span class="device-metric-icon device-metric-icon--authorized"><i class="bi bi-shield-check" aria-hidden="true"></i></span>
        <span class="device-metric-content">
            <span class="device-metric-label">Authorized</span><strong>{{ number_format($deviceCounts['authorized']) }}</strong>
            <small>Allowed to connect</small>
            <span class="device-metric-action">View authorized <i class="bi bi-arrow-right" aria-hidden="true"></i></span>
        </span>
    </button>
    <button type="button" class="security-metric device-metric-filter {{ ($filters['status'] ?? '') === 'blocked' ? 'is-active' : '' }}" data-device-status-filter="blocked" aria-pressed="{{ ($filters['status'] ?? '') === 'blocked' ? 'true' : 'false' }}">
        <span class="device-metric-icon device-metric-icon--blocked"><i class="bi bi-slash-circle" aria-hidden="true"></i></span>
        <span class="device-metric-content">
            <span class="device-metric-label">Blocked</span><strong>{{ number_format($deviceCounts['blocked']) }}</strong>
            <small>Access denied</small>
            <span class="device-metric-action">View blocked <i class="bi bi-arrow-right" aria-hidden="true"></i></span>
        </span>
    </button>
    <button type="button" class="security-metric device-metric-filter {{ ($filters['status'] ?? '') === 'inactive' ? 'is-active' : '' }}" data-device-status-filter="inactive" aria-pressed="{{ ($filters['status'] ?? '') === 'inactive' ? 'true' : 'false' }}">
        <span class="device-metric-icon device-metric-icon--inactive"><i class="bi bi-pause-circle" aria-hidden="true"></i></span>
        <span class="device-metric-content">
            <span class="device-metric-label">Inactive</span><strong>{{ number_format($deviceCounts['inactive']) }}</strong>
            <small>Temporarily disabled</small>
            <span class="device-metric-action">View inactive <i class="bi bi-arrow-right" aria-hidden="true"></i></span>
        </span>
    </button>
    <button type="button" class="security-metric device-metric-filter {{ empty($filters['status']) ? 'is-active' : '' }}" data-device-status-filter="" aria-pressed="{{ empty($filters['status']) ? 'true' : 'false' }}">
        <span class="device-metric-icon"><i class="bi bi-pc-display" aria-hidden="true"></i></span>
        <span class="device-metric-content">
            <span class="device-metric-label">Total devices</span><strong>{{ number_format($deviceCounts['total']) }}</strong>
            <small>All registered records</small>
            <span class="device-metric-action">View all <i class="bi bi-arrow-right" aria-hidden="true"></i></span>
        </span>
    </button>
</div>

<form method="GET" action="{{ route('security.devices') }}" class="filter-bar security-filter-bar" role="search" data-live-filter>
    <div class="security-search-control"><x-icon name="search" /><input id="deviceSearch" type="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search device, IP, MAC, or assigned user..."></div>
    <select name="status" aria-label="Filter by device status">
        <option value="">All statuses</option>
        @foreach(['authorized' => 'Authorized', 'blocked' => 'Blocked', 'inactive' => 'Inactive'] as $value => $label)
        <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
        @endforeach
    </select>
    @if(filled($filters['search'] ?? null) || filled($filters['status'] ?? null))
    <a href="{{ route('security.devices') }}" class="btn btn-secondary btn-sm" data-live-filter-clear><i class="bi bi-x-lg" aria-hidden="true"></i> Clear filters</a>
    @endif
    <span class="live-filter-status" data-live-filter-status role="status" aria-live="polite"></span>
</form>

<div class="card device-policy-card">
    <div class="card-header"><div><span class="card-title"><x-icon name="shield" /> Device Access Policy</span><span class="device-card-caption">Identifiers detected for your current request.</span></div></div>
    <div class="card-body device-policy-body">
        <div class="current-device-panel">
            <div class="current-device-row"><span>Current IP</span><code>{{ $currentIp ?? 'Not detected' }}</code></div>
            <div class="current-device-row"><span>Current MAC</span><code>{{ $currentMac ?? 'Not detected' }}</code></div>
        </div>
        <p class="section-note"><i class="bi bi-info-circle" aria-hidden="true"></i> Only <strong>Authorized</strong> records can pass strict device checks. Assigning a user limits the rule to that account.</p>
    </div>
</div>

<div class="card" data-live-results>
    <div class="card-header"><span class="card-title"><x-icon name="devices" /> Registered Devices</span><span class="security-note">{{ $devices->total() }} matching</span></div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Device</th>
                    <th>IP</th>
                    <th>MAC</th>
                    <th>User</th>
                    <th>Status</th>
                    <th class="td-actions text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse($devices as $device)
            <tr class="device-table-row">
                <td><div class="device-table-name"><span class="device-table-icon"><i class="bi bi-pc-display"></i></span><div><strong>{{ $device->device_name }}</strong><small>Updated {{ $device->updated_at?->diffForHumans() ?? 'recently' }}</small></div></div></td>
                <td><span class="code-badge">{{ $device->ip_address ?? '-' }}</span></td>
                <td><span class="code-badge">{{ $device->mac_address ?? '-' }}</span></td>
                <td><div class="device-user-name">{{ $device->assignedUser?->name ?? 'Any active user' }}</div>@if($device->assignedUser)<div class="table-meta">{{ $device->assignedUser->username }}</div>@endif</td>
                <td>
                    <span class="status {{ $device->status === 'authorized' ? 'status-active' : ($device->status === 'blocked' ? 'status-inactive' : 'status-low') }}">
                        {{ ucfirst($device->status) }}
                    </span>
                </td>
                <td>
                    <div class="table-actions table-actions--center device-actions">
                        <button type="button" class="btn btn-secondary btn-sm device-action-btn" data-view-device="{{ $device->id }}" title="View Device" aria-label="View {{ $device->device_name }}">
                            <i class="bi bi-eye"></i>
                        </button>
                        <a href="{{ route('security.devices', ['edit_device' => $device->id]) }}" class="btn btn-secondary btn-sm device-action-btn" data-edit-device="{{ $device->id }}" title="Edit Device" aria-label="Edit {{ $device->device_name }}">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form method="POST" action="{{ route('security.devices.destroy', $device) }}" class="device-action-form"
                            data-confirm
                            data-confirm-title="Are you sure?"
                            data-confirm-message="Delete this device {{ $device->device_name }}? This cannot be undone."
                            data-confirm-button="Confirm">
                            @csrf @method('DELETE')
                            <button type="button" class="btn btn-danger btn-sm device-action-btn" title="Delete Device" aria-label="Delete {{ $device->device_name }}" data-confirm-trigger><i class="bi bi-trash3"></i></button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="empty-state">No devices registered yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div style="padding:14px;">{{ $devices->links() }}</div>
    <script type="application/json" id="authorizedDevicesData">@json($devices->items(), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT)</script>
</div>

<div id="registerDeviceModal" class="modal-backdrop" style="display:none;" role="presentation" aria-hidden="true">
    <div class="modal device-editor-modal" role="dialog" aria-modal="true" aria-labelledby="registerDeviceTitle" aria-describedby="registerDeviceDescription">
        <div class="modal-header">
            <div><span class="modal-title" id="registerDeviceTitle">Register Device</span><span class="device-modal-caption" id="registerDeviceDescription">Add a trusted device rule for POS access.</span></div>
            <button type="button" class="modal-close-btn" aria-label="Close device registration" data-close-device-register>&times;</button>
        </div>
        <form method="POST" action="{{ route('security.devices.store') }}" class="device-modal-form">
            @csrf
            <input type="hidden" name="_device_form" value="register">
            <div class="modal-body device-modal-body">
                <div class="form-group device-form-wide">
                    <label for="registerDeviceName">Device Name <span aria-hidden="true">*</span></label>
                    <input id="registerDeviceName" name="device_name" value="{{ old('device_name') }}" maxlength="100" required placeholder="e.g. Front Counter POS">
                    <div class="form-help">Use a name staff can quickly recognize.</div>
                </div>
                <div class="form-group">
                    <label for="registerDeviceIp">IP Address</label>
                    <input id="registerDeviceIp" name="ip_address" value="{{ old('ip_address') }}" placeholder="{{ request()->ip() }}" inputmode="decimal">
                    <div class="form-help">Current request: {{ $currentIp ?? 'Not detected' }}</div>
                </div>
                <div class="form-group">
                    <label for="registerDeviceMac">MAC Address</label>
                    <input id="registerDeviceMac" name="mac_address" value="{{ old('mac_address') }}" maxlength="17" placeholder="AA:BB:CC:DD:EE:FF" spellcheck="false">
                    <div class="form-help">Provide an IP address, MAC address, or both.</div>
                </div>
                <div class="form-group">
                    <label for="registerDeviceUser">Assigned User</label>
                    <select id="registerDeviceUser" name="assigned_user_id">
                        <option value="">Any active user</option>
                        @foreach($users as $user)
                        <option value="{{ $user->id }}" @selected((string) old('assigned_user_id') === (string) $user->id)>{{ $user->name }} ({{ $user->username }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="registerDeviceStatus">Initial Status</label>
                    <select id="registerDeviceStatus" name="status">
                        <option value="authorized" @selected(old('status', 'authorized') === 'authorized')>Authorized</option>
                        <option value="blocked" @selected(old('status') === 'blocked')>Blocked</option>
                        <option value="inactive" @selected(old('status') === 'inactive')>Inactive</option>
                    </select>
                </div>
                <div class="device-form-note"><i class="bi bi-shield-check" aria-hidden="true"></i><span><strong>Access reminder</strong> Authorized devices can access protected areas when the rest of the security checks pass.</span></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-close-device-register>Cancel</button>
                <button class="btn btn-primary" type="submit"><x-icon name="devices" /> Register Device</button>
            </div>
        </form>
    </div>
</div>

<div id="viewDeviceModal" class="modal-backdrop" style="display:none;" role="presentation" aria-hidden="true">
    <div class="modal device-detail-modal" role="dialog" aria-modal="true" aria-labelledby="viewDeviceTitle">
        <div class="modal-header">
            <span class="modal-title" id="viewDeviceTitle">Device Details</span>
            <button type="button" class="modal-close-btn" aria-label="Close device details" data-close-device-view>&times;</button>
        </div>
        <div class="modal-body">
            <div class="device-detail-summary">
                <span class="device-detail-icon" aria-hidden="true"><i class="bi bi-pc-display"></i></span>
                <div>
                    <div id="viewDeviceName" class="device-detail-name">-</div>
                    <span id="viewDeviceStatus" class="status status-low">-</span>
                </div>
            </div>

            <div class="device-detail-grid">
                <div class="detail-item">
                    <span class="detail-label">IP Address</span>
                    <span id="viewDeviceIp" class="detail-value detail-value--mono">-</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">MAC Address</span>
                    <span id="viewDeviceMac" class="detail-value detail-value--mono">-</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Assigned User</span>
                    <span id="viewDeviceUser" class="detail-value">-</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Created</span>
                    <span id="viewDeviceCreated" class="detail-value">-</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Last Updated</span>
                    <span id="viewDeviceUpdated" class="detail-value">-</span>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-close-device-view>Close</button>
            <a id="viewDeviceEditLink" href="{{ route('security.devices') }}" class="btn btn-primary" data-edit-template="{{ route('security.devices', ['edit_device' => '__ID__'], false) }}">Edit Device</a>
        </div>
    </div>
</div>

<div id="editDeviceModal" class="modal-backdrop" style="display:none;" role="presentation" aria-hidden="true">
    <div class="modal device-editor-modal" role="dialog" aria-modal="true" aria-labelledby="editDeviceTitle">
        <div class="modal-header">
            <span class="modal-title" id="editDeviceTitle">Edit Device Rule</span>
            <button type="button" class="modal-close-btn" aria-label="Close device editor" data-close-device-edit>&times;</button>
        </div>
        <form id="editDeviceForm" method="POST" data-action-template="{{ route('security.devices.update', ['device' => '__ID__'], false) }}">
            @csrf @method('PUT')
            <input type="hidden" name="_device_form" value="edit">
            <input type="hidden" name="_device_id" value="">
            <div class="modal-body device-modal-body">
                <div class="form-group" style="margin:0;"><label>Device Name</label><input name="device_name" required></div>
                <div class="grid-2" style="gap:10px;">
                    <div class="form-group" style="margin:0;"><label>IP Address</label><input name="ip_address"></div>
                    <div class="form-group" style="margin:0;"><label>MAC Address</label><input name="mac_address"></div>
                </div>
                <div class="grid-2" style="gap:10px;">
                    <div class="form-group" style="margin:0;">
                        <label>Assigned User</label>
                        <select name="assigned_user_id">
                            <option value="">Any active user</option>
                            @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->username }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group" style="margin:0;">
                        <label>Status</label>
                        <select name="status">
                            <option value="authorized">Authorized</option>
                            <option value="blocked">Blocked</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-close-device-edit>Cancel</button>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('styles')
@vite('resources/css/views/security-pages-1.css')
@vite('resources/css/views/security-devices-1.css')
@endpush

@push('scripts')
@vite('resources/js/views/security-devices-1.js')
@endpush
