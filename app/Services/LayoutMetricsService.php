<?php

namespace App\Services;

use App\Models\InventoryStock;
use App\Models\IntrusionEvent;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class LayoutMetricsService
{
    private const CACHE_SECONDS = 15;
    private const STOCK_ALERTS_KEY = 'layout.metrics.stock-alerts.v1';
    private const SECURITY_ALERTS_KEY = 'layout.metrics.security-alerts.v1';

    public function stockAlerts(): array
    {
        return Cache::remember(self::STOCK_ALERTS_KEY, self::CACHE_SECONDS, function () {
            $counts = InventoryStock::query()
                ->selectRaw('SUM(CASE WHEN quantity_on_hand <= 0 THEN 1 ELSE 0 END) as out_count')
                ->selectRaw('SUM(CASE WHEN quantity_on_hand > 0 AND quantity_on_hand <= critical_stock THEN 1 ELSE 0 END) as critical_count')
                ->selectRaw('SUM(CASE WHEN quantity_on_hand > critical_stock AND quantity_on_hand <= minimum_stock THEN 1 ELSE 0 END) as low_count')
                ->first();

            $low = (int) ($counts?->low_count ?? 0);
            $critical = (int) ($counts?->critical_count ?? 0);
            $out = (int) ($counts?->out_count ?? 0);

            return [
                'low' => $low,
                'critical' => $critical,
                'out' => $out,
                'total' => $low + $critical + $out,
            ];
        });
    }

    public function securityAlerts(): int
    {
        return Cache::remember(
            self::SECURITY_ALERTS_KEY,
            self::CACHE_SECONDS,
            fn () => IntrusionEvent::where('event_status', 'open')->count()
        );
    }

    public function netSalesForDate(CarbonInterface|string $date, ?int $cashierId = null): float
    {
        $date = $date instanceof CarbonInterface ? $date->toDateString() : (string) $date;

        return Cache::remember($this->netSalesKey($date, $cashierId), self::CACHE_SECONDS, function () use ($date, $cashierId) {
            $refundTotals = DB::table('refunds')
                ->selectRaw('transaction_id, SUM(refund_amount) as refunded_total')
                ->groupBy('transaction_id');

            $query = DB::table('transactions')
                ->leftJoinSub($refundTotals, 'layout_refunds', 'layout_refunds.transaction_id', '=', 'transactions.id')
                ->whereDate('transactions.date', $date)
                ->when($cashierId, fn ($query) => $query->where('transactions.cashier_id', $cashierId));

            $result = $query->selectRaw(
                'COALESCE(SUM(CASE '
                .'WHEN transactions.status = ? THEN 0 '
                .'WHEN transactions.total - COALESCE(layout_refunds.refunded_total, 0) < 0 THEN 0 '
                .'ELSE transactions.total - COALESCE(layout_refunds.refunded_total, 0) END), 0) as net_sales',
                ['voided']
            )->first();

            return round((float) ($result?->net_sales ?? 0), 2);
        });
    }

    public function forgetStockAlerts(): void
    {
        Cache::forget(self::STOCK_ALERTS_KEY);
    }

    public function forgetSecurityAlerts(): void
    {
        Cache::forget(self::SECURITY_ALERTS_KEY);
    }

    public function forgetNetSales(CarbonInterface|string|null $date, ?int $cashierId): void
    {
        if (!$date) {
            return;
        }

        $date = $date instanceof CarbonInterface ? $date->toDateString() : (string) $date;
        Cache::forget($this->netSalesKey($date, null));
        Cache::forget($this->netSalesKey($date, $cashierId));
    }

    private function netSalesKey(string $date, ?int $cashierId): string
    {
        return 'layout.metrics.net-sales.v1.'.$date.'.'.($cashierId ?: 'all');
    }
}
