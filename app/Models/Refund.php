<?php

namespace App\Models;

use App\Services\DocumentSequenceService;
use App\Services\TransactionSettlementService;
use Illuminate\Database\Eloquent\Model;

class Refund extends Model
{
    protected $fillable = [
        'refund_no',
        'transaction_id',
        'processed_by',
        'reason',
        'refund_type',
        'refund_amount',
        'receivable_adjustment_amount',
        'reimbursement_amount',
        'refund_method_id',
        'refund_method',
        'notes',
    ];

    protected $appends = ['refund_method'];

    protected $casts = [
        'refund_amount' => 'decimal:2',
        'receivable_adjustment_amount' => 'decimal:2',
        'reimbursement_amount' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::saved(fn (Refund $refund) => $refund->synchronizeTransactionPaymentStatus());
        static::deleted(fn (Refund $refund) => $refund->synchronizeTransactionPaymentStatus());
    }

    private function synchronizeTransactionPaymentStatus(): void
    {
        $transaction = $this->transaction()->first();
        if ($transaction) {
            app(TransactionSettlementService::class)->synchronizePaymentStatus($transaction);
        }
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function items()
    {
        return $this->hasMany(RefundItem::class);
    }

    public function refundMethod()
    {
        return $this->belongsTo(PaymentMethod::class, 'refund_method_id');
    }

    public function getRefundMethodAttribute(): string
    {
        if ($this->relationLoaded('refundMethod')) {
            return $this->getRelation('refundMethod')?->code ?? 'cash';
        }

        return PaymentMethod::find($this->attributes['refund_method_id'] ?? null)?->code ?? 'cash';
    }

    public function setRefundMethodAttribute(?string $value): void
    {
        $this->attributes['refund_method_id'] = $value === null
            ? null
            : PaymentMethod::idForCode($value);
    }

    public static function generateRefundNo(): string
    {
        return app(DocumentSequenceService::class)
            ->next('refunds', 'REF-', static::class, 'refund_no');
    }
}

