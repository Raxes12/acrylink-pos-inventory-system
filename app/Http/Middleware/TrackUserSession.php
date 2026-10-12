<?php

namespace App\Http\Middleware;

use App\Models\UserSession;
use App\Services\DeviceIdentityService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class TrackUserSession
{
    public function handle(Request $request, Closure $next): mixed
    {
        if ($request->user()) {
            $identity = app(DeviceIdentityService::class);
            $sessionId = $request->session()->getId();
            $session = UserSession::where('session_id', $sessionId)->first();

            // Only establish a new IDS ledger entry after this application has
            // completed login, or when Laravel restored a remembered login.
            if (!$session && !$request->session()->has('auth.login_time') && !Auth::viaRemember()) {
                return $next($request);
            }

            $session ??= UserSession::create([
                'session_id' => $sessionId,
                'user_id' => $request->user()->id,
                'ip_address' => $request->ip(),
                'mac_address' => $identity->macAddress($request),
                'login_time' => $request->session()->get('auth.login_time', now()),
                'last_activity' => now(),
                'session_status' => 'active',
            ]);

            $updates = [
                'user_id' => $request->user()->id,
                'last_activity' => now(),
                'session_status' => 'active',
            ];

            if (Schema::hasColumn('user_sessions', 'user_agent') && blank($session->user_agent)) {
                $updates['user_agent'] = (string) $request->userAgent();
            }

            $session->update($updates);
        }

        return $next($request);
    }
}
