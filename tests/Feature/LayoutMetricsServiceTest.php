<?php

namespace Tests\Feature;

use App\Models\InventoryStock;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use App\Services\LayoutMetricsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class LayoutMetricsServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        Cache::flush();
    }

    public function test_stock_alert_metric_matches_existing_breakdown_and_is_cached(): void
    {
        $expected = Product::stockAlertBreakdown();
        Cache::flush();
        DB::enableQueryLog();

        $metrics = app(LayoutMetricsService::class);
        $this->assertSame($expected, $metrics->stockAlerts());
        $this->assertCount(1, DB::getQueryLog());

        DB::flushQueryLog();
        $this->assertSame($expected, $metrics->stockAlerts());
        $this->assertCount(0, DB::getQueryLog());
    }

    public function test_net_sales_metric_matches_business_calculation_and_is_cached(): void
    {
        $expected = Transaction::netSalesForDate(today());
        Cache::flush();
        DB::enableQueryLog();

        $metrics = app(LayoutMetricsService::class);
        $this->assertSame($expected, $metrics->netSalesForDate(today()));
        $this->assertCount(1, DB::getQueryLog());

        DB::flushQueryLog();
        $this->assertSame($expected, $metrics->netSalesForDate(today()));
        $this->assertCount(0, DB::getQueryLog());
    }

    public function test_stock_metric_cache_is_invalidated_when_inventory_changes(): void
    {
        $metrics = app(LayoutMetricsService::class);
        $metrics->stockAlerts();

        $stock = InventoryStock::firstOrFail();
        $stock->update(['quantity_on_hand' => 0]);
        DB::flushQueryLog();
        DB::enableQueryLog();

        $this->assertGreaterThanOrEqual(1, $metrics->stockAlerts()['out']);
        $this->assertCount(1, DB::getQueryLog());
    }

    public function test_net_sales_cache_is_invalidated_when_transaction_changes(): void
    {
        $metrics = app(LayoutMetricsService::class);
        $metrics->netSalesForDate(today());

        $cashier = User::where('role', User::ROLE_CASHIER)->firstOrFail();
        Transaction::create([
            'receipt_no' => 'RCP-LAYOUT-METRIC',
            'date' => today()->toDateString(),
            'time' => now()->toTimeString(),
            'cashier_id' => $cashier->id,
            'subtotal' => 100,
            'discount' => 0,
            'total' => 100,
            'payment_method' => 'cash',
            'payment' => 100,
            'change_due' => 0,
            'status' => 'completed',
        ]);
        $this->assertFalse(Cache::has('layout.metrics.net-sales.v1.'.today()->toDateString().'.all'));
        $expected = Transaction::netSalesForDate(today());

        DB::flushQueryLog();
        DB::enableQueryLog();

        $this->assertSame($expected, $metrics->netSalesForDate(today()));
        $this->assertCount(1, DB::getQueryLog());
    }
}
