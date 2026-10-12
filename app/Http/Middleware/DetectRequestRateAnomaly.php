<?php

namespace App\Http\Middleware;

use App\Models\BlockedEntity;
use App\Models\IntrusionEvent;
use App\Models\SecurityRule;
use App\Services\BlockedEntityService;
use App\Services\IntrusionDetectionService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class DetectRequestRateAnomaly
{
    public function handle(Request $request, Closure $next): mixed
    {
        $ids = app(IntrusionDetectionService::class);
        $rule = $ids->ruleFor('ip_rate_limit');

        if ($rule === false || !$request->ip()) {
            return $next($request);
        }

        $threshold = max(1, (int) $ids->ruleThreshold('ip_rate_limit', 60));
        $window = max(1, $ids->ruleWindow('ip_rate_limit', 1));
        $key = 'ids-ip-rate|'.$request->ip();

        RateLimiter::hit($key, $window * 60);
        $attempts = RateLimiter::attempts($key);

        if ($attempts < $threshold) {
            return $next($request);
        }

        $description = "IP {$request->ip()} made {$attempts} web requests within {$window} minute(s), reaching the configured limit of {$threshold}.";
        $recentEventExists = IntrusionEvent::where('event_type', 'ip_rate_limit')
            ->where('ip_address', $request->ip())
            ->where('detected_at', '>=', now()->subMinutes($window))
            ->exists();

        if (!$recentEventExists) {
            $ids->createIntrusionEvent(
                'ip_rate_limit',
                $ids->ruleSeverity('ip_rate_limit', 'medium'),
                $description,
                $request,
                $request->user(),
                $rule instanceof SecurityRule ? $rule : null,
                'network',
                [
                    'request_path' => '/'.ltrim($request->path(), '/'),
                    'request_method' => $request->method(),
                    'user_role' => $request->user()?->roleLabel(),
                ]
            );
        }

        if ($ids->ruleAction('ip_rate_limit', 'alert') !== 'block') {
            return $next($request);
        }

        app(BlockedEntityService::class)->block(
            BlockedEntity::TYPE_IP_ADDRESS,
            $request->ip(),
            $description,
            $request->user()
        );

        abort(Response::HTTP_TOO_MANY_REQUESTS, 'Too many requests. Access is temporarily blocked by security policy.');
    }
}
