<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'category_id',
        'category',
        'status',
    ];

    protected $appends = ['category', 'unit'];

    public function supplier()
    {
        return $this->hasOneThrough(
            Supplier::class,
            ProductVariant::class,
            'product_id',
            'id',
            'id',
            'supplier_id'
        );
    }

    public function productCategory()
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function unitRef()
    {
        return $this->hasOneThrough(
            Unit::class,
            ProductVariant::class,
            'product_id',
            'id',
            'id',
            'unit_id'
        );
    }

    public function transactionItems()
    {
        return $this->hasMany(TransactionItem::class);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function primaryVariant()
    {
        return $this->hasOne(ProductVariant::class)->oldestOfMany();
    }

    public function inventoryMovements()
    {
        return $this->hasManyThrough(
            InventoryMovement::class,
            ProductVariant::class,
            'product_id',
            'variant_id',
            'id',
            'id'
        );
    }

    public function stockAlerts()
    {
        return $this->hasManyThrough(
            StockAlert::class,
            ProductVariant::class,
            'product_id',
            'variant_id',
            'id',
            'id'
        );
    }

    public function getCategoryAttribute(): ?string
    {
        return $this->productCategory?->name;
    }

    public function setCategoryAttribute(?string $value): void
    {
        $this->attributes['category_id'] = ProductCategory::idForName($value);
    }

    public function getUnitAttribute(): ?string
    {
        return $this->primaryVariantRecord()?->unit?->code;
    }

    public function setUnitAttribute(?string $value): void
    {
        $this->setCompatibilityValue('unit_id', Unit::idForCode($value));
    }

    public function isLowStock(): bool
    {
        return $this->variantsForStockSummary()
            ->contains(function (ProductVariant $variant) {
                $stock = $variant->inventoryStock;

                return $stock && $stock->quantity_on_hand <= $stock->minimum_stock;
            });
    }

    public function unitMargin(): float
    {
        return (float) $this->price - (float) $this->cost;
    }

    public function marginPct(): float
    {
        return $this->price > 0
            ? round(((float) $this->price - (float) $this->cost) / (float) $this->price * 100, 2)
            : 0;
    }

    public function inventoryValue(): float
    {
        return (float) $this->variantsForStockSummary()
            ->sum(function (ProductVariant $variant) {
                return (int) ($variant->inventoryStock?->quantity_on_hand ?? 0) * (float) $variant->cost_price;
            });
    }

    public static function lowStockCount(): int
    {
        return InventoryStock::whereColumn('quantity_on_hand', '<=', 'minimum_stock')->count();
    }

    public static function outOfStockCount(): int
    {
        return InventoryStock::where('quantity_on_hand', '<=', 0)->count();
    }

    public static function stockAlertBreakdown(): array
    {
        $outOfStock = InventoryStock::where('quantity_on_hand', '<=', 0)->count();

        $criticalStock = InventoryStock::where('quantity_on_hand', '>', 0)
            ->whereColumn('quantity_on_hand', '<=', 'critical_stock')
            ->count();

        $lowStock = InventoryStock::where('quantity_on_hand', '>', 0)
            ->whereColumn('quantity_on_hand', '<=', 'minimum_stock')
            ->whereColumn('quantity_on_hand', '>', 'critical_stock')
            ->count();

        return [
            'low' => $lowStock,
            'critical' => $criticalStock,
            'out' => $outOfStock,
            'total' => $lowStock + $criticalStock + $outOfStock,
        ];
    }

    public static function inventoryValueTotal(): float
    {
        return (float) InventoryStock::query()
            ->join('product_variants', 'product_variants.id', '=', 'inventory_stocks.variant_id')
            ->selectRaw('COALESCE(SUM(inventory_stocks.quantity_on_hand * product_variants.cost_price), 0) as total_value')
            ->value('total_value');
    }

    public static function lowStockItems(int $limit = 5)
    {
        return ProductVariant::with('product.productCategory', 'brand', 'supplier', 'unit', 'inventoryStock')
            ->whereHas('inventoryStock', fn ($query) => $query->whereColumn('quantity_on_hand', '<=', 'minimum_stock'))
            ->get()
            ->sortByDesc(fn (ProductVariant $variant) => (int) ($variant->inventoryStock?->minimum_stock ?? 0) - (int) ($variant->inventoryStock?->quantity_on_hand ?? 0))
            ->take($limit)
            ->values();
    }

    public function getCodeAttribute(): ?string
    {
        return $this->compatibilityValue('variant_code');
    }

    public function getBarcodeAttribute(): ?string
    {
        return $this->compatibilityValue('barcode');
    }

    public function getBrandAttribute(): ?string
    {
        return $this->primaryVariantRecord()?->brand?->name;
    }

    public function getModelAttribute(): ?string
    {
        return $this->compatibilityValue('model');
    }

    public function getVariantAttribute(): ?string
    {
        return $this->compatibilityValue('size');
    }

    public function getSizeAttribute(): ?string
    {
        return $this->compatibilityValue('size');
    }

    public function getColorAttribute(): ?string
    {
        return $this->compatibilityValue('color');
    }

    public function getPriceAttribute(): float
    {
        return (float) ($this->compatibilityValue('selling_price') ?? 0);
    }

    public function getCostAttribute(): float
    {
        return (float) ($this->compatibilityValue('cost_price') ?? 0);
    }

    public function getStockAttribute(): int
    {
        return (int) ($this->primaryVariantRecord()?->inventoryStock?->quantity_on_hand ?? 0);
    }

    public function getMinStockAttribute(): int
    {
        return (int) ($this->primaryVariantRecord()?->inventoryStock?->minimum_stock ?? 0);
    }

    public function getCriticalStockAttribute(): int
    {
        return (int) ($this->primaryVariantRecord()?->inventoryStock?->critical_stock ?? 0);
    }

    public function getSupplierIdAttribute(): ?int
    {
        return $this->compatibilityValue('supplier_id');
    }

    public function getUnitIdAttribute(): ?int
    {
        return $this->compatibilityValue('unit_id');
    }

    public static function categories(): array
    {
        $categories = app(\App\Services\ReferenceDataService::class)->categories();

        return $categories ?: ProductCategory::defaults();
    }

    public static function units(): array
    {
        $units = app(\App\Services\ReferenceDataService::class)->units();

        return $units ?: Unit::defaults();
    }

    private function compatibilityValue(string $column): mixed
    {
        if (array_key_exists($column, $this->attributes)) {
            return $this->attributes[$column];
        }

        return $this->primaryVariantRecord()?->{$column};
    }

    private function setCompatibilityValue(string $column, mixed $value): void
    {
        if (array_key_exists($column, $this->attributes)) {
            $this->attributes[$column] = $value;
        }
    }

    private function primaryVariantRecord(): ?ProductVariant
    {
        if ($this->relationLoaded('primaryVariant')) {
            return $this->getRelation('primaryVariant');
        }

        return $this->primaryVariant()
            ->with('brand', 'unit', 'supplier', 'inventoryStock')
            ->first();
    }

    private function variantsForStockSummary()
    {
        if ($this->relationLoaded('variants')) {
            return $this->getRelation('variants');
        }

        return $this->variants()
            ->with('inventoryStock')
            ->get();
    }
}
