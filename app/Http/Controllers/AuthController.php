<?php

namespace App\Http\Controllers;

use App\Http\Middleware\ValidateAuthorizedDevice;
use App\Models\AuditLog;
use App\Models\AuthorizedDevice;
use App\Models\MfaChallenge;
use App\Models\SecurityRule;
use App\Models\User;
use App\Models\UserSession;
use App\Services\IntrusionDetectionService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    private const DEFAULT_MAX_LOGIN_ATTEMPTS = 3;

    private const DEFAULT_LOCKOUT_MINUTES = 5;

    private const OTP_TTL_MINUTES = 5;

    private const MAX_OTP_ATTEMPTS = 5;

    public function showLogin()
    {
        return Auth::check() ? redirect()->to(Auth::user()->defaultLandingRoute()) : view('auth.login');
    }

    public function showOtp(Request $request)
    {
        if (! $this->hasPendingOtp($request)) {
            return redirect()->route('login');
        }

        return view('auth.otp', [
            'expiresAt' => $request->session()->get('auth.otp.expires_at'),
        ]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:50',
            'password' => 'required|string|max:255',
        ]);

        $throttleKey = $this->throttleKey($request);
        $ids = app(IntrusionDetectionService::class);
        $failedLoginRule = $ids->ruleFor('failed_login_limit');
        $failedLoginProtectionEnabled = $failedLoginRule !== false;
        $maxLoginAttempts = $failedLoginRule instanceof SecurityRule
            ? max(1, (int) $failedLoginRule->thresholdOr(self::DEFAULT_MAX_LOGIN_ATTEMPTS))
            : self::DEFAULT_MAX_LOGIN_ATTEMPTS;
        $lockoutMinutes = $failedLoginRule instanceof SecurityRule
            ? max(1, $failedLoginRule->windowOr(self::DEFAULT_LOCKOUT_MINUTES))
            : self::DEFAULT_LOCKOUT_MINUTES;
        $lockoutSeconds = $lockoutMinutes * 60;

        if ($failedLoginProtectionEnabled && RateLimiter::tooManyAttempts($throttleKey, $maxLoginAttempts)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $minutes = (int) ceil($seconds / 60);
            $lockedUser = User::where('username', $request->username)->first();

            $ids->recordLoginAttempt(
                $request,
                $request->username,
                $lockedUser,
                'failed',
                'Login lockout after repeated failed attempts'
            );
            AuditLog::record('LOGIN_FAILED', "Locked login attempt for username: {$request->username}", $lockedUser, [
                'affected_table' => 'users',
                'affected_record_id' => $lockedUser?->id,
                'new_values' => [
                    'username' => $request->username,
                    'reason' => 'lockout',
                ],
            ]);

            return back()->withErrors([
                'username' => "Too many login attempts. Try again in {$minutes} minute(s).",
            ])->with('login_attempt_warning', "The configured limit of {$maxLoginAttempts} failed login attempts was reached. Login is temporarily locked for {$minutes} minute(s).")->withInput();
        }

        $user = User::where('username', $request->username)
            ->where('status', 'active')
            ->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            if ($failedLoginProtectionEnabled) {
                RateLimiter::hit($throttleKey, $lockoutSeconds);
            } else {
                RateLimiter::clear($throttleKey);
            }
            $attempts = $failedLoginProtectionEnabled ? RateLimiter::attempts($throttleKey) : 0;
            $remaining = max(0, $maxLoginAttempts - $attempts);
            $ids->recordLoginAttempt(
                $request,
                $request->username,
                $user,
                'failed',
                'Invalid username or password'
            );
            AuditLog::record('LOGIN_FAILED', "Failed login for username: {$request->username}", $user, [
                'affected_table' => 'users',
                'affected_record_id' => $user?->id,
                'new_values' => [
                    'username' => $request->username,
                    'reason' => 'invalid_credentials',
                ],
            ]);

            $warning = $failedLoginProtectionEnabled && $attempts >= max(1, $maxLoginAttempts - 1)
                ? "{$attempts} failed login attempts detected. {$remaining} attempt(s) remaining before temporary lockout."
                : null;

            $response = back()->withErrors(['username' => 'Invalid username or password.'])->withInput();

            return $warning ? $response->with('login_attempt_warning', $warning) : $response;
        }

        RateLimiter::clear($throttleKey);

        if (! $user->mfa_enabled) {
            $this->completeLogin($request, $user, $request->boolean('remember'), 'Login successful');

            return $this->redirectAfterLogin($request, $user);
        }

        if (! $this->startOtpChallenge($request, $user, $request->boolean('remember'))) {
            return back()->withErrors([
                'username' => 'OTP email could not be sent. Please check the mail settings.',
            ])->withInput();
        }

        return redirect()->route('login.otp');
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|digits:6',
        ]);

        if (! $this->hasPendingOtp($request)) {
            return redirect()->route('login')->withErrors(['otp' => 'Your OTP session expired. Please sign in again.']);
        }

        $challenge = $this->currentMfaChallenge($request);
        if (! $challenge) {
            return redirect()->route('login')->withErrors(['otp' => 'Your OTP session expired. Please sign in again.']);
        }

        if (! $this->otpMatches($request->otp, $challenge->otp_hash)) {
            $challenge->increment('attempt_count');
            $challenge->refresh();

            if ($challenge->attempt_count >= self::MAX_OTP_ATTEMPTS) {
                $challenge->update(['status' => MfaChallenge::STATUS_FAILED]);
                $request->session()->forget('auth.otp');

                return redirect()->route('login')->withErrors([
                    'otp' => 'Too many incorrect OTP attempts. Please sign in again.',
                ]);
            }

            $remaining = self::MAX_OTP_ATTEMPTS - $challenge->attempt_count;

            return back()->withErrors([
                'otp' => "Invalid OTP code. {$remaining} attempt(s) remaining.",
            ])->withInput();
        }

        $user = User::whereKey($challenge->user_id)->where('status', 'active')->first();
        if (! $user) {
            $challenge->update(['status' => MfaChallenge::STATUS_FAILED]);
            $request->session()->forget('auth.otp');

            return redirect()->route('login')->withErrors(['username' => 'Account is unavailable.']);
        }

        $challenge->update([
            'status' => MfaChallenge::STATUS_VERIFIED,
            'verified_at' => now(),
        ]);

        $remember = (bool) $request->session()->get('auth.otp.remember', false);
        $request->session()->forget('auth.otp');
        $this->completeLogin($request, $user, $remember, 'Login successful with OTP');

        return $this->redirectAfterLogin($request, $user);
    }

    public function resendOtp(Request $request)
    {
        if (! $this->hasPendingOtp($request, false)) {
            return redirect()->route('login')->withErrors(['otp' => 'Please sign in again to request an OTP.']);
        }

        $challenge = $this->currentMfaChallenge($request, false);
        $user = $challenge
            ? User::whereKey($challenge->user_id)->where('status', 'active')->first()
            : null;

        if (! $user) {
            $request->session()->forget('auth.otp');

            return redirect()->route('login')->withErrors(['username' => 'Account is unavailable.']);
        }

        $throttleKey = 'otp-resend|'.$user->id.'|'.$request->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 3)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return back()->withErrors(['otp' => "Please wait {$seconds} seconds before requesting another OTP."]);
        }

        RateLimiter::hit($throttleKey, 60);
        if (! $this->startOtpChallenge($request, $user, (bool) $request->session()->get('auth.otp.remember', false))) {
            return back()->withErrors(['otp' => 'OTP email could not be sent. Please check the mail settings.']);
        }

        return redirect()->route('login.otp')->with('status', 'A new OTP code has been sent.');
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        AuditLog::record('LOGOUT', 'User logged out', $user, [
            'affected_table' => 'users',
            'affected_record_id' => $user?->id,
        ]);
        UserSession::where('session_id', $request->session()->getId())
            ->where('session_status', 'active')
            ->update([
                'logout_time' => now(),
                'session_status' => 'logged_out',
            ]);

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function throttleKey(Request $request): string
    {
        return Str::lower($request->input('username')).'|'.$request->ip();
    }

    private function startOtpChallenge(Request $request, User $user, bool $remember): bool
    {
        $otp = (string) random_int(100000, 999999);
        $expiresAt = now()->addMinutes($this->otpTtlMinutes());

        MfaChallenge::where('user_id', $user->id)
            ->where('status', MfaChallenge::STATUS_PENDING)
            ->update(['status' => MfaChallenge::STATUS_EXPIRED]);

        $challenge = MfaChallenge::create([
            'user_id' => $user->id,
            'otp_hash' => $this->hashOtp($otp),
            'delivery_method' => MfaChallenge::METHOD_EMAIL,
            'expires_at' => $expiresAt,
            'attempt_count' => 0,
            'status' => MfaChallenge::STATUS_PENDING,
        ]);

        $request->session()->put('auth.otp', [
            'challenge_id' => $challenge->mfa_challenge_id,
            'user_id' => $user->id,
            'remember' => $remember,
            'expires_at' => $expiresAt->toIso8601String(),
        ]);

        if (! $this->mailIsConfigured()) {
            Log::warning('Login OTP email was not scheduled because mail is not configured.', [
                'user_id' => $user->id,
                'username' => $user->username,
            ]);
            $challenge->update(['status' => MfaChallenge::STATUS_FAILED]);
            $request->session()->forget('auth.otp');

            return false;
        }

        $challengeId = $challenge->getKey();
        $userId = $user->getKey();

        defer(function () use ($challengeId, $userId, $otp) {
            $recipient = User::find($userId);

            if (! $recipient || ! $this->deliverOtp($recipient, $otp)) {
                MfaChallenge::whereKey($challengeId)
                    ->where('status', MfaChallenge::STATUS_PENDING)
                    ->update(['status' => MfaChallenge::STATUS_FAILED]);
            }
        }, "login-otp:{$challengeId}", always: true);

        return true;
    }

    private function completeLogin(Request $request, User $user, bool $remember, string $message): void
    {
        $request->session()->regenerate();
        $request->session()->put('auth.login_time', now());

        Auth::login($user, $remember);
        app(ValidateAuthorizedDevice::class)->handle($request, fn () => null);

        $user->update(['last_login' => now()]);
        app(IntrusionDetectionService::class)->recordLoginAttempt(request(), $user->username, $user, 'success');
        AuditLog::record('LOGIN', $message, $user, [
            'affected_table' => 'users',
            'affected_record_id' => $user->id,
            'new_values' => [
                'last_login' => $user->last_login?->toDateTimeString(),
                'mfa_enabled' => $user->mfa_enabled,
            ],
        ]);
    }

    private function loginDestination(User $user): string
    {
        if (
            config('app.device_whitelist_mode', true)
            && $user->isAdmin()
            && ! AuthorizedDevice::where('status', 'authorized')->exists()
        ) {
            return route('security.devices');
        }

        return $user->defaultLandingRoute();
    }

    private function redirectAfterLogin(Request $request, User $user)
    {
        // A guest may have been sent to login from a page their role cannot use.
        // Never let that stale intended URL override the role-safe landing page.
        $request->session()->forget('url.intended');

        return redirect()->to($this->loginDestination($user));
    }

    private function deliverOtp(User $user, string $otp): bool
    {
        $message = "Your Acry-Link login OTP is {$otp}. It expires in ".$this->otpTtlMinutes().' minutes.';

        try {
            Mail::raw($message, function ($mail) use ($user) {
                $mail->to($user->email)->subject('Acry-Link Login OTP');
            });
            Log::info('Login OTP email sent', [
                'user_id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
            ]);

            return true;
        } catch (\Throwable $e) {
            Log::warning('Unable to send login OTP email', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    private function mailIsConfigured(): bool
    {
        $mailer = config('mail.default');

        if (! $mailer) {
            return false;
        }

        if ($mailer === 'smtp') {
            return filled(config('mail.mailers.smtp.host'))
                && filled(config('mail.mailers.smtp.username'))
                && filled(config('mail.mailers.smtp.password'))
                && filled(config('mail.from.address'));
        }

        return filled(config('mail.from.address'));
    }

    private function otpTtlMinutes(): int
    {
        return max(1, (int) config('auth.mfa.otp_ttl_minutes', self::OTP_TTL_MINUTES));
    }

    private function hashOtp(string $otp): string
    {
        return 'hmac-sha256:'.hash_hmac('sha256', $otp, (string) config('app.key'));
    }

    private function otpMatches(string $otp, string $storedHash): bool
    {
        if (str_starts_with($storedHash, 'hmac-sha256:')) {
            return hash_equals($storedHash, $this->hashOtp($otp));
        }

        // Keep challenges issued before the HMAC optimization valid until they expire.
        return Hash::check($otp, $storedHash);
    }

    private function hasPendingOtp(Request $request, bool $requireUnexpired = true): bool
    {
        return (bool) $this->currentMfaChallenge($request, $requireUnexpired);
    }

    private function currentMfaChallenge(Request $request, bool $requireUnexpired = true): ?MfaChallenge
    {
        $sessionChallenge = $request->session()->get('auth.otp');

        if (! $sessionChallenge || empty($sessionChallenge['challenge_id']) || empty($sessionChallenge['user_id'])) {
            return null;
        }

        $challenge = MfaChallenge::whereKey($sessionChallenge['challenge_id'])
            ->where('user_id', $sessionChallenge['user_id'])
            ->where('status', MfaChallenge::STATUS_PENDING)
            ->first();

        if (! $challenge) {
            $request->session()->forget('auth.otp');

            return null;
        }

        // Resend must still be able to resolve the account from an expired
        // challenge. startOtpChallenge() will expire this record as it issues
        // the replacement. Verification and page loads remain strict.
        if ($requireUnexpired && $challenge->isExpired()) {
            $challenge->update(['status' => MfaChallenge::STATUS_EXPIRED]);
            $request->session()->forget('auth.otp');

            return null;
        }

        if ($requireUnexpired && ! empty($sessionChallenge['expires_at']) && now()->greaterThanOrEqualTo(Carbon::parse($sessionChallenge['expires_at']))) {
            $challenge->update(['status' => MfaChallenge::STATUS_EXPIRED]);
            $request->session()->forget('auth.otp');

            return null;
        }

        return $challenge;
    }
}
