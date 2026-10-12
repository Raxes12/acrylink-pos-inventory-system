<?php

namespace App\Http\Middleware;

use App\Services\IntrusionDetectionService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): mixed
    {
        $user = $request->user();

        if ($user && $user->status !== 'active') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            abort(403, 'Your account is inactive. Please contact an administrator.');
        }

        if (!$user || !$user->hasAnyRole($roles)) {
            if ($user) {
                try {
                    app(IntrusionDetectionService::class)->recordUnauthorizedEndpointAccess($request, $user, $roles);
                } catch (\Throwable $e) {
                    report($e);
                }
            }

            if ($request->expectsJson()) {
                return response()->json(['error' => 'Unauthorized.'], 403);
            }
            abort(403, 'Access restricted. Your role does not have permission to view this section.');
        }

        return $next($request);
    }
}
