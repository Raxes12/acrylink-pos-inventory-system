<?php echo $__env->make('errors._status', [
    'code' => 404,
    'title' => 'Page Not Found',
    'heading' => 'Page Not Found',
    'message' => 'The page you requested may have moved, been removed, or never existed.',
    'actionUrl' => auth()->user()?->defaultLandingRoute() ?? route('login'),
    'actionLabel' => auth()->check() ? 'Return to Home' : 'Return to Login',
    'tone' => 'warning',
], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php /**PATH C:\xampp\htdocs\acrylink_newbie\resources\views/errors/404.blade.php ENDPATH**/ ?>