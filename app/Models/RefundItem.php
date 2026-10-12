<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RefundItem extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'refund_id',
        'transaction_item_id',
        'product_name',
        'quantity',
        'unit_price',
        'restock',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'restock' => 'boolean',
    ];

    public function refund()
    {
        return $this->belongsTo(Refund::class);
    }

    public function transactionItem()
    {
        return $this->belongsTo(TransactionItem::class);
    }

    public function lineTotal(): float
    {
        return $this->quantity * (float) $this->unit_price;
    }
}
