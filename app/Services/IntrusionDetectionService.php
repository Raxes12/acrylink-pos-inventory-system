<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\IntrusionAlert;
use App\Models\IntrusionEvent;
use App\Models\LoginAttempt;
use App\Models\NotificationLog;
use App\Models\Role;
use App\Models\SecurityRule;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class IntrusionDetectionService
{
    private array $ruleCache = [];
    public function recordLoginAttempt(Request $request, string $username, ?User $user, string $status, ?string $reason = null): void
    {
        LoginAttempt::create([
            'username' => $username,
            'user_id' => $user?->id,
            'ip_address' => $request->ip(),
            'mac_address' => $this->macAddress($request),
            'attempt_status' => $status,
            'failure_reason' => $reason,
            'attempted_at' => now(),
        ]);

        if ($status === 'failed') {
            $this->detectRepeatedFailedLogin($request, $username, $user);
        }
    }

    public function detectRepeatedFailedLogin(Request $request, string $username, ?User $user = null): ?IntrusionEvent
    {
        $rule = $this->securityRule('failed_login_limit');

        if ($rule === false) {
            return null;
        }

        $threshold = $rule?->thresholdOr(3) ?? 3;
        $window = $rule?->windowOr(10) ?? 10;
        $severity = $rule?->severity ?? 'high';

        $attempts = LoginAttempt::where('username', $username)
            ->where('attempt_status', 'failed')
            ->where('ip_address', $request->ip())
            ->where('attempted_at', '>=', now()->subMinutes($window))
            ->count();

        if ($attempts < $threshold) {
            return null;
        }

        $action = $rule?->action ?? 'alert';
        if ($action === 'block') {
            $this->autoBlockFailedLoginIp($request, $username, $attempts, $window, $user);
        }

        $recentOpen = IntrusionEvent::where('event_type', 'failed_login')
            ->where('event_status', 'open')
            ->where('ip_address', $request->ip())
            ->where('description', 'like', '%username '.$username.'.%')
            ->where('detected_at', '>=', now()->subMinutes($window))
            ->exists();

        if ($recentOpen) {
            return null;
        }

        return $this->raise(
            'failed_login',
            $severity,
            "Possible brute-force attempt against username {$username}. {$attempts} failed attempts were recorded from IP {$request->ip()} within {$window} minutes.",
            $request,
            $user,
            $rule
        );
    }

    public function raise(string $type, string $severity, string $description, Request $request, ?User $user = null, ?SecurityRule $rule = null, ?string $sourceModule = null): IntrusionEvent
    {
        return $this->createIntrusionEvent(
            type: $type,
            severity: $severity,
            description: $description,
            request: $request,
            user: $user,
            rule: $rule,
            sourceModule: $sourceModule
        );
    }

    public function recordUnauthorizedEndpointAccess(Request $request, User $user, array $allowedRoles): ?IntrusionEvent
    {
        $rule = $this->securityRule('unauthorized_endpoint_access');

        if ($rule === false) {
            return null;
        }

        $window = $rule?->windowOr(5) ?? 5;
        $threshold = max(1, (int) ($rule?->thresholdOr(1) ?? 1));
        $severity = $rule?->severity ?? 'high';
        $path = Str::limit('/'.ltrim($request->path(), '/'), 2048, '');
        $method = Str::upper($request->method());
        $role = $user->roleLabel();
        $requiredRoles = collect($allowedRoles)->implode(', ');

        $attempts = \App\Models\AuditLog::where('action', 'UNAUTHORIZED_ACCESS')
            ->where('user_id', $user->id)
            ->where('ip_address', $request->ip())
            ->where('timestamp', '>=', now()->subMinutes($window))
            ->count() + 1;
        AuditLog::record('UNAUTHORIZED_ACCESS', "Denied {$method} {$path}", $user);

        if ($attempts < $threshold || $this->recentUnauthorizedEndpointAccessExists($request, $user, $path, $method, $window)) {
            return null;
        }

        $routeName = $request->route()?->getName();
        $description = "Unauthorized endpoint access denied. User {$user->username} ({$role}) attempted {$method} {$path}"
            .($routeName ? " [route: {$routeName}]" : '')
            .". Required role(s): {$requiredRoles}.";

        return $this->createIntrusionEvent(
            type: 'unauthorized_endpoint_access',
            severity: $severity,
            description: $description,
            request: $request,
            user: $user,
            rule: $rule instanceof SecurityRule ? $rule : null,
            sourceModule: 'access_control',
            context: [
                'request_path' => $path,
                'request_method' => $method,
                'user_role' => $role,
                'required_roles' => Str::limit($requiredRoles, 255, ''),
            ]
        );
    }

    public function createIntrusionEvent(
        string $type,
        string $severity,
        string $description,
        Request $request,
        ?User $user = null,
        ?SecurityRule $rule = null,
        ?string $sourceModule = null,
        array $context = []
    ): IntrusionEvent
    {
        $data = [
            'user_id' => $user?->id,
            'rule_id' => $rule?->security_rule_id,
            'event_type' => $type,
            'source_module' => $sourceModule ?: $this->sourceModuleFor($type),
            'severity' => $severity,
            'description' => $description,
            'ip_address' => $request->ip(),
            'mac_address' => $this->macAddress($request),
            'user_agent' => (string) $request->userAgent(),
            'request_path' => Str::limit('/'.ltrim($request->path(), '/'), 2048, ''),
            'request_method' => Str::upper($request->method()),
            'user_role' => $user?->roleLabel(),
            'event_status' => 'open',
            'detected_at' => now(),
        ];

        foreach (['request_path', 'request_method', 'user_role', 'required_roles'] as $column) {
            if (array_key_exists($column, $context)) {
                $data[$column] = $context[$column];
            }
        }

        foreach (['rule_id', 'source_module', 'request_path', 'request_method', 'user_role', 'required_roles', 'user_agent'] as $column) {
            if (!Schema::hasColumn('intrusion_events', $column)) {
                unset($data[$column]);
            }
        }

        $event = IntrusionEvent::create($data);

        AuditLog::record('IDS', strtoupper($type).": {$description}", $user, [
            'affected_table' => 'intrusion_events',
            'affected_record_id' => $event->id,
            'new_values' => [
                'event_type' => $event->event_type,
                'source_module' => $event->source_module ?? null,
                'severity' => $event->severity,
                'event_status' => $event->event_status,
                'ip_address' => $event->ip_address,
                'mac_address' => $event->mac_address,
            ],
        ]);
        try {
            $this->createAlertIfNeeded($event);
        } catch (\Throwable $exception) {
            Log::error('Unable to create intrusion alert for event.', [
                'intrusion_event_id' => $event->id,
                'event_type' => $event->event_type,
                'severity' => $event->severity,
                'error' => $exception->getMessage(),
            ]);
        }

        return $event;
    }

    private function recentUnauthorizedEndpointAccessExists(Request $request, User $user, string $path, string $method, int $window): bool
    {
        if (!Schema::hasTable('intrusion_events')) {
            return false;
        }

        return IntrusionEvent::where('event_type', 'unauthorized_endpoint_access')
            ->where('user_id', $user->id)
            ->where('ip_address', $request->ip())
            ->where('detected_at', '>=', now()->subMinutes(max(1, $window)))
            ->when(
                Schema::hasColumn('intrusion_events', 'request_path'),
                fn ($query) => $query->where('request_path', $path)
            )
            ->when(
                Schema::hasColumn('intrusion_events', 'request_method'),
                fn ($query) => $query->where('request_method', $method)
            )
            ->exists();
    }

    public function raiseDeviceAlertOnce(
        string $description,
        Request $request,
        ?User $user = null,
        string $severity = 'critical',
        ?SecurityRule $rule = null
    ): ?IntrusionEvent
    {
        $activeRule = $rule ?? $this->securityRule('rogue_device');
        if ($activeRule === false) {
            return null;
        }

        $ipAddress = $request->ip();
        $macAddress = $this->macAddress($request);

        $recentOpen = IntrusionEvent::where('event_type', 'rogue_device')
            ->where('event_status', 'open')
            ->where('ip_address', $ipAddress)
            ->when($macAddress, fn ($query) => $query->where('mac_address', $macAddress))
            ->when($activeRule instanceof SecurityRule, fn ($query) => $query->where('rule_id', $activeRule->security_rule_id))
            ->where('detected_at', '>=', now()->subMinutes($activeRule instanceof SecurityRule ? $activeRule->windowOr(10) : 10))
            ->exists();

        if ($recentOpen) {
            return null;
        }

        return $this->raise('rogue_device', $severity, $description, $request, $user, $activeRule instanceof SecurityRule ? $activeRule : null);
    }

    public function recordDeviceAssignmentMismatch(string $description, Request $request, User $user): ?IntrusionEvent
    {
        AuditLog::record('LOGIN_DENIED', $description, $user, [
            'affected_table' => 'users',
            'affected_record_id' => $user->id,
            'new_values' => [
                'reason' => 'device_assignment_mismatch',
                'attempted_user_id' => $user->id,
                'attempted_username' => $user->username,
                'ip_address' => $request->ip(),
                'mac_address' => $this->macAddress($request),
            ],
        ]);

        $rule = $this->securityRule('rogue_device');

        if ($rule === false) {
            return null;
        }

        return $this->raiseDeviceAlertOnce(
            $description,
            $request,
            $user,
            $rule instanceof SecurityRule ? $rule->severity : 'critical',
            $rule instanceof SecurityRule ? $rule : null
        );
    }

    public function clearUnauthorizedDeviceAttempts(Request $request): void
    {
        RateLimiter::clear($this->deviceAttemptKey($request));
    }

    public function recordUnauthorizedDeviceAttempt(string $description, Request $request, ?User $user = null): ?IntrusionEvent
    {
        $rule = $this->securityRule('rogue_device');

        if ($rule === false) {
            return null;
        }

        $threshold = $rule instanceof SecurityRule ? (int) $rule->thresholdOr(3) : 3;
        $window = $rule instanceof SecurityRule ? $rule->windowOr(10) : 10;
        $severity = $rule instanceof SecurityRule ? $rule->severity : 'critical';
        $action = $rule instanceof SecurityRule ? $rule->action : 'block';
        $key = $this->deviceAttemptKey($request);

        RateLimiter::hit($key, max(1, $window) * 60);
        $attempts = RateLimiter::attempts($key);

        if ($action === 'block' && $attempts >= max(1, $threshold)) {
            $this->autoBlockUnauthorizedDevice($request, $description, $attempts, $window, $user);
        }

        return $attempts >= max(1, $threshold)
            ? $this->raiseDeviceAlertOnce($description, $request, $user, $severity, $rule instanceof SecurityRule ? $rule : null)
            : null;
    }

    private function autoBlockFailedLoginIp(Request $request, string $username, int $attempts, int $window, ?User $user = null): void
    {
        $ipAddress = $request->ip();

        if (!$ipAddress) {
            return;
        }

        $reason = "Auto-blocked after {$attempts} failed login attempts for username {$username} within {$window} minutes.";

        $block = app(BlockedEntityService::class)->blockIp($ipAddress, $reason);

        if ($block?->wasRecentlyCreated) {
            AuditLog::record('SECURITY', "Auto-blocked IP {$ipAddress}: {$reason}");
        }

        if ($user && !$user->isAdmin()) {
            $userReason = "Auto-blocked user {$user->username} after {$attempts} failed login attempts within {$window} minutes.";
            $userBlock = app(BlockedEntityService::class)->blockUser($user, $userReason);

            if ($userBlock?->wasRecentlyCreated) {
                AuditLog::record('SECURITY', $userReason, $user);
            }
        }
    }

    private function autoBlockUnauthorizedDevice(Request $request, string $description, int $attempts, int $window, ?User $user = null): void
    {
        $identity = app(DeviceIdentityService::class);
        $ipAddress = $identity->ipAddress($request);
        $macAddress = $identity->macAddress($request);
        $reason = "Auto-blocked after {$attempts} unauthorized device attempts within {$window} minutes. {$description}";
        $blocklist = app(BlockedEntityService::class);

        $deviceBlock = $blocklist->blockMatchingDevice($request, $reason, $user);
        $macBlock = $blocklist->blockMac($macAddress, $reason);

        if (!$macBlock) {
            $macBlock = $blocklist->blockIp($ipAddress, $reason);
        }

        if ($deviceBlock?->wasRecentlyCreated || $macBlock?->wasRecentlyCreated) {
            AuditLog::record('SECURITY', "Auto-blocked unauthorized device: {$reason}", $user);
        }
    }

    private function deviceAttemptKey(Request $request): string
    {
        $identity = app(DeviceIdentityService::class);
        $macAddress = $identity->macAddress($request);
        $ipAddress = $identity->ipAddress($request);

        return 'unauthorized-device|'.($macAddress ?: $ipAddress ?: 'unknown');
    }

    public function macAddress(Request $request): ?string
    {
        return app(DeviceIdentityService::class)->macAddress($request);
    }

    public function ruleFor(string $name): SecurityRule|false|null
    {
        return $this->securityRule($name);
    }

    public function ruleThreshold(string $name, float|int $fallback): float|int
    {
        $rule = $this->securityRule($name);

        return $rule instanceof SecurityRule ? $rule->thresholdOr($fallback) : $fallback;
    }

    public function ruleWindow(string $name, int $fallback): int
    {
        $rule = $this->securityRule($name);

        return $rule instanceof SecurityRule ? $rule->windowOr($fallback) : $fallback;
    }

    public function ruleSeverity(string $name, string $fallback): string
    {
        $rule = $this->securityRule($name);

        return $rule instanceof SecurityRule ? $rule->severity : $fallback;
    }

    public function ruleAction(string $name, string $fallback): string
    {
        $rule = $this->securityRule($name);

        return $rule instanceof SecurityRule ? $rule->action : $fallback;
    }

    private function sourceModuleFor(string $type): string
    {
        return match ($type) {
            'failed_login' => 'authentication',
            'ip_rate_limit' => 'network',
            'duplicate_session', 'session_ip_mismatch', 'user_agent_anomaly' => 'session',
            'rogue_device', 'unauthorized_endpoint_access', 'bulk_data_export', 'report_access_limit', 'large_report_date_range' => 'access_control',
            'off_hours_transaction', 'large_transaction', 'rapid_successive_transaction', 'excessive_discount', 'excessive_void' => 'transaction',
            'bulk_inventory_change', 'inventory_discrepancy_spike' => 'inventory',
            'sql_injection_attempt', 'xss_attempt' => 'input_validation',
            default => 'system',
        };
    }

    private function createAlertIfNeeded(IntrusionEvent $event): void
    {
        if (!Schema::hasTable('intrusion_alerts')) {
            return;
        }

        $action = $event->rule?->action;
        if ($action === 'log') {
            return;
        }

        $title = strtoupper(str_replace('_', ' ', $event->event_type));
        $alertStatus = match ($event->event_status) {
            'resolved' => 'resolved',
            'reviewed' => 'acknowledged',
            default => 'new',
        };

        $attributes = [
            'severity' => $event->severity,
        ];

        if (Schema::hasColumn('intrusion_alerts', 'alert_title')) {
            $attributes['alert_title'] = $title;
            $attributes['alert_message'] = $event->description;
            $attributes['alert_channel'] = $action === 'alert' ? 'dashboard' : 'both';
            $attributes['alert_status'] = $alertStatus;
        }

        if (Schema::hasColumn('intrusion_alerts', 'status')) {
            $attributes['status'] = $event->event_status === 'open' ? 'open' : $event->event_status;
        }

        $alert = IntrusionAlert::firstOrCreate(
            ['intrusion_event_id' => $event->id],
            $attributes
        );

        if (
            $alert->wasRecentlyCreated
            && Schema::hasColumn('intrusion_alerts', 'alert_channel')
            && in_array($alert->alert_channel, ['email', 'both'], true)
        ) {
            $this->sendAlertEmails($alert);
        }
    }

    public function sendAlertEmails(IntrusionAlert $alert): bool
    {
        if (
            !Schema::hasTable('notification_logs')
            || (
                Schema::hasColumn('intrusion_alerts', 'alert_channel')
                && !in_array($alert->alert_channel, ['email', 'both'], true)
            )
        ) {
            return false;
        }

        $recipients = $this->adminAlertRecipients();

        if ($recipients->isEmpty()) {
            return false;
        }

        $sentAny = false;

        foreach ($recipients as $email) {
            $subject = '[Acry-Link IDS] '.$alert->severity.' alert: '.$alert->alert_title;
            $message = $this->alertEmailMessage($alert);
            $log = NotificationLog::create([
                'intrusion_alert_id' => $alert->intrusion_alert_id,
                'recipient_email' => $email,
                'subject' => $subject,
                'message' => $message,
                'notification_type' => 'intrusion_alert',
                'send_status' => 'pending',
            ]);

            if (!$this->mailIsConfigured()) {
                $log->update([
                    'send_status' => 'failed',
                    'error_message' => 'Mail is not configured.',
                ]);
                continue;
            }

            try {
                Mail::raw($message, function ($mail) use ($email, $subject) {
                    $mail->to($email)->subject($subject);
                });

                $log->update([
                    'send_status' => 'sent',
                    'sent_at' => now(),
                ]);
                $sentAny = true;
            } catch (\Throwable $e) {
                $log->update([
                    'send_status' => 'failed',
                    'error_message' => $e->getMessage(),
                ]);
            }
        }

        return $sentAny;
    }

    private function adminAlertRecipients()
    {
        $configuredEmail = trim((string) config('services.security_alert_email'));

        if ($configuredEmail !== '') {
            return collect([$configuredEmail])->unique()->values();
        }

        if (Schema::hasTable('roles') && Schema::hasColumn('users', 'role_id')) {
            $roleRecipients = User::where('status', 'active')
                ->whereNotNull('email')
                ->whereHas('role', fn ($query) => $query->where('role_name', Role::ADMIN))
                ->pluck('email')
                ->filter()
                ->unique()
                ->values();

            if ($roleRecipients->isNotEmpty()) {
                return $roleRecipients;
            }
        }

        return User::where('role', User::ROLE_ADMIN)
            ->where('status', 'active')
            ->whereNotNull('email')
            ->pluck('email')
            ->filter()
            ->unique()
            ->values();
    }

    private function alertEmailMessage(IntrusionAlert $alert): string
    {
        $alert->loadMissing('intrusionEvent');

        $event = $alert->intrusionEvent;
        $safeMessage = Str::limit(strip_tags((string) $alert->alert_message), 500);

        return implode("\n", array_filter([
            'Acry-Link detected a '.$alert->severity.' anomaly alert.',
            'Title: '.$alert->alert_title,
            'Event Type: '.($event?->event_type ? str_replace('_', ' ', $event->event_type) : 'N/A'),
            'Source Module: '.($event?->source_module ?: 'N/A'),
            'IP Address: '.($event?->ip_address ?: 'N/A'),
            'Detected: '.optional($event?->detected_at)->format('Y-m-d H:i:s'),
            '',
            'Summary: '.$safeMessage,
        ]));
    }

    private function mailIsConfigured(): bool
    {
        $mailer = config('mail.default');

        if (!$mailer) {
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

    private function securityRule(string $name): SecurityRule|false|null
    {
        if (array_key_exists($name, $this->ruleCache)) {
            return $this->ruleCache[$name];
        }

        if (!Schema::hasTable('security_rules')) {
            return $this->ruleCache[$name] = null;
        }

        $rule = SecurityRule::where('rule_name', $name)->first();

        if ($rule && !$rule->is_active) {
            return $this->ruleCache[$name] = false;
        }

        return $this->ruleCache[$name] = $rule;
    }
}
