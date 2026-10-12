@extends('layouts.app')
@section('title', 'IDS Event Details')
@section('page-label', 'Anomaly Alerts')

@section('content')
@php
    $statusClass = $event->event_status === 'resolved' ? 'status-active' : ($event->event_status === 'open' ? 'status-inactive' : 'status-low');
    $severityClass = in_array($event->severity, ['high', 'critical'], true) ? 'status-inactive' : 'status-low';
@endphp

<x-security-nav />
<x-page-header title="IDS Event Details" subtitle="{{ str_replace('_', ' ', ucfirst($event->event_type)) }}">
    <x-slot:actions>
        <a href="{{ route('security.events') }}" class="btn btn-secondary">Back</a>
    </x-slot:actions>
</x-page-header>

<div class="security-detail-banner {{ in_array($event->severity, ['high', 'critical'], true) ? 'security-detail-banner--danger' : '' }}">
    <span class="security-detail-banner__icon"><x-icon name="shield" /></span>
    <div class="security-detail-banner__copy"><span class="security-detail-eyebrow">{{ strtoupper($event->severity) }} severity</span><strong>{{ str_replace('_', ' ', ucfirst($event->event_type)) }}</strong></div>
    <span class="status {{ $statusClass }}">{{ ucfirst($event->event_status) }}</span>
</div>

<div class="grid-2 security-top-grid">
    <div class="card">
        <div class="card-header">
            <span class="card-title"><x-icon name="shield" /> Anomaly Event</span>
            <span class="status {{ $statusClass }}">{{ ucfirst($event->event_status) }}</span>
        </div>
        <div class="card-body security-card-body">
            <div class="detail-grid">
                <div class="detail-item">
                    <div class="detail-label">Event Type</div>
                    <div class="detail-value">{{ str_replace('_', ' ', ucfirst($event->event_type)) }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Severity</div>
                    <span class="status {{ $severityClass }}">{{ strtoupper($event->severity) }}</span>
                </div>
            </div>
            <div class="detail-grid">
                <div class="detail-item">
                    <div class="detail-label">Source Module</div>
                    <div class="detail-value">{{ $event->source_module ? str_replace('_', ' ', ucwords($event->source_module, '_')) : '-' }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Rule</div>
                    <div class="detail-value">{{ $event->rule?->rule_name ?? '-' }}</div>
                </div>
            </div>
            @if($event->request_path || $event->user_role || $event->required_roles)
            <div class="detail-grid">
                <div class="detail-item">
                    <div class="detail-label">Attempted Endpoint</div>
                    <div class="detail-value detail-value--mono">{{ trim(($event->request_method ?? '').' '.($event->request_path ?? '')) ?: '-' }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Role Check</div>
                    <div class="detail-value">{{ $event->user_role ?? '-' }} / required: {{ $event->required_roles ?? '-' }}</div>
                </div>
            </div>
            @endif
            <div class="detail-grid">
                <div class="detail-item">
                    <div class="detail-label">IP Address</div>
                    <div class="detail-value detail-value--mono">{{ $event->ip_address ?? '-' }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">MAC Address</div>
                    <div class="detail-value detail-value--mono">{{ $event->mac_address ?? '-' }}</div>
                </div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Description</div>
                <div class="detail-value">{{ $event->description }}</div>
            </div>
            <div class="detail-item">
                <div class="detail-label">User Agent</div>
                <div class="detail-value detail-value--mono">{{ $event->user_agent ?? '-' }}</div>
            </div>
            <div class="detail-grid">
                <div class="detail-item">
                    <div class="detail-label">Detected</div>
                    <div class="detail-value">{{ $event->detected_at?->format('M d, Y h:i A') ?? '-' }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">User</div>
                    <div class="detail-value">{{ $event->user?->username ?? '-' }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <span class="card-title"><x-icon name="alerts" /> Linked Dashboard Alerts</span>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Alert</th>
                        <th>Status</th>
                        <th>Email</th>
                        <th>Created</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($event->alerts as $alert)
                @php
                    $latestEmail = $alert->notificationLogs->sortByDesc('created_at')->first();
                    $emailNeeded = in_array($alert->alert_channel, ['email', 'both'], true);
                    $emailStatus = $emailNeeded ? ($latestEmail?->send_status ?? 'pending') : 'No email needed';
                @endphp
                <tr>
                    <td>
                        <div class="fw-bold">{{ $alert->alert_title }}</div>
                        <div class="security-note">{{ $alert->alert_message }}</div>
                    </td>
                    <td><span class="status {{ $alert->alert_status === 'resolved' ? 'status-active' : ($alert->alert_status === 'new' ? 'status-inactive' : 'status-low') }}">{{ ucfirst($alert->alert_status) }}</span></td>
                    <td><span class="status {{ $emailStatus === 'sent' ? 'status-active' : ($emailStatus === 'failed' ? 'status-inactive' : 'status-low') }}">{{ ucfirst($emailStatus) }}</span></td>
                    <td class="td-muted td-nowrap">{{ $alert->created_at?->format('M d, Y h:i A') ?? '-' }}</td>
                </tr>
                @empty
                <x-empty-state colspan="4" title="No linked dashboard alerts" message="This IDS event has no linked alert record yet." icon="alerts" />
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('styles')
@vite('resources/css/views/security-pages-1.css')
@endpush
