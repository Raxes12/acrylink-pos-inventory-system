<?php

namespace App\Http\Middleware;

use App\Models\AuthorizedDevice;
use App\Services\DeviceIdentityService;
use App\Services\IntrusionDetectionService;
use Closure;
use Illuminate\Http\Request;

class DenyBlockedDevice
{
    public function handle(Request $request, Closure $next): mixed
    {
        if ($request->is('logout') || $request->is('security/devices*')) {
            return $next($request);
        }

        $identity = app(DeviceIdentityService::class);
        $blockedDevice = $identity->matchingDevices($request, 'blocked')->first();

        if ($blockedDevice) {
            app(IntrusionDetectionService::class)->raiseDeviceAlertOnce(
                "Denied pre-login access from blocked device: {$blockedDevice->device_name}.",
                $request
            );

            abort(403, 'This device is blocked from accessing Acry-Link.');
        }

        if (config('app.device_whitelist_mode', true)) {
            // Allow the login screen to remain reachable for initial/recovery setup.
            // Authenticated access is still restricted by ValidateAuthorizedDevice.
            if (!AuthorizedDevice::where('status', 'authorized')->exists()) {
                return $next($request);
            }

            $registeredDevice = $identity->matchingDevices($request, 'authorized')->first();

            if (!$registeredDevice) {
                app(IntrusionDetectionService::class)->recordUnauthorizedDeviceAttempt(
                    'Denied access from an unregistered device before login.',
                    $request
                );

                abort(403, 'This device is not registered to access Acry-Link.');
            }
        }

        return $next($request);
    }
}
