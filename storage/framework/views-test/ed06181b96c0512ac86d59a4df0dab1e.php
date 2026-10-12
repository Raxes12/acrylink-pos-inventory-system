<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Acry-Link - Security Code</title>
    <link rel="icon" type="image/x-icon" href="<?php echo e(asset('/logo/Acry-Link_Logo.ico')); ?>">
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/views/auth-otp-1.css', 'resources/js/views/auth-otp-1.js']); ?>
</head>
<body>
<a href="#auth-form" class="auth-skip-link">Skip to verification form</a>
<main class="login-shell">
    <section class="form-panel">
        <div class="form-card" id="auth-form" tabindex="-1">
            <header class="auth-header">
            <div class="brand-lockup">
                <div class="brand-mark">
                    <img src="<?php echo e(asset('/logo/Acry-Link_Logo.ico')); ?>" alt="Acry-Link logo">
                </div>
                <div>
                    <div class="brand-name">Acry-Link</div>
                    <div class="brand-sub">POS and Inventory Management System</div>
                </div>
            </div>
            <span class="security-mark" aria-label="Two-step verification" title="Two-step verification">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                    <path d="m9 12 2 2 4-4"></path>
                </svg>
            </span>
            </header>

            <div class="auth-intro">
                <div class="form-eyebrow">Verification</div>
                <h1 class="form-title">Check your email</h1>
                <p class="form-note">Enter the 6-digit security code sent to your registered email address.</p>
            </div>

            <?php if($errors->any()): ?>
            <div class="error is-animated" role="alert"><?php echo e($errors->first()); ?></div>
            <?php endif; ?>

            <?php if(session('status')): ?>
            <div class="status-banner" role="status"><?php echo e(session('status')); ?></div>
            <?php endif; ?>

            <form method="POST" action="<?php echo e(route('login.otp.verify')); ?>" data-otp-form>
                <?php echo csrf_field(); ?>
                <div class="otp-group" data-otp-group>
                    <label class="otp-label" for="otp-digit-0">One-time password</label>
                    <div class="otp-digits" role="group" aria-label="6-digit security code">
                        <?php for($i = 0; $i < 6; $i++): ?>
                        <input
                            id="otp-digit-<?php echo e($i); ?>"
                            class="otp-digit"
                            type="text"
                            inputmode="numeric"
                            pattern="[0-9]"
                            maxlength="1"
                            <?php if($i === 0): ?> autocomplete="one-time-code" <?php else: ?> autocomplete="off" <?php endif; ?>
                            data-otp-digit
                            <?php if($i === 0): ?> autofocus <?php endif; ?>
                            aria-label="Digit <?php echo e($i + 1); ?>"
                        >
                        <?php endfor; ?>
                    </div>
                    <input type="hidden" name="otp" id="otp-hidden" value="<?php echo e(old('otp')); ?>" required>
                </div>

                <div class="otp-meta">
                    <span
                        class="otp-timer"
                        data-otp-timer
                        data-expires-at="<?php echo e($expiresAt); ?>"
                        aria-live="polite"
                    >Code expiration time is loading</span>
                </div>

                <button type="submit" class="btn" data-otp-submit disabled>
                    <span class="btn-spinner" aria-hidden="true"></span>
                    <span data-otp-submit-label>Verify &amp; Sign In</span>
                </button>
            </form>

            <form method="POST" action="<?php echo e(route('login.otp.resend')); ?>" class="resend-form">
                <?php echo csrf_field(); ?>
                <button type="submit" class="link-btn" data-otp-resend disabled>Resend code</button>
            </form>
            <a class="back-link" href="<?php echo e(route('login')); ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                    <path d="M19 12H5"></path>
                    <path d="m12 19-7-7 7-7"></path>
                </svg>
                Back to sign in
            </a>
            <footer class="auth-footer">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                    <path d="m9 12 2 2 4-4"></path>
                </svg>
                Code expires after five minutes
            </footer>
        </div>
    </section>
</main>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\acrylink_newbie\resources\views/auth/otp.blade.php ENDPATH**/ ?>