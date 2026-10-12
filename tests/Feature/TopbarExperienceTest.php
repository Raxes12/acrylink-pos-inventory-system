<?php

namespace Tests\Feature;

use App\Http\Middleware\CheckBlockedEntity;
use App\Http\Middleware\DenyBlockedDevice;
use App\Http\Middleware\DetectInputAttack;
use App\Http\Middleware\DetectSessionAnomaly;
use App\Http\Middleware\TrackUserSession;
use App\Http\Middleware\ValidateAuthorizedDevice;
use App\Models\InventoryStock;
use App\Models\IntrusionEvent;
use App\Models\User;
use App\Services\LayoutMetricsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TopbarExperienceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->withoutMiddleware([
            CheckBlockedEntity::class,
            DenyBlockedDevice::class,
            DetectInputAttack::class,
            DetectSessionAnomaly::class,
            TrackUserSession::class,
            ValidateAuthorizedDevice::class,
        ]);
        $this->actingAs(User::where('role', User::ROLE_ADMIN)->firstOrFail());
    }

    public function test_topbar_uses_context_account_controls_and_cached_alert_totals(): void
    {
        IntrusionEvent::create([
            'user_id' => auth()->id(),
            'event_type' => 'topbar_test_anomaly',
            'source_module' => 'system',
            'severity' => 'high',
            'description' => 'Topbar notification aggregation test.',
            'ip_address' => '127.0.0.1',
            'event_status' => 'open',
            'detected_at' => now(),
        ]);
        $metrics = app(LayoutMetricsService::class);
        $stock = $metrics->stockAlerts();
        $security = $metrics->securityAlerts();
        $total = $stock['total'] + $security;

        $response = $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Overview')
            ->assertSee('Dashboard')
            ->assertSee('data-topbar-menu', false)
            ->assertSee('Needs Attention')
            ->assertSee((string) $total)
            ->assertSee('Stock Alerts')
            ->assertSee('Anomaly Alerts')
            ->assertSee('My Profile')
            ->assertSee('data-live-clock-date', false)
            ->assertSee('id="themeToggle"', false)
            ->assertDontSee('pill-sales', false)
            ->assertDontSee('Admin Workspace');

        $this->assertSame($total, $stock['low'] + $stock['critical'] + $stock['out'] + $security);
    }

    public function test_topbar_context_follows_the_current_route(): void
    {
        $this->get(route('inventory.stock-overview'))
            ->assertOk()
            ->assertSee('Inventory / Stock Control')
            ->assertSee('Stock Overview');

        $this->get(route('security.events'))
            ->assertOk()
            ->assertSee('Security &amp; Monitoring', false)
            ->assertSee('Anomaly Alerts');
    }

    public function test_zero_alerts_hide_the_notification_control(): void
    {
        InventoryStock::query()->update([
            'quantity_on_hand' => 100000,
            'minimum_stock' => 1,
            'critical_stock' => 0,
        ]);
        IntrusionEvent::query()->update(['event_status' => 'resolved']);
        $metrics = app(LayoutMetricsService::class);
        $metrics->forgetStockAlerts();
        $metrics->forgetSecurityAlerts();

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('topbar-alert-menu', false)
            ->assertSee('topbar-profile-menu', false);
    }
}
