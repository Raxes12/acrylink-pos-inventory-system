@extends('layouts.app')
@section('title', 'Customers')
@section('page-label', 'Customers')

@php
$customerEditData = $customers->getCollection()->mapWithKeys(fn ($listedCustomer) => [(string) $listedCustomer->id => [
    'id' => $listedCustomer->id,
    'name' => $listedCustomer->customer_name,
    'customer_name' => $listedCustomer->customer_name,
    'customer_code' => $listedCustomer->customer_code,
    'phone' => $listedCustomer->phone,
    'email' => $listedCustomer->email,
    'address' => $listedCustomer->address,
    'status' => $listedCustomer->status,
    'credit_enabled' => (bool) $listedCustomer->credit_enabled,
    'credit_limit' => $listedCustomer->credit_limit,
    'default_credit_term_days' => $listedCustomer->default_credit_term_days,
    'update_url' => route('customers.update', $listedCustomer),
]]);
@endphp

@section('content')
<div class="customers-page">
<x-page-header title="Customers" subtitle="Manage customer profiles, account access, and credit terms from one place.">
    <x-slot:actions><a href="{{ route('accounts-receivable.index') }}" class="btn btn-secondary">Accounts Receivable</a>
        <a href="{{ route('customers.create') }}" class="btn btn-primary" data-open-customer-create>
            <x-icon name="plus" />
            Add Customer
        </a>
    </x-slot:actions>
</x-page-header>

<form method="GET" action="{{ route('customers.index') }}" class="filter-bar customer-filter" data-live-filter>
    <div class="customer-search-wrap"><i class="bi bi-search" aria-hidden="true"></i><input type="search" name="search" maxlength="150" autocomplete="off" value="{{ $filters['search'] ?? '' }}" placeholder="Search name, code, phone, or email..." aria-label="Search customers"></div>
    <select name="status" aria-label="Customer status">
        <option value="">All statuses</option>
        <option value="active" @selected(($filters['status'] ?? '') === 'active')>Active</option>
        <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>Inactive</option>
    </select>
    <select name="credit_enabled" aria-label="Credit eligibility">
        <option value="">All credit settings</option>
        <option value="1" @selected(($filters['credit_enabled'] ?? '') === '1')>Credit enabled</option>
        <option value="0" @selected(($filters['credit_enabled'] ?? '') === '0')>Credit disabled</option>
    </select>
    @if(filled($filters['search'] ?? null) || filled($filters['status'] ?? null) || isset($filters['credit_enabled']))
    <a class="btn btn-secondary customer-filter-clear" href="{{ route('customers.index') }}" data-live-filter-clear><i class="bi bi-x-lg" aria-hidden="true"></i> Clear filters</a>
    @endif
    <span class="live-filter-status" data-live-filter-status role="status" aria-live="polite"></span>
</form>

<div class="card customer-table-card" data-live-results>
    <div class="card-header"><div><span class="card-title">Customer Directory</span><span class="customer-card-caption">Contact information and credit availability.</span></div><span class="tag tag--accent">{{ $customers->total() }} customers</span></div>
    <div class="table-wrap" tabindex="0" role="region" aria-label="Customers table">
        <table>
            <thead><tr><th>Code / Name</th><th>Contact</th><th>Status</th><th>Credit</th><th class="text-right">Credit limit</th><th>Term</th><th>Actions</th></tr></thead>
            <tbody>
                @forelse($customers as $customer)
                <tr>
                    <td data-label="Customer"><div class="customer-directory-identity"><span class="customer-directory-avatar" aria-hidden="true">{{ strtoupper(substr($customer->customer_name, 0, 1)) }}</span><div class="td-stack"><strong>{{ $customer->customer_name }}</strong><span class="td-mono">{{ $customer->customer_code }}</span></div></div></td>
                    <td data-label="Contact"><div class="td-stack customer-contact"><span><i class="bi bi-telephone" aria-hidden="true"></i>{{ $customer->phone ?: '—' }}</span><span><i class="bi bi-envelope" aria-hidden="true"></i>{{ $customer->email ?: '—' }}</span></div></td>
                    <td data-label="Status"><x-tag :variant="$customer->status === 'active' ? 'success' : 'muted'">{{ ucfirst($customer->status) }}</x-tag></td>
                    <td data-label="Credit"><span class="tag {{ $customer->credit_enabled ? 'tag--success' : 'tag--muted' }}">{{ $customer->credit_enabled ? 'Enabled' : 'Disabled' }}</span></td>
                    <td data-label="Credit limit" class="text-right customer-credit-limit">&#8369;{{ number_format((float) $customer->credit_limit, 2) }}</td>
                    <td data-label="Term">{{ $customer->default_credit_term_days }} days</td>
                    <td data-label="Actions">
                        <div class="table-actions customer-actions">
                            <a class="btn btn-secondary btn-sm user-action-btn" aria-label="View {{ $customer->customer_name }}" title="View customer" href="{{ route('customers.show', $customer) }}">
                                <i class="bi bi-eye" aria-hidden="true"></i>
                            </a>
                            <a class="btn btn-secondary btn-sm user-action-btn" aria-label="Edit {{ $customer->customer_name }}" title="Edit customer" href="{{ route('customers.edit', $customer) }}" data-open-customer-edit data-customer-id="{{ $customer->id }}">
                                <i class="bi bi-pencil" aria-hidden="true"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <x-empty-state colspan="7" title="No customers found" message="Add a customer or adjust your search filters." />
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="view-blades-footer">
        <div class="pagination-summary">
            Showing {{ $customers->firstItem() ?? 0 }}-{{ $customers->lastItem() ?? 0 }} of {{ $customers->total() }} customers
        </div>
        @if($customers->hasPages())
        @php
            $currentPage = $customers->currentPage();
            $lastPage = $customers->lastPage();
            $pages = collect([1, $currentPage - 1, $currentPage, $currentPage + 1, $lastPage])
                ->filter(fn ($page) => $page >= 1 && $page <= $lastPage)
                ->unique()
                ->sort()
                ->values();
            $previousPage = null;
        @endphp
        <nav class="rules-pagination" aria-label="Stock variant pagination">
            @if($customers->onFirstPage())
                <span class="rules-page is-disabled" aria-disabled="true">Previous</span>
            @else
                <a class="rules-page" href="{{ $customers->previousPageUrl() }}" rel="prev">Previous</a>
            @endif

            @foreach($pages as $page)
                @if($previousPage && $page > $previousPage + 1)
                    <span class="rules-page rules-page--gap" aria-hidden="true">...</span>
                @endif

                @if($page === $currentPage)
                    <span class="rules-page is-active" aria-current="page">{{ $page }}</span>
                @else
                    <a class="rules-page" href="{{ $customers->url($page) }}">{{ $page }}</a>
                @endif

                @php $previousPage = $page; @endphp
            @endforeach

            @if($customers->hasMorePages())
                <a class="rules-page" href="{{ $customers->nextPageUrl() }}" rel="next">Next</a>
            @else
                <span class="rules-page is-disabled" aria-disabled="true">Next</span>
            @endif
        </nav>
        @endif
    </div>
</div>

<div id="createCustomerModal" class="modal-backdrop customer-modal-backdrop" style="display:none;" role="presentation" aria-hidden="true" data-open-on-error="{{ old('_customer_edit') !== '1' && $errors->any() ? 'true' : 'false' }}">
    <div class="modal customer-create-modal" role="dialog" aria-modal="true" aria-labelledby="createCustomerTitle" aria-describedby="createCustomerDescription">
        <div class="modal-header customer-modal-header">
            <div><span class="modal-title" id="createCustomerTitle">Add Customer</span><span class="customer-modal-caption" id="createCustomerDescription">Create a customer profile and configure credit access.</span></div>
            <button type="button" class="modal-close-btn" data-close-customer-create aria-label="Close customer form">&times;</button>
        </div>
        <form method="POST" action="{{ route('customers.store') }}" class="customer-create-form">
            @csrf
            <input type="hidden" name="_customer_create" value="1">
            <div class="modal-body customer-modal-body">
                <section class="customer-modal-section">
                    <div class="customer-modal-section-head"><span class="customer-modal-step">1</span><div><strong>Customer details</strong><span>Basic contact information used on sales and account records.</span></div></div>
                    <div class="customer-modal-fields">
                        <div class="form-group customer-field-wide"><label for="modal_customer_name">Customer Name <span aria-hidden="true">*</span></label><input id="modal_customer_name" name="customer_name" value="{{ old('customer_name') }}" maxlength="150" required autocomplete="name" placeholder="Full customer or business name"><div class="form-help">A customer code is generated automatically after saving.</div></div>
                        <div class="form-group"><label for="modal_phone">Phone</label><input id="modal_phone" type="tel" name="phone" maxlength="30" autocomplete="tel" value="{{ old('phone') }}" placeholder="09XX XXX XXXX"></div>
                        <div class="form-group"><label for="modal_email">Email</label><input id="modal_email" type="email" name="email" maxlength="255" autocomplete="email" value="{{ old('email') }}" placeholder="customer@example.com"></div>
                        <div class="form-group customer-field-wide"><label for="modal_address">Address</label><textarea id="modal_address" name="address" rows="2" maxlength="2000" placeholder="Street, barangay, city, and province">{{ old('address') }}</textarea></div>
                    </div>
                </section>
                <section class="customer-modal-section">
                    <div class="customer-modal-section-head"><span class="customer-modal-step">2</span><div><strong>Account and credit</strong><span>Set purchasing access and default payment terms.</span></div></div>
                    <div class="customer-modal-fields">
                        <div class="form-group"><label for="modal_status">Account Status <span aria-hidden="true">*</span></label><select id="modal_status" name="status" required><option value="active" @selected(old('status', 'active') === 'active')>Active</option><option value="inactive" @selected(old('status') === 'inactive')>Inactive</option></select></div>
                        <div class="form-group"><label for="modal_credit_enabled">Customer Credit <span aria-hidden="true">*</span></label><select id="modal_credit_enabled" name="credit_enabled" required><option value="0" @selected(old('credit_enabled', '0') == '0')>Disabled</option><option value="1" @selected(old('credit_enabled') == '1')>Enabled</option></select></div>
                        <div class="form-group"><label for="modal_credit_limit">Credit Limit (PHP) <span aria-hidden="true">*</span></label><input id="modal_credit_limit" type="number" name="credit_limit" min="0" max="9999999999.99" step="0.01" value="{{ old('credit_limit', '0.00') }}" required><div class="form-help">Maximum unpaid balance.</div></div>
                        <div class="form-group"><label for="modal_credit_term">Default Term <span aria-hidden="true">*</span></label><div class="customer-term-input"><input id="modal_credit_term" type="number" name="default_credit_term_days" min="1" max="65535" step="1" value="{{ old('default_credit_term_days', 30) }}" required><span>days</span></div><div class="form-help">Used for new credit-sale due dates.</div></div>
                    </div>
                    <div class="customer-credit-note"><i class="bi bi-info-circle" aria-hidden="true"></i><span>Credit can be disabled now and enabled later from the customer edit page.</span></div>
                </section>
            </div>
            <div class="modal-footer customer-modal-footer"><button type="button" class="btn btn-secondary" data-close-customer-create>Cancel</button><button type="submit" class="btn btn-primary"><i class="bi bi-person-plus" aria-hidden="true"></i> Create Customer</button></div>
        </form>
    </div>
</div>

<script type="application/json" id="customerEditData">@json($customerEditData, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT)</script>
<div id="editCustomerModal" class="modal-backdrop customer-modal-backdrop" style="display:none;" role="presentation" aria-hidden="true" data-open-on-error="{{ old('_customer_edit') === '1' && $errors->any() ? 'true' : 'false' }}" data-error-customer-id="{{ old('_customer_edit_id') }}">
    <div class="modal customer-create-modal" role="dialog" aria-modal="true" aria-labelledby="editCustomerTitle" aria-describedby="editCustomerDescription">
        <div class="modal-header customer-modal-header">
            <div class="contact-modal-heading"><span class="contact-modal-avatar" data-edit-customer-avatar aria-hidden="true">C</span><span><span class="modal-title" id="editCustomerTitle">Edit Customer</span><span class="customer-modal-caption" id="editCustomerDescription" data-edit-customer-code>Update contact details and credit settings.</span></span></div>
            <button type="button" class="modal-close-btn" data-close-customer-edit aria-label="Close customer edit form">&times;</button>
        </div>
        <form method="POST" action="" class="customer-create-form" data-customer-edit-form>
            @csrf @method('PUT')
            <input type="hidden" name="_customer_edit" value="1"><input type="hidden" name="_customer_edit_id" value="{{ old('_customer_edit_id') }}">
            <div class="modal-body customer-modal-body">
                <section class="customer-modal-section">
                    <div class="customer-modal-section-head"><span class="customer-modal-step">1</span><div><strong>Customer details</strong><span>Keep contact information accurate for sales and account records.</span></div></div>
                    <div class="customer-modal-fields">
                        <div class="form-group customer-field-wide"><label for="edit_customer_name">Customer Name <span aria-hidden="true">*</span></label><input id="edit_customer_name" name="customer_name" value="{{ old('_customer_edit') === '1' ? old('customer_name') : '' }}" maxlength="150" required autocomplete="name"></div>
                        <div class="form-group"><label for="edit_customer_phone">Phone</label><input id="edit_customer_phone" type="tel" name="phone" maxlength="30" autocomplete="tel" value="{{ old('_customer_edit') === '1' ? old('phone') : '' }}"></div>
                        <div class="form-group"><label for="edit_customer_email">Email</label><input id="edit_customer_email" type="email" name="email" maxlength="255" autocomplete="email" value="{{ old('_customer_edit') === '1' ? old('email') : '' }}"></div>
                        <div class="form-group customer-field-wide"><label for="edit_customer_address">Address</label><textarea id="edit_customer_address" name="address" rows="2" maxlength="2000">{{ old('_customer_edit') === '1' ? old('address') : '' }}</textarea></div>
                    </div>
                </section>
                <section class="customer-modal-section">
                    <div class="customer-modal-section-head"><span class="customer-modal-step">2</span><div><strong>Account and credit</strong><span>Control purchasing access, balance limits, and payment terms.</span></div></div>
                    <div class="customer-modal-fields">
                        <div class="form-group"><label for="edit_customer_status">Account Status <span aria-hidden="true">*</span></label><select id="edit_customer_status" name="status" required><option value="active" @selected(old('_customer_edit') === '1' && old('status') === 'active')>Active</option><option value="inactive" @selected(old('_customer_edit') === '1' && old('status') === 'inactive')>Inactive</option></select></div>
                        <div class="form-group"><label for="edit_customer_credit_enabled">Customer Credit <span aria-hidden="true">*</span></label><select id="edit_customer_credit_enabled" name="credit_enabled" required><option value="0" @selected(old('_customer_edit') === '1' && old('credit_enabled') == '0')>Disabled</option><option value="1" @selected(old('_customer_edit') === '1' && old('credit_enabled') == '1')>Enabled</option></select></div>
                        <div class="form-group"><label for="edit_customer_credit_limit">Credit Limit (PHP) <span aria-hidden="true">*</span></label><input id="edit_customer_credit_limit" type="number" name="credit_limit" min="0" max="9999999999.99" step="0.01" value="{{ old('_customer_edit') === '1' ? old('credit_limit') : '' }}" required><div class="form-help">Maximum unpaid balance.</div></div>
                        <div class="form-group"><label for="edit_customer_credit_term">Default Term <span aria-hidden="true">*</span></label><div class="customer-term-input"><input id="edit_customer_credit_term" type="number" name="default_credit_term_days" min="1" max="65535" step="1" value="{{ old('_customer_edit') === '1' ? old('default_credit_term_days') : '' }}" required><span>days</span></div><div class="form-help">Used for new credit-sale due dates.</div></div>
                    </div>
                    <div class="customer-credit-note"><i class="bi bi-journal-check" aria-hidden="true"></i><span>Credit and account changes are recorded automatically in the audit log.</span></div>
                </section>
            </div>
            <div class="modal-footer customer-modal-footer"><button type="button" class="btn btn-secondary" data-close-customer-edit>Cancel</button><button type="submit" class="btn btn-primary"><i class="bi bi-check-lg" aria-hidden="true"></i> Save Changes</button></div>
        </form>
    </div>
</div>
</div>
@endsection

@push('styles')
@vite('resources/css/views/customers-1.css')
@endpush

@push('scripts')
@vite('resources/js/views/customers-index-1.js')
@endpush
