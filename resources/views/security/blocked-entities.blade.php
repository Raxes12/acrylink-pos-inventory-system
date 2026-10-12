@extends('layouts.app')
@section('title', 'Blocked Entities')
@section('page-label', 'Blocked Entities')

@section('content')
<x-security-nav />
<x-page-header
    title="Blocklist"
    subtitle="Review access restrictions, understand why they were created, and safely release active blocks."
/>

@if($blocklistUnavailable ?? false)
<div class="alert alert-error">Blocklist table is not available yet. Run database migrations to enable blocked entity enforcement.</div>
@endif

@unless($blocklistUnavailable ?? false)
<div class="security-metrics security-metrics--three blocklist-metrics" aria-label="Blocklist summary">
    <div class="security-metric security-metric--danger"><span class="security-metric__icon"><x-icon name="blocklist" /></span><span class="security-metric__body"><span class="security-metric__label">Active blocks</span><strong>{{ number_format($activeBlocks) }}</strong><small>Currently enforced</small></span></div>
    <div class="security-metric security-metric--warning"><span class="security-metric__icon"><x-icon name="shield" /></span><span class="security-metric__body"><span class="security-metric__label">Permanent</span><strong>{{ number_format($permanentBlocks) }}</strong><small>No automatic expiry</small></span></div>
    <div class="security-metric"><span class="security-metric__icon"><x-icon name="transactions" /></span><span class="security-metric__body"><span class="security-metric__label">All entries</span><strong>{{ number_format($totalBlocks) }}</strong><small>Complete block history</small></span></div>
</div>
<form method="GET" action="{{ route('security.blocked-entities') }}" class="blocklist-filter" role="search" data-live-filter>
    <div class="blocklist-filter__top">
        <label class="security-search-control blocklist-search" for="blockedEntitySearch">
            <x-icon name="search" />
            <span class="visually-hidden">Search blocklist</span>
            <input id="blockedEntitySearch" type="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search IP, MAC, user, reason, or administrator..." autocomplete="off">
        </label>
        <label class="blocklist-select"><span>Entity type</span><select name="type"><option value="">All types</option>@foreach(['user' => 'User', 'ip_address' => 'IP Address', 'mac_address' => 'MAC Address', 'device' => 'Device'] as $value => $label)<option value="{{ $value }}" @selected(($filters['type'] ?? '') === $value)>{{ $label }}</option>@endforeach</select></label>
        <label class="blocklist-select"><span>Block status</span><select name="status"><option value="">All statuses</option>@foreach(['active' => 'Active', 'expired' => 'Expired', 'removed' => 'Removed'] as $value => $label)<option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>@endforeach</select></label>
        <span class="live-filter-status blocklist-filter-status" data-live-filter-status role="status" aria-live="polite"></span>
    </div>
</form>
@endunless

<div class="card blocklist-card" data-live-results>
    <div class="card-header">
        <div><span class="card-title"><x-icon name="blocklist" /> Blocked entities</span><span class="blocklist-result-copy">{{ number_format($blockedEntities->total()) }} {{ Str::plural('entry', $blockedEntities->total()) }} found</span></div>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Entity</th>
                    <th>Reason</th>
                    <th>Status</th>
                    <th>Blocked</th>
                    <th>Expires</th>
                    <th>Blocked By</th>
                    <th class="td-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse($blockedEntities as $entity)
            @php
                $statusClass = $entity->status === 'active' ? 'status-inactive' : ($entity->status === 'expired' ? 'status-low' : 'status-active');
            @endphp
            <tr>
                <td class="blocklist-entity-cell">
                    <div class="blocklist-entity-type"><span class="blocklist-entity-icon" aria-hidden="true"><x-icon :name="$entity->entity_type === 'user' ? 'users' : ($entity->entity_type === 'device' ? 'devices' : 'shield')" /></span><span>{{ str_replace('_', ' ', ucfirst($entity->entity_type)) }}</span></div>
                    <div class="code-badge security-code">{{ $entity->entity_value }}</div>
                </td>
                <td class="table-meta blocklist-reason" title="{{ $entity->reason }}">{{ $entity->reason }}</td>
                <td><span class="status {{ $statusClass }}">{{ ucfirst($entity->status) }}</span></td>
                <td class="td-muted td-nowrap">{{ $entity->blocked_at?->format('M d, Y h:i A') ?? '-' }}</td>
                <td class="td-muted td-nowrap">{{ $entity->expires_at?->format('M d, Y h:i A') ?? 'Permanent' }}</td>
                <td class="td-muted">{{ $entity->blocker?->username ?? 'System' }}</td>
                <td>
                    <div class="security-row-actions">
                        @if($entity->status === 'active')
                        <form method="POST" action="{{ route('security.blocked-entities.expire', $entity) }}" class="security-form-inline">
                            @csrf @method('PATCH')
                            <button class="btn btn-secondary btn-sm w-full" type="submit">Expire</button>
                        </form>
                        <form method="POST" action="{{ route('security.blocked-entities.remove', $entity) }}" class="security-form-inline"
                            data-confirm
                            data-confirm-title="Remove Block"
                            data-confirm-message="Remove this blocklist entry? The entity may be able to access the system again."
                            data-confirm-button="Remove">
                            @csrf @method('PATCH')
                            <button class="btn btn-danger btn-sm w-full" type="submit">Remove</button>
                        </form>
                        @else
                        <span class="security-note">No action needed</span>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <x-empty-state colspan="7" :title="collect($filters)->filter()->isNotEmpty() ? 'No matching blocklist entries' : 'No blocked entities'" :message="collect($filters)->filter()->isNotEmpty() ? 'Try a different search term or clear the active filters.' : 'Blocked IP addresses, devices, MAC addresses, and users will appear here when generated by security rules.'" icon="blocklist">
            </x-empty-state>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="view-blades-footer">
        <div class="pagination-summary">
            Showing {{ $blockedEntities->firstItem() ?? 0 }}-{{ $blockedEntities->lastItem() ?? 0 }} of {{ $blockedEntities->total() }} entities
        </div>

        @if($blockedEntities->hasPages())
        @php
            $currentPage = $blockedEntities->currentPage();
            $lastPage = $blockedEntities->lastPage();
            $pages = collect([1, $currentPage - 1, $currentPage, $currentPage + 1, $lastPage])
                ->filter(fn ($page) => $page >= 1 && $page <= $lastPage)
                ->unique()
                ->sort()
                ->values();
            $previousPage = null;
        @endphp
        <nav class="rules-pagination" aria-label="Blocklist pagination">
            @if($blockedEntities->onFirstPage())
                <span class="rules-page is-disabled" aria-disabled="true">Previous</span>
            @else
                <a class="rules-page" href="{{ $blockedEntities->previousPageUrl() }}" rel="prev">Previous</a>
            @endif

            @foreach($pages as $page)
                @if($previousPage && $page > $previousPage + 1)
                    <span class="rules-page rules-page--gap" aria-hidden="true">...</span>
                @endif

                @if($page === $currentPage)
                    <span class="rules-page is-active" aria-current="page">{{ $page }}</span>
                @else
                    <a class="rules-page" href="{{ $blockedEntities->url($page) }}">{{ $page }}</a>
                @endif

                @php $previousPage = $page; @endphp
            @endforeach

            @if($blockedEntities->hasMorePages())
                <a class="rules-page" href="{{ $blockedEntities->nextPageUrl() }}" rel="next">Next</a>
            @else
                <span class="rules-page is-disabled" aria-disabled="true">Next</span>
            @endif
        </nav>
        @endif
    </div>
</div>
@endsection

@push('styles')
@vite('resources/css/views/security-pages-1.css')
@endpush
