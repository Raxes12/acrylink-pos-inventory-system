<?php

namespace App\Models;

use App\Services\DocumentSequenceService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Customer extends Model
{
    protected $fillable = [
        'customer_code',
        'customer_name',
        'phone',
        'email',
        'address',
        'credit_enabled',
        'credit_limit',
        'default_credit_term_days',
        'status',
    ];

    protected $casts = [
        'credit_enabled' => 'boolean',
        'credit_limit' => 'decimal:2',
        'default_credit_term_days' => 'integer',
    ];

    /** Create a customer and reserve its code in the same locked transaction. */
    public static function createWithGeneratedCode(array $attributes): static
    {
        return DB::transaction(function () use ($attributes) {
            $attributes['customer_code'] = static::generateCustomerCode();

            return static::create($attributes);
        }, 3);
    }

    /** Like receipt numbering, callers must already have a DB transaction open. */
    public static function generateCustomerCode(): string
    {
        return app(DocumentSequenceService::class)
            ->next('customers', 'CUST-', static::class, 'customer_code', 6);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'customer_id');
    }

    public function creditSales(): HasMany
    {
        return $this->hasMany(CreditSale::class);
    }
}
