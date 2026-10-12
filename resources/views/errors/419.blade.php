@include('errors._status', [
    'code' => 419,
    'title' => 'Session Expired',
    'heading' => 'Your Session Expired',
    'message' => 'For your security, this page can no longer submit changes. Return to the application and try again.',
    'actionUrl' => auth()->user()?->defaultLandingRoute() ?? route('login'),
    'actionLabel' => auth()->check() ? 'Return to Home' : 'Return to Login',
    'tone' => 'warning',
])
