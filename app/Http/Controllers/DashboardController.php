<?php

namespace App\Http\Controllers;

use App\Models\IntrusionAlert;
use App\Models\IntrusionEvent;
use App\Models\ProductVariant;
use App\Models\Transaction;
use App\Services\DashboardAnalyticsService;
use App\Services\AccountsReceivableService;
use App\Services\FinancialReportingService;
use App\Services\LayoutMetricsService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request, DashboardAnalyticsService $analytics)
    {
        $user = auth()->user();
        $isCashier = $user?->isCashier();
        $now = now();
        $today = $now->toDateString();
        $metrics = app(LayoutMetricsService::class);
        $todaySales = $metrics->netSalesForDate($today, $isCashier ? $user->id : null);
        $todayTxns = $isCashier
            ? Transaction::whereDate('date', $today)->where('cashier_id', $user->id)->count()
            : Transaction::whereDate('date', $today)->count();
        $yesterdaySales = $isCashier
            ? 0
            : $metrics->netSalesForDate(now()->subDay()->toDateString());
        $salesChange = ! $isCashier && $yesterdaySales > 0
            ? (($todaySales - $yesterdaySales) / $yesterdaySales) * 100
            : (! $isCashier && $todaySales > 0 ? 100 : 0);
        $avgSale = (! $isCashier && $todayTxns > 0) ? $todaySales / $todayTxns : 0;
        $todayFinancials = $isCashier ? [] : app(FinancialReportingService::class)->todayMetrics();
        $receivableSummary = $isCashier ? [] : app(AccountsReceivableService::class)->summary();
        $partialReceivables = $isCashier ? 0 : app(AccountsReceivableService::class)
            ->openReceivablesQuery(['payment_status' => 'partially_paid'])->count();
        $overdueReceivables = $isCashier ? 0 : app(AccountsReceivableService::class)
            ->openReceivablesQuery(['payment_status' => 'overdue'])->count();
        $inventorySummary = $analytics->inventorySummary($now->copy()->startOfMonth(), $now);
        $lowStock = $inventorySummary['low'];
        $outOfStock = $inventorySummary['out'];
        $recentIntrusionEvents = $user?->isAdmin()
            ? IntrusionEvent::latest('detected_at')->limit(5)->get()
            : collect();
        $recentIntrusionAlerts = $user?->isAdmin()
            ? IntrusionAlert::with('notificationLogs')->latest('created_at')->limit(5)->get()
            : collect();
        $openIntrusionEvents = $user?->isAdmin() ? app(LayoutMetricsService::class)->securityAlerts() : 0;
        $newIntrusionAlerts = $user?->isAdmin() ? IntrusionAlert::where('alert_status', 'new')->count() : 0;

        $week = $analytics->salesSummary($now->copy()->startOfWeek(), $now);
        $previousWeek = $analytics->salesSummary(
            $now->copy()->subWeek()->startOfWeek(),
            $now->copy()->subWeek()->endOfWeek()
        );
        $month = $analytics->salesSummary($now->copy()->startOfMonth(), $now);
        $previousMonth = $analytics->salesSummary(
            $now->copy()->subMonthNoOverflow()->startOfMonth(),
            $now->copy()->subMonthNoOverflow()->endOfMonth()
        );
        $weekChange = $this->percentageChange((float) $week->revenue, (float) $previousWeek->revenue);
        $monthChange = $this->percentageChange((float) $month->revenue, (float) $previousMonth->revenue);
        $salesPeriod = in_array($request->query('sales_period'), ['weekly', 'monthly', 'quarterly'], true)
            ? $request->query('sales_period')
            : 'monthly';
        $salesPeriodDays = match ($salesPeriod) {
            'weekly' => 7,
            'quarterly' => 90,
            default => 30,
        };
        $salesPeriodLabel = match ($salesPeriod) {
            'weekly' => 'Last 7 days',
            'quarterly' => 'Last 3 months',
            default => 'Last 30 days',
        };
        $salesPeriodStart = $now->copy()->subDays($salesPeriodDays - 1)->startOfDay();
        $salesChart = $isCashier
            ? collect()
            : $analytics->salesTrend($salesPeriodStart, $now);
        if (! $isCashier && $salesPeriod === 'quarterly') {
            $salesChart = $salesChart->chunk(7)->map(function ($days) {
                $first = $days->first();
                $last = $days->last();

                return [
                    'date' => $first['date'],
                    'label' => \Carbon\Carbon::parse($first['date'])->format('M d').'–'.\Carbon\Carbon::parse($last['date'])->format('M d'),
                    'total' => round($days->sum('total'), 2),
                ];
            })->values();
        }
        $trendSummary = $isCashier
            ? null
            : $analytics->salesSummary($salesPeriodStart, $now);
        $topProducts = $isCashier
            ? collect()
            : $analytics->topProducts($now->copy()->startOfMonth(), $now);
        $categoryPerformance = $isCashier
            ? collect()
            : $analytics->categoryPerformance($now->copy()->startOfMonth(), $now);
        $cashierPerformance = $isCashier
            ? collect()
            : $analytics->cashierPerformance($now->copy()->startOfMonth(), $now);

        // Recent transactions
        $recentTxns = Transaction::with('cashier', 'refunds')
            ->when($isCashier, fn ($query) => $query->where('cashier_id', $user->id))
            ->orderByDesc('date')
            ->orderByDesc('time')
            ->limit(5)
            ->get();

        // Low stock alerts
        $lowStockItems = ProductVariant::with('product.productCategory', 'brand', 'supplier', 'unit', 'inventoryStock')
            ->join('inventory_stocks', 'inventory_stocks.variant_id', '=', 'product_variants.id')
            ->whereColumn('inventory_stocks.quantity_on_hand', '<=', 'inventory_stocks.minimum_stock')
            ->orderByRaw('(inventory_stocks.minimum_stock - inventory_stocks.quantity_on_hand) DESC')
            ->select('product_variants.*')
            ->limit(5)
            ->get();

        return view('dashboard.index', compact(
            'todaySales', 'todayTxns', 'lowStock', 'salesChart', 'topProducts', 'recentTxns', 'lowStockItems',
            'yesterdaySales', 'salesChange', 'avgSale', 'outOfStock',
            'recentIntrusionEvents', 'recentIntrusionAlerts', 'openIntrusionEvents', 'newIntrusionAlerts',
            'week', 'previousWeek', 'weekChange', 'month', 'previousMonth', 'monthChange',
            'inventorySummary', 'categoryPerformance', 'cashierPerformance', 'trendSummary',
            'salesPeriod', 'salesPeriodDays', 'salesPeriodLabel',
            'todayFinancials', 'receivableSummary', 'partialReceivables', 'overdueReceivables'
        ));
    }

    public function myTransactions()
    {
        $user = auth()->user();
        abort_unless($user?->isCashier(), 403);

        $transactions = Transaction::with('cashier', 'refunds', 'paymentMethod')
            ->where('cashier_id', $user->id)
            ->orderByDesc('date')
            ->orderByDesc('time')
            ->paginate(15);

        $today = now()->toDateString();
        $todaySales = app(LayoutMetricsService::class)->netSalesForDate($today, $user->id);
        $todayTxns = Transaction::whereDate('date', $today)
            ->where('cashier_id', $user->id)
            ->count();

        return view('dashboard.my-transactions', compact('transactions', 'todaySales', 'todayTxns'));
    }

    private function percentageChange(float $current, float $previous): ?float
    {
        if ($previous <= 0) {
            return $current > 0 ? null : 0.0;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }
}
