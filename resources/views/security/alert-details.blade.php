@extends('layouts.app')
@section('title', 'Anomaly Alert Details')
@section('page-label', 'Anomaly Alerts / View Details')

@section('content')
@php
    $event = $alert->intrusionEvent;
    $statusClass = $alert->alert_status === 'resolved' ? 'status-active' : ($alert->alert_status === 'new' ? 'status-inactive' : 'status-low');
    $severityClass = in_array($alert->severity, ['high', 'critical'], true) ? 'status-inactive' : 'status-low';
@endphp

<a href="{{ route('security.events') }}" class="editor-breadcrumb">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
        stroke-linecap="round" stroke-linejoin="round">
        <path d="M19 12H5"/><path d="m12 19-7-7 7-7"/>
    </svg>
    Back to Alerts
</a>

<x-page-header title="" subtitle="">
    <x-slot:actions>
        @if($alert->alert_status === 'new')
        <form method="POST" action="{{ route('security.events.alerts.acknowledge', $alert) }}">@csrf @method('PATCH')<button class="btn btn-secondary" type="submit">Acknowledge</button></form>
        @endif
        @if(in_array($alert->alert_status, ['new', 'acknowledged'], true))
        <form method="POST" action="{{ route('security.events.alerts.resolve', $alert) }}">@csrf @method('PATCH')<button class="btn btn-success" type="submit">Resolve</button></form>
        @endif
    </x-slot:actions>
</x-page-header>

<div class="security-detail-banner {{ in_array($alert->severity, ['high', 'critical'], true) ? 'security-detail-banner--danger' : '' }}">
    <span class="security-detail-banner__icon"><x-icon name="alerts" /></span>
    <div class="security-detail-banner__copy"><span class="security-detail-eyebrow">{{ strtoupper($alert->severity) }} severity</span><strong>{{ $alert->alert_title }}</strong></div>
    <span class="status {{ $statusClass }}">{{ ucfirst($alert->alert_status) }}</span>
</div>

<div class="grid-2 security-top-grid">
    <div class="card">
        <div class="card-header">
            <span class="card-title"><x-icon name="alerts" /> Alert Information</span>
            <span class="status {{ $statusClass }}">{{ ucfirst($alert->alert_status) }}</span>
        </div>
        <div class="card-body security-card-body">
            <div class="detail-item">
                <div class="detail-label">Title</div>
                <div class="detail-value fw-bold">{{ $alert->alert_title }}</div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Message</div>
                <div class="detail-value">{{ $alert->alert_message }}</div>
            </div>
            <div class="detail-grid">
                <div class="detail-item">
                    <div class="detail-label">Severity</div>
                    <span class="status {{ $severityClass }}">{{ strtoupper($alert->severity) }}</span>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Channel</div>
                    <span class="status status-low">{{ ucfirst($alert->alert_channel) }}</span>
                </div>
            </div>
            <div class="detail-grid">
                <div class="detail-item">
                    <div class="detail-label">Acknowledged By</div>
                    <div class="detail-value">{{ $alert->acknowledgedBy?->username ?? '-' }}</div>
                    <div class="security-note">{{ $alert->acknowledged_at?->format('M d, Y h:i A') ?? '' }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Resolved By</div>
                    <div class="detail-value">{{ $alert->resolvedBy?->username ?? '-' }}</div>
                    <div class="security-note">{{ $alert->resolved_at?->format('M d, Y h:i A') ?? '' }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <span class="card-title"><x-icon name="shield" /> Anomaly Event</span>
            <span class="status {{ $event?->event_status === 'resolved' ? 'status-active' : ($event?->event_status === 'open' ? 'status-inactive' : 'status-low') }}">{{ ucfirst($event?->event_status ?? 'missing') }}</span>
        </div>
        <div class="card-body security-card-body">
            <div class="detail-grid">
                <div class="detail-item">
                    <div class="detail-label">Event Type</div>
                    <div class="detail-value">{{ $event?->event_type ? str_replace('_', ' ', ucfirst($event->event_type)) : '-' }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Source Module</div>
                    <div class="detail-value">{{ $event?->source_module ? str_replace('_', ' ', ucwords($event->source_module, '_')) : '-' }}</div>
                </div>
            </div>
            <div class="detail-grid">
                <div class="detail-item">
                    <div class="detail-label">IP Address</div>
                    <div class="detail-value detail-value--mono">{{ $event?->ip_address ?? '-' }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">MAC Address</div>
                    <div class="detail-value detail-value--mono">{{ $event?->mac_address ?? '-' }}</div>
                </div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Description</div>
                <div class="detail-value">{{ $event?->description ?? '-' }}</div>
            </div>
            <div class="detail-item">
                <div class="detail-label">User Agent</div>
                <div class="detail-value detail-value--mono">{{ $event?->user_agent ?? '-' }}</div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Detected</div>
                <div class="detail-value">{{ $event?->detected_at?->format('M d, Y h:i A') ?? '-' }}</div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
@vite('resources/css/views/security-pages-1.css')
@endpush
