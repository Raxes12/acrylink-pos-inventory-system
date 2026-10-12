<?php

namespace Tests\Feature;

use App\Http\Middleware\CheckBlockedEntity;
use App\Http\Middleware\DetectInputAttack;
use App\Http\Middleware\DetectRequestRateAnomaly;
use App\Http\Middleware\TrackUserSession;
use App\Models\AuditLog;
use App\Models\AuthorizedDevice;
use App\Models\LoginAttempt;
use App\Models\IntrusionEvent;
use App\Models\SecurityRule;
use App\Models\User;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginDeviceAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->withoutMiddleware([
            CheckBlockedEntity::class,
            DetectInputAttack::class,
            DetectRequestRateAnomaly::class,
            TrackUserSession::class,
        ]);

        $inventoryUser = User::where('role', User::ROLE_INVENTORY)->firstOrFail();
        AuthorizedDevice::create([
            'device_name' => 'Inventory assigned test terminal',
            'ip_address' => '127.0.0.1',
            'assigned_user_id' => $inventoryUser->id,
            'status' => 'authorized',
        ]);
    }

    public function test_wrong_user_is_not_recorded_as_a_successful_login(): void
    {
        $cashier = User::where('username', 'cashier1')->firstOrFail();

        $this->post(route('login.post'), [
            'username' => 'cashier1',
            'password' => 'james123',
        ])->assertForbidden()
            ->assertSee('This device is not authorized to access this user account.');

        $this->assertGuest();
        $this->assertNull($cashier->fresh()->last_login);
        $this->assertFalse(AuditLog::where('action', 'LOGIN')->where('user_id', $cashier->id)->exists());
        $this->assertFalse(LoginAttempt::where('attempt_status', 'success')->where('user_id', $cashier->id)->exists());
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'LOGIN_DENIED',
            'user_id' => $cashier->id,
            'affected_table' => 'users',
            'affected_record_id' => (string) $cashier->id,
        ]);
    }

    public function test_assigned_user_is_recorded_as_successful(): void
    {
        $inventoryUser = User::where('username', 'inventory1')->firstOrFail();

        $this->post(route('login.post'), [
            'username' => 'inventory1',
            'password' => 'eugene123',
        ])->assertRedirect($inventoryUser->defaultLandingRoute());

        $this->assertAuthenticatedAs($inventoryUser);
        $this->assertNotNull($inventoryUser->fresh()->last_login);
        $this->assertTrue(AuditLog::where('action', 'LOGIN')->where('user_id', $inventoryUser->id)->exists());
        $this->assertTrue(LoginAttempt::where('attempt_status', 'success')->where('user_id', $inventoryUser->id)->exists());
    }

    public function test_login_page_remains_reachable_when_no_devices_are_registered(): void
    {
        AuthorizedDevice::query()->delete();

        $this->get(route('login'))->assertOk();
    }

    public function test_admin_can_open_device_management_to_recover_from_an_empty_whitelist(): void
    {
        AuthorizedDevice::query()->delete();
        $admin = User::where('role', User::ROLE_ADMIN)->firstOrFail();

        $this->actingAs($admin)
            ->get(route('security.devices'))
            ->assertOk();
    }

    public function test_non_admin_cannot_bypass_an_empty_device_whitelist(): void
    {
        AuthorizedDevice::query()->delete();
        $cashier = User::where('role', User::ROLE_CASHIER)->firstOrFail();

        $this->actingAs($cashier)
            ->get(route('pos.index'))
            ->assertForbidden()
            ->assertSee('No authorized device is registered.');

        $this->assertGuest();
    }

    public function test_failed_login_lockout_uses_the_saved_rule_threshold_and_window(): void
    {
        SecurityRule::where('rule_name', 'failed_login_limit')->update([
            'threshold_value' => 5,
            'time_window_minutes' => 2,
            'is_active' => true,
        ]);
        RateLimiter::clear('cashier1|127.0.0.1');

        foreach (range(1, 5) as $attempt) {
            $response = $this->post(route('login.post'), [
                'username' => 'cashier1',
                'password' => 'incorrect-password',
            ]);

            $response->assertSessionHasErrors(['username' => 'Invalid username or password.']);
        }

        $this->assertSame(5, LoginAttempt::where('username', 'cashier1')->where('attempt_status', 'failed')->count());
        $this->assertDatabaseHas('intrusion_events', ['event_type' => 'failed_login']);

        $this->post(route('login.post'), [
            'username' => 'cashier1',
            'password' => 'incorrect-password',
        ])->assertSessionHasErrors(['username' => 'Too many login attempts. Try again in 2 minute(s).']);
    }

    public function test_disabling_failed_login_rule_disables_its_configured_lockout(): void
    {
        SecurityRule::where('rule_name', 'failed_login_limit')->update(['is_active' => false]);
        RateLimiter::clear('cashier1|127.0.0.1');

        foreach (range(1, 5) as $attempt) {
            $this->post(route('login.post'), [
                'username' => 'cashier1',
                'password' => 'incorrect-password',
            ])->assertSessionHasErrors(['username' => 'Invalid username or password.']);
        }

        $this->assertFalse(IntrusionEvent::where('event_type', 'failed_login')->exists());
    }

    public function test_disabling_rogue_device_rule_stops_detection_while_whitelist_policy_still_denies_access(): void
    {
        SecurityRule::where('rule_name', 'rogue_device')->update(['is_active' => false]);

        $this->withServerVariables(['REMOTE_ADDR' => '10.20.30.40'])
            ->post(route('login.post'), [
                'username' => 'cashier1',
                'password' => 'james123',
            ])
            ->assertForbidden()
            ->assertSee('This device is not registered to access Acry-Link.');

        $this->assertFalse(IntrusionEvent::where('event_type', 'rogue_device')->exists());
        $this->assertSame(0, RateLimiter::attempts('unauthorized-device|10.20.30.40'));
    }
}
