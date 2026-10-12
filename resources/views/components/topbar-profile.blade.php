@props(['user'])
@php
    $initials = collect(preg_split('/\s+/', trim($user->name ?: $user->username)) ?: [])
        ->filter()->take(2)->map(fn ($part) => strtoupper(substr($part, 0, 1)))->implode('');
@endphp
<details class="topbar-profile-menu" data-topbar-menu>
    <summary aria-label="Open account menu for {{ $user->name ?: $user->username }}">
        <span class="topbar-profile-avatar" aria-hidden="true">{{ $initials ?: strtoupper(substr($user->username, 0, 2)) }}</span>
    </summary>
    <div class="topbar-profile-panel">
        <header><span class="topbar-profile-avatar" aria-hidden="true">{{ $initials }}</span><div><strong>{{ $user->name ?: $user->username }}</strong><small>{{ $user->username }} &middot; {{ $user->roleLabel() }}</small></div></header>
        @if($user->isAdmin())
        <a href="{{ route('users.show', $user) }}"><i class="bi bi-person" aria-hidden="true"></i><span>My Profile</span></a>
        @endif
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"><x-icon name="logout" /><span>Sign Out</span></button>
        </form>
    </div>
</details>
