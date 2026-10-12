<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Transaction;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Tests\TestCase;

class CustomerFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_defaults_casts_and_indexes(): void
    {
        $customer = Customer::createWithGeneratedCode(['customer_name' => 'Test Customer'])->refresh();

        $this->assertSame('CUST-000001', $customer->customer_code);
        $this->assertFalse($customer->credit_enabled);
        $this->assertSame('0.00', $customer->credit_limit);
        $this->assertSame(30, $customer->default_credit_term_days);
        $this->assertSame('active', $customer->status);
        $this->assertNull($customer->phone);
        $this->assertNull($customer->email);
        $this->assertNull($customer->address);
        $this->assertNotNull($customer->created_at);
        $this->assertFalse(Schema::hasColumn('customers', 'current_balance'));
        $this->assertTrue(Schema::hasColumn('transactions', 'customer_id'));

        foreach (['customer_code', 'customer_name', 'status', 'credit_enabled'] as $column) {
            $index = collect(Schema::getIndexes('customers'))->firstWhere('columns', [$column]);
            $this->assertNotNull($index, "Missing index for {$column}");
            if ($column === 'customer_code') {
                $this->assertTrue($index['unique']);
            }
        }
    }

    public function test_customer_contact_and_credit_settings_round_trip(): void
    {
        $customer = Customer::createWithGeneratedCode([
            'customer_name' => 'Account Customer',
            'phone' => '09123456789',
            'email' => 'customer@example.test',
            'address' => '123 Test Street',
            'credit_enabled' => true,
            'credit_limit' => '1234567890.12',
            'default_credit_term_days' => 60,
            'status' => 'inactive',
        ])->refresh();

        $this->assertTrue($customer->credit_enabled);
        $this->assertSame('1234567890.12', $customer->credit_limit);
        $this->assertSame(60, $customer->default_credit_term_days);
        $this->assertSame('inactive', $customer->status);
        $this->assertSame('09123456789', $customer->phone);
        $this->assertSame('customer@example.test', $customer->email);
        $this->assertSame('123 Test Street', $customer->address);
    }

    public function test_codes_follow_existing_maximum_and_do_not_reuse_deleted_codes(): void
    {
        Customer::create(['customer_code' => 'CUST-000099', 'customer_name' => 'Imported']);
        $first = Customer::createWithGeneratedCode(['customer_name' => 'First']);
        $this->assertSame('CUST-000100', $first->customer_code);
        $first->delete();

        $second = Customer::createWithGeneratedCode([
            'customer_name' => 'Second',
            'customer_code' => 'CUST-000001',
        ]);
        $this->assertSame('CUST-000101', $second->customer_code);
        $this->assertDatabaseHas('document_sequences', ['sequence_key' => 'customers', 'current_value' => 101]);
        $this->assertSame(0, Transaction::count());
    }

    public function test_duplicate_customer_code_is_rejected_by_database(): void
    {
        $customer = Customer::createWithGeneratedCode(['customer_name' => 'First']);

        $this->expectException(QueryException::class);
        Customer::create(['customer_code' => $customer->customer_code, 'customer_name' => 'Duplicate']);
    }

    public function test_failed_creation_rolls_back_sequence_reservation(): void
    {
        Customer::createWithGeneratedCode(['customer_name' => 'First']);
        try {
            Customer::createWithGeneratedCode([]);
            $this->fail('A customer name is required.');
        } catch (QueryException) {
            $this->assertSame(1, Customer::count());
            $this->assertDatabaseHas('document_sequences', ['sequence_key' => 'customers', 'current_value' => 1]);
        }

        $customer = Customer::createWithGeneratedCode(['customer_name' => 'Second']);
        $this->assertSame('CUST-000002', $customer->customer_code);
    }

    public function test_outer_rollback_removes_customer_and_sequence_changes(): void
    {
        try {
            DB::transaction(function () {
                Customer::createWithGeneratedCode(['customer_name' => 'Rolled back']);
                throw new RuntimeException('Abort business operation');
            });
        } catch (RuntimeException $exception) {
            $this->assertSame('Abort business operation', $exception->getMessage());
        }

        $this->assertSame(0, Customer::count());
        $this->assertDatabaseMissing('document_sequences', ['sequence_key' => 'customers']);
    }

    public function test_transaction_relationship_declares_the_correct_foreign_key(): void
    {
        $relationship = (new Customer)->transactions();
        $this->assertInstanceOf(Transaction::class, $relationship->getRelated());
        $this->assertSame('customer_id', $relationship->getForeignKeyName());
        $this->assertSame('id', $relationship->getLocalKeyName());
    }

    public function test_migration_rollback_preserves_other_tables_and_sequence_high_water_mark(): void
    {
        Customer::createWithGeneratedCode(['customer_name' => 'First']);
        // Roll back dependent foreign keys before the customer foundation.
        $creditMigration = require database_path('migrations/2026_09_15_000034_add_credit_sales_foundation.php');
        $creditMigration->down();
        $migration = require database_path('migrations/2026_09_14_000033_create_customers_table.php');
        $migration->down();

        $this->assertFalse(Schema::hasTable('customers'));
        $this->assertTrue(Schema::hasTable('transactions'));
        $this->assertTrue(Schema::hasTable('document_sequences'));
        $migration->up();
        $creditMigration->up();

        $customer = Customer::createWithGeneratedCode(['customer_name' => 'After rollback']);
        $this->assertSame('CUST-000002', $customer->customer_code);
    }
}

