@props(['page' => 'Dashboard', 'title' => null])
@php
    $routeName = request()->route()?->getName() ?? '';
    $section = match (true) {
        $routeName === 'dashboard' => 'Overview',
        str_starts_with($routeName, 'inventory.'), str_starts_with($routeName, 'stock-receipts.') => 'Inventory / Stock Control',
        str_starts_with($routeName, 'categories.'), str_starts_with($routeName, 'suppliers.') => 'Inventory',
        str_starts_with($routeName, 'pos.'), str_starts_with($routeName, 'dashboard.my-transactions') => 'Sales & Transactions',
        str_starts_with($routeName, 'accounts-receivable.'), str_starts_with($routeName, 'customers.') => 'Customers & Credit',
        str_starts_with($routeName, 'reports.') => 'Reports & Analytics',
        str_starts_with($routeName, 'security.') => 'Security & Monitoring',
        str_starts_with($routeName, 'users.'), str_starts_with($routeName, 'audit.') => 'Administration',
        default => auth()->user()?->roleLabel().' Workspace',
    };
    $segments = collect(preg_split('/\s*\/\s*/', trim($page)) ?: [])->filter()->values();
    $currentPage = trim($title ?: ($segments->last() ?: $page));
    $detailSegments = $segments->count() > 1
        ? $segments->reject(fn ($segment) => strcasecmp($segment, $currentPage) === 0)->values()
        : collect();
@endphp
<div class="topbar-context">
    <nav class="topbar-breadcrumb" aria-label="Current location">
        <span>{{ $section }}</span>
        @foreach($detailSegments->take(2) as $segment)
            <i class="bi bi-chevron-right" aria-hidden="true"></i><span>{{ $segment }}</span>
        @endforeach
    </nav>
    <span class="page-label">{{ $currentPage }}</span>
</div>
