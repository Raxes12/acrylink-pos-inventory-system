@extends('layouts.app')
@section('title', 'Anomaly Alerts')
@section('page-label', 'Anomaly Alerts')

@section('content')
<x-security-nav />
<x-page-header
    title="Anomaly Alerts"
    subtitle="Review unusual transactions and activities, prioritize risk, and track response status."
/>

<div class="security-metrics" aria-label="Anomaly alert summary">
    <div class="security-metric security-metric--danger"><span class="security-metric__icon"><x-icon name="alerts" /></span><span class="security-metric__body"><span class="security-metric__label">Open</span><strong>{{ number_format($eventCounts['open']) }}</strong><small>Needs attention</small></span></div>
    <div class="security-metric security-metric--warning"><span class="security-metric__icon"><x-icon name="shield" /></span><span class="security-metric__body"><span class="security-metric__label">High risk active</span><strong>{{ number_format($eventCounts['high_risk']) }}</strong><small>Prioritize review</small></span></div>
    <div class="security-metric security-metric--success"><span class="security-metric__icon"><x-icon name="audit" /></span><span class="security-metric__body"><span class="security-metric__label">Resolved</span><strong>{{ number_format($eventCounts['resolved']) }}</strong><small>Response completed</small></span></div>
    <div class="security-metric"><span class="security-metric__icon"><x-icon name="transactions" /></span><span class="security-metric__body"><span class="security-metric__label">Total detections</span><strong>{{ number_format($eventCounts['total']) }}</strong><small>Recorded events</small></span></div>
</div>

<form method="GET" action="{{ route('security.events') }}" class="security-alert-filter" role="search" data-live-filter>
    <div class="security-alert-filter__controls">
        <label class="security-search-control security-alert-search" for="securityEventSearch">
            <x-icon name="search" />
            <input id="securityEventSearch" type="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search alert, user, IP, MAC, or source..." autocomplete="off">
        </label>
        <label class="security-alert-select">
            <span>Risk level</span>
            <select name="severity"><option value="">All severities</option>@foreach(['critical', 'high', 'medium', 'low'] as $severity)<option value="{{ $severity }}" @selected(($filters['severity'] ?? '') === $severity)>{{ ucfirst($severity) }}</option>@endforeach</select>
        </label>
        <label class="security-alert-select">
            <span>Response status</span>
            <select name="status"><option value="">All statuses</option>@foreach(['open', 'reviewed', 'resolved'] as $status)<option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ ucfirst($status) }}</option>@endforeach</select>
        </label>
        <span class="live-filter-status security-alert-filter__status" data-live-filter-status role="status" aria-live="polite"></span>
    </div>
</form>

<div class="card card--spaced" data-live-results>
    <div class="card-header">
        <span class="card-title">
            <x-icon name="shield" />
            Manage Anomaly Detections
        </span>
        @if($eventCounts['open'] > 0)
        <span class="status status-inactive">{{ $eventCounts['open'] }} open</span>
        @else
        <span class="status status-active">All reviewed</span>
        @endif
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Detection</th>
                    <th>Risk</th>
                    <th>Origin</th>
                    <th>Response</th>
                    <th>Detected</th>
                    <th class="td-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse($events as $event)
            @php
                $alert = $event->latestAlert;
                $severity = $alert?->severity ?? $event->severity;
                $status = $alert?->alert_status ?? $event->event_status;
                $latestEmail = $alert?->latestNotificationLog;
                $emailNeeded = $alert && in_array($alert->alert_channel, ['email', 'both'], true);
                $emailStatus = $emailNeeded ? ($latestEmail?->send_status ?? 'pending') : 'No email needed';
                $severityClass = in_array($severity, ['high', 'critical'], true) ? 'status-inactive' : 'status-low';
                $statusClass = in_array($status, ['resolved'], true) ? 'status-active' : (in_array($status, ['new', 'open'], true) ? 'status-inactive' : 'status-low');
            @endphp
            <tr class="security-alert-row security-alert-row--{{ $severity }}">
                <td>
                    <a href="{{ $alert ? route('security.events.alerts.show', $alert) : route('security.events.show', $event) }}" class="security-alert-title">{{ $alert?->alert_title ?? str_replace('_', ' ', ucfirst($event->event_type)) }}</a>
                    <div class="security-alert-summary">{{ \Illuminate\Support\Str::limit($alert?->alert_message ?? $event->description, 105) }}</div>
                    <div class="security-alert-meta"><span>{{ str_replace('_', ' ', ucfirst($event->event_type)) }}</span>@if($event->user)<span>User: {{ $event->user->username }}</span>@endif</div>
                </td>
                <td><span class="status {{ $severityClass }}">{{ strtoupper($severity) }}</span></td>
                <td class="table-meta security-origin-cell">
                    <strong>{{ $event->source_module ? str_replace('_', ' ', ucwords($event->source_module, '_')) : 'Unknown source' }}</strong>
                    @if($event->request_path)
                    <div class="security-endpoint">{{ $event->request_method }} {{ \Illuminate\Support\Str::limit($event->request_path, 42) }}</div>
                    @endif
                    <div class="security-address">IP: {{ $event->ip_address ?: 'Not captured' }}</div>
                </td>
                <td class="security-response-cell">
                    <span class="status {{ $statusClass }}">{{ ucfirst($status) }}</span>
                    @if($alert?->acknowledgedBy && $status === 'acknowledged')
                    <div class="text-muted td-muted--sm mt-1">By {{ $alert->acknowledgedBy->username }}</div>
                    @endif
                    @if($alert?->resolvedBy && $status === 'resolved')
                    <div class="text-muted td-muted--sm mt-1">By {{ $alert->resolvedBy->username }}</div>
                    @endif
                    <div class="security-email-state">Email: <span>{{ ucfirst($emailStatus) }}</span></div>
                </td>
                <td class="td-muted td-nowrap"><strong>{{ $event->detected_at?->diffForHumans() ?? '-' }}</strong><div class="security-exact-time">{{ $event->detected_at?->format('M d, Y h:i A') ?? '' }}</div></td>
                <td>
                    <div class="security-row-actions security-alert-actions" role="group" aria-label="Actions for {{ $alert?->alert_title ?? str_replace('_', ' ', ucfirst($event->event_type)) }}">
                        <a href="{{ $alert ? route('security.events.alerts.show', $alert) : route('security.events.show', $event) }}" class="btn btn-secondary btn-sm security-view-action" aria-label="View details for {{ $alert?->alert_title ?? str_replace('_', ' ', ucfirst($event->event_type)) }}">
                            <x-icon name="eye" /> View details
                        </a>

                        @if(($alert && $status === 'new') || (!$alert && $status === 'open'))
                        <form method="POST" action="{{ $alert ? route('security.events.alerts.acknowledge', $alert) : route('security.events.update', $event) }}" class="form-stack">
                            @csrf @method('PATCH')
                            @unless($alert)
                            <input type="hidden" name="event_status" value="reviewed">
                            @endunless
                            <button class="btn btn-secondary btn-sm w-full" type="submit"><x-icon name="audit" /> Acknowledge</button>
                        </form>
                        @endif

                        @if(($alert && $status === 'acknowledged') || (!$alert && $status === 'reviewed'))
                        <form method="POST" action="{{ $alert ? route('security.events.alerts.resolve', $alert) : route('security.events.update', $event) }}" class="form-stack">
                            @csrf @method('PATCH')
                            @unless($alert)
                            <input type="hidden" name="event_status" value="resolved">
                            @endunless
                            <button class="btn btn-success btn-sm w-full" type="submit"><x-icon name="rules" /> Resolve</button>
                        </form>
                        @endif

                        @if(($event->ip_address || $event->mac_address) && $status !== 'resolved')
                        <form method="POST" action="{{ route('security.events.block-device', $event) }}" class="form-stack security-alert-actions__danger"
                            data-confirm
                            data-confirm-title="Block Device"
                            data-confirm-message="Block this IP/MAC address? Future access from this entity may be denied."
                            data-confirm-button="Block Device">
                            @csrf
                            <button class="btn btn-danger btn-sm w-full" type="submit"><x-icon name="blocklist" /> Block device</button>
                        </form>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <x-empty-state colspan="6" title="No anomalies detected" message="Unusual transactions and activities will appear here when detected." icon="alerts" />
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="view-blades-footer">
        <div class="pagination-summary">
            Showing {{ $events->firstItem() ?? 0 }}-{{ $events->lastItem() ?? 0 }} of {{ $events->total() }} alerts or events
        </div>

        @if($events->hasPages())
        @php
            $currentPage = $events->currentPage();
            $lastPage = $events->lastPage();
            $pages = collect([1, $currentPage - 1, $currentPage, $currentPage + 1, $lastPage])
                ->filter(fn ($page) => $page >= 1 && $page <= $lastPage)
                ->unique()
                ->sort()
                ->values();
            $previousPage = null;
        @endphp
        <nav class="rules-pagination" aria-label="Anomaly alerts pagination">
            @if($events->onFirstPage())
                <span class="rules-page is-disabled" aria-disabled="true">Previous</span>
            @else
                <a class="rules-page" href="{{ $events->previousPageUrl() }}" rel="prev">Previous</a>
            @endif

            @foreach($pages as $page)
                @if($previousPage && $page > $previousPage + 1)
                    <span class="rules-page rules-page--gap" aria-hidden="true">...</span>
                @endif

                @if($page === $currentPage)
                    <span class="rules-page is-active" aria-current="page">{{ $page }}</span>
                @else
                    <a class="rules-page" href="{{ $events->url($page) }}">{{ $page }}</a>
                @endif

                @php $previousPage = $page; @endphp
            @endforeach

            @if($events->hasMorePages())
                <a class="rules-page" href="{{ $events->nextPageUrl() }}" rel="next">Next</a>
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
