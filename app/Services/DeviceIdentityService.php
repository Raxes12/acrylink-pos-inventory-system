<?php

namespace App\Services;

use App\Models\AuthorizedDevice;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DeviceIdentityService
{
    private const REQUEST_MAC_ATTRIBUTE = '_device_identity_mac';

    public function ipAddress(Request $request): ?string
    {
        return $request->ip();
    }

    public function macAddress(Request $request): ?string
    {
        if ($request->attributes->has(self::REQUEST_MAC_ATTRIBUTE)) {
            return $request->attributes->get(self::REQUEST_MAC_ATTRIBUTE);
        }

        // HTTP headers and form fields are client-controlled and cannot prove
        // the requester's LAN identity. Only use the server's ARP observation.
        $macAddress = $this->macFromArp($request->ip());

        $request->attributes->set(self::REQUEST_MAC_ATTRIBUTE, $macAddress);

        return $macAddress;
    }

    public function normalizeMac(?string $mac): ?string
    {
        if (!$mac) {
            return null;
        }

        $mac = strtolower(trim($mac));
        $mac = str_replace('-', ':', $mac);

        if (!preg_match('/^([0-9a-f]{2}:){5}[0-9a-f]{2}$/', $mac)) {
            return null;
        }

        return $mac;
    }

    public function identifiersMatch(?string $deviceIp, ?string $deviceMac, ?string $requestIp, ?string $requestMac): bool
    {
        $deviceIp = $deviceIp ?: null;
        $deviceMac = $this->normalizeMac($deviceMac);
        $requestMac = $this->normalizeMac($requestMac);

        if (!$deviceIp && !$deviceMac) {
            return false;
        }

        if ($deviceIp && $deviceMac && $requestIp && $requestMac) {
            return $requestIp === $deviceIp && $requestMac === $deviceMac;
        }

        if ($deviceMac && $requestMac) {
            return $requestMac === $deviceMac;
        }

        if ($deviceIp && $requestIp) {
            return $requestIp === $deviceIp;
        }

        return false;
    }

    public function matchingDevices(Request $request, ?string $status = null): Collection
    {
        $ipAddress = $this->ipAddress($request);
        $macAddress = $this->macAddress($request);

        if (!$ipAddress && !$macAddress) {
            return new Collection();
        }

        return AuthorizedDevice::query()
            ->when($status, fn ($query) => $query->where('status', $status))
            ->where(function ($query) use ($ipAddress, $macAddress) {
                if ($ipAddress) {
                    $query->where('ip_address', $ipAddress);
                }

                if ($macAddress) {
                    $query->orWhere('mac_address', $macAddress);
                }
            })
            ->get()
            ->filter(fn (AuthorizedDevice $device) => $this->identifiersMatch(
                $device->ip_address,
                $device->mac_address,
                $ipAddress,
                $macAddress
            ));
    }

    private function macFromArp(?string $ip): ?string
    {
        if (!$ip || !filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return null;
        }

        if (in_array($ip, ['127.0.0.1', '0.0.0.0'], true)) {
            return null;
        }

        $command = stripos(PHP_OS_FAMILY, 'Windows') !== false
            ? 'arp -a '.escapeshellarg($ip)
            : 'arp -n '.escapeshellarg($ip);

        try {
            $output = shell_exec($command);
        } catch (\Throwable $e) {
            Log::debug('Unable to read ARP table for device MAC detection.', [
                'ip' => $ip,
                'error' => $e->getMessage(),
            ]);
            return null;
        }

        if (!$output) {
            return null;
        }

        // Match the requested IP's ARP row; another row's MAC is not evidence
        // of this client device.
        $pattern = '/(?:^|\r?\n)\s*'.preg_quote($ip, '/').'\s+(([0-9a-f]{2}[:-]){5}[0-9a-f]{2})(?:\s|$)/i';
        if (!preg_match($pattern, $output, $matches)) {
            return null;
        }

        return $this->normalizeMac($matches[1]);
    }
}
