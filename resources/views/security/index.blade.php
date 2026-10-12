@extends('layouts.app')
@section('title', 'Security Center')
@section('page-label', 'Security Center')

@section('content')
<x-security-nav />
<x-page-header title="Security Center" subtitle="Monitor security posture and open the appropriate operational workspace." />

<div class="security-metrics" aria-label="Security summary">
    <a href="{{ route('security.events', ['status' => 'open']) }}" class="security-metric security-metric--danger"><span class="security-metric__icon"><x-icon name="alerts" /></span><span class="security-metric__body"><span class="security-metric__label">New alerts</span><strong>{{ number_format($newAlerts) }}</strong><small>Waiting for review</small></span><span class="security-metric__arrow" aria-hidden="true">›</span></a>
    <a href="{{ route('security.rules') }}" class="security-metric security-metric--success"><span class="security-metric__icon"><x-icon name="shield" /></span><span class="security-metric__body"><span class="security-metric__label">Enabled rules</span><strong>{{ number_format($enabledRules) }}</strong><small>Actively monitoring</small></span><span class="security-metric__arrow" aria-hidden="true">›</span></a>
    <a href="{{ route('security.blocked-entities', ['status' => 'active']) }}" class="security-metric security-metric--warning"><span class="security-metric__icon"><x-icon name="blocklist" /></span><span class="security-metric__body"><span class="security-metric__label">Active blocks</span><strong>{{ number_format($activeBlocks) }}</strong><small>Access restrictions</small></span><span class="security-metric__arrow" aria-hidden="true">›</span></a>
    <a href="{{ route('security.devices', ['status' => 'authorized']) }}" class="security-metric"><span class="security-metric__icon"><x-icon name="devices" /></span><span class="security-metric__body"><span class="security-metric__label">Authorized devices</span><strong>{{ number_format($authorizedDevices) }}</strong><small>Trusted endpoints</small></span><span class="security-metric__arrow" aria-hidden="true">›</span></a>
</div>

<section class="card">
    <div class="card-header"><div><strong>Recent Security Events</strong><div class="text-muted text-sm">Latest activity recorded by the intrusion monitoring system.</div></div><a class="btn btn-secondary btn-sm" href="{{ route('security.events') }}">View all alerts</a></div>
    <div class="table-wrap"><table><thead><tr><th>Detected</th><th>Event</th><th>User</th><th>Severity</th><th>Status</th></tr></thead><tbody>
        @forelse($recentEvents as $event)
        <tr><td class="td-nowrap">{{ $event->detected_at?->format('M d, Y h:i A') }}</td><td><a class="security-alert-title" href="{{ route('security.events.show', $event) }}">{{ str_replace('_', ' ', ucfirst($event->event_type)) }}</a></td><td>{{ $event->user?->name ?? 'System/Guest' }}</td><td><span class="status status-{{ $event->severity === 'critical' ? 'inactive' : 'pending' }}">{{ ucfirst($event->severity) }}</span></td><td><span class="status {{ $event->event_status === 'resolved' ? 'status-active' : ($event->event_status === 'open' ? 'status-inactive' : 'status-low') }}">{{ ucfirst($event->event_status) }}</span></td></tr>
        @empty
        <x-empty-state colspan="5" title="No security events" message="No security activity has been recorded." />
        @endforelse
    </tbody></table></div>
</section>
@endsection

@push('styles')
@vite('resources/css/views/security-pages-1.css')
@endpush
