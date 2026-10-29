<?php

namespace Tests\Feature;

use App\Http\Middleware\CheckBlockedEntity;
use App\Http\Middleware\DenyBlockedDevice;
use App\Http\Middleware\DetectInputAttack;
use App\Http\Middleware\DetectRequestRateAnomaly;
use App\Http\Middleware\DetectSessionAnomaly;
use App\Http\Middleware\TrackUserSession;
use App\Http\Middleware\ValidateAuthorizedDevice;
use App\Models\AuthorizedDevice;
use App\Models\BlockedEntity;
use App\Models\InputAttackLog;
use App\Models\IntrusionAlert;
use App\Models\IntrusionEvent;
use App\Models\SecurityRule;
use App\Models\User;
use App\Models\UserSession;
use App\Services\IntrusionDetectionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Session\ArraySessionHandler;
use Illuminate\Session\Store;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Tests\TestCase;

class IDSSecurityRegressionTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
        $this->withoutMiddleware([
            CheckBlockedEntity::class,
            DenyBlockedDevice::class,
            DetectInputAttack::class,
            DetectRequestRateAnomaly::class,
            DetectSessionAnomaly::class,
            TrackUserSession::class,
            ValidateAuthorizedDevice::class,
        ]);

        $this->admin = User::where('role', User::ROLE_ADMIN)->firstOrFail();
    }

    public function test_duplicate_session_rule_defaults_to_block(): void
    {
        $this->assertSame('block', SecurityRule::where('rule_name', 'duplicate_session')->value('action'));
        $this->assertSame('2.00', SecurityRule::where('rule_name', 'duplicate_session')->value('threshold_value'));
    }

    public function test_duplicate_session_threshold_cannot_be_set_below_two(): void
    {
        $rule = SecurityRule::where('rule_name', 'duplicate_session')->firstOrFail();

        $this->actingAs($this->admin)
            ->put(route('security.rules.update', $rule), [
                'threshold_value' => 1,
                'time_window_minutes' => 5,
                'severity' => 'medium',
                'action' => 'block',
                'is_active' => 1,
            ])
            ->assertSessionHasErrors(['threshold_value']);

        $this->assertSame('2.00', $rule->fresh()->threshold_value);
    }

    public function test_session_ledger_only_registers_an_established_application_login(): void
    {
        $request = $this->sessionRequest('cccccccccccccccccccccccccccccccccccccccc');
        $middleware = new TrackUserSession;

        $middleware->handle($request, fn () => response('unestablished'));
        $this->assertDatabaseCount('user_sessions', 0);

        $request->session()->put('auth.login_time', now());
        $middleware->handle($request, fn () => response('established'));

        $this->assertDatabaseHas('user_sessions', [
            'session_id' => 'cccccccccccccccccccccccccccccccccccccccc',
            'user_id' => $this->admin->id,
            'session_status' => 'active',
        ]);
    }

    public function test_oldest_session_stays_active_and_newer_duplicate_is_expired_even_when_event_is_deduplicated(): void
    {
        SecurityRule::where('rule_name', 'duplicate_session')->update([
            'threshold_value' => 2,
            'time_window_minutes' => 5,
            'action' => 'block',
            'is_active' => true,
        ]);

        UserSession::create([
            'session_id' => 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa',
            'user_id' => $this->admin->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'IDS Test Browser',
            'login_time' => now()->subMinute(),
            'last_activity' => now(),
            'session_status' => 'active',
        ]);
        UserSession::create([
            'session_id' => 'bbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbb',
            'user_id' => $this->admin->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'IDS Test Browser',
            'login_time' => now(),
            'last_activity' => now(),
            'session_status' => 'active',
        ]);

        $firstRequest = $this->sessionRequest('aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa');
        $this->assertSame('aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa', $firstRequest->session()->getId());
        $this->assertSame($this->admin->id, $firstRequest->user()?->id);
        $this->assertSame('2.00', SecurityRule::where('rule_name', 'duplicate_session')->value('threshold_value'));
        $this->assertSame(2, UserSession::where('user_id', $this->admin->id)->where('session_status', 'active')->count());

        Auth::login($this->admin);
        $firstResponse = (new DetectSessionAnomaly)->handle(
            $firstRequest,
            fn () => response('allowed')
        );

        $this->assertSame(200, $firstResponse->getStatusCode());
        $this->assertSame(2, UserSession::where('user_id', $this->admin->id)->where('session_status', 'active')->count());
        $this->assertDatabaseHas('intrusion_events', [
            'event_type' => 'duplicate_session',
            'user_id' => $this->admin->id,
        ]);

        Auth::login($this->admin);
        $secondResponse = (new DetectSessionAnomaly)->handle(
            $this->sessionRequest('bbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbb'),
            fn () => response('should not run')
        );

        $this->assertSame(302, $secondResponse->getStatusCode());
        $this->assertSame(route('login'), $secondResponse->getTargetUrl());
        $this->assertDatabaseHas('user_sessions', [
            'session_id' => 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa',
            'session_status' => 'active',
        ]);
        $this->assertDatabaseHas('user_sessions', [
            'session_id' => 'bbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbb',
            'session_status' => 'expired',
        ]);
        $this->assertSame(1, IntrusionEvent::where('event_type', 'duplicate_session')->count());
    }

    public function test_disabled_input_rule_does_not_log_or_raise_an_event(): void
    {
        SecurityRule::where('rule_name', 'sql_injection_attempt')->update(['is_active' => false]);

        $request = $this->request('POST', '/inventory', '127.0.0.1', [
            'search' => "' OR 1=1 -- ",
        ]);

        $response = (new DetectInputAttack)->handle($request, fn () => response('allowed'));

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(0, InputAttackLog::count());
        $this->assertFalse(IntrusionEvent::where('event_type', 'sql_injection_attempt')->exists());
    }

    public function test_active_blocking_input_rule_rejects_and_records_attack(): void
    {
        SecurityRule::where('rule_name', 'xss_attempt')->update([
            'action' => 'block',
            'is_active' => true,
        ]);

        $request = $this->request('POST', '/inventory', '127.0.0.1', [
            'name' => '<script>alert(1)</script>',
        ]);

        $this->assertSame('block', SecurityRule::where('rule_name', 'xss_attempt')->value('action'));
        $this->assertSame('<script>alert(1)</script>', $request->input('name'));

        try {
            (new DetectInputAttack)->handle($request, fn () => response('should not run'));
            $this->fail('The XSS request was not blocked.');
        } catch (HttpExceptionInterface $exception) {
            $this->assertSame(400, $exception->getStatusCode());
        }

        $this->assertDatabaseHas('input_attack_logs', ['attack_type' => 'xss']);
        $this->assertDatabaseHas('intrusion_events', ['event_type' => 'xss_attempt']);
    }

    public function test_ip_rate_rule_detects_once_and_only_blocks_when_configured(): void
    {
        SecurityRule::where('rule_name', 'ip_rate_limit')->update([
            'threshold_value' => 2,
            'time_window_minutes' => 1,
            'action' => 'alert',
            'is_active' => true,
        ]);
        RateLimiter::clear('ids-ip-rate|10.0.0.10');

        $middleware = new DetectRequestRateAnomaly;
        $firstRequest = $this->request('GET', '/login', '10.0.0.10');
        $this->assertSame('10.0.0.10', $firstRequest->ip());
        $middleware->handle($firstRequest, fn () => response('first'));
        $this->assertSame(1, RateLimiter::attempts('ids-ip-rate|10.0.0.10'));
        $response = $middleware->handle($this->request('GET', '/login', '10.0.0.10'), fn () => response('second'));
        $this->assertSame(2, RateLimiter::attempts('ids-ip-rate|10.0.0.10'));
        $middleware->handle($this->request('GET', '/login', '10.0.0.10'), fn () => response('third'));

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(1, IntrusionEvent::where('event_type', 'ip_rate_limit')->where('ip_address', '10.0.0.10')->count());
        $this->assertFalse(BlockedEntity::where('entity_value', '10.0.0.10')->exists());

        SecurityRule::where('rule_name', 'ip_rate_limit')->update(['action' => 'block']);
        RateLimiter::clear('ids-ip-rate|10.0.0.11');
        $middleware->handle($this->request('GET', '/login', '10.0.0.11'), fn () => response('first'));

        try {
            $middleware->handle($this->request('GET', '/login', '10.0.0.11'), fn () => response('should not run'));
            $this->fail('The excessive request rate was not blocked.');
        } catch (HttpExceptionInterface $exception) {
            $this->assertSame(429, $exception->getStatusCode());
        }

        $this->assertDatabaseHas('blocked_entities', [
            'entity_type' => BlockedEntity::TYPE_IP_ADDRESS,
            'entity_value' => '10.0.0.11',
            'status' => BlockedEntity::STATUS_ACTIVE,
        ]);
    }

    public function test_alert_only_failed_login_and_rogue_device_rules_do_not_create_blocks(): void
    {
        SecurityRule::where('rule_name', 'failed_login_limit')->update([
            'threshold_value' => 2,
            'action' => 'alert',
            'is_active' => true,
        ]);
        SecurityRule::where('rule_name', 'rogue_device')->update([
            'threshold_value' => 1,
            'action' => 'alert',
            'is_active' => true,
        ]);

        $ids = app(IntrusionDetectionService::class);
        $loginRequest = $this->request('POST', '/login', '10.0.0.20');
        $ids->recordLoginAttempt($loginRequest, 'unknown-user', null, 'failed', 'Invalid credentials');
        $ids->recordLoginAttempt($loginRequest, 'unknown-user', null, 'failed', 'Invalid credentials');

        RateLimiter::clear('unauthorized-device|10.0.0.21');
        $ids->recordUnauthorizedDeviceAttempt(
            'Unauthorized device test.',
            $this->request('GET', '/login', '10.0.0.21')
        );

        $this->assertDatabaseHas('intrusion_events', ['event_type' => 'failed_login']);
        $this->assertDatabaseHas('intrusion_events', ['event_type' => 'rogue_device']);
        $this->assertFalse(BlockedEntity::whereIn('entity_value', ['10.0.0.20', '10.0.0.21'])->exists());
    }

    public function test_acknowledging_alert_updates_parent_event_status(): void
    {
        $event = $this->createEventWithAlert();
        $alert = $event->alerts()->firstOrFail();

        $this->actingAs($this->admin)
            ->patch(route('security.events.alerts.acknowledge', $alert))
            ->assertSessionHasNoErrors();

        $this->assertSame('acknowledged', $alert->fresh()->alert_status);
        $this->assertSame('reviewed', $event->fresh()->event_status);
    }

    public function test_alert_email_reports_failure_when_mail_is_not_configured(): void
    {
        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.host' => null,
            'mail.mailers.smtp.username' => null,
            'mail.mailers.smtp.password' => null,
        ]);

        $event = IntrusionEvent::create([
            'user_id' => $this->admin->id,
            'event_type' => 'abnormal_activity',
            'source_module' => 'system',
            'severity' => 'high',
            'description' => 'Email failure test.',
            'ip_address' => '127.0.0.1',
            'event_status' => 'open',
            'detected_at' => now(),
        ]);
        $alert = IntrusionAlert::create([
            'intrusion_event_id' => $event->id,
            'alert_title' => 'EMAIL FAILURE TEST',
            'alert_message' => 'Email failure test.',
            'severity' => 'high',
            'alert_channel' => 'both',
            'alert_status' => 'new',
            'created_at' => now(),
        ]);

        $this->assertFalse(app(IntrusionDetectionService::class)->sendAlertEmails($alert));
        $this->assertDatabaseHas('notification_logs', [
            'intrusion_alert_id' => $alert->intrusion_alert_id,
            'send_status' => 'failed',
        ]);
    }

    public function test_security_alerts_are_sent_to_every_active_admin_email(): void
    {
        Mail::fake();
        config(['services.security_alert_email' => 'ignored@example.test']);

        $secondAdmin = User::create([
            'username' => 'second_admin',
            'name' => 'Second Administrator',
            'email' => 'second-admin@example.test',
            'password' => $this->admin->password,
            'role' => User::ROLE_ADMIN,
            'role_id' => $this->admin->role_id,
            'status' => 'active',
            'mfa_enabled' => false,
        ]);
        User::create([
            'username' => 'inactive_admin',
            'name' => 'Inactive Administrator',
            'email' => 'inactive-admin@example.test',
            'password' => $this->admin->password,
            'role' => User::ROLE_ADMIN,
            'role_id' => $this->admin->role_id,
            'status' => 'inactive',
            'mfa_enabled' => false,
        ]);

        $event = IntrusionEvent::create([
            'user_id' => $this->admin->id,
            'event_type' => 'abnormal_activity',
            'source_module' => 'system',
            'severity' => 'high',
            'description' => 'Multiple-admin recipient test.',
            'ip_address' => '127.0.0.1',
            'event_status' => 'open',
            'detected_at' => now(),
        ]);
        $alert = IntrusionAlert::create([
            'intrusion_event_id' => $event->id,
            'alert_title' => 'MULTIPLE ADMIN RECIPIENT TEST',
            'alert_message' => 'Each active administrator should receive this alert.',
            'severity' => 'high',
            'alert_channel' => 'both',
            'alert_status' => 'new',
        ]);

        $this->assertTrue(app(IntrusionDetectionService::class)->sendAlertEmails($alert));
        $this->assertDatabaseHas('notification_logs', [
            'intrusion_alert_id' => $alert->intrusion_alert_id,
            'recipient_email' => $this->admin->email,
            'send_status' => 'sent',
        ]);
        $this->assertDatabaseHas('notification_logs', [
            'intrusion_alert_id' => $alert->intrusion_alert_id,
            'recipient_email' => $secondAdmin->email,
            'send_status' => 'sent',
        ]);
        $this->assertDatabaseMissing('notification_logs', [
            'intrusion_alert_id' => $alert->intrusion_alert_id,
            'recipient_email' => 'inactive-admin@example.test',
        ]);
        $this->assertDatabaseMissing('notification_logs', [
            'intrusion_alert_id' => $alert->intrusion_alert_id,
            'recipient_email' => 'ignored@example.test',
        ]);
    }

    public function test_authorized_device_requires_a_valid_ip_or_mac_address(): void
    {
        $this->actingAs($this->admin)
            ->post(route('security.devices.store'), [
                'device_name' => 'Unidentifiable device',
                'status' => 'authorized',
            ])
            ->assertSessionHasErrors(['mac_address', 'ip_address']);

        $this->post(route('security.devices.store'), [
            'device_name' => 'Invalid MAC device',
            'mac_address' => 'not-a-mac',
            'status' => 'authorized',
        ])->assertSessionHasErrors(['mac_address']);
    }

    public function test_assigned_device_rejects_another_user_without_auto_blocking_the_device(): void
    {
        $cashier = User::where('role', User::ROLE_CASHIER)->firstOrFail();
        $ipAddress = '10.20.30.40';
        $sessionId = 'dddddddddddddddddddddddddddddddddddddddd';

        AuthorizedDevice::create([
            'device_name' => 'Assigned admin terminal',
            'ip_address' => $ipAddress,
            'assigned_user_id' => $this->admin->id,
            'status' => 'authorized',
        ]);
        UserSession::create([
            'session_id' => $sessionId,
            'user_id' => $cashier->id,
            'ip_address' => $ipAddress,
            'user_agent' => 'IDS Test Browser',
            'login_time' => now(),
            'last_activity' => now(),
            'session_status' => 'active',
        ]);

        $request = $this->sessionRequest($sessionId);
        $request->server->set('REMOTE_ADDR', $ipAddress);
        $request->setUserResolver(fn () => $cashier);
        RateLimiter::clear('unauthorized-device|'.$ipAddress);
        RateLimiter::hit('unauthorized-device|'.$ipAddress, 600);
        RateLimiter::hit('unauthorized-device|'.$ipAddress, 600);
        Auth::login($cashier);

        try {
            (new ValidateAuthorizedDevice)->handle($request, fn () => response('should not run'));
            $this->fail('A user who is not assigned to the device was not rejected.');
        } catch (HttpExceptionInterface $exception) {
            $this->assertSame(403, $exception->getStatusCode());
            $this->assertSame('This device is not authorized to access this user account.', $exception->getMessage());
        }

        $this->assertGuest();
        $this->assertDatabaseHas('user_sessions', [
            'session_id' => $sessionId,
            'session_status' => 'logged_out',
        ]);
        $this->assertSame(0, RateLimiter::attempts('unauthorized-device|'.$ipAddress));
        $this->assertFalse(BlockedEntity::where('entity_value', $ipAddress)->exists());
        $this->assertDatabaseHas('intrusion_events', [
            'event_type' => 'rogue_device',
            'user_id' => $cashier->id,
            'ip_address' => $ipAddress,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'LOGIN_DENIED',
            'user_id' => $cashier->id,
        ]);
    }

    public function test_unassigned_duplicate_cannot_override_a_matching_user_assignment(): void
    {
        $cashier = User::where('role', User::ROLE_CASHIER)->firstOrFail();
        $inventoryUser = User::where('role', User::ROLE_INVENTORY)->firstOrFail();
        $sessionId = 'eeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeee';

        AuthorizedDevice::create([
            'device_name' => 'Inventory-only localhost terminal',
            'ip_address' => '127.0.0.1',
            'assigned_user_id' => $inventoryUser->id,
            'status' => 'authorized',
        ]);
        UserSession::create([
            'session_id' => $sessionId,
            'user_id' => $cashier->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'IDS Test Browser',
            'login_time' => now(),
            'last_activity' => now(),
            'session_status' => 'active',
        ]);

        $request = $this->sessionRequest($sessionId);
        $request->setUserResolver(fn () => $cashier);
        Auth::login($cashier);

        try {
            (new ValidateAuthorizedDevice)->handle($request, fn () => response('should not run'));
            $this->fail('An unassigned duplicate device record overrode the explicit user assignment.');
        } catch (HttpExceptionInterface $exception) {
            $this->assertSame(403, $exception->getStatusCode());
            $this->assertSame('This device is not authorized to access this user account.', $exception->getMessage());
        }

        $this->assertGuest();
        $this->assertDatabaseHas('user_sessions', [
            'session_id' => $sessionId,
            'session_status' => 'logged_out',
        ]);
    }

    public function test_blocked_device_guard_allows_logout_recovery(): void
    {
        AuthorizedDevice::create([
            'device_name' => 'Blocked test terminal',
            'ip_address' => '10.20.30.41',
            'status' => 'blocked',
        ]);

        $response = (new DenyBlockedDevice)->handle(
            $this->request('POST', '/logout', '10.20.30.41'),
            fn () => response('logout allowed')
        );

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('logout allowed', $response->getContent());
    }

    private function createEventWithAlert(): IntrusionEvent
    {
        return app(IntrusionDetectionService::class)->createIntrusionEvent(
            'abnormal_activity',
            'medium',
            'Alert status synchronization test.',
            $this->request('GET', '/security-test', '127.0.0.1'),
            $this->admin,
            null,
            'system'
        );
    }

    private function sessionRequest(string $sessionId): Request
    {
        $request = $this->request('GET', '/dashboard', '127.0.0.1');
        $session = new Store('ids-test', new ArraySessionHandler(120));
        $session->setId($sessionId);
        $session->start();
        $request->setLaravelSession($session);

        return $request;
    }

    private function request(string $method, string $uri, string $ip, array $parameters = []): Request
    {
        $request = Request::create($uri, $method, $parameters, [], [], [
            'REMOTE_ADDR' => $ip,
            'HTTP_USER_AGENT' => 'IDS Test Browser',
        ]);
        $request->setUserResolver(fn () => $this->admin);

        return $request;
    }
}
