<?php

namespace App\Services;

use App\Models\InventoryMovement;
use App\Models\InventoryStock;
use App\Models\PaymentMethod;
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

        $calendarDays = (int) $from->copy()->startOfDay()->diffInDays($to->copy()->startOfDay());

        return collect(range(0, $calendarDays))
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
        return $this->netItemPerformanceRows($from, $to)
            ->groupBy('aggregation_key')
            ->map(function (Collection $rows) {
                $first = $rows->first();

                return (object) [
                    'product_name' => $first->product_name,
                    'identifier' => $first->identifier,
                    'units_sold' => (int) $rows->sum('net_units'),
                    'revenue' => round((float) $rows->sum('allocated_revenue'), 2),
                ];
            })
            ->filter(fn ($row) => $row->units_sold > 0 || $row->revenue > 0)
            ->sort(function ($left, $right) {
                return $right->units_sold <=> $left->units_sold
                    ?: $right->revenue <=> $left->revenue
                    ?: strcasecmp($left->product_name, $right->product_name);
            })
            ->take($limit)
            ->values();
    }

    public function categoryPerformance(CarbonInterface $from, CarbonInterface $to, int $limit = 5): Collection
    {
        $categories = $this->netItemPerformanceRows($from, $to)
            ->groupBy('category_name')
            ->map(fn (Collection $rows, string $category) => (object) [
                'category_name' => $category,
                'revenue' => round((float) $rows->sum('allocated_revenue'), 2),
                'units_sold' => (int) $rows->sum('net_units'),
            ])
            ->filter(fn ($row) => $row->units_sold > 0 || $row->revenue > 0)
            ->sortByDesc('revenue')
            ->values();

        $reportedRevenue = round((float) $categories->sum('revenue'), 2);
        $authoritativeRevenue = round((float) $this->salesSummary($from, $to)->revenue, 2);
        $unallocated = round($authoritativeRevenue - $reportedRevenue, 2);
        if (abs($unallocated) >= 0.01) {
            if ($unallocated > 0) {
                $categories->push((object) [
                    'category_name' => 'Uncategorized / unallocated',
                    'revenue' => $unallocated,
                    'units_sold' => 0,
                ]);
            } elseif ($categories->isNotEmpty()) {
                $categories->first()->revenue = round(max(0, $categories->first()->revenue + $unallocated), 2);
            }
            $categories = $categories->sortByDesc('revenue')->values();
        }

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

    public function salesAssociatePerformance(CarbonInterface $from, CarbonInterface $to, int $limit = 5): Collection
    {
        [$start, $end] = $this->dateBounds($from, $to);
        $refunds = DB::table('refunds')
            ->selectRaw('transaction_id, SUM(refund_amount) as refunded_amount')
            ->groupBy('transaction_id');
        $netTotal = $this->netTransactionTotalSql();

        return Transaction::query()
            ->leftJoinSub($refunds, 'refund_totals', fn ($join) => $join->on('refund_totals.transaction_id', '=', 'transactions.id'))
            ->whereBetween('transactions.date', [$start, $end])
            ->where('transactions.status', '!=', 'voided')
            ->whereNotNull('transactions.sales_associate')
            ->where('transactions.sales_associate', '!=', '')
            ->groupBy('transactions.sales_associate')
            ->selectRaw('transactions.sales_associate as sales_associate_name')
            ->selectRaw('COUNT(transactions.id) as transaction_count')
            ->selectRaw("SUM({$netTotal}) as revenue")
            ->orderByDesc('revenue')
            ->limit($limit)
            ->get();
    }

    public function paymentMethodPerformance(CarbonInterface $from, CarbonInterface $to): Collection
    {
        [$start, $end] = $this->dateBounds($from, $to);
        $refunds = DB::table('refunds')
            ->selectRaw('transaction_id, SUM(refund_amount) as refunded_amount')
            ->groupBy('transaction_id');
        $netTotal = $this->netTransactionTotalSql();

        return Transaction::query()
            ->leftJoin('payment_methods', 'payment_methods.id', '=', 'transactions.payment_method_id')
            ->leftJoinSub($refunds, 'refund_totals', fn ($join) => $join->on('refund_totals.transaction_id', '=', 'transactions.id'))
            ->whereBetween('transactions.date', [$start, $end])
            ->where('transactions.status', '!=', 'voided')
            ->groupBy('payment_methods.code')
            ->selectRaw("COALESCE(payment_methods.code, '__unlinked__') as payment_code")
            ->selectRaw('COUNT(transactions.id) as transaction_count')
            ->selectRaw("COALESCE(SUM({$netTotal}), 0) as revenue")
            ->orderByDesc('revenue')
            ->get()
            ->map(function ($row) {
                $row->is_unknown = $row->payment_code === '__unlinked__';
                $row->label = $row->is_unknown
                    ? 'Unknown / Unlinked'
                    : PaymentMethod::labelFor((string) $row->payment_code);
                $row->revenue = (float) $row->revenue;
                $row->transaction_count = (int) $row->transaction_count;

                return $row;
            });
    }

    public function inventorySummary(CarbonInterface $monthStart, CarbonInterface $today): array
    {
        $stock = InventoryStock::query()
            ->join('product_variants', 'product_variants.id', '=', 'inventory_stocks.variant_id')
            ->selectRaw('COALESCE(SUM(inventory_stocks.quantity_on_hand), 0) as total_quantity')
            ->selectRaw('COALESCE(SUM(inventory_stocks.quantity_on_hand * product_variants.cost_price), 0) as inventory_value')
            ->selectRaw('SUM(CASE WHEN inventory_stocks.quantity_on_hand > inventory_stocks.critical_stock AND inventory_stocks.quantity_on_hand <= inventory_stocks.minimum_stock THEN 1 ELSE 0 END) as low_count')
            ->selectRaw('SUM(CASE WHEN inventory_stocks.quantity_on_hand > 0 AND inventory_stocks.quantity_on_hand <= inventory_stocks.critical_stock THEN 1 ELSE 0 END) as critical_count')
            ->selectRaw('SUM(CASE WHEN inventory_stocks.quantity_on_hand <= 0 THEN 1 ELSE 0 END) as out_count')
            ->first();

        return [
            'products' => Product::where('status', 'active')->count(),
            'variants' => ProductVariant::where('status', 'active')->count(),
            'quantity' => (int) ($stock?->total_quantity ?? 0),
            'value' => (float) ($stock?->inventory_value ?? 0),
            'low' => (int) ($stock?->low_count ?? 0),
            'critical' => (int) ($stock?->critical_count ?? 0),
            'out' => (int) ($stock?->out_count ?? 0),
            'attention' => (int) ($stock?->low_count ?? 0)
                + (int) ($stock?->critical_count ?? 0)
                + (int) ($stock?->out_count ?? 0),
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

    private function netItemPerformanceRows(CarbonInterface $from, CarbonInterface $to): Collection
    {
        [$start, $end] = $this->dateBounds($from, $to);
        $refundedItems = DB::table('refund_items')
            ->selectRaw('transaction_item_id, SUM(quantity) as refunded_quantity')
            ->groupBy('transaction_item_id');
        $refundTotals = DB::table('refunds')
            ->selectRaw('transaction_id, SUM(refund_amount) as refunded_amount')
            ->groupBy('transaction_id');
        $netUnits = $this->netItemUnitsSql();
        $netTotal = $this->netTransactionTotalSql();

        $rows = TransactionItem::query()
            ->join('transactions', 'transactions.id', '=', 'transaction_items.transaction_id')
            ->leftJoin('product_variants', 'product_variants.id', '=', 'transaction_items.variant_id')
            ->leftJoin('products', 'products.id', '=', 'transaction_items.product_id')
            ->leftJoin('product_categories', 'product_categories.id', '=', 'products.category_id')
            ->leftJoinSub($refundedItems, 'refunded_items', fn ($join) => $join->on('refunded_items.transaction_item_id', '=', 'transaction_items.id'))
            ->leftJoinSub($refundTotals, 'refund_totals', fn ($join) => $join->on('refund_totals.transaction_id', '=', 'transactions.id'))
            ->whereBetween('transactions.date', [$start, $end])
            ->where('transactions.status', '!=', 'voided')
            ->selectRaw('transaction_items.id as transaction_item_id, transaction_items.transaction_id, transaction_items.variant_id, transaction_items.product_id')
            ->selectRaw("COALESCE(transaction_items.product_name_snapshot, transaction_items.product_name, 'Unknown item') as product_name")
            ->selectRaw('COALESCE(product_variants.variant_code, transaction_items.part_number_snapshot) as identifier')
            ->selectRaw("COALESCE(product_categories.name, 'Uncategorized') as category_name")
            ->selectRaw("{$netUnits} as net_units")
            ->selectRaw("({$netUnits}) * transaction_items.unit_price as remaining_gross")
            ->selectRaw("{$netTotal} as transaction_net")
            ->get();

        return $rows->groupBy('transaction_id')->flatMap(function (Collection $transactionRows) {
            $transactionRows = $transactionRows->values();
            $grossTotal = (float) $transactionRows->sum('remaining_gross');
            $transactionNet = round((float) $transactionRows->first()->transaction_net, 2);
            $allocated = 0.0;
            $lastEligibleIndex = $transactionRows->keys()
                ->filter(fn ($index) => (float) $transactionRows[$index]->remaining_gross > 0)
                ->last();

            return $transactionRows->map(function ($row, int $index) use ($grossTotal, $transactionNet, &$allocated, $lastEligibleIndex) {
                if ($grossTotal <= 0 || $transactionNet <= 0 || (float) $row->remaining_gross <= 0) {
                    $row->allocated_revenue = 0.0;
                } elseif ($index === $lastEligibleIndex) {
                    $row->allocated_revenue = round($transactionNet - $allocated, 2);
                } else {
                    $row->allocated_revenue = round($transactionNet * ((float) $row->remaining_gross / $grossTotal), 2);
                    $allocated += $row->allocated_revenue;
                }

                $row->net_units = (int) $row->net_units;
                $row->identifier = $row->identifier
                    ?: ($row->product_id ? 'Product #'.$row->product_id : 'Legacy item');
                $row->aggregation_key = $row->variant_id
                    ? 'variant:'.$row->variant_id
                    : ($row->product_id
                        ? 'product:'.$row->product_id
                        : 'legacy:'.mb_strtolower(trim($row->product_name.'|'.$row->identifier)));

                return $row;
            });
        })->values();
    }

    private function dateBounds(CarbonInterface $from, CarbonInterface $to): array
    {
        return [
            $from->copy()->startOfDay()->toDateTimeString(),
            $to->copy()->endOfDay()->toDateTimeString(),
        ];
    }
}
