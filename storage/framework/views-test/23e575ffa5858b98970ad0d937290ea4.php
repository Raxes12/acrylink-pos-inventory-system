<?php echo $__env->make('errors._status', [
    'code' => 500,
    'title' => 'Service Error',
    'heading' => 'We Could Not Complete That Request',
    'message' => 'An unexpected error occurred. Your data has not been intentionally changed. Please try again later.',
    'actionUrl' => auth()->user()?->defaultLandingRoute() ?? route('login'),
    'actionLabel' => auth()->check() ? 'Return to Home' : 'Return to Login',
    'tone' => 'danger',
], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php /**PATH C:\xampp\htdocs\acrylink_newbie\resources\views\errors\500.blade.php ENDPATH**/ ?>