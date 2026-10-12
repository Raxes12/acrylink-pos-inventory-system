<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $selectedCategory = ProductCategory::find($request->integer('category'));
        $search = trim((string) $request->input('search'));

        $categories = ProductCategory::withCount('products')
            ->with('products.variants.inventoryStock')
            ->orderBy('name')
            ->get()
            ->each(function (ProductCategory $category) {
                $variants = $category->products->flatMap->variants;
                $category->setAttribute('variants_count', $variants->count());
                $category->setAttribute('low_stock_count', $variants->filter(fn (ProductVariant $variant) => $this->variantIsLowStock($variant))->count());
                $category->setAttribute('stock_units', $variants->sum(fn (ProductVariant $variant) => (int) ($variant->inventoryStock?->quantity_on_hand ?? 0)));
                $category->unsetRelation('products');
            });

        $products = Product::with(
            'productCategory',
            'variants.inventoryStock',
            'variants.brand',
            'variants.unit',
            'variants.supplier'
        )
            ->when($selectedCategory, fn($query) => $query->where('category_id', $selectedCategory->id))
            ->when($search, fn($query) => $query->where(function ($inner) use ($search) {
                $inner->where('name', 'like', "%{$search}%")
                    ->orWhereHas('variants', fn ($variantQuery) => $variantQuery
                        ->where('variant_code', 'like', "%{$search}%")
                        ->orWhere('barcode', 'like', "%{$search}%")
                        ->orWhereHas('brand', fn ($brandQuery) => $brandQuery->where('name', 'like', "%{$search}%")));
            }))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();
        $products->getCollection()->each(fn (Product $product) => $this->appendVariantSummary($product));

        return view('categories.index', compact('categories', 'products', 'selectedCategory', 'search'));
    }

    public function create()
    {
        return view('categories.form');
    }

    public function edit(ProductCategory $category)
    {
        return view('categories.form', compact('category'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:50', 'unique:product_categories,name'],
        ]);

        $category = ProductCategory::create($data);
        AuditLog::record('CATEGORY', "Created category: {$category->name}");

        return redirect()->route('categories.index')->with('success', "Category \"{$category->name}\" added.");
    }

    public function update(Request $request, ProductCategory $category)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:50', Rule::unique('product_categories', 'name')->ignore($category->id)],
        ]);

        $oldName = $category->name;
        $category->update($data);
        AuditLog::record('CATEGORY', "Renamed category: {$oldName} to {$category->name}");

        return redirect()->route('categories.index')->with('success', "Category renamed to \"{$category->name}\".");
    }

    public function destroy(ProductCategory $category)
    {
        if ($category->products()->exists()) {
            return back()->withErrors(['category' => "Cannot delete \"{$category->name}\" because it still has products."]);
        }

        $name = $category->name;
        $category->delete();
        AuditLog::record('CATEGORY', "Deleted category: {$name}");

        return redirect()->route('categories.index')->with('success', "Category \"{$name}\" deleted.");
    }

    private function appendVariantSummary(Product $product): void
    {
        $variants = $product->variants;
        $stocks = $variants->map(fn (ProductVariant $variant) => $variant->inventoryStock);
        $prices = $variants->pluck('selling_price')->filter(fn ($price) => $price !== null);
        $suppliers = $variants
            ->map(fn (ProductVariant $variant) => $variant->supplier?->name)
            ->filter()
            ->unique()
            ->values();

        $product->setAttribute('variants_count', $variants->count());
        $product->setAttribute('total_stock_units', $stocks->sum(fn ($stock) => (int) ($stock?->quantity_on_hand ?? 0)));
        $product->setAttribute('low_stock_variants_count', $variants->filter(fn (ProductVariant $variant) => $this->variantIsLowStock($variant))->count());
        $product->setAttribute('out_of_stock_variants_count', $stocks->filter(fn ($stock) => (int) ($stock?->quantity_on_hand ?? 0) <= 0)->count());
        $product->setAttribute('min_selling_price', $prices->min());
        $product->setAttribute('max_selling_price', $prices->max());
        $product->setAttribute('supplier_names', $suppliers);
    }

    private function variantIsLowStock(ProductVariant $variant): bool
    {
        $stock = $variant->inventoryStock;

        return $stock && $stock->quantity_on_hand <= $stock->minimum_stock;
    }
}
