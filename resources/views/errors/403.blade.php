<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Access Denied &mdash; Acry-Link</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('/logo/Acry-Link_Logo.ico') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    @vite('resources/css/views/errors-403-1.css')
</head>
<body>
@php
    $errorMessage = $exception->getMessage() ?: 'You do not have permission to view this page.';
    $requiresLoginRecovery = str_contains(strtolower($errorMessage), 'device')
        || str_contains(strtolower($errorMessage), 'blocked by security policy');
    $user = auth()->user();
    $fallbackUrl = $user?->defaultLandingRoute() ?? route('login');
    $previousUrl = url()->previous();
    $previousHost = parse_url($previousUrl, PHP_URL_HOST);
    $currentHost = request()->getHost();
    $canReturnToPrevious = $user
        && !$requiresLoginRecovery
        && $previousUrl !== request()->fullUrl()
        && $previousUrl !== route('login')
        && (!$previousHost || $previousHost === $currentHost);
    $returnUrl = $canReturnToPrevious ? $previousUrl : $fallbackUrl;
@endphp
<main class="error-shell">
    <div class="error-card">
        <div class="error-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                <path d="M12 8v4"/>
                <path d="M12 16h.01"/>
            </svg>
        </div>
        <div class="error-code">403</div>
        <h1 class="error-title">Access Restricted</h1>
        <p class="error-message">{{ $errorMessage }}</p>
        @if($user && !$requiresLoginRecovery)
        <a href="{{ $returnUrl }}" class="error-btn">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M19 12H5"/><path d="m12 19-7-7 7-7"/>
            </svg>
            Go Back
        </a>
        @elseif($user)
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="error-btn">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M19 12H5"/>
                    <path d="m12 19-7-7 7-7"/>
                </svg>
                Return to Login
            </button>
        </form>
        @else
        <a href="{{ route('login') }}" class="error-btn">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M19 12H5"/>
                <path d="m12 19-7-7 7-7"/>
            </svg>
            Return to Login
        </a>
        @endif
    </div>
</main>
</body>
</html>
