<?php

namespace App\Services;

use App\Models\PaymentMethod;
use App\Models\ProductCategory;
use App\Models\Role;
use App\Models\Unit;
use Illuminate\Support\Facades\Cache;

class ReferenceDataService
{
    private const TTL = 300;

    public function categories(): array
    {
        return Cache::remember('reference.categories.v1', self::TTL, fn () => ProductCategory::orderBy('name')->pluck('name')->all());
    }

    public function units(): array
    {
        return Cache::remember('reference.units.v1', self::TTL, fn () => Unit::orderBy('code')->pluck('code')->all());
    }

    public function paymentMethods()
    {
        return Cache::remember('reference.payment-methods.v2', self::TTL, fn () => PaymentMethod::whereIn('code', PaymentMethod::defaultCodes())->where('is_active', true)->orderBy('name')->get());
    }

    public function roles()
    {
        return Cache::remember('reference.roles.v1', self::TTL, fn () => Role::orderBy('role_id')->get());
    }

    public function forgetCategories(): void { Cache::forget('reference.categories.v1'); }
    public function forgetUnits(): void { Cache::forget('reference.units.v1'); }
    public function forgetPaymentMethods(): void { Cache::forget('reference.payment-methods.v2'); }
    public function forgetRoles(): void { Cache::forget('reference.roles.v1'); }
}
