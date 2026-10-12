@include('errors._status', [
    'code' => 500,
    'title' => 'Service Error',
    'heading' => 'We Could Not Complete That Request',
    'message' => 'An unexpected error occurred. Your data has not been intentionally changed. Please try again later.',
    'actionUrl' => auth()->user()?->defaultLandingRoute() ?? route('login'),
    'actionLabel' => auth()->check() ? 'Return to Home' : 'Return to Login',
    'tone' => 'danger',
])
