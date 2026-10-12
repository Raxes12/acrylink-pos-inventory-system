<?php echo $__env->make('errors._status', [
    'code' => 429,
    'title' => 'Too Many Requests',
    'heading' => 'Please Slow Down',
    'message' => 'Too many requests were received in a short period. Wait a moment, then try again.',
    'actionUrl' => auth()->user()?->defaultLandingRoute() ?? route('login'),
    'actionLabel' => auth()->check() ? 'Return to Home' : 'Return to Login',
    'tone' => 'warning',
], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php /**PATH C:\xampp\htdocs\acrylink_newbie\resources\views\errors\429.blade.php ENDPATH**/ ?>