<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\InventoryStock;
use App\Models\ProductVariant;
use App\Models\StockReceipt;
use App\Models\StockReceiptItem;
use App\Models\Supplier;
use App\Services\InventoryMonitoringService;
use App\Support\InputLimits;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class StockReceiptController extends Controller
{
    public function index()
    {
        $receipts = StockReceipt::with('supplier', 'receivedBy')
            ->withCount('items')
            ->withSum('items', 'total_cost')
            ->latest('date_received')
            ->latest('id')
            ->paginate(20);

        return view('stock_receipts.index', compact('receipts'));
    }

    public function create()
    {
        $supplierId = old('supplier_id');
        $variantIds = collect(old('items', []))
            ->pluck('variant_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique();

        return view('stock_receipts.create', [
            'selectedSupplier' => $supplierId
                ? Supplier::query()->select('id', 'name')->find($supplierId)
                : null,
            'selectedVariants' => $this->variantSearchQuery()
                ->whereIn('product_variants.id', $variantIds)
                ->get()
                ->keyBy('id'),
        ]);
    }

    public function searchSuppliers(Request $request)
    {
        $data = $request->validate(['q' => ['nullable', 'string', 'max:100']]);
        $term = trim($data['q'] ?? '');

        $suppliers = Supplier::query()
            ->select('id', 'name', 'contact', 'phone', 'email')
            ->where('status', 'active')
            ->when($term !== '', function ($query) use ($term) {
                $query->where(function ($search) use ($term) {
                    $search->where('name', 'like', "%{$term}%")
                        ->orWhere('contact', 'like', "%{$term}%")
                        ->orWhere('phone', 'like', "%{$term}%")
                        ->orWhere('email', 'like', "%{$term}%");
                });
            })
            ->orderBy('name')
            ->limit(15)
            ->get();

        return response()->json(['data' => $suppliers]);
    }

    public function searchVariants(Request $request)
    {
        $data = $request->validate(['q' => ['nullable', 'string', 'max:100']]);
        $term = trim($data['q'] ?? '');

        $variants = $this->variantSearchQuery()
            ->when($term !== '', function ($query) use ($term) {
                $query->where(function ($search) use ($term) {
                    $search->where('product_variants.variant_code', 'like', "%{$term}%")
                        ->orWhere('product_variants.barcode', 'like', "%{$term}%")
                        ->orWhere('product_variants.part_number', 'like', "%{$term}%")
                        ->orWhereHas('product', fn ($product) => $product->where('name', 'like', "%{$term}%"))
                        ->orWhereHas('brand', fn ($brand) => $brand->where('name', 'like', "%{$term}%"));
                });
            })
            ->orderBy('products.name')
            ->orderBy('product_variants.variant_code')
            ->limit(20)
            ->get()
            ->map(function (ProductVariant $variant) {
                $details = collect([
                    $variant->part_number,
                    $variant->brand?->name,
                    $variant->size,
                    $variant->color,
                    $variant->unit?->code,
                ])->filter()->values();

                return [
                    'id' => $variant->id,
                    'product_name' => $variant->product?->name ?? 'Deleted product',
                    'variant_code' => $variant->variant_code,
                    'details' => $details,
                    'stock' => (int) ($variant->inventoryStock?->quantity_on_hand ?? 0),
                    'cost' => (float) $variant->cost_price,
                ];
            });

        return response()->json(['data' => $variants]);
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules(), $this->messages());

        foreach ($data['items'] as $index => $item) {
            if ((float) $item['quantity_received'] * (float) $item['unit_cost'] > InputLimits::DATABASE_MONEY_MAX) {
                throw ValidationException::withMessages([
                    "items.{$index}.unit_cost" => 'The line total is too large. Reduce the quantity or unit cost.',
                ]);
            }
        }

        $variants = ProductVariant::with('product', 'brand', 'unit', 'inventoryStock')
            ->whereIn('id', collect($data['items'])->pluck('variant_id')->unique())
            ->get()
            ->keyBy('id');

        $receipt = null;

        DB::transaction(function () use ($data, $variants, &$receipt) {
            $receipt = StockReceipt::create([
                'supplier_id' => filled($data['supplier_id'] ?? null) ? (int) $data['supplier_id'] : null,
                'received_by' => auth()->id(),
                'receipt_no' => StockReceipt::generateReceiptNo(),
                'date_received' => $data['date_received'],
                'remarks' => $data['remarks'] ?? null,
            ]);

            $auditItems = [];

            foreach ($data['items'] as $item) {
                $quantity = (int) $item['quantity_received'];
                $unitCost = (float) $item['unit_cost'];
                $totalCost = round($quantity * $unitCost, 2);
                $variant = $variants->get((int) $item['variant_id']);

                if (! $variant) {
                    throw ValidationException::withMessages(['items' => 'One of the selected variants is no longer available.']);
                }

                $receiptItem = StockReceiptItem::create([
                    'receipt_id' => $receipt->id,
                    'variant_id' => $variant->id,
                    'quantity_received' => $quantity,
                    'unit_cost' => $unitCost,
                    'total_cost' => $totalCost,
                ]);

                $variant->update(['cost_price' => $unitCost]);

                $movement = app(InventoryMonitoringService::class)->recordMovement(
                    $variant,
                    'stock_in',
                    $quantity,
                    'stock_receipt',
                    $receipt->id,
                    "Stock received via {$receipt->receipt_no}."
                );

                InventoryStock::where('variant_id', $variant->id)->update([
                    'last_received_at' => $data['date_received'],
                ]);

                $auditItems[] = [
                    'receipt_item_id' => $receiptItem->id,
                    'variant_id' => $variant->id,
                    'variant_code' => $variant->variant_code,
                    'product_name' => $variant->product?->name,
                    'quantity_received' => $quantity,
                    'unit_cost' => $unitCost,
                    'total_cost' => $totalCost,
                    'quantity_before' => $movement->quantity_before,
                    'quantity_after' => $movement->quantity_after,
                ];
            }

            AuditLog::record('STOCK_RECEIPT', "Created stock receipt {$receipt->receipt_no}.", null, [
                'affected_table' => 'stock_receipts',
                'affected_record_id' => $receipt->id,
                'new_values' => [
                    'receipt_no' => $receipt->receipt_no,
                    'supplier_id' => $receipt->supplier_id,
                    'date_received' => $receipt->date_received?->toDateString(),
                    'items' => $auditItems,
                ],
            ]);
        });

        return redirect()
            ->route('stock-receipts.show', $receipt)
            ->with('success', "Stock receipt {$receipt->receipt_no} saved.");
    }

    public function show(StockReceipt $stockReceipt)
    {
        $stockReceipt->load(
            'supplier',
            'receivedBy',
            'items.productVariant.product',
            'items.productVariant.brand',
            'items.productVariant.unit',
            'items.productVariant.inventoryStock'
        );

        return view('stock_receipts.show', ['receipt' => $stockReceipt]);
    }

    private function variantSearchQuery()
    {
        return ProductVariant::query()
            ->with([
                'product:id,name,status',
                'brand:id,name',
                'unit:id,code',
                'inventoryStock:id,variant_id,quantity_on_hand',
            ])
            ->where('product_variants.status', 'active')
            ->whereHas('product', fn ($query) => $query->where('status', 'active'))
            ->join('products', 'products.id', '=', 'product_variants.product_id')
            ->select([
                'product_variants.id', 'product_variants.product_id', 'product_variants.brand_id',
                'product_variants.unit_id', 'product_variants.variant_code', 'product_variants.barcode',
                'product_variants.part_number', 'product_variants.size', 'product_variants.color',
                'product_variants.cost_price',
            ]);
    }

    private function rules(): array
    {
        return [
            'supplier_id' => ['nullable', Rule::exists('suppliers', 'id')->where('status', 'active')],
            'date_received' => ['required', 'date'],
            'remarks' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.variant_id' => [
                'required',
                Rule::exists('product_variants', 'id')->where(fn ($query) => $query
                    ->where('status', 'active')
                    ->whereExists(fn ($products) => $products
                        ->selectRaw('1')
                        ->from('products')
                        ->whereColumn('products.id', 'product_variants.product_id')
                        ->where('products.status', 'active'))),
            ],
            'items.*.quantity_received' => ['required', 'integer', 'min:1', 'max:'.InputLimits::DATABASE_INTEGER_MAX],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0', 'max:'.InputLimits::DATABASE_MONEY_MAX],
        ];
    }

    private function messages(): array
    {
        return [
            'items.required' => 'Add at least one received variant.',
            'items.min' => 'Add at least one received variant.',
            'items.*.variant_id.required' => 'Choose a product variant for every receipt line.',
            'items.*.quantity_received.required' => 'Enter the received quantity for every receipt line.',
            'items.*.quantity_received.min' => 'Received quantity must be at least 1.',
            'items.*.unit_cost.required' => 'Enter the unit cost for every receipt line.',
            'items.*.unit_cost.min' => 'Unit cost cannot be negative.',
        ];
    }
}
