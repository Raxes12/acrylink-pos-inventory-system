<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use App\Models\ProductCategory;
use App\Models\Unit;
use Illuminate\Database\Seeder;

class LookupSeeder extends Seeder
{
    public function run(): void
    {
        foreach (ProductCategory::defaults() as $name) {
            ProductCategory::firstOrCreate(['name' => $name]);
        }

        foreach (Unit::defaults() as $code) {
            Unit::firstOrCreate(['code' => $code], ['name' => $code]);
        }

        PaymentMethod::whereNotIn('code', PaymentMethod::defaultCodes())->update(['is_active' => false]);
        app(\App\Services\ReferenceDataService::class)->forgetPaymentMethods();

        foreach (PaymentMethod::defaultCodes() as $code) {
            $paymentMethod = PaymentMethod::firstOrCreate(
                ['code' => $code],
                [
                    'name' => PaymentMethod::labelFor($code),
                    'is_active' => true,
                ],
            );

            if (in_array($code, ['credit', 'credit_account'], true)
                && $paymentMethod->name !== PaymentMethod::labelFor($code)) {
                $paymentMethod->update(['name' => PaymentMethod::labelFor($code)]);
            }
        }
    }
}
