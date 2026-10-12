<?php

namespace App\Services;

use App\Models\InventoryMovement;
use App\Models\InventoryStock;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockReceiptItem;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DashboardAnalyticsService
{
    public function salesSummary(CarbonInterface $from, CarbonInterface $to): object
    {
        [$start, $end] = $this->dateBounds($from, $to);
        $refunds = DB::table('refunds')
            ->selectRaw('transaction_id, SUM(refund_amount) as refunded_amount')
            ->groupBy('transaction_id');

        $netTotal = $this->netTransactionTotalSql();

        return Transaction::query()
            ->leftJoinSub($refunds, 'refund_totals', fn ($join) => $join->on('refund_totals.transaction_id', '=', 'transactions.id'))
            ->whereBetween('transactions.date', [$start, $end])
            ->selectRaw("COALESCE(SUM({$netTotal}), 0) as revenue")
            ->selectRaw("SUM(CASE WHEN transactions.status != 'voided' THEN 1 ELSE 0 END) as transaction_count")
            ->first();
    }

    public function salesTrend(CarbonInterface $from, CarbonInterface $to): Collection
    {
        [$start, $end] = $this->dateBounds($from, $to);
        $refunds = DB::table('refunds')
            ->selectRaw('transaction_id, SUM(refund_amount) as refunded_amount')
            ->groupBy('transaction_id');
        $netTotal = $this->netTransactionTotalSql();

        $totals = Transaction::query()
            ->leftJoinSub($refunds, 'refund_totals', fn ($join) => $join->on('refund_totals.transaction_id', '=', 'transactions.id'))
            ->whereBetween('transactions.date', [$start, $end])
            ->groupBy('transactions.date')
            ->orderBy('transactions.date')
            ->selectRaw('transactions.date as sale_date')
            ->selectRaw("COALESCE(SUM({$netTotal}), 0) as revenue")
            ->get()
            ->mapWithKeys(fn ($row) => [
                Carbon::parse($row->sale_date)->toDateString() => (float) $row->revenue,
            ]);

        return collect(range(0, $from->diffInDays($to)))
            ->map(function (int $offset) use ($from, $totals) {
                $date = $from->copy()->addDays($offset);

                return [
                    'date' => $date->toDateString(),
                    'label' => $date->format('M d'),
                    'total' => round((float) ($totals[$date->toDateString()] ?? 0), 2),
                ];
            });
    }

    public function topProducts(CarbonInterface $from, CarbonInterface $to, int $limit = 5): Collection
    {
        [$start, $end] = $this->dateBounds($from, $to);
        $refunds = DB::table('refund_items')
            ->selectRaw('transaction_item_id, SUM(quantity) as refunded_quantity')
            ->groupBy('transaction_item_id');
        $netUnits = $this->netItemUnitsSql();

        return TransactionItem::query()
            ->join('transactions', 'transactions.id', '=', 'transaction_items.transaction_id')
            ->leftJoinSub($refunds, 'refunded_items', fn ($join) => $join->on('refunded_items.transaction_item_id', '=', 'transaction_items.id'))
            ->whereBetween('transactions.date', [$start, $end])
            ->where('transactions.status', '!=', 'voided')
            ->groupBy('transaction_items.product_name_snapshot', 'transaction_items.product_name')
            ->selectRaw("COALESCE(transaction_items.product_name_snapshot, transaction_items.product_name, 'Unknown item') as product_name")
            ->selectRaw("SUM({$netUnits}) as units_sold")
            ->selectRaw("SUM({$netUnits} * transaction_items.unit_price) as revenue")
            ->orderByDesc('units_sold')
            ->limit($limit)
            ->get();
    }

    public function categoryPerformance(CarbonInterface $from, CarbonInterface $to, int $limit = 5): Collection
    {
        [$start, $end] = $this->dateBounds($from, $to);
        $refunds = DB::table('refund_items')
            ->selectRaw('transaction_item_id, SUM(quantity) as refunded_quantity')
            ->groupBy('transaction_item_id');
        $netUnits = $this->netItemUnitsSql();

        $categories = TransactionItem::query()
            ->join('transactions', 'transactions.id', '=', 'transaction_items.transaction_id')
            ->leftJoin('products', 'products.id', '=', 'transaction_items.product_id')
            ->leftJoin('product_categories', 'product_categories.id', '=', 'products.category_id')
            ->leftJoinSub($refunds, 'refunded_items', fn ($join) => $join->on('refunded_items.transaction_item_id', '=', 'transaction_items.id'))
            ->whereBetween('transactions.date', [$start, $end])
            ->where('transactions.status', '!=', 'voided')
            ->groupBy('product_categories.name')
            ->selectRaw("COALESCE(product_categories.name, 'Uncategorized') as category_name")
            ->selectRaw("SUM({$netUnits} * transaction_items.unit_price) as revenue")
            ->selectRaw("SUM({$netUnits}) as units_sold")
            ->orderByDesc('revenue')
            ->get();

        if ($categories->count() <= $limit) {
            return $categories;
        }

        $visible = $categories->take($limit)->values();
        $others = $categories->skip($limit);
        $visible->push((object) [
            'category_name' => 'Others',
            'revenue' => $others->sum('revenue'),
            'units_sold' => $others->sum('units_sold'),
        ]);

        return $visible;
    }

    public function cashierPerformance(CarbonInterface $from, CarbonInterface $to, int $limit = 5): Collection
    {
        [$start, $end] = $this->dateBounds($from, $to);
        $refunds = DB::table('refunds')
            ->selectRaw('transaction_id, SUM(refund_amount) as refunded_amount')
            ->groupBy('transaction_id');
        $netTotal = $this->netTransactionTotalSql();

        return Transaction::query()
            ->leftJoin('users', 'users.id', '=', 'transactions.cashier_id')
            ->leftJoinSub($refunds, 'refund_totals', fn ($join) => $join->on('refund_totals.transaction_id', '=', 'transactions.id'))
            ->whereBetween('transactions.date', [$start, $end])
            ->where('transactions.status', '!=', 'voided')
            ->groupBy('transactions.cashier_id', 'users.name')
            ->selectRaw("COALESCE(users.name, 'Unassigned') as cashier_name")
            ->selectRaw('COUNT(transactions.id) as transaction_count')
            ->selectRaw("SUM({$netTotal}) as revenue")
            ->orderByDesc('revenue')
            ->limit($limit)
            ->get();
    }

    public function inventorySummary(CarbonInterface $monthStart, CarbonInterface $today): array
    {
        $stock = InventoryStock::query()
            ->join('product_variants', 'product_variants.id', '=', 'inventory_stocks.variant_id')
            ->selectRaw('COALESCE(SUM(inventory_stocks.quantity_on_hand), 0) as total_quantity')
            ->selectRaw('COALESCE(SUM(inventory_stocks.quantity_on_hand * product_variants.cost_price), 0) as inventory_value')
            ->selectRaw('SUM(CASE WHEN inventory_stocks.quantity_on_hand <= inventory_stocks.minimum_stock THEN 1 ELSE 0 END) as low_count')
            ->selectRaw('SUM(CASE WHEN inventory_stocks.quantity_on_hand <= 0 THEN 1 ELSE 0 END) as out_count')
            ->first();

        return [
            'products' => Product::where('status', 'active')->count(),
            'variants' => ProductVariant::where('status', 'active')->count(),
            'quantity' => (int) ($stock?->total_quantity ?? 0),
            'value' => (float) ($stock?->inventory_value ?? 0),
            'low' => (int) ($stock?->low_count ?? 0),
            'out' => (int) ($stock?->out_count ?? 0),
            'received_this_month' => (int) StockReceiptItem::query()
                ->join('stock_receipts', 'stock_receipts.id', '=', 'stock_receipt_items.receipt_id')
                ->whereBetween('stock_receipts.date_received', [$monthStart->toDateString(), $today->toDateString()])
                ->sum('stock_receipt_items.quantity_received'),
            'movements_today' => InventoryMovement::whereDate('created_at', $today->toDateString())->count(),
        ];
    }

    private function netTransactionTotalSql(): string
    {
        return "CASE WHEN transactions.status = 'voided' THEN 0 ELSE CASE WHEN transactions.total - COALESCE(refund_totals.refunded_amount, 0) > 0 THEN transactions.total - COALESCE(refund_totals.refunded_amount, 0) ELSE 0 END END";
    }

    private function netItemUnitsSql(): string
    {
        return 'CASE WHEN transaction_items.quantity > COALESCE(refunded_items.refunded_quantity, 0) THEN transaction_items.quantity - COALESCE(refunded_items.refunded_quantity, 0) ELSE 0 END';
    }

    private function dateBounds(CarbonInterface $from, CarbonInterface $to): array
    {
        return [
            $from->copy()->startOfDay()->toDateTimeString(),
            $to->copy()->endOfDay()->toDateTimeString(),
        ];
    }
}
