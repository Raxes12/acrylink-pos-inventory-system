@include('errors._status', [
    'code' => 429,
    'title' => 'Too Many Requests',
    'heading' => 'Please Slow Down',
    'message' => 'Too many requests were received in a short period. Wait a moment, then try again.',
    'actionUrl' => auth()->user()?->defaultLandingRoute() ?? route('login'),
    'actionLabel' => auth()->check() ? 'Return to Home' : 'Return to Login',
    'tone' => 'warning',
])
