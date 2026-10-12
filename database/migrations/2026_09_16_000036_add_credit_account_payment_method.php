<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('payment_methods')) {
            return;
        }

        if (DB::table('payment_methods')->where('code', 'credit_account')->exists()) {
            DB::table('payment_methods')->where('code', 'credit_account')->update([
                'name' => 'Credit Sale',
                'updated_at' => now(),
            ]);
        } else {
            DB::table('payment_methods')->insert([
                'code' => 'credit_account',
                'name' => 'Credit Sale',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        Cache::forget('reference.payment-methods.v1');
    }

    public function down(): void
    {
        if (! Schema::hasTable('payment_methods')) {
            return;
        }

        $creditAccountId = DB::table('payment_methods')->where('code', 'credit_account')->value('id');
        if ($creditAccountId && $this->paymentMethodIsReferenced((int) $creditAccountId)) {
            throw new RuntimeException('Cannot remove the credit_account payment method while financial records reference it.');
        }

        DB::table('payment_methods')->where('code', 'credit_account')->delete();
        DB::table('payment_methods')
            ->where('code', 'credit')
            ->where('name', 'Credit Card')
            ->update(['name' => 'Credit', 'updated_at' => now()]);
        Cache::forget('reference.payment-methods.v1');
    }

    private function paymentMethodIsReferenced(int $paymentMethodId): bool
    {
        foreach ([
            ['transactions', 'payment_method_id'],
            ['transaction_payments', 'payment_method_id'],
            ['refunds', 'refund_method_id'],
            ['suppliers', 'payment_method_id'],
        ] as [$table, $column]) {
            if (Schema::hasTable($table)
                && Schema::hasColumn($table, $column)
                && DB::table($table)->where($column, $paymentMethodId)->exists()) {
                return true;
            }
        }

        return false;
    }
};
