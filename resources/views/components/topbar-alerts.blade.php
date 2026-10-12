@props(['stockAlerts', 'securityAlerts' => 0, 'canViewStock' => false, 'canViewSecurity' => false])
@php
    $stockCount = $canViewStock ? (int) ($stockAlerts['total'] ?? 0) : 0;
    $securityCount = $canViewSecurity ? (int) $securityAlerts : 0;
    $total = $stockCount + $securityCount;
@endphp
@if($total > 0)
<details class="topbar-alert-menu" data-topbar-menu>
    <summary class="topbar-alert-trigger" aria-label="Open notifications: {{ $total }} item{{ $total === 1 ? '' : 's' }} requiring attention">
        <span class="topbar-alert-icon"><x-icon name="alerts" /></span>
        <strong class="topbar-alert-count" aria-hidden="true">{{ $total > 999 ? '999+' : $total }}</strong>
    </summary>
    <div class="topbar-alert-panel">
        <header class="topbar-menu-heading">
            <div><strong>Needs Attention</strong><small>Items requiring administrator review</small></div>
            <span>{{ number_format($total) }}</span>
        </header>
        <div class="topbar-alert-list" aria-label="Notification categories">
            @if($stockCount > 0)
            <a class="topbar-alert-row topbar-alert-row--warning" href="{{ route('inventory.stock-overview', ['stock_level' => 'attention']) }}">
                <span class="topbar-row-icon"><x-icon name="alerts" /></span>
                <span class="topbar-row-copy">
                    <span><b>Stock Alerts</b><strong>{{ number_format($stockCount) }}</strong></span>
                    <small>{{ number_format((int) ($stockAlerts['low'] ?? 0)) }} low &middot; {{ number_format((int) ($stockAlerts['critical'] ?? 0)) }} critical &middot; {{ number_format((int) ($stockAlerts['out'] ?? 0)) }} out of stock</small>
                </span>
                <i class="bi bi-chevron-right" aria-hidden="true"></i>
            </a>
            @endif
            @if($securityCount > 0)
            <a class="topbar-alert-row topbar-alert-row--danger" href="{{ route('security.events') }}">
                <span class="topbar-row-icon"><x-icon name="shield" /></span>
                <span class="topbar-row-copy">
                    <span><b>Anomaly Alerts</b><strong>{{ number_format($securityCount) }}</strong></span>
                    <small>Unusual system or staff activity requiring review</small>
                </span>
                <i class="bi bi-chevron-right" aria-hidden="true"></i>
            </a>
            @endif
        </div>
    </div>
</details>
@endif
