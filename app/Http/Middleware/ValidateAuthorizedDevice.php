<?php

namespace App\Http\Middleware;

use App\Models\AuthorizedDevice;
use App\Models\UserSession;
use App\Services\DeviceIdentityService;
use App\Services\IntrusionDetectionService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ValidateAuthorizedDevice
{
    public function handle(Request $request, Closure $next): mixed
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        $whitelistMode = config('app.device_whitelist_mode', true);

        // If whitelist mode is enabled, deny access when there are no registered/authorized devices.
        if ($whitelistMode && !AuthorizedDevice::where('status', 'authorized')->exists()) {
            $isAdminRecovery = $user->isAdmin()
                && ($request->is('login*') || $request->is('security/devices*'));

            if ($isAdminRecovery) {
                return $next($request);
            }

            $this->terminateCurrentSession($request);
            abort(403, 'No authorized device is registered. An administrator must register this device first.');
        }

        $identity = app(DeviceIdentityService::class);

        $blockedDevice = $identity->matchingDevices($request, 'blocked')->first();

        if ($blockedDevice) {
            app(IntrusionDetectionService::class)->raiseDeviceAlertOnce(
                "Blocked access from device record: {$blockedDevice->device_name}.",
                $request,
                $user
            );

            $this->terminateCurrentSession($request);
            abort(403, 'This device is blocked from accessing Acry-Link.');
        }

        if ($whitelistMode) {
            $recognizedDevices = $identity->matchingDevices($request, 'authorized');
            $assignedDevices = $recognizedDevices->whereNotNull('assigned_user_id');
            $device = $assignedDevices->isNotEmpty()
                ? $assignedDevices->firstWhere('assigned_user_id', $user->id)
                : $recognizedDevices->first();

            if (!$device) {
                if ($recognizedDevices->isNotEmpty()) {
                    $recognizedDevice = $recognizedDevices->first();
                    $ids = app(IntrusionDetectionService::class);
                    $ids->clearUnauthorizedDeviceAttempts($request);
                    $ids->recordDeviceAssignmentMismatch(
                        "Registered device {$recognizedDevice->device_name} was used to access an account other than its assigned user.",
                        $request,
                        $user
                    );

                    $this->terminateCurrentSession($request);

                    abort(403, 'This device is not authorized to access this user account.');
                }

                app(IntrusionDetectionService::class)->recordUnauthorizedDeviceAttempt(
                    'Blocked access from an unauthorized device.',
                    $request,
                    $user
                );

                $this->terminateCurrentSession($request);
                abort(403, 'This device is not authorized to access Acry-Link.');
            }
        }


        return $next($request);
    }

    private function terminateCurrentSession(Request $request): void
    {
        UserSession::where('session_id', $request->session()->getId())
            ->where('session_status', 'active')
            ->update([
                'logout_time' => now(),
                'session_status' => 'logged_out',
            ]);

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }


}
