<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryStock extends Model
{
    protected $fillable = [
        'variant_id',
        'quantity_on_hand',
        'minimum_stock',
        'critical_stock',
        'last_received_at',
    ];

    protected $casts = [
        'quantity_on_hand' => 'integer',
        'minimum_stock' => 'integer',
        'critical_stock' => 'integer',
        'last_received_at' => 'date',
    ];

    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }
}
