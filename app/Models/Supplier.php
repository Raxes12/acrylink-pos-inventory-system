<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'contact',
        'phone',
        'email',
        'address',
        'status',
        'payment_method_id',
        'payment_method',
    ];

    protected $appends = ['payment_method'];

    public function products()
{
    return $this->hasManyThrough(
        Product::class,
        ProductVariant::class,
        'supplier_id',
        'id',
        'id',
        'product_id'
    )
    ->select('products.*')
    ->distinct();
}

    public function productVariants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function stockReceipts()
    {
        return $this->hasMany(StockReceipt::class);
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function getPaymentMethodAttribute(): string
    {
        if ($this->relationLoaded('paymentMethod')) {
            return $this->getRelation('paymentMethod')?->code ?? 'cash';
        }

        return PaymentMethod::find($this->attributes['payment_method_id'] ?? null)?->code ?? 'cash';
    }

    public function setPaymentMethodAttribute(?string $value): void
    {
        $this->attributes['payment_method_id'] = PaymentMethod::idForCode($value ?: 'cash');
    }
}
