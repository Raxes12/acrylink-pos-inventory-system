<?php

namespace App\Models;

use App\Services\DocumentSequenceService;
use App\Services\TransactionSettlementService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'receipt_no',
        'checkout_token',
        'date',
        'time',
        'cashier_id',
        'sales_associate',
        'customer_id',
        'subtotal',
        'discount',
        'total',
        'payment_method_id',
        'payment_method',
        'payment_reference',
        'payment_card_type',
        'payment_card_last4',
        'payment_payer_name',
        'payment_status',
        'check_number',
        'check_bank',
        'check_date',
        'payment',
        'change_due',
        'status',
    ];

    protected $appends = ['payment_method'];

    protected $casts = [
        'date' => 'date',
        'check_date' => 'date',
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
        'payment' => 'decimal:2',
        'change_due' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::updated(function (Transaction $transaction) {
            if ($transaction->wasChanged('status')) {
                app(TransactionSettlementService::class)->synchronizePaymentStatus($transaction);
            }
        });
    }

    public function cashier()
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function creditSale(): HasOne
    {
        return $this->hasOne(CreditSale::class);
    }

    public function items()
    {
        return $this->hasMany(TransactionItem::class);
    }

    public function refunds()
    {
        return $this->hasMany(Refund::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(TransactionPayment::class);
    }

    public function totalPaid(): float
    {
        return app(TransactionSettlementService::class)->totalPaid($this);
    }

    public function outstandingBalance(): float
    {
        return app(TransactionSettlementService::class)->outstandingBalance($this);
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

    public function totalRefunded(): float
    {
        if ($this->status === 'voided') {
            return 0;
        }

        if ($this->relationLoaded('refunds')) {
            return (float) $this->refunds->sum('refund_amount');
        }

        return (float) $this->refunds()->sum('refund_amount');
    }

    public function refundedGross(): float
    {
        if ($this->status === 'voided') {
            return 0;
        }

        if ($this->relationLoaded('refunds')) {
            return (float) $this->refunds->sum(function (Refund $refund) {
                return $refund->relationLoaded('items')
                    ? $refund->items->sum(fn (RefundItem $item) => $item->lineTotal())
                    : $refund->items()->get()->sum(fn (RefundItem $item) => $item->lineTotal());
            });
        }

        return (float) RefundItem::query()
            ->join('refunds', 'refunds.id', '=', 'refund_items.refund_id')
            ->where('refunds.transaction_id', $this->id)
            ->selectRaw('COALESCE(SUM(refund_items.quantity * refund_items.unit_price), 0) as total')
            ->value('total');
    }

    public function netTotal(): float
    {
        return app(TransactionSettlementService::class)->netTotal($this);
    }

    public function netSubtotal(): float
    {
        if ($this->status === 'voided') {
            return 0;
        }

        return round(max(0, (float) $this->subtotal - $this->refundedGross()), 2);
    }

    public function netDiscount(): float
    {
        return round(max(0, $this->netSubtotal() - $this->netTotal()), 2);
    }

    public static function netSalesForDate($date, ?int $cashierId = null): float
    {
        return round((float) static::with('refunds')
            ->whereDate('date', $date)
            ->when($cashierId, fn ($query) => $query->where('cashier_id', $cashierId))
            ->get()
            ->sum(fn (Transaction $transaction) => $transaction->netTotal()), 2);
    }

    public function isFullyRefunded(): bool
    {
        return $this->status === 'refunded';
    }

    public static function generateReceiptNo(): string
    {
        return app(DocumentSequenceService::class)
            ->next('sales', 'RCP-', static::class, 'receipt_no');
    }

    public static function paymentMethodLabel(string $method): string
    {
        return PaymentMethod::labelFor($method);
    }
}

