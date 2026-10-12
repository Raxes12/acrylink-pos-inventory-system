<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Keep IDs and historical financial references intact.
        DB::table('payment_methods')
            ->whereNotIn('code', ['cash', 'gcash', 'credit_account', 'check'])
            ->update(['is_active' => false, 'updated_at' => now()]);

        foreach (['cash' => 'Cash', 'gcash' => 'GCash', 'credit_account' => 'Credit Sale', 'check' => 'Check'] as $code => $name) {
            DB::table('payment_methods')->updateOrInsert(['code' => $code], [
                'name' => $name, 'is_active' => true, 'updated_at' => now(),
            ]);
        }

        Cache::forget('reference.payment-methods.v1');
        Cache::forget('reference.payment-methods.v2');
    }

    public function down(): void
    {
        // Previous activation states are unknown; do not re-enable retired methods.
    }
};
