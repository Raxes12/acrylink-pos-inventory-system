@extends('layouts.app')
@section('title', 'System Settings')
@section('page-label', 'System Settings')

@section('content')
<x-page-header title="System Settings" subtitle="Manage the client business details and shared Acry-Link preferences." />

@php
    $sections = [
        'general' => ['General', 'dashboard'],
        'inventory' => ['Inventory', 'stock'],
        'sales' => ['Sales & POS', 'pos'],
        'receipt' => ['Receipt', 'receipts'],
        'security' => ['Security', 'shield'],
        'maintenance' => ['Backup & Maintenance', 'audit'],
    ];
@endphp
<div class="settings-layout">
    <nav class="settings-nav" aria-label="Settings sections">
        @foreach($sections as $key => [$label, $icon])
        <a href="{{ route('settings.index', ['section' => $key]) }}" class="settings-nav-link {{ $section === $key ? 'is-active' : '' }}" @if($section === $key) aria-current="page" @endif><x-icon :name="$icon" /><span>{{ $label }}</span></a>
        @endforeach
    </nav>

    <div class="settings-content">
        @if($section === 'general')
        <form method="POST" action="{{ route('settings.general.update') }}" class="card settings-card" enctype="multipart/form-data">@csrf @method('PUT')
            <div class="card-header"><div><strong>General Settings</strong><div class="text-muted text-sm">Business identity shared by the application and printed documents.</div></div></div>
            <div class="settings-card-body">
                <div class="settings-heading"><strong>Client Business Information</strong><span>Used on receipts and exported reports. This does not rename the Acry-Link system.</span></div>
                <section class="settings-logo-editor" aria-labelledby="business-logo-title">
                    <div class="settings-logo-preview" data-logo-dropzone tabindex="0" role="button" aria-label="Choose or drop a new business logo">
                        <img id="businessLogoPreview" src="{{ $businessLogoUrl }}" alt="Current business logo">
                        <span class="settings-logo-preview-action"><i class="bi bi-camera" aria-hidden="true"></i> Replace</span>
                    </div>
                    <div class="settings-logo-content">
                        <div class="settings-logo-title-row">
                            <div><strong id="business-logo-title">Business Logo</strong><p>Appears on sales receipts and payment acknowledgments.</p></div>
                            <span class="settings-logo-state"><i class="bi bi-check-circle-fill" aria-hidden="true"></i>{{ $hasCustomBusinessLogo ? 'Custom logo' : 'Default logo' }}</span>
                        </div>
                        <div class="settings-logo-drop-copy"><i class="bi bi-cloud-arrow-up" aria-hidden="true"></i><span><strong>Drop an image here</strong><small>or select one from your device</small></span></div>
                        <div class="settings-logo-controls">
                            <label class="settings-logo-upload" for="business_logo"><i class="bi bi-folder2-open" aria-hidden="true"></i><span>{{ $hasCustomBusinessLogo ? 'Choose new logo' : 'Choose logo' }}</span></label>
                            <input id="business_logo" name="business_logo" type="file" accept="image/png,image/jpeg,image/webp" data-logo-input>
                            @if($hasCustomBusinessLogo)
                            <label class="settings-logo-reset"><input type="checkbox" name="reset_business_logo" value="1" data-logo-reset data-default-logo="{{ asset(\App\Services\SystemSettingsService::DEFAULTS['business.logo_path']) }}"><i class="bi bi-arrow-counterclockwise" aria-hidden="true"></i><span>Use default</span></label>
                            @endif
                        </div>
                        <span class="settings-logo-file" data-logo-file aria-live="polite"><i class="bi bi-info-circle" aria-hidden="true"></i> PNG, JPG, or WEBP &middot; Maximum 2 MB</span>
                        @error('business_logo')<span class="field-error">{{ $message }}</span>@enderror
                    </div>
                </section>
                <div class="settings-form-grid">
                    <div class="form-group settings-wide"><label for="business_name">Business Name</label><input id="business_name" name="business_name" maxlength="120" required autocomplete="organization" value="{{ old('business_name', $settings['business.name']) }}" aria-describedby="business_name_help"><span id="business_name_help" class="form-help">Shown as the business heading on customer-facing documents.</span>@error('business_name')<span class="field-error">{{ $message }}</span>@enderror</div>
                    <div class="form-group settings-wide"><label for="business_address">Address</label><input id="business_address" name="business_address" maxlength="255" autocomplete="street-address" value="{{ old('business_address', $settings['business.address']) }}">@error('business_address')<span class="field-error">{{ $message }}</span>@enderror</div>
                    <div class="form-group"><label for="business_contact_number">Contact Number</label><input id="business_contact_number" name="business_contact_number" maxlength="40" autocomplete="tel" value="{{ old('business_contact_number', $settings['business.contact_number']) }}">@error('business_contact_number')<span class="field-error">{{ $message }}</span>@enderror</div>
                    <div class="form-group"><label for="business_email">Email</label><input id="business_email" name="business_email" type="email" maxlength="120" autocomplete="email" value="{{ old('business_email', $settings['business.email']) }}">@error('business_email')<span class="field-error">{{ $message }}</span>@enderror</div>
                </div>
                <div class="settings-heading"><strong>Regional Settings</strong><span>Current system-wide values are fixed to avoid inconsistent transaction formatting.</span></div>
                <dl class="settings-readonly"><div><dt>Currency</dt><dd>Philippine Peso (&#8369;)</dd></div><div><dt>Timezone</dt><dd>Asia/Manila</dd></div><div><dt>Date format</dt><dd>MMM DD, YYYY</dd></div></dl>
            </div>
            <div class="editor-actions"><span class="settings-save-hint">Changes apply to future and existing receipt views.</span><button class="btn btn-primary" type="submit"><i class="bi bi-check2" aria-hidden="true"></i> Save General Settings</button></div>
        </form>
        @elseif($section === 'inventory')
        <section class="card settings-card">
            <div class="card-header"><div><strong>Inventory Settings</strong><div class="text-muted text-sm">Manage shared product reference data.</div></div></div>
            <div class="settings-card-body">
                <a class="settings-resource" href="{{ route('categories.index') }}"><span class="settings-resource-icon"><x-icon name="categories" /></span><span><strong>Product Categories</strong><small>{{ number_format($categoryCount) }} categories &middot; open the existing category workspace</small></span><i class="bi bi-chevron-right" aria-hidden="true"></i></a>
                <div class="settings-heading"><strong>Units of Measurement</strong><span>Codes are used by products and variants. Units in use cannot be deleted.</span></div>
                <form method="POST" action="{{ route('settings.units.store') }}" class="settings-unit-add">@csrf<input name="code" maxlength="20" required placeholder="Code (e.g. PCS)" aria-label="Unit code"><input name="name" maxlength="50" required placeholder="Display name" aria-label="Unit name"><button class="btn btn-primary" type="submit">Add Unit</button></form>
                <div class="settings-unit-list">
                    @foreach($units as $unit)
                    <div class="settings-unit-row">
                        <form method="POST" action="{{ route('settings.units.update', $unit) }}">@csrf @method('PUT')<input name="code" maxlength="20" required value="{{ $unit->code }}" aria-label="Unit code"><input name="name" maxlength="50" required value="{{ $unit->name }}" aria-label="Unit name"><button class="btn btn-secondary btn-sm" type="submit">Save</button></form>
                        <form method="POST" action="{{ route('settings.units.destroy', $unit) }}" data-confirm data-confirm-title="Delete unit?" data-confirm-message="Delete {{ $unit->code }} if it is not used by any product?">@csrf @method('DELETE')<button class="btn btn-danger btn-sm" type="submit">Delete</button></form>
                    </div>
                    @endforeach
                </div>
            </div>
        </section>
        @elseif($section === 'receipt')
        <form method="POST" action="{{ route('settings.receipt.update') }}" class="card settings-card">@csrf @method('PUT')
            <div class="card-header"><div><strong>Receipt Settings</strong><div class="text-muted text-sm">Control presentation fields used by new and existing receipt views.</div></div></div>
            <div class="settings-card-body">
                <div class="form-group"><label for="footer_message">Receipt Footer Message</label><textarea id="footer_message" name="footer_message" maxlength="240" required>{{ old('footer_message', $settings['receipt.footer_message']) }}</textarea>@error('footer_message')<span class="field-error">{{ $message }}</span>@enderror</div>
                @foreach(['show_cashier' => ['Show cashier name', 'Identify the staff member who processed the sale.'], 'show_customer' => ['Show customer name', 'Show the customer on receipts when a customer is attached.'], 'show_payment_method' => ['Show payment method', 'Include cash, GCash, check, credit, or card information.']] as $field => [$label, $help])
                <label class="editor-check"><input type="checkbox" name="{{ $field }}" value="1" @checked(old($field, $settings['receipt.'.$field]))><span><strong>{{ $label }}</strong><small>{{ $help }}</small></span></label>
                @endforeach
            </div>
            <div class="editor-actions"><button class="btn btn-primary" type="submit">Save Receipt Settings</button></div>
        </form>
        @elseif($section === 'security')
        <section class="card settings-card"><div class="card-header"><div><strong>Security Configuration</strong><div class="text-muted text-sm">Operational controls are managed by the existing security engine.</div></div></div><div class="settings-card-body"><div class="settings-notice"><x-icon name="shield" /><div><strong>No duplicated security switches</strong><p>Login detection thresholds and response actions are already enforced by Security Rules. Manage those live controls in the Security Center.</p><a class="btn btn-secondary btn-sm" href="{{ route('security.rules') }}">Manage Security Rules</a></div></div></div></section>
        @elseif($section === 'sales')
        <section class="card settings-card"><div class="card-header"><div><strong>Sales &amp; POS</strong><div class="text-muted text-sm">Current transaction behavior remains protected by the established POS workflow.</div></div></div><div class="settings-card-body"><div class="settings-notice"><x-icon name="pos" /><div><strong>No safe global overrides are needed</strong><p>Payment availability, credit validation, receipt sequences, refunds, and checkout rules are already enforced by database-backed services. They have not been duplicated here.</p></div></div></div></section>
        @else
        <section class="card settings-card"><div class="card-header"><div><strong>Backup &amp; Maintenance</strong><div class="text-muted text-sm">Read-only environment information without exposing credentials or destructive actions.</div></div></div><div class="settings-card-body"><dl class="settings-readonly"><div><dt>Application</dt><dd>{{ config('app.name', 'Acry-Link') }}</dd></div><div><dt>Laravel</dt><dd>{{ app()->version() }}</dd></div><div><dt>PHP</dt><dd>{{ PHP_VERSION }}</dd></div><div><dt>Database driver</dt><dd>{{ strtoupper($databaseDriver) }}</dd></div><div><dt>Environment</dt><dd>{{ app()->environment() }}</dd></div></dl><div class="settings-notice"><x-icon name="audit" /><div><strong>Backup actions are not exposed</strong><p>Automated database backups require deployment-specific storage, retention, and encryption. Adding a generic download button could expose customer and security data.</p></div></div></div></section>
        @endif
    </div>
</div>
@endsection

@push('styles')
@vite('resources/css/views/settings-1.css')
@endpush

@push('scripts')
@vite('resources/js/views/settings-1.js')
@endpush
