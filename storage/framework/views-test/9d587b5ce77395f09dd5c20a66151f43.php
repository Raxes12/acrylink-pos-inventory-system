<?php echo $__env->make('errors._status', [
    'code' => 419,
    'title' => 'Session Expired',
    'heading' => 'Your Session Expired',
    'message' => 'For your security, this page can no longer submit changes. Return to the application and try again.',
    'actionUrl' => auth()->user()?->defaultLandingRoute() ?? route('login'),
    'actionLabel' => auth()->check() ? 'Return to Home' : 'Return to Login',
    'tone' => 'warning',
], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php /**PATH C:\xampp\htdocs\acrylink_newbie\resources\views\errors\419.blade.php ENDPATH**/ ?>