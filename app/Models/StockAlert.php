<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockAlert extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'variant_id',
        'alert_type',
        'message',
        'alert_status',
        'created_at',
        'resolved_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    public function productVariant()
    {
        return $this->variant();
    }
}
