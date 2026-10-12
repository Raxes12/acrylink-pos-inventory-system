<?php

namespace App\Models;

use App\Services\ReferenceDataService;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    protected $fillable = ['code', 'name', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function suppliers()
    {
        return $this->hasMany(Supplier::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function refunds()
    {
        return $this->hasMany(Refund::class, 'refund_method_id');
    }

    public function transactionPayments()
    {
        return $this->hasMany(TransactionPayment::class);
    }

    public static function defaultCodes(): array
    {
        return ['cash', 'gcash', 'credit_account', 'check'];
    }

    public static function labelFor(string $code): string
    {
        return match ($code) {
            'cash' => 'Cash',
            'check' => 'Check',
            'credit' => 'Credit Card', // Historical records only.
            'credit_account' => 'Credit Sale',
            'gcash' => 'GCash',
            default => ucwords(str_replace('_', ' ', $code)),
        };
    }

    public static function idForCode(?string $code): ?int
    {
        if (!$code) {
            return null;
        }

        $cached = app(ReferenceDataService::class)
            ->paymentMethods()
            ->firstWhere('code', $code);

        if ($cached) {
            return $cached->id;
        }

        return static::firstOrCreate(
            ['code' => $code],
            ['name' => static::labelFor($code), 'is_active' => in_array($code, static::defaultCodes(), true)],
        )->id;
    }
}
