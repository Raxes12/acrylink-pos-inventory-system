@include('errors._status', [
    'code' => 404,
    'title' => 'Page Not Found',
    'heading' => 'Page Not Found',
    'message' => 'The page you requested may have moved, been removed, or never existed.',
    'actionUrl' => auth()->user()?->defaultLandingRoute() ?? route('login'),
    'actionLabel' => auth()->check() ? 'Return to Home' : 'Return to Login',
    'tone' => 'warning',
])
