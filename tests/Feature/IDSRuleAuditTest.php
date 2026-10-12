<?php

namespace Tests\Feature;

use App\Http\Controllers\POSController;
use App\Http\Middleware\DetectInputAttack;
use App\Http\Middleware\DetectSessionAnomaly;
use App\Models\IntrusionEvent;
use App\Models\AuditLog;
use App\Models\LoginAttempt;
use App\Models\SecurityRule;
use App\Models\User;
use App\Models\UserSession;
use App\Services\IntrusionDetectionService;
use Database\Seeders\SecurityRuleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Mail;
use Illuminate\Session\ArraySessionHandler;
use Illuminate\Session\Store;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Tests\TestCase;

class IDSRuleAuditTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->admin = User::where('role', User::ROLE_ADMIN)->firstOrFail();
        $this->actingAs($this->admin);
    }

    public function test_all_nineteen_rules_have_editable_semantics_and_seeding_preserves_custom_settings(): void
    {
        $names = [
            'failed_login_limit', 'ip_rate_limit', 'rogue_device', 'duplicate_session',
            'off_hours_transaction', 'large_transaction', 'rapid_successive_transaction',
            'excessive_discount', 'excessive_void', 'bulk_inventory_change',
            'inventory_discrepancy_spike', 'unauthorized_endpoint_access',
            'bulk_data_export', 'report_access_limit', 'large_report_date_range',
            'session_ip_mismatch', 'user_agent_anomaly', 'sql_injection_attempt', 'xss_attempt',
        ];

        $this->assertEqualsCanonicalizing($names, SecurityRule::pluck('rule_name')->all());
        foreach (SecurityRule::all() as $rule) {
            $this->assertContains('alert', $rule->supportedActions(), $rule->rule_name);
            $this->assertContains('notify', $rule->supportedActions(), $rule->rule_name);
            $this->assertSame($rule->usesThreshold(), $rule->threshold_value !== null, $rule->rule_name);
            $this->assertSame($rule->usesWindow(), $rule->time_window_minutes !== null, $rule->rule_name);
        }

        $rule = SecurityRule::where('rule_name', 'report_access_limit')->firstOrFail();
        $rule->update(['threshold_value' => 2, 'time_window_minutes' => 2, 'severity' => 'critical', 'action' => 'block']);
        (new SecurityRuleSeeder())->run();
        $this->assertSame('2.00', $rule->fresh()->threshold_value);
        $this->assertSame(2, $rule->fresh()->time_window_minutes);
        $this->assertSame('critical', $rule->fresh()->severity);
        $this->assertSame('block', $rule->fresh()->action);
        $this->assertSame(19, SecurityRule::count());
    }

    public function test_inventory_rules_reject_unsupported_block_action(): void
    {
        foreach (['bulk_inventory_change', 'inventory_discrepancy_spike'] as $name) {
            $rule = SecurityRule::where('rule_name', $name)->firstOrFail();
            $this->put(route('security.rules.update', $rule), [
                'threshold_value' => $rule->threshold_value,
                'time_window_minutes' => $rule->time_window_minutes,
                'severity' => $rule->severity,
                'action' => 'block',
                'is_active' => 1,
            ])->assertSessionHasErrors(['action']);
            $this->assertSame('alert', $rule->fresh()->action);
        }
    }

    public function test_rule_thresholds_accept_whole_numbers_only_and_render_without_decimal_places(): void
    {
        $rule = SecurityRule::where('rule_name', 'large_transaction')->firstOrFail();

        $position = SecurityRule::orderBy('rule_type')->orderBy('rule_name')
            ->pluck('security_rule_id')->search($rule->security_rule_id) + 1;
        $page = (int) ceil($position / 10);

        $this->get(route('security.rules', ['page' => max(1, $page)]))
            ->assertOk()
            ->assertSee('value="10000"', false)
            ->assertDontSee('value="10000.00"', false);

        $this->put(route('security.rules.update', $rule), [
            'threshold_value' => '12500.50',
            'severity' => 'high',
            'action' => 'alert',
            'is_active' => 1,
            '_rule_id' => $rule->security_rule_id,
        ])->assertSessionHasErrors(['threshold_value']);

        $this->assertSame('10000.00', $rule->fresh()->threshold_value);
    }

    public function test_every_rule_edit_changes_runtime_lookup_and_disabled_state(): void
    {
        foreach (SecurityRule::all() as $rule) {
            $payload = [
                'severity' => 'low',
                'action' => 'notify',
                'is_active' => 1,
            ];
            if ($rule->usesThreshold()) {
                $payload['threshold_value'] = $rule->minimumThreshold() + 1;
            }
            if ($rule->usesWindow()) {
                $payload['time_window_minutes'] = 2;
            }

            $this->put(route('security.rules.update', $rule), $payload)->assertSessionHasNoErrors();
            $runtimeRule = app(IntrusionDetectionService::class)->ruleFor($rule->rule_name);
            $this->assertInstanceOf(SecurityRule::class, $runtimeRule, $rule->rule_name);
            $this->assertSame('low', $runtimeRule->severity, $rule->rule_name);
            $this->assertSame('notify', $runtimeRule->action, $rule->rule_name);
            if ($rule->usesThreshold()) {
                $this->assertSame((float) $payload['threshold_value'], $runtimeRule->thresholdOr(0), $rule->rule_name);
            }
            if ($rule->usesWindow()) {
                $this->assertSame(2, $runtimeRule->windowOr(0), $rule->rule_name);
            }

            $payload['is_active'] = 0;
            $this->put(route('security.rules.update', $rule), $payload)->assertSessionHasNoErrors();
            $this->assertFalse(app(IntrusionDetectionService::class)->ruleFor($rule->rule_name), $rule->rule_name);
        }
    }

    public function test_rule_actions_choose_dashboard_email_or_no_alert_consistently(): void
    {
        Mail::fake();
        $rule = SecurityRule::where('rule_name', 'ip_rate_limit')->firstOrFail();
        foreach (['log' => null, 'alert' => 'dashboard', 'notify' => 'both', 'block' => 'both'] as $action => $channel) {
            $rule->update(['action' => $action]);
            $event = app(IntrusionDetectionService::class)->createIntrusionEvent(
                'ip_rate_limit', 'medium', "Action {$action}",
                Request::create('/login', 'GET', [], [], [], ['REMOTE_ADDR' => '10.99.5.1']),
                $this->admin, $rule->fresh()
            );
            if ($channel === null) {
                $this->assertSame(0, $event->alerts()->count());
            } else {
                $this->assertSame($channel, $event->alerts()->firstOrFail()->alert_channel);
            }
        }
    }

    public function test_untrusted_forwarded_ip_cannot_bypass_rate_rule(): void
    {
        SecurityRule::where('rule_name', 'ip_rate_limit')->update([
            'threshold_value' => 1, 'action' => 'alert', 'is_active' => true,
        ]);
        RateLimiter::clear('ids-ip-rate|127.0.0.1');
        $this->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->withHeader('X-Forwarded-For', '203.0.113.77')
            ->get(route('reports.index'))
            ->assertOk();
        $this->assertDatabaseHas('intrusion_events', [
            'event_type' => 'ip_rate_limit', 'ip_address' => '127.0.0.1',
        ]);
    }

    public function test_report_access_rule_uses_custom_threshold_severity_action_and_rule_relationship(): void
    {
        $rule = SecurityRule::where('rule_name', 'report_access_limit')->firstOrFail();
        $rule->update(['threshold_value' => 2, 'time_window_minutes' => 1, 'severity' => 'critical', 'action' => 'alert']);

        $this->get(route('reports.index'))->assertOk();
        $this->assertFalse(IntrusionEvent::where('event_type', 'report_access_limit')->exists());
        $this->get(route('reports.index'))->assertOk();

        $event = IntrusionEvent::where('event_type', 'report_access_limit')->firstOrFail();
        $this->assertSame($rule->security_rule_id, $event->rule_id);
        $this->assertSame('critical', $event->severity);
        $this->assertSame('reports', $event->source_module);
        $this->assertSame($this->admin->id, $event->user_id);
        $this->assertSame('new', $event->alerts()->firstOrFail()->alert_status);
        $this->get(route('reports.index'))->assertOk();
        $this->assertSame(1, IntrusionEvent::where('event_type', 'report_access_limit')->count());

        $rule->update(['is_active' => false]);
        $this->get(route('reports.index'))->assertOk();
        $this->assertSame(1, IntrusionEvent::where('event_type', 'report_access_limit')->count());
    }

    public function test_input_rules_use_custom_threshold_and_block_only_at_threshold(): void
    {
        foreach (['sql_injection_attempt' => "' OR 1=1 -- ", 'xss_attempt' => '<script>alert(1)</script>'] as $name => $payload) {
            $rule = SecurityRule::where('rule_name', $name)->firstOrFail();
            $rule->update(['threshold_value' => 2, 'time_window_minutes' => 1, 'severity' => 'medium', 'action' => 'block']);
            $key = 'ids-input|'.$name.'|10.9.0.'.($name === 'xss_attempt' ? '2' : '1');
            RateLimiter::clear($key);
            $ip = $name === 'xss_attempt' ? '10.9.0.2' : '10.9.0.1';
            $request = fn () => $this->inputRequest($ip, $payload);

            $this->assertSame(200, (new DetectInputAttack())->handle($request(), fn () => response('allowed'))->getStatusCode());
            $this->assertFalse(IntrusionEvent::where('event_type', $name)->exists());
            try {
                (new DetectInputAttack())->handle($request(), fn () => response('should be blocked'));
                $this->fail("{$name} was not blocked at its threshold");
            } catch (HttpExceptionInterface $exception) {
                $this->assertSame(400, $exception->getStatusCode());
            }

            $event = IntrusionEvent::where('event_type', $name)->firstOrFail();
            $this->assertSame($rule->security_rule_id, $event->rule_id);
            $this->assertSame('medium', $event->severity);
            $this->assertSame('new', $event->alerts()->firstOrFail()->alert_status);
            $rule->update(['is_active' => false]);
            $this->assertSame(200, (new DetectInputAttack())->handle($request(), fn () => response('allowed'))->getStatusCode());
            $this->assertSame(1, IntrusionEvent::where('event_type', $name)->count());
        }
    }

    public function test_combined_sql_and_xss_payload_records_both_rules_before_blocking(): void
    {
        foreach (['sql_injection_attempt', 'xss_attempt'] as $name) {
            SecurityRule::where('rule_name', $name)->update(['threshold_value' => 1, 'action' => 'block']);
        }
        $request = Request::create('/inventory', 'POST', [
            'search' => "' OR 1=1 -- ",
            'name' => '<script>alert(1)</script>',
        ], [], [], ['REMOTE_ADDR' => '10.99.6.1']);
        $request->setUserResolver(fn () => $this->admin);
        foreach (['sql_injection_attempt', 'xss_attempt'] as $name) {
            RateLimiter::clear('ids-input|'.$name.'|10.99.6.1');
        }

        try {
            (new DetectInputAttack())->handle($request, fn () => response('should be blocked'));
            $this->fail('Combined attack payload was not blocked.');
        } catch (HttpExceptionInterface $exception) {
            $this->assertSame(400, $exception->getStatusCode());
        }

        foreach (['sql_injection_attempt', 'xss_attempt'] as $name) {
            $event = IntrusionEvent::where('event_type', $name)->firstOrFail();
            $this->assertSame(SecurityRule::where('rule_name', $name)->value('security_rule_id'), $event->rule_id);
        }
    }

    public function test_rogue_device_rule_reaches_threshold_and_does_not_trust_client_mac_header(): void
    {
        $rule = SecurityRule::where('rule_name', 'rogue_device')->firstOrFail();
        $rule->update(['threshold_value' => 2, 'time_window_minutes' => 1, 'severity' => 'medium', 'action' => 'alert']);
        $request = Request::create('/login', 'GET', [], [], [], [
            'REMOTE_ADDR' => '10.99.0.5',
            'HTTP_X_CLIENT_MAC' => 'aa:bb:cc:dd:ee:ff',
        ]);
        RateLimiter::clear('unauthorized-device|10.99.0.5');
        $ids = app(IntrusionDetectionService::class);
        $this->assertNull($ids->macAddress($request));
        $this->assertNull($ids->recordUnauthorizedDeviceAttempt('Unauthorized terminal', $request));
        $event = $ids->recordUnauthorizedDeviceAttempt('Unauthorized terminal', $request);
        $this->assertNotNull($event);
        $this->assertSame($rule->security_rule_id, $event->rule_id);
        $this->assertSame('medium', $event->severity);
        $this->assertSame(1, $event->alerts()->count());
        $this->assertNull($ids->recordUnauthorizedDeviceAttempt('Unauthorized terminal', $request));
        $this->assertSame(1, IntrusionEvent::where('event_type', 'rogue_device')->count());
    }

    public function test_transaction_rules_record_each_incident_even_when_another_rule_blocks_checkout(): void
    {
        config(['security.business_hours.start_hour' => 0, 'security.business_hours.end_hour' => 24]);
        SecurityRule::where('rule_name', 'large_transaction')->update(['threshold_value' => 100, 'severity' => 'critical', 'action' => 'block']);
        SecurityRule::where('rule_name', 'rapid_successive_transaction')->update(['threshold_value' => 1, 'action' => 'alert']);
        SecurityRule::where('rule_name', 'excessive_discount')->update(['threshold_value' => 10, 'action' => 'alert']);
        $request = Request::create('/pos/checkout', 'POST');
        $request->setUserResolver(fn () => $this->admin);
        $reason = (new \ReflectionMethod(POSController::class, 'detectCheckoutAnomalies'))
            ->invoke(app(POSController::class), $request, 200.0, 40.0, 160.0);

        $this->assertNotNull($reason);
        foreach (['large_transaction', 'rapid_successive_transaction', 'excessive_discount'] as $name) {
            $event = IntrusionEvent::where('event_type', $name)->firstOrFail();
            $this->assertSame(SecurityRule::where('rule_name', $name)->value('security_rule_id'), $event->rule_id);
            $this->assertSame(1, $event->alerts()->count());
        }
        $this->assertSame('critical', IntrusionEvent::where('event_type', 'large_transaction')->value('severity'));
    }

    public function test_export_and_large_date_range_rules_create_separate_events(): void
    {
        SecurityRule::where('rule_name', 'bulk_data_export')->update(['threshold_value' => 1, 'action' => 'alert']);
        SecurityRule::where('rule_name', 'large_report_date_range')->update(['threshold_value' => 2, 'action' => 'alert']);

        $this->get(route('reports.export', [
            'range' => 'custom', 'date_from' => '2026-09-01', 'date_to' => '2026-09-02',
        ]))->assertOk();

        foreach (['bulk_data_export', 'large_report_date_range'] as $name) {
            $event = IntrusionEvent::where('event_type', $name)->firstOrFail();
            $this->assertSame(SecurityRule::where('rule_name', $name)->value('security_rule_id'), $event->rule_id);
            $this->assertSame(1, $event->alerts()->count());
        }
    }

    public function test_alert_lifecycle_and_manual_device_block_stay_synchronized(): void
    {
        $rule = SecurityRule::where('rule_name', 'rogue_device')->firstOrFail();
        $event = app(IntrusionDetectionService::class)->createIntrusionEvent(
            'rogue_device', 'high', 'Unauthorized terminal',
            Request::create('/login', 'GET', [], [], [], ['REMOTE_ADDR' => '10.99.2.5']),
            $this->admin, $rule
        );
        $alert = $event->alerts()->firstOrFail();

        $this->patch(route('security.events.alerts.acknowledge', $alert))->assertSessionHasNoErrors();
        $this->assertSame('acknowledged', $alert->fresh()->alert_status);
        $this->assertSame('reviewed', $event->fresh()->event_status);
        $this->patch(route('security.events.alerts.resolve', $alert))->assertSessionHasNoErrors();
        $this->assertSame('resolved', $alert->fresh()->alert_status);
        $this->assertSame('resolved', $event->fresh()->event_status);
        $this->patch(route('security.events.alerts.reopen', $alert))->assertSessionHasNoErrors();
        $this->assertSame('new', $alert->fresh()->alert_status);
        $this->assertSame('open', $event->fresh()->event_status);
        $this->assertNull($alert->fresh()->acknowledged_at);

        $this->post(route('security.events.block-device', $event))->assertSessionHasNoErrors();
        $this->assertSame('acknowledged', $alert->fresh()->alert_status);
        $this->assertSame('reviewed', $event->fresh()->event_status);
        $this->assertDatabaseHas('authorized_devices', ['ip_address' => '10.99.2.5', 'status' => 'blocked']);
    }

    public function test_failed_login_rule_counts_only_recent_attempts_and_keeps_its_rule_id(): void
    {
        $rule = SecurityRule::where('rule_name', 'failed_login_limit')->firstOrFail();
        $rule->update(['threshold_value' => 2, 'time_window_minutes' => 1, 'severity' => 'critical', 'action' => 'alert']);
        $request = Request::create('/login', 'POST', [], [], [], ['REMOTE_ADDR' => '10.99.3.1']);
        $ids = app(IntrusionDetectionService::class);
        LoginAttempt::create([
            'username' => 'test-ids', 'ip_address' => '10.99.3.1',
            'attempt_status' => 'failed', 'attempted_at' => now()->subMinutes(2),
        ]);
        $ids->recordLoginAttempt($request, 'test-ids', null, 'failed');
        $this->assertFalse(IntrusionEvent::where('event_type', 'failed_login')->exists());
        $ids->recordLoginAttempt($request, 'test-ids', null, 'failed');
        $event = IntrusionEvent::where('event_type', 'failed_login')->firstOrFail();
        $this->assertSame($rule->security_rule_id, $event->rule_id);
        $this->assertSame('critical', $event->severity);
        $this->assertSame(1, $event->alerts()->count());
        $ids->recordLoginAttempt($request, 'test-ids', null, 'failed');
        $this->assertSame(1, IntrusionEvent::where('event_type', 'failed_login')->count());
    }

    public function test_unauthorized_endpoint_rule_uses_threshold_and_disabled_state(): void
    {
        $rule = SecurityRule::where('rule_name', 'unauthorized_endpoint_access')->firstOrFail();
        $rule->update(['threshold_value' => 2, 'time_window_minutes' => 1, 'severity' => 'medium', 'action' => 'alert']);
        $cashier = User::where('role', User::ROLE_CASHIER)->firstOrFail();
        $this->actingAs($cashier);
        $this->get(route('security.events'))->assertForbidden();
        $this->assertFalse(IntrusionEvent::where('event_type', 'unauthorized_endpoint_access')->exists());
        $this->get(route('security.events'))->assertForbidden();
        $event = IntrusionEvent::where('event_type', 'unauthorized_endpoint_access')->firstOrFail();
        $this->assertSame($rule->security_rule_id, $event->rule_id);
        $this->assertSame('medium', $event->severity);
        $this->assertSame(1, $event->alerts()->count());
        $rule->update(['is_active' => false]);
        $this->get(route('security.events'))->assertForbidden();
        $this->assertSame(1, IntrusionEvent::where('event_type', 'unauthorized_endpoint_access')->count());
    }

    public function test_off_hours_and_excessive_void_rules_have_reachable_pos_triggers(): void
    {
        config(['security.business_hours.start_hour' => 8, 'security.business_hours.end_hour' => 17]);
        $this->travelTo(now()->startOfDay()->addHours(2));
        SecurityRule::where('rule_name', 'large_transaction')->update(['is_active' => false]);
        SecurityRule::where('rule_name', 'rapid_successive_transaction')->update(['is_active' => false]);
        SecurityRule::where('rule_name', 'excessive_discount')->update(['is_active' => false]);
        $request = Request::create('/pos/checkout', 'POST');
        $request->setUserResolver(fn () => $this->admin);
        (new \ReflectionMethod(POSController::class, 'detectCheckoutAnomalies'))
            ->invoke(app(POSController::class), $request, 100.0, 0.0, 100.0);
        $event = IntrusionEvent::where('event_type', 'off_hours_transaction')->firstOrFail();
        $this->assertSame(SecurityRule::where('rule_name', 'off_hours_transaction')->value('security_rule_id'), $event->rule_id);

        $rule = SecurityRule::where('rule_name', 'excessive_void')->firstOrFail();
        $rule->update(['threshold_value' => 2, 'time_window_minutes' => 1, 'action' => 'alert']);
        $method = new \ReflectionMethod(POSController::class, 'detectRepeatedReversals');
        $this->assertNull($method->invoke(app(POSController::class), $request));
        AuditLog::record('VOID', 'Prior void', $this->admin);
        $this->assertNull($method->invoke(app(POSController::class), $request));
        $event = IntrusionEvent::where('event_type', 'excessive_void')->firstOrFail();
        $this->assertSame($rule->security_rule_id, $event->rule_id);
        $this->assertSame(1, $event->alerts()->count());
        $this->travelBack();
    }

    public function test_session_ip_and_user_agent_rules_detect_both_changes_on_one_request(): void
    {
        SecurityRule::where('rule_name', 'session_ip_mismatch')->update(['action' => 'block', 'severity' => 'critical']);
        SecurityRule::where('rule_name', 'user_agent_anomaly')->update(['action' => 'alert', 'severity' => 'high']);
        $id = str_repeat('f', 40);
        UserSession::create([
            'session_id' => $id, 'user_id' => $this->admin->id,
            'ip_address' => '10.10.1.1', 'user_agent' => 'Original browser',
            'login_time' => now()->subMinute(), 'last_activity' => now(),
            'session_status' => 'active',
        ]);
        $request = Request::create('/dashboard', 'GET', [], [], [], [
            'REMOTE_ADDR' => '10.10.1.2', 'HTTP_USER_AGENT' => 'Changed browser',
        ]);
        $session = new Store('ids-audit', new ArraySessionHandler(120));
        $session->setId($id);
        $session->start();
        $request->setLaravelSession($session);
        $request->setUserResolver(fn () => $this->admin);

        $response = (new DetectSessionAnomaly())->handle($request, fn () => response('allowed'));
        $this->assertSame(302, $response->getStatusCode());
        foreach (['session_ip_mismatch', 'user_agent_anomaly'] as $name) {
            $event = IntrusionEvent::where('event_type', $name)->firstOrFail();
            $this->assertSame(SecurityRule::where('rule_name', $name)->value('security_rule_id'), $event->rule_id);
            $this->assertSame(1, $event->alerts()->count());
        }
        $this->assertSame('expired', UserSession::where('session_id', $id)->value('session_status'));
    }

    private function inputRequest(string $ip, string $payload): Request
    {
        $request = Request::create('/inventory', 'POST', ['nested' => ['search' => $payload]], [], [], ['REMOTE_ADDR' => $ip]);
        $request->setUserResolver(fn () => $this->admin);

        return $request;
    }
}
