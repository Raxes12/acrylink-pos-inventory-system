<?php

namespace App\Http\Middleware;

use App\Models\SecurityRule;
use App\Models\UserSession;
use App\Services\IntrusionDetectionService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class DetectSessionAnomaly
{
    public function handle(Request $request, Closure $next): mixed
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        $this->expireStaleSessions($request);

        $session = UserSession::where('session_id', $request->session()->getId())
            ->where('user_id', $user->id)
            ->where('session_status', 'active')
            ->first();

        if (!$session) {
            return $next($request);
        }

        $blockReason = null;

        // Keep the oldest allowed sessions and treat only newer sessions as excess.
        $activeSessions = UserSession::where('user_id', $user->id)
            ->where('session_status', 'active')
            ->orderBy('login_time')
            ->orderBy('id')
            ->get(['id', 'session_id']);
        $duplicateThreshold = max(2, (int) ceil($this->threshold('duplicate_session', 'duplicate_session_detection')));
        if ($activeSessions->count() >= $duplicateThreshold) {
            $allowedSessionIds = $activeSessions
                ->take($duplicateThreshold - 1)
                ->pluck('session_id');
            $currentSessionIsExcess = !$allowedSessionIds->contains($session->session_id);

            $reason = $this->recordSessionAnomaly(
                'duplicate_session',
                "User {$user->username} has {$activeSessions->count()} active sessions.",
                $request,
                $session,
                $currentSessionIsExcess
            );
            $blockReason ??= $reason;
        }

        // IP mismatch: detects a stable session id being used from a different IP address.
        if ($session->ip_address && $session->ip_address !== $request->ip()) {
            $reason = $this->recordSessionAnomaly(
                'session_ip_mismatch',
                "Session IP changed from {$session->ip_address} to {$request->ip()} for user {$user->username}.",
                $request,
                $session
            );
            $blockReason ??= $reason;
        }

        // User-agent anomaly: detects a stable session id being reused by a different browser/client signature.
        if (Schema::hasColumn('user_sessions', 'user_agent')) {
            $currentUserAgent = (string) $request->userAgent();
            if ($session->user_agent && $session->user_agent !== $currentUserAgent) {
                $reason = $this->recordSessionAnomaly(
                    'user_agent_anomaly',
                    "Session user agent changed for user {$user->username}.",
                    $request,
                    $session
                );
                $blockReason ??= $reason;
            }
        }

        if ($blockReason) {
            $this->expireCurrentSession($request, $session);

            return redirect()->route('login')->withErrors([
                'username' => $blockReason,
            ]);
        }

        return $next($request);
    }

    private function recordSessionAnomaly(
        string $ruleName,
        string $description,
        Request $request,
        UserSession $session,
        bool $mayBlockCurrentSession = true
    ): ?string
    {
        $ids = app(IntrusionDetectionService::class);
        $rule = $this->rule($ruleName);

        if ($rule === false) {
            return null;
        }

        $severity = $rule instanceof SecurityRule
            ? $rule->severity
            : (string) config("security.rules.{$ruleName}.severity", 'medium');
        $action = $rule instanceof SecurityRule
            ? $rule->action
            : (string) config("security.rules.{$ruleName}.action", 'alert');

        if (!$this->hasRecentEvent($ruleName, $request, $session, $rule instanceof SecurityRule ? $rule : null)) {
            $ids->createIntrusionEvent(
                $ruleName,
                $severity,
                $description,
                $request,
                $request->user(),
                $rule instanceof SecurityRule ? $rule : null,
                'session'
            );
        }

        return $action === 'block' && $mayBlockCurrentSession
            ? 'Your session was expired after a security anomaly was detected.'
            : null;
    }

    private function hasRecentEvent(string $type, Request $request, UserSession $session, ?SecurityRule $rule): bool
    {
        $window = $rule?->windowOr(5) ?? (int) config("security.rules.{$type}.time_window_minutes", 5);

        return \App\Models\IntrusionEvent::where('event_type', $type)
            ->where('user_id', $session->user_id)
            ->where('ip_address', $request->ip())
            ->where('detected_at', '>=', now()->subMinutes($window))
            ->exists();
    }

    private function expireStaleSessions(Request $request): void
    {
        $lifetime = max(1, (int) config('session.lifetime', 120));

        UserSession::where('session_status', 'active')
            ->where('session_id', '!=', $request->session()->getId())
            ->where('last_activity', '<', now()->subMinutes($lifetime))
            ->update([
                'logout_time' => now(),
                'session_status' => 'expired',
            ]);
    }

    private function threshold(string $ruleName, ?string $fallbackRuleName = null): float
    {
        $fallback = (float) config("security.rules.{$ruleName}.threshold_value", 1);
        $threshold = app(IntrusionDetectionService::class)->ruleThreshold($ruleName, $fallback);

        if ($threshold > 0 || !$fallbackRuleName) {
            return (float) $threshold;
        }

        return (float) app(IntrusionDetectionService::class)->ruleThreshold(
            $fallbackRuleName,
            (float) config("security.rules.{$fallbackRuleName}.threshold_value", $fallback)
        );
    }

    private function rule(string $ruleName): SecurityRule|false|null
    {
        $rule = app(IntrusionDetectionService::class)->ruleFor($ruleName);

        if ($rule || $rule === false || $ruleName !== 'duplicate_session') {
            return $rule;
        }

        return app(IntrusionDetectionService::class)->ruleFor('duplicate_session_detection');
    }

    private function expireCurrentSession(Request $request, UserSession $session): void
    {
        $session->update([
            'logout_time' => now(),
            'session_status' => 'expired',
        ]);

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }
}
