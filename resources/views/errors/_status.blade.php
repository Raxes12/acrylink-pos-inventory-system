<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $title }} &mdash; Acry-Link</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('/logo/Acry-Link_Logo.ico') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    @vite('resources/css/views/errors-403-1.css')
</head>
<body>
<main class="error-shell" id="main-content">
    <section class="error-card" aria-labelledby="error-title">
        <div class="error-icon error-icon--{{ $tone ?? 'danger' }}" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="9"/><path d="M12 8v4"/><path d="M12 16h.01"/>
            </svg>
        </div>
        <div class="error-code">{{ $code }}</div>
        <h1 class="error-title" id="error-title">{{ $heading }}</h1>
        <p class="error-message">{{ $message }}</p>
        <a href="{{ $actionUrl }}" class="error-btn">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M19 12H5"/><path d="m12 19-7-7 7-7"/>
            </svg>
            {{ $actionLabel }}
        </a>
    </section>
</main>
</body>
</html>
