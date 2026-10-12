<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    protected $fillable = [
        'product_id',
        'brand_id',
        'unit_id',
        'supplier_id',
        'variant_code',
        'barcode',
        'part_number',
        'size',
        'color',
        'image_path',
        'selling_price',
        'cost_price',
        'status',
    ];

    protected $casts = [
        'selling_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function inventoryStock()
    {
        return $this->hasOne(InventoryStock::class, 'variant_id');
    }

    public function transactionItems()
    {
        return $this->hasMany(TransactionItem::class, 'variant_id');
    }

    public function inventoryMovements()
    {
        return $this->hasMany(InventoryMovement::class, 'variant_id');
    }

    public function stockAlerts()
    {
        return $this->hasMany(StockAlert::class, 'variant_id');
    }

    public function stockReceiptItems()
    {
        return $this->hasMany(StockReceiptItem::class, 'variant_id');
    }
}
