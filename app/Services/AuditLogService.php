<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AuditLogService
{
    private const SENSITIVE_KEYS = [
        'password',
        'password_confirmation',
        'current_password',
        'new_password',
        'otp',
        'otp_hash',
        'token',
        'remember_token',
        'api_token',
        '_token',
        'session',
        'session_id',
    ];

    public function record(string $action, string $detail, ?User $user = null, array $context = []): void
    {
        if (!Schema::hasTable('audit_logs')) {
            return;
        }

        $actor = $user ?? auth()->user();
        $request = request();
        $macAddress = null;

        try {
            $macAddress = app(DeviceIdentityService::class)->macAddress($request);
        } catch (\Throwable) {
            $macAddress = null;
        }

        $data = [
            'user_id' => $actor?->id,
            'username' => $actor?->username ?? 'system',
            'role' => $actor?->roleLabel() ?? $actor?->role ?? 'system',
            'action' => Str::limit(Str::upper($action), 30, ''),
            'detail' => $detail,
            'ip_address' => $request?->ip() ?? '127.0.0.1',
            'timestamp' => now(),
            'mac_address' => $macAddress,
            'user_agent' => $request ? (string) $request->userAgent() : null,
            'affected_table' => $context['affected_table'] ?? null,
            'affected_record_id' => isset($context['affected_record_id']) ? (string) $context['affected_record_id'] : null,
            'old_values' => $this->sanitize($context['old_values'] ?? null),
            'new_values' => $this->sanitize($context['new_values'] ?? null),
        ];

        foreach (['mac_address', 'user_agent', 'affected_table', 'affected_record_id', 'old_values', 'new_values'] as $column) {
            if (!Schema::hasColumn('audit_logs', $column)) {
                unset($data[$column]);
            }
        }

        AuditLog::create($data);
    }

    private function sanitize(mixed $value): mixed
    {
        if ($value instanceof Model) {
            $value = $value->getAttributes();
        }

        if ($value === null) {
            return null;
        }

        if (!is_array($value)) {
            return is_string($value) ? Str::limit($value, 1000) : $value;
        }

        $clean = [];

        foreach ($value as $key => $item) {
            $keyString = (string) $key;

            if ($this->isSensitiveKey($keyString)) {
                $clean[$key] = '[redacted]';
                continue;
            }

            $clean[$key] = $this->sanitize($item);
        }

        return $clean;
    }

    private function isSensitiveKey(string $key): bool
    {
        $normalized = Str::of($key)->lower()->replace(['-', ' '], '_')->toString();

        return in_array($normalized, self::SENSITIVE_KEYS, true)
            || Str::contains($normalized, ['password', 'otp', 'token', 'secret']);
    }
}
