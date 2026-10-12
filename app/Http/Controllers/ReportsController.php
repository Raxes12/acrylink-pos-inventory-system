<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\IntrusionEvent;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\SecurityRule;
use App\Models\Transaction;
use App\Models\TransactionPayment;
use App\Services\IntrusionDetectionService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ReportsController extends Controller
{
    public function index(Request $request)
    {
        $filters = $this->reportFilters($request);
        $range = $filters['range'];
        $query = $this->reportQuery($filters);

        $reportTransactions = (clone $query)->get();
        $transactions = $query
            ->orderByDesc('date')
            ->orderByDesc('time')
            ->paginate(10)
            ->withQueryString();

        $totalRefunds = $reportTransactions->sum(fn (Transaction $transaction) => $transaction->totalRefunded());
        $totalSales = $reportTransactions->sum(fn (Transaction $transaction) => $transaction->netTotal());
        $totalDiscount = $reportTransactions->sum(fn (Transaction $transaction) => $transaction->netDiscount());
        $grossRevenue = $reportTransactions->sum(fn (Transaction $transaction) => $transaction->netSubtotal());
        $avgSale = $reportTransactions->count()
            ? $reportTransactions->avg(fn (Transaction $transaction) => $transaction->netTotal())
            : 0;
        $collectionQuery = $this->applyPaymentDateFilters(TransactionPayment::query(), $filters);
        $totalCollections = (float) (clone $collectionQuery)->sum('amount');
        $salesPeriodCollections = (float) (clone $collectionQuery)
            ->whereHas('transaction', fn ($transactionQuery) => $this->applyDateFilters($transactionQuery, $filters))
            ->sum('amount');
        $priorCreditCollections = round(max(0, $totalCollections - $salesPeriodCollections), 2);
        $totalCreditSales = $reportTransactions
            ->filter(fn (Transaction $transaction) => $transaction->creditSale !== null)
            ->sum(fn (Transaction $transaction) => $transaction->netTotal());

        // Group by date for chart
        $byDate = $reportTransactions
            ->groupBy(fn ($transaction) => $transaction->date->toDateString())
            ->map(fn ($group) => $group->sum(fn (Transaction $transaction) => $transaction->netTotal()))
            ->sortKeys();

        // Group by cashier
        $byCashier = $reportTransactions->groupBy(fn ($t) => $t->cashier?->name ?? 'Unknown')
            ->map(fn ($g) => $g->sum(fn (Transaction $transaction) => $transaction->netTotal()))
            ->sortDesc();

        // Top products
        $topProducts = $reportTransactions
            ->flatMap(function (Transaction $transaction) {
                if ($transaction->status === 'voided') {
                    return collect();
                }

                $refundedByItem = $transaction->refunds
                    ->flatMap->items
                    ->groupBy('transaction_item_id')
                    ->map->sum('quantity');

                return $transaction->items->map(function ($item) use ($refundedByItem) {
                    $remainingUnits = max(0, (int) $item->quantity - (int) ($refundedByItem[$item->id] ?? 0));
                    $itemLabel = $this->transactionItemReportName($item);

                    return [
                        'product_name' => $itemLabel,
                        'units' => $remainingUnits,
                        'revenue' => $remainingUnits * (float) $item->unit_price,
                    ];
                });
            })
            ->groupBy('product_name')
            ->map(fn ($items, $productName) => (object) [
                'product_name' => $productName,
                'units' => $items->sum('units'),
                'revenue' => $items->sum('revenue'),
            ])
            ->filter(fn ($product) => $product->units > 0)
            ->sortByDesc('units')
            ->take(10)
            ->values();

        $blockedReason = $this->monitorReportView($request, $filters, $reportTransactions->count());

        AuditLog::record('REPORT', 'Viewed sales report: '.$this->reportLabel($filters), null, [
            'affected_table' => 'reports',
            'affected_record_id' => $range,
            'new_values' => [
                'range' => $range,
                'date_from' => $filters['date_from'],
                'date_to' => $filters['date_to'],
                'access_status' => $blockedReason ? 'blocked' : 'allowed',
                'transactions' => $reportTransactions->count(),
                'total_sales' => $totalSales,
                'total_refunds' => $totalRefunds,
                'gross_revenue' => $grossRevenue,
                'total_discount' => $totalDiscount,
            ],
        ]);

        if ($blockedReason) {
            abort(403, $blockedReason);
        }

        $dateFrom = $filters['date_from'];
        $dateTo = $filters['date_to'];
        $reportLabel = $this->reportLabel($filters);
        $exportUrl = route('reports.export', array_filter([
            'range' => $range,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
        ], fn ($value) => filled($value)));
        $exportPdfUrl = route('reports.export.pdf', array_filter([
            'range' => $range,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
        ], fn ($value) => filled($value)));

        return view('reports.index', compact(
            'transactions', 'totalSales', 'totalRefunds', 'totalDiscount', 'grossRevenue',
            'avgSale', 'byDate', 'byCashier', 'topProducts', 'range',
            'dateFrom', 'dateTo', 'reportLabel', 'exportUrl', 'exportPdfUrl',
            'totalCollections', 'salesPeriodCollections', 'priorCreditCollections', 'totalCreditSales'
        ));
    }

    public function inventory(Request $request)
    {
        $filters = $this->inventoryFilters($request);
        $search = $filters['search'];
        $category = $filters['category'];
        $stockLevel = $filters['stock_level'];
        $variantQuery = $this->inventoryReportQuery($filters);
        $inventoryMetricsQuery = clone $variantQuery;

        $variants = $variantQuery
            ->orderBy('variant_code')
            ->paginate(10)
            ->withQueryString();

        $inventoryMetrics = $inventoryMetricsQuery
            ->leftJoin('inventory_stocks', 'inventory_stocks.variant_id', '=', 'product_variants.id')
            ->selectRaw('COUNT(*) as total_variants')
            ->selectRaw('COALESCE(SUM(inventory_stocks.quantity_on_hand), 0) as total_stock')
            ->selectRaw('COALESCE(SUM(inventory_stocks.quantity_on_hand * product_variants.cost_price), 0) as total_value')
            ->selectRaw('SUM(CASE WHEN COALESCE(inventory_stocks.quantity_on_hand, 0) <= 0 THEN 1 ELSE 0 END) as out_count')
            ->selectRaw('SUM(CASE WHEN inventory_stocks.quantity_on_hand > 0 AND inventory_stocks.quantity_on_hand <= inventory_stocks.critical_stock THEN 1 ELSE 0 END) as critical_count')
            ->selectRaw('SUM(CASE WHEN inventory_stocks.quantity_on_hand > inventory_stocks.critical_stock AND inventory_stocks.quantity_on_hand <= inventory_stocks.minimum_stock THEN 1 ELSE 0 END) as low_count')
            ->first();
        $totalVariants = (int) ($inventoryMetrics?->total_variants ?? 0);
        $totalStock = (int) ($inventoryMetrics?->total_stock ?? 0);
        $inventoryValue = (float) ($inventoryMetrics?->total_value ?? 0);
        $outOfStock = (int) ($inventoryMetrics?->out_count ?? 0);
        $criticalStock = (int) ($inventoryMetrics?->critical_count ?? 0);
        $lowStock = (int) ($inventoryMetrics?->low_count ?? 0);

        $categories = Product::categories();
        $exportParameters = array_filter($filters, fn ($value) => filled($value));
        $exportUrl = route('reports.inventory.export', $exportParameters);
        $exportPdfUrl = route('reports.inventory.export.pdf', $exportParameters);

        $blockedReason = $this->monitorReportView($request, [
            'range' => 'inventory', 'date_from' => null, 'date_to' => null,
        ], $variants->total());

        AuditLog::record('REPORT', 'Viewed inventory report', null, [
            'affected_table' => 'reports',
            'affected_record_id' => 'inventory',
            'new_values' => [
                'search' => $search,
                'category' => $category,
                'stock_level' => $stockLevel,
                'variants_showing' => $variants->total(),
                'access_status' => $blockedReason ? 'blocked' : 'allowed',
            ],
        ]);

        if ($blockedReason) {
            abort(403, $blockedReason);
        }

        return view('reports.inventory', compact(
            'variants',
            'categories',
            'totalVariants',
            'totalStock',
            'inventoryValue',
            'outOfStock',
            'criticalStock',
            'lowStock',
            'search',
            'category',
            'stockLevel',
            'exportUrl',
            'exportPdfUrl'
        ));
    }

    public function export(Request $request)
    {
        $filters = $this->reportFilters($request);
        $transactions = $this->reportQuery($filters)
            ->orderBy('date')
            ->orderBy('time')
            ->get();

        $blockedReason = $this->monitorReportExport($request, $filters, $transactions);

        AuditLog::record('REPORT_EXPORT', ($blockedReason ? 'Blocked' : 'Exported').' sales report: '.$this->reportLabel($filters), null, [
            'affected_table' => 'reports',
            'affected_record_id' => $filters['range'],
            'new_values' => [
                'range' => $filters['range'],
                'date_from' => $filters['date_from'],
                'date_to' => $filters['date_to'],
                'export_status' => $blockedReason ? 'blocked' : 'allowed',
                'transactions' => $transactions->count(),
                'date_span_days' => $this->dateSpanDays($filters, $transactions),
            ],
        ]);

        if ($blockedReason) {
            return back()->withErrors(['export' => $blockedReason]);
        }

        $filename = 'sales-report-'.Str::slug($this->reportLabel($filters)).'-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($transactions, $filters) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, [app(\App\Services\SystemSettingsService::class)->get('business.name', 'Acry-Link')]);
            fputcsv($handle, ['SALES REPORT']);
            fputcsv($handle, ['REPORT PERIOD', $this->reportLabel($filters)]);
            fputcsv($handle, ['GENERATED', now()->format('F j, Y g:i A')]);
            fputcsv($handle, ['TRANSACTIONS', $transactions->count()]);
            fputcsv($handle, ['NET SALES', number_format($transactions->sum(fn (Transaction $transaction) => $transaction->netTotal()), 2, '.', '')]);
            fputcsv($handle, []);
            fputcsv($handle, [
                'Receipt No',
                'Date',
                'Time',
                'Cashier',
                'Payment Method',
                'Status',
                'Original Subtotal',
                'Original Discount',
                'Original Total',
                'Refunded',
                'Net Total',
            ]);

            foreach ($transactions as $transaction) {
                fputcsv($handle, [
                    $this->csvCell($transaction->receipt_no),
                    optional($transaction->date)->format('Y-m-d'),
                    $transaction->time,
                    $this->csvCell($transaction->cashier?->name ?? 'Unknown'),
                    $this->csvCell(Transaction::paymentMethodLabel($transaction->payment_method)),
                    $this->csvCell($transaction->status),
                    (string) $transaction->subtotal,
                    (string) $transaction->discount,
                    (string) $transaction->total,
                    number_format($transaction->totalRefunded(), 2, '.', ''),
                    number_format($transaction->netTotal(), 2, '.', ''),
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function exportPdf(Request $request)
    {
        $filters = $this->reportFilters($request);
        $transactions = $this->reportQuery($filters)->orderBy('date')->orderBy('time')->get();

        if ($response = $this->authorizeReportExport($request, 'sales', $filters, $transactions)) {
            return $response;
        }

        return Pdf::loadView('reports.exports.sales-pdf', [
            'companyName' => app(\App\Services\SystemSettingsService::class)->get('business.name', 'Acry-Link'),
            'reportLabel' => $this->reportLabel($filters),
            'transactions' => $transactions,
        ])->setPaper('a4', 'landscape')->download(
            'sales-report-'.Str::slug($this->reportLabel($filters)).'-'.now()->format('Ymd-His').'.pdf'
        );
    }

    public function exportInventory(Request $request)
    {
        $filters = $this->inventoryFilters($request);
        $variants = $this->inventoryReportQuery($filters)->orderBy('variant_code')->get();

        if ($response = $this->authorizeInventoryExport($request, $filters, $variants, 'csv')) {
            return $response;
        }

        $filename = 'inventory-report-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($variants, $filters) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, [app(\App\Services\SystemSettingsService::class)->get('business.name', 'Acry-Link')]);
            fputcsv($handle, ['INVENTORY STOCK REPORT']);
            fputcsv($handle, ['DATE AS OF', now()->format('F j, Y')]);
            fputcsv($handle, ['FILTERS', $this->inventoryFilterLabel($filters)]);
            fputcsv($handle, ['VARIANTS', $variants->count()]);
            fputcsv($handle, ['TOTAL ON HAND', $variants->sum(fn (ProductVariant $variant) => (int) ($variant->inventoryStock?->quantity_on_hand ?? 0))]);
            fputcsv($handle, ['INVENTORY VALUE', number_format($variants->sum(fn (ProductVariant $variant) => (float) $variant->cost_price * (int) ($variant->inventoryStock?->quantity_on_hand ?? 0)), 2, '.', '')]);
            fputcsv($handle, []);
            fputcsv($handle, ['Product Name', 'Variant Code', 'Part Number', 'Brand', 'Category', 'UOM', 'Supplier', 'Cost Price', 'Total Cost', 'Selling Price', 'On Hand', 'Minimum', 'Critical', 'Stock Status']);

            foreach ($variants as $variant) {
                $quantity = (int) ($variant->inventoryStock?->quantity_on_hand ?? 0);
                fputcsv($handle, [
                    $this->csvCell($variant->product?->name ?? 'Deleted product'),
                    $this->csvCell($variant->variant_code),
                    $this->csvCell($variant->part_number),
                    $this->csvCell($variant->brand?->name),
                    $this->csvCell($variant->product?->productCategory?->name ?? $variant->product?->category),
                    $this->csvCell($variant->unit?->code),
                    $this->csvCell($variant->supplier?->name),
                    number_format((float) $variant->cost_price, 2, '.', ''),
                    number_format((float) $variant->cost_price * $quantity, 2, '.', ''),
                    number_format((float) $variant->selling_price, 2, '.', ''),
                    $quantity,
                    (int) ($variant->inventoryStock?->minimum_stock ?? 0),
                    (int) ($variant->inventoryStock?->critical_stock ?? 0),
                    $this->inventoryStatus($variant),
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function exportInventoryPdf(Request $request)
    {
        $filters = $this->inventoryFilters($request);
        $variants = $this->inventoryReportQuery($filters)->orderBy('variant_code')->get();

        if ($response = $this->authorizeInventoryExport($request, $filters, $variants, 'pdf')) {
            return $response;
        }

        return Pdf::loadView('reports.exports.inventory-pdf', [
            'companyName' => app(\App\Services\SystemSettingsService::class)->get('business.name', 'Acry-Link'),
            'filterLabel' => $this->inventoryFilterLabel($filters),
            'variants' => $variants,
        ])->setPaper('a4', 'landscape')->download('inventory-report-'.now()->format('Ymd-His').'.pdf');
    }

    private function reportFilters(Request $request): array
    {
        $data = $request->validate([
            'range' => 'nullable|in:all,year,month,week,today,custom',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
        ]);

        $dateFrom = $data['date_from'] ?? null;
        $dateTo = $data['date_to'] ?? null;
        $range = $data['range'] ?? 'all';

        if ($dateFrom || $dateTo) {
            $range = 'custom';
        }

        return [
            'range' => $range,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
        ];
    }

    private function inventoryFilters(Request $request): array
    {
        $data = $request->validate([
            'search' => 'nullable|string|max:100',
            'category' => 'nullable|string|max:100',
            'stock_level' => 'nullable|in:attention,normal,low,critical,out',
        ]);

        return [
            'search' => $data['search'] ?? null,
            'category' => $data['category'] ?? null,
            'stock_level' => $data['stock_level'] ?? null,
        ];
    }

    private function inventoryReportQuery(array $filters)
    {
        return ProductVariant::with('product.productCategory', 'brand', 'unit', 'supplier', 'inventoryStock')
            ->whereHas('product')
            ->when($filters['search'], function ($query, string $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('variant_code', 'like', "%{$search}%")
                        ->orWhere('barcode', 'like', "%{$search}%")
                        ->orWhere('part_number', 'like', "%{$search}%")
                        ->orWhere('size', 'like', "%{$search}%")
                        ->orWhere('color', 'like', "%{$search}%")
                        ->orWhereHas('product', fn ($productQuery) => $productQuery->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('brand', fn ($brandQuery) => $brandQuery->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('supplier', fn ($supplierQuery) => $supplierQuery->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($filters['category'], function ($query, string $category) {
                $query->whereHas('product.productCategory', fn ($categoryQuery) => $categoryQuery->where('name', $category));
            })
            ->when($filters['stock_level'], function ($query, string $stockLevel) {
                $query->whereHas('inventoryStock', function ($stockQuery) use ($stockLevel) {
                    match ($stockLevel) {
                        'attention' => $stockQuery->where(function ($attention) {
                            $attention->where('quantity_on_hand', '<=', 0)
                                ->orWhereColumn('quantity_on_hand', '<=', 'critical_stock')
                                ->orWhereColumn('quantity_on_hand', '<=', 'minimum_stock');
                        }),
                        'out' => $stockQuery->where('quantity_on_hand', '<=', 0),
                        'critical' => $stockQuery->where('quantity_on_hand', '>', 0)
                            ->whereColumn('quantity_on_hand', '<=', 'critical_stock'),
                        'low' => $stockQuery->whereColumn('quantity_on_hand', '>', 'critical_stock')
                            ->whereColumn('quantity_on_hand', '<=', 'minimum_stock'),
                        default => $stockQuery->whereColumn('quantity_on_hand', '>', 'minimum_stock'),
                    };
                });
            });
    }

    private function inventoryStatus(ProductVariant $variant): string
    {
        $quantity = (int) ($variant->inventoryStock?->quantity_on_hand ?? 0);
        $minimum = (int) ($variant->inventoryStock?->minimum_stock ?? 0);
        $critical = (int) ($variant->inventoryStock?->critical_stock ?? 0);

        return $quantity <= 0 ? 'Out of Stock' : ($quantity <= $critical ? 'Critical' : ($quantity <= $minimum ? 'Low Stock' : 'Normal'));
    }

    private function inventoryFilterLabel(array $filters): string
    {
        $labels = collect([
            $filters['search'] ? 'Search: '.$filters['search'] : null,
            $filters['category'] ? 'Category: '.$filters['category'] : null,
            $filters['stock_level'] ? 'Stock: '.Str::headline($filters['stock_level']) : null,
        ])->filter();

        return $labels->isEmpty() ? 'All inventory records' : $labels->join(' | ');
    }

    private function csvCell(?string $value): string
    {
        $value ??= '';

        return preg_match('/^[=+\-@]/', $value) ? "'".$value : $value;
    }

    private function authorizeReportExport(Request $request, string $format, array $filters, Collection $transactions)
    {
        $blockedReason = $this->monitorReportExport($request, $filters, $transactions);

        AuditLog::record('REPORT_EXPORT', ($blockedReason ? 'Blocked' : 'Exported').' sales '.$format.' report: '.$this->reportLabel($filters), null, [
            'affected_table' => 'reports',
            'affected_record_id' => $filters['range'],
            'new_values' => [
                'format' => $format,
                'export_status' => $blockedReason ? 'blocked' : 'allowed',
                'transactions' => $transactions->count(),
            ],
        ]);

        return $blockedReason ? back()->withErrors(['export' => $blockedReason]) : null;
    }

    public function authorizeReceivableReportView(Request $request, array $filters, int $recordCount): void
    {
        $securityFilters = [
            'range' => 'custom',
            'date_from' => $filters['transaction_from'] ?? null,
            'date_to' => $filters['transaction_to'] ?? null,
        ];
        $blockedReason = $this->monitorReportView($request, $securityFilters, $recordCount);
        AuditLog::record('REPORT', 'Viewed Accounts Receivable report', null, [
            'affected_table' => 'reports',
            'affected_record_id' => 'accounts_receivable',
            'new_values' => [
                'filters' => $filters,
                'records' => $recordCount,
                'access_status' => $blockedReason ? 'blocked' : 'allowed',
            ],
        ]);

        if ($blockedReason) {
            abort(403, $blockedReason);
        }
    }

    public function authorizeReceivableReportExport(Request $request, string $format, array $filters, Collection $transactions)
    {
        $securityFilters = [
            'range' => 'custom',
            'date_from' => $filters['transaction_from'] ?? null,
            'date_to' => $filters['transaction_to'] ?? null,
        ];
        $blockedReason = $this->monitorReportExport($request, $securityFilters, $transactions);
        AuditLog::record('REPORT_EXPORT', ($blockedReason ? 'Blocked' : 'Exported').' Accounts Receivable '.$format.' report', null, [
            'affected_table' => 'reports',
            'affected_record_id' => 'accounts_receivable',
            'new_values' => [
                'format' => $format,
                'filters' => $filters,
                'records' => $transactions->count(),
                'export_status' => $blockedReason ? 'blocked' : 'allowed',
            ],
        ]);

        return $blockedReason ? back()->withErrors(['export' => $blockedReason]) : null;
    }

    private function authorizeInventoryExport(Request $request, array $filters, Collection $variants, string $format)
    {
        $blockedReason = null;
        $rule = app(IntrusionDetectionService::class)->ruleFor('bulk_data_export');

        if ($rule !== false) {
            $threshold = $rule instanceof SecurityRule ? max(1, (int) $rule->thresholdOr(3)) : 3;
            $window = $rule instanceof SecurityRule ? $rule->windowOr(10) : 10;
            $exportCount = $this->recentAuditCount('REPORT_EXPORT', $window) + 1;

            if ($exportCount >= $threshold) {
                $blockedReason = $this->recordReportAnomaly(
                    $request,
                    $rule,
                    'bulk_data_export',
                    "Repeated report export behavior detected. {$exportCount} export attempts were made within {$window} minutes.",
                    ['range' => 'inventory', 'date_from' => null, 'date_to' => null],
                    $variants->count(),
                    'high',
                    'notify'
                );
            }
        }

        AuditLog::record('REPORT_EXPORT', ($blockedReason ? 'Blocked' : 'Exported').' inventory '.$format.' report', null, [
            'affected_table' => 'reports',
            'affected_record_id' => 'inventory',
            'new_values' => [
                'format' => $format,
                'export_status' => $blockedReason ? 'blocked' : 'allowed',
                'records' => $variants->count(),
                'filters' => $filters,
            ],
        ]);

        return $blockedReason ? back()->withErrors(['export' => $blockedReason]) : null;
    }

    private function reportQuery(array $filters)
    {
        return $this->applyDateFilters(
            Transaction::with('cashier', 'items', 'paymentMethod', 'refunds.items', 'creditSale'),
            $filters
        );
    }

    private function applyDateFilters($query, array $filters)
    {
        $today = now()->toDateString();
        $weekStart = now()->startOfWeek()->toDateString();
        $monthStart = now()->startOfMonth()->toDateString();
        $yearStart = now()->startOfYear()->toDateString();

        return $query
            ->when($filters['range'] === 'today', fn ($q) => $q->whereDate('date', $today))
            ->when($filters['range'] === 'week', fn ($q) => $q->whereBetween('date', [$weekStart, $today]))
            ->when($filters['range'] === 'month', fn ($q) => $q->whereBetween('date', [$monthStart, $today]))
            ->when($filters['range'] === 'year', fn ($q) => $q->whereBetween('date', [$yearStart, $today]))
            ->when($filters['range'] === 'custom' && $filters['date_from'], fn ($q) => $q->whereDate('date', '>=', $filters['date_from']))
            ->when($filters['range'] === 'custom' && $filters['date_to'], fn ($q) => $q->whereDate('date', '<=', $filters['date_to']));
    }

    private function applyPaymentDateFilters($query, array $filters)
    {
        $today = now()->toDateString();
        $weekStart = now()->startOfWeek()->toDateString();
        $monthStart = now()->startOfMonth()->toDateString();
        $yearStart = now()->startOfYear()->toDateString();

        return $query
            ->when($filters['range'] === 'today', fn ($q) => $q->whereDate('paid_at', $today))
            ->when($filters['range'] === 'week', fn ($q) => $q->whereDate('paid_at', '>=', $weekStart)->whereDate('paid_at', '<=', $today))
            ->when($filters['range'] === 'month', fn ($q) => $q->whereDate('paid_at', '>=', $monthStart)->whereDate('paid_at', '<=', $today))
            ->when($filters['range'] === 'year', fn ($q) => $q->whereDate('paid_at', '>=', $yearStart)->whereDate('paid_at', '<=', $today))
            ->when($filters['range'] === 'custom' && $filters['date_from'], fn ($q) => $q->whereDate('paid_at', '>=', $filters['date_from']))
            ->when($filters['range'] === 'custom' && $filters['date_to'], fn ($q) => $q->whereDate('paid_at', '<=', $filters['date_to']));
    }

    private function reportLabel(array $filters): string
    {
        $today = now()->startOfDay();

        if ($filters['range'] === 'today') {
            return $today->format('F j, Y');
        }

        if ($filters['range'] === 'week') {
            return $this->formatReportDateSpan($today->copy()->startOfWeek(), $today);
        }

        if ($filters['range'] === 'month') {
            return $today->format('F Y');
        }

        if ($filters['range'] === 'year') {
            return $today->format('Y');
        }

        if ($filters['range'] === 'custom') {
            $from = filled($filters['date_from']) ? Carbon::parse($filters['date_from'])->startOfDay() : null;
            $to = filled($filters['date_to']) ? Carbon::parse($filters['date_to'])->startOfDay() : null;

            if ($from && $to) {
                return $this->formatReportDateSpan($from, $to);
            }

            if ($from) {
                return 'From '.$from->format('F j, Y');
            }

            return 'Through '.($to ?: $today)->format('F j, Y');
        }

        return 'All records through '.$today->format('F j, Y');
    }

    private function formatReportDateSpan(Carbon $from, Carbon $to): string
    {
        if ($from->isSameDay($to)) {
            return $from->format('F j, Y');
        }

        if ($from->isSameMonth($to)) {
            return $from->format('F j').'–'.$to->format('j, Y');
        }

        if ($from->year === $to->year) {
            return $from->format('F j').'–'.$to->format('F j, Y');
        }

        return $from->format('F j, Y').'–'.$to->format('F j, Y');
    }

    private function transactionItemReportName($item): string
    {
        $productName = $item->product_name_snapshot ?: $item->product_name ?: 'Unknown item';
        $details = collect([
            $item->part_number_snapshot,
            $item->brand_snapshot,
            $item->model_snapshot,
            $item->size_snapshot,
            $item->color_snapshot,
            $item->unit_snapshot ? 'Unit: '.$item->unit_snapshot : null,
        ])->filter()->join(' / ');

        return $details ? "{$productName} ({$details})" : $productName;
    }

    private function monitorReportView(Request $request, array $filters, int $recordCount): ?string
    {
        $rule = app(IntrusionDetectionService::class)->ruleFor('report_access_limit');

        if ($rule === false) {
            return null;
        }

        $threshold = $rule instanceof SecurityRule
            ? max(1, (int) $rule->thresholdOr(config('security.reports.repeated_access_threshold', 10)))
            : max(1, (int) config('security.reports.repeated_access_threshold', 10));
        $window = $rule instanceof SecurityRule
            ? $rule->windowOr((int) config('security.reports.repeated_access_window_minutes', 10))
            : (int) config('security.reports.repeated_access_window_minutes', 10);

        $accessCount = $this->recentAuditCount('REPORT', $window) + 1;

        if ($accessCount < $threshold) {
            return null;
        }

        return $this->recordReportAnomaly(
            $request,
            $rule,
            'report_access_limit',
            'Repeated sensitive report access detected. '
                .'User '.auth()->user()?->username." viewed reports {$accessCount} times within {$window} minutes.",
            $filters,
            $recordCount,
            'medium',
            'alert'
        );
    }

    private function monitorReportExport(Request $request, array $filters, Collection $transactions): ?string
    {
        $blockedReason = null;
        $exportRule = app(IntrusionDetectionService::class)->ruleFor('bulk_data_export');

        if ($exportRule !== false) {
            $threshold = $exportRule instanceof SecurityRule ? max(1, (int) $exportRule->thresholdOr(3)) : 3;
            $window = $exportRule instanceof SecurityRule ? $exportRule->windowOr(10) : 10;
            $exportCount = $this->recentAuditCount('REPORT_EXPORT', $window) + 1;

            if ($exportCount >= $threshold) {
                $reason = $this->recordReportAnomaly(
                    $request,
                    $exportRule,
                    'bulk_data_export',
                    "Repeated report export behavior detected. {$exportCount} export attempts were made within {$window} minutes.",
                    $filters,
                    $transactions->count(),
                    'high',
                    'notify'
                );
                $blockedReason ??= $reason;
            }
        }

        $rangeRule = app(IntrusionDetectionService::class)->ruleFor('large_report_date_range');

        if ($rangeRule !== false) {
            $spanDays = $this->dateSpanDays($filters, $transactions);
            $largeRangeDays = $rangeRule instanceof SecurityRule
                ? max(1, (int) $rangeRule->thresholdOr(config('security.reports.large_export_days', 31)))
                : max(1, (int) config('security.reports.large_export_days', 31));

            if ($spanDays >= $largeRangeDays) {
                $reason = $this->recordReportAnomaly(
                    $request,
                    $rangeRule,
                    'large_report_date_range',
                    "Large report export range detected. The export spans {$spanDays} days, reaching the {$largeRangeDays}-day threshold.",
                    $filters,
                    $transactions->count(),
                    'high',
                    'notify'
                );
                $blockedReason ??= $reason;
            }
        }

        return $blockedReason;
    }

    private function recentAuditCount(string $action, int $windowMinutes): int
    {
        return AuditLog::where('action', $action)
            ->when(
                Schema::hasColumn('audit_logs', 'affected_table'),
                fn ($query) => $query->where('affected_table', 'reports')
            )
            ->where('user_id', auth()->id())
            ->where('ip_address', request()->ip())
            ->where('timestamp', '>=', now()->subMinutes(max(1, $windowMinutes)))
            ->count();
    }

    private function recordReportAnomaly(
        Request $request,
        SecurityRule|false|null $rule,
        string $eventType,
        string $description,
        array $filters,
        int $recordCount,
        string $fallbackSeverity,
        string $fallbackAction
    ): ?string {
        $ids = app(IntrusionDetectionService::class);
        $securityRule = $rule instanceof SecurityRule ? $rule : null;
        $window = $securityRule?->windowOr(10) ?? 10;
        $action = $securityRule?->action ?? $fallbackAction;
        $severity = $securityRule?->severity ?? $fallbackSeverity;
        $user = auth()->user();

        $recentEventExists = IntrusionEvent::where('event_type', $eventType)
            ->where('source_module', 'reports')
            ->where('user_id', $user?->id)
            ->where('ip_address', $request->ip())
            ->when($securityRule, fn ($query) => $query->where('rule_id', $securityRule->security_rule_id))
            ->where('detected_at', '>=', now()->subMinutes(max(1, $window)))
            ->exists();

        if (! $recentEventExists) {
            $ids->createIntrusionEvent(
                $eventType,
                $severity,
                $description,
                $request,
                $user,
                $securityRule,
                'reports',
                [
                    'request_path' => '/'.ltrim($request->path(), '/'),
                    'request_method' => Str::upper($request->method()),
                    'user_role' => $user?->roleLabel(),
                    'required_roles' => 'Admin, Inventory Staff',
                ]
            );
        }

        AuditLog::record('REPORT_ALERT', $description, $user, [
            'affected_table' => 'reports',
            'affected_record_id' => $filters['range'],
            'new_values' => [
                'event_type' => $eventType,
                'rule_name' => $securityRule?->rule_name,
                'range' => $filters['range'],
                'date_from' => $filters['date_from'],
                'date_to' => $filters['date_to'],
                'records' => $recordCount,
                'action' => $action,
            ],
        ]);

        return $action === 'block'
            ? 'Report access blocked by security rule: '.($securityRule?->rule_name ?? $eventType).'.'
            : null;
    }

    private function dateSpanDays(array $filters, Collection $transactions): int
    {
        if ($filters['range'] === 'today') {
            return 1;
        }

        if ($filters['range'] === 'week') {
            return 7;
        }

        if ($filters['range'] === 'month') {
            return now()->day;
        }

        $minDate = $transactions->min('date');
        $maxDate = $transactions->max('date');
        $start = $filters['date_from']
            ? Carbon::parse($filters['date_from'])
            : ($minDate ? Carbon::parse($minDate) : null);
        $end = $filters['date_to']
            ? Carbon::parse($filters['date_to'])
            : ($maxDate ? Carbon::parse($maxDate) : null);

        if (! $start || ! $end) {
            return 0;
        }

        return (int) $start->startOfDay()->diffInDays($end->startOfDay()) + 1;
    }
}
