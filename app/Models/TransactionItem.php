<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionItem extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'transaction_id',
        'product_id',
        'variant_id',
        'product_name_snapshot',
        'part_number_snapshot',
        'brand_snapshot',
        'model_snapshot',
        'size_snapshot',
        'color_snapshot',
        'unit_snapshot',
        'product_name',
        'quantity',
        'unit_price',
        'subtotal',
        'discount',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    public function productVariant()
    {
        return $this->variant();
    }

    public function refundItems()
    {
        return $this->hasMany(RefundItem::class);
    }

    public function lineTotal(): float
    {
        return (float) ($this->subtotal ?: ($this->quantity * (float) $this->unit_price));
    }

    public function netLineTotal(): float
    {
        return round(max(0, $this->lineTotal() - (float) $this->discount), 2);
    }
}
