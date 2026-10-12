<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Login - Acry-Link</title>
    <link rel="icon" type="image/x-icon" href="<?php echo e(asset('/logo/Acry-Link_Logo.ico')); ?>">
    <?php echo app('Illuminate\Foundation\Vite')('resources/css/views/auth-login-1.css'); ?>
</head>
<body>
<a href="#auth-form" class="auth-skip-link">Skip to sign-in form</a>
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
            <span class="security-mark" aria-label="Secure staff access" title="Secure staff access">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                    <path d="m9 12 2 2 4-4"></path>
                </svg>
            </span>
            </header>

            <div class="auth-intro">
                <div class="form-eyebrow">Staff access</div>
                <h1 class="form-title">Sign in to your account</h1>
                <p class="form-note">Use your assigned username and password to continue.</p>
            </div>

            <?php if($errors->any()): ?>
            <div class="error is-animated" role="alert"><?php echo e($errors->first()); ?></div>
            <?php endif; ?>

            <?php if(session('login_attempt_warning')): ?>
            <div class="attempt-warning" role="alert">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                    <path d="M10.3 4.3 2.8 18a2 2 0 0 0 1.7 3h15a2 2 0 0 0 1.7-3L13.7 4.3a2 2 0 0 0-3.4 0z"></path>
                    <path d="M12 9v4"></path>
                    <path d="M12 17h.01"></path>
                </svg>
                <span><?php echo e(session('login_attempt_warning')); ?></span>
            </div>
            <?php endif; ?>

            <form 
                method="POST" 
                action="<?php echo e(route('login.post')); ?>" 
                data-login-form
                data-login-failed="<?php echo e($errors->any() ? 'true' : 'false'); ?>">
                <?php echo csrf_field(); ?>
                <div class="form-group">
                    <label class="field-label" for="username">Username</label>
                    <div class="input-wrap">
                        <span class="input-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M20 21a8 8 0 0 0-16 0"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                        </span>
                        <input id="username" type="text" name="username" value="<?php echo e(old('username')); ?>" placeholder="Enter your username" autocomplete="username" autofocus required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="field-label" for="password">Password</label>
                    <div class="input-wrap has-toggle">
                        <span class="input-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <rect x="5" y="11" width="14" height="10" rx="2"></rect>
                                <path d="M8 11V7a4 4 0 0 1 8 0v4"></path>
                            </svg>
                        </span>
                        <input id="password" type="password" name="password" placeholder="Enter your password" autocomplete="current-password" required>
                        <button type="button" class="password-toggle" aria-label="Show password" aria-pressed="false" data-password-toggle>
                            <svg class="eye" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                                <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                            <svg class="eye-off" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                                <path d="M3 3l18 18"></path>
                                <path d="M10.6 10.6a2 2 0 0 0 2.8 2.8"></path>
                                <path d="M9.9 4.2A9.5 9.5 0 0 1 12 4c6.5 0 10 8 10 8a18.5 18.5 0 0 1-2.8 4.1"></path>
                                <path d="M6.6 6.6C3.7 8.6 2 12 2 12s3.5 8 10 8a9.8 9.8 0 0 0 4.6-1.1"></path>
                            </svg>
                        </button>
                    </div>
                    <span class="caps-lock-hint" data-caps-lock role="status" aria-live="polite" hidden>Caps Lock is on.</span>
                </div>
                <div class="check-row">
                    <input type="checkbox" name="remember" id="remember">
                    <label for="remember">Keep me signed in on this device</label>
                </div>
                <button type="submit" class="btn" data-login-submit>
                    <span class="btn-spinner" aria-hidden="true"></span>
                    <svg class="btn-check" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="m5 12 4 4L19 6"></path>
                    </svg>
                    <span data-login-submit-label>Sign in</span>
                </button>
            </form>
            <footer class="auth-footer">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                    <rect x="5" y="11" width="14" height="10" rx="2"></rect>
                    <path d="M8 11V7a4 4 0 0 1 8 0v4"></path>
                </svg>
                Secure access for authorized staff
            </footer>
        </div>
    </section>
</main>
<?php echo app('Illuminate\Foundation\Vite')('resources/js/views/auth-login-1.js'); ?>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\acrylink_newbie\resources\views\auth\login.blade.php ENDPATH**/ ?>