<?php

namespace Tests\Feature;

use App\Models\PaymentMethod;
use App\Models\Refund;
use App\Models\Supplier;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class NPlusOneQueryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_eager_loaded_transaction_payment_methods_do_not_query_per_row(): void
    {
        $cashier = User::where('role', User::ROLE_CASHIER)->firstOrFail();
        $paymentMethod = PaymentMethod::where('code', 'cash')->firstOrFail();

        foreach (range(1, 5) as $number) {
            Transaction::create([
                'receipt_no' => 'RCP-N1-'.str_pad((string) $number, 3, '0', STR_PAD_LEFT),
                'date' => today()->toDateString(),
                'time' => now()->toTimeString(),
                'cashier_id' => $cashier->id,
                'subtotal' => 100,
                'discount' => 0,
                'total' => 100,
                'payment_method_id' => $paymentMethod->id,
                'payment' => 100,
                'change_due' => 0,
                'status' => 'completed',
            ]);
        }

        $transactions = Transaction::with('paymentMethod')->get();
        DB::enableQueryLog();
        DB::flushQueryLog();

        $this->assertSame(
            ['cash'],
            $transactions->map->payment_method->unique()->values()->all()
        );
        $this->assertCount(0, DB::getQueryLog());
    }

    public function test_eager_loaded_supplier_payment_methods_do_not_query_per_row(): void
    {
        $suppliers = Supplier::with('paymentMethod')->get();
        $this->assertNotEmpty($suppliers);

        DB::enableQueryLog();
        DB::flushQueryLog();

        $suppliers->each(fn (Supplier $supplier) => $supplier->payment_method);

        $this->assertCount(0, DB::getQueryLog());
    }

    public function test_eager_loaded_refund_payment_methods_do_not_query_per_row(): void
    {
        $refunds = Refund::with('refundMethod')->get();

        DB::enableQueryLog();
        DB::flushQueryLog();

        $refunds->each(fn (Refund $refund) => $refund->refund_method);

        $this->assertCount(0, DB::getQueryLog());
    }
}
