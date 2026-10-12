<?php

namespace Tests\Feature;

use App\Models\CreditSale;
use App\Models\Customer;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use RuntimeException;
use Tests\TestCase;

class CreditSalesFoundationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_legacy_sales_become_paid_without_losing_history_and_rollback_restores_exact_values(): void
    {
        $migration = $this->migration();
        $migration->down();
        $originals = [];
        foreach (['cash', 'gcash', 'credit', 'check'] as $index => $method) {
            foreach (['pending', 'confirmed'] as $legacyStatus) {
                $transaction = $this->transaction([
                    'payment_method' => $method,
                    'payment_status' => $legacyStatus,
                    'status' => ['completed', 'partial_refund', 'refunded', 'voided'][$index],
                    'payment_reference' => 'REFERENCE',
                    'payment_card_type' => 'credit', 'payment_card_last4' => '1234',
                    'payment_payer_name' => 'Card Holder',
                    'check_number' => 'CHECK-001', 'check_bank' => 'Test Bank', 'check_date' => '2026-09-15',
                ]);
                $originals[$transaction->id] = (array) DB::table('transactions')->find($transaction->id);
            }
        }
        $migration->up();
        foreach ($originals as $id => $before) {
            $after = (array) DB::table('transactions')->find($id);
            $this->assertSame('paid', $after['payment_status']);
            $this->assertSame($before['payment_status'], $after['legacy_payment_status']);
            $this->assertNull($after['customer_id']);
            unset($after['customer_id'], $after['legacy_payment_status']);
            $after['payment_status'] = $before['payment_status'];
            $this->assertEquals($before, $after);
        }
        $this->assertSame(0, CreditSale::count());
        $newSale = $this->transaction();
        $this->assertSame('paid', $newSale->fresh()->payment_status);

        $migration->down();
        foreach ($originals as $id => $before) {
            $this->assertEquals($before, (array) DB::table('transactions')->find($id));
        }
        $this->assertSame('confirmed', $newSale->fresh()->payment_status);
        $this->assertFalse(Schema::hasColumn('transactions', 'customer_id'));
        $this->assertFalse(Schema::hasColumn('transactions', 'legacy_payment_status'));
        $migration->up();
    }

    public function test_payment_and_transaction_statuses_are_independent(): void
    {
        foreach (['completed', 'partial_refund', 'refunded', 'voided'] as $status) {
            foreach (['unpaid', 'partially_paid', 'paid', 'overdue'] as $paymentStatus) {
                $transaction = $this->transaction(['status' => $status, 'payment_status' => $paymentStatus])->refresh();
                $this->assertSame($status, $transaction->status);
                $this->assertSame($paymentStatus, $transaction->payment_status);
            }
        }
        $this->assertSame('paid', $this->transaction()->fresh()->payment_status);
    }

    public function test_legacy_payment_status_cannot_be_written_into_new_status_column(): void
    {
        $this->expectException(QueryException::class);
        $this->transaction(['payment_status' => 'confirmed']);
    }

    public function test_transaction_status_constraint_is_not_weakened(): void
    {
        $this->expectException(QueryException::class);
        $this->transaction(['status' => 'unpaid']);
    }

    public function test_relationships_and_credit_casts_work_without_per_row_queries(): void
    {
        $customer = Customer::createWithGeneratedCode(['customer_name' => 'Credit Customer']);
        $transaction = $this->transaction(['customer_id' => $customer->id, 'payment_status' => 'unpaid']);
        $credit = CreditSale::create($this->creditData($transaction, $customer))->refresh();
        $this->assertSame(30, $credit->credit_term_days);
        $this->assertSame('2026-10-15', $credit->due_date->toDateString());
        $this->assertSame('100.00', $credit->original_credit_amount);

        $customer->load('transactions.creditSale', 'creditSales.transaction', 'creditSales.customer');
        $transaction->load('customer', 'creditSale');
        DB::enableQueryLog();
        DB::flushQueryLog();
        $this->assertTrue($customer->transactions->first()->is($transaction));
        $this->assertTrue($customer->creditSales->first()->is($credit));
        $this->assertTrue($customer->creditSales->first()->transaction->is($transaction));
        $this->assertTrue($customer->creditSales->first()->customer->is($customer));
        $this->assertTrue($transaction->customer->is($customer));
        $this->assertTrue($transaction->creditSale->is($credit));
        $this->assertCount(0, DB::getQueryLog());
        DB::disableQueryLog();
    }

    public function test_foreign_keys_and_one_credit_sale_per_transaction_are_enforced(): void
    {
        $customer = Customer::createWithGeneratedCode(['customer_name' => 'Credit Customer']);
        $transaction = $this->transaction(['customer_id' => $customer->id]);
        $data = $this->creditData($transaction, $customer);
        CreditSale::create($data);
        $other = $this->transaction();
        $operations = [
            fn () => CreditSale::create($data),
            fn () => CreditSale::create(array_replace($data, ['transaction_id' => 999999])),
            fn () => CreditSale::create(array_replace($data, ['transaction_id' => $other->id, 'customer_id' => 999999])),
            fn () => $this->transaction(['customer_id' => 999999]),
            fn () => $transaction->delete(),
            fn () => $customer->delete(),
        ];
        foreach ($operations as $operation) {
            try {
                $operation();
                $this->fail('Expected database constraint rejection.');
            } catch (QueryException $exception) {
                $this->assertNotEmpty($exception->getMessage());
            }
        }
        $this->assertSame(1, CreditSale::count());
    }

    public function test_new_and_existing_indexes_are_preserved(): void
    {
        foreach ([
            'transactions' => ['transactions_customer_date_time_index', 'transactions_payment_status_date_index', 'transactions_date_time_index', 'transactions_cashier_date_time_index', 'transactions_checkout_token_unique'],
            'credit_sales' => ['credit_sales_transaction_id_unique', 'credit_sales_customer_id_due_date_index', 'credit_sales_due_date_index'],
        ] as $table => $indexes) {
            foreach ($indexes as $index) {
                $this->assertTrue(Schema::hasIndex($table, $index), $index);
            }
        }
        $this->assertTrue(Schema::hasIndex('credit_sales', 'credit_sales_transaction_id_unique', 'unique'));
        $this->migration()->down();
        $this->assertTrue(Schema::hasIndex('transactions', 'transactions_date_time_index'));
        $this->assertTrue(Schema::hasIndex('transactions', 'transactions_cashier_date_time_index'));
        $this->assertTrue(Schema::hasIndex('transactions', 'transactions_checkout_token_unique'));
        $this->migration()->up();
    }

    public function test_rerunning_up_preserves_new_states_and_resumes_interrupted_constraints(): void
    {
        $transaction = $this->transaction(['payment_status' => 'unpaid']);
        Schema::table('credit_sales', fn ($table) => $table->dropForeign(['customer_id']));
        Schema::table('credit_sales', fn ($table) => $table->dropIndex('credit_sales_due_date_index'));
        $this->migration()->up();
        $this->assertSame('unpaid', $transaction->fresh()->payment_status);
        $this->assertTrue(Schema::hasIndex('credit_sales', 'credit_sales_due_date_index'));
        $this->assertTrue(collect(Schema::getForeignKeys('credit_sales'))->contains(fn ($key) => $key['columns'] === ['customer_id']));
    }

    public function test_up_resumes_after_legacy_column_rename_without_losing_pending_value(): void
    {
        $migration = $this->migration();
        $migration->down();
        $transaction = $this->transaction(['payment_status' => 'pending']);
        Schema::table('transactions', fn ($table) => $table->renameColumn('payment_status', 'legacy_payment_status'));
        $migration->up();
        $transaction->refresh();
        $this->assertSame('pending', $transaction->legacy_payment_status);
        $this->assertSame('paid', $transaction->payment_status);
        $this->assertNull($transaction->customer_id);
    }

    public function test_rollback_refuses_credit_rows_before_removing_any_schema(): void
    {
        $customer = Customer::createWithGeneratedCode(['customer_name' => 'Credit Customer']);
        $transaction = $this->transaction();
        CreditSale::create($this->creditData($transaction, $customer));
        $this->assertRollbackRefused();
        $this->assertSame(1, CreditSale::count());
    }

    public function test_rollback_refuses_customer_links_and_unpaid_states(): void
    {
        $customer = Customer::createWithGeneratedCode(['customer_name' => 'Credit Customer']);
        $transaction = $this->transaction(['customer_id' => $customer->id]);
        $this->assertRollbackRefused();
        $transaction->update(['customer_id' => null, 'payment_status' => 'partially_paid']);
        $this->assertRollbackRefused();
        $this->assertSame('partially_paid', $transaction->fresh()->payment_status);
    }

    private function assertRollbackRefused(): void
    {
        try {
            $this->migration()->down();
            $this->fail('Rollback must preserve feature data.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('Cannot roll back credit sales', $exception->getMessage());
        }
        $this->assertTrue(Schema::hasTable('credit_sales'));
        $this->assertTrue(Schema::hasColumn('transactions', 'customer_id'));
        $this->assertTrue(Schema::hasColumn('transactions', 'legacy_payment_status'));
    }

    private function migration(): object
    {
        return require database_path('migrations/2026_09_15_000034_add_credit_sales_foundation.php');
    }

    private function transaction(array $overrides = []): Transaction
    {
        return Transaction::create(array_replace([
            'receipt_no' => 'T-'.Str::random(16), 'checkout_token' => (string) Str::uuid(),
            'date' => '2026-09-15', 'time' => '10:00:00', 'cashier_id' => User::firstOrFail()->id,
            'subtotal' => 100, 'discount' => 0, 'total' => 100,
            'payment_method' => 'cash', 'payment' => 150, 'change_due' => 50, 'status' => 'completed',
        ], $overrides));
    }

    private function creditData(Transaction $transaction, Customer $customer): array
    {
        return [
            'transaction_id' => $transaction->id, 'customer_id' => $customer->id,
            'credit_term_days' => 30, 'due_date' => '2026-10-15', 'original_credit_amount' => '100.00',
        ];
    }
}

