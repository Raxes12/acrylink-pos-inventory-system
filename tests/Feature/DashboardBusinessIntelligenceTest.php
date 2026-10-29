<?php

namespace Tests\Feature;

use App\Http\Middleware\CheckBlockedEntity;
use App\Http\Middleware\DenyBlockedDevice;
use App\Http\Middleware\DetectInputAttack;
use App\Http\Middleware\DetectSessionAnomaly;
use App\Http\Middleware\TrackUserSession;
use App\Http\Middleware\ValidateAuthorizedDevice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardBusinessIntelligenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_renders_supported_business_intelligence_metrics(): void
    {
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

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertViewHas('salesChart', fn ($trend) => $trend->count() === 30)
            ->assertViewHasAll([
                'week',
                'month',
                'inventorySummary',
                'topProducts',
                'categoryPerformance',
                'cashierPerformance',
                'salesAssociatePerformance',
            ])
            ->assertViewHas('trendSummary')
            ->assertViewHas('salesPeriod', 'monthly')
            ->assertSee('Sales Performance')
            ->assertSee('Weekly')
            ->assertSee('Monthly')
            ->assertSee('Quarterly')
            ->assertSee('data-sales-performance', false)
            ->assertSee('data-sales-period="weekly"', false)
            ->assertSee('Average daily sales')
            ->assertSee('Inventory Snapshot')
            ->assertSee('Top Selling Products')
            ->assertSee('Category Sales Distribution')
            ->assertSee('Cashier Sales Performance')
            ->assertSee('Sales Associate Performance')
            ->assertSee('id="dashboardChartData"', false)
            ->assertSee('Inventory value');
    }

    public function test_dashboard_sales_performance_supports_weekly_and_quarterly_periods(): void
    {
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

        $this->get(route('dashboard', ['sales_period' => 'weekly']))
            ->assertOk()
            ->assertViewHas('salesPeriod', 'weekly')
            ->assertViewHas('salesPeriodDays', 7)
            ->assertViewHas('salesChart', fn ($trend) => $trend->count() === 7)
            ->assertSee('db-sales-period is-active', false);

        $this->get(route('dashboard', ['sales_period' => 'quarterly']))
            ->assertOk()
            ->assertViewHas('salesPeriod', 'quarterly')
            ->assertViewHas('salesPeriodDays', 90)
            ->assertViewHas('salesChart', fn ($trend) => $trend->count() === 13);
    }

    public function test_sales_report_accepts_month_range(): void
    {
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

        $this->get(route('reports.index', ['range' => 'month']))
            ->assertOk()
            ->assertViewHas('range', 'month')
            ->assertSee('This Month')
            ->assertSee('report-period-chip is-active', false);

        $this->get(route('reports.index', ['range' => 'year']))
            ->assertOk()
            ->assertViewHas('range', 'year')
            ->assertSee('This Year')
            ->assertDontSee('Period Comparison')
            ->assertSee('Top Selling Items');
    }

    public function test_inventory_report_metrics_follow_active_filters(): void
    {
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

        $this->get(route('reports.inventory', ['search' => '__NO_INVENTORY_MATCH__']))
            ->assertOk()
            ->assertViewHas('totalVariants', 0)
            ->assertViewHas('totalStock', 0)
            ->assertViewHas('inventoryValue', 0.0);
    }
}
