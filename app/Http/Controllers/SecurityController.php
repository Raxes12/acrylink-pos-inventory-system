<?php

namespace App\Http\Controllers;

use App\Models\AuthorizedDevice;
use App\Models\BlockedEntity;
use App\Models\IntrusionAlert;
use App\Models\IntrusionEvent;
use App\Models\SecurityRule;
use App\Models\User;
use App\Models\AuditLog;
use App\Services\DeviceIdentityService;
use App\Services\BlockedEntityService;
use App\Services\IntrusionDetectionService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Schema;

class SecurityController extends Controller
{
    public function index()
    {
        return view('security.index', [
            'newAlerts' => IntrusionAlert::where('alert_status', 'new')->count(),
            'enabledRules' => SecurityRule::active()->count(),
            'activeBlocks' => Schema::hasTable('blocked_entities') ? BlockedEntity::active()->count() : 0,
            'authorizedDevices' => AuthorizedDevice::where('status', 'authorized')->count(),
            'recentEvents' => IntrusionEvent::with('user')->latest('detected_at')->limit(6)->get(),
        ]);
    }

    public function devices(Request $request)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:authorized,blocked,inactive'],
            'edit_device' => ['nullable', 'integer', 'exists:authorized_devices,id'],
        ]);
        $identity = app(DeviceIdentityService::class);
        $editingDevice = filled($filters['edit_device'] ?? null)
            ? AuthorizedDevice::find((int) $filters['edit_device'])
            : null;

        $devices = AuthorizedDevice::with('assignedUser')
            ->when($filters['search'] ?? null, function ($query, string $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('device_name', 'like', "%{$search}%")
                        ->orWhere('ip_address', 'like', "%{$search}%")
                        ->orWhere('mac_address', 'like', "%{$search}%")
                        ->orWhereHas('assignedUser', function ($user) use ($search) {
                            $user->where('name', 'like', "%{$search}%")
                                ->orWhere('username', 'like', "%{$search}%");
                        });
                });
            })
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $deviceCounts = AuthorizedDevice::query()
            ->selectRaw('COUNT(*) as total_count')
            ->selectRaw("SUM(CASE WHEN status = 'authorized' THEN 1 ELSE 0 END) as authorized_count")
            ->selectRaw("SUM(CASE WHEN status = 'blocked' THEN 1 ELSE 0 END) as blocked_count")
            ->selectRaw("SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) as inactive_count")
            ->first();

        return view('security.devices', [
            'devices' => $devices,
            'users' => User::where('status', 'active')->orderBy('name')->get(),
            'currentIp' => request()->ip(),
            'currentMac' => $identity->macAddress(request()),
            'editingDevice' => $editingDevice,
            'filters' => $filters,
            'deviceCounts' => [
                'authorized' => (int) ($deviceCounts?->authorized_count ?? 0),
                'blocked' => (int) ($deviceCounts?->blocked_count ?? 0),
                'inactive' => (int) ($deviceCounts?->inactive_count ?? 0),
                'total' => (int) ($deviceCounts?->total_count ?? 0),
            ],
        ]);
    }

    public function storeDevice(Request $request)
    {
        $data = $request->validate([
            'device_name' => 'required|string|max:100',
            'mac_address' => ['nullable', 'required_without:ip_address', 'string', 'max:17', 'regex:/^([0-9A-Fa-f]{2}[:-]){5}[0-9A-Fa-f]{2}$/'],
            'ip_address' => ['nullable', 'required_without:mac_address', 'ip'],
            'assigned_user_id' => 'nullable|exists:users,id',
            'status' => 'required|in:authorized,blocked,inactive',
        ], $this->deviceValidationMessages());

        $data['mac_address'] = app(DeviceIdentityService::class)->normalizeMac($data['mac_address'] ?? null);
        AuthorizedDevice::create($data);
        return back()->with('success', 'Authorized device saved.');
    }

    public function updateDevice(Request $request, AuthorizedDevice $device)
    {
        $data = $request->validate([
            'device_name' => 'required|string|max:100',
            'mac_address' => ['nullable', 'required_without:ip_address', 'string', 'max:17', 'regex:/^([0-9A-Fa-f]{2}[:-]){5}[0-9A-Fa-f]{2}$/'],
            'ip_address' => ['nullable', 'required_without:mac_address', 'ip'],
            'assigned_user_id' => 'nullable|exists:users,id',
            'status' => 'required|in:authorized,blocked,inactive',
        ], $this->deviceValidationMessages());

        $data['mac_address'] = app(DeviceIdentityService::class)->normalizeMac($data['mac_address'] ?? null);
        $device->update($data);

        return redirect()->route('security.devices')->with('success', 'Device updated.');
    }

    public function destroyDevice(AuthorizedDevice $device)
    {
        $name = $device->device_name;
        $deviceId = $device->id;
        $oldValues = $device->only([
            'device_name',
            'mac_address',
            'ip_address',
            'assigned_user_id',
            'status',
        ]);

        $device->delete();

        AuditLog::record('SECURITY', "Deleted authorized device: {$name}", null, [
            'affected_table' => 'authorized_devices',
            'affected_record_id' => $deviceId,
            'old_values' => $oldValues,
        ]);

        return redirect()->route('security.devices')->with('success', "Device \"{$name}\" deleted.");
    }

    public function blockedEntities(Request $request, BlockedEntityService $blockedEntities)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'type' => ['nullable', 'in:user,ip_address,mac_address,device'],
            'status' => ['nullable', 'in:active,expired,removed'],
        ]);

        if (!Schema::hasTable('blocked_entities')) {
            return view('security.blocked-entities', [
                'blockedEntities' => new LengthAwarePaginator([], 0, 20),
                'blocklistUnavailable' => true,
                'filters' => $filters,
            ]);
        }

        $blockedEntities->expireOldBlocks();

        $blockCounts = BlockedEntity::query()
            ->selectRaw('COUNT(*) as total_count')
            ->selectRaw("SUM(CASE WHEN status = 'active' AND (expires_at IS NULL OR expires_at > CURRENT_TIMESTAMP) THEN 1 ELSE 0 END) as active_count")
            ->selectRaw("SUM(CASE WHEN status = 'active' AND expires_at IS NULL THEN 1 ELSE 0 END) as permanent_count")
            ->first();

        return view('security.blocked-entities', [
            'blockedEntities' => BlockedEntity::with('blocker')
                ->when($filters['search'] ?? null, function ($query, string $search) {
                    $query->where(function ($query) use ($search) {
                        $query->where('entity_value', 'like', "%{$search}%")
                            ->orWhere('reason', 'like', "%{$search}%")
                            ->orWhereHas('blocker', fn ($user) => $user
                                ->where('username', 'like', "%{$search}%")
                                ->orWhere('name', 'like', "%{$search}%"));
                    });
                })
                ->when($filters['type'] ?? null, fn ($query, string $type) => $query->where('entity_type', $type))
                ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
                ->latest('blocked_at')
                ->paginate(10)
                ->withQueryString(),
            'blocklistUnavailable' => false,
            'filters' => $filters,
            'activeBlocks' => (int) ($blockCounts?->active_count ?? 0),
            'permanentBlocks' => (int) ($blockCounts?->permanent_count ?? 0),
            'totalBlocks' => (int) ($blockCounts?->total_count ?? 0),
        ]);
    }

    public function expireBlockedEntity(BlockedEntity $blockedEntity)
    {
        $blockedEntity->update([
            'status' => BlockedEntity::STATUS_EXPIRED,
            'expires_at' => now(),
        ]);

        AuditLog::record('SECURITY', "Expired blocked entity #{$blockedEntity->blocked_entity_id}: {$blockedEntity->entity_type} {$blockedEntity->entity_value}");

        return back()->with('success', 'Blocked entity expired.');
    }

    public function removeBlockedEntity(BlockedEntity $blockedEntity)
    {
        $blockedEntity->update([
            'status' => BlockedEntity::STATUS_REMOVED,
        ]);

        AuditLog::record('SECURITY', "Removed blocked entity #{$blockedEntity->blocked_entity_id}: {$blockedEntity->entity_type} {$blockedEntity->entity_value}");

        return back()->with('success', 'Blocked entity removed.');
    }

    public function events(Request $request)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'severity' => ['nullable', 'in:low,medium,high,critical'],
            'status' => ['nullable', 'in:open,reviewed,resolved'],
        ]);

        $query = IntrusionEvent::with([
            'user',
            'rule',
            'latestAlert.latestNotificationLog',
            'latestAlert.acknowledgedBy',
            'latestAlert.resolvedBy',
        ])->when($filters['search'] ?? null, function ($query, string $search) {
            $query->where(function ($query) use ($search) {
                $query->where('event_type', 'like', "%{$search}%")
                    ->orWhere('source_module', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%")
                    ->orWhere('mac_address', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($user) => $user->where('username', 'like', "%{$search}%"))
                    ->orWhereHas('alerts', fn ($alert) => $alert->where('alert_title', 'like', "%{$search}%"));
            });
        })->when($filters['severity'] ?? null, function ($query, string $severity) {
            $query->where(function ($query) use ($severity) {
                $query->where('severity', $severity)
                    ->orWhereHas('latestAlert', fn ($alert) => $alert->where('severity', $severity));
            });
        })->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('event_status', $status));

        $eventCounts = IntrusionEvent::query()
            ->selectRaw('COUNT(*) as total_count')
            ->selectRaw("SUM(CASE WHEN event_status = 'open' THEN 1 ELSE 0 END) as open_count")
            ->selectRaw("SUM(CASE WHEN severity IN ('high', 'critical') AND event_status != 'resolved' THEN 1 ELSE 0 END) as high_risk_count")
            ->selectRaw("SUM(CASE WHEN event_status = 'resolved' THEN 1 ELSE 0 END) as resolved_count")
            ->first();

        return view('security.events', [
            'events' => $query->latest('detected_at')->paginate(10)->withQueryString(),
            'filters' => $filters,
            'eventCounts' => [
                'open' => (int) ($eventCounts?->open_count ?? 0),
                'high_risk' => (int) ($eventCounts?->high_risk_count ?? 0),
                'resolved' => (int) ($eventCounts?->resolved_count ?? 0),
                'total' => (int) ($eventCounts?->total_count ?? 0),
            ],
        ]);
    }

    public function showEvent(IntrusionEvent $event)
    {
        return view('security.event-details', [
            'event' => $event->load([
                'user',
                'rule',
                'alerts.notificationLogs',
                'alerts.acknowledgedBy',
                'alerts.resolvedBy',
            ]),
        ]);
    }

    public function showAlert(IntrusionAlert $alert)
    {
        return view('security.alert-details', [
            'alert' => $alert->load([
                'intrusionEvent.user',
                'intrusionEvent.rule',
                'notificationLogs',
                'acknowledgedBy',
                'resolvedBy',
            ]),
        ]);
    }

    public function acknowledgeAlert(IntrusionAlert $alert)
    {
        abort_unless($alert->alert_status === 'new', 409, 'Only new alerts can be acknowledged.');
        $alert->update([
            'alert_status' => 'acknowledged',
            'acknowledged_by' => auth()->id(),
            'acknowledged_at' => now(),
        ]);

        $alert->intrusionEvent?->update([
            'event_status' => 'reviewed',
            'resolved_at' => null,
        ]);

        AuditLog::record('SECURITY', "Acknowledged intrusion alert #{$alert->intrusion_alert_id}: {$alert->alert_title}", null, [
            'affected_table' => 'intrusion_alerts',
            'affected_record_id' => $alert->intrusion_alert_id,
            'old_values' => ['alert_status' => 'new'],
            'new_values' => $alert->only(['alert_status', 'acknowledged_by', 'acknowledged_at']),
        ]);

        return back()->with('success', 'Anomaly alert acknowledged.');
    }

    public function resolveAlert(IntrusionAlert $alert)
    {
        abort_if($alert->alert_status === 'resolved', 409, 'This alert is already resolved.');
        $data = [
            'alert_status' => 'resolved',
            'acknowledged_by' => $alert->acknowledged_by ?: auth()->id(),
            'acknowledged_at' => $alert->acknowledged_at ?: now(),
        ];

        if (Schema::hasColumn('intrusion_alerts', 'resolved_by')) {
            $data['resolved_by'] = auth()->id();
        }

        if (Schema::hasColumn('intrusion_alerts', 'resolved_at')) {
            $data['resolved_at'] = now();
        }

        $alert->update($data);

        $alert->intrusionEvent?->update([
            'event_status' => 'resolved',
            'resolved_at' => now(),
        ]);

        AuditLog::record('SECURITY', "Resolved intrusion alert #{$alert->intrusion_alert_id}: {$alert->alert_title}", null, [
            'affected_table' => 'intrusion_alerts',
            'affected_record_id' => $alert->intrusion_alert_id,
            'new_values' => $alert->only(['alert_status', 'acknowledged_by', 'acknowledged_at', 'resolved_by', 'resolved_at']),
        ]);

        return back()->with('success', 'Anomaly alert resolved.');
    }

    public function reopenAlert(IntrusionAlert $alert)
    {
        abort_unless(in_array($alert->alert_status, ['acknowledged', 'resolved'], true), 409, 'Only acknowledged or resolved alerts can be reopened.');
        $data = [
            'alert_status' => 'new',
            'acknowledged_by' => null,
            'acknowledged_at' => null,
        ];

        if (Schema::hasColumn('intrusion_alerts', 'resolved_by')) {
            $data['resolved_by'] = null;
        }

        if (Schema::hasColumn('intrusion_alerts', 'resolved_at')) {
            $data['resolved_at'] = null;
        }

        $alert->update($data);

        $alert->intrusionEvent?->update([
            'event_status' => 'open',
            'resolved_at' => null,
        ]);

        AuditLog::record('SECURITY', "Reopened intrusion alert #{$alert->intrusion_alert_id}: {$alert->alert_title}", null, [
            'affected_table' => 'intrusion_alerts',
            'affected_record_id' => $alert->intrusion_alert_id,
            'new_values' => $alert->only(['alert_status', 'resolved_by', 'resolved_at']),
        ]);

        return back()->with('success', 'Anomaly alert reopened.');
    }

    public function resendAlertEmail(IntrusionAlert $alert, IntrusionDetectionService $intrusionDetection)
    {
        if (!in_array($alert->alert_channel, ['email', 'both'], true)) {
            return back()->withErrors(['email' => 'This anomaly alert is configured for dashboard display only.']);
        }

        $sent = $intrusionDetection->sendAlertEmails($alert->load('intrusionEvent'));

        AuditLog::record('SECURITY', "Resent intrusion alert email for #{$alert->intrusion_alert_id}: {$alert->alert_title}");

        if (!$sent) {
            return back()->withErrors([
                'email' => 'The alert email could not be sent. Check the recipient and mail settings, then review the notification log.',
            ]);
        }

        return back()->with('success', 'Anomaly alert email queued/sent to admin recipients.');
    }

    public function rules()
    {
        return view('security.rules', [
            'rules' => SecurityRule::orderBy('rule_type')->orderBy('rule_name')->paginate(10),
            'severityOptions' => ['low', 'medium', 'high', 'critical'],
            'actionOptions' => ['log', 'alert', 'block', 'notify'],
        ]);
    }

    public function updateRule(Request $request, SecurityRule $rule)
    {
        $data = $request->validate([
            'threshold_value' => $rule->usesThreshold()
                ? "required|integer|min:{$rule->minimumThreshold()}|max:9999999999"
                : 'prohibited',
            'time_window_minutes' => $rule->usesWindow() ? 'required|integer|min:1|max:10080' : 'prohibited',
            'severity' => 'required|in:low,medium,high,critical',
            'action' => 'required|in:'.implode(',', $rule->supportedActions()),
            'is_active' => 'nullable|boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active');
        if (!$rule->usesThreshold()) $data['threshold_value'] = null;
        if (!$rule->usesWindow()) $data['time_window_minutes'] = null;
        $rule->update($data);

        AuditLog::record('SECURITY', "Updated security rule: {$rule->rule_name}", null, [
            'affected_table' => 'security_rules',
            'affected_record_id' => $rule->security_rule_id,
            'new_values' => $rule->only(['threshold_value', 'time_window_minutes', 'severity', 'action', 'is_active']),
        ]);

        return back()->with('success', "Security rule \"{$rule->rule_name}\" updated.");
    }

    public function updateEvent(Request $request, IntrusionEvent $event)
    {
        abort_if($event->alerts()->exists(), 422, 'Use the alert actions to update an event with an alert.');
        $data = $request->validate([
            'event_status' => 'required|in:open,reviewed,resolved',
        ]);

        $event->update([
            'event_status' => $data['event_status'],
            'resolved_at' => $data['event_status'] === 'resolved' ? now() : null,
        ]);

        AuditLog::record('SECURITY', "Updated intrusion event #{$event->id} status to {$data['event_status']}", null, [
            'affected_table' => 'intrusion_events',
            'affected_record_id' => $event->id,
            'new_values' => $event->only(['event_status', 'resolved_at']),
        ]);

        return back()->with('success', 'Anomaly event updated.');
    }

    public function blockEventDevice(Request $request, IntrusionEvent $event)
    {
        if (!$event->ip_address && !$event->mac_address) {
            return back()->withErrors(['device' => 'This alert has no IP or MAC address to block.']);
        }

        $macAddress = app(DeviceIdentityService::class)->normalizeMac($event->mac_address);
        $deviceName = 'Blocked from '.$event->event_type.' #'.$event->id;

        $device = AuthorizedDevice::where('status', 'blocked')
            ->where(function ($query) use ($event, $macAddress) {
                if ($event->ip_address) {
                    $query->orWhere('ip_address', $event->ip_address);
                }

                if ($macAddress) {
                    $query->orWhere('mac_address', $macAddress);
                }
            })
            ->first();

        if ($device) {
            $device->update([
                'device_name' => $device->device_name ?: $deviceName,
                'ip_address' => $device->ip_address ?: $event->ip_address,
                'mac_address' => $device->mac_address ?: $macAddress,
                'assigned_user_id' => null,
                'status' => 'blocked',
            ]);
        } else {
            $device = AuthorizedDevice::create([
                'device_name' => $deviceName,
                'ip_address' => $event->ip_address,
                'mac_address' => $macAddress,
                'assigned_user_id' => null,
                'status' => 'blocked',
            ]);
        }

        $event->update([
            'event_status' => 'reviewed',
            'resolved_at' => null,
        ]);
        $event->alerts()->where('alert_status', 'new')->update([
            'alert_status' => 'acknowledged',
            'acknowledged_by' => auth()->id(),
            'acknowledged_at' => now(),
        ]);

        $reason = "Manual block from intrusion event #{$event->id}: {$event->event_type}.";
        $blocklist = app(BlockedEntityService::class);
        $blocklist->block(BlockedEntity::TYPE_DEVICE, (string) $device->id, $reason, auth()->user());
        $blocklist->blockIp($event->ip_address, $reason, auth()->user());
        $blocklist->blockMac($macAddress, $reason, auth()->user());

        AuditLog::record(
            'SECURITY',
            "Blocked device from intrusion event #{$event->id}: IP {$event->ip_address}, MAC ".($macAddress ?: 'not detected')
        );

        return back()->with('success', "Device added to blocklist: {$device->device_name}.");
    }

    private function deviceValidationMessages(): array
    {
        return [
            'mac_address.required_without' => 'Enter a MAC address or an IP address.',
            'mac_address.regex' => 'Enter a valid MAC address such as AA:BB:CC:DD:EE:FF.',
            'ip_address.required_without' => 'Enter an IP address or a MAC address.',
            'ip_address.ip' => 'Enter a valid IPv4 or IPv6 address.',
        ];
    }

}
