<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Retain the exact historical pending/confirmed values for a lossless rollback.
        // Each DDL step is restartable because MySQL does not transact schema changes.
        if (! Schema::hasColumn('transactions', 'legacy_payment_status')) {
            if (DB::connection()->getDriverName() === 'mysql') {
                // MySQL/MariaDB can report the enum default with embedded quotes;
                // Doctrine then double-quotes it during renameColumn(). Restate the
                // known legacy definition explicitly to keep this migration runnable.
                DB::statement("ALTER TABLE `transactions` CHANGE `payment_status` `legacy_payment_status` ENUM('pending','confirmed') NOT NULL DEFAULT 'confirmed'");
            } else {
                Schema::table('transactions', fn (Blueprint $table) => $table->renameColumn('payment_status', 'legacy_payment_status'));
            }
        }

        if (! Schema::hasColumn('transactions', 'payment_status')) {
            Schema::table('transactions', function (Blueprint $table) {
                // The database also populates existing rows with this default. No
                // monetary fields, transaction statuses or timestamps are rewritten.
                $table->enum('payment_status', ['unpaid', 'partially_paid', 'paid', 'overdue'])->default('paid');
            });
        }

        if (! Schema::hasColumn('transactions', 'customer_id')) {
            Schema::table('transactions', fn (Blueprint $table) => $table->unsignedBigInteger('customer_id')->nullable());
        }
        if (! Schema::hasIndex('transactions', 'transactions_customer_date_time_index')) {
            Schema::table('transactions', fn (Blueprint $table) => $table->index(['customer_id', 'date', 'time'], 'transactions_customer_date_time_index'));
        }
        if (! Schema::hasIndex('transactions', 'transactions_payment_status_date_index')) {
            Schema::table('transactions', fn (Blueprint $table) => $table->index(['payment_status', 'date'], 'transactions_payment_status_date_index'));
        }
        if (! collect(Schema::getForeignKeys('transactions'))->contains(fn ($key) => $key['columns'] === ['customer_id'])) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->foreign('customer_id')->references('id')->on('customers')->restrictOnDelete();
                $this->preserveSqliteStatusChecks($table);
            });
        }

        if (! Schema::hasTable('credit_sales')) {
            Schema::create('credit_sales', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('transaction_id');
                $table->unsignedBigInteger('customer_id');
                $table->unsignedSmallInteger('credit_term_days');
                $table->date('due_date');
                $table->decimal('original_credit_amount', 12, 2);
                $table->timestamps();
            });
        }
        // Add constraints separately so a failed MySQL ALTER can be retried.
        foreach ([
            'credit_sales_transaction_id_unique' => ['transaction_id'],
            'credit_sales_customer_id_due_date_index' => ['customer_id', 'due_date'],
            'credit_sales_due_date_index' => ['due_date'],
        ] as $name => $columns) {
            if (! Schema::hasIndex('credit_sales', $name)) {
                Schema::table('credit_sales', function (Blueprint $table) use ($name, $columns) {
                    if ($columns === ['transaction_id']) {
                        $table->unique($columns, $name);
                    } else {
                        $table->index($columns, $name);
                    }
                });
            }
        }
        foreach (['transaction_id' => 'transactions', 'customer_id' => 'customers'] as $column => $parent) {
            if (! collect(Schema::getForeignKeys('credit_sales'))->contains(fn ($key) => $key['columns'] === [$column])) {
                Schema::table('credit_sales', fn (Blueprint $table) => $table->foreign($column)->references('id')->on($parent)->restrictOnDelete());
            }
        }
    }

    public function down(): void
    {
        // Refuse to erase live credit records or silently translate unpaid debt
        // into the old confirmed state. This check precedes every destructive DDL.
        if ((Schema::hasTable('credit_sales') && DB::table('credit_sales')->exists())
            || (Schema::hasColumn('transactions', 'customer_id') && DB::table('transactions')->whereNotNull('customer_id')->exists())
            || (Schema::hasColumn('transactions', 'legacy_payment_status') && Schema::hasColumn('transactions', 'payment_status')
                && DB::table('transactions')->where('payment_status', '!=', 'paid')->exists())) {
            throw new RuntimeException('Cannot roll back credit sales while credit records, customer-linked transactions, or non-paid transactions exist. Preserve and reconcile these records before downgrading.');
        }

        Schema::dropIfExists('credit_sales');
        if (Schema::hasColumn('transactions', 'customer_id')) {
            if (collect(Schema::getForeignKeys('transactions'))->contains(fn ($key) => $key['columns'] === ['customer_id'])) {
                Schema::table('transactions', function (Blueprint $table) {
                    $table->dropForeign(['customer_id']);
                    $this->preserveSqliteStatusChecks($table);
                });
            }
            if (Schema::hasIndex('transactions', 'transactions_customer_date_time_index')) {
                Schema::table('transactions', fn (Blueprint $table) => $table->dropIndex('transactions_customer_date_time_index'));
            }
            Schema::table('transactions', fn (Blueprint $table) => $table->dropColumn('customer_id'));
        }
        if (Schema::hasColumn('transactions', 'legacy_payment_status')) {
            if (Schema::hasIndex('transactions', 'transactions_payment_status_date_index')) {
                Schema::table('transactions', fn (Blueprint $table) => $table->dropIndex('transactions_payment_status_date_index'));
            }
            if (Schema::hasColumn('transactions', 'payment_status')) {
                Schema::table('transactions', fn (Blueprint $table) => $table->dropColumn('payment_status'));
            }
            if (DB::connection()->getDriverName() === 'mysql') {
                DB::statement("ALTER TABLE `transactions` CHANGE `legacy_payment_status` `payment_status` ENUM('pending','confirmed') NOT NULL DEFAULT 'confirmed'");
            } else {
                Schema::table('transactions', fn (Blueprint $table) => $table->renameColumn('legacy_payment_status', 'payment_status'));
            }
        }
    }

    private function preserveSqliteStatusChecks(Blueprint $table): void
    {
        if (DB::connection()->getDriverName() !== 'sqlite') {
            return;
        }

        // Laravel rebuilds SQLite tables for FK changes; introspection does not
        // retain enum CHECK clauses. Restate them in that same rebuild.
        $table->enum('status', ['completed', 'refunded', 'partial_refund', 'voided'])->default('completed')->change();
        $table->enum('legacy_payment_status', ['pending', 'confirmed'])->default('confirmed')->change();
        $table->enum('payment_status', ['unpaid', 'partially_paid', 'paid', 'overdue'])->default('paid')->change();
    }
};
