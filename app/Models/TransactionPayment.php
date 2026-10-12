<?php

namespace App\Models;

use App\Services\TransactionSettlementService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransactionPayment extends Model
{
    protected $fillable = [
        'transaction_id',
        'payment_method_id',
        'amount',
        'payment_reference',
        'check_number',
        'check_bank',
        'check_date',
        'received_by',
        'paid_at',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'check_date' => 'date',
    ];

    protected static function booted(): void
    {
        static::saved(fn (TransactionPayment $payment) => $payment->synchronizeTransactionPaymentStatus());
        static::deleted(fn (TransactionPayment $payment) => $payment->synchronizeTransactionPaymentStatus());
    }

    private function synchronizeTransactionPaymentStatus(): void
    {
        $transaction = $this->transaction()->first();
        if ($transaction) {
            app(TransactionSettlementService::class)->synchronizePaymentStatus($transaction);
        }
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }
}
