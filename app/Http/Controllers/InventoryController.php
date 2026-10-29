<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Brand;
use App\Models\InventoryMovement;
use App\Models\InventoryStock;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Supplier;
use App\Models\Unit;
use App\Services\InventoryMonitoringService;
use App\Support\CaseInsensitiveSearch;
use App\Support\InputLimits;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $query = $this->variantQuery();

        if ($request->filled('search')) {
            $this->applyVariantSearch($query, $request->search);
        }

        if ($request->filled('category') && $request->category !== 'All') {
            $this->applyCategoryFilter($query, $request->category);
        }

        if (in_array($request->status, ['active', 'inactive'], true)) {
            $query->where('product_variants.status', $request->status);
        }

        $variants = $this->orderVariants($query)->paginate(10)->withQueryString();
        $categories = Product::categories();

        return view('inventory.index', compact('variants', 'categories'));
    }

    public function stockOverview(Request $request)
    {
        $query = $this->variantQuery();

        if ($request->filled('search')) {
            $this->applyVariantSearch($query, $request->search);
        }

        if ($request->filled('category') && $request->category !== 'All') {
            $this->applyCategoryFilter($query, $request->category);
        }

        if ($request->stock_level === 'attention') {
            $query->whereHas('inventoryStock', fn ($stock) => $stock->where(function ($attention) {
                $attention->where('quantity_on_hand', '<=', 0)
                    ->orWhereColumn('quantity_on_hand', '<=', 'critical_stock')
                    ->orWhereColumn('quantity_on_hand', '<=', 'minimum_stock');
            }));
        } elseif ($request->stock_level === 'normal') {
            $query->whereHas('inventoryStock', fn ($stock) => $stock->whereColumn('quantity_on_hand', '>', 'minimum_stock'));
        } elseif ($request->stock_level === 'critical') {
            $query->whereHas('inventoryStock', fn ($stock) => $stock
                ->whereColumn('quantity_on_hand', '<=', 'critical_stock')
                ->where('quantity_on_hand', '>', 0));
        } elseif ($request->stock_level === 'low') {
            $query->whereHas('inventoryStock', fn ($stock) => $stock
                ->whereColumn('quantity_on_hand', '<=', 'minimum_stock')
                ->whereColumn('quantity_on_hand', '>', 'critical_stock')
                ->where('quantity_on_hand', '>', 0));
        } elseif ($request->stock_level === 'out') {
            $query->whereHas('inventoryStock', fn ($stock) => $stock->where('quantity_on_hand', '<=', 0));
        }

        $variants = $this->orderVariants($query)->paginate(10)->withQueryString();
        $categories = Product::categories();
        $stockAlertCounts = Product::stockAlertBreakdown();

        return view('inventory.stock-overview', compact('variants', 'categories', 'stockAlertCounts'));
    }

    public function movements(Request $request)
    {
        $query = InventoryMovement::with(
            'variant.product.productCategory',
            'variant.brand',
            'variant.unit',
            'variant.inventoryStock',
            'user'
        )->latest('created_at')->latest('id');

        if ($request->filled('type')) {
            $query->where('movement_type', $request->type);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $movements = $query->paginate(10)->withQueryString();

        return view('inventory.movements', compact('movements'));
    }

    public function create()
    {
        return view('inventory.form', $this->formOptions());
    }

    public function searchProducts(Request $request)
    {
        $data = $request->validate(['q' => ['nullable', 'string', 'max:100']]);
        $term = trim($data['q'] ?? '');

        $products = Product::query()
            ->select('id', 'name', 'category_id')
            ->with('productCategory:id,name')
            ->where('status', 'active')
            ->when($term !== '', function ($query) use ($term) {
                $query->where(function ($search) use ($term) {
                    CaseInsensitiveSearch::where($search, 'name', $term)
                        ->orWhereHas('productCategory', fn ($category) => CaseInsensitiveSearch::where($category, 'name', $term));
                });
            })
            ->orderBy('name')
            ->limit(20)
            ->get()
            ->map(fn (Product $product) => [
                'id' => $product->id,
                'name' => $product->name,
                'category' => $product->productCategory?->name,
            ]);

        return response()->json(['data' => $products]);
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
                    CaseInsensitiveSearch::where($search, 'name', $term);
                    CaseInsensitiveSearch::orWhere($search, 'contact', $term);
                    CaseInsensitiveSearch::orWhere($search, 'phone', $term);
                    CaseInsensitiveSearch::orWhere($search, 'email', $term);
                });
            })
            ->orderBy('name')
            ->limit(15)
            ->get();

        return response()->json(['data' => $suppliers]);
    }

    public function edit(ProductVariant $variant)
    {
        $variant->load('product.productCategory', 'brand', 'unit', 'supplier', 'inventoryStock');

        return view('inventory.form', array_merge($this->formOptions(), compact('variant')));
    }

    public function show(ProductVariant $variant)
    {
        $variant->load('product.productCategory', 'brand', 'unit', 'supplier', 'inventoryStock');

        return view('inventory.show', compact('variant'));
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->variantRules($request), $this->variantMessages());
        $data['status'] = 'active';
        $data['image_path'] = $this->storeVariantImage($request);
        $variant = null;

        DB::transaction(function () use ($data, &$variant) {
            $product = $this->productForStore($data);
            $variant = $this->saveVariant(new ProductVariant, $product, $data);
            $this->saveInventoryStock($variant, 0, (int) $data['min_stock'], (int) $data['critical_stock']);

            app(InventoryMonitoringService::class)->recordMovement(
                $variant,
                'stock_in',
                (int) $data['stock'],
                'variant_create',
                $variant->id,
                'Initial variant stock.'
            );

            $variant->load('product.productCategory', 'brand', 'unit', 'supplier', 'inventoryStock');

            AuditLog::record('PRODUCT', "Added variant: {$variant->product->name} ({$variant->variant_code})", null, [
                'affected_table' => 'product_variants',
                'affected_record_id' => $variant->id,
                'new_values' => $this->variantAuditSnapshot($variant),
            ]);
        });

        return redirect()->route('inventory.index')
            ->with('success', "Variant \"{$variant->variant_code}\" added.");
    }

    public function update(Request $request, ProductVariant $variant)
    {
        $data = $request->validate($this->variantRules($request, $variant), $this->variantMessages());
        $oldImagePath = $variant->image_path;
        $data['image_path'] = $this->storeVariantImage($request, $oldImagePath);

        DB::transaction(function () use ($data, $variant) {
            $variant->load('product.productCategory', 'brand', 'unit', 'supplier', 'inventoryStock');
            $oldValues = $this->variantAuditSnapshot($variant);

            $product = $this->productForUpdate($variant, $data);
            $variant = $this->saveVariant($variant, $product, $data);
            $this->saveInventoryStock($variant, null, (int) $data['min_stock'], (int) $data['critical_stock']);

            $variant->load('inventoryStock');
            $stockChange = (int) $data['stock'] - (int) ($variant->inventoryStock?->quantity_on_hand ?? 0);

            if ($stockChange !== 0) {
                app(InventoryMonitoringService::class)->recordMovement(
                    $variant,
                    'adjustment',
                    $stockChange,
                    'variant_update',
                    $variant->id,
                    'Manual stock adjustment from inventory module.'
                );
            } else {
                app(InventoryMonitoringService::class)->syncStockAlert($variant);
            }

            $variant->load('product.productCategory', 'brand', 'unit', 'supplier', 'inventoryStock');

            AuditLog::record('PRODUCT', "Updated variant: {$variant->product->name} ({$variant->variant_code})", null, [
                'affected_table' => 'product_variants',
                'affected_record_id' => $variant->id,
                'old_values' => $oldValues,
                'new_values' => $this->variantAuditSnapshot($variant),
            ]);
        });

        if (($data['image_path'] ?? null) && $oldImagePath && $oldImagePath !== $data['image_path']) {
            Storage::disk('public')->delete($oldImagePath);
        }

        return redirect()->route('inventory.index')
            ->with('success', "Variant \"{$variant->fresh()->variant_code}\" updated.");
    }

    public function adjustStock(Request $request, ProductVariant $variant)
    {
        $data = $request->validate([
            'type' => 'required|in:stock_in,stock_out,adjustment',
            'quantity' => 'required|integer|min:1|max:'.InputLimits::DATABASE_INTEGER_MAX,
            'remarks' => 'nullable|string|max:500',
        ]);

        $variant->load('product');
        $requestedChange = $data['type'] === 'stock_out'
            ? -1 * (int) $data['quantity']
            : (int) $data['quantity'];

        $movement = app(InventoryMonitoringService::class)->recordMovement(
            $variant,
            $data['type'],
            $requestedChange,
            'manual_adjustment',
            $variant->id,
            $data['remarks'] ?: 'Manual stock adjustment from stock overview.'
        );

        AuditLog::record('PRODUCT', "Adjusted stock for variant: {$variant->product?->name} ({$variant->variant_code})", null, [
            'affected_table' => 'inventory_stocks',
            'affected_record_id' => $variant->inventoryStock?->id,
            'old_values' => ['quantity_on_hand' => $movement->quantity_before],
            'new_values' => [
                'quantity_on_hand' => $movement->quantity_after,
                'actual_change' => $movement->quantity_change,
                'movement_type' => $data['type'],
                'variant_id' => $variant->id,
                'remarks' => $data['remarks'] ?? null,
            ],
        ]);

        return back()->with('success', "Stock adjusted for \"{$variant->variant_code}\".");
    }

    public function destroy(ProductVariant $variant)
    {
        if ($variant->status === 'active') {
            return back()->withErrors([
                'error' => 'Active product variants cannot be deleted. Set the variant to inactive before deleting it.',
            ]);
        }

        $variant->load('product', 'brand', 'unit', 'supplier', 'inventoryStock');
        $label = trim(($variant->product?->name ?? 'Product').' '.$variant->variant_code);
        $oldValues = $this->variantAuditSnapshot($variant);
        $variantId = $variant->id;

        $variant->delete();

        AuditLog::record('PRODUCT', "Deleted variant: {$label}", null, [
            'affected_table' => 'product_variants',
            'affected_record_id' => $variantId,
            'old_values' => $oldValues,
        ]);

        return back()->with('success', "Variant \"{$label}\" deleted.");
    }

    private function formOptions(): array
    {
        $productId = old('product_id');
        $supplierId = old('supplier_id');

        return [
            'selectedProduct' => $productId
                ? Product::with('productCategory:id,name')->find($productId)
                : null,
            'selectedSupplier' => $supplierId
                ? Supplier::query()->select('id', 'name')->find($supplierId)
                : null,
            'categories' => Product::categories(),
            'units' => Product::units(),
        ];
    }

    private function variantQuery()
    {
        return ProductVariant::with(
            'product.productCategory',
            'brand',
            'supplier',
            'unit',
            'inventoryStock'
        );
    }

    private function orderVariants($query)
    {
        return $query
            ->join('products', 'products.id', '=', 'product_variants.product_id')
            ->select('product_variants.*')
            ->orderBy('products.name')
            ->orderBy('product_variants.variant_code');
    }

    private function applyCategoryFilter($query, string $category): void
    {
        $query->whereHas('product.productCategory', fn ($q) => $q->where('name', $category));
    }

    private function applyVariantSearch($query, string $search): void
    {
        $query->where(function ($q) use ($search) {
            $q->whereHas('product', fn ($productQuery) => CaseInsensitiveSearch::where($productQuery, 'name', $search));
            CaseInsensitiveSearch::orWhere($q, 'variant_code', $search);
            CaseInsensitiveSearch::orWhere($q, 'barcode', $search);
            CaseInsensitiveSearch::orWhere($q, 'part_number', $search);
            CaseInsensitiveSearch::orWhere($q, 'size', $search);
            CaseInsensitiveSearch::orWhere($q, 'color', $search);
            $q->orWhereHas('brand', fn ($brandQuery) => CaseInsensitiveSearch::where($brandQuery, 'name', $search));
        });
    }

    private function variantRules(Request $request, ?ProductVariant $variant = null): array
    {
        $requiresNewProduct = blank($request->input('product_id'));

        return [
            'product_id' => ['nullable', 'exists:products,id'],
            'code' => ['required', 'string', 'max:50', Rule::unique('product_variants', 'variant_code')->ignore($variant?->id)],
            'barcode' => ['nullable', 'string', 'max:80'],
            'part_number' => ['nullable', 'string', 'max:100'],
            'name' => [Rule::requiredIf($requiresNewProduct), 'nullable', 'string', 'max:200'],
            'description' => ['nullable', 'string', 'max:1000'],
            'brand' => ['nullable', 'string', 'max:100'],
            'size' => ['nullable', 'string', 'max:100'],
            'color' => ['nullable', 'string', 'max:50'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'category' => [Rule::requiredIf($requiresNewProduct), 'nullable', 'string', 'exists:product_categories,name'],
            'price' => ['required', 'numeric', 'min:0', 'max:'.InputLimits::DATABASE_MONEY_MAX],
            'cost' => ['required', 'numeric', 'min:0', 'max:'.InputLimits::DATABASE_MONEY_MAX],
            'stock' => ['required', 'integer', 'min:0', 'max:'.InputLimits::DATABASE_INTEGER_MAX],
            'min_stock' => ['required', 'integer', 'min:0', 'max:'.InputLimits::DATABASE_INTEGER_MAX],
            'critical_stock' => ['required', 'integer', 'min:0', 'max:'.InputLimits::DATABASE_INTEGER_MAX, 'lte:min_stock'],
            'unit' => ['required', 'string', 'max:20', 'exists:units,code'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'status' => $variant
                ? ['required', 'in:active,inactive']
                : ['exclude'],
        ];
    }

    private function variantMessages(): array
    {
        return [
            'name.required' => 'Enter a product name when creating a new general product.',
            'category.required' => 'Choose a category when creating a new general product.',
            'critical_stock.lte' => 'Critical stock must be less than or equal to the low stock alert level.',
        ];
    }

    private function productForStore(array $data): Product
    {
        if (filled($data['product_id'] ?? null)) {
            return Product::findOrFail((int) $data['product_id']);
        }

        return Product::create([
            'name' => $data['name'],
            'category' => $data['category'],
            'description' => $data['description'] ?? null,
            'status' => 'active',
        ]);
    }

    private function productForUpdate(ProductVariant $variant, array $data): Product
    {
        $product = filled($data['product_id'] ?? null)
            ? Product::findOrFail((int) $data['product_id'])
            : $variant->product()->firstOrFail();

        if (filled($data['name'] ?? null) && filled($data['category'] ?? null)) {
            $product->update([
                'name' => $data['name'],
                'category' => $data['category'],
                'description' => $data['description'] ?? null,
            ]);
        }

        return $product;
    }

    private function saveVariant(ProductVariant $variant, Product $product, array $data): ProductVariant
    {
        $variant->fill([
            'product_id' => $product->id,
            'brand_id' => $this->brandId($data['brand'] ?? null),
            'unit_id' => Unit::idForCode($data['unit'] ?? null),
            'supplier_id' => filled($data['supplier_id'] ?? null) ? (int) $data['supplier_id'] : null,
            'variant_code' => $data['code'],
            'barcode' => $data['barcode'] ?? null,
            'part_number' => $data['part_number'] ?? null,
            'size' => $data['size'] ?? null,
            'color' => $data['color'] ?? null,
            'image_path' => $data['image_path'] ?? $variant->image_path,
            'selling_price' => $data['price'],
            'cost_price' => $data['cost'],
            'status' => $data['status'],
        ]);
        $variant->save();

        return $variant;
    }

    private function saveInventoryStock(ProductVariant $variant, ?int $stock, int $minimumStock, int $criticalStock): InventoryStock
    {
        $values = [
            'minimum_stock' => $minimumStock,
            'critical_stock' => $criticalStock,
        ];

        if ($stock !== null) {
            $values['quantity_on_hand'] = $stock;
        }

        return InventoryStock::updateOrCreate(
            ['variant_id' => $variant->id],
            $values
        );
    }

    private function brandId(?string $brandName): ?int
    {
        $brandName = trim((string) $brandName);

        return $brandName === ''
            ? null
            : Brand::firstOrCreate(['name' => $brandName])->id;
    }

    private function storeVariantImage(Request $request, ?string $existingPath = null): ?string
    {
        if (! $request->hasFile('image')) {
            return $existingPath;
        }

        return $request->file('image')->store('product-variants', 'public');
    }

    private function variantAuditSnapshot(ProductVariant $variant): array
    {
        return [
            'product_id' => $variant->product_id,
            'product_name' => $variant->product?->name,
            'category' => $variant->product?->category,
            'variant_code' => $variant->variant_code,
            'barcode' => $variant->barcode,
            'part_number' => $variant->part_number,
            'brand' => $variant->brand?->name,
            'size' => $variant->size,
            'color' => $variant->color,
            'image_path' => $variant->image_path,
            'selling_price' => $variant->selling_price,
            'cost_price' => $variant->cost_price,
            'quantity_on_hand' => $variant->inventoryStock?->quantity_on_hand,
            'minimum_stock' => $variant->inventoryStock?->minimum_stock,
            'critical_stock' => $variant->inventoryStock?->critical_stock,
            'unit' => $variant->unit?->code,
            'supplier_id' => $variant->supplier_id,
            'status' => $variant->status,
        ];
    }
}
