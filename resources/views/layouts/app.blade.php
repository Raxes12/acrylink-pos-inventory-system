<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Acry-Link') }} &ndash; @yield('title', 'Dashboard')</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('/logo/Acry-Link_Logo.ico') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Space+Grotesk:wght@600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <meta name="page-styles-start">
    @stack('styles')
</head>
<body>
<a href="#main-content" class="skip-link">Skip to main content</a>
<div id="routeProgress" class="route-progress" role="progressbar" aria-label="Loading page" hidden></div>

<aside id="appSidebar" class="sidebar" aria-label="Main navigation">
    @php
        $user = auth()->user();
    @endphp

    <div class="sidebar-brand">
        <div class="brand-mark">
            <img src="{{ asset('/logo/Acry-Link.ico') }}" alt="Acry-Link">
        </div>
        <div class="brand-copy">
            <div class="brand-name">Acry-Link</div>
            <div class="brand-tagline">POS &amp; Inventory System</div>
        </div>
        <button type="button" id="mobileSidebarClose" class="mobile-sidebar-close" aria-label="Close navigation menu">
            <x-icon name="close" />
        </button>
        <button type="button" id="desktopSidebarToggle" class="desktop-sidebar-toggle" aria-label="Collapse sidebar" aria-pressed="false" title="Collapse sidebar">
            <span class="desktop-sidebar-toggle-icon" aria-hidden="true"></span>
        </button>
    </div>

    <div class="sidebar-user">
        <div class="user-avatar">{{ strtoupper(substr(auth()->user()->name,0,2)) }}</div>
        <div>
            <div class="user-name">{{ auth()->user()->name }}</div>
            <div class="user-role">{{ $user->roleLabel() }}</div>
        </div>
    </div>

    <nav class="sidebar-nav">
        @if($user->isAdmin())
        <div class="nav-section nav-section-static"><div class="nav-section-label">Overview</div><a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"><span class="nav-icon-wrap"><x-icon name="dashboard" /></span><span class="nav-text">Dashboard</span></a></div>
        @endif

        @if($user->canAccessSales())
        @php $salesActive = request()->routeIs('pos.*', 'accounts-receivable.*'); @endphp
        <div class="nav-section nav-group {{ $salesActive ? 'is-route-active' : '' }}" data-nav-group="sales">
            <button type="button" class="nav-section-toggle" aria-expanded="true" aria-controls="navGroupSales"><span>Sales</span><i class="bi bi-chevron-down" aria-hidden="true"></i></button>
            <div class="nav-group-items" id="navGroupSales">
                <a href="{{ route('pos.index') }}" class="nav-link {{ request()->routeIs('pos.index') ? 'active' : '' }}"><span class="nav-icon-wrap"><x-icon name="pos" /></span><span class="nav-text">POS Terminal</span></a>
                <a href="{{ route('pos.transactions') }}" class="nav-link {{ request()->routeIs('pos.transactions', 'pos.receipt', 'pos.refund.*') ? 'active' : '' }}"><span class="nav-icon-wrap"><x-icon name="transactions" /></span><span class="nav-text">Transactions</span></a>
                <a href="{{ route('accounts-receivable.index') }}" class="nav-link {{ request()->routeIs('accounts-receivable.*', 'pos.credit-payments.*') ? 'active' : '' }}"><span class="nav-icon-wrap"><x-icon name="sales-report" /></span><span class="nav-text">Accounts Receivable</span></a>
            </div>
        </div>
        @endif

        @if($user->canAccessInventory())
        @php $inventoryActive = request()->routeIs('inventory.*', 'stock-receipts.*'); @endphp
        <div class="nav-section nav-group {{ $inventoryActive ? 'is-route-active' : '' }}" data-nav-group="inventory">
            <button type="button" class="nav-section-toggle" aria-expanded="true" aria-controls="navGroupInventory"><span>Inventory</span><i class="bi bi-chevron-down" aria-hidden="true"></i></button>
            <div class="nav-group-items" id="navGroupInventory">
                <a href="{{ route('inventory.stock-overview') }}" class="nav-link {{ request()->routeIs('inventory.stock-overview') ? 'active' : '' }}"><span class="nav-icon-wrap"><x-icon name="stock" /></span><span class="nav-text">Stock Overview</span>@if($layoutStockAlerts['total'] > 0)<span class="nav-badge">{{ $layoutStockAlerts['total'] }}</span>@endif</a>
                <a href="{{ route('inventory.index') }}" class="nav-link {{ request()->routeIs('inventory.index', 'inventory.create', 'inventory.show', 'inventory.edit') ? 'active' : '' }}"><span class="nav-icon-wrap"><x-icon name="products" /></span><span class="nav-text">Products</span></a>
                @if($user->canEdit())<a href="{{ route('stock-receipts.index') }}" class="nav-link {{ request()->routeIs('stock-receipts.*') ? 'active' : '' }}"><span class="nav-icon-wrap"><x-icon name="receipts" /></span><span class="nav-text">Stock Receipts</span></a>@endif
                <a href="{{ route('inventory.movements') }}" class="nav-link {{ request()->routeIs('inventory.movements') ? 'active' : '' }}"><span class="nav-icon-wrap"><x-icon name="transactions" /></span><span class="nav-text">Stock Movements</span></a>
            </div>
        </div>
        @endif

        @if($user->isAdmin() || $user->canEdit())
        @php $contactsActive = request()->routeIs('customers.*', 'suppliers.*'); @endphp
        <div class="nav-section nav-group {{ $contactsActive ? 'is-route-active' : '' }}" data-nav-group="contacts">
            <button type="button" class="nav-section-toggle" aria-expanded="{{ $contactsActive ? 'true' : 'false' }}" aria-controls="navGroupContacts"><span>Contacts</span><i class="bi bi-chevron-down" aria-hidden="true"></i></button>
            <div class="nav-group-items" id="navGroupContacts" @if(!$contactsActive) hidden @endif>
                @if($user->isAdmin())<a href="{{ route('customers.index') }}" class="nav-link {{ request()->routeIs('customers.*') ? 'active' : '' }}"><span class="nav-icon-wrap"><x-icon name="users" /></span><span class="nav-text">Customers</span></a>@endif
                @if($user->canEdit())<a href="{{ route('suppliers.index') }}" class="nav-link {{ request()->routeIs('suppliers.*') ? 'active' : '' }}"><span class="nav-icon-wrap"><x-icon name="suppliers" /></span><span class="nav-text">Suppliers</span></a>@endif
            </div>
        </div>
        @endif

        @if($user->isAdmin())
        @php $reportsActive = request()->routeIs('reports.*'); $adminActive = request()->routeIs('users.*', 'audit.*', 'security.*', 'settings.*', 'categories.*'); @endphp
        <div class="nav-section nav-group {{ $reportsActive ? 'is-route-active' : '' }}" data-nav-group="reports"><button type="button" class="nav-section-toggle" aria-expanded="{{ $reportsActive ? 'true' : 'false' }}" aria-controls="navGroupReports"><span>Reports</span><i class="bi bi-chevron-down" aria-hidden="true"></i></button><div class="nav-group-items" id="navGroupReports" @if(!$reportsActive) hidden @endif>
            <a href="{{ route('reports.index') }}" class="nav-link {{ request()->routeIs('reports.index', 'reports.export*') ? 'active' : '' }}"><span class="nav-icon-wrap"><x-icon name="sales-report" /></span><span class="nav-text">Sales</span></a>
            <a href="{{ route('reports.inventory') }}" class="nav-link {{ request()->routeIs('reports.inventory*') ? 'active' : '' }}"><span class="nav-icon-wrap"><x-icon name="inventory-report" /></span><span class="nav-text">Inventory</span></a>
            <a href="{{ route('reports.accounts-receivable') }}" class="nav-link {{ request()->routeIs('reports.accounts-receivable*') ? 'active' : '' }}"><span class="nav-icon-wrap"><x-icon name="transactions" /></span><span class="nav-text">Receivables</span></a>
        </div></div>
        <div class="nav-section nav-group {{ $adminActive ? 'is-route-active' : '' }}" data-nav-group="administration"><button type="button" class="nav-section-toggle" aria-expanded="{{ $adminActive ? 'true' : 'false' }}" aria-controls="navGroupAdministration"><span>Administration</span><i class="bi bi-chevron-down" aria-hidden="true"></i></button><div class="nav-group-items" id="navGroupAdministration" @if(!$adminActive) hidden @endif>
            <a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}"><span class="nav-icon-wrap"><x-icon name="users" /></span><span class="nav-text">Users</span></a>
            <a href="{{ route('audit.index') }}" class="nav-link {{ request()->routeIs('audit.*') ? 'active' : '' }}"><span class="nav-icon-wrap"><x-icon name="audit" /></span><span class="nav-text">Audit Logs</span></a>
            <a href="{{ route('security.index') }}" class="nav-link {{ request()->routeIs('security.*') ? 'active' : '' }}"><span class="nav-icon-wrap"><x-icon name="shield" /></span><span class="nav-text">Security</span>@if($layoutSecurityAlerts > 0)<span class="nav-badge">{{ $layoutSecurityAlerts }}</span>@endif</a>
            <a href="{{ route('settings.index') }}" class="nav-link {{ request()->routeIs('settings.*', 'categories.*') ? 'active' : '' }}"><span class="nav-icon-wrap"><x-icon name="categories" /></span><span class="nav-text">System Settings</span></a>
        </div></div>
        @endif
    </nav>

    <div class="sidebar-footer">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn-sign-out">
                <x-icon name="logout" />
                <span class="sidebar-footer-label">Sign Out</span>
            </button>
        </form>
    </div>
</aside>

<div id="sidebarScrim" class="sidebar-scrim" hidden></div>

<div class="main">
    <header class="topbar">
        <div class="topbar-left">
            <button type="button" id="mobileMenuToggle" class="mobile-menu-toggle" aria-label="Open navigation menu" aria-controls="appSidebar" aria-expanded="false">
                <x-icon name="menu" class="mobile-menu-icon mobile-menu-icon--open" />
                <x-icon name="close" class="mobile-menu-icon mobile-menu-icon--close" />
            </button>
            <x-topbar-context :page="trim($__env->yieldContent('page-label', 'Dashboard'))" :title="trim($__env->yieldContent('title', 'Dashboard'))" />
        </div>
        <div class="topbar-right">
            <x-topbar-alerts :stock-alerts="$layoutStockAlerts" :security-alerts="$layoutSecurityAlerts" :can-view-stock="$user->canAccessInventory()" :can-view-security="$user->isAdmin()" />
            <span id="liveClock" class="topbar-pill pill-clock" data-server-time="{{ now()->toIso8601String() }}" title="Current local date and time"><i class="bi bi-clock" aria-hidden="true"></i><span data-live-clock-date>{{ now()->format('M d, Y') }}</span><span class="topbar-clock-divider" aria-hidden="true"></span><span data-live-clock-time>{{ now()->format('h:i A') }}</span></span>
            <button type="button" id="themeToggle" class="btn-theme" aria-label="Switch color theme" aria-pressed="false">
                <span class="theme-icon theme-icon--moon"><x-icon name="moon" /></span>
                <span class="theme-icon theme-icon--sun"><x-icon name="sun" /></span>
                <span class="visually-hidden" data-theme-toggle-label>Dark Mode</span>
            </button>
            <x-topbar-profile :user="$user" />
        </div>
    </header>

    <main class="content" id="main-content" tabindex="-1">
        @if(session('success'))
        <div hidden data-flash-toast data-toast-type="success">{{ session('success') }}</div>
        @endif
        @if(session('warning'))
        <div hidden data-flash-toast data-toast-type="warning">{{ session('warning') }}</div>
        @endif
        @if(session('error'))
        <div hidden data-flash-toast data-toast-type="error">{{ session('error') }}</div>
        @endif
        @if(session('info') || session('status'))
        <div hidden data-flash-toast data-toast-type="info">{{ session('info') ?: session('status') }}</div>
        @endif
        @if($errors->any())
        <div hidden data-flash-toast data-toast-type="error">{{ $errors->first() }}</div>
        <section class="validation-summary" role="alert" aria-labelledby="validationSummaryTitle">
            <div class="validation-summary__icon" aria-hidden="true"><x-icon name="alerts" /></div>
            <div>
                <h2 id="validationSummaryTitle">Please review the highlighted information</h2>
                @if($errors->count() === 1)
                <p>{{ $errors->first() }}</p>
                @else
                <ul>
                    @foreach($errors->all() as $message)
                    <li>{{ $message }}</li>
                    @endforeach
                </ul>
                @endif
            </div>
        </section>
        @endif
        @yield('content')
    </main>
</div>

<div id="toastStack" class="toast-stack" aria-live="polite" aria-relevant="additions"></div>

<div id="confirmActionModal" class="modal-backdrop confirm-backdrop" hidden>
    <div class="modal confirm-modal" role="dialog" aria-modal="true" aria-labelledby="confirmActionTitle" aria-describedby="confirmActionMessage">
        <div class="confirm-icon" aria-hidden="true">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v5"/><path d="M14 11v5"/></svg>
        </div>
        <div class="confirm-content">
            <h2 id="confirmActionTitle">Are you sure?</h2>
            <p id="confirmActionMessage">Are you sure you want to continue?</p>
        </div>
        <div class="confirm-actions">
            <button type="button" class="btn btn-danger" id="confirmActionSubmit">Continue</button>
            <button type="button" class="btn btn-secondary" id="confirmActionCancel">Cancel</button>
        </div>
    </div>
</div>

<div id="page-scripts" hidden>
    @stack('scripts')
</div>
</body>
</html>
