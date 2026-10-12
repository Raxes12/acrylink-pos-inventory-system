<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DatabaseIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_transaction_query_indexes_exist(): void
    {
        $indexNames = collect(Schema::getIndexes('transactions'))->pluck('name');

        $this->assertContains('transactions_date_time_index', $indexNames);
        $this->assertContains('transactions_cashier_date_time_index', $indexNames);
    }

    public function test_transaction_query_index_migration_rolls_back_cleanly(): void
    {
        $migration = require database_path('migrations/2026_09_06_000029_add_transaction_query_indexes.php');

        $migration->down();
        $indexNames = collect(Schema::getIndexes('transactions'))->pluck('name');
        $this->assertNotContains('transactions_date_time_index', $indexNames);
        $this->assertNotContains('transactions_cashier_date_time_index', $indexNames);

        $migration->up();
        $restoredIndexNames = collect(Schema::getIndexes('transactions'))->pluck('name');
        $this->assertContains('transactions_date_time_index', $restoredIndexNames);
        $this->assertContains('transactions_cashier_date_time_index', $restoredIndexNames);
    }
}
