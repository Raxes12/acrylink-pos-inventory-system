<?php

namespace App\Models;

use App\Services\TransactionSettlementService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreditSale extends Model
{
    protected $fillable = [
        'transaction_id',
        'customer_id',
        'credit_term_days',
        'due_date',
        'original_credit_amount',
    ];

    protected $casts = [
        'credit_term_days' => 'integer',
        'due_date' => 'date',
        'original_credit_amount' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::saved(fn (CreditSale $creditSale) => $creditSale->synchronizeTransactionPaymentStatus());
        static::deleted(fn (CreditSale $creditSale) => $creditSale->synchronizeTransactionPaymentStatus());
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

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
