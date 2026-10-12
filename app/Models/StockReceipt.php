<?php

namespace App\Models;

use App\Services\DocumentSequenceService;
use Illuminate\Database\Eloquent\Model;

class StockReceipt extends Model
{
    protected $fillable = [
        'supplier_id',
        'received_by',
        'receipt_no',
        'date_received',
        'remarks',
    ];

    protected $casts = [
        'date_received' => 'date',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function receivedBy()
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function items()
    {
        return $this->hasMany(StockReceiptItem::class, 'receipt_id');
    }

    public static function generateReceiptNo(): string
    {
        $date = now()->format('Ymd');
        $prefix = "SR-{$date}-";

        return app(DocumentSequenceService::class)
            ->next("stock-receipts:{$date}", $prefix, static::class, 'receipt_no');
    }
}
