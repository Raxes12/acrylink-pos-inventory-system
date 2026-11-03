<?php

namespace Tests\Feature;

use App\Http\Middleware\CheckBlockedEntity;
use App\Http\Middleware\DenyBlockedDevice;
use App\Http\Middleware\DetectInputAttack;
use App\Http\Middleware\DetectSessionAnomaly;
use App\Http\Middleware\TrackUserSession;
use App\Http\Middleware\ValidateAuthorizedDevice;
use App\Models\User;
use App\Models\PaymentMethod;
use App\Models\ProductVariant;
use App\Models\Refund;
use App\Models\RefundItem;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\TransactionPayment;
use App\Services\DashboardAnalyticsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Tests\TestCase;

class DashboardBusinessIntelligenceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

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
                'paymentMethodPerformance',
            ])
            ->assertViewHas('trendSummary')
            ->assertViewHas('salesPeriod', 'monthly')
            ->assertSee('Sales Performance')
            ->assertSee('Today')
            ->assertSee('Weekly')
            ->assertSee('Monthly')
            ->assertSee('Quarterly')
            ->assertSee('data-sales-performance', false)
            ->assertSee('data-dashboard-performance-scope', false)
            ->assertSee('data-sales-period="weekly"', false)
            ->assertSee('Average daily sales')
            ->assertSee('Inventory Snapshot')
            ->assertSee('Top Selling Products')
            ->assertSee('Category Sales Distribution')
            ->assertSee('Cashier Sales Performance')
            ->assertSee('Sales Associate Performance')
            ->assertSee('class="grid-2 db-row db-insight-row"', false)
            ->assertSee('class="card db-panel db-team-performance"', false)
            ->assertSee('Sales processed at checkout')
            ->assertSee('Sales credited to the assisting staff member')
            ->assertSee('Sales by Payment Method')
            ->assertSee('data-sales-payment-methods', false)
            ->assertSee('class="db-distribution-grid"', false)
            ->assertSee('Latest sales, inventory attention, and security events in one place.')
            ->assertSee('class="card db-panel db-operations-panel"', false)
            ->assertDontSee('View chart data')
            ->assertSee('id="dashboardChartData"', false)
            ->assertSee('Value at cost');
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

        $this->get(route('dashboard', ['sales_period' => 'today']))
            ->assertOk()
            ->assertViewHas('salesPeriod', 'today')
            ->assertViewHas('salesPeriodDays', 1)
            ->assertViewHas('salesChart', fn ($trend) => $trend->count() === 1)
            ->assertSee('data-sales-period="today"', false);

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

    public function test_payment_products_and_categories_remain_distinct_and_reconcile_after_refund(): void
    {
        $this->seed();
        $admin = User::where('role', User::ROLE_ADMIN)->firstOrFail();
        $cash = PaymentMethod::where('code', 'cash')->firstOrFail();
        $variants = ProductVariant::query()
            ->whereIn('product_id', function ($query) {
                $query->from('product_variants')->select('product_id')->groupBy('product_id')->havingRaw('COUNT(*) >= 2');
            })
            ->orderBy('product_id')
            ->orderBy('id')
            ->get()
            ->groupBy('product_id')
            ->first()
            ->take(2)
            ->values();
        $this->assertCount(2, $variants);

        $sale = $this->transaction($admin, 'RCP-DASH-001', 360, $cash->id);
        $firstItem = $this->item($sale, $variants[0], 2, 100);
        $this->item($sale, $variants[1], 1, 200);
        $refund = Refund::create([
            'refund_no' => 'REF-DASH-001',
            'transaction_id' => $sale->id,
            'processed_by' => $admin->id,
            'refund_type' => 'partial',
            'refund_amount' => 90,
            'refund_method_id' => $cash->id,
        ]);
        RefundItem::create([
            'refund_id' => $refund->id,
            'transaction_item_id' => $firstItem->id,
            'product_name' => $firstItem->product_name_snapshot,
            'quantity' => 1,
            'unit_price' => 100,
            'restock' => true,
        ]);
        $this->transaction($admin, 'RCP-DASH-002', 50, null);

        $analytics = app(DashboardAnalyticsService::class);
        $from = now()->copy()->startOfMonth();
        $to = now();
        $payments = $analytics->paymentMethodPerformance($from, $to);
        $products = $analytics->topProducts($from, $to);
        $categories = $analytics->categoryPerformance($from, $to);

        $this->assertSame(['Cash', 'Unknown / Unlinked'], $payments->pluck('label')->sort()->values()->all());
        $this->assertSame(2, $payments->pluck('label')->unique()->count());
        $this->assertEqualsWithDelta(320, $payments->sum('revenue'), 0.01);
        $this->assertCount(2, $products);
        $this->assertCount(2, $products->pluck('identifier')->unique());
        $this->assertSame(2, $products->sum('units_sold'));
        $this->assertEqualsWithDelta(270, $products->sum('revenue'), 0.01);
        $this->assertEqualsWithDelta(320, $categories->sum('revenue'), 0.01);
    }

    public function test_voided_sales_are_excluded_and_missing_prior_day_comparison_is_neutral(): void
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
        $admin = User::where('role', User::ROLE_ADMIN)->firstOrFail();
        $cash = PaymentMethod::where('code', 'cash')->firstOrFail();
        $voided = $this->transaction($admin, 'RCP-DASH-VOID', 500, $cash->id);
        $voided->update(['status' => 'voided']);

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertViewHas('todayTxns', 0)
            ->assertViewHas('periodChange', null)
            ->assertSee('No prior-period sales for comparison')
            ->assertSee('No sales recorded for this period');
    }

    public function test_dashboard_slicer_updates_every_performance_kpi_from_one_date_range(): void
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
        $admin = User::where('role', User::ROLE_ADMIN)->firstOrFail();
        $cash = PaymentMethod::where('code', 'cash')->firstOrFail();
        $todaySale = $this->transaction($admin, 'RCP-SLICER-TODAY', 100, $cash->id);
        $olderSale = $this->transaction($admin, 'RCP-SLICER-OLDER', 200, $cash->id);
        $olderSale->update(['date' => today()->subDays(10)]);

        TransactionPayment::create([
            'transaction_id' => $todaySale->id,
            'payment_method_id' => $cash->id,
            'amount' => 100,
            'received_by' => $admin->id,
            'paid_at' => now(),
        ]);
        TransactionPayment::create([
            'transaction_id' => $olderSale->id,
            'payment_method_id' => $cash->id,
            'amount' => 200,
            'received_by' => $admin->id,
            'paid_at' => now()->subDays(10),
        ]);

        $this->actingAs($admin)
            ->get(route('dashboard', ['sales_period' => 'weekly']))
            ->assertOk()
            ->assertViewHas('periodSales', 100.0)
            ->assertViewHas('periodCollections', 100.0)
            ->assertViewHas('periodTransactions', 1)
            ->assertViewHas('periodAverageSale', 100.0);

        $this->get(route('dashboard', ['sales_period' => 'monthly']))
            ->assertOk()
            ->assertViewHas('periodSales', 300.0)
            ->assertViewHas('periodCollections', 300.0)
            ->assertViewHas('periodTransactions', 2)
            ->assertViewHas('periodAverageSale', 150.0);
    }

    private function transaction(User $cashier, string $receipt, float $total, ?int $paymentMethodId): Transaction
    {
        return Transaction::create([
            'receipt_no' => $receipt,
            'checkout_token' => (string) Str::uuid(),
            'date' => today(),
            'time' => now()->toTimeString(),
            'cashier_id' => $cashier->id,
            'subtotal' => $total,
            'discount' => 0,
            'total' => $total,
            'payment_method_id' => $paymentMethodId,
            'payment' => $total,
            'change_due' => 0,
            'payment_status' => 'paid',
            'status' => 'completed',
        ]);
    }

    private function item(Transaction $transaction, ProductVariant $variant, int $quantity, float $unitPrice): TransactionItem
    {
        return TransactionItem::create([
            'transaction_id' => $transaction->id,
            'product_id' => $variant->product_id,
            'variant_id' => $variant->id,
            'product_name' => $variant->product->name,
            'product_name_snapshot' => $variant->product->name,
            'part_number_snapshot' => $variant->part_number,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'subtotal' => $quantity * $unitPrice,
        ]);
    }
}
